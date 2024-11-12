<?php defined('BASEPATH') or exit('No direct script access allowed');
if ($lead['status'] == $status['id']) { ?>
    <li data-lead-id="<?php echo $lead['id']; ?>" class="lead-kan-ban not-sortable">
        <div class="panel-body lead-body">
            <div class="row">
                <div class="col-xs-12 lead-name">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <?php if (true) { ?>
                                <a href="<?php echo admin_url('profile/' . $lead['requester_id']); ?>" 
                                   data-placement="right"
                                   data-toggle="tooltip" 
                                   title="<?php echo get_staff_full_name($lead['requester_id']); ?>"
                                   class="mr-2">
                                   <!--  <?php echo staff_profile_image($lead['requester_id'], [
                                        'staff-profile-image-xs',
                                    ]); ?> -->
                                </a>
                            <?php  } ?>
                            <a href="<?php echo admin_url('approvify/view_request/' . $lead['id']); ?>" 
                               class="lead-link">
                                <span class="lead-name-span">
                                    #<?php echo $lead['id'] . ' - ' . $lead['request_title']; ?>
                                </span>
                            </a>
                        </div>
                        <div>
                            <?php 
                            $isApproved = false;
                            $activities = $this->approvify_model->getActivities($lead['id']);
                            foreach ($activities as $activity) {
                                if (strpos($activity['description'], 'Đã phê duyệt đề xuất của bạn') !== false) {
                                    $isApproved = true;
                                    break;
                                }
                            }
                            if ($lead['status'] == 0 && !$isApproved) { 
                            ?>
                                <a href="<?php echo admin_url('approvify/edit_request/' . $lead['id']); ?>" 
                                   class="btn btn-icon btn-sm btn-default" 
                                   data-toggle="tooltip" 
                                   title="<?php echo _l('edit'); ?>">
                                    <i class="fas fa-edit"></i>
                                </a>
                            <?php } ?>
                            <?php if ($lead['status'] == 3 && has_permission('approvify', '', 'delete')) { ?>
                                <a href="#" 
                                   onclick="deleteRequest(<?php echo $lead['id']; ?>); return false;" 
                                   class="btn btn-icon btn-sm btn-danger" 
                                   data-toggle="tooltip" 
                                   title="<?php echo _l('xoa_de_xuat'); ?>">
                                    <i class="fas fa-trash"></i>
                                </a>
                            <?php } ?>
                        </div>
                    </div>
                </div>
                <div class="col-xs-12 mt-2">
                    <div class="d-flex justify-content-between flex-wrap">
                        <div class="lead-info">
                            <small class="text-dark">
                                <?php echo _l('approvify_request_category'); ?>: 
                                <span class="bold text-primary">
                                    <?php echo $lead['category_name']; ?>
                                </span>
                            </small>
                        </div>
                        <div class="lead-created">
                            <small class="text-dark">
                                <?php echo _l('lead_created'); ?>: 
                                <span class="bold" data-toggle="tooltip"
                                      data-title="<?php echo _dt($lead['created_at']); ?>">
                                    <?php echo time_ago($lead['created_at']); ?>
                                </span>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </li>
<?php } ?>