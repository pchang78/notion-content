<?php

// Extract Database ID from URL
function notion_extract_database_id($url) {
    if (preg_match('/([a-f0-9]{32})/', $url, $matches)) {
        return $matches[1];
    }
    return false;
}

// Fetch pages from Notion API
function notion_get_pages($api_key, $database_id) {
    $url = "https://api.notion.com/v1/databases/$database_id/query";
    $response = wp_remote_post($url, [
        'headers' => [
            'Authorization' => 'Bearer ' . $api_key,
            'Content-Type' => 'application/json',
            'Notion-Version' => '2022-06-28'
        ]
    ]);

    if (is_wp_error($response)) {
        return $response;
    }

    $body = json_decode(wp_remote_retrieve_body($response), true);
    
    if (isset($body['results'])) {
        $pages = [];
        foreach ($body['results'] as $result) {
            $title = $result['properties']['Name']['title'][0]['plain_text'] ?? 'Untitled';
            $page_id = $result['id'];
            $pages[] = ['title' => $title, 'id' => $page_id];
        }
        return $pages;
    }

    return new WP_Error('notion_api_error', 'Could not retrieve pages from Notion.');
}

// Fetch and render individual Notion page content as HTML
function notion_get_page_content($api_key, $page_id, $debug = false) {
    $url = "https://api.notion.com/v1/blocks/$page_id/children";
    $response = wp_remote_get($url, [
        'headers' => [
            'Authorization' => 'Bearer ' . $api_key,
            'Notion-Version' => '2022-06-28'
        ]
    ]);

    if (is_wp_error($response)) {
        return $response;
    }

    $body = json_decode(wp_remote_retrieve_body($response), true);
    $bulleted_list_item = false;
    $numbered_list_item = false;
    
    if (isset($body['results'])) {
        $content = '';
        foreach ($body['results'] as $block) {

            $extra = "";
            switch($block['type']) {
                case "bulleted_list_item":
                    if(!$bulleted_list_item) {
                        $bulleted_list_item = true;
                        $extra = "open";
                    }
                    break;

                case "numbered_list_item":
                    if(!$numbered_list_item) {
                        $numbered_list_item = true;
                        $extra = "open";
                    }
                    break;

                default: 

                    if($bulleted_list_item) {
                        $bulleted_list_item = false;
                        $content .= "</ul>";
                    }
                    if($numbered_list_item) {
                        $numbered_list_item = false;
                        $content .= "</ol>";
                    }
                    break;
            }
            $content .= notion_render_block($block, $api_key, $extra);
        }

        if($bulleted_list_item) {
            $bulleted_list_item = false;
            $content .= "</ul>";
        }
        if($numbered_list_item) {
            $numbered_list_item = false;
            $content .= "</ol>";
        }



        return $content;
    }

    return new WP_Error('notion_api_error', 'Could not retrieve page content from Notion.');
}

