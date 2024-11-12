<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="tw-flex tw-justify-between tw-items-center tw-mb-2">
                    <div class="tw-flex tw-items-center">
                        <button onclick="window.history.back();" class="btn btn-primary tw-mr-2">
                            <i class="fa fa-arrow-left"></i> Quay lại
                        </button>

                    </div>
                </div>
                <div>
                    <p>
                        <?php if ($request_data->status === '0'): ?>
                            <?php if ($is_in_approve_list): ?>
                                <a href="#" onclick="showActionReasonModal('<?php echo admin_url('approvify/approve_request/' . $request_data->id) ?>', 'approve')" class="btn btn-success pull-right">
                                    <?php echo _l('approvify_btn_approve_status'); ?>
                                </a>
                                <a href="#" onclick="showActionReasonModal('<?php echo admin_url('approvify/refuse_request/' . $request_data->id) ?>', 'refuse')" class="btn btn-danger pull-right mright5">
                                    <?php echo _l('approvify_btn_refuse_status'); ?>
                                </a>
                            <?php endif; ?>
                            <?php if ($is_requester): ?>
                                <a href="<?php echo admin_url('approvify/cancel_request/' . $request_data->id) ?>" class="btn btn-info pull-right mright5">
                                    <?php echo _l('approvify_btn_canceled_status'); ?>
                                </a>
                            <?php endif; ?>
                        <?php endif; ?>
                    </p>

                    <div class="clearfix"></div>
                    <p><button class="btn btn-success" onclick="printContent()">
                            <i class="fa fa-print" aria-hidden="true"></i> In phiếu
                        </button>
                        <?php
                        $isApproved = false;
                        $activities = $this->approvify_model->getActivities($request_data->id);
                        foreach ($activities as $activity) {
                            if (strpos($activity['description'], 'Đã phê duyệt đề xuất của bạn') !== false) {
                                $isApproved = true;
                                break;
                            }
                        }
                        if ($request_data->status === '0' && !$is_review && !$isApproved) :
                        ?>
                            <a href="<?php echo admin_url('approvify/edit_request/' . $request_data->id); ?>" class="btn btn-primary">
                                <i class="fas fa-edit"></i> <?php echo _l('approvify_edit_request'); ?>
                            </a>
                        <?php endif; ?>
                    </p>
                    <h4 class="tw-mt-0 tw-font-semibold tw-text-neutral-700">
                        <span class="tw-text-lg">
                            <strong>#<?php echo $request_data->id; ?></strong> - <?php echo $request_data->request_title; ?>
                        </span>
                    </h4>

                </div>

            </div>

            <div id="print-section" class="col-md-8">
                <div class="panel_s">
                    <div class="panel-body">
                        <img src="https://info.hyh.vn/media/public/logo-HYH.png" alt="Logo">
                        <h4><?php echo _l('approvify_request_information'); ?> - Số: <?php echo $request_data->id; ?></h4>
                        <hr class="hr-panel-heading" />
                        <table class="table border table-striped">
                            <tbody>
                                <tr>
                                    <td class="bold" width="30%"><?php echo _l('approvify_request_title'); ?></td>
                                    <td><?php echo $request_data->request_title; ?></td>
                                </tr>
                                <tr>
                                    <td class="bold"><?php echo _l('approvify_request_content'); ?></td>
                                    <td>
                                        <div class="request-content">
                                            <?php echo $request_data->request_content; ?>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="bold"><?php echo _l('approvify_request_status'); ?></td>
                                    <td><?php echo approvify_return_request_status_html($request_data->status); ?></td>
                                </tr>
                                <tr>
                                    <td class="bold"><?php echo _l('approvify_table_created_at'); ?></td>
                                    <td><?php echo $request_data->created_at; ?></td>
                                </tr>
                                <tr>
                                    <td class="bold"><?php echo _l('approvify_request_reviewers'); ?></td>
                                    <td>
                                        <?php
                                        $approveList = '';
                                        if (!empty($request_data->approve_list)) {
                                            $decodeApproveList = json_decode($request_data->approve_list);
                                            foreach ($decodeApproveList as $staff) {
                                                $approveList .= get_staff_full_name($staff, ['staff_id']) . ' ; ';
                                            }
                                        }
                                        echo rtrim($approveList, ' ; ');
                                        ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin"><?php echo _l('approvify_request_activity'); ?></h4>
                        <hr class="hr-panel-heading" />
                        <div class="activity-feed">
                            <?php
                            if (empty($request_activity_log)) {
                                echo _l('approvify_empty_activity_log');
                            }
                            foreach ($request_activity_log as $activity) :
                                $name = get_staff_full_name($activity['staff_id']);
                                $href = admin_url('profile/' . $activity['staff_id']);
                            ?>
                                <div class="feed-item">
                                    <div class="date">
                                        <span class="text-has-action" data-toggle="tooltip" data-title="<?php echo _dt($activity['created_at']); ?>">
                                            <?php echo time_ago($activity['created_at']); ?> <i class="fas fa-history"></i>
                                        </span>
                                    </div>
                                    <div class="text">
                                        <p class="bold no-mbot">
                                            <a href="<?php echo $href; ?>">
                                                <?php echo staff_profile_image($activity['staff_id'], ['staff-profile-xs-image', 'pull-left mright10']); ?>
                                            </a>
                                            <?php if ($href != '') : ?>
                                                <a href="<?php echo $href; ?>"><?php echo $name; ?></a>
                                            <?php else : ?>
                                                <?php echo $name; ?> -
                                            <?php endif; ?>
                                            <?php echo $activity['description']; ?>
                                        </p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin"><?php echo _l('approvify_request_attachments'); ?></h4>
                        <hr class="hr-panel-heading" />
                        <?php
                        if (empty($request_data->attachments)) {
                            echo _l('approvify_empty_request_attachments');
                        }
                        foreach ($request_data->attachments as $image) :
                            $path = FCPATH . 'modules/approvify/uploads/requests/' . $request_data->id . '/' . $image['filename'];
                            $is_image = is_image($path);
                            if ($is_image) echo '<div class="preview_image">';
                        ?>
                            <a target="_blank" href="<?php echo substr(module_dir_url('approvify/uploads/requests/' . $request_data->id . '/' . $image['filename']), 0, -1); ?>" class="display-block mbot5" <?php if ($is_image) echo 'data-lightbox="attachment-reply-"'; ?>>
                                <i class=""></i>
                                <?php echo $image['filename']; ?>
                                <?php if ($is_image) : ?>
                                    <img class="mtop5" src="<?php echo site_url('download/preview_image?path=' . protected_file_url_by_path($path) . '&type='); ?>">
                                <?php endif; ?>
                            </a>
                        <?php
                            if ($is_image) echo '</div>';
                        endforeach;
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>

