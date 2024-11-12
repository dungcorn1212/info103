<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Approvify_model extends App_Model
{

    public function __construct()
    {
        parent::__construct();
    }


    public function updateRequest($id, $data)
    {
        log_message('debug', 'updateRequest input data: ' . print_r($data, true));

        $updateData = [
            'request_title' => $data['request_title'] ?? null,
            'request_content' => $data['request_content'] ?? null,
            'status' => $data['status'] ?? null,
            // Thêm các trường khác nếu cần
        ];

        // Loại bỏ các phần tử null khỏi $updateData
        $updateData = array_filter($updateData, function ($value) {
            return $value !== null;
        });

        log_message('debug', 'updateRequest filtered data: ' . print_r($updateData, true));

        $this->db->trans_start();

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'approvify_requests', $updateData);

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            $error = $this->db->error();
            log_message('error', 'updateRequest transaction failed: ' . $error['message']);
            return ['success' => false, 'message' => 'Lỗi cập nhật: ' . $error['message']];
        }

        $affected_rows = $this->db->affected_rows();
        log_message('debug', 'updateRequest affected rows: ' . $affected_rows);

        if ($affected_rows > 0) {
            $this->logActivity($id, 'request_updated');
            return ['success' => true, 'message' => 'Cập nhật đề xuất thành công.'];
        } else {
            log_message('info', 'updateRequest: No rows affected. Data might be unchanged.');
            return ['success' => true, 'message' => 'Không có thay đổi nào được cập nhật.'];
        }
    }

    public function addRequest($data)
    {

        $data['created_at']      = date('Y-m-d H:i:s');
        $data['requester_id'] = get_staff_user_id();
        $data['request_title']   = trim($data['request_title']);
        $data['request_content']   = nl2br_save_html($data['request_content']);

        $categoryData = $this->getType($data['category_id']);

        $this->db->insert(db_prefix() . 'approvify_requests', $data);
        $requestId = $this->db->insert_id();
        if ($requestId) {

            if (!empty($categoryData->approve_list)) {

                $this->load->model('emails_model');
                $decodeApproveList = json_decode($categoryData->approve_list);

                // foreach ($decodeApproveList as $staff) {

                //     $staffData = get_staff($staff);
                $staffs = $this->getStaffs([$decodeApproveList[0]]);
                $staff = $staffs[$decodeApproveList[0]];
                $notified = add_notification([
                    'description'     => 'approvify_new_request_from_staff',
                    'touserid'        => $staff['staffid'],
                    'fromcompany'     => 1,
                    'fromuserid'      => 0,
                    'link'            => 'approvify/view_request/' . $requestId . '?review=1',
                    'additional_data' => serialize([
                        get_staff_full_name($staff['staffid']),
                        $data['request_title'],
                    ]),
                ]);

                if ($notified) {
                    pusher_trigger_notification([$staff['staffid']]);
                }

                // Thay thế $data['title'] bằng $categoryData->category_name để lấy tên danh mục
                $this->emails_model->send_simple_email(
                    $staff['email'],
                    get_option('companyname') . ' - Bạn có đề xuất mới cần phê duyệt',
                    '
        Xin chào ' . get_staff_full_name($staff['staffid']) . ',
<br>
Bạn có đề xuất mới được tạo bởi: <strong>' . (get_staff_full_name(get_staff_user_id())) . ' - ' . $categoryData->category_name . '</span></strong> cần được phê duyệt với tiêu đề: <strong>' . $data['request_title'] . '</strong>.
<br>
<a href="' . admin_url('approvify/view_request/' . $requestId) . '/?review=true">Truy cập trang quản lý</a>
<br>
Trân trọng,<br>
' . get_option('companyname') . '
        '
                );
            }

            // }

            $attachments = approvify_handle_request_attachments($requestId);
            if ($attachments) {
                $this->insertRequestFilesToDatabase($attachments, $requestId);
            }

            $_attachments = $this->getRequestAttachments($requestId);

            return $requestId;
        }

        return false;
    }
    public function deleteRequestAttachment($attachmentId)
    {
        $this->db->where('id', $attachmentId);
        $attachment = $this->db->get(db_prefix() . 'approvify_request_files')->row();

        if ($attachment) {
            $filePath = get_upload_path_by_type('approvify') . $attachment->request_id . '/' . $attachment->file_name;
            if (file_exists($filePath)) {
                if (!unlink($filePath)) {
                    log_message('error', 'Không thể xóa file: ' . $filePath);
                    return false;
                }
            }

            $this->db->where('id', $attachmentId);
            $deleted = $this->db->delete(db_prefix() . 'approvify_request_files');

            if (!$deleted) {
                log_message('error', 'Không thể xóa bản ghi từ cơ sở dữ liệu cho attachment_id: ' . $attachmentId);
            }

            return $deleted;
        }

        log_message('error', 'Không tìm thấy attachment với id: ' . $attachmentId);
        return false;
    }

    public function getRequest($requestId)
    {
        $this->db->select(db_prefix() . 'approvify_requests.*, ' . db_prefix() . 'approvify_approval_categories.category_name, ' . db_prefix() . 'approvify_approval_categories.approve_list');
        $this->db->from(db_prefix() . 'approvify_requests');
        $this->db->join(db_prefix() . 'approvify_approval_categories', db_prefix() . 'approvify_approval_categories.id = ' . db_prefix() . 'approvify_requests.category_id', 'left');

        $this->db->where(db_prefix() . 'approvify_requests.id', $requestId);

        $request = $this->db->get()->row();
        if ($request) {
            $request->attachments = $this->getRequestAttachments($request->id);
        }

        return $request;
    }

    public function getRequestAttachments($id)
    {
        $this->db->where('request_id', $id);
        return $this->db->get('approvify_request_files')->result_array();
    }

    public function insertRequestFilesToDatabase($attachments, $requestId)
    {
        foreach ($attachments as $attachment) {
            $attachment['request_id']  = $requestId;
            $attachment['created_at'] = date('Y-m-d H:i:s');
            $attachment['filename'] = $attachment['file_name'];
            unset($attachment['file_name'], $attachment['filetype']);

            $this->db->insert(db_prefix() . 'approvify_request_files', $attachment);
        }
    }


    public function addActivity($data)
    {
        $this->db->insert(db_prefix() . 'approvify_request_activity', $data);
        $insert_id = $this->db->insert_id();

        if ($insert_id) {
            return $insert_id;
        }

        return false;
    }

    public function getActivities($requestId)
    {
        $this->db->where('request_id', $requestId);
        return $this->db->get(db_prefix() . 'approvify_request_activity')->result_array();
    }

    public function deleteRequest($requestId)
    {
        // Bắt đầu một giao dịch cơ sở dữ liệu
        $this->db->trans_start();

        // Xóa tệp đính kèm
        $attachments = $this->getRequestAttachments($requestId);
        foreach ($attachments as $attachment) {
            $this->deleteRequestAttachment($attachment['id']);
        }

        // Xóa các hoạt động liên quan
        $this->db->where('request_id', $requestId);
        $this->db->delete(db_prefix() . 'approvify_request_activity');

        // Xóa đề xuất
        $this->db->where('id', $requestId);
        $this->db->delete(db_prefix() . 'approvify_requests');

        // Hoàn thành giao dịch
        $this->db->trans_complete();

        // Trả về true nếu giao dịch thành công, ngược lại trả về false
        return $this->db->trans_status();
    }

    public function addType($data)
    {
        $this->db->insert(db_prefix() . 'approvify_approval_categories', $data);
        $insert_id = $this->db->insert_id();

        if ($insert_id) {
            return $insert_id;
        }

        return false;
    }

    public function getType($id)
    {
        $this->db->where('id', $id);
        return $this->db->get(db_prefix() . 'approvify_approval_categories')->row();
    }

    public function getTypes()
    {
        $this->db->where('is_active', '1');
        return $this->db->get(db_prefix() . 'approvify_approval_categories')->result_array();
    }

    public function updateType($id, $data)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'approvify_approval_categories', $data);

        return $this->db->affected_rows() > 0;
    }

    public function deleteType($id)
    {

        if (is_reference_in_table('category_id', db_prefix() . 'approvify_requests', $id)) {
            return [
                'referenced' => true,
            ];
        }

        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'approvify_approval_categories');

        if ($this->db->affected_rows() > 0) {
            return true;
        }

        return false;
    }

    public function changeTypeStatus($id, $status)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'approvify_approval_categories', [
            'is_active' => $status,
        ]);

        if ($this->db->affected_rows() > 0) {
            return true;
        }

        return false;
    }

    public function addRequestHistory($data)
    {
        $this->db->insert(db_prefix() . 'approvify_request_histories', $data);
        $approvify_request_history_id = $this->db->insert_id();
        return $approvify_request_history_id;
    }

    public function getRequestHistories($requestId)
    {
        $this->db->where('approvify_request_id', $requestId);
        $result = $this->db->get(db_prefix() . 'approvify_request_histories')->result_array();
        if (empty($result)) {
            return $result;
        }
        $data = [];
        foreach ($result as $item) {
            $data[$item['approve_user']] = $item;
        }
        return $data;
    }

    public function getStaffs($staffIds)
    {
        $this->db->where_in('staffid', $staffIds);
        $result = $this->db->get(db_prefix() . 'staff')->result_array();

        if (empty($result)) {
            return $result;
        }
        $data = [];
        foreach ($result as $item) {
            $data[$item['staffid']] = $item;
        }
        return $data;
    }
}
