jQuery(document).ready(function($) {
    // Initialize color pickers
    $('.wpfed-color-picker').wpColorPicker({
        change: function(event, ui) {
            // Use a timeout to ensure the color value is updated before triggering our functions
            setTimeout(function() {
                updateLivePreview();
                updateShortcodePreview(); // Also update shortcode if styles are params
            }, 10);
        },
        clear: function() {
            updateLivePreview();
            updateShortcodePreview();
        }
    });

    // --- Live Preview Updater ---
    function updateLivePreview() {
        const $preview = $('.wpfed-live-preview');
        if (!$preview.length) return;

        // Style settings
        const styles = {
            '--preview-bg-color': $('#wpfed_background_color').val() || '#ffffff',
            '--preview-border-color': $('#wpfed_border_color').val() || '#d2d2d7',
            '--preview-text-color': $('#wpfed_text_color').val() || '#1d1d1f',
            '--preview-header-color': $('#wpfed_header_color').val() || '#6e6e73',
            '--preview-border-radius': $('#wpfed_border_radius').val() || '12px',
            '--preview-padding': $('#wpfed_padding').val() || '20px',
            '--preview-box-shadow': $('#wpfed_box_shadow').val() || 'none',
            // Updated typography settings for all field types
            '--preview-date-font-size': $('#wpfed_date_font_size').val() || '14px',
            '--preview-date-font-weight': $('#wpfed_date_font_weight').val() || 'bold',
            '--preview-date-font-style': $('#wpfed_date_font_style').val() || 'normal',
            '--preview-username-font-size': $('#wpfed_username_font_size').val() || '14px',
            '--preview-username-font-weight': $('#wpfed_username_font_weight').val() || 'bold',
            '--preview-username-font-style': $('#wpfed_username_font_style').val() || 'normal',
            '--preview-email-font-size': $('#wpfed_email_font_size').val() || '14px',
            '--preview-email-font-weight': $('#wpfed_email_font_weight').val() || 'normal',
            '--preview-email-font-style': $('#wpfed_email_font_style').val() || 'normal',
            '--preview-field-labels-font-size': $('#wpfed_field_labels_font_size').val() || '14px',
            '--preview-field-labels-font-weight': $('#wpfed_field_labels_font_weight').val() || 'bold',
            '--preview-field-labels-font-style': $('#wpfed_field_labels_font_style').val() || 'normal',
            '--preview-comment-font-size': $('#wpfed_comment_font_size').val() || '16px',
            '--preview-comment-font-weight': $('#wpfed_comment_font_weight').val() || 'normal',
            '--preview-comment-font-style': $('#wpfed_comment_font_style').val() || 'normal',
        };
        $preview.css(styles);

        // Apply typography to specific preview elements with !important
        $('.wpfed-preview-date').css({
            'font-size': styles['--preview-date-font-size'] + ' !important',
            'font-weight': styles['--preview-date-font-weight'] + ' !important',
            'font-style': styles['--preview-date-font-style'] + ' !important'
        });

        $('.wpfed-preview-username').css({
            'font-size': styles['--preview-username-font-size'] + ' !important',
            'font-weight': styles['--preview-username-font-weight'] + ' !important',
            'font-style': styles['--preview-username-font-style'] + ' !important'
        });

        $('.wpfed-preview-label').css({
            'font-size': styles['--preview-field-labels-font-size'] + ' !important',
            'font-weight': styles['--preview-field-labels-font-weight'] + ' !important',
            'font-style': styles['--preview-field-labels-font-style'] + ' !important'
        });

        // Apply different styles to different field types in preview
        $('.wpfed-preview-field.email .wpfed-preview-value').css({
            'font-size': styles['--preview-email-font-size'] + ' !important',
            'font-weight': styles['--preview-email-font-weight'] + ' !important',
            'font-style': styles['--preview-email-font-style'] + ' !important'
        });

        $('.wpfed-preview-field.comment .wpfed-preview-value').css({
            'font-size': styles['--preview-comment-font-size'] + ' !important',
            'font-weight': styles['--preview-comment-font-weight'] + ' !important',
            'font-style': styles['--preview-comment-font-style'] + ' !important'
        });

        $('.wpfed-preview-field.general .wpfed-preview-value').css({
            'font-size': styles['--preview-comment-font-size'] + ' !important',
            'font-weight': styles['--preview-comment-font-weight'] + ' !important',
            'font-style': styles['--preview-comment-font-style'] + ' !important'
        });

        // Display settings (for toggles)
        $('.wpfed-preview-date').parent().toggle($('input[name="wpfed_options[show_date]"]').is(':checked'));
        $('.wpfed-preview-username').parent().toggle($('input[name="wpfed_options[show_username]"]').is(':checked'));
        $('.wpfed-preview-label').toggle(!$('input[name="wpfed_options[hide_field_labels]"]').is(':checked'));
    }

    // --- Shortcode Generator ---
    function updateShortcodePreview() {
        let shortcode = '[wpforms_entries_display';
        const params = {};

        // Collect values from shortcode generator fields
        $('.wpfed-sc-param').each(function() {
            const $field = $(this);
            const attr = $field.attr('id').replace('wpfed_sc_', '');
            let value = $field.val();

            if (value) {
                params[attr] = value;
            }
        });

        for (const [attr, value] of Object.entries(params)) {
            shortcode += ` ${attr}="${value}"`;
        }

        shortcode += ']';
        $('#wpfed_shortcode_result').text(shortcode);
    }

    // --- Event Handlers ---

    // Trigger updates on any setting change
    $('.wpfed-admin-main input, .wpfed-admin-main select').on('change', function() {
        updateLivePreview();
    });

    // Trigger shortcode update on generator field change
    $('.wpfed-sc-param').on('change keyup', updateShortcodePreview);

    // Copy shortcode to clipboard
    $('#wpfed_copy_shortcode').on('click', function() {
        const shortcodeText = $('#wpfed_shortcode_result').text();
        const $button = $(this);
        const originalHtml = $button.html();

        navigator.clipboard.writeText(shortcodeText).then(() => {
            $button.html('<span class="dashicons dashicons-yes-alt"></span> Copied');
            setTimeout(() => $button.html(originalHtml), 2000);
        }).catch(() => {
            $button.html('<span class="dashicons dashicons-dismiss"></span> Failed');
            setTimeout(() => $button.html(originalHtml), 2000);
        });
    });

    // AJAX to get form fields for the default settings
    $('#wpfed_form_id').on('change', function() {
        const formId = $(this).val();
        const $container = $('#wpfed_display_fields_container');

        if (!formId) {
            $container.html(`<p>${wpfed_admin_vars.select_form_text}</p>`);
            return;
        }

        $.ajax({
            url: wpfed_admin_vars.ajaxurl,
            type: 'POST',
            data: {
                action: 'wpfed_get_form_fields',
                nonce: wpfed_admin_vars.nonce,
                form_id: formId,
            },
            beforeSend: function() {
                $container.html('<p>Loading fields...</p>');
            },
            success: function(response) {
                if (response.success) {
                    let fieldHtml = '';
                    response.data.forEach(field => {
                        fieldHtml += `<label><input type="checkbox" name="wpfed_options[display_fields][]" value="${field.id}" class="wpfed-field-checkbox"> ${field.label} (ID: ${field.id})</label>`;
                    });
                    $container.html(fieldHtml);
                } else {
                    $container.html(`<p>${response.data}</p>`);
                }
            },
            error: function() {
                $container.html('<p>An error occurred.</p>');
            }
        });
    });

    // Initial setup on page load
    updateLivePreview();
    updateShortcodePreview();
});