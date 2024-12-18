<?php
/* This file is used to handle the settings of the Notion Content plugin. */

// Register settings and display settings page
add_action('admin_init', 'notion_content_register_settings');

function notion_content_register_settings() {
    register_setting('notion_content_settings_group', 'notion_content_api_key');
    register_setting('notion_content_settings_group', 'notion_content_database_url');
    register_setting('notion_content_settings_group', 'notion_content_image_size');
    register_setting('notion_content_settings_group', 'notion_content_column_tag');

}

function notion_content_display_success_message() {

    if (isset($_GET['settings-updated']) && $_GET['settings-updated'] == 'true') {
        add_settings_error(
            'notion_content_settings_group', // Setting group
            'notion_content_success', // Error key (unique)
            'Settings have been saved. <p>In order for your settings to take into effect, you must refresh your content.</p>', // Message text
            'updated' // Type (e.g., 'updated', 'error', 'warning', 'success')
        );
    }

    // Check if there are any saved settings errors
    settings_errors('notion_content_settings_group');
}
add_action('admin_notices', 'notion_content_display_success_message');



// Get all image sizes, including custom sizes
function get_all_image_sizes() {

    $all_sizes = wp_get_registered_image_subsizes();
    $excluded_sizes = ['1536x1536', '2048x2048']; // Add sizes to exclude here
    $filtered_sizes = [];
    foreach ($all_sizes as $size_name => $attributes) {
        if (!in_array($size_name, $excluded_sizes, true)) {
            if($attributes['height'] == 0) {
                $attributes['height'] = $attributes['width'];
            }
            $label = ucwords(str_replace("_", " ", $size_name));
            $filtered_sizes[$size_name] = sprintf(
                '%s (%dx%d)',
                $label,
                $attributes['width'],
                $attributes['height']
            );
        }
    }
    $filtered_sizes["full"] = "Full Size";
    return $filtered_sizes;

}

// Display the settings page
function notion_content_display_settings() {
    // API and URL not setup yet
    if(!notion_content_is_setup()) {
        notion_content_setup_page();
        return;
    }

    include NOTION_CONTENT_PLUGIN_PATH . 'includes/admin-header.php';

    $tab_name = 'setup';
    $setting_tab_url = add_query_arg( array( 'page' => 'notion-content-settings', 'tab' => $tab_name, '_wpnonce' => wp_create_nonce( 'switch_tab_' . $tab_name ) ), admin_url( 'admin.php' ) );


    $general_active = "";
    $setup_active = "   ";
    if ( isset( $_GET['tab'] ) && isset( $_GET['_wpnonce'] ) ) {

        $tab = sanitize_text_field( wp_unslash( $_GET['tab'] ) );
        $nonce = sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) );
        // Verify nonce
        if ( ! wp_verify_nonce( $nonce, 'switch_tab_' . $tab ) ) {
            die( 'Security check failed' ); // Or handle the error appropriately
        } elseif ($_GET['tab'] === 'setup') {
            $setup_active = "nav-tab-active";
        }
    }
    else {
        $general_active = "nav-tab-active";
    }



    ?>
    <div class="wrap" id="notion-content-plugin-admin">
        <h1>Notion Content Settings</h1>


         <!-- Tab Navigation -->
         <h2 class="nav-tab-wrapper">
            <a href="?page=notion-content-settings" class="nav-tab <?php echo esc_attr($general_active); ?>">General</a>
            <a href="<?php echo esc_url($setting_tab_url); ?>" class="nav-tab <?php echo esc_attr($setup_active); ?>">Setup</a>
        </h2>

        <form method="post" action="options.php">
        <?php settings_fields('notion_content_settings_group'); ?>
        <?php do_settings_sections('notion_content_settings_group'); ?>


        <?php if (!isset($_GET['tab']) || $_GET['tab'] === 'general') : ?>
            <input type="hidden" name="notion_content_api_key" value="<?php echo esc_attr(get_option('notion_content_api_key')); ?>" />
            <input type="hidden" name="notion_content_database_url" value="<?php echo esc_attr(get_option('notion_content_database_url')); ?>" />
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Image Size
                        <span class="help-tip" title="Determine which image size to be displayed on your wordpress page.">
                            <span class="dashicons dashicons-editor-help"></span>
                         </span>
                    </th>
                    <td>
                        
                    <select name="notion_content_image_size">
                    <?php
                        $image_sizes = get_all_image_sizes();
                        $selected_option = esc_attr(get_option('notion_content_image_size'));
                        if(!isset($selected_option) || !$selected_option) {
                            $selected_option = "full";
                        }
                    ?>
                    <?php foreach ($image_sizes as $name => $label) : ?>
                        <option value='<?php echo esc_attr($name); ?>' <?php selected($selected_option, $name); ?>><?php echo esc_html($label); ?></option>
                    <?php endforeach; ?>
                    </select>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row">Notion Columns
                        <span class="help-tip" title="Convert Notion columns into div or table tags">
                            <span class="dashicons dashicons-editor-help"></span>
                         </span>
                    </th>
                    <td>
                        
                    <?php
                        $selected_tag_option = esc_attr(get_option('notion_content_column_tag'));
                    ?>
                    <select name="notion_content_column_tag">
                        <option name='div' value='div' <?php selected($selected_tag_option, 'div'); ?>>Div </option>
                        <option name='table' value='table' <?php selected($selected_tag_option, 'table'); ?>>Table </option>
                    </select>
                    </td>
                </tr>
            </table>

        <?php elseif ($_GET['tab'] === 'setup') : ?>

            <input type="hidden" name="notion_content_image_size" value="<?php echo esc_attr(get_option('notion_content_image_size')); ?>" />
            <input type="hidden" name="notion_content_column_tag" value="<?php echo esc_attr(get_option('notion_content_column_tag')); ?>" />
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">
                        Notion API Key
                        <span class="help-tip" title="Internal Integration Secret found in Notion in the Notion Developers site">
                             <span class="dashicons dashicons-editor-help"></span>
                        </span>
                    </th>
                    <td><input type="text" name="notion_content_api_key" value="<?php echo esc_attr(get_option('notion_content_api_key')); ?>" class="widefat" /></td>
                </tr>
                
                <tr valign="top">
                    <th scope="row">Notion Database URL
                    <span class="help-tip" title="The full URL of the Notion database (not just the ID)">
                        <span class="dashicons dashicons-editor-help"></span>
                    </span>
                    </th>
                    <td><input type="text" name="notion_content_database_url" value="<?php echo esc_attr(get_option('notion_content_database_url')); ?>" class="widefat" /></td>
                </tr>
            </table>
        <?php endif; ?>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}
