<?php

// Register the Notion Page shortcode
add_shortcode('notion_page', 'notion_page_shortcode');

function notion_page_shortcode($atts) {
    $atts = shortcode_atts(['page_id' => ''], $atts, 'notion_page');
    $page_id = sanitize_text_field($atts['page_id']);

    global $wpdb;
    $table_name = $wpdb->prefix . 'notion_content';

    $page = $wpdb->get_row($wpdb->prepare("SELECT content FROM $table_name WHERE page_id = %s AND is_active = 1", $page_id), ARRAY_A);

    if ($page) {
        return '<div class="notion-page-content">' . $page['content'] . '</div>';
    } else {
        return '<p>Content not found or inactive.</p>';
    }
}
?>
