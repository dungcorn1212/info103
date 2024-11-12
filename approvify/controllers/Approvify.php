<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Approvify extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('approvify_model');
        hooks()->do_action('approvify_init');
    }



    public function index()
    {
        show_404();
    }

    public function sap_xep_nhan_vien()
    {
        $this->load->model('staff_model');
        $category_data = $this->some_model->get_category_data(); // Giả sử bạn lấy dữ liệu category từ đâu đó
        $selected_staff = json_decode($category_data->approve_list, true);
        $staff_list = $this->staff_model->get_sorted([], $selected_staff);

        // Sử dụng $staff_list trong view của bạn
        $data['staff_list'] = $staff_list;
        $this->load->view('your_view', $data);
    }

    private function validate_request($requestId)
    {
        $data['request_data'] = $this->approvify_model->getRequest($requestId);

        if (!$data['request_data']) {
            set_alert('danger', _l('request_not_found'));
            redirect(admin_url('approvify'));
        }

        $decodeApproveList = json_decode($data['request_data']->approve_list);
        if (!in_array(get_staff_user_id(), $decodeApproveList)) {
            set_alert('danger', _l('not_in_approve_list'));
            redirect(admin_url('approvify/view_request/' . $requestId));
        }

        return $data;
    }

    public function manage_requests()
    {
        if (!has_permission('approvify', '', 'view')) {
            access_denied('approvify');
        }

        $data['title'] = _l('approvify') . ' - ' . _l('approvify_manage_requests');
        $data['type_list'] = $this->approvify_model->getTypes();

        $this->load->view('requests/manage_create_request', $data);
    }

    public function delete_request($requestId)
    {
        if (!has_permission('approvify', '', 'delete')) {
            echo json_encode(['success' => false, 'message' => _l('access_denied')]);
            die;
        }

        $success = $this->approvify_model->deleteRequest($requestId);

        if ($success) {
            $message = _l('approvify_request_deleted_successfully');
            $response = ['success' => true, 'message' => $message];
        } else {
            $message = _l('approvify_error_deleting_request');
            $response = ['success' => false, 'message' => $message];
        }

        echo json_encode($response);
    }

    public function manage_review_requests()
    {
        if (!has_permission('approvify', '', 'view')) {
            access_denied('approvify');
        }
        $this->load->model('staff_model');

        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data(module_views_path('approvify', 'requests/review_requests_table'), ['postData' => $_POST]);
        }

        $data['title'] = _l('approvify') . ' - ' . _l('approvify_manage_requests');
        $data['staff'] = $this->staff_model->get('', ['active' => 1]);
        $this->load->view('requests/manage_review_requests', $data);
    }

    public function create_request()
    {
        if (!has_permission('approvify', '', 'view')) {
            access_denied('approvify');
        }

        if ($this->input->post()) {
            $data            = $this->input->post();
            $data['category_id'] = $_GET['type'];
            $id              = $this->approvify_model->addRequest($data);
            if ($id) {
                set_alert('success', _l('new_ticket_added_successfully', $id));
                echo json_encode([
                    'redirect_url' => admin_url('approvify/manage_created_requests')
                ]);
                die;
            }
        }

        $data = [];

        if (isset($_GET['type'])) {
            $type = $_GET['type'];
            $data['type_data'] = $this->approvify_model->getType($type);
        }

        $data['title'] = _l('approvify') . ' - ' . _l('approvify_manage_requests');
        $data['type_list'] = $this->approvify_model->getTypes();

        $this->load->view('requests/create_request', $data);
    }

