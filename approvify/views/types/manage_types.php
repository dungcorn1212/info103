<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <?php if (has_permission('approvify', '', 'create_category')) { ?>
                    <div class="tw-mb-2 sm:tw-mb-4">
                        <a href="<?php echo admin_url('approvify/approvify/create_type'); ?>" class="btn btn-primary">
                            <i class="fa-regular fa-plus tw-mr-1"></i>
                            <?php echo _l('approvify_create_type'); ?>
                        </a>
                    </div>
                <?php } ?>
                <div class="panel_s">
                    <div class="panel-body panel-table-full">
                        <?php render_datatable([
                            _l('id'),
                            _l('approvify_table_type_name'),
                            _l('approvify_table_type_description'),
                            _l('approvify_table_type_icon'),
                            _l('approvify_table_approve_list'),
                            _l('approvify_table_is_active'),
                            _l('approvify_table_created_at'),
                            _l('options'),
                        ], 'manage-types'); ?>
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
        initDataTable('.table-manage-types', window.location.href, [0], [0], [], [0, 'desc']);
    });

</script>
</body>

</html>