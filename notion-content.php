<?php
/*
Plugin Name: Notion Content
Description: A plugin to pull content from a Notion database and display it on WordPress.
Version: 1.0
Author: Patrick Chang
*/

if (!defined('ABSPATH')) exit; // Exit if accessed directly

// Define plugin path
define('NOTION_CONTENT_PLUGIN_PATH', plugin_dir_path(__FILE__));

// Include required files
require_once NOTION_CONTENT_PLUGIN_PATH . 'includes/admin-menu.php';
require_once NOTION_CONTENT_PLUGIN_PATH . 'includes/styles.php';
require_once NOTION_CONTENT_PLUGIN_PATH . 'includes/settings.php';
require_once NOTION_CONTENT_PLUGIN_PATH . 'includes/shortcode.php';
require_once NOTION_CONTENT_PLUGIN_PATH . 'includes/notion-api.php';


// Activate plugin and create custom table
register_activation_hook(__FILE__, 'notion_content_create_table');
function notion_content_create_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'notion_content';
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE $table_name (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        page_id VARCHAR(255) NOT NULL,
        title VARCHAR(255) NOT NULL,
        content LONGTEXT NOT NULL,
        images TEXT,
        last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        is_active TINYINT(1) DEFAULT 1,
        webhook_id VARCHAR(255) UNIQUE,
        PRIMARY KEY  (id)
    ) $charset_collate;";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

register_activation_hook(__FILE__, 'notion_create_images_table');
function notion_create_images_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'notion_images';
    $charset_collate = $wpdb->get_charset_collate();
    // SQL to create the table
    $sql = "CREATE TABLE $table_name (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        object_id VARCHAR(255) NOT NULL,
        post_id BIGINT(20) UNSIGNED NOT NULL,
        PRIMARY KEY  (id),
        UNIQUE KEY unique_object (object_id)
    ) $charset_collate;";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}


function notion_content_admin_msg($message) {
?>
    <div class="notice notice-success is-dismissible"> <p><?php echo $message; ?></p> </div>
<?php
}

function notion_content_enqueue_styles() {
    wp_enqueue_style('notion-content-custom-styles', plugin_dir_url(__FILE__) . 'css/custom-styles.css');
    wp_enqueue_style('notion-content-tooltip', plugin_dir_url(__FILE__) . 'css/tooltip.css', array(), '1.0.0');

}
add_action('admin_enqueue_scripts', 'notion_content_enqueue_styles');

// Add Settings link to the plugin action links
function notion_content_plugin_action_links($links) {
    $settings_link = '<a href="' . admin_url('admin.php?page=notion-content-settings&tab=setup') . '">Settings</a>';
    array_unshift($links, $settings_link); // Adds the link to the beginning of the array
    return $links;
}

add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'notion_content_plugin_action_links');