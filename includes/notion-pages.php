<?php

// Display pages
function notion_content_display_pages() {

    // API and URL not setup yet
    if(!notion_content_is_setup()) {
        notion_content_setup_page();
        return;
    }

    // Refresh all content action
    if (isset($_POST['refresh_content']) && isset($_POST['notion_content_pages_form_nonce']) && wp_verify_nonce( sanitize_text_field(wp_unslash($_POST["notion_content_pages_form_nonce"])), 'notion_content_pages_form' )) {
        notion_content_refresh(); // Refresh all pages
        notion_content_admin_msg("All Content Updated");
    }

    // Refresh individual page action
    if (isset($_POST['refresh_single_page']) && isset($_POST['page_id']) && isset($_POST['notion_content_pages_form_nonce']) && wp_verify_nonce( sanitize_text_field(wp_unslash($_POST["notion_content_pages_form_nonce"])), 'notion_content_pages_form' )) {
        if(isset($_POST['page_id'])) {
            $page_id = sanitize_text_field(wp_unslash($_POST['page_id'])); // Ensure page_id is a string
            notion_content_refresh_single_page($page_id); // Refresh specific page
            notion_content_admin_msg("Content " . $page_id . " updated");
        }
    }

    include NOTION_CONTENT_PLUGIN_PATH . 'includes/admin-header.php';
    ?>
    <div class="wrap" id="notion-content-plugin-admin">
        <h1>Notion Pages</h1>
        
        <form method="post">
            <input type="submit" name="refresh_content" class="button button-primary" value="Refresh All Content">
            <?php wp_nonce_field( 'notion_content_pages_form', 'notion_content_pages_form_nonce' ); ?>
        </form>
        <br>
        
        <?php
        global $wpdb;
        $table_name = $wpdb->prefix . 'notion_content';
        $pages = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE is_active = %d", 1), ARRAY_A);

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
                echo '<td>' . esc_html($title) . '</td>';
                echo '<td>';
                echo '<input type="text" value="' . esc_attr($shortcode) . '" readonly style="width: 350px;"/> ';
                echo '<button class="button copy-button" data-shortcode="' . esc_attr($shortcode) . '">Copy</button>';
                echo '</td>';
                echo '<td>' . esc_html($last_updated) . '</td>';
                echo '<td>';
                echo '<form method="post" style="display:inline;">';
                echo '<input type="hidden" name="notion_content_pages_form_nonce" value="' . esc_attr(wp_create_nonce('notion_content_pages_form')) . '">';
                echo '<input type="hidden" name="page_id" value="' . esc_attr($page_id) . '">';
                echo '<input type="submit" name="refresh_single_page" class="button" value="Refresh Page">';
                $preview_url = add_query_arg(array('id' => urlencode($page_id), '_wpnonce' => wp_create_nonce( 'notion_content_preview_nonce' )), plugin_dir_url(__FILE__) . '../preview.php');
                echo '<a href="' . esc_url($preview_url) . '" class="button" target="_blank" style="margin-left: 4px;">Preview</a>';
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

	    <div id="loading-overlay">
		<div class="loading-message">Updating, please wait...</div>
	    </div>

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

