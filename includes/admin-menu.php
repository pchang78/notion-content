<?php

add_action('admin_menu', 'notion_content_admin_menu');
function notion_content_admin_menu() {

    add_menu_page(
        'Notion Content',
        'Notion Content',
        'manage_options',
        'notion-content',
        'notion_content_display_pages',
        plugins_url('../assets/notion-content-icon.png', __FILE__),
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


function notion_content_display_pages() {
    // Refresh all content action
    if (isset($_POST['refresh_content'])) {
        notion_content_refresh(); // Refresh all pages
        notion_content_admin_msg("All Content Updated");
    }

    // Refresh individual page action
    if (isset($_POST['refresh_single_page']) && isset($_POST['page_id'])) {
        $page_id = sanitize_text_field($_POST['page_id']); // Ensure page_id is a string
        notion_content_refresh_single_page($page_id); // Refresh specific page
        notion_content_admin_msg("Content " . $page_id . " updated");
    }

    ?>
    <div class="wrap">
        <h1>Notion Pages</h1>
        
        <form method="post">
            <input type="submit" name="refresh_content" class="button button-primary" value="Refresh All Content">
        </form>
        <br>
        
        <?php
        global $wpdb;
        $table_name = $wpdb->prefix . 'notion_content';
        
        $pages = $wpdb->get_results("SELECT * FROM $table_name WHERE is_active = 1", ARRAY_A);

        if ($pages) {
            echo '<table class="wp-list-table widefat widetable striped">';
            echo '<thead>
            <tr>
                <th>Title</th>
                <th>Shortcode</th>
                <th>Last Updated</th>
                <th>Actions</th>
                <th>Auto Update Interval
                    <span class="help-tip" title="Automatic refreshing content from Notion.">
                             <span class="dashicons dashicons-editor-help"></span>
                    </span>
                </th>
            </tr>
            </thead>';
            echo '<tbody>';
            foreach ($pages as $page) {
                $title = esc_html($page['title']);
                $page_id = esc_attr($page['page_id']);
                $last_updated = esc_html($page['last_updated']);
                $shortcode = '[notion_page page_id="' . $page_id . '"]';
                
                echo '<tr>';
                echo '<td>' . $title . '</td>';
                echo '<td>';
                echo '<input type="text" value="' . esc_attr($shortcode) . '" readonly style="width: 350px;"/> ';
                echo '<button class="button copy-button" data-shortcode="' . esc_attr($shortcode) . '">Copy</button>';
                echo '</td>';
                echo '<td>' . $last_updated . '</td>';
                echo '<td>';
                echo '<form method="post" style="display:inline;">';
                echo '<input type="hidden" name="page_id" value="' . $page_id . '">';
                echo '<input type="submit" name="refresh_single_page" class="button" value="Refresh Page">';
                echo '</form>';
                echo '</td>';

                ?>

                <td>
                <select class="cron-interval" data-page-id="<?php echo esc_attr($page['page_id']); ?>">
                    <option value="manual" <?php selected($page['cron_interval'], 'manual'); ?>>Manual</option>
                    <option value="15_minutes" <?php selected($page['cron_interval'], '15_minutes'); ?>>Every 15 Minutes</option>
                    <option value="30_minutes" <?php selected($page['cron_interval'], '30_minutes'); ?>>Every 30 Minutes</option>
                    <option value="1_hour" <?php selected($page['cron_interval'], '1_hour'); ?>>Every Hour</option>
                    <option value="6_hours" <?php selected($page['cron_interval'], '6_hours'); ?>>Every 6 Hours</option>
                    <option value="12_hours" <?php selected($page['cron_interval'], '12_hours'); ?>>Every 12 Hours</option>
                    <option value="once_a_day" <?php selected($page['cron_interval'], 'once_a_day'); ?>>Once a Day</option>
                </select>
                </td>


            <?php
                echo '</tr>';
            }
            echo '</tbody>';
            echo '</table>';
        } else {
            echo '<p>No active pages found.</p>';
        }
        ?>
    </div>


    <div id="loading-overlay">
        <div class="loading-message">Updating, please wait...</div>
    </div>


    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const copyButtons = document.querySelectorAll('.copy-button');
            copyButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const shortcode = this.getAttribute('data-shortcode');
                    const tempInput = document.createElement('input');
                    document.body.appendChild(tempInput);
                    tempInput.value = shortcode;
                    tempInput.select();
                    document.execCommand('copy');
                    document.body.removeChild(tempInput);
                    alert('Shortcode copied to clipboard!'); // Optional alert
                });
            });
        });
    </script>



    <?php
}

