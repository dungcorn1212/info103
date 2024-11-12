<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    'id',
    'category_name',
    'category_description',
    'category_icon',
    'approve_list',
    'is_active',
    'created_at'
];

$sIndexColumn = 'id';
$sTable = db_prefix() . 'approvify_approval_categories';

$join = [];
$where = [];

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
    'id'
]);

$output = $result['output'];
$rResult = $result['rResult'];


foreach ($rResult as $aRow) {
    $row = [];
    for ($i = 0; $i < count($aColumns); $i++) {

        $row[] = $aRow['id'];
        $row[] = $aRow['category_name'];
        $row[] = $aRow['category_description'];
        $row[] = '<i class="'.$aRow['category_icon'].'"></i>';

        $approveList = '';
        if (!empty($aRow['approve_list'])) {
            $decodeApproveList = json_decode($aRow['approve_list']);

            foreach ($decodeApproveList as $staff) {
                $approveList .= '<a href="' . admin_url('staff/profile/' . $staff) . '">' . staff_profile_image($staff, [
                        'staff-profile-image-small',
                    ]) . '</a>';
            }
        }

        $row[] = $approveList;

        $checked = '';
        if ($aRow['is_active'] == 1) {
            $checked = 'checked';
        }
        $row[]= '<div class="onoffswitch">
                <input type="checkbox" data-switch-url="' . admin_url() . 'approvify/update_type_status" name="onoffswitch" class="onoffswitch-checkbox" id="c_' . $aRow['id'] . '" data-id="' . $aRow['id'] . '" ' . $checked . '>
                <label class="onoffswitch-label" for="c_' . $aRow['id'] . '"></label>
            </div>';

        $row[] = $aRow['created_at'];

        $options = '<div class="tw-flex tw-items-center tw-space-x-3">';
        $options .= '<a href="' . admin_url('approvify/create_type/' . $aRow['id']) . '" class="tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700">
        <i class="fa-regular fa-pen-to-square fa-lg"></i>
    </a>';

        $options .= '<a href="' . admin_url('approvify/delete_type/' . $aRow['id']) . '"
    class="tw-mt-px tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700 _delete">
        <i class="fa-regular fa-trash-can fa-lg"></i>
    </a>';

        $options .= '</div>';

        $row[] = $options;
    }

    $output['aaData'][] = $row;
}
