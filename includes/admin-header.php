<?php
// This file is used to display the header of the Notion Content plugin in the Wordpress admin.
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>
<div class="content-importer-for-notion-header">
    <div class="content-importer-for-notion-header-inner">
        <img src="<?php echo esc_html(plugin_dir_url(__FILE__) . '../assets/content-importer-logo.png'); ?>" alt="Content Importer for Notion Logo" class="content-importer-for-notion-logo">
        <h1 class="content-importer-for-notion-title">Content Importer for Notion</h1>
        <nav class="content-importer-for-notion-nav">
<?php

$screen = get_current_screen();
switch($screen->id) {
    case 'toplevel_page_content-importer-for-notion':
        $current_page = 'content-importer-for-notion';
        break;
    case 'content-importer-for-notion_page_content-importer-for-notion-styles':
        $current_page = 'content-importer-for-notion-styles';
        break;
    case 'content-importer-for-notion_page_content-importer-for-notion-settings':
        $current_page = 'content-importer-for-notion-settings';
        break;
}


?>
<?php if(content_importer_for_notion_is_setup()) : ?>

        <a href="<?php echo esc_url(admin_url('admin.php?page=content-importer-for-notion')); ?>" class="<?php echo $current_page === 'content-importer-for-notion' ? 'active' : ''; ?>">Pages</a>
        <a href="<?php echo esc_url(admin_url('admin.php?page=content-importer-for-notion-styles')); ?>" class="<?php echo $current_page === 'content-importer-for-notion-styles' ? 'active' : ''; ?>">Styles</a>
        <a href="<?php echo esc_url(admin_url('admin.php?page=content-importer-for-notion-settings')); ?>" class="<?php echo $current_page === 'content-importer-for-notion-settings' ? 'active' : ''; ?>">Settings</a>
<?php endif; ?>


            </nav>
    
    </div>
</div>

