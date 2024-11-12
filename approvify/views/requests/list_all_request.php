<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin"><?php echo _l('approvify_list_all_requests'); ?></h4>
                        <hr class="hr-panel-heading" />
                        <?php render_datatable(array(
                            _l('id'),
                            _l('request_created'),
                            _l('request_title'),
                            _l('options'),
                            _l('requester_and_category'),
                            _l('last_reviewer'),
                            _l('status'),
                            _l('approve_list'),
                        ), 'all-requests'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
    $(function() {
        initDataTable('.table-all-requests', window.location.href + '/get_all_requests_table', undefined, undefined, undefined, [0, 'desc']);
    });
</script>