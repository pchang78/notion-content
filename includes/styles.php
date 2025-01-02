<?php
/* This file is used to handle the styles of the Notion Content plugin. */

// Display the styles page
function content_importer_for_notion_styles_page() {
    if (!current_user_can('manage_options')) {
        wp_die(esc_html(__('You do not have sufficient permissions to access this page.', 'content-importer-notion')));
    }

    // API and URL not setup yet
    if(!content_importer_for_notion_is_setup()) {
        content_importer_for_notion_setup_page();
        return;
    }

    $arrFields = array(
        'content_importer_for_notion_style_paragraph' => 'paragraph_style',
        'content_importer_for_notion_style_heading1' => 'heading1_style',
        'content_importer_for_notion_style_heading2' => 'heading2_style',
        'content_importer_for_notion_style_heading3' => 'heading3_style',
        'content_importer_for_notion_style_table' => 'table_style',
        'content_importer_for_notion_style_row' => 'row_style',
        'content_importer_for_notion_style_col' => 'col_style',
        'content_importer_for_notion_style_ul' => 'ul_style',
        'content_importer_for_notion_style_li' => 'li_style',
        'content_importer_for_notion_style_quote' => 'quote_style',
        'content_importer_for_notion_style_hr' => 'hr_style',
        'content_importer_for_notion_style_img' => 'img_style',
        'content_importer_for_notion_style_column_div_wrapper' => 'col_div_wrapper_style',
        'content_importer_for_notion_style_column_div' => 'col_div_style',
        'content_importer_for_notion_style_column_table' => 'col_table_style',
        'content_importer_for_notion_style_column_row' => 'col_row_style',
        'content_importer_for_notion_style_column_col' => 'col_col_style',
    );


    // Save form data when submitted
    if (isset($_POST['content_importer_for_notion_styles_save'])) {

        // Save Classes styles
        if(isset($_POST['paragraph_style']) && isset($_POST['content_importer_for_notion_styles_page_nonce']) && wp_verify_nonce( sanitize_text_field(wp_unslash($_POST["content_importer_for_notion_styles_page_nonce"])), 'content_importer_for_notion_styles_page' )) {

            foreach($arrFields as $option_name => $field_name) {
                if(isset($_POST[$field_name])) {
                    update_option($option_name, sanitize_text_field(wp_unslash($_POST[$field_name])));
                }
            }


            echo '<div class="updated">
                <p>Styles updated successfully!</p>
                <p>You must refresh your content for the styles to be applied.</p>
            </div>';
        }
        
        // Save Custom CSS
        if(isset($_POST['custom_css']) && isset($_POST['content_importer_for_notion_styles_page_nonce']) && wp_verify_nonce( sanitize_text_field(wp_unslash($_POST["content_importer_for_notion_styles_page_nonce"])), 'content_importer_for_notion_styles_page' )) {
            update_option('content_importer_for_notion_custom_css', wp_strip_all_tags(sanitize_text_field(wp_unslash($_POST['custom_css']))));
            echo '<div class="updated">
                <p>Custom CSS updated successfully!</p>
                <p>You must refresh your content for the custom CSS to be applied.</p>
            </div>';
        }

    }

    reset($arrFields);
    // Get all the options values
    foreach($arrFields as $option_name => $field_name) {
        $$field_name = get_option($option_name, '');
    }

    // Get the custom CSS
    $custom_css = get_option('content_importer_for_notion_custom_css', '');

    include NOTION_CONTENT_PLUGIN_PATH . 'includes/admin-header.php';

    // Display the tab interface
    ?>
    <div class="wrap" id="content-importer-for-notion-plugin-admin">
        <h1>Content Importer for Notion Classes / Styles</h1>
        
        <!-- Tab Navigation -->
        <h2 class="nav-tab-wrapper">
            <a href="?page=content-importer-for-notion-styles&tab=classes" class="nav-tab <?php echo ((isset($_GET['tab']) && $_GET['tab'] === 'classes') || !isset($_GET['tab'])) ? 'nav-tab-active' : ''; ?>">Classes</a>
            <a href="?page=content-importer-for-notion-styles&tab=custom_css" class="nav-tab <?php echo (isset($_GET['tab']) && $_GET['tab'] === 'custom_css') ? 'nav-tab-active' : ''; ?>">Custom CSS</a>
        </h2>

        <!-- Tab Content -->
        <form method="post">
        <?php wp_nonce_field( 'content_importer_for_notion_styles_page', 'content_importer_for_notion_styles_page_nonce' ); ?>

            <?php if (!isset($_GET['tab']) || $_GET['tab'] === 'classes') : ?>
                <!-- Classes Tab -->
                <h3>Define CSS Classes for Elements</h3>
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="paragraph_style">Paragraph (p)</label></th>
                        <td><input type="text" id="paragraph_style" name="paragraph_style" value="<?php echo esc_attr($paragraph_style); ?>" style="width: 100%;">
                            <?php if($paragraph_style) : ?>
                            <br><small>Result: &lt;p class="<?php echo esc_attr($paragraph_style); ?>"&gt;</small>
                            <?php endif; ?>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><label for="heading1_style">Heading 1 (h1)</label></th>
                        <td><input type="text" id="heading1_style" name="heading1_style" value="<?php echo esc_attr($heading1_style); ?>" style="width: 100%;">
                            <?php if($heading1_style) : ?>
                            <br><small>Result: &lt;h1 class="<?php echo esc_attr($heading1_style); ?>"&gt;</small>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="heading2_style">Heading 2 (h2)</label></th>
                        <td><input type="text" id="heading2_style" name="heading2_style" value="<?php echo esc_attr($heading2_style); ?>" style="width: 100%;">
                            <?php if($heading2_style) : ?>
                            <br><small>Result: &lt;h2 class="<?php echo esc_attr($heading2_style); ?>"&gt;</small>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="heading3_style">Heading 3 (h3)</label></th>
                        <td><input type="text" id="heading3_style" name="heading3_style" value="<?php echo esc_attr($heading3_style); ?>" style="width: 100%;">
                            <?php if($heading3_style) : ?>
                            <br><small>Result: &lt;h3 class="<?php echo esc_attr($heading3_style); ?>"&gt;</small>
                            <?php endif; ?>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><label for="table_style">Table (table)</label></th>
                        <td><input type="text" id="table_style" name="table_style" value="<?php echo esc_attr($table_style); ?>" style="width: 100%;">
                            <?php if($table_style) : ?>
                            <br><small>Result: &lt;table class="<?php echo esc_attr($table_style); ?>"&gt;</small>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="table_style">Table Row (tr)</label></th>
                        <td><input type="text" id="row_style" name="row_style" value="<?php echo esc_attr($row_style); ?>" style="width: 100%;">
                            <?php if($row_style) : ?>
                            <br><small>Result: &lt;tr class="<?php echo esc_attr($row_style); ?>"&gt;</small>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="table_style">Table Column (td)</label></th>
                        <td><input type="text" id="col_style" name="col_style" value="<?php echo esc_attr($col_style); ?>" style="width: 100%;">
                            <?php if($col_style) : ?>
                            <br><small>Result: &lt;td class="<?php echo esc_attr($col_style); ?>"&gt;</small>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="ul_style">Unordered List (ul)</label></th>
                        <td><input type="text" id="ul_style" name="ul_style" value="<?php echo esc_attr($ul_style); ?>" style="width: 100%;">
                            <?php if($ul_style) : ?>
                            <br><small>Result: &lt;ul class="<?php echo esc_attr($ul_style); ?>"&gt;</small>
                            <?php endif; ?>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><label for="li_style">List Item (li)</label></th>
                        <td><input type="text" id="li_style" name="li_style" value="<?php echo esc_attr($li_style); ?>" style="width: 100%;">
                            <?php if($li_style) : ?>
                            <br><small>Result: &lt;li class="<?php echo esc_attr($li_style); ?>"&gt;</small>
                            <?php endif; ?>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><label for="hr_style">Separator (hr)</label></th>
                        <td><input type="text" id="hr_style" name="hr_style" value="<?php echo esc_attr($hr_style); ?>" style="width: 100%;">
                            <?php if($hr_style) : ?>
                            <br><small>Result: &lt;hr class="<?php echo esc_attr($hr_style); ?>"&gt;</small>
                            <?php endif; ?>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><label for="img_style">Image (img)</label></th>
                        <td><input type="text" id="img_style" name="img_style" value="<?php echo esc_attr($img_style); ?>" style="width: 100%;">
                            <?php if($img_style) : ?>
                            <br><small>Result: &lt;img class="<?php echo esc_attr($img_style); ?>"&gt;</small>
                            <?php endif; ?>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><label for="quote_style">Block Quote (blockquote)</label></th>

                        <td><input type="text" id="quote_style" name="quote_style" value="<?php echo esc_attr($quote_style); ?>" style="width: 100%;">
                            <?php if($quote_style) : ?>
                            <br><small>Result: &lt;blockquote class="<?php echo esc_attr($quote_style); ?>"&gt;</small>
                            <?php endif; ?>
                        </td>

                    </tr>

                    <tr>
                        <th colspan="2">
                            <hr>
                            Notion Column Classes
                        </th>
                    </tr>

                    <tr>
                        <th scope="row"><label for="col_div_wrapper_style">Div Wrapper (div)</label></th>
                        <td><input type="text" id="col_div_wrapper_style" name="col_div_wrapper_style" value="<?php echo esc_attr($col_div_wrapper_style); ?>" style="width: 100%;">
                            <?php if($col_div_wrapper_style) : ?>
                            <br><small>Result: &lt;div class="<?php echo esc_attr($col_div_wrapper_style); ?>"&gt; <?php endif; ?>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><label for="col_div_style">Div (div)</label></th>
                        <td><input type="text" id="col_div_style" name="col_div_style" value="<?php echo esc_attr($col_div_style); ?>" style="width: 100%;">
                            <?php if($col_div_style) : ?>
                            <br><small>Result: &lt;div class="<?php echo esc_attr($col_div_style); ?>"&gt; </small>
                            <?php endif; ?>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><label for="col_table_style">Table (table)</label></th>
                        <td><input type="text" id="col_table_style" name="col_table_style" value="<?php echo esc_attr($col_table_style); ?>" style="width: 100%;">
                            <?php if($col_table_style) : ?>
                            <br><small>Result: &lt;table class="<?php echo esc_attr($col_table_style); ?>"&gt; </small>
                            <?php endif; ?>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><label for="col_row_style">Table Row (tr)</label></th>
                        <td><input type="text" id="col_row_style" name="col_row_style" value="<?php echo esc_attr($col_row_style); ?>" style="width: 100%;">
                            <?php if($col_row_style) : ?>
                            <br><small>Result: &lt;tr class="<?php echo esc_attr($col_row_style); ?>"&gt; </small>
                            <?php endif; ?>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><label for="col_col_style">Table Column (td)</label></th>
                        <td><input type="text" id="col_col_style" name="col_col_style" value="<?php echo esc_attr($col_col_style); ?>" style="width: 100%;">
                            <?php if($col_col_style) : ?>
                            <br><small>Result: &lt;tr class="<?php echo esc_attr($col_col_style); ?>"&gt; </small>
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

            <?php submit_button('Save Changes', 'primary', 'content_importer_for_notion_styles_save'); ?>
        </form>
    </div>
    <?php
}