// Render individual block types as HTML
function notion_render_block($block, $api_key, $extra = "") {
    $html = '';
    $blockID = trim(str_replace("-", "", $block['id']));
    
    switch ($block['type']) {

        // Paragraph block
        case 'paragraph':
            $text = notion_get_text($block['paragraph']['rich_text']);
            $paragraph_style = get_option('notion_content_style_paragraph', '');
            if(isset($paragraph_style) && $paragraph_style) {
                $html = "<p class='$paragraph_style'>$text</p>";
            }
            else {
                $html = "<p>$text</p>";
            }
            break;

        // Heading 1 block
        case 'heading_1':
            $text = notion_get_text($block['heading_1']['rich_text']);
            $heading1_style = get_option('notion_content_style_heading1', '');
            if(isset($heading1_style) && $heading1_style) {
                $html = "<h1 class='$heading1_style'>$text</h1>";
            }
            else {
                $html = "<h1>$text</h1>";
            }
            break;

        // Heading 2 block
        case 'heading_2':
            $text = notion_get_text($block['heading_2']['rich_text']);
            $heading2_style = get_option('notion_content_style_heading2', '');
            if(isset($heading2_style) && $heading2_style) {
                $html = "<h2 class='$heading2_style'>$text</h2>";
            }
            else {
                $html = "<h2>$text</h2>";
            }
            break;

        // Heading 3 block
        case 'heading_3':
            $text = notion_get_text($block['heading_3']['rich_text']);
            $heading3_style = get_option('notion_content_style_heading3', '');
            if(isset($heading3_style) && $heading3_style) {
                $html = "<h3 class='$heading3_style'>$text</h3>";
            }
            else {
                $html = "<h3>$text</h3>";
            }
            break;

        // Bulleted list item block
        case 'bulleted_list_item':
            $text = notion_get_text($block['bulleted_list_item']['rich_text']);
            $li_style = get_option('notion_content_style_li', '');
            if(isset($li_style) && $li_style) {
                $html = "<li class='$li_style'>$text";
            }
            else {
                $html = "<li>$text";
            }

            // Check for nested blocks
            if(isset($block["has_children"]) && $block["has_children"]) {
                $html .= notion_get_page_content($api_key, $blockID);
            }
            $html .= "</li>";

            if($extra == "open") {
                $ul_style = get_option('notion_content_style_ul', '');
                if(isset($ul_style) && $ul_style) {
                    $html = "<ul class='$ul_style'>$html";
                }
                else {
                    $html = "<ul>$html";
                }
            }
            break;

        // Numbered list item block
        case 'numbered_list_item':
            $text = notion_get_text($block['numbered_list_item']['rich_text']);
            $html = "<li>$text</li>";
            if($extra == "open") {
                $html = "<ol>$html";
            }
            break;

        // To do block
        case 'to_do':
            $text = notion_get_text($block['to_do']['rich_text']);
            $checked = $block['to_do']['checked'] ? 'checked' : '';
            $html = "<p><input type='checkbox' $checked disabled> $text</p>";
            break;

        // Toggle block
        case 'toggle':
            $toggle_content = "";
            $toggle_content = notion_get_page_content($api_key, $blockID);
            $text = notion_get_text($block['toggle']['rich_text']);
            $html = "<details><summary>$text</summary>$toggle_content</details>";
            break;

        // Quote block
        case 'quote':
            $text = notion_get_text($block['quote']['rich_text']);
            $quote_style = get_option('notion_content_style_quote', '');
            if(isset($quote_style) && $quote_style) {
                $html = "<blockquote class='$quote_style'>$text</blockquote>";
            }
            else {
                $html = "<blockquote>$text</blockquote>";
            }
            break;

        // Divider block
        case 'divider':
            $hr_style = get_option('notion_content_style_hr', '');
            if(isset($hr_style) && $hr_style) {
                $html = "<hr class='$hr_style'>";
            }
            else {
                $html = "<hr>";
            }
            break;

        // Table block
        case 'table':
            $table_style = get_option('notion_content_style_table', '');
            if(isset($table_style) && $table_style) {
                $html = "<table class='$table_style'>\n";
            }
            else {
                $html = "<table>\n";
            }
            $table_content = notion_get_page_content($api_key, $blockID);
            $html .= $table_content;
            $html .= "</table>";
            break;

        // Table row block
        case 'table_row':
            $row_style = get_option('notion_content_style_row', '');
            if(isset($row_style) && $row_style) {
                $html = "<tr class='$row_style'>";
            }
            else {
                $html = "<tr>";
            }
            $html .= notion_get_table_cells($block['table_row']['cells']);
            $html .= "</tr>";
            break;

        // Image block
        case 'image':
            $attachment_id = notion_handle_image($block['id'], $block['image']['file']['url']);
            if (is_wp_error($attachment_id)) {
                // Return a placeholder or fallback image
                $html = "<p>[Image could not be loaded]</p>";
            } else {
                $image_size = get_option('notion_content_image_size');
                if(!isset($image_size) || !$image_size || $image_size == "full") {
                    $image_url = wp_get_attachment_url($attachment_id);
                } else {
                    $image_src = wp_get_attachment_image_src($attachment_id, $image_size);
                    $image_url = $image_src[0];
                }
                $img_style = get_option('notion_content_style_img', '');
                if(isset($img_style) && $img_style) {
                    $html = "<img class='$img_style' src='$image_url'>";
                } else {
                    $html = "<img src='$image_url'>";
                }
            }
            break;

        // Column list block
        case "column_list":
            $column_tag = get_option('notion_content_column_tag');
            if($column_tag == 'div') {

                $col_div_wrapper_style = get_option('notion_content_style_column_div_wrapper', '');
                if(isset($col_div_wrapper_style) && $col_div_wrapper_style) {
                    $html = "<div class='$col_div_wrapper_style'>";
                }
                else {
                    $html = "<div>";
                }
            }
            else {
                $col_table_style = get_option('notion_content_style_column_table', '');
                if(isset($col_table_style) && $col_table_style) {
                    $html = "<table class='$col_table_style'>";
                }
                else {
                    $html = "<table>";
                }

                $col_row_style = get_option('notion_content_style_column_row', '');
                if(isset($col_row_style) && $col_row_style) {
                    $html .= "<tr class='$col_row_style'>";
                }
                else {
                    $html .= "<tr>";
                }
            }
            $html .= notion_get_page_content($api_key, $blockID);
            if($column_tag == 'div') {
                $html .= "</div>";
            }
            else {
                $html .= "</tr></table>";
            }
            break;

        // Column block
        case "column":
            $column_tag = get_option('notion_content_column_tag');
            if($column_tag == 'div') {
                $col_div_style = get_option('notion_content_style_column_div', '');
                if(isset($col_div_style) && $col_div_style) {
                    $html = "<div class='$col_div_style'>";
                }
                else {
                    $html = "<div>";
                }
            }
            else {
                $col_col_style = get_option('notion_content_style_column_col', '');
                if(isset($col_col_style) && $col_col_style) {
                    $html = "<td class='$col_col_style'>";
                }
                else {
                    $html = "<td>";
                }
            }
            $html .= notion_get_page_content($api_key, $blockID);
            if($column_tag == 'div') {
                $html .= "</div>";
            }
            else {
                $html .= "</td>";
            }
            break;

        default:
            $html = "<p>[Unsupported block type: {$block['type']}]</p>";
            break;
    }
    return $html . "\n";
}