<script>
    // Hàm để chuyển đổi dữ liệu PHP thành đối tượng JavaScript
    function getPHPData() {
        return {
            id: <?php echo json_encode($request_data->id); ?>,
            request_title: <?php echo json_encode($request_data->request_title); ?>,
            request_content: <?php echo json_encode($request_data->request_content); ?>,
            status: <?php echo json_encode($request_data->status); ?>,
            created_at: <?php echo json_encode($request_data->created_at); ?>,
            approve_list: <?php echo json_encode($request_data->approve_list); ?>,
            reviewers: <?php
                        $reviewers = '';
                        if (!empty($request_data->approve_list)) {
                            $decodeApproveList = json_decode($request_data->approve_list);
                            foreach ($decodeApproveList as $staff) {
                                $reviewers .= get_staff_full_name($staff) . ' ; ';
                            }
                            $reviewers = rtrim($reviewers, ' ; ');
                        }
                        echo json_encode($reviewers);
                        ?>
        };
    }


    // Hàm để trả về HTML trạng thái yêu cầu
    function getRequestStatusHtml(status) {
        switch (status) {
            case '0':
                return '<span class="status pending">Chờ phê duyệt</span>';
            case '1':
                return '<span class="status approved">Đã phê duyệt</span>';
            case '2':
                return '<span class="status rejected">Bị từ chối</span>';
            default:
                return '<span class="status unknown">Unknown</span>';
        }
    }

    function printContent() {
        var data = getPHPData();

        var createdDate = data.created_at.split(' ')[0]; // Định dạng hiện tại: yyyy-mm-dd
        var parts = createdDate.split('-');
        var formattedDate = parts[2] + '-' + parts[1] + '-' + parts[0];

        var printContent = `
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Phiếu đề xuất - HYH Group</title>
    <style>
        @page {
            size: A4;
            margin: 0;
        }
        body {
            font-family: Arial, sans-serif;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        .container {
            max-width: 100%;
            margin: 0 auto;
            padding: 20mm;
            display: flex;
            flex-direction: column;
            flex: 1;
        }
        .header {
            display: flex;
            justify-content: flex-start;
            align-items: flex-start;
            margin-bottom: 10mm;
            overflow-x: auto;
            width: 100%;
        }
        .logo {
            max-width: 40mm;
            height: auto;
            margin-right: 10mm;
            flex-shrink: 0;
        }
        .info-section {
            display: flex;
            flex-wrap: nowrap;
            justify-content: flex-start;
            flex-grow: 1;
        }
        .info-column {
            flex: 0 0 auto;
            margin-right: 10mm;
        }
        .info-row {
            margin-bottom: 3mm;
            white-space: nowrap;
        }
        .info-label {
            font-weight: bold;
            display: inline-block;
            min-width: 30mm;
        }
        @media print {
            body {
                min-height: 0;
                display: block;
            }
            .container {
                min-height: 0;
            }
            .header {
                overflow-x: visible;
            }
        }
                .status {
                    display: inline-block;
                    padding: 1mm 2mm;
                    border-radius: 2mm;
                    font-weight: bold;
                    font-size: 10pt;
                }
                .status.pending { background-color: #ffeaa7; color: #d35400; }
                .status.approved { background-color: #55efc4; color: #27ae60; }
                .status.rejected { background-color: #fab1a0; color: #c0392b; }
                .status.unknown { background-color: #dfe6e9; color: #636e72; }
                .request-content {
                    flex: 1;
                    overflow: hidden;
                }
                /* Thêm CSS mới cho nội dung */
                .table>tbody>tr>td, .table>tfoot>tr>td {
                    color: #333333;
                }
                b, strong {
                    font-weight: 700;
                }
                .request-content h1, .request-content h2, .request-content h3 { text-align: center; }
                .request-content p { text-indent: 20px; }
                .request-content .text-center { text-align: center; }
                .request-content .text-right { text-align: right; }
                .request-content .indent { text-indent: 20px; }
                .request-content table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
                .request-content table, .request-content th, .request-content td { border: 1px solid #ccc; }
                .request-content th, .request-content td { padding: 8px; text-align: left; }
                .request-content ol, .request-content ul { padding-left: 30px; }
                .request-content li { margin-bottom: 5px; }
                @media print {
                    body {
                        print-color-adjust: exact;
                        -webkit-print-color-adjust: exact;
                    }
                }
                @media screen and (max-width: 210mm), print and (max-width: 210mm) {
                    @page {
                        size: A5;
                    }
                    body {
                        font-size: 9pt;
                    }
                    .container {
                        padding: 10mm;
                    }
                    .request-title {
                        font-size: 14pt;
                    }
                    .info-row {
                        flex: 1 0 100%;
                    }
                    .logo {
                        max-width: 30mm;
                    }
                    .request-content table { font-size: 8pt; }
                    .request-content th, .request-content td { padding: 4px; }
                }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="https://info.hyh.vn/media/public/logo-HYH.png" alt="Logo" class="logo">
            <div class="info-section">
                <div class="info-column">
                    <div class="info-row"><span class="info-label">Mã phiếu:</span> #${data.id}</div>
                    <div class="info-row"><span class="info-label">Trạng thái:</span> ${getRequestStatusHtml(data.status)}</div>
                </div>
                <div class="info-column">
                    <div class="info-row"><span class="info-label">Ngày tạo:</span> ${formattedDate}</div>
                    <div class="info-row"><span class="info-label">Người duyệt:</span> ${data.reviewers}</div>
                </div>
            </div>
        </div>

        <div class="request-content">
            ${data.request_content}
        </div>
    </div>
</body>
</html>
    `;

        var printWindow = window.open('', '_blank');
        printWindow.document.write(printContent);
        printWindow.document.close();

        printWindow.onload = function() {
            printWindow.print();
            printWindow.onafterprint = function() {
                printWindow.close();
            };
        };
    }
