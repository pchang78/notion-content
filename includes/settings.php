<?php
/* This file is used to handle the settings of the Notion Content plugin. */

// Register settings and display settings page
add_action('admin_init', 'content_importer_for_notion_register_settings');

function content_importer_for_notion_register_settings() {
    register_setting('content_importer_for_notion_settings_group', 'content_importer_for_notion_api_key');
    register_setting('content_importer_for_notion_settings_group', 'content_importer_for_notion_database_url');
    register_setting('content_importer_for_notion_settings_group', 'content_importer_for_notion_image_size');
    register_setting('content_importer_for_notion_settings_group', 'content_importer_for_notion_column_tag');

}

function content_importer_for_notion_display_success_message() {

    if (isset($_GET['settings-updated']) && $_GET['settings-updated'] == 'true') {
        add_settings_error(
            'content_importer_for_notion_settings_group', // Setting group
            'content_importer_for_notion_success', // Error key (unique)
            'Settings have been saved. <p>In order for your settings to take into effect, you must refresh your content.</p>', // Message text
            'updated' // Type (e.g., 'updated', 'error', 'warning', 'success')
        );
    }

    // Check if there are any saved settings errors
    settings_errors('content_importer_for_notion_settings_group');
}
add_action('admin_notices', 'content_importer_for_notion_display_success_message');



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
    if(!content_importer_for_notion_is_setup()) {
        content_importer_for_notion_setup_page();
        return;
    }

    include NOTION_CONTENT_PLUGIN_PATH . 'includes/admin-header.php';

    $tab_name = 'setup';
    $setting_tab_url = add_query_arg( array( 'page' => 'content-importer-for-notion-settings', 'tab' => $tab_name, '_wpnonce' => wp_create_nonce( 'switch_tab_' . $tab_name ) ), admin_url( 'admin.php' ) );


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
    <div class="wrap" id="content-importer-for-notion-plugin-admin">
        <h1>Content Importer for Notion Settings</h1>


         <!-- Tab Navigation -->
         <h2 class="nav-tab-wrapper">
            <a href="?page=content-importer-for-notion-settings" class="nav-tab <?php echo esc_attr($general_active); ?>">General</a>
            <a href="<?php echo esc_url($setting_tab_url); ?>" class="nav-tab <?php echo esc_attr($setup_active); ?>">Setup</a>
        </h2>

        <form method="post" action="options.php">
        <?php settings_fields('content_importer_for_notion_settings_group'); ?>
        <?php do_settings_sections('content_importer_for_notion_settings_group'); ?>


        <?php if (!isset($_GET['tab']) || $_GET['tab'] === 'general') : ?>
            <input type="hidden" name="content_importer_for_notion_api_key" value="<?php echo esc_attr(get_option('content_importer_for_notion_api_key')); ?>" />
            <input type="hidden" name="content_importer_for_notion_database_url" value="<?php echo esc_attr(get_option('content_importer_for_notion_database_url')); ?>" />
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Image Size
                        <span class="help-tip" title="Determine which image size to be displayed on your wordpress page.">
                            <span class="dashicons dashicons-editor-help"></span>
                         </span>
                    </th>
                    <td>
                        
                    <select name="content_importer_for_notion_image_size">
                    <?php
                        $image_sizes = get_all_image_sizes();
                        $selected_option = esc_attr(get_option('content_importer_for_notion_image_size'));
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
                        $selected_tag_option = esc_attr(get_option('content_importer_for_notion_column_tag'));
                    ?>
                    <select name="content_importer_for_notion_column_tag">
                        <option name='div' value='div' <?php selected($selected_tag_option, 'div'); ?>>Div </option>
                        <option name='table' value='table' <?php selected($selected_tag_option, 'table'); ?>>Table </option>
                    </select>
                    </td>
                </tr>
            </table>

        <?php elseif ($_GET['tab'] === 'setup') : ?>

            <input type="hidden" name="content_importer_for_notion_image_size" value="<?php echo esc_attr(get_option('content_importer_for_notion_image_size')); ?>" />
            <input type="hidden" name="content_importer_for_notion_column_tag" value="<?php echo esc_attr(get_option('content_importer_for_notion_column_tag')); ?>" />
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">
                        Notion API Key
                        <span class="help-tip" title="Internal Integration Secret found in Notion in the Notion Developers site">
                             <span class="dashicons dashicons-editor-help"></span>
                        </span>
                    </th>
                    <td><input type="text" name="content_importer_for_notion_api_key" value="<?php echo esc_attr(get_option('content_importer_for_notion_api_key')); ?>" class="widefat" /></td>
                </tr>
                
                <tr valign="top">
                    <th scope="row">Notion Database URL
                    <span class="help-tip" title="The full URL of the Notion database (not just the ID)">
                        <span class="dashicons dashicons-editor-help"></span>
                    </span>
                    </th>
                    <td><input type="text" name="content_importer_for_notion_database_url" value="<?php echo esc_attr(get_option('content_importer_for_notion_database_url')); ?>" class="widefat" /></td>
                </tr>
            </table>
        <?php endif; ?>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}
