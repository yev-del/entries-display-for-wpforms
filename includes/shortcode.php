<?php
// Exit if accessed directly to prevent unauthorized access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register the shortcode during the 'init' action
 */
function wpfed_register_shortcode() {
    add_shortcode('wpforms_entries_display', 'wpfed_shortcode_callback');
}
add_action('init', 'wpfed_register_shortcode');

/**
 * Shortcode callback function to display form entries
 *
 * @param array $atts Shortcode attributes
 * @return string HTML output for form entries display
 */
function wpfed_shortcode_callback($atts) {
    // Ensure WPForms is active
    if (!function_exists('wpforms')) {
        return '<p class="wpfed-error">' . esc_html__('Error: WPForms plugin is not active.', 'entries-display-for-wpforms') . '</p>';
    }
    
    // Check availability of WPForms Entries API
    $wpforms = wpforms();
    if (!is_object($wpforms) || !isset($wpforms->entry) || !method_exists($wpforms->entry, 'get_entries')) {
        return '<p class="wpfed-error">' . esc_html__('Error: WPForms Entries API is not available. Make sure you have WPForms Pro installed.', 'entries-display-for-wpforms') . '</p>';
    }
    
    // Retrieve plugin options
    $options = get_option('wpfed_options');
    
    // Define default attributes for the shortcode
    $default_atts = array(
        'id'               => isset($options['form_id']) ? $options['form_id'] : '',
        'fields'           => '',
        'number'           => isset($options['entries_per_page']) ? $options['entries_per_page'] : 30,
        'user'             => '',
        'type'             => 'all', // Options: all, unread, read, or starred
        'sort'             => '',
        'order'            => 'asc',
        'show_date'        => isset($options['show_date']) ? $options['show_date'] : 'yes',
        'date_format'      => isset($options['date_format']) ? $options['date_format'] : 'F j, Y g:i a',
        'show_username'    => isset($options['show_username']) ? $options['show_username'] : 'no',
        'hide_field_labels' => isset($options['hide_field_labels']) ? $options['hide_field_labels'] : 'no',
        'hide_empty_fields' => isset($options['hide_empty_fields']) ? $options['hide_empty_fields'] : 'no',
    );
    
    // Merge user-supplied attributes with defaults
    $atts = shortcode_atts($default_atts, $atts);
    
    // Validate form ID
    if (empty($atts['id'])) {
        return '<p class="wpfed-error">' . esc_html__('Error: Form ID is required. Please specify a form ID in the shortcode or in the plugin settings.', 'entries-display-for-wpforms') . '</p>';
    }
    
    // Attempt to retrieve the form
    $form = $wpforms->form->get(absint($atts['id']));
    
    // Verify form existence
    if (empty($form)) {
        return '<p class="wpfed-error">' . esc_html__('Error: Form not found. Please check the form ID.', 'entries-display-for-wpforms') . '</p>';
    }
    
    // Extract form data
    $form_data = !empty($form->post_content) ? wpforms_decode($form->post_content) : '';
    if (empty($form_data) || empty($form_data['fields'])) {
        return '<p class="wpfed-error">' . esc_html__('Error: Form has no fields.', 'entries-display-for-wpforms') . '</p>';
    }
    
    // Determine fields to display
    $form_field_ids = array();
    
    // Use fields from shortcode attributes or plugin options
    if (!empty($atts['fields'])) {
        $form_field_ids = explode(',', str_replace(' ', '', $atts['fields']));
    } else if (!empty($options['display_fields'])) {
        $form_field_ids = $options['display_fields'];
    }
    
    // Configure form fields for display
    if (empty($form_field_ids)) {
        $form_fields = $form_data['fields'];
    } else {
        $form_fields = array();
        foreach ($form_field_ids as $field_id) {
            if (isset($form_data['fields'][$field_id])) {
                $form_fields[$field_id] = $form_data['fields'][$field_id];
            }
        }
    }
    
    // Ensure fields are available for display
    if (empty($form_fields)) {
        return '<p class="wpfed-error">' . esc_html__('Error: No fields selected for display.', 'entries-display-for-wpforms') . '</p>';
    }
    
    // Specify field types to exclude
    $form_fields_disallow = apply_filters('wpfed_disallowed_field_types', array('divider', 'html', 'pagebreak', 'captcha'));
    
    // Filter out disallowed field types
    foreach ($form_fields as $field_id => $form_field) {
        if (in_array($form_field['type'], $form_fields_disallow, true)) {
            unset($form_fields[$field_id]);
        }
    }
    
    // Set up arguments to retrieve entries
    $entries_args = array(
        'form_id' => absint($atts['id']),
    );
    
    // Apply user-specific filters if applicable
    if (!empty($atts['user'])) {
        if ($atts['user'] === 'current' && is_user_logged_in()) {
            $entries_args['user_id'] = get_current_user_id();
        } else {
            $entries_args['user_id'] = absint($atts['user']);
        }
    }
    
    // Define number of entries to retrieve
    if (!empty($atts['number'])) {
        $entries_args['number'] = absint($atts['number']);
    }
    
    // Apply filters to the entry type
    if ($atts['type'] === 'unread') {
        $entries_args['viewed'] = '0';
    } elseif ($atts['type'] === 'read') {
        $entries_args['viewed'] = '1';
    } elseif ($atts['type'] === 'starred') {
        $entries_args['starred'] = '1';
    }
    
    // Securely retrieve entries
    try {
        $entries = $wpforms->entry->get_entries($entries_args);
        $entries = json_decode(json_encode($entries), true);
    } catch (Exception $e) {
        return '<p class="wpfed-error">' . esc_html__('Error retrieving entries: ', 'entries-display-for-wpforms') . esc_html($e->getMessage()) . '</p>';
    }
    
    // Display message if no entries are found
    if (empty($entries)) {
        return '<p class="wpfed-no-entries">' . esc_html__('No entries found.', 'entries-display-for-wpforms') . '</p>';
    }
    
    // Process entries for display
    foreach ($entries as $key => $entry) {
        $entries[$key]['fields'] = json_decode($entry['fields'], true);
        $entries[$key]['meta'] = json_decode($entry['meta'], true);
    }
    
    // Sort entries if a sort parameter is provided
    if (!empty($atts['sort']) && isset($entries[0]['fields'][$atts['sort']])) {
        usort($entries, function ($entry1, $entry2) use ($atts) {
            return strtolower($atts['order']) == 'asc' ? strcmp($entry1['fields'][$atts['sort']]['value'], $entry2['fields'][$atts['sort']]['value']) : strcmp($entry2['fields'][$atts['sort']]['value'], $entry1['fields'][$atts['sort']]['value']);
        });
    }
    
    // Enqueue styles for frontend display
    wp_enqueue_style('wpfed-frontend-styles');
    
    // Generate custom CSS based on saved style settings
    $styles = isset($options['styles']) ? $options['styles'] : array();
    $custom_css = wpfed_generate_custom_css($styles);
    wp_add_inline_style('wpfed-frontend-styles', $custom_css);
    
    // Start output buffering to capture HTML output
    ob_start();
    
    echo '<div class="wpfed-comments-container">';
    
    // Output each entry as a comment block
    foreach ($entries as $entry) {
        echo '<div class="wpfed-comment">';
        
        // Display date/time if enabled, along with username if applicable
        if ($atts['show_date'] === 'yes' && !empty($entry['date'])) {
            $date = date_i18n($atts['date_format'], strtotime($entry['date']));
            echo '<div class="wpfed-comment-date">';
            
            if (isset($options['show_username']) && $options['show_username'] === 'yes' && !empty($entry['user_id'])) {
                $user = get_userdata($entry['user_id']);
                if ($user) {
                    echo '<span class="wpfed-username">' . esc_html($user->display_name) . '</span> • ';
                }
            }
            
            echo esc_html($date) . '</div>';
        } else if (isset($options['show_username']) && $options['show_username'] === 'yes' && !empty($entry['user_id'])) {
            // Display only username if date is disabled
            $user = get_userdata($entry['user_id']);
            if ($user) {
                echo '<div class="wpfed-comment-date"><span class="wpfed-username">' . esc_html($user->display_name) . '</span></div>';
            }
        }
        
        echo '<div class="wpfed-comment-content">';
        
        $entry_fields = $entry['fields'];
        
        // Loop through and display fields
        foreach ($form_fields as $form_field) {
            $field_value = '';
            foreach ($entry_fields as $entry_field) {
                if (absint($entry_field['id']) === absint($form_field['id'])) {
                    $field_value = apply_filters('wpforms_html_field_value', wp_strip_all_tags($entry_field['value']), $entry_field, $form_data, 'entry-frontend-table');
                    break;
                }
            }
            
            // Skip empty values if configured to hide them
            if (empty($field_value) && isset($options['hide_empty_fields']) && $options['hide_empty_fields'] === 'yes') {
                continue;
            }
            
            echo '<div class="wpfed-comment-field">';
            
            // Conditionally display field labels
            if (!isset($options['hide_field_labels']) || $options['hide_field_labels'] !== 'yes') {
                echo '<span class="wpfed-field-label">' . esc_html($form_field['label']) . ':</span> ';
            }
            
            echo '<span class="wpfed-field-value">' . wp_kses_post($field_value) . '</span>';
            echo '</div>';
        }
        
        echo '</div>'; // .wpfed-comment-content
        echo '</div>'; // .wpfed-comment
    }
    
    echo '</div>'; // .wpfed-comments-container
    
    // Capture buffered output
    $output = ob_get_clean();
    
    return $output;
}

