<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
// Register the Notion Page shortcode
add_shortcode('notion_page', 'notion_page_shortcode');

function notion_page_shortcode($atts) {
    $atts = shortcode_atts(['page_id' => ''], $atts, 'notion_page');
    $notion_page_id = sanitize_text_field($atts['page_id']);

    // Query for posts with matching notion_page_id
    $args = array(
        'post_type' => 'notion_content',
        'meta_query' => array(
            array(
                'key' => 'notion_page_id',
                'value' => $notion_page_id,
                'compare' => '='
            )
        ),
        'posts_per_page' => 1,
        'post_status' => 'publish'
    );

    $query = new WP_Query($args);

    if ($query->have_posts()) {
        $query->the_post();
        $content = get_the_content();
        wp_reset_postdata();

        $custom_css = get_option('content_importer_for_notion_custom_css', '');
        if($custom_css) {
            wp_register_style('notion-content-custom-styles', false);
            wp_enqueue_style('notion-content-custom-styles');
            wp_add_inline_style('notion-content-custom-styles', $custom_css);
        }

        return '<div class="notion-page-content">' . $content  . '</div>';
    } else {
        return '<p>Content not found or inactive.</p>';
    }
}
