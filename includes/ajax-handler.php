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
        'display'  => __('Every 15 Minutes')
    );
    $schedules['30_minutes'] = array(
        'interval' => 1800, // 30 minutes
        'display'  => __('Every 30 Minutes')
    );
    $schedules['1_hour'] = array(
        'interval' => 3600, // 1 hour
        'display'  => __('Every Hour')
    );
    $schedules['6_hours'] = array(
        'interval' => 21600, // 6 hours
        'display'  => __('Every 6 Hours')
    );
    $schedules['12_hours'] = array(
        'interval' => 43200, // 12 hours
        'display'  => __('Every 12 Hours')
    );
    $schedules['once_a_day'] = array(
        'interval' => 86400, // 1 day
        'display'  => __('Once a Day')
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
    $page_id = sanitize_text_field($_POST['page_id']);
    $interval = sanitize_text_field($_POST['interval']);

    global $wpdb;
    $table_name = $wpdb->prefix . 'notion_content';

    // Update the database with the new interval
    $updated = $wpdb->update(
        $table_name,
        array('cron_interval' => $interval),
        array('page_id' => $page_id),
        array('%s'),
        array('%s')
    );

    if ($updated === false) {
        wp_send_json_error(array('message' => 'Failed to update database.'));
    }

    // Schedule or clear the cron job for this page
    $hook = 'notion_hook_' . $interval;

    wp_clear_scheduled_hook('notion_cron_update', array($page_id));
    if ($interval !== 'manual') {
        //wp_schedule_event(time(), $interval, $hook, array($page_id));
        wp_schedule_event(time(), $interval, 'notion_cron_update', array($page_id));
    }

    wp_send_json_success();
}
add_action('wp_ajax_notion_set_cron_interval', 'notion_set_cron_interval');