// Get table cells for a given row
function notion_get_table_cells($table_cells) {
    $text = '';
    foreach($table_cells AS $cell) {
        $col_style = get_option('notion_content_style_col', '');
        if(isset($col_style) && $col_style) {
            $text .= '
                <td class="'. $col_style . '">';
        }
        else {
            $text .= '
                <td>';
        }
        $text .= notion_get_text($cell, true);
        $text .= '</td>';
    }
    return $text;
}

// Get text from a rich text array
function notion_get_text($rich_text_array, $add_breaks = false) {
    $text = '';
    foreach ($rich_text_array as $rich_text) {
        $plain_text = esc_html($rich_text['plain_text']);
        
        // Check if there's a link in the text
        if (isset($rich_text['href']) && !empty($rich_text['href'])) {
            $url = esc_url($rich_text['href']);
            $plain_text = "<a href=\"$url\" target=\"_blank\">$plain_text</a>";
        }
        // Apply text styling (bold, italic, underline)
        if (isset($rich_text['annotations'])) {
            $annotations = $rich_text['annotations'];
            if ($annotations['bold']) {
                $plain_text = "<strong>$plain_text</strong>";
            }
            if ($annotations['italic']) {
                $plain_text = "<em>$plain_text</em>";
            }
            if ($annotations['underline']) {
                $plain_text = "<u>$plain_text</u>";
            }
        }
        // Append to the final text
        $text .= $plain_text;
    }
    if($add_breaks) {
        $text = str_replace("\n", "<br>", $text);
    }

    return $text;
}

