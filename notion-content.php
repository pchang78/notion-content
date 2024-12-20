<?php
/*
Plugin Name: Notion Content
Plugin URI: https://everydaytech.tv/wp/notion-content/
Description: A plugin to pull content from a Notion database and display it on WordPress.
Version: 1.0.0
Author: Patrick Chang
Author URI: https://everydaytech.tv/wp/
License: GPLv2 or later
*/

if (!defined('ABSPATH')) exit; // Exit if accessed directly

// Define plugin path
define('NOTION_CONTENT_PLUGIN_PATH', plugin_dir_path(__FILE__));

// Include required files
require_once NOTION_CONTENT_PLUGIN_PATH . 'includes/plugin-startup.php';
require_once NOTION_CONTENT_PLUGIN_PATH . 'includes/notion-pages.php';
require_once NOTION_CONTENT_PLUGIN_PATH . 'includes/styles.php';
require_once NOTION_CONTENT_PLUGIN_PATH . 'includes/settings.php';
require_once NOTION_CONTENT_PLUGIN_PATH . 'includes/shortcode.php';
require_once NOTION_CONTENT_PLUGIN_PATH . 'includes/notion-api.php';
require_once NOTION_CONTENT_PLUGIN_PATH . 'includes/ajax-handler.php';



// Activate plugin and create custom table
// Local table is used to store content from Notion to be displayed on your Wordpress post, page, or custom post type.
register_activation_hook(__FILE__, 'notion_content_activation');
function notion_content_activation() {
    // Register both custom post types to ensure rewrite rules are flushed
    notion_content_register_post_type();
    notion_images_register_post_type();
    
    // Flush rewrite rules
    flush_rewrite_rules();
}

// Register Custom Post Type
function notion_content_register_post_type() {
    register_post_type('notion_content', array(
        'public' => false,
        'publicly_queryable' => false,
        'show_ui' => false,
        'supports' => array('title', 'editor'),
        'can_export' => true,
        'delete_with_user' => false
    ));
}
add_action('init', 'notion_content_register_post_type');

