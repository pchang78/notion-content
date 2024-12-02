    <div class="notion-content-header">
        <div class="notion-content-header-inner">
            <img src="<?php echo plugin_dir_url(__FILE__) . '../assets/notion-content-logo.png'; ?>" alt="Notion Content Logo" class="notion-content-logo">
            <h1 class="notion-content-title">Notion Content</h1>
            <nav class="notion-content-nav">
    <?php
    $current_page = isset($_GET['page']) ? $_GET['page'] : '';
    ?>
            <a href="<?php echo esc_url(admin_url('admin.php?page=notion-content')); ?>" class="<?php echo $current_page === 'notion-content' ? 'active' : ''; ?>">Pages</a>
            <a href="<?php echo esc_url(admin_url('admin.php?page=notion-content-styles')); ?>" class="<?php echo $current_page === 'notion-content-styles' ? 'active' : ''; ?>">Styles</a>
            <a href="<?php echo esc_url(admin_url('admin.php?page=notion-content-settings')); ?>" class="<?php echo $current_page === 'notion-content-settings' ? 'active' : ''; ?>">Settings</a>


                </nav>
     
        </div>
    </div>
