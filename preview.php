<?php
// This file is used to preview the content of a Notion page on your Wordpress site.

// Load WordPress environment
require_once dirname(__FILE__) . '/../../../wp-load.php';

global $wpdb;


if ( !isset( $_GET['_wpnonce'] ) || !wp_verify_nonce( sanitize_text_field(wp_unslash( $_GET['_wpnonce'] )), 'notion_content_preview_nonce' ) ) {
    wp_die(esc_html(__('Invalid nonce.', 'notion-content')));
}



// Check if 'id' is passed as a GET parameter
$page_id = isset($_GET['id']) ? sanitize_text_field(wp_unslash($_GET['id'])) : null;

if (!$page_id) {
    wp_die(esc_html(__('Invalid page ID.', 'notion-content')));
}

// Fetch the page content from the database
$table_name = $wpdb->prefix . 'notion_content';
$page_results = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE page_id = %s AND is_active = %d", $page_id, 1));

if (!$page_results) {
    wp_die(esc_html(__('Page not found or inactive.', 'notion-content')));
}

// Output the page content with the header and footer
get_header();

if (!empty($page_results->content)) {
    echo '<div class="notion-content-preview">';
    echo wp_kses_post($page_results->content); // Safely output the content
    echo '</div>';
} else {
    echo '<p>' . esc_html(__('No content available for preview.', 'notion-content')) . '</p>';
}

get_footer();