// Add custom meta boxes for Notion-specific fields
function notion_content_add_meta_boxes() {
    add_meta_box(
        'notion_content_meta',
        __('Notion Details', 'notion-content'),
        'notion_content_meta_box_callback',
        'notion_content',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'notion_content_add_meta_boxes');

// Meta box callback function
function notion_content_meta_box_callback($post) {
    // Add nonce for security
    wp_nonce_field('notion_content_meta_box', 'notion_content_meta_box_nonce');

    // Get existing values
    $page_id = get_post_meta($post->ID, '_notion_page_id', true);
    $cron_interval = get_post_meta($post->ID, '_notion_cron_interval', true);
    $is_active = get_post_meta($post->ID, '_notion_is_active', true);
    $webhook_id = get_post_meta($post->ID, '_notion_webhook_id', true);

    // Output the fields
    ?>
    <p>
        <label for="notion_page_id"><?php _e('Notion Page ID:', 'notion-content'); ?></label>
        <input type="text" id="notion_page_id" name="notion_page_id" value="<?php echo esc_attr($page_id); ?>" />
    </p>
    <p>
        <label for="notion_cron_interval"><?php _e('Sync Interval:', 'notion-content'); ?></label>
        <select id="notion_cron_interval" name="notion_cron_interval">
            <option value="manual" <?php selected($cron_interval, 'manual'); ?>>Manual</option>
            <option value="hourly" <?php selected($cron_interval, 'hourly'); ?>>Hourly</option>
            <option value="daily" <?php selected($cron_interval, 'daily'); ?>>Daily</option>
            <option value="weekly" <?php selected($cron_interval, 'weekly'); ?>>Weekly</option>
        </select>
    </p>
    <p>
        <label for="notion_is_active"><?php _e('Active:', 'notion-content'); ?></label>
        <input type="checkbox" id="notion_is_active" name="notion_is_active" <?php checked($is_active, '1'); ?> />
    </p>
    <?php if ($webhook_id) : ?>
        <input type="hidden" name="notion_webhook_id" value="<?php echo esc_attr($webhook_id); ?>" />
    <?php endif; ?>
    <?php
}

// Save meta box data
function notion_content_save_meta_box_data($post_id) {
    // Verify nonce
    if (!isset($_POST['notion_content_meta_box_nonce']) ||
        !wp_verify_nonce($_POST['notion_content_meta_box_nonce'], 'notion_content_meta_box')) {
        return;
    }

    // If this is an autosave, don't do anything
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Check user permissions
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // Save the meta fields
    if (isset($_POST['notion_page_id'])) {
        update_post_meta($post_id, '_notion_page_id', sanitize_text_field($_POST['notion_page_id']));
    }
    
    if (isset($_POST['notion_cron_interval'])) {
        update_post_meta($post_id, '_notion_cron_interval', sanitize_text_field($_POST['notion_cron_interval']));
    }
    
    $is_active = isset($_POST['notion_is_active']) ? '1' : '0';
    update_post_meta($post_id, '_notion_is_active', $is_active);
    
    if (isset($_POST['notion_webhook_id'])) {
        update_post_meta($post_id, '_notion_webhook_id', sanitize_text_field($_POST['notion_webhook_id']));
    }
}
add_action('save_post_notion_content', 'notion_content_save_meta_box_data');

// Add the new custom post type registration for notion_images
function notion_images_register_post_type() {
    register_post_type('notion_images', array(
        'public' => false,
        'publicly_queryable' => false,
        'show_ui' => false,
        'supports' => array('title', 'editor'),
        'can_export' => true,
        'delete_with_user' => false
    ));
}
add_action('init', 'notion_images_register_post_type');

// Add menu to the Wordpress admin
add_action('admin_menu', 'notion_content_admin_menu');
function notion_content_admin_menu() {

    // Add main menu page
    add_menu_page(
        'Notion Content',
        'Notion Content',
        'manage_options',
        'notion-content',
        'notion_content_display_pages',
        plugins_url('assets/notion-content-icon.png', __FILE__),
        20
    );

    // Add submenu page for styles
    add_submenu_page(
        'notion-content', // Parent slug
        'Styles',                  // Page title
        'Styles',                  // Menu title
        'manage_options',          // Capability
        'notion-content-styles',   // Menu slug
        'notion_content_styles_page' // Function to display the styles page
    );

    // Add submenu page for settings
    add_submenu_page(
        'notion-content',
        'Settings',
        'Settings',
        'manage_options',
        'notion-content-settings',
        'notion_content_display_settings'
    );

}

// Display success message
function notion_content_admin_msg($message) {
?>
    <div class="notice notice-success is-dismissible"> <p><?php echo esc_html($message); ?></p> </div>
<?php
}

// Enqueue scripts
function notion_enqueue_scripts($hook) {
    wp_enqueue_script('notion-cron-script', plugins_url('js/notion-cron.js', __FILE__), array('jquery'), '1.0', true);
    wp_localize_script('notion-cron-script', 'notionCronAjax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('notion_cron_nonce')
    ));
}
add_action('admin_enqueue_scripts', 'notion_enqueue_scripts');

// Enqueue styles
function notion_content_enqueue_styles() {
    $screen = get_current_screen();
    if ($screen && ($screen->id === 'toplevel_page_notion-content' || strpos($screen->id, 'notion-content') !== false)) {
        wp_enqueue_style('notion-content-custom-styles', plugin_dir_url(__FILE__) . 'css/custom-styles.css');
        wp_enqueue_style('notion-content-tooltip', plugin_dir_url(__FILE__) . 'css/tooltip.css', array(), '1.0.0');
    }

}
add_action('admin_enqueue_scripts', 'notion_content_enqueue_styles');

// Add Settings link to the plugin action links
function notion_content_plugin_action_links($links) {
    $settings_link = '<a href="' . admin_url('admin.php?page=notion-content-settings&tab=setup') . '">Settings</a>';
    array_unshift($links, $settings_link); // Adds the link to the beginning of the array
    return $links;
}

add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'notion_content_plugin_action_links');
