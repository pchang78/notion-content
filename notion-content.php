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
require_once NOTION_CONTENT_PLUGIN_PATH . 'includes/plugin-startup.php';
require_once NOTION_CONTENT_PLUGIN_PATH . 'includes/notion-pages.php';
require_once NOTION_CONTENT_PLUGIN_PATH . 'includes/styles.php';
require_once NOTION_CONTENT_PLUGIN_PATH . 'includes/settings.php';
require_once NOTION_CONTENT_PLUGIN_PATH . 'includes/shortcode.php';
require_once NOTION_CONTENT_PLUGIN_PATH . 'includes/notion-api.php';
require_once NOTION_CONTENT_PLUGIN_PATH . 'includes/ajax-handler.php';




// Code of debugging startup configurations
/*
if(isset($_POST["notion_content_check_config"]) && $_POST["notion_content_check_config"]) {
        $api_key = $_POST["notion_content_api_key"];
        $pageID = notion_extract_database_id($_POST["notion_content_database_url"]);
        notion_content_check_notion_config($api_key, $pageID);

    exit;
}
    */




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
        cron_interval varchar(50) NOT NULL DEFAULT 'manual',
        is_active TINYINT(1) DEFAULT 1,
        webhook_id VARCHAR(255) UNIQUE,
        PRIMARY KEY  (id),
        INDEX idx_page_id (page_id),
        INDEX idx_is_active (is_active),
        INDEX idx_cron_interval (cron_interval)
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


add_action('admin_menu', 'notion_content_admin_menu');
function notion_content_admin_menu() {

    add_menu_page(
        'Notion Content',
        'Notion Content',
        'manage_options',
        'notion-content',
        'notion_content_display_pages',
        plugins_url('assets/notion-content-icon.png', __FILE__),
        20
    );

    add_submenu_page(
        'notion-content', // Parent slug
        'Styles',                  // Page title
        'Styles',                  // Menu title
        'manage_options',          // Capability
        'notion-content-styles',   // Menu slug
        'notion_content_styles_page' // Function to display the styles page
    );

    add_submenu_page(
        'notion-content',
        'Settings',
        'Settings',
        'manage_options',
        'notion-content-settings',
        'notion_content_display_settings'
    );

}





function notion_content_admin_msg($message) {
?>
    <div class="notice notice-success is-dismissible"> <p><?php echo $message; ?></p> </div>
<?php
}


function notion_enqueue_scripts($hook) {
    wp_enqueue_script('notion-cron-script', plugins_url('js/notion-cron.js', __FILE__), array('jquery'), '1.0', true);
    wp_localize_script('notion-cron-script', 'notionCronAjax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('notion_cron_nonce')
    ));
}
add_action('admin_enqueue_scripts', 'notion_enqueue_scripts');

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
