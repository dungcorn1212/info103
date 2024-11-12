<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Phê duyệt đề xuất
Description: Chức năng tùy chỉnh riêng cho HYH
Author: Nguyễn Anh Đức
Author URI: https://anhduc.net
Version: 1.0.0
Requires at least: 1.0.*
*/

define('APPROVIFY_MODULE_NAME', 'approvify');

hooks()->add_action('admin_init', 'approvify_module_init_menu_items');
hooks()->add_action('admin_init', 'approvify_permissions');
hooks()->add_action('approvify_init', APPROVIFY_MODULE_NAME . '_appint');
hooks()->add_action('pre_activate_module', APPROVIFY_MODULE_NAME . '_preactivate');
hooks()->add_action('pre_deactivate_module', APPROVIFY_MODULE_NAME . '_predeactivate');
hooks()->add_action('pre_uninstall_module', APPROVIFY_MODULE_NAME . '_uninstall');


require(__DIR__ . '/services/ApprovifyRequestsKanBan.php');

/**
 * Load the module helper
 */
$CI = &get_instance();
$CI->load->helper(APPROVIFY_MODULE_NAME . '/approvify'); //on module main file

function approvify_permissions()
{
    $capabilities = [];

    $capabilities['capabilities'] = [
        'view' => _l('permission_view'),
        'create' => _l('permission_create'),
        'edit' => _l('permission_edit'),
        'delete' => _l('permission_delete'),
        'create_category' => _l('approvify_create_categories'),
    ];
    register_staff_capabilities('approvify', $capabilities, _l('approvify'));
}

/**
 * Register activation module hook
 */
register_activation_hook(APPROVIFY_MODULE_NAME, 'approvify_module_activation_hook');

function approvify_module_activation_hook()
{
    $CI = &get_instance();
    require_once(__DIR__ . '/install.php');
}

/**
 * Register language files, must be registered if the module is using languages
 */
register_language_files(APPROVIFY_MODULE_NAME, [APPROVIFY_MODULE_NAME]);

/**
 * Init module menu items in setup in admin_init hook
 * @return null
 */
function approvify_module_init_menu_items()
{
    $CI = &get_instance();

    if (has_permission('approvify', '', 'view')) {
        $CI->app_menu->add_sidebar_menu_item('approvify', [
            'slug' => 'approvify',
            'name' => _l('approvify_mod_name'),
            'position' => 6,
            'icon' => 'fas fa-check'
        ]);
    }

    if (has_permission('approvify', '', 'create')) {
        $CI->app_menu->add_sidebar_children_item('approvify', [
            'slug' => 'approvify-create-request',
            'name' => _l('approvify_request'),
            'position' => 6,
            'icon' => 'fas fa-plus-circle',
            'href' => admin_url('approvify/manage_requests')
        ]);
    }

    if (has_permission('approvify', '', 'create')) {
        $CI->app_menu->add_sidebar_children_item('approvify', [
            'slug' => 'approvify-my-request',
            'name' => _l('approvify_my_requests'),
            'position' => 6,
            'icon' => 'fas fa-tasks',
            'href' => admin_url('approvify/manage_created_requests')
        ]);
    }

    if (has_permission('approvify', '', 'view')) {
        $CI->app_menu->add_sidebar_children_item('approvify', [
            'slug' => 'approvify-review-request',
            'name' => _l('approvify_review_requests'),
            'position' => 6,
            'icon' => 'fas fa-exchange-alt',
            'href' => admin_url('approvify/manage_review_requests')
        ]);
    }

    if (has_permission('approvify', '', 'create_category')) {
        $CI->app_menu->add_sidebar_children_item('approvify', [
            'slug' => 'approvify-request-categories',
            'name' => _l('approvify_categories'),
            'position' => 6,
            'icon' => 'fas fa-layer-group',
            'href' => admin_url('approvify/manage_types')
        ]);
    }
}

function approvify_appint()
{
    $CI = &get_instance();
    require_once 'libraries/leclib.php';
    $module_api = new ApprovifyLic();

}

function approvify_preactivate($module_name)
{
    if ($module_name['system_name'] == APPROVIFY_MODULE_NAME) {
        require_once 'libraries/leclib.php';
        $module_api = new ApprovifyLic();

    }
}

function approvify_predeactivate($module_name)
{

}

function approvify_uninstall($module_name)
{
    if ($module_name['system_name'] == APPROVIFY_MODULE_NAME) {
        require_once 'libraries/leclib.php';
        $warehouse_api = new ApprovifyLic();
    }
}