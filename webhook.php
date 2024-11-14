<?php
// Load WordPress environment
require_once(dirname(__FILE__) . '/../../../wp-load.php');

// Get the webhook_id from the URL
$webhook_id = isset($_GET['webhook_id']) ? sanitize_text_field($_GET['webhook_id']) : '';

// If no webhook_id is provided, exit
if (empty($webhook_id)) {
    wp_die('No webhook_id provided.');
}

// Query the database for the matching webhook_id
global $wpdb;
$table_name = $wpdb->prefix . 'notion_content';
$page_entry = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE webhook_id = %s", $webhook_id));

// If no matching entry is found, exit
if (!$page_entry) {
    wp_die('Invalid webhook_id.');
}


notion_content_refresh_single_page($page_entry->page_id);


