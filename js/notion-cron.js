
jQuery(document).ready(function ($) {
    $('.cron-interval').change(function () {
        const pageId = $(this).data('page-id');
        const interval = $(this).val();

        // Show the loading overlay
        $('#loading-overlay').fadeIn();

        $.post(notionCronAjax.ajax_url, {
            action: 'notion_set_cron_interval',
            nonce: notionCronAjax.nonce,
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