/**
 * Generate custom CSS based on style settings
 *
 * @param array $styles Style settings
 * @return string CSS rules to apply
 */
function wpfed_generate_custom_css($styles) {
    $css = '';
    
    // Apply default styles if specific settings are not provided
    $background_color = isset($styles['background_color']) ? $styles['background_color'] : '#f9f9f9';
    $border_color = isset($styles['border_color']) ? $styles['border_color'] : '#e0e0e0';
    $text_color = isset($styles['text_color']) ? $styles['text_color'] : '#333333';
    $header_color = isset($styles['header_color']) ? $styles['header_color'] : '#444444';
    $border_radius = isset($styles['border_radius']) ? $styles['border_radius'] : '5px';
    $padding = isset($styles['padding']) ? $styles['padding'] : '15px';
    $box_shadow = isset($styles['box_shadow']) ? $styles['box_shadow'] : '0 2px 5px rgba(0,0,0,0.1)';
    
    // Configure CSS rules for the comments container
    $css .= '.wpfed-comments-container {';
    $css .= 'margin-bottom: 20px;';
    $css .= '}';
    
    // Configure CSS for each individual comment
    $css .= '.wpfed-comment {';
    $css .= 'background-color: ' . $background_color . ';';
    $css .= 'color: ' . $text_color . ';';
    $css .= 'border: 1px solid ' . $border_color . ';';
    $css .= 'border-radius: ' . $border_radius . ';';
    $css .= 'padding: ' . $padding . ';';
    $css .= 'margin-bottom: 20px;';
    
    // Include box shadow only if specified and not set to 'none'
    if ($box_shadow !== 'none') {
        $css .= 'box-shadow: ' . $box_shadow . ';';
    }
    
    $css .= '}';
    
    // Customize comment date display
    $css .= '.wpfed-comment-date {';
    $css .= 'font-size: 0.9em;';
    $css .= 'color: ' . $header_color . ';';
    $css .= 'margin-bottom: 10px;';
    $css .= 'font-weight: bold;';
    $css .= '}';
    
    // Define styles for the username
    $css .= '.wpfed-username {';
    $css .= 'font-weight: bold;';
    $css .= 'color: ' . $header_color . ';';
    $css .= '}';
    
    // Configure styles for comment fields
    $css .= '.wpfed-comment-field {';
    $css .= 'margin-bottom: 8px;';
    $css .= '}';
    
    // Define font styles for field labels
    $css .= '.wpfed-field-label {';
    $css .= 'font-weight: bold;';
    $css .= 'color: ' . $header_color . ';';
    $css .= '}';
    
    // Define styles for field values
    $css .= '.wpfed-field-value {';
    $css .= 'word-break: break-word;';
    $css .= '}';
    
    // Remove margin for last field element
    $css .= '.wpfed-comment-field:last-child {';
    $css .= 'margin-bottom: 0;';
    $css .= '}';
    
    // Configure styles for error messages
    $css .= '.wpfed-error {';
    $css .= 'color: #d63638;';
    $css .= 'padding: 10px;';
    $css .= 'border: 1px solid #ffb8b8;';
    $css .= 'background-color: #ffecec;';
    $css .= 'border-radius: 4px;';
    $css .= 'margin-bottom: 20px;';
    $css .= '}';
    
    // Define styles for 'no entries' message
    $css .= '.wpfed-no-entries {';
    $css .= 'padding: 15px;';
    $css .= 'text-align: center;';
    $css .= 'background-color: ' . $background_color . ';';
    $css .= 'border: 1px solid ' . $border_color . ';';
    $css .= 'border-radius: ' . $border_radius . ';';
    $css .= '}';
    
    return $css;
}