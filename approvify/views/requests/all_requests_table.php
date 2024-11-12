<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    db_prefix() . 'approvify_requests.id as id',
    'requester_id',
    'category_id',
    'request_title',
    'status',
    db_prefix() . 'approvify_requests.created_at as request_created',
    db_prefix() . 'approvify_approval_categories.category_name as category_name',
    db_prefix() . 'approvify_approval_categories.approve_list as approve_list',
    db_prefix() . 'approvify_approval_categories.category_icon as category_icon',
    '(SELECT staff_id FROM ' . db_prefix() . 'approvify_request_activity WHERE request_id = ' . db_prefix() . 'approvify_requests.id ORDER BY id DESC LIMIT 1) as last_reviewer_id',
    '(SELECT COUNT(*) FROM ' . db_prefix() . 'approvify_request_activity WHERE request_id = ' . db_prefix() . 'approvify_requests.id AND description LIKE "%Đã phê duyệt%") as approval_count'
];

$sIndexColumn = 'id';
$sTable = db_prefix() . 'approvify_requests';

$join = [
    'LEFT JOIN ' . db_prefix() . 'approvify_approval_categories ON ' . db_prefix() . 'approvify_approval_categories.id = ' . db_prefix() . 'approvify_requests.category_id',
];

$where = [];

// Thêm điều kiện để loại bỏ các đề xuất có trạng thái Đã hủy
array_push($where, "AND " . db_prefix() . "approvify_requests.status != 4");
// array_push($where, "AND FIND_IN_SET('" . get_staff_user_id() . "', REPLACE(REPLACE(REPLACE(" . db_prefix() . "approvify_approval_categories.approve_list, '[',''), ']', ''), '\"', ''))");

if (isset($postData['approvify_next_reviewer']) && $postData['approvify_next_reviewer']) {
    $nextReviewers = $postData['approvify_next_reviewer'];
    $nextReviewers = array_filter($nextReviewers);
    $nextReviewers = array_map(function ($reviewer) {
        return "'" . $reviewer . "'";
    }, $nextReviewers);
    
    $whereClause = 'AND JSON_UNQUOTE(JSON_EXTRACT(
        ' . db_prefix() . 'approvify_approval_categories.approve_list,
        CONCAT("$[", (
            SELECT COUNT(*)
            FROM ' . db_prefix() . 'approvify_request_activity
            WHERE request_id = ' . db_prefix() . 'approvify_requests.id
            AND description LIKE "%Đã phê duyệt%"
        ), "]")
    )) IN (' . implode(',', $nextReviewers) . ')';
    
    $whereClause .= ' AND ' . db_prefix() . 'approvify_requests.status != 0';
    
    array_push($where, $whereClause);
}

if (isset($postData['approvify_request_status']) && $postData['approvify_request_status']) {
    $statuses = $postData['approvify_request_status'];
    $statuses = array_filter($statuses);
    $statuses = array_map(function ($status) {
        if ($status === 'sub') {
            $status = 0;
        }
        return "'" . $status . "'";
    }, $statuses);
    array_push($where, 'AND ' . db_prefix() . 'approvify_requests.status IN (' . implode(',', $statuses) . ')');
}

if (isset($postData['approvify_request_staff']) && $postData['approvify_request_staff']) {
    $statuses = $postData['approvify_request_staff'];
    $statuses = array_filter($statuses);
    $statuses = array_map(function ($status) {
        if ($status === 'sub') {
            $status = 0;
        }
        return "'" . $status . "'";
    }, $statuses);
    array_push($where, 'AND ' . db_prefix() . 'approvify_requests.requester_id IN (' . implode(',', $statuses) . ')');
}

if (isset($postData['approvify_approval_categories']) && $postData['approvify_approval_categories']) {
    $categories = $postData['approvify_approval_categories'];
    $categories = array_filter($categories);
    $categories = array_map(function ($category) {
        return "'" . $category . "'";
    }, $categories);
    array_push($where, 'AND ' . db_prefix() . 'approvify_requests.category_id IN (' . implode(',', $categories) . ')');
}

if (isset($postData['approvify_last_reviewer']) && $postData['approvify_last_reviewer']) {
    $reviewers = $postData['approvify_last_reviewer'];
    $reviewers = array_filter($reviewers);
    $reviewers = array_map(function ($reviewer) {
        return "'" . $reviewer . "'";
    }, $reviewers);
    array_push($where, 'AND (SELECT staff_id FROM ' . db_prefix() . 'approvify_request_activity WHERE request_id = ' . db_prefix() . 'approvify_requests.id ORDER BY id DESC LIMIT 1) IN (' . implode(',', $reviewers) . ')');
}

