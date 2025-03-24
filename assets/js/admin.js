jQuery(document).ready(function($) {
    // Initialize color pickers with a change event to update the shortcode preview
    $('.wpfed-color-picker').wpColorPicker({
        change: function() {
            // Use a timeout to wait for the color change to apply before updating
            setTimeout(function() {
                updateShortcodePreview();
            }, 100);
        }
    });
    
    // Function to generate and update the shortcode preview
    function updateShortcodePreview() {
        var shortcode = '[wpforms_entries_display';
        
        // Check and append attributes to the shortcode based on user selections
        
        // Show username option
        var showUsername = $('#wpfed_sc_show_username').val();
        if (showUsername) {
            shortcode += ' show_username="' + showUsername + '"';
        }

        // Hide field labels option
        var hideFieldLabels = $('#wpfed_sc_hide_field_labels').val();
        if (hideFieldLabels) {
            shortcode += ' hide_field_labels="' + hideFieldLabels + '"';
        }

        // Hide empty fields option
        var hideEmptyFields = $('#wpfed_sc_hide_empty_fields').val();
        if (hideEmptyFields) {
            shortcode += ' hide_empty_fields="' + hideEmptyFields + '"';
        }

        // Form ID
        var formId = $('#wpfed_sc_form_id').val();
        if (formId) {
            shortcode += ' id="' + formId + '"';
        }
        
        // Fields
        var fields = $('#wpfed_sc_fields').val();
        if (fields) {
            shortcode += ' fields="' + fields + '"';
        }
        
        // Number of entries
        var number = $('#wpfed_sc_number').val();
        if (number) {
            shortcode += ' number="' + number + '"';
        }
        
        // User
        var user = $('#wpfed_sc_user').val();
        if (user) {
            shortcode += ' user="' + user + '"';
        }
        
        // Type
        var type = $('#wpfed_sc_type').val();
        if (type && type !== 'all') {
            shortcode += ' type="' + type + '"';
        }
        
        // Sort field
        var sort = $('#wpfed_sc_sort').val();
        if (sort) {
            shortcode += ' sort="' + sort + '"';
        }
        
        // Order of sorting
        var order = $('#wpfed_sc_order').val();
        if (order && order !== 'asc') {
            shortcode += ' order="' + order + '"';
        }
        
        shortcode += ']';
        
        // Update the displayed shortcode for user reference
        $('#wpfed_shortcode_result').text(shortcode);
    }
    
    // Automatically update the shortcode when any of the parameters change
    $('.wpfed-sc-param').on('change keyup', updateShortcodePreview);
    
    // Initialize the shortcode preview on page load
    updateShortcodePreview();
    
    // Copy the generated shortcode to clipboard
    $('#wpfed_copy_shortcode').on('click', function() {
        var shortcodeText = $('#wpfed_shortcode_result').text();
        
        // Create a temporary textarea to handle the copy operation
        var $temp = $('<textarea>');
        $('body').append($temp);
        $temp.val(shortcodeText).select();
        
        // Execute the copy command
        document.execCommand('copy');
        
        // Remove the temporary textarea after copying
        $temp.remove();
        
        // Show a success message to the user
        var $button = $(this);
        var originalText = $button.html();
        
        $button.html('<span class="dashicons dashicons-yes"></span> Copied!');
        
        // Revert the button text back to original after 2 seconds
        setTimeout(function() {
            $button.html(originalText);
        }, 2000);
    });
    
    // Update the fields available for sorting when a form is selected
    $('#wpfed_sc_form_id').on('change', function() {
        var formId = $(this).val();
        
        if (formId) {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'wpfed_get_form_fields',
                    form_id: formId,
                    nonce: $('#wpfed_nonce').val()
                },
                success: function(response) {
                    // Parse response to extract field IDs and labels
                    var fieldsHtml = $(response);
                    var fieldOptions = [];
                    
                    fieldsHtml.filter('label').each(function() {
                        var fieldId = $(this).find('input').val();
                        var fieldLabel = $(this).text().trim();
                        
                        if (fieldId) {
                            fieldOptions.push({
                                id: fieldId,
                                label: fieldLabel
                            });
                        }
                    });
                    
                    // Update the sort field dropdown with the retrieved fields
                    var $sortField = $('#wpfed_sc_sort');
                    $sortField.empty();
                    $sortField.append('<option value="">No sorting</option>');
                    
                    $.each(fieldOptions, function(index, field) {
                        $sortField.append('<option value="' + field.id + '">' + field.label + '</option>');
                    });
                }
            });
        }
    });
    
    // Handle checkbox selection for fields in main settings to update shortcode
    $(document).on('change', '.wpfed-field-checkbox', function() {
        // Collect all selected field IDs
        var selectedFields = [];
        $('.wpfed-field-checkbox:checked').each(function() {
            selectedFields.push($(this).val());
        });
        
        // Update the fields input in the shortcode generator with selected fields
        $('#wpfed_sc_fields').val(selectedFields.join(','));
        updateShortcodePreview();
    });

    // Custom date format handling: Apply and add new formats to dropdown
    $('#wpfed_apply_custom_date').on('click', function() {
        var customFormat = $('#wpfed_custom_date_format').val();
        if (customFormat) {
            // Check if the custom format already exists in the dropdown
            var exists = false;
            $('#wpfed_date_format option').each(function() {
                if ($(this).val() === customFormat) {
                    exists = true;
                    return false;
                }
            });
            
            // Add the custom format to the dropdown if it is new
            if (!exists) {
                $('#wpfed_date_format').append('<option value="' + customFormat + '">' + customFormat + '</option>');
            }
            
            // Set the selected option to the custom format
            $('#wpfed_date_format').val(customFormat);
        }
    });

    // Handle changes to show date and date format, triggering shortcode updates
    $('#wpfed_sc_show_date').on('change', updateShortcodePreview);
    $('#wpfed_sc_date_format').on('change', updateShortcodePreview);
});