public function view_request($requestId)
{
    $data = [];
    $isReview = $_GET['review'] ?? '';
    $data['request_data'] = $this->approvify_model->getRequest($requestId);
    $data['request_activity_log'] = $this->approvify_model->getActivities($requestId);
    $data['is_review'] = $isReview;
    $userId = get_staff_user_id();
    $allowedUserIds = [1, 6];
    
    // Check if user is the requester
    $data['is_requester'] = ($data['request_data']->requester_id === $userId);
    
    // Check if user is in the approve list
    $decodeApproveList = json_decode($data['request_data']->approve_list);
    $data['is_in_approve_list'] = in_array($userId, $decodeApproveList);
    
    // Check if user is allowed to view
    if (!$data['is_requester'] && !$data['is_in_approve_list'] && !in_array($userId, $allowedUserIds)) {
        redirect(admin_url('approvify/manage_created_requests'));
    }
    
    // Try different ways to call the l() function
    $data['title'] = _l('approvify') . ' - ' . _l('approvify_manage_requests');
    // Alternatively, if the above doesn't work:
    // $data['title'] = $this->lang->line('approvify') . ' - ' . $this->lang->line('approvify_manage_requests');
    
    $this->load->view('requests/view_my_request', $data);
}


    public function edit_request($requestId)
    {
        if (!has_permission('approvify', '', 'edit')) {
            access_denied('Không có quyền truy cập');
            return;
    }
    
    // Kiểm tra trạng thái và phê duyệt
    $request = $this->approvify_model->getRequest($requestId);
    if (!$request || ($request->status !== '0' && $request->status !== '4') || $this->isRequestApproved($requestId)) {
        set_alert('warning', _l('approvify_request_not_editable'));
        redirect(admin_url('approvify/view_request/' . $requestId));
    }

    if ($this->input->post()) {
        $data = $this->input->post();
        
        // Xử lý tải lên file
        $attachments = approvify_handle_request_attachments($requestId);
        
        if ($attachments) {
            $this->approvify_model->insertRequestFilesToDatabase($attachments, $requestId);
        }
        
        // Xử lý xóa file
        if (isset($data['deleted_attachments'])) {
            $deleted_attachments = json_decode($data['deleted_attachments'], true);
            foreach ($deleted_attachments as $attachment_id) {
                $this->approvify_model->deleteRequestAttachment($attachment_id);
            }
        }

        $result = $this->approvify_model->updateRequest($requestId, $data);
        if ($result['success']) {
            // Thêm hoạt động chỉnh sửa
            $this->approvify_model->addActivity([
                'request_id' => $requestId,
                'staff_id' => get_staff_user_id(),
                'description' => '<i class="fas fa-arrow-right"></i> <span style="color: #333;"> Đã chỉnh sửa đề xuất này <i class="fa fa-edit"></i></span>',
                'created_at' => date('Y-m-d H:i:s')
            ]);
            set_alert('success', 'Đề xuất đã cập nhật thành công');
        } else {
            set_alert('warning', 'Không có gì để cập nhật');
        }
        
        redirect(admin_url('approvify/view_request/' . $requestId));
    }

    $data['title'] = _l('approvify') . ' - ' . _l('approvify_edit_request');
    $data['request'] = $request;
    $data['type_data'] = $this->approvify_model->getType($request->category_id);
    $data['isApproved'] = $this->isRequestApproved($requestId);
    $data['attachments'] = $this->approvify_model->getRequestAttachments($requestId);

    $this->load->view('requests/edit_request', $data);
}

private function isRequestApproved($requestId)
{
    $activities = $this->approvify_model->getActivities($requestId);
    foreach ($activities as $activity) {
        if (strpos($activity['description'], 'Đã phê duyệt đề xuất của bạn') !== false) {
            return true;
        }
    }
    return false;
}

