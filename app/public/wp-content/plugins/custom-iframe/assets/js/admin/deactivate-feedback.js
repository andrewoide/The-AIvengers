jQuery(document).ready(function ($) {
    'use strict';

    var $deactivateLink = $('#the-list').find('[data-slug="custom-iframe"] span.deactivate a'),
        $overlay = $('<div class="custif-feedback-overlay"></div>'),
        $dialog = $('#custif-feedback-dialog-wrapper'),
        $form = $('#custif-feedback-dialog-form'),
        $skipButton = $('<button class="custif-feedback-skip">Skip</button>'),
        $submitButton = $('<button class="custif-feedback-submit">Submit & Deactivate</button>');

    // Add buttons to dialog
    $dialog.append($skipButton);
    $dialog.append($submitButton);

    // Show dialog when deactivate link is clicked
    $deactivateLink.on('click', function (e) {
        e.preventDefault();
        $('body').append($overlay);
        $dialog.addClass('active');
    });

    // Handle skip button click
    $skipButton.on('click', function () {
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'custif_skip_feedback',
                nonce: $form.find('input[name="nonce"]').val()
            },
            success: function () {
                window.location.href = $deactivateLink.attr('href');
            }
        });
    });

    // Handle submit button click
    $submitButton.on('click', function () {
        var $selectedReason = $form.find('input[name="reason_key"]:checked'),
            $reasonText = $form.find('input[name="reason_' + $selectedReason.val() + '"]');

        if (!$selectedReason.length) {
            alert('Please select a reason for deactivation.');
            return;
        }

        if ($reasonText.length && !$reasonText.val()) {
            alert('Please provide more details.');
            return;
        }

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'custif_deactivate_feedback',
                nonce: $form.find('input[name="nonce"]').val(),
                reason_key: $selectedReason.val(),
                reason_text: $reasonText.val()
            },
            success: function () {
                window.location.href = $deactivateLink.attr('href');
            }
        });
    });

    // Close dialog when clicking outside
    $overlay.on('click', function () {
        $dialog.removeClass('active');
        $overlay.remove();
    });

    // Show/hide reason text input based on selection
    $form.find('input[name="reason_key"]').on('change', function () {
        $form.find('.custif-feedback-text').hide();
        var $selectedReason = $(this),
            $reasonText = $form.find('input[name="reason_' + $selectedReason.val() + '"]');

        if ($reasonText.length) {
            $reasonText.show();
        }
    });
}); 