<?php
// This file is used to preview the content of a Notion page on your Wordpress site.

// Load WordPress environment
require_once dirname(__FILE__) . '/../../../wp-load.php';

if (!isset($_GET['_wpnonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'notion_content_preview_nonce')) {
    wp_die(esc_html(__('Invalid nonce.', 'notion-content')));
}

// Check if 'id' is passed as a GET parameter
$page_id = isset($_GET['id']) ? sanitize_text_field(wp_unslash($_GET['id'])) : null;

if (!$page_id) {
    wp_die(esc_html(__('Invalid page ID.', 'notion-content')));
}

// Query posts with matching notion_page_id
$args = array(
    'post_type' => 'notion_content',
    'meta_query' => array(
        array(
            'key' => 'notion_page_id',
            'value' => $page_id,
            'compare' => '='
        )
    ),
    'posts_per_page' => 1
);

$query = new WP_Query($args);

if (!$query->have_posts()) {
    wp_die(esc_html(__('Page not found or inactive.', 'notion-content')));
}

// Output the page content with the header and footer
get_header();

while ($query->have_posts()) {
    $query->the_post();
    echo '<div class="notion-content-preview">';
    the_content();
    echo '</div>';
}

wp_reset_postdata();

get_footer();