// Refresh all pages from Notion
function notion_content_refresh() {
    // Set all Notion posts to draft initially
    $notion_posts = get_posts(array(
        'post_type' => 'notion_content',
        'meta_key' => 'notion_page_id',
        'posts_per_page' => -1
    ));
    
    foreach ($notion_posts as $post) {
        $update_result = wp_update_post(array(
            'ID' => $post->ID,
            'post_status' => 'draft'
        ), true); // Add true parameter to return WP_Error on failure

        if (is_wp_error($update_result)) {
            continue; // Skip if update fails
        }
    }

    $api_key = get_option('notion_content_api_key');
    $database_url = get_option('notion_content_database_url');
    $database_id = notion_extract_database_id($database_url);

    if (!$api_key || !$database_id) {
        return new WP_Error('notion_content_error', 'API Key or Database ID is missing.');
    }

    $pages = notion_get_pages($api_key, $database_id);

    if (is_wp_error($pages)) {
        return $pages;
    }

    foreach ($pages as $page) {
        $page_id = $page['id'];
        $title = $page['title'];
        $content = notion_get_page_content($api_key, $page_id);

        if (is_wp_error($content)) {
            // Skip this page if there's an error fetching content
            continue;
        }

        // Find any post with this notion_page_id
        $existing_posts = get_posts(array(
            'post_type' => 'notion_content',
            'meta_query' => array(
                array(
                    'key' => 'notion_page_id',
                    'value' => $page_id
                )
            ),
            'posts_per_page' => 1,
            'post_status' => 'any'
        ));

        if (!empty($existing_posts)) {
            $post = $existing_posts[0];
            // Update existing post
            $update_result = wp_update_post(array(
                'ID' => $post->ID,
                'post_title' => $title,
                'post_content' => $content,
                'post_status' => 'publish',
                'post_type' => 'notion_content'
            ), true); // Add true parameter to return WP_Error on failure

            if (is_wp_error($update_result)) {
                continue; // Skip to next page if update fails
            }
            
            // Update meta values
            update_post_meta($post->ID, 'notion_page_id', $page_id);
        } else {
            // Insert new post only if no existing post was found
            $post_id = wp_insert_post(array(
                'post_title' => $title,
                'post_content' => $content,
                'post_status' => 'publish',
                'post_type' => 'notion_content'
            ), true); // Add true parameter to return WP_Error on failure

            if (is_wp_error($post_id)) {
                continue; // Skip to next page if insert fails
            }

            // Add Notion metadata
            add_post_meta($post_id, 'notion_page_id', $page_id);
        }
    }

    return true;
}

// Refresh a single page from Notion
function notion_content_refresh_single_page($page_id) {
    $api_key = get_option('notion_content_api_key');
    if (!$api_key) {
        return new WP_Error('notion_content_error', 'API Key is missing.');
    }

    // Fetch page content and title from Notion
    $content = notion_get_page_content($api_key, $page_id);
    if (is_wp_error($content)) {
        return $content;
    }
    
    $page_title = notion_get_page_title($api_key, $page_id);
    if (is_wp_error($page_title)) {
        $page_title = 'Untitled Page'; // Provide a default title if there's an error
    }

    // Find any post with this notion_page_id
    $existing_posts = get_posts(array(
        'post_type' => 'notion_content',
        'meta_query' => array(
            array(
                'key' => 'notion_page_id',
                'value' => $page_id
            )
        ),
        'posts_per_page' => 1,
        'post_status' => 'any'
    ));

    if (!empty($existing_posts)) {
        $post = $existing_posts[0];
        // Update existing post
        $update_result = wp_update_post(array(
            'ID' => $post->ID,
            'post_title' => $page_title,
            'post_content' => $content,
            'post_status' => 'publish',
            'post_type' => 'notion_content',
            'post_modified' => current_time('mysql'),
            'post_modified_gmt' => current_time('mysql', true)
        ), true); // Add true parameter to return WP_Error on failure

        if (is_wp_error($update_result)) {
            return $update_result;
        }
        
        // Update meta values
        update_post_meta($post->ID, 'notion_page_id', $page_id);
    } else {
        // Insert new post only if no existing post was found
        $post_id = wp_insert_post(array(
            'post_title' => $page_title,
            'post_content' => $content,
            'post_status' => 'publish',
            'post_type' => 'notion_content'
        ), true); // Add true parameter to return WP_Error on failure

        if (is_wp_error($post_id)) {
            return $post_id;
        }

        // Add Notion metadata
        add_post_meta($post_id, 'notion_page_id', $page_id);
    }

    return true;
}


