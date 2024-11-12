<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <h3><?php echo _l('approvify_requests_review'); ?></h3>
                <div class="display-block">
                    <hr>
                    <button id="toggleFilters" class="btn btn-primary btn-block visible-xs visible-sm mb-4">
                        <?php echo _l('show_filters'); ?>
                    </button>
                    <div id="filterContainer" class="row hidden-xs hidden-sm">
                        <div class="col-md-3 col-sm-6 col-xs-12 mb-3">
                            <?php echo render_select('approvify_request_status', [
                                ['id' => 'sub', 'name' => _l('approvify_submitted_status')],
                                ['id' => '1', 'name' => _l('approvify_approved_status')],
                                ['id' => '2', 'name' => _l('approvify_refused_status')],
                                ['id' => '3', 'name' => _l('approvify_canceled_status')]
                            ], array('id', 'name'), 'approvify_request_status', '', array('multiple' => true, 'data-actions-box' => true), array(), '', '', false); ?>
                        </div>
                        <div class="col-md-3 col-sm-6 col-xs-12 mb-3">
                            <?php echo render_select('approvify_request_staff', $staff, array('staffid', array('firstname', 'lastname')), 'approvify_request_staff', '', array('multiple' => true, 'data-actions-box' => true), array(), '', '', false); ?>
                        </div>
                        <div class="col-md-3 col-sm-6 col-xs-12 mb-3">
                            <?php
                            $categories = $this->approvify_model->getTypes();
                            echo render_select('approvify_approval_categories', $categories, array('id', 'category_name'), 'approvify_request_category', '', array('multiple' => true, 'data-actions-box' => true), array(), '', '', false);
                            ?>
                        </div>
                        <div class="col-md-3 col-sm-6 col-xs-12 mb-3">
                            <?php echo render_select('approvify_last_reviewer', $staff, array('staffid', array('firstname', 'lastname')), 'approvify_last_reviewer', '', array('multiple' => true, 'data-actions-box' => true), array(), '', '', false); ?>
                        </div>
                        <div class="col-md-6 col-sm-12 col-xs-12 mb-3">
                            <div class="row">
                                <div class="col-md-4">
                                    <?php
                                    echo render_select('approvify_date_type', [
                                        ['id' => 'created', 'name' => _l('approvify_date_created')],
                                        // ['id' => 'updated', 'name' => _l('approvify_date_updated')]
                                    ], array('id', 'name'), 'approvify_date_type', 'created');
                                    ?>
                                </div>
                                <div class="col-md-4">
                                    <?php echo render_date_input('approvify_date_from', 'approvify_date_from'); ?>
                                </div>
                                <div class="col-md-4">
                                    <?php echo render_date_input('approvify_date_to', 'approvify_date_to'); ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 col-xs-12 mb-3">
                            <?php echo render_select('approvify_next_reviewer', $staff, array('staffid', array('firstname', 'lastname')), 'approvify_next_reviewer', '', array('multiple' => true, 'data-actions-box' => true), array(), '', '', false); ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body panel-table-full">
                        <?php
                        $table_data = [
                            _l('id'),
                            _l('approvify_table_created_at'),
                            _l('approvify_request_title'),
                            _l('options'),
                            _l('approvify_request_sender_category'),
                            _l('last_updater'),
                            _l('trang_thai_de_xuat'),
                            _l('approvify_request_reviewers'),
                        ];
                        render_datatable($table_data, 'manage-review-requests');
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
"use strict";

var fnServerParams;

$(function () {
    generate_table();

    fnServerParams = {
        "approvify_request_status": '[name="approvify_request_status"]',
        "approvify_request_staff": '[name="approvify_request_staff"]',
        "approvify_approval_categories": '[name="approvify_approval_categories"]',
        "approvify_last_reviewer": '[name="approvify_last_reviewer"]',
        "approvify_date_type": '[name="approvify_date_type"]',
        "approvify_date_from": '[name="approvify_date_from"]',
        "approvify_date_to": '[name="approvify_date_to"]',
        "approvify_next_reviewer": '[name="approvify_next_reviewer"]'
    }

    $('select[name="approvify_request_status"], select[name="approvify_request_staff"], select[name="approvify_approval_categories"], select[name="approvify_last_reviewer"], select[name="approvify_next_reviewer"], select[name="approvify_date_type"], input[name="approvify_date_from"], input[name="approvify_date_to"]').on('change', function () {
        generate_table();
    });

    // Toggle filters on mobile
    $('#toggleFilters').on('click', function() {
        $('#filterContainer').toggleClass('hidden-xs hidden-sm');
        $(this).text(function(i, text){
            return text === "<?php echo _l('show_filters'); ?>" ? "<?php echo _l('hide_filters'); ?>" : "<?php echo _l('show_filters'); ?>";
        });
    });
});

function generate_table() {
    const tableClass = $('.table-manage-review-requests');

    if ($.fn.DataTable.isDataTable(tableClass)) {
        tableClass.DataTable().destroy();
    }

    var table = initDataTable(tableClass, window.location.href, [0], [0], fnServerParams, [0, 'desc']);

    tableClass.on('mouseenter', 'td:nth-child(5)', function () {
        var $this = $(this);
        var $row = $this.closest('tr');
        var lastReviewer = $row.find('td:last').html();

        if (lastReviewer && $this.text().trim() === '<?php echo _l("approvify_submitted_status"); ?>') {
            $this.tooltip({
                title: "Người duyệt cuối: " + lastReviewer,
                container: 'body',
                html: true
            }).tooltip('show');
        }
    });

    tableClass.on('mouseleave', 'td', function () {
        $(this).tooltip('hide');
    });
}
</script>
</body>
</html>