<?php
// This file is used to handle AJAX requests for the Notion Content plugin.

// Ensure direct access to this file is restricted
if (!defined('ABSPATH')) {
    exit;
}

// This function is used to add custom cron schedules to the Wordpress cron system.
function notion_custom_cron_schedules($schedules) {
    $schedules['15_minutes'] = array(
        'interval' => 900, // 15 minutes in seconds
        'display'  => __('Every 15 Minutes', 'notion-content')
    );
    $schedules['30_minutes'] = array(
        'interval' => 1800, // 30 minutes
        'display'  => __('Every 30 Minutes', 'notion-content')
    );
    $schedules['1_hour'] = array(
        'interval' => 3600, // 1 hour
        'display'  => __('Every Hour', 'notion-content')
    );
    $schedules['6_hours'] = array(
        'interval' => 21600, // 6 hours
        'display'  => __('Every 6 Hours', 'notion-content')
    );
    $schedules['12_hours'] = array(
        'interval' => 43200, // 12 hours
        'display'  => __('Every 12 Hours', 'notion-content')
    );
    $schedules['once_a_day'] = array(
        'interval' => 86400, // 1 day
        'display'  => __('Once a Day', 'notion-content')
    );
    return $schedules;
}
add_filter('cron_schedules', 'notion_custom_cron_schedules');

function notion_cron_update_func($page_id = 0)  {
    notion_content_refresh_single_page($page_id);
}
add_action('notion_cron_update', 'notion_cron_update_func');

// This function is used to set the cron interval for a page.
// It is called via AJAX when the user changes the cron interval for a page.
function notion_set_cron_interval() {
    check_ajax_referer('notion_cron_nonce', 'nonce');
    if(isset($_POST['page_id'])) {
        $page_id = sanitize_text_field(wp_unslash($_POST['page_id']));
    }
    if(isset($_POST['interval'])) {
        $interval = sanitize_text_field(wp_unslash($_POST['interval']));
    }

    // Update the post meta with the new interval
    $updated = update_post_meta($page_id, 'notion_cron_interval', $interval);

    if ($updated === false) {
        wp_send_json_error(array('message' => 'Failed to update cron interval.'));
    }

    // Schedule or clear the cron job for this page
    wp_clear_scheduled_hook('notion_cron_update', array($page_id));
    if ($interval !== 'manual') {
        wp_schedule_event(time(), $interval, 'notion_cron_update', array($page_id));
    }

    wp_send_json_success();
}
add_action('wp_ajax_notion_set_cron_interval', 'notion_set_cron_interval');