// Function to get the title of a single Notion page
function notion_get_page_title($api_key, $page_id) {
    $url = "https://api.notion.com/v1/pages/$page_id";

    $response = wp_remote_get($url, [
        'headers' => [
            'Authorization' => "Bearer $api_key",
            'Notion-Version' => '2022-06-28'
        ]
    ]);
    
    if (is_wp_error($response)) {
        return 'Error Fetching Title'; // Return a string instead of WP_Error
    }

    $body = json_decode(wp_remote_retrieve_body($response), true);

    // Check if the page has properties and title
    if (isset($body['properties']) && isset($body['properties']['Name'])) {
        $title_property = $body['properties']['Name'];
        if (isset($title_property['title'][0]['plain_text'])) {
            return esc_html($title_property['title'][0]['plain_text']);
        }
    }

    return 'Untitled Page'; // Default if no title is found
}

// Handle image
function notion_handle_image($object_id, $image_url) {
    global $wp_filesystem;

    // Initialize WP_Filesystem
    if (!function_exists('WP_Filesystem')) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
    }
    WP_Filesystem();

    // Check if there's an existing notion_images post for this object_id
    $existing_images = get_posts(array(
        'post_type' => 'notion_images',
        'meta_query' => array(
            array(
                'key' => 'notion_object_id',
                'value' => $object_id
            )
        ),
        'posts_per_page' => 1
    ));

    // Check if the file exists in the WordPress Media Library
    if (!empty($existing_images)) {
        $image_post = $existing_images[0];
        $post_id = get_post_meta($image_post->ID, 'attachment_id', true);

        // Check if attachment exists in the posts table
        $attachment = get_post($post_id);

        // Verify the file still exists in the upload directory
        $file_path = get_attached_file($post_id);

        if ($attachment && file_exists($file_path)) {
            // File and attachment exist, return the post_id
            return $post_id;
        }

        // If the attachment or file doesn't exist, delete the notion_images post
        wp_delete_post($image_post->ID, true);
    }

    // Use `download_url` to download the image
    $temp_file = download_url($image_url);

    if (is_wp_error($temp_file)) {
        return new WP_Error('image_download_failed', 'Failed to download the image.');
    }

    // Move the downloaded file to the WordPress upload directory
    $uploads_dir = wp_upload_dir();
    $parsed_url = wp_parse_url($image_url);
    $filename = basename($parsed_url['path']); // Extract just the filename from the URL path

    // Get file info
    $path_parts = pathinfo($filename);
    $name = $path_parts['filename'];
    $ext = $path_parts['extension'];

    // Create unique filename
    $counter = 1;
    $destination = $uploads_dir['path'] . '/' . $filename;
    
    while (file_exists($destination)) {
        $filename = $name . '-' . $counter . '.' . $ext;
        $destination = $uploads_dir['path'] . '/' . $filename;
        $counter++;
    }

    if (!$wp_filesystem->move($temp_file, $destination)) {
        wp_delete_file($temp_file); // Clean up temp file if move fails
        return new WP_Error('file_move_failed', 'Failed to move the downloaded file to the upload directory.');
    }

    // Add the image to the WordPress Media Library
    $attachment = array(
        'post_mime_type' => wp_check_filetype($filename)['type'],
        'post_title' => sanitize_file_name($filename),
        'post_content' => '',
        'post_status' => 'inherit',
    );

    $attachment_id = wp_insert_attachment($attachment, $destination);
    require_once ABSPATH . 'wp-admin/includes/image.php';

    // Generate attachment metadata
    $attachment_data = wp_generate_attachment_metadata($attachment_id, $destination);
    wp_update_attachment_metadata($attachment_id, $attachment_data);

    // Create a new notion_images post
    $notion_image_post = wp_insert_post(array(
        'post_type' => 'notion_images',
        'post_status' => 'publish',
        'post_title' => $object_id // Using object_id as the title for reference
    ));

    // Add meta values
    update_post_meta($notion_image_post, 'notion_object_id', $object_id);
    update_post_meta($notion_image_post, 'attachment_id', $attachment_id);

    // Return the attachment_id
    return $attachment_id;
}