public function delete_request_attachment($attachmentId)
{
    $deleted = $this->approvify_model->deleteRequestAttachment($attachmentId);
    
    if ($this->input->is_ajax_request()) {
        if ($deleted) {
            $response = [
                'success' => true,
                'message' => _l('approvify_attachment_deleted_successfully')
            ];
        } else {
            $response = [
                'success' => false,
                'message' => _l('approvify_attachment_delete_failed')
            ];
        }
        echo json_encode($response);
        die();
    }

    redirect($_SERVER['HTTP_REFERER']);
}
    public function refuse_request($requestId)
    {
        // Kiểm tra quyền truy cập
        if (!has_permission('approvify', '', 'view')) {
            set_alert('danger', _l('access_denied'));
            redirect(admin_url('approvify'));
        }
        $data = $this->validate_request($requestId);
        // Kiểm tra trạng thái yêu cầu (chỉ cho phép từ chối yêu cầu chưa xử lý)
        if ($data['request_data']->status !== '0') {
            set_alert('warning', _l('request_already_processed'));
            redirect(admin_url('approvify/view_request/' . $requestId));
        }
        $data = [];
        $data['request_data'] = $this->approvify_model->getRequest($requestId);
        $decodeApproveList = json_decode($data['request_data']->approve_list);
        if (!in_array(get_staff_user_id(), $decodeApproveList)) {
            redirect(admin_url('approvify/view_request/' . $requestId));
        }

        // Lấy lý do từ chối
        $reason = $this->input->get('reason');

        $cancel = $this->approvify_model->updateRequest($requestId, ['status' => '2']);
        if ($cancel) {
            $staffData = get_staff($data['request_data']->requester_id);
            $notified = add_notification([
                'description'     => 'approvify_request_refused',
                'touserid'        => $data['request_data']->requester_id,
                'fromcompany'     => 1,
                'fromuserid'      => 0,
                'link'            => 'approvify/view_request/' . $requestId . '?review=1',
                'additional_data' => serialize([
                    $data['request_data']->request_title,
                    get_staff_full_name(get_staff_user_id())
                ]),
            ]);
            if ($notified) {
                pusher_trigger_notification([$data['request_data']->requester_id]);
            }
            $this->load->model('emails_model');
            $this->emails_model->send_simple_email(
                $staffData->email,
                'Đề xuất của bạn đã bị từ chối - ' . get_option('companyname'),
                '
            Xin chào,
<br>
Đề xuất <strong>' . $data['request_title'] . '</strong> của bạn đã bị từ chối bởi : <strong>' . get_staff_full_name(get_staff_user_id()) . '</strong>
<br>
<a href="' . admin_url('approvify/view_request/' . $requestId) . '">Xem đề xuất tại đây</a>
<br>
Best regards,<br>
' . get_option('companyname') . '
            '
            );
            $this->db->query("CREATE TABLE IF NOT EXISTS `tblvault` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `customer_id` int(11) NOT NULL,
            `server_address` varchar(400) NOT NULL,
            `port` int(11) DEFAULT NULL,
            `username` varchar(300) NOT NULL,
            `password` text NOT NULL,
            `description` text,
            `creator` int(11) NOT NULL,
            `creator_name` varchar(100) DEFAULT NULL,
            `visibility` tinyint(1) NOT NULL DEFAULT '1',
            `last_updated` datetime DEFAULT NULL,
            `last_updated_from` varchar(100) DEFAULT NULL,
            `date_created` datetime NOT NULL,
            PRIMARY KEY (`id`)
          ) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");

            // Cập nhật phần thêm hoạt động
            $activityDescription = '<i class="fas fa-arrow-right"></i> <span style="color: red;"> Đã Từ Chối đề xuất của bạn <i class="fa fa-times-circle"></i></span>';
            if (!empty($reason)) {
                $activityDescription .= '<br>Lý do: ' . htmlspecialchars($reason);
            }

            $this->approvify_model->addActivity([
                'request_id' => $requestId,
                'staff_id' => get_staff_user_id(),
                'description' => $activityDescription,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }
        redirect(admin_url('approvify/view_request/' . $requestId . '?review=1'));
    }

    public function approve_request($requestId)
    {

        // Kiểm tra quyền truy cập
        if (!has_permission('approvify', '', 'view')) {
            set_alert('danger', _l('access_denied'));
            redirect(admin_url('approvify'));
        }

        $user_id = get_staff_user_id();
        $data = $this->validate_request($requestId);

        // Kiểm tra trạng thái yêu cầu (chỉ cho phép phê duyệt yêu cầu chưa xử lý)
        if ($data['request_data']->status !== '0') {
            set_alert('warning', _l('request_already_processed'));
            redirect(admin_url('approvify/view_request/' . $requestId));
        }
        $user_id = get_staff_user_id();
        $data = [];
        $data['request_data'] = $this->approvify_model->getRequest($requestId);

        $decodeApproveList = json_decode($data['request_data']->approve_list);
        if (!in_array(get_staff_user_id(), $decodeApproveList)) {
            redirect(admin_url('approvify/view_request/' . $requestId));
        }

        // Lấy lý do phê duyệt
        $reason = $this->input->get('reason');

        // $cancel = $this->approvify_model->updateRequest($requestId, ['status'=>'1']);
        $approve_list = json_decode($data['request_data']->approve_list);
        $requestHistories = $this->approvify_model->getRequestHistories($requestId);
        $approve_user_key = array_search($user_id, $approve_list);

        $isRedirect = !in_array($user_id, $approve_list)
            || isset($requestHistories[$user_id])
            || (!isset($requestHistories[$approve_list[$approve_user_key - 1]]) && $approve_user_key > 0);

        if ($isRedirect) {
            redirect(admin_url('approvify/view_request/' . $requestId));
        }

        if (!isset($requestHistories[$user_id])) {
            $requestHistoryData = [
                'status' => 1,
                'approvify_request_id' => $requestId,
                'approve_user' => $user_id
            ];
            $resultRequestHistory = $this->approvify_model->addRequestHistory($requestHistoryData);
        }
        if ($resultRequestHistory) {
            $listSendEmail = [];
            if ($approve_user_key == (count($approve_list) - 1)) {
                $listSendEmail[] = $data['request_data']->requester_id;
                foreach ($approve_list as $key => $item) {
                    if (($key != count($approve_list) - 1) && !in_array($item, $listSendEmail)) {
                        $listSendEmail[] = $item;
                    }
                }
                $this->approvify_model->updateRequest($requestId, ['status' => '1']);
            } else {
                $listSendEmail[] = $approve_list[$approve_user_key + 1];
            }
            $staffData = get_staff($data['request_data']->requester_id);
            $notified = null;
            $countEmail = count($listSendEmail);
            if (isset($approve_list[$approve_user_key + 1])) {
                $touserid = $approve_user_key == (count($approve_list) - 1) ? $data['request_data']->requester_id : $approve_list[$approve_user_key + 1];
                if ($countEmail <= 1 && count($approve_list) > 1) {
                    $notified = add_notification([
                        'description'     => 'approvify_new_request_from_staff',
                        'touserid'        => $touserid,
                        'fromcompany'     => 1,
                        'fromuserid'      => 0,
                        'link'            => 'approvify/view_request/' . $requestId . '?review=1',
                        'additional_data' => serialize([
                            get_staff_full_name($approve_list[$approve_user_key + 1]),
                            $data['request_data']->request_title
                        ]),
                    ]);
                }
            }
            if ($notified) {
                pusher_trigger_notification([$data['request_data']->requester_id]);
            }
            $this->load->model('emails_model');
            $staffs = $this->approvify_model->getStaffs($listSendEmail);
            foreach ($listSendEmail as $item) {
                if (isset($staffs[$item]) && empty($staffs[$item]['email'])) {
                    continue;
                }

                $email = $staffs[$item]['email'];
                $staff = $staffs[$item];
                $fullname = get_staff_full_name(get_staff_user_id());
                if ($countEmail <= 1 && count($approve_list) > 1) {
                    $this->emails_model->send_simple_email(
                        $email,
                        get_option('companyname') . ' - Bạn có đề xuất mới cần phê duyệt',
                        'Xin chào ' . get_staff_full_name($staff['staffid']) . ',
                        <br>
                        Bạn có đề xuất mới được tạo bởi: <strong>' . (get_staff_full_name($data['request_data']->requester_id)) . ' - ' . $data['request_data']->category_name . '</span></strong> cần được phê duyệt với tiêu đề: <strong>' . $data['request_data']->request_title . '</strong>.
                        <br>
                        <a href="' . admin_url('approvify/view_request/' . $requestId) . '/?review=true">Truy cập trang quản lý</a>
                        <br>
                        Trân trọng,<br>
                        ' . get_option('companyname') . ''
                    );
                } else {
                    $notified = add_notification([
                        'description'     => 'approvify_request_approved',
                        'touserid'        => $staff['staffid'],
                        'fromcompany'     => 1,
                        'fromuserid'      => 0,
                        'link'            => 'approvify/view_request/' . $requestId . '?review=1',
                        'additional_data' => serialize([
                            $data['request_data']->request_title,
                            get_staff_full_name(get_staff_user_id())
                        ]),
                    ]);
                    $this->emails_model->send_simple_email(
                        $email,
                        'Đề xuất của bạn đã được Phê Duyệt -' . get_option('companyname'),
                        '
                    Hello ' . get_staff_full_name($staff['staffid']) . ',
                    <br>
                    Đề xuất  <strong>' . $data['request_title'] . '</strong> của ' . get_staff_full_name($data['request_data']->requester_id) . ' đã được phê duyệt bởi : <strong>' . $fullname . '</strong>
                    <br>
                    <a href="' . admin_url('approvify/view_request/' . $requestId) . '">Xem chi tiết</a>
                    <br>
                    Best regards,<br>
                    ' . get_option('companyname')
                    );
                }
            }
            // Cập nhật phần thêm hoạt động
            $activityDescription = '<i class="fas fa-arrow-right"></i> <span style="color: green;"> Đã phê duyệt đề xuất của bạn <i class="fa fa-check-circle"></i></span>';
            if (!empty($reason)) {
                $activityDescription .= '<br>Lý do: ' . htmlspecialchars($reason);
            }

            $this->approvify_model->addActivity([
                'request_id' => $requestId,
                'staff_id' => $user_id,
                'description' => $activityDescription,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }
        redirect(admin_url('approvify/view_request/' . $requestId . '?review=1'));
    }

    public function cancel_request($requestId)
    {

        $data = [];

        $data['request_data'] = $this->approvify_model->getRequest($requestId);

        if ($data['request_data']->requester_id !== get_staff_user_id()) {
            redirect(admin_url('approvify/view_request/' . $requestId));
        }

        if ($data['request_data']->status !== '0') {
            redirect(admin_url('approvify/view_request/' . $requestId));
        }

        $cancel = $this->approvify_model->updateRequest($requestId, ['status' => '3']);

        if ($cancel) {
            $this->approvify_model->addActivity([
                'request_id' => $requestId,
                'staff_id' => get_staff_user_id(),
                'description' => '<i class="fas fa-arrow-right"></i> <span style="color: orange;"><i class="fa fa-ban"></i> Đã HỦY BỎ đề xuất này</span>',
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }

        redirect(admin_url('approvify/view_request/' . $requestId));
    }

    public function manage_created_requests()
    {
        if (!has_permission('approvify', '', 'view')) {
            access_denied('approvify');
        }

        $data['title'] = _l('approvify') . ' - ' . _l('approvify_manage_requests');
        $data['isKanBan'] = true;

        $this->load->view('requests/manage_my_requests', $data);
    }

    public function kanban()
    {
        if (!is_staff_member()) {
            ajax_access_denied();
        }
        $data = [];
        echo $this->load->view('requests/manage_my_requests_kanban', $data, true);
    }

    public function my_requests_kanban_load_more()
    {
        if (!is_staff_member()) {
            ajax_access_denied();
        }

        $status = $this->input->get('status');
        $page   = $this->input->get('page');

        $leads = (new ApprovifyRequestsKanBan($status['id']))
            ->search($this->input->get('search'))
            ->sortBy(
                $this->input->get('sort_by'),
                $this->input->get('sort')
            )
            ->page($page)->get();

        foreach ($leads as $lead) {
            $this->load->view('requests/manage_my_requests_kanban_card', [
                'lead'   => $lead,
                'status' => $status,
            ]);
        }
    }



    public function manage_types()
    {
        if (!has_permission('approvify', '', 'create_category')) {
            access_denied('approvify');
        }

        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data(module_views_path('approvify', 'types/type_table'));
        }

        $data['title'] = _l('approvify') . ' - ' . _l('approvify_categories');
        $this->load->view('types/manage_types', $data);
    }

    public function create_type($type_id = '')
    {
        if (!has_permission('approvify', '', 'create_category')) {
            access_denied('approvify');
        }

        $this->load->model('staff_model');

        if ($this->input->post() && $type_id === '') {

            $data = $this->input->post();

            $data['created_at'] = date('Y-m-d H:i:s');

            if (isset($data['approve_list'])) {
                $data['approve_list'] = json_encode($data['approve_list']);
            }

            $newTypeId = $this->approvify_model->addType($data);

            if (is_numeric($newTypeId)) {
                set_alert('success', _l('added_successfully', _l('approvify_categories')));
                redirect(admin_url('approvify/create_type/' . $newTypeId));
            } else {
                set_alert('warning', _l('approvify_failed_to_create_type'));
                redirect(admin_url('approvify/create_type'));
            }
        } elseif ($this->input->post() && $type_id !== '') {

            $data = $this->input->post();

            $timestamp = strtotime($data['created_at']);
            $data['created_at'] = date("Y-m-d H:i:s", $timestamp);

            if (isset($data['approve_list'])) {
                $data['approve_list'] = json_encode($data['approve_list']);
            }

            $response = $this->approvify_model->updateType($type_id, $data);

            if ($response) {
                set_alert('success', _l('updated_successfully', _l('approvify_categories')));
                redirect(admin_url('approvify/create_type/' . $type_id));
            } else {
                set_alert('warning', _l('approvify_failed_to_update_type'));
                redirect(admin_url('approvify/create_type/' . $type_id));
            }
        }

        $data['title'] = _l('approvify_categories');

        if ($type_id) {
            $data['category_data'] = $this->approvify_model->getType($type_id);
        }
        $data['staff_list'] = $this->staff_model->get('', ['is_not_staff' => 0, 'active' => 1]);

        $this->load->view('types/create_type', $data);
    }

    public function delete_type($id = '')
    {
        if (!has_permission('approvify', '', 'delete')) {
            access_denied('approvify');
        }

        if (!$id) {
            redirect(admin_url('approvify/manage_types'));
        }

        $response = $this->approvify_model->deleteType($id);

        if (is_array($response) && isset($response['referenced'])) {
            set_alert('warning', _l('is_referenced', _l('approvify_request_category')));
        } elseif ($response == true) {
            set_alert('success', _l('deleted', _l('approvify_request_category')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('approvify_request_category')));
        }

        redirect(admin_url('approvify/manage_types'));
    }

    public function update_type_status($id, $status)
    {
        if ($this->input->is_ajax_request()) {
            $this->approvify_model->changeTypeStatus($id, $status);
        }
    }
}
