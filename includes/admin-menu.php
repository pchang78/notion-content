<?php

add_action('admin_menu', 'notion_content_admin_menu');
function notion_content_admin_menu() {
    add_menu_page(
        'Notion Content',
        'Notion Content',
        'manage_options',
        'notion-content',
        'notion_content_display_pages',
        'dashicons-layout',
        20
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


function notion_content_display_pages() {
    ?>
    <div class="wrap">
        <h1>Notion Pages</h1>
        <form method="post">
            <input type="submit" name="refresh_content" class="button button-primary" value="Refresh All Content">
        </form>
        <br>
        <?php
        if (isset($_POST['refresh_content'])) {
            notion_content_refresh(); // Refresh all pages
        }

        if (isset($_POST['refresh_single_page'])) {
            notion_content_refresh_single_page(sanitize_text_field($_POST['page_id'])); // Refresh individual page
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'notion_content';
        
        $pages = $wpdb->get_results("SELECT * FROM $table_name WHERE is_active = 1", ARRAY_A);

        if ($pages) {
            echo '<table class="wp-list-table widefat fixed striped">';
            echo '<thead><tr><th>Title</th><th>Shortcode</th><th>Actions</th></tr></thead>';
            echo '<tbody>';
            foreach ($pages as $page) {
                $title = esc_html($page['title']);
                $page_id = esc_attr($page['page_id']);
                $shortcode = '[notion_page page_id="' . $page_id . '"]';
                
                echo '<tr>';
                echo '<td>' . $title . '</td>';
                echo '<td><code>' . esc_html($shortcode) . '</code></td>';
                echo '<td>';
                echo '<form method="post" style="display:inline;">';
                echo '<input type="hidden" name="page_id" value="' . $page_id . '">';
                echo '<input type="submit" name="refresh_single_page" class="button" value="Refresh Page">';
                echo '</form>';
                echo '</td>';
                echo '</tr>';
            }
            echo '</tbody>';
            echo '</table>';
        } else {
            echo '<p>No active pages found.</p>';
        }
        ?>
    </div>
    <?php
}
?>