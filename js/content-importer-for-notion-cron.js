
jQuery(document).ready(function ($) {
    $('.cron-interval').change(function () {
        const pageId = $(this).data('page-id');
        const interval = $(this).val();

        // Show the loading overlay
        $('#loading-overlay').fadeIn();

        $.post(contentImporterForNotionCronAjax.ajax_url, {
            action: 'content_importer_for_notion_set_cron_interval',
            nonce: contentImporterForNotionCronAjax.nonce,
            page_id: pageId,
            interval: interval
        }, function (response) {
            $('#loading-overlay').fadeOut();
            if (response.success) {
                //alert('Cron interval updated successfully!');
            } else {
                alert('Failed to update cron interval: ' + response.data.message);
            }
        });
    });
});