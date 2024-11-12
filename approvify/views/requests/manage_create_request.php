<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<style>
    .template_card {
        font-family: "Lato", sans-serif;

        background-color: #fff;
        margin-bottom: 1.5rem;
        width: 100%;
        border: 1px solid #dbe2eb;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        border-radius: 8px;
        padding: 1rem 1.5rem;
    }

    .template_card .card-body {
        min-height: 155px;
    }

    .template_card .template-icon {
        font-size: 16px;
        padding: 7px;
        background: #E1F0FF;
        color: #007bff;
        border-radius: 5px;
    }

    .number-font {
        font-family: "Poppins", sans-serif;
        font-weight: 700;
    }

    .fs-13 {
        font-size: 13px;
    }

    .text-muted {
        color: #728096 !important;
    }
</style>
<div id="wrapper">
    <div class="content">

        <div class="row">
            <div class="col-md-12">

                <h4 class="tw-mt-0 tw-font-semibold tw-text-lg tw-text-neutral-700">
                    <?php echo $title; ?>
                </h4>
                <hr>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <?php
                    if (count($type_list) === 0) {
                        echo _l('approvify_no_types');
                    }
                    ?>
                    <?php
                    foreach ($type_list as $template) {
                        ?>
                        <div class="col-xs-12 col-sm-12 col-md-3 col-lg-3 col-xl-3 templates-item <?php echo $template['id']; ?>">
                            <div class="card template_card">
                                <div class="card-body">
                                    <i class="<?php echo $template['category_icon'] ?? 'fa-solid fa-file-text' ?> menu-icon template-icon"></i>
                                    <div class="template-title">
                                        <h4 class="mb-2 fs-15 number-font"><?php echo $template['category_name'] ?></h4>
                                    </div>
                                    <p class="card-text fs-13 text-muted mb-2"><?php echo $template['category_description'] ?></p>

                                    <?php
                                    if (has_permission('approvify', '', 'view')) {
                                        ?>
                                        <a href="<?php echo admin_url('approvify/create_request?type='.$template['id']); ?>"
                                           class="btn btn-primary"><i class="fa-regular fa-plus tw-mr-1"></i><?php echo _l('approvify_create_request'); ?></a>
                                        <?php
                                    }
                                    ?>

                                </div>
                            </div>
                        </div>
                        <?php
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
</body>
</html>