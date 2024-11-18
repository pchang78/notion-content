<?php

// Register settings and display settings page
add_action('admin_init', 'notion_content_register_settings');

function notion_content_register_settings() {
    register_setting('notion_content_settings_group', 'notion_api_key');
    register_setting('notion_content_settings_group', 'notion_database_url');
    register_setting('notion_content_settings_group', 'notion_image_size');
}

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

function notion_content_display_settings() {

    if (isset($_GET['settings-updated'])) {
        add_settings_error('notion_content_messages', 'notion_content_message', 'Settings have been saved.', 'updated');
    }

    settings_errors('notion_content_messages');

    ?>
    <div class="wrap">
        <h1>Notion Content Settings</h1>


         <!-- Tab Navigation -->
         <h2 class="nav-tab-wrapper">
            <a href="?page=notion-content-settings" class="nav-tab <?php echo ((isset($_GET['tab']) && $_GET['tab'] === 'general') || !isset($_GET['tab'])) ? 'nav-tab-active' : ''; ?>">General</a>
            <a href="?page=notion-content-settings&tab=setup" class="nav-tab <?php echo (isset($_GET['tab']) && $_GET['tab'] === 'setup') ? 'nav-tab-active' : ''; ?>">Setup</a>
        </h2>

        <form method="post" action="options.php">
        <?php settings_fields('notion_content_settings_group'); ?>
        <?php do_settings_sections('notion_content_settings_group'); ?>

        <?php if (!isset($_GET['tab']) || $_GET['tab'] === 'general') : ?>


            <input type="hidden" name="notion_api_key" value="<?php echo esc_attr(get_option('notion_api_key')); ?>" />
            <input type="hidden" name="notion_database_url" value="<?php echo esc_attr(get_option('notion_database_url')); ?>" />

            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Image Size</th>
                    <td>
                        
                    <select name="notion_image_size">
                    <?php
                        $image_sizes = get_all_image_sizes();
                        $selected_option = esc_attr(get_option('notion_image_size'));
                        if(!isset($selected_option) || !$selected_option) {
                            $selected_option = "full";
                        }
                    ?>
                    <?php foreach ($image_sizes as $name => $label) : ?>
                        <option value='<?php echo $name; ?>' <?php selected($selected_option, $name); ?>><?php echo $label; ?></option>
                    <?php endforeach; ?>
                    </select>
                </td>
                </tr>
            </table>

        <?php elseif ($_GET['tab'] === 'setup') : ?>

            <input type="hidden" name="notion_image_size" value="<?php echo esc_attr(get_option('notion_image_size')); ?>" />
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Notion API Key</th>
                    <td><input type="text" name="notion_api_key" value="<?php echo esc_attr(get_option('notion_api_key')); ?>" class="widefat" /></td>
                </tr>
                
                <tr valign="top">
                    <th scope="row">Notion Database URL</th>
                    <td><input type="text" name="notion_database_url" value="<?php echo esc_attr(get_option('notion_database_url')); ?>" class="widefat" /></td>
                </tr>
            </table>
            


        <?php endif; ?>

            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}
