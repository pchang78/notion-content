<?php

function notion_content_styles_page() {
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }


      // API and URL not setup yet
      if(!notion_content_is_setup()) {
        notion_content_setup_page();
        return;
    }




    // Save form data when submitted
    if (isset($_POST['notion_content_styles_save'])) {

        // Save Classes styles
        if(isset($_POST['paragraph_style'])) {
            update_option('notion_content_style_paragraph', sanitize_text_field($_POST['paragraph_style']));
            update_option('notion_content_style_heading1', sanitize_text_field($_POST['heading1_style']));
            update_option('notion_content_style_heading2', sanitize_text_field($_POST['heading2_style']));
            update_option('notion_content_style_heading3', sanitize_text_field($_POST['heading3_style']));
            update_option('notion_content_style_table', sanitize_text_field($_POST['table_style']));
            update_option('notion_content_style_row', sanitize_text_field($_POST['row_style']));
            update_option('notion_content_style_col', sanitize_text_field($_POST['col_style']));
            update_option('notion_content_style_ul', sanitize_text_field($_POST['ul_style']));
            update_option('notion_content_style_li', sanitize_text_field($_POST['li_style']));
            update_option('notion_content_style_quote', sanitize_text_field($_POST['quote_style']));
            update_option('notion_content_style_hr', sanitize_text_field($_POST['hr_style']));
            update_option('notion_content_style_img', sanitize_text_field($_POST['img_style']));
            update_option('notion_content_style_column_div_wrapper', sanitize_text_field($_POST['col_div_wrapper_style']));
            update_option('notion_content_style_column_div', sanitize_text_field($_POST['col_div_style']));
            update_option('notion_content_style_column_table', sanitize_text_field($_POST['col_table_style']));
            update_option('notion_content_style_column_row', sanitize_text_field($_POST['col_row_style']));
            update_option('notion_content_style_column_col', sanitize_text_field($_POST['col_col_style']));

            echo '<div class="updated">
                <p>Styles updated successfully!</p>
                <p>You must refresh your content for the styles to be applied.</p>
            </div>';
        }
        
        // Save Custom CSS
        if(isset($_POST['custom_css'])) {
            update_option('notion_content_custom_css', wp_strip_all_tags($_POST['custom_css']));
            echo '<div class="updated">
                <p>Custom CSS updated successfully!</p>
                <p>You must refresh your content for the custom CSS to be applied.</p>
            </div>';
        }

    }

    // Retrieve saved options
    $paragraph_style = get_option('notion_content_style_paragraph', '');
    $heading1_style = get_option('notion_content_style_heading1', '');
    $heading2_style = get_option('notion_content_style_heading2', '');
    $heading3_style = get_option('notion_content_style_heading3', '');
    $table_style = get_option('notion_content_style_table', '');
    $row_style = get_option('notion_content_style_row', '');
    $col_style = get_option('notion_content_style_col', '');
    $ul_style = get_option('notion_content_style_ul', '');
    $li_style = get_option('notion_content_style_li', '');
    $quote_style = get_option('notion_content_style_quote', '');
    $hr_style = get_option('notion_content_style_hr', '');
    $img_style = get_option('notion_content_style_img', '');
    $col_div_wrapper_style = get_option('notion_content_style_column_div_wrapper', '');
    $col_div_style = get_option('notion_content_style_column_div', '');
    $col_table_style = get_option('notion_content_style_column_table', '');
    $col_row_style = get_option('notion_content_style_column_row', '');
    $col_col_style = get_option('notion_content_style_column_col', '');

    $custom_css = get_option('notion_content_custom_css', '');



    include NOTION_CONTENT_PLUGIN_PATH . 'includes/admin-header.php';

    // Display the tab interface
    ?>
    <div class="wrap" id="notion-content-plugin-admin">
        <h1>Notion Content Classes / Styles</h1>
        
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
                        <th scope="row"><label for="paragraph_style">Paragraph (p)</label></th>
                        <td><input type="text" id="paragraph_style" name="paragraph_style" value="<?php echo esc_attr($paragraph_style); ?>" style="width: 100%;">
                            <?php if($paragraph_style) : ?>
                            <br><small>Result: &lt;p class="<?php echo $paragraph_style; ?>"&gt;</small>
                            <?php endif; ?>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><label for="heading1_style">Heading 1 (h1)</label></th>
                        <td><input type="text" id="heading1_style" name="heading1_style" value="<?php echo esc_attr($heading1_style); ?>" style="width: 100%;">
                            <?php if($heading1_style) : ?>
                            <br><small>Result: &lt;h1 class="<?php echo $heading1_style; ?>"&gt;</small>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="heading2_style">Heading 2 (h2)</label></th>
                        <td><input type="text" id="heading2_style" name="heading2_style" value="<?php echo esc_attr($heading2_style); ?>" style="width: 100%;">
                            <?php if($heading2_style) : ?>
                            <br><small>Result: &lt;h2 class="<?php echo $heading2_style; ?>"&gt;</small>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="heading3_style">Heading 3 (h3)</label></th>
                        <td><input type="text" id="heading3_style" name="heading3_style" value="<?php echo esc_attr($heading3_style); ?>" style="width: 100%;">
                            <?php if($heading3_style) : ?>
                            <br><small>Result: &lt;h3 class="<?php echo $heading3_style; ?>"&gt;</small>
                            <?php endif; ?>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><label for="table_style">Table (table)</label></th>
                        <td><input type="text" id="table_style" name="table_style" value="<?php echo esc_attr($table_style); ?>" style="width: 100%;">
                            <?php if($table_style) : ?>
                            <br><small>Result: &lt;table class="<?php echo $table_style; ?>"&gt;</small>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="table_style">Table Row (tr)</label></th>
                        <td><input type="text" id="row_style" name="row_style" value="<?php echo esc_attr($row_style); ?>" style="width: 100%;">
                            <?php if($row_style) : ?>
                            <br><small>Result: &lt;tr class="<?php echo $row_style; ?>"&gt;</small>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="table_style">Table Column (td)</label></th>
                        <td><input type="text" id="col_style" name="col_style" value="<?php echo esc_attr($col_style); ?>" style="width: 100%;">
                            <?php if($col_style) : ?>
                            <br><small>Result: &lt;td class="<?php echo $col_style; ?>"&gt;</small>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="ul_style">Unordered List (ul)</label></th>
                        <td><input type="text" id="ul_style" name="ul_style" value="<?php echo esc_attr($ul_style); ?>" style="width: 100%;">
                            <?php if($ul_style) : ?>
                            <br><small>Result: &lt;ul class="<?php echo $ul_style; ?>"&gt;</small>
                            <?php endif; ?>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><label for="li_style">List Item (li)</label></th>
                        <td><input type="text" id="li_style" name="li_style" value="<?php echo esc_attr($li_style); ?>" style="width: 100%;">
                            <?php if($li_style) : ?>
                            <br><small>Result: &lt;li class="<?php echo $li_style; ?>"&gt;</small>
                            <?php endif; ?>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><label for="hr_style">Separator (hr)</label></th>
                        <td><input type="text" id="hr_style" name="hr_style" value="<?php echo esc_attr($hr_style); ?>" style="width: 100%;">
                            <?php if($hr_style) : ?>
                            <br><small>Result: &lt;hr class="<?php echo $hr_style; ?>"&gt;</small>
                            <?php endif; ?>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><label for="img_style">Image (img)</label></th>
                        <td><input type="text" id="img_style" name="img_style" value="<?php echo esc_attr($img_style); ?>" style="width: 100%;">
                            <?php if($img_style) : ?>
                            <br><small>Result: &lt;img class="<?php echo $hr_style; ?>"&gt;</small>
                            <?php endif; ?>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><label for="quote_style">Block Quote (blockquote)</label></th>

                        <td><input type="text" id="quote_style" name="quote_style" value="<?php echo esc_attr($quote_style); ?>" style="width: 100%;">
                            <?php if($quote_style) : ?>
                            <br><small>Result: &lt;blockquote class="<?php echo $quote_style; ?>"&gt;</small>
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
                            <br><small>Result: &lt;div class="<?php echo $col_div_wrapper_style; ?>"&gt; <?php endif; ?>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><label for="col_div_style">Div (div)</label></th>
                        <td><input type="text" id="col_div_style" name="col_div_style" value="<?php echo esc_attr($col_div_style); ?>" style="width: 100%;">
                            <?php if($col_div_style) : ?>
                            <br><small>Result: &lt;div class="<?php echo $col_div_style; ?>"&gt; </small>
                            <?php endif; ?>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><label for="col_table_style">Table (table)</label></th>
                        <td><input type="text" id="col_table_style" name="col_table_style" value="<?php echo esc_attr($col_table_style); ?>" style="width: 100%;">
                            <?php if($col_table_style) : ?>
                            <br><small>Result: &lt;table class="<?php echo $col_table_style; ?>"&gt; </small>
                            <?php endif; ?>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><label for="col_row_style">Table Row (tr)</label></th>
                        <td><input type="text" id="col_row_style" name="col_row_style" value="<?php echo esc_attr($col_row_style); ?>" style="width: 100%;">
                            <?php if($col_row_style) : ?>
                            <br><small>Result: &lt;tr class="<?php echo $col_row_style; ?>"&gt; </small>
                            <?php endif; ?>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><label for="col_col_style">Table Column (td)</label></th>
                        <td><input type="text" id="col_col_style" name="col_col_style" value="<?php echo esc_attr($col_col_style); ?>" style="width: 100%;">
                            <?php if($col_col_style) : ?>
                            <br><small>Result: &lt;tr class="<?php echo $col_col_style; ?>"&gt; </small>
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
