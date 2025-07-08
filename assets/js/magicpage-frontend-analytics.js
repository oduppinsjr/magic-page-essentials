jQuery(document).ready(function($) {
    $.post(MagicPageAnalyticsFrontend.ajax_url, {
        action: 'magicpage_record_visit',
        nonce: MagicPageAnalyticsFrontend.nonce,
        post_id: MagicPageAnalyticsFrontend.post_id
    });
});