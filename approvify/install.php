<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI =& get_instance();

// Create 'approval_categories' table
if (!$CI->db->table_exists(db_prefix() . 'approvify_approval_categories')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "approvify_approval_categories` (
        `id` int(11) AUTO_INCREMENT,
        `category_name` varchar(255),
        `category_description` text,
        `category_icon` varchar(255),
        `approve_list` text,
        `is_active` tinyint(1) DEFAULT '1',
        `minimum_approval` int(11),
        `created_at` datetime,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

// Create 'requests' table
if (!$CI->db->table_exists(db_prefix() . 'approvify_requests')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "approvify_requests` (
        `id` int(11) AUTO_INCREMENT,
        `requester_id` int(11),
        `category_id` int(11),
        `request_title` varchar(255),
        `request_content` text,
        `status` enum('0','1','2','3') DEFAULT '0',
        `created_at` datetime,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

// Create 'request_approvals' table
if (!$CI->db->table_exists(db_prefix() . 'approvify_request_approvals')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "approvify_request_approvals` (
        `id` int(11) AUTO_INCREMENT,
        `request_id` int(11),
        `approved_by` int(11),
        `approved_at` datetime,
        `approver_note` text,
        `created_at` datetime,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

// Create 'request_files' table
if (!$CI->db->table_exists(db_prefix() . 'approvify_request_files')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "approvify_request_files` (
        `id` int(11) AUTO_INCREMENT,
        `request_id` int(11),
        `filename` varchar(255),
        `filepath` text,
        `created_at` datetime,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

// Create 'request_activity' table
if (!$CI->db->table_exists(db_prefix() . 'approvify_request_activity')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "approvify_request_activity` (
        `id` int(11) AUTO_INCREMENT,
        `request_id` int(11),
        `staff_id` int(11),
        `description` varchar(255),
        `created_at` datetime,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

// Modify 'id' columns to AUTO_INCREMENT

$CI->db->query('ALTER TABLE `' . db_prefix() . 'approvify_approval_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1');

$CI->db->query('ALTER TABLE `' . db_prefix() . 'approvify_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1');

$CI->db->query('ALTER TABLE `' . db_prefix() . 'approvify_request_approvals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1');

$CI->db->query('ALTER TABLE `' . db_prefix() . 'approvify_request_files`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1');

$CI->db->query('ALTER TABLE `' . db_prefix() . 'approvify_request_activity`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1');