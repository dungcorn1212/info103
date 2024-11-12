<?php defined('BASEPATH') or exit('No direct script access allowed');
$is_admin = is_admin();
$i = 0;
$statuses = [
    [
        'id' => 0,
        'color' => 'grey',
        'statusorder' => 0,
        'name' => 'Đã gửi',
        'isdefault' => 1
    ],
    [
        'id' => 1,
        'color' => 'darkgreen',
        'statusorder' => 1,
        'name' => 'Đã phê duyệt',
        'isdefault' => 1
    ],
    [
        'id' => 2,
        'color' => 'darkred',
        'statusorder' => 2,
        'name' => 'Bị từ chối',
        'isdefault' => 1
    ],
    [
        'id' => 3,
        'color' => 'orange',
        'statusorder' => 3,
        'name' => 'Đã hủy bỏ',
        'isdefault' => 1
    ],
];

foreach ($statuses as $status) {
    $kanBan = new ApprovifyRequestsKanBan($status['id']);
    $kanBan->search($this->input->get('search'))
        ->sortBy($this->input->get('sort_by'), $this->input->get('sort'));
    if ($this->input->get('refresh')) {
        $kanBan->refresh($this->input->get('refresh')[$status['id']] ?? null);
    }
    $leads = $kanBan->get();

    $total_leads = count($leads);
    $total_pages = $kanBan->totalPages();

    $settings = '';
    foreach (get_system_favourite_colors() as $color) {
        $color_selected_class = 'cpicker-small';
        if ($color == $status['color']) {
            $color_selected_class = 'cpicker-big';
        }
        $settings .= "<div class='kanban-cpicker cpicker " . $color_selected_class . "' data-color='" . $color . "' style='background:" . $color . ';border:1px solid ' . $color . "'></div>";
    } ?>
    <ul class="kan-ban-col" data-col-status-id="<?php echo $status['id']; ?>"
        data-total-pages="<?php echo $total_pages; ?>"
        data-total="<?php echo $total_leads; ?>">
        <li class="kan-ban-col-wrapper">
            <div class="border-right panel_s">
                <?php
                $status_color = '';
                if (!empty($status['color'])) {
                    $status_color = 'style="background:' . $status['color'] . ';border:1px solid ' . $status['color'] . '"';
                } ?>
                <div class="panel-heading tw-bg-neutral-700 tw-text-white"
                     <?php if ($status['isdefault'] == 1) { ?>data-toggle="tooltip"
                      <?php } ?>
                    <?php echo $status_color; ?> data-status-id="<?php echo $status['id']; ?>">
                    <i class="fa fa-reorder pointer"></i>
                    <span class="heading pointer tw-ml-1" <?php if ($is_admin) { ?>
                        data-order="<?php echo $status['statusorder']; ?>" data-color="<?php echo $status['color']; ?>"
                        data-name="<?php echo $status['name']; ?>"
                        onclick="edit_status(this,<?php echo $status['id']; ?>); return false;"
                    <?php } ?>><?php echo $status['name']; ?>
                </span> -
                    <a href="#" onclick="return false;"
                       class="pull-right color-white kanban-color-picker kanban-stage-color-picker<?php if ($status['isdefault'] == 1) {
                           echo ' kanban-stage-color-picker-last';
                       } ?>" data-placement="bottom" data-toggle="popover" data-content="
            <?php if (is_admin()) { ?>
            <hr />
            <div class='kan-ban-settings cpicker-wrapper'>
              <?php echo $settings; ?>
            </div><?php } ?>" data-html="true" data-trigger="focus">
                        <i class="fa fa-angle-down"></i>
                    </a>
                </div>
                <div class="kan-ban-content-wrapper">
                    <div class="kan-ban-content">
                        <ul class="status leads-status sortable" data-lead-status-id="<?php echo $status['id']; ?>">
                            <?php
                            foreach ($leads as $lead) {
                                $this->load->view('requests/manage_my_requests_kanban_card', ['lead' => $lead, 'status' => $status]);
                            } ?>
                            <?php if ($total_leads > 0) { ?>
                                <li class="text-center not-sortable kanban-load-more"
                                    data-load-status="<?php echo $status['id']; ?>">
                                    <a href="#"
                                       class="btn btn-default btn-block<?php if ($total_pages <= 1 || $kanBan->getPage() === $total_pages) {
                                           echo ' disabled';
                                       } ?>" data-page="<?php echo $kanBan->getPage(); ?>"
                                       onclick="kanban_load_more(<?php echo $status['id']; ?>, this, 'approvify/my_requests_kanban_load_more', 315, 360); return false;"
                                       ;>
                                        <?php echo _l('load_more'); ?>
                                    </a>
                                </li>
                            <?php } ?>
                            <li class="text-center not-sortable mtop30 kanban-empty<?php if ($total_leads > 0) {
                                echo ' hide';
                            } ?>">
                                <h4>
                                    <i class="fa-solid fa-circle-notch" aria-hidden="true"></i><br/><br/>
                                    <?php echo _l('approvify_no_request_found'); ?>
                                </h4>
                            </li>
                        </ul>
                    </div>
                </div>
        </li>
    </ul>
    <?php $i++;
} ?>
