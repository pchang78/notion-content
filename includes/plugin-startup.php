<?php



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


function notion_content_setup_page() {

    include NOTION_CONTENT_PLUGIN_PATH . 'includes/admin-header.php';
    ?>

    <div class="wrap" id="notion-content-plugin-admin">
            <h1>Notion Content Setup</h1>

            <p>Work in progress.  </p>
            <p>This page will be dedicated to properly setting up the API and Database URL fields with error checks.</p>


    </div>


<?php
}
