<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="tw-flex tw-justify-between tw-items-center tw-mb-2">
                    <h4 class="tw-mt-0 tw-font-semibold tw-text-neutral-700">
                        <span class="tw-text-lg">
                            <strong>#<?php echo $request_data->id; ?></strong>-<?php echo $request_data->request_title; ?>
                        </span>
                                            <button class="btn btn-success" onclick="printContent()">
                        <i class="fa fa-print" aria-hidden="true"></i> In phiếu
                    </button>
                    </h4>
                </div>
                <div>
                    <p>
                        <?php if (!empty($is_review)) : ?>
                            <a href="<?php echo admin_url('approvify/approve_request/' . $request_data->id) ?>" class="btn btn-success pull-right">
                                <?php echo _l('approvify_btn_approve_status'); ?>
                            </a>
                            <a href="<?php echo admin_url('approvify/refuse_request/' . $request_data->id) ?>" class="btn btn-danger pull-right mright5">
                                <?php echo _l('approvify_btn_refuse_status'); ?>
                            </a>
                        <?php endif; ?>
                        <?php if ($request_data->status === '0') : ?>
                            <a href="<?php echo admin_url('approvify/cancel_request/' . $request_data->id) ?>" class="btn btn-info pull-right mright5">
                                <?php echo _l('approvify_btn_canceled_status'); ?>
                            </a>
                        <?php endif; ?>
                    </p>
                    <div class="clearfix"></div>
                    <p></p>
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
                                            <?php echo time_ago($activity['created_at']); ?>
                                        </span>
                                    </div>
                                    <div class="text">
                                        <p class="bold no-mbot">
                                            <a href="<?php echo $href; ?>">
                                                <?php echo staff_profile_image($activity['staff_id'], ['staff-profile-xs-image', 'pull-left mright10']); ?>
                                            </a>
                                            <?php if ($href != '') : ?>
                                                <a href="<?php echo $href; ?>"><?php echo $name; ?></a> -
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
                            <a href="<?php echo substr(module_dir_url('approvify/uploads/requests/' . $request_data->id . '/' . $image['filename']), 0, -1); ?>" class="display-block mbot5" <?php if ($is_image) echo 'data-lightbox="attachment-reply-"'; ?>>
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

<script type="text/javascript">
function printContent() {
    var printSection = document.getElementById("print-section");
    var printDiv = document.createElement("div");
    printDiv.innerHTML = printSection.innerHTML;

    var printWindow = window.open("", "_blank", "width=800,height=600");
    printWindow.document.open();
    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
            <head>
                <title>In phiếu</title>
                ${document.head.outerHTML}
                <style>
                    @page {
                        size: A5;
                        margin: 0;
                    }
                    body {
                        margin: 0;
                        padding: 10px;
                    }
                    #print-section {
                        width: calc(100% - 20px);
                    }
                    table {
                        width: 100%;
                        border-collapse: collapse;
                    }
                    td {
                        padding: 5px;
                        border: 1px solid #ddd;
                    }
                    .bold {
                        font-weight: bold;
                    }
                    img {
                        max-width: 100%;
                        height: auto;
                    }
                </style>
            </head>
            <body>
                ${printDiv.innerHTML}
            </body>
        </html>
    `);
    printWindow.document.close();

    setTimeout(function() {
        printWindow.print();
        printWindow.onafterprint = function () {
            printWindow.close();
        };
    }, 1000);
}
</script>
</body>
</html>