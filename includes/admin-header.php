<?php
// This file is used to display the header of the Notion Content plugin in the Wordpress admin.
?>
<div class="notion-content-header">
    <div class="notion-content-header-inner">
        <img src="<?php echo esc_html(plugin_dir_url(__FILE__) . '../assets/notion-content-logo.png'); ?>" alt="Notion Content Logo" class="notion-content-logo">
        <h1 class="notion-content-title">Notion Content</h1>
        <nav class="notion-content-nav">
<?php

$screen = get_current_screen();
switch($screen->id) {
    case 'toplevel_page_notion-content':
        $current_page = 'notion-content';
        break;
    case 'notion-content_page_notion-content-styles':
        $current_page = 'notion-content-styles';
        break;
    case 'notion-content_page_notion-content-settings':
        $current_page = 'notion-content-settings';
        break;
}


?>
<?php if(notion_content_is_setup()) : ?>

        <a href="<?php echo esc_url(admin_url('admin.php?page=notion-content')); ?>" class="<?php echo $current_page === 'notion-content' ? 'active' : ''; ?>">Pages</a>
        <a href="<?php echo esc_url(admin_url('admin.php?page=notion-content-styles')); ?>" class="<?php echo $current_page === 'notion-content-styles' ? 'active' : ''; ?>">Styles</a>
        <a href="<?php echo esc_url(admin_url('admin.php?page=notion-content-settings')); ?>" class="<?php echo $current_page === 'notion-content-settings' ? 'active' : ''; ?>">Settings</a>
<?php endif; ?>


            </nav>
    
    </div>
</div>

