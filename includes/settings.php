<?php

// Register settings and display settings page
add_action('admin_init', 'notion_content_register_settings');

function notion_content_register_settings() {
    register_setting('notion_content_settings_group', 'notion_api_key');
    register_setting('notion_content_settings_group', 'notion_database_url');
}

function notion_content_display_settings() {
    ?>
    <div class="wrap">
        <h1>Notion Content Settings</h1>
        <form method="post" action="options.php">
            <?php settings_fields('notion_content_settings_group'); ?>
            <?php do_settings_sections('notion_content_settings_group'); ?>
            
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Notion API Key</th>
                    <td><input type="text" name="notion_api_key" value="<?php echo esc_attr(get_option('notion_api_key')); ?>" /></td>
                </tr>
                
                <tr valign="top">
                    <th scope="row">Notion Database URL</th>
                    <td><input type="text" name="notion_database_url" value="<?php echo esc_attr(get_option('notion_database_url')); ?>" /></td>
                </tr>
            </table>
            
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}
?>
