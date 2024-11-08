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




// Main plugin file: notion-content.php

// Activate plugin and create custom table
register_activation_hook(__FILE__, 'notion_content_create_table');
function notion_content_create_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'notion_content';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id INT NOT NULL AUTO_INCREMENT,
        page_id VARCHAR(255) NOT NULL UNIQUE,
        title VARCHAR(255) NOT NULL,
        content LONGTEXT NOT NULL,
        images TEXT,
        is_active BOOLEAN DEFAULT TRUE,
        last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
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
}
add_action('admin_enqueue_scripts', 'notion_content_enqueue_styles');


// Add Settings link to the plugin action links
function notion_content_plugin_action_links($links) {
    $settings_link = '<a href="' . admin_url('admin.php?page=notion-content-settings') . '">Settings</a>';
    array_unshift($links, $settings_link); // Adds the link to the beginning of the array
    return $links;
}

add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'notion_content_plugin_action_links');