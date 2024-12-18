<?php
/* This file is used to handle the setup of the Notion Content plugin.  If the plugin does not have a valid API key and database URL, it will display a setup page.
*/

// Check if the plugin is setup
function notion_content_is_setup() {
    $notion_content_api_key = esc_attr(get_option('notion_content_api_key'));
    $notion_content_database_url = esc_attr(get_option('notion_content_database_url'));
    if( isset($notion_content_api_key) && $notion_content_api_key && isset($notion_content_database_url) && $notion_content_database_url) {
        return true;
    }
    else {
        return false;
    }
}


// Check to see if ID is a page or database
function notion_content_check_notion_config($api_key, $pageID) {

    // Check to see if ID is a page
    $results = array();
    $url = "https://api.notion.com/v1/pages/$pageID";
    $response = wp_remote_get(
        $url,
        [
            'headers' => [
                'Authorization' => "Bearer $api_key",
                'Notion-Version' => '2022-06-28',
            ],
        ]
    );

    // Is a page
    if($response["response"]["code"] == 200) {

        $url = "https://api.notion.com/v1/blocks/$pageID/children";
        $response = wp_remote_get($url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $api_key,
                'Notion-Version' => '2022-06-28'
            ]
        ]);
        $body = json_decode(wp_remote_retrieve_body($response), true);
        $results["url_type"] = "Page";
        $results["databases"] = array();
        // Look for Databases
        foreach ($body['results'] as $result) {
            if($result["type"] == "child_database") {
                $arrDB = array();
                $arrDB["id"] = str_replace("-", "", $result["id"]);
                $arrDB["name"] = $result["child_database"]["title"];
                $results["databases"][] = $arrDB;
            }
        }
    }
    else {

        // Check to see if the ID is a database.
        $url = "https://api.notion.com/v1/databases/$pageID";
        $response = wp_remote_get($url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $api_key,
                'Notion-Version' => '2022-06-28'
            ]
        ]);
        if($response["response"]["code"] == "200") {
            $results["url_type"] = "Database";
        }
        else {
            // The ID is either not a database, an invalid ID, or has not been integrated with the API key
            $results["url_type"] = "Not Found";
        }
    }

    return $results;
}

// Display the setup page
function notion_content_setup_page() {
    $page = "";
    if(isset($_POST["notion_content_check_config"]) && $_POST["notion_content_check_config"]) {
        $api_key = $_POST["notion_content_api_key"];
        $pageID = notion_extract_database_id($_POST["notion_content_database_url"]);
        $results = notion_content_check_notion_config($api_key, $pageID);
        switch($results["url_type"]) {
            case "Page":
                // Check to see if there are child databases and then list them.  If there are not, then give an error message.  
                if(count($results["databases"]) > 0) {
                    // Show list of databases
                    $page = "database";

                }
                else {
                    $msg = "No databases found on the given URL";
                }
                break;
            case "Database":
                // Success!  Save API Key and Database URL into database
                $page = "success";
                update_option('notion_content_api_key', sanitize_text_field($_POST['notion_content_api_key']));
                update_option('notion_content_database_url', sanitize_text_field($_POST['notion_content_database_url']));



                break;
            case "Not Found":
                $msg = "There was an error finding the database.  Please check the API Key and the URL.  Make sure that the Notion Database has been integrated with the API key.";
                break;
        }
    }
    include NOTION_CONTENT_PLUGIN_PATH . 'includes/admin-header.php';

    ?>

    <?php if(isset($msg) && $msg) : ?>
    <div class="notice notice-error is-dismissible">
        <p><?php print_f(__('%1', 'notion-content'), $msg); ?></p>
    </div>
    <?php endif; ?>

    <div class="wrap" id="notion-content-plugin-admin">
        <h1>Notion Content Setup</h1>
        <?php 
        switch($page) {

            case "success":
                notion_content_setup_page_success(); 
                break;

            case "database":
                notion_content_setup_page_choose_database($results["databases"]); 
                break;

            default:
                notion_content_setup_page_form(); 
                break;
        }
        ?>
    </div>
<?php
}

// Display the setup page form
function notion_content_setup_page_form() {
    $api_key = "";
    if(isset($_POST["notion_content_api_key"])) {
        $api_key = $_POST["notion_content_api_key"];
    }
    $database_url = "";
    if(isset($_POST["notion_content_database_url"])) {
        $database_url = $_POST["notion_content_database_url"];
    }
    ?>
        <form method="post" action="">
        <input type="hidden" name="notion_content_check_config" value="1">
        <table class="form-table">
            <tr valign="top">
                <th scope="row">
                    Notion API Key
                    <span class="help-tip" title="Internal Integration Secret found in Notion in the Notion Developers site">
                            <span class="dashicons dashicons-editor-help"></span>
                    </span>
                </th>
                <td><input type="text" name="notion_content_api_key" value="<?php echo esc_attr($api_key); ?>" class="widefat" /></td>
            </tr>
            <tr valign="top">
                <th scope="row">Notion Database URL
                <span class="help-tip" title="The full URL of the Notion database (not just the ID)">
                    <span class="dashicons dashicons-editor-help"></span>
                </span>
                </th>
                <td><input type="text" name="notion_content_database_url" value="<?php echo esc_attr($database_url); ?>" class="widefat" /></td>
            </tr>
        </table>
        <?php submit_button(); ?>
        </form>
<?php
}

// Display the setup page choose database
function notion_content_setup_page_choose_database($databases = array()) {
    $api_key = "";
    if(isset($_POST["notion_content_api_key"])) {
        $api_key = $_POST["notion_content_api_key"];
    }
    $database_url = "";
    if(isset($_POST["notion_content_database_url"])) {
        $database_url = $_POST["notion_content_database_url"];
    }
    ?>
        <form method="post" action="">
        <input type="hidden" name="notion_content_check_config" value="1">
        <input type="hidden" name="notion_content_api_key" value="<?php echo esc_attr($api_key); ?>">
        <table class="form-table">
            <tr valign="top">
                <th scope="row">Notion Database URL
                </th>
                <td>
                <select name="notion_content_database_url">
                <?php foreach($databases AS $database) : ?>
                    <option value="https:www.notion.so/<?php echo esc_attr($database["id"]); ?>"><?php echo esc_html($database["name"]); ?></option>
                <?php endforeach; ?>
                </select>
                </td>
            </tr>
        </table>
        <?php submit_button(); ?>
        </form>
<?php
}


// Display the setup page success
function notion_content_setup_page_success() {
    ?>
<div class="wrap">
    <h1>🎉 Congratulations!</h1>
    <div class="postbox">
        <div class="inside">
            <p>Your setup is complete! Your plugin is ready to use.</p>
        </div>
    </div>
    <div class="postbox">
        <div class="inside">
            <h2>Next Steps:</h2>
            <ul>
                <li><strong>Refresh Content:</strong> Before you can use your Notion content, you need to import the content into Wordpress first.</li>
                <li><strong>Shortcodes:</strong> After importing your content, use the shortcode of the Notion page to display the content in Wordpress.  </li>
                <li><strong>Customize Sytles:</strong> Customize your styles in the <a href="<?php echo esc_url(admin_url('admin.php?page=notion-content-styles')); ?>">Styles</a> page. 
                <li><strong>Documentation:</strong> Visit our <a href="#">documentation</a> for detailed guides and tips.</li>
            </ul>
        </div>
    </div>
    <p>
        <a href="<?php echo esc_url(admin_url('admin.php?page=notion-content')); ?>" class="button button-primary">Go to the Notion Content Page</a>
    </p>
</div>

<?php
}