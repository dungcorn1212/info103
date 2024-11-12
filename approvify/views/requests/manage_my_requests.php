<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="_buttons tw-mb-2 sm:tw-mb-4">
                    <a href="<?php echo admin_url('approvify/manage_requests'); ?>"
                       class="btn btn-primary mright5 pull-left display-block">
                        <i class="fa-regular fa-plus tw-mr-1"></i>
                        <?php echo _l('approvify_request'); ?>
                    </a>
                    <div class="row">
                        <div class="col-sm-4 col-xs-12 pull-right leads-search">
                                <div>
                                    <?php echo render_input('search', '', '', 'search', ['data-name' => 'search', 'onkeyup' => 'myRequestsKanBan();', 'placeholder' => _l('approvify_search_based_on_request_title')], [], 'no-margin') ?>
                                </div>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                </div>
                <div class="<?php echo $isKanBan ? '' : 'panel_s' ; ?>">
                    <div class="<?php echo $isKanBan ? '' : 'panel-body' ; ?>">
                        <div class="tab-content">
                            <?php
                            if ($isKanBan) { ?>
                                <div class="active kan-ban-tab" id="kan-ban-tab" style="overflow:auto;">
                                    <div class="row">
                                        <div class="container-fluid leads-kan-ban">
                                            <div id="kan-ban"></div>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
    "use strict";

    $(function() {
        myRequestsKanBan();
    });

    function myRequestsKanBan(search) {
        init_kanban(
            "approvify/kanban",
            '',
            ".leads-status",
            290,
            360,
            ''
        );
    }

function deleteRequest(requestId) {
    if (confirm("<?php echo _l('approvify_confirm_delete_request'); ?>")) {
        $.ajax({
            url: admin_url + 'approvify/delete_request/' + requestId,
            type: 'POST',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert_float('success', response.message);
                    $('li[data-lead-id="' + requestId + '"]').remove();
                    // Cập nhật số lượng đề xuất trong cột
                    var statusId = 3; // ID của trạng thái "Đã hủy bỏ"
                    var $column = $('ul[data-col-status-id="' + statusId + '"]');
                    var currentTotal = parseInt($column.attr('data-total')) || 0;
                    $column.attr('data-total', currentTotal - 1);
                    $column.find('.panel-heading span.tw-ml-1').text('(' + (currentTotal - 1) + ')');
                } else {
                    alert_float('warning', response.message);
                }
            },
            error: function() {
                alert_float('danger', "<?php echo _l('approvify_error_deleting_request'); ?>");
            }
        });
    }
}



</script>
</body>

</html>