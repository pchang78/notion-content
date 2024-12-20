<?php
// Load WordPress environment
require_once(dirname(__FILE__) . '/../../../wp-load.php');

// Get the webhook_id from the URL
$id = isset($_GET['id']) ? sanitize_text_field(wp_unslash($_GET['id'])) : '';

// If no webhook_id is provided, exit
if (empty($id)) {
    wp_die('No id provided.');
}

// Query for posts with matching notion_page_id
$args = array(
    'post_type' => 'notion_content',
    'meta_query' => array(
        array(
            'key' => 'notion_page_id',
            'value' => $id,
            'compare' => '='
        )
    ),
    'posts_per_page' => 1
);

$query = new WP_Query($args);

// If no matching post is found, exit
if (!$query->have_posts()) {
    wp_die('Invalid id.');
}

notion_content_refresh_single_page($id);


