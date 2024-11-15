<?php
// Load WordPress environment
require_once(dirname(__FILE__) . '/../../../wp-load.php');

// Get the webhook_id from the URL
$id = isset($_GET['id']) ? sanitize_text_field($_GET['id']) : '';

// If no webhook_id is provided, exit
if (empty($id)) {
    wp_die('No id provided.');
}

// Query the database for the matching webhook_id
global $wpdb;
$table_name = $wpdb->prefix . 'notion_content';
$page_entry = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE page_id= %s", $id));

// If no matching entry is found, exit
if (!$page_entry) {
    wp_die('Invalid id.');
}


notion_content_refresh_single_page($page_entry->page_id);


