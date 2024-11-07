<?php

function notion_content_styles_page() {
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }

    // Save form data when submitted
    if (isset($_POST['notion_content_styles_save'])) {

        // Save Classes styles
        if(isset($_POST['table_style'])) {
            update_option('notion_content_style_table', sanitize_text_field($_POST['table_style']));
            update_option('notion_content_style_ul', sanitize_text_field($_POST['ul_style']));
            update_option('notion_content_style_li', sanitize_text_field($_POST['li_style']));
            echo '<div class="updated"><p>Styles updated successfully!</p></div>';
        }
        
        // Save Custom CSS
        if(isset($_POST['custom_css'])) {
            update_option('notion_content_custom_css', wp_strip_all_tags($_POST['custom_css']));
            echo '<div class="updated"><p>Custom CSS updated successfully!</p></div>';
        }

    }

    // Retrieve saved options
    $table_style = get_option('notion_content_style_table', '');
    $ul_style = get_option('notion_content_style_ul', '');
    $li_style = get_option('notion_content_style_li', '');
    $custom_css = get_option('notion_content_custom_css', '');

    // Display the tab interface
    ?>
    <div class="wrap">
        <h1>Notion Content Styles</h1>
        
        <!-- Tab Navigation -->
        <h2 class="nav-tab-wrapper">
            <a href="?page=notion-content-styles&tab=classes" class="nav-tab <?php echo ((isset($_GET['tab']) && $_GET['tab'] === 'classes') || !isset($_GET['tab'])) ? 'nav-tab-active' : ''; ?>">Classes</a>
            <a href="?page=notion-content-styles&tab=custom_css" class="nav-tab <?php echo (isset($_GET['tab']) && $_GET['tab'] === 'custom_css') ? 'nav-tab-active' : ''; ?>">Custom CSS</a>
        </h2>

        <!-- Tab Content -->
        <form method="post">
            <?php if (!isset($_GET['tab']) || $_GET['tab'] === 'classes') : ?>
                <!-- Classes Tab -->
                <h3>Define CSS Classes for Elements</h3>
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="table_style">Table Style</label></th>
                        <td><input type="text" id="table_style" name="table_style" value="<?php echo esc_attr($table_style); ?>" style="width: 100%;">
                        <?php if($table_style) : ?>
                        <br><small>Result: &lt;table class="<?php echo $table_style ?>"&gt;
                        <?php endif; ?>
                    </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="ul_style">Unordered List Style (ul)</label></th>
                        <td><input type="text" id="ul_style" name="ul_style" value="<?php echo esc_attr($ul_style); ?>" style="width: 100%;">
                        <?php if($ul_style) : ?>
                        <br><small>Result: &lt;ul class="<?php echo $ul_style ?>"&gt;
                        <?php endif; ?>
                    </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="li_style">List Item Style (li)</label></th>
                        <td><input type="text" id="li_style" name="li_style" value="<?php echo esc_attr($li_style); ?>" style="width: 100%;">
                        <?php if($li_style) : ?>
                        <br><small>Result: &lt;li class="<?php echo $li_style ?>"&gt;
                        <?php endif; ?>
                    </td>
                    </tr>
                </table>

            <?php elseif ($_GET['tab'] === 'custom_css') : ?>
                <!-- Custom CSS Tab -->
                <h3>Custom CSS</h3>
                <p>Enter your custom CSS styles:</p>
                <p>Note: All content styles here will be applied globally.  To apply styles to just the content from Notion, use <u>.notion-page-content</u> as the parent class. </p>
                <p>
                    Example: .notion-page-content .my_class 
                </p>
                <textarea name="custom_css" style="width: 100%; height: 300px;"><?php echo esc_textarea($custom_css); ?></textarea>
                           <?php endif; ?>

            <?php submit_button('Save Changes', 'primary', 'notion_content_styles_save'); ?>
        </form>
    </div>
    <?php
}
