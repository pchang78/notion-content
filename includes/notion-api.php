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
function notion_get_page_content($api_key, $page_id) {
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
    
    if (isset($body['results'])) {
        $content = '';
        foreach ($body['results'] as $block) {
            $content .= notion_render_block($block);
        }
        return $content;
    }

    return new WP_Error('notion_api_error', 'Could not retrieve page content from Notion.');
}

// Render individual block types as HTML
function notion_render_block($block) {
    $html = '';
    
    switch ($block['type']) {
        case 'paragraph':
            $text = notion_get_text($block['paragraph']['rich_text']);
            $html = "<p>$text</p>";
            break;
        
        case 'heading_1':
            $text = notion_get_text($block['heading_1']['rich_text']);
            $html = "<h1>$text</h1>";
            break;

        case 'heading_2':
            $text = notion_get_text($block['heading_2']['rich_text']);
            $html = "<h2>$text</h2>";
            break;

        case 'heading_3':
            $text = notion_get_text($block['heading_3']['rich_text']);
            $html = "<h3>$text</h3>";
            break;

        case 'bulleted_list_item':
            $text = notion_get_text($block['bulleted_list_item']['rich_text']);
            $html = "<li>$text</li>";
            break;

        case 'numbered_list_item':
            $text = notion_get_text($block['numbered_list_item']['rich_text']);
            $html = "<li>$text</li>";
            break;

        case 'to_do':
            $text = notion_get_text($block['to_do']['rich_text']);
            $checked = $block['to_do']['checked'] ? 'checked' : '';
            $html = "<p><input type='checkbox' $checked disabled> $text</p>";
            break;

            /*
        case 'toggle':
            $text = notion_get_text($block['toggle']['rich_text']);
            $html = "<details><summary>$text</summary></details>";
            break;
            */

        case 'quote':
            $text = notion_get_text($block['quote']['rich_text']);
            $html = "<blockquote>$text</blockquote>";
            break;

        default:
            $html = "<p>[Unsupported block type: {$block['type']}]</p>";
            break;
    }
    return $html . "\n";
}


// Helper function to extract and concatenate text with rich formatting
function notion_get_text($rich_text_array) {
    $formatted_text = '';
    foreach ($rich_text_array as $text_block) {
        $text = esc_html($text_block['plain_text']); // Escape HTML characters

        // Apply formatting based on text_block annotations
        $annotations = $text_block['annotations'];

        if ($annotations['bold']) {
            $text = "<strong>$text</strong>";
        }
        if ($annotations['italic']) {
            $text = "<em>$text</em>";
        }
        if ($annotations['underline']) {
            $text = "<u>$text</u>";
        }

        // Append formatted text to result
        $formatted_text .= $text;
    }
    return $formatted_text;
}




function notion_content_refresh() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'notion_content';

    // Set all pages to inactive
    $wpdb->update($table_name, ['is_active' => 0], ['is_active' => 1]);

    $api_key = get_option('notion_api_key');
    $database_url = get_option('notion_database_url');
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

        // Check if page exists in database
        $existing_page = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE page_id = %s", $page_id), ARRAY_A);

        if ($existing_page) {
            // Update existing page
            $wpdb->update(
                $table_name,
                ['title' => $title, 'content' => $content, 'is_active' => 1],
                ['page_id' => $page_id]
            );
        } else {
            // Insert new page
            $wpdb->insert($table_name, [
                'page_id' => $page_id,
                'title' => $title,
                'content' => $content,
                'is_active' => 1
            ]);
        }
    }
}


function notion_content_refresh_single_page($page_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'notion_content';

    // Retrieve API key
    $api_key = get_option('notion_api_key');
    if (!$api_key) {
        return new WP_Error('notion_content_error', 'API Key is missing.');
    }

    // Fetch page content and title from Notion
    $content = notion_get_page_content($api_key, $page_id);
    if (is_wp_error($content)) {
        return $content;
    }
    $page_title = notion_get_page_title($api_key, $page_id);

    // Check if the page already exists in the database
    $existing_entry = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table_name WHERE page_id = %s",
        $page_id
    ));

    if ($existing_entry > 0) {
        // Update existing row
        $wpdb->update(
            $table_name,
            [
                'title' => $page_title,
                'content' => $content,
                'is_active' => 1,
                'last_updated' => current_time('mysql')  // Update last_updated to current time
            ],
            ['page_id' => $page_id],
            ['%s', '%s', '%d', '%s'],
            ['%s']
        );
    } else {
        // Insert new row if page does not exist
        $wpdb->insert(
            $table_name,
            [
                'page_id' => $page_id,
                'title' => $page_title,
                'content' => $content,
                'is_active' => 1,
                'last_updated' => current_time('mysql')
            ],
            ['%s', '%s', '%s', '%d', '%s']
        );
    }
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
        return $response;
    }

    $body = json_decode(wp_remote_retrieve_body($response), true);

    // Check if the page has a title property
    if (isset($body['properties'])) {
        foreach ($body['properties'] as $property) {
            if ($property['type'] === 'title' && isset($property['title'][0]['plain_text'])) {
                return esc_html($property['title'][0]['plain_text']);
            }
        }
    }

    return 'Untitled Page'; // Default if no title is found
}
?>