</script>
<!-- lý do -->
<div class="modal fade" id="actionReasonModal" tabindex="-1" role="dialog" aria-labelledby="actionReasonModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="actionReasonModalLabel">Nhập lý do</h4>
            </div>
            <div class="modal-body">
                <textarea id="actionReason" class="form-control" rows="3" placeholder="Nhập lý do ở đây (không bắt buộc)"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Đóng</button>
                <button type="button" class="btn btn-primary" id="submitActionReason">Xác nhận</button>
            </div>
        </div>
    </div>
</div>

<script>
    var actionUrl = '';
    var actionType = '';

    function showActionReasonModal(url, type) {
        actionUrl = url;
        actionType = type;
        $('#actionReasonModalLabel').text(type === 'approve' ? 'Nhập lý do phê duyệt' : 'Nhập lý do từ chối');
        $('#submitActionReason').removeClass('btn-success btn-danger').addClass(type === 'approve' ? 'btn-success' : 'btn-danger');
        $('#submitActionReason').text(type === 'approve' ? 'Phê duyệt' : 'Từ chối');
        $('#actionReasonModal').modal('show');
    }

    $('#submitActionReason').click(function() {
        var reason = $('#actionReason').val();
        var url = actionUrl + (actionUrl.indexOf('?') > -1 ? '&' : '?') + 'reason=' + encodeURIComponent(reason);
        window.location.href = url;
    });
</script>
</body>

</html>