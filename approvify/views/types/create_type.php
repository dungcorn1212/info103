<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <?php
            if (isset($category_data)) {
                $requestUrl = 'approvify/create_type/' . $category_data->id;
            } else {
                $requestUrl = 'approvify/create_type';
            }
            echo form_open(admin_url($requestUrl));
            ?>
            <div class="col-md-12">
                <h4 class="tw-mt-0 tw-font-semibold tw-text-lg tw-text-neutral-700">
                    <?php echo $title; ?>
                </h4>
                <div class="panel_s">
                    <div class="panel-body">

                        <div class="col-md-4">
                            <?php echo render_input('category_name', 'approvify_category_name', $category_data->category_name ?? ''); ?>
                        </div>

                        <div class="col-md-4">
                            <?php echo render_input('category_description', 'approvify_category_description', $category_data->category_description ?? ''); ?>
                        </div>

                        <div class="col-md-4">
                            <?php echo render_input('category_icon', 'approvify_category_icon', $category_data->category_icon ?? ''); ?>
                        </div>

                        <div class="col-md-12">

                            <?php
                            $selectedStaff = [];
                            if (isset($category_data)) {
                                $selectedStaff = json_decode($category_data->approve_list, true);
                                if (json_last_error() !== JSON_ERROR_NONE) {
                                    error_log('JSON decode error: ' . json_last_error_msg());
                                    $selectedStaff = [];
                                }
                            }

                            usort($staff_list, function($a, $b) use ($selectedStaff) {
                                $aIndex = array_search($a['staffid'], $selectedStaff);
                                $bIndex = array_search($b['staffid'], $selectedStaff);

                                // If both are found, sort by their index
                                if ($aIndex !== false && $bIndex !== false) {
                                    return $aIndex <=> $bIndex;
                                }

                                // If only one is found, it should come first
                                if ($aIndex !== false) return -1;
                                if ($bIndex !== false) return 1;

                                // If neither are found, sort by name
                                return strcmp($a['firstname'] . ' ' . $a['lastname'], $b['firstname'] . ' ' . $b['lastname']);
                            });

                            $staff_list_json = json_encode($staff_list);
                            echo render_select('approve_list[]', $staff_list, ['staffid', ['firstname', 'lastname']], 'approvify_approvers', $selectedStaff, ['multiple' => true], [], '', '', false);
                            ?>
                            <script>
                            document.addEventListener('DOMContentLoaded', function () {
                                const selectElement = document.querySelector('select[name="approve_list[]"]');

                                function sortOptions() {
                                    const selectedValues = Array.from(selectElement.selectedOptions).map(option => option.value);
                                    const allOptions = Array.from(selectElement.options);
                                    const sortedOptions = [];

                                    selectedValues.forEach(value => {
                                        const option = allOptions.find(opt => opt.value === value);
                                        if (option) {
                                            sortedOptions.push(option);
                                        }
                                    });

                                    const unselectedOptions = allOptions.filter(opt => !selectedValues.includes(opt.value));
                                    const finalOptions = sortedOptions.concat(unselectedOptions);

                                    selectElement.innerHTML = '';
                                    finalOptions.forEach(option => selectElement.appendChild(option));
                                }

                                if (selectElement) {
                                    sortOptions();

                                    selectElement.addEventListener('change', function () {
                                        sortOptions();
                                    });
                                }
                            });
                            </script>

                        </div>

                        <div class="btn-bottom-toolbar text-right">
                            <button type="submit" class="btn btn-primary"><?php echo _l('save'); ?></button>
                        </div>
                    </div>
                </div>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>
<?php init_tail(); ?>

