jQuery(document).ready(function($) {
    $("#wpfed_form_id").on("change", function() {
        var formId = $(this).val();
        if (formId) {
            $.ajax({
                url: wpfed_admin_vars.ajaxurl,
                type: "POST",
                data: {
                    action: "wpfed_get_form_fields",
                    form_id: formId,
                    nonce: wpfed_admin_vars.nonce
                },
                success: function(response) {
                    $("#wpfed_display_fields_container").html(response);
                    // Update shortcode generator
                    updateShortcodePreview();
                }
            });
        } else {
            $("#wpfed_display_fields_container").html("<p>" + wpfed_admin_vars.select_form_text + "</p>");
            // Update shortcode generator
            updateShortcodePreview();
        }
    });
});