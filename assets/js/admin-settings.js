jQuery(document).ready(function($) {
    // Function to fetch and display form fields
    function loadFormFields(formId) {
        var container = $('#wpfed_display_fields_container');
        var selectedFields = container.find('input:checked').map(function() {
            return this.value;
        }).get();

        if (!formId) {
            container.html('<p>' + wpfed_admin_vars.select_form_text + '</p>');
            return;
        }

        container.html('<p>Loading fields...</p>');

        $.ajax({
            url: wpfed_admin_vars.ajaxurl,
            type: 'POST',
            data: {
                action: 'wpfed_get_form_fields',
                form_id: formId,
                nonce: wpfed_admin_vars.nonce
            },
            success: function(response) {
                container.empty();
                if (response.success) {
                    $.each(response.data, function(index, field) {
                        var checked = $.inArray(field.id, selectedFields) !== -1 ? 'checked' : '';
                        var checkbox = '<label><input type="checkbox" name="wpfed_options[display_fields][]" value="' + field.id + '" ' + checked + ' class="wpfed-field-checkbox"> ' + field.label + ' (ID: ' + field.id + ')</label><br>';
                        container.append(checkbox);
                    });
                } else {
                    container.html('<p>' + response.data + '</p>');
                }
            },
            error: function() {
                container.html('<p>An error occurred while fetching fields.</p>');
            }
        });
    }

    // Initial load of form fields if a form is pre-selected
    var initialFormId = $('#wpfed_form_id').val();
    if (initialFormId) {
        loadFormFields(initialFormId);
    }

    // Handle form selection change
    $('#wpfed_form_id').on('change', function() {
        loadFormFields($(this).val());
    });
});