jQuery(document).on('click', '.notice.custom-iframe-notice .notice-dismiss', function () {
    jQuery.post(customIframeNotice.ajax_url, {
        action: 'dismiss_custom_iframe_notice',
        nonce: customIframeNotice.nonce,
    });
});

jQuery(document).on('click', '.custif-pro-active-notice .notice-dismiss', function () {
    jQuery.post(customIframeNotice.ajax_url, {
        action: 'dismiss_custom_iframe_pro_notice',
        nonce: customIframeNotice.nonce,
    });
});

jQuery(document).on('click', '.custif-dismiss-rating .notice-dismiss, .custif-rating-notice .notice-dismiss', function (e) {
    e.preventDefault();
    jQuery.post(customIframeNotice.ajax_url, {
        action: 'custif_dismiss_rating_notice',
        nonce: customIframeNotice.nonce,
    }, function () {
        jQuery('.custif-rating-notice').fadeOut();
    });
});

jQuery(document).on('click', '.custif-remind-later', function (e) {
    e.preventDefault();
    jQuery.post(customIframeNotice.ajax_url, {
        action: 'custif_remind_later_rating',
        nonce: customIframeNotice.nonce,
    }, function () {
        jQuery('.custif-rating-notice').fadeOut();
    });
});
