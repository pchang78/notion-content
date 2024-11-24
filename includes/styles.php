<?php

function notion_content_styles_page() {
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
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

    $custom_css = get_option('notion_content_custom_css', '');

    // Display the tab interface
    ?>
    <div class="wrap" id="notion-content-plugin-admin">
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
                        <th scope="row"><label for="paragraph_style">Paragraph Style (p)</label></th>
                        <td><input type="text" id="paragraph_style" name="paragraph_style" value="<?php echo esc_attr($paragraph_style); ?>" style="width: 100%;">
                            <?php if($paragraph_style) : ?>
                            <br><small>Result: &lt;p class="<?php echo $paragraph_style; ?>"&gt;
                            <?php endif; ?>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><label for="heading1_style">Heading 1 Style (h1)</label></th>
                        <td><input type="text" id="heading1_style" name="heading1_style" value="<?php echo esc_attr($heading1_style); ?>" style="width: 100%;">
                            <?php if($heading1_style) : ?>
                            <br><small>Result: &lt;h1 class="<?php echo $heading1_style; ?>"&gt;
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="heading2_style">Heading 2 Style (h2)</label></th>
                        <td><input type="text" id="heading2_style" name="heading2_style" value="<?php echo esc_attr($heading2_style); ?>" style="width: 100%;">
                            <?php if($heading2_style) : ?>
                            <br><small>Result: &lt;h2 class="<?php echo $heading2_style; ?>"&gt;
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="heading3_style">Heading 3 Style (h3)</label></th>
                        <td><input type="text" id="heading3_style" name="heading3_style" value="<?php echo esc_attr($heading3_style); ?>" style="width: 100%;">
                            <?php if($heading3_style) : ?>
                            <br><small>Result: &lt;h3 class="<?php echo $heading3_style; ?>"&gt;
                            <?php endif; ?>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><label for="table_style">Table Style (table)</label></th>
                        <td><input type="text" id="table_style" name="table_style" value="<?php echo esc_attr($table_style); ?>" style="width: 100%;">
                            <?php if($table_style) : ?>
                            <br><small>Result: &lt;table class="<?php echo $table_style; ?>"&gt;
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="table_style">Table Row Style (tr)</label></th>
                        <td><input type="text" id="row_style" name="row_style" value="<?php echo esc_attr($row_style); ?>" style="width: 100%;">
                            <?php if($row_style) : ?>
                            <br><small>Result: &lt;tr class="<?php echo $row_style; ?>"&gt;
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="table_style">Table Column Style (td)</label></th>
                        <td><input type="text" id="col_style" name="col_style" value="<?php echo esc_attr($col_style); ?>" style="width: 100%;">
                            <?php if($col_style) : ?>
                            <br><small>Result: &lt;td class="<?php echo $col_style; ?>"&gt;
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="ul_style">Unordered List Style (ul)</label></th>
                        <td><input type="text" id="ul_style" name="ul_style" value="<?php echo esc_attr($ul_style); ?>" style="width: 100%;">
                            <?php if($ul_style) : ?>
                            <br><small>Result: &lt;ul class="<?php echo $ul_style; ?>"&gt;
                            <?php endif; ?>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><label for="li_style">List Item Style (li)</label></th>
                        <td><input type="text" id="li_style" name="li_style" value="<?php echo esc_attr($li_style); ?>" style="width: 100%;">
                            <?php if($li_style) : ?>
                            <br><small>Result: &lt;li class="<?php echo $li_style; ?>"&gt;
                            <?php endif; ?>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><label for="hr_style">Separator Style (hr)</label></th>
                        <td><input type="text" id="hr_style" name="hr_style" value="<?php echo esc_attr($hr_style); ?>" style="width: 100%;">
                            <?php if($hr_style) : ?>
                            <br><small>Result: &lt;hr class="<?php echo $hr_style; ?>"&gt;
                            <?php endif; ?>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><label for="img_style">Image Style (img)</label></th>
                        <td><input type="text" id="img_style" name="img_style" value="<?php echo esc_attr($img_style); ?>" style="width: 100%;">
                            <?php if($img_style) : ?>
                            <br><small>Result: &lt;img class="<?php echo $hr_style; ?>"&gt;
                            <?php endif; ?>
                        </td>
                    </tr>


                    <tr>
                        <th scope="row"><label for="quote_style">Block Quote Style (blockquote)</label></th>
                        <td><input type="text" id="quote_style" name="quote_style" value="<?php echo esc_attr($quote_style); ?>" style="width: 100%;">
                            <?php if($quote_style) : ?>
                            <br><small>Result: &lt;blockquote class="<?php echo $quote_style; ?>"&gt;
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
