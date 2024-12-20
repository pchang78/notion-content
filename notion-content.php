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
    array_unshift($links, $settings_link);
    return $links;
}
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'notion_content_plugin_action_links');