// Add date range filter
if (isset($postData['approvify_date_from']) && $postData['approvify_date_from']) {
    array_push($where, 'AND ' . db_prefix() . 'approvify_requests.created_at >= "' . to_sql_date($postData['approvify_date_from']) . '"');
}

if (isset($postData['approvify_date_to']) && $postData['approvify_date_to']) {
    array_push($where, 'AND ' . db_prefix() . 'approvify_requests.created_at <= "' . to_sql_date($postData['approvify_date_to']) . ' 23:59:59"');
}

// Thêm sắp xếp vào truy vấn
$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
    db_prefix() . 'approvify_requests.id'
], null, null, 'ORDER BY ' . db_prefix() . 'approvify_requests.id DESC');


$output = $result['output'];
$rResult = $result['rResult'];



$output['aaData'] = [];

foreach ($rResult as $aRow) {
    $row = [];
    for ($i = 0; $i < count($aColumns); $i++) {

        $row[] = $aRow['id'];
        $row[] = $aRow['request_created'];
        $request_title = '<div class="tw-flex tw-items-center tw-space-x-3">';
        $request_title .= '<a href="' . admin_url('approvify/view_request/' . $aRow['id'] . '/?review=true') . '" >';
        $request_title .= $aRow['request_title'];
        $request_title .= '</a>';
        $request_title .= '</div>';
        $row[] = $request_title;

        $options = '<div class="tw-flex tw-items-center tw-space-x-3">';
        $options .= '<a href="' . admin_url('approvify/view_request/' . $aRow['id'] . '/?review=true') . '" class="tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700">
        <i class="fa-regular fa-eye fa-lg"></i>
    </a>';

        $options .= '</div>';

        $row[] = $options;
        // $row[] = '<i class="' . $aRow['category_icon'] . '"></i>  ' . $aRow['category_name'];
        // $row[] = get_staff_full_name($aRow['requester_id']);
        $row[] = '<div>' . '<i class="fas fa-user"></i> ' . get_staff_full_name($aRow['requester_id']) . '<br> <i class="' . $aRow['category_icon'] . '"></i> ' . $aRow['category_name'] . '</div>';
        

        $last_reviewer_name = '';
        if (!empty($aRow['last_reviewer_id'])) {
            $last_reviewer_name = get_staff_full_name($aRow['last_reviewer_id']);
            // $last_reviewer_name = '<a href="' . admin_url('staff/profile/' . $aRow['last_reviewer_id']) . '">' . $last_reviewer_name . '</a>';
        }
        $row[] = $last_reviewer_name;

        // $row[] = approvify_return_request_status_html($aRow['status']);

        // Thêm logic để xác định người phê duyệt tiếp theo
        $approveList = json_decode($aRow['approve_list'], true);
        $approvalCount = intval($aRow['approval_count']);
        $status = intval($aRow['status']);

        switch ($status) {
            case 0: // Chờ phê duyệt
                if ($approvalCount >= count($approveList)) {
                    $nextApprover = '<span class="pending-status label project-status-5">Chờ phê duyệt</span>';
                } else {
                    $nextApproverId = $approveList[$approvalCount];
                    $nextApprover = '<span class="pending-status label project-status-5">Chờ phê duyệt</span>' . '<br> Đang chờ: ' . '<a href="' . admin_url('staff/profile/' . $nextApproverId) . '">' . get_staff_full_name($nextApproverId) . '</a>';
                }
                break;
            case 1: // Đã phê duyệt
                $nextApprover = '<span class="success-status label project-status-5">Đã phê duyệt</span>';
                break;
            case 2: // Đã Từ chối
                $nextApprover = '<span class="rejected-status label project-status-5">Từ chối</span>';
                break;
            case 3: // Đã hủy
                $nextApprover = '<span class="cancel-status label project-status-5">Đã hủy</span>';
                break;
            default:
                $nextApprover = 'Không xác định';
        }

        $row[] = $nextApprover;

        $approveList = '';
        if (!empty($aRow['approve_list'])) {
            $decodeApproveList = json_decode($aRow['approve_list']);
            foreach ($decodeApproveList as $staff) {
                $approveList .= '<a href="' . admin_url('staff/profile/' . $staff) . '" 
            data-placement="top" data-toggle="tooltip" title="' . get_staff_full_name($staff, ['staff_id']) . '">' .
                    staff_profile_image($staff, ['staff-profile-image-small']) . '</a>';
            }
        }

        $row[] = $approveList;
    }

    $output['aaData'][] = $row;
}
