<?php
// Exit if accessed directly to prevent unauthorized access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class to handle the [wpforms_entries_display] shortcode.
 */
class WPFED_Shortcode {

    private $options;

    /**
     * Constructor to initialize the shortcode.
     */
    public function __construct() {
        $this->options = get_option('wpfed_options');
        add_shortcode('wpforms_entries_display', array($this, 'shortcode_callback'));
    }

    /**
     * Shortcode callback function to display form entries.
     *
     * @param array $atts Shortcode attributes.
     * @return string HTML output for form entries display.
     */
    public function shortcode_callback($atts) {
        // Ensure WPForms is active and the necessary components are available.
        if (!function_exists('wpforms') || !property_exists(wpforms(), 'entry') || !method_exists(wpforms()->entry, 'get_entries')) {
            return $this->get_error_message(esc_html__('WPForms Pro and the Entries API are required.', 'entries-display-for-wpforms'));
        }

        // Merge user-supplied attributes with defaults.
        $atts = $this->get_attributes($atts);

        // Validate form ID.
        if (empty($atts['id'])) {
            return $this->get_error_message(esc_html__('Form ID is required.', 'entries-display-for-wpforms'));
        }

        // Retrieve and validate the form.
        $form = wpforms()->form->get(absint($atts['id']));
        if (empty($form)) {
            return $this->get_error_message(esc_html__('Form not found.', 'entries-display-for-wpforms'));
        }

        // Decode form data and get fields.
        $form_data = !empty($form->post_content) ? wpforms_decode($form->post_content) : array();
        $form_fields = $this->get_display_fields($atts, $form_data);

        if (empty($form_fields)) {
            return $this->get_error_message(esc_html__('No fields are selected for display.', 'entries-display-for-wpforms'));
        }

        // Fetch entries from the database.
        $entries = $this->get_entries($atts);

        // Display a message if no entries are found.
        if (empty($entries)) {
            return '<div class="wpfed-no-entries">' . esc_html__('No entries found.', 'entries-display-for-wpforms') . '</div>';
        }

        // Enqueue styles and generate custom CSS.
        $this->enqueue_styles();

        // Return the rendered HTML for the entries.
        return $this->render_entries($entries, $form_fields, $atts, $form_data);
    }

    /**
     * Merges shortcode attributes with default values.
     *
     * @param array $atts User-provided attributes.
     * @return array Merged attributes.
     */
    private function get_attributes($atts) {
        $default_atts = array(
            'id'                => $this->options['form_id'] ?? '',
            'fields'            => '',
            'number'            => $this->options['entries_per_page'] ?? 30,
            'user'              => '',
            'type'              => 'all',
            'sort'              => '',
            'order'             => 'desc',
            'show_date'         => $this->options['show_date'] ?? 'yes',
            'date_format'       => $this->options['date_format'] ?? 'F j, Y g:i a',
            'show_username'     => $this->options['show_username'] ?? 'no',
            'hide_field_labels' => $this->options['hide_field_labels'] ?? 'no',
            'hide_empty_fields' => $this->options['hide_empty_fields'] ?? 'no',
        );
        return shortcode_atts($default_atts, $atts, 'wpforms_entries_display');
    }

    /**
     * Determines which form fields to display based on attributes and settings.
     *
     * @param array $atts Shortcode attributes.
     * @param array $form_data Decoded form data.
     * @return array The fields to display.
     */
    private function get_display_fields($atts, $form_data) {
        if (empty($form_data['fields'])) {
            return array();
        }

        $all_fields = $form_data['fields'];
        $field_ids_to_display = array();

        if (!empty($atts['fields'])) {
            $field_ids_to_display = array_map('trim', explode(',', $atts['fields']));
        } elseif (!empty($this->options['display_fields'])) {
            $field_ids_to_display = $this->options['display_fields'];
        }

        $form_fields = array();
        if (empty($field_ids_to_display)) {
            $form_fields = $all_fields;
        } else {
            foreach ($field_ids_to_display as $field_id) {
                if (isset($all_fields[$field_id])) {
                    $form_fields[$field_id] = $all_fields[$field_id];
                }
            }
        }

        // Filter out disallowed field types.
        $disallowed_types = apply_filters('wpfed_disallowed_field_types', array('divider', 'html', 'pagebreak', 'captcha'));
        foreach ($form_fields as $field_id => $field) {
            if (in_array($field['type'], $disallowed_types, true)) {
                unset($form_fields[$field_id]);
            }
        }

        return $form_fields;
    }

    /**
     * Fetches entries from the database based on shortcode attributes.
     *
     * @param array $atts Shortcode attributes.
     * @return array The fetched entries.
     */
    private function get_entries($atts) {
        $args = array(
            'form_id' => absint($atts['id']),
            'number'  => absint($atts['number']),
            'orderby' => 'entry_id', // Default sorting
            'order'   => strtoupper($atts['order']),
        );

        // User filtering.
        if (!empty($atts['user'])) {
            if ($atts['user'] === 'current' && is_user_logged_in()) {
                $args['user_id'] = get_current_user_id();
            } else {
                $args['user_id'] = absint($atts['user']);
            }
        }

        // Entry type filtering.
        $type_map = array(
            'unread'  => array('viewed', '0'),
            'read'    => array('viewed', '1'),
            'starred' => array('starred', '1'),
        );
        if (isset($type_map[$atts['type']])) {
            list($key, $value) = $type_map[$atts['type']];
            $args[$key] = $value;
        }

        // Custom field sorting.
        if (!empty($atts['sort'])) {
            $args['orderby'] = 'field_' . absint($atts['sort']);
        }

        try {
            return wpforms()->entry->get_entries($args);
        } catch (Exception $e) {
            // In a real-world scenario, you might want to log this error.
            return array();
        }
    }

    /**
     * Renders the HTML for the entries.
     *
     * @param array $entries The entries to render.
     * @param array $form_fields The form fields to display.
     * @param array $atts Shortcode attributes.
     * @param array $form_data Decoded form data.
     * @return string The rendered HTML.
     */
    private function render_entries($entries, $form_fields, $atts, $form_data) {
        ob_start();
        ?>
        <div class="wpfed-comments-container">
            <?php foreach ($entries as $entry) :
                $entry_fields = !empty($entry->fields) ? wpforms_decode($entry->fields) : array();
            ?>
                <div class="wpfed-comment">
                    <?php $this->render_entry_header($entry, $atts); ?>
                    <div class="wpfed-comment-content">
                        <?php foreach ($form_fields as $form_field) :
                            $field_value = $entry_fields[$form_field['id']]['value'] ?? '';

                            if (empty($field_value) && $atts['hide_empty_fields'] === 'yes') {
                                continue;
                            }

                            // Skip username field if it's already shown in header and it's a name field
                            if ($atts['show_username'] === 'yes' && 
                                (stripos($form_field['label'], 'name') !== false || 
                                 stripos($form_field['label'], 'user') !== false) &&
                                !empty($entry->user_id)) {
                                continue;
                            }

                            $field_value_processed = apply_filters('wpforms_html_field_value', wp_strip_all_tags($field_value), $entry_fields[$form_field['id']], $form_data, 'entry-frontend-table');
                            
                            // Determine field type for styling
                            $field_type_class = $this->get_field_type_class($form_field);
                            ?>
                            <div class="wpfed-comment-field <?php echo esc_attr($field_type_class); ?>">
                                <?php if ($atts['hide_field_labels'] !== 'yes') : ?>
                                    <span class="wpfed-field-label"><?php echo esc_html($form_field['label']); ?>:</span>
                                <?php endif; ?>
                                <span class="wpfed-field-value"><?php echo wp_kses_post($field_value_processed); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Renders the header for a single entry.
     *
     * @param object $entry The entry object.
     * @param array $atts Shortcode attributes.
     */
    private function render_entry_header($entry, $atts) {
        $show_date = $atts['show_date'] === 'yes';
        $show_username = $atts['show_username'] === 'yes' && !empty($entry->user_id);

        if (!$show_date && !$show_username) {
            return;
        }

        echo '<div class="wpfed-comment-header">';
        
        // Show date if enabled
        if ($show_date && !empty($entry->date)) {
            $date_info = date_i18n($atts['date_format'], strtotime($entry->date));
            echo '<div class="wpfed-comment-date">' . esc_html($date_info) . '</div>';
        }
        
        // Show WordPress username as separate field if enabled (not form field data)
        if ($show_username) {
            $user = get_userdata($entry->user_id);
            if ($user) {
                echo '<div class="wpfed-username">' . esc_html($user->display_name) . '</div>';
            }
        }
        
        echo '</div>';
    }

    /**
     * Enqueues frontend styles and generates custom CSS.
     */
    private function enqueue_styles() {
        wp_enqueue_style('wpfed-frontend-styles');
        $custom_css = $this->generate_custom_css();
        wp_add_inline_style('wpfed-frontend-styles', $custom_css);
    }

    /**
     * Generates custom CSS based on style settings.
     *
     * @return string CSS rules to apply.
     */
    private function generate_custom_css() {
        $styles = $this->options['styles'] ?? array();
        $css_vars = array(
            '--wpfed-bg-color'       => $styles['background_color'] ?? '#f9f9f9',
            '--wpfed-border-color'   => $styles['border_color'] ?? '#e0e0e0',
            '--wpfed-text-color'     => $styles['text_color'] ?? '#333333',
            '--wpfed-header-color'   => $styles['header_color'] ?? '#444444',
            '--wpfed-border-radius'  => $styles['border_radius'] ?? '5px',
            '--wpfed-padding'        => $styles['padding'] ?? '15px',
            '--wpfed-box-shadow'     => ($styles['box_shadow'] ?? 'none') === 'none' ? 'none' : ($styles['box_shadow'] ?? '0 2px 5px rgba(0,0,0,0.1)'),
            '--wpfed-vertical-align' => $styles['vertical_alignment'] ?? 'top',
            // Updated typography variables for all field types
            '--wpfed-date-font-size'     => $styles['date_font_size'] ?? '14px',
            '--wpfed-date-font-weight'   => $styles['date_font_weight'] ?? 'bold',
            '--wpfed-date-font-style'    => $styles['date_font_style'] ?? 'normal',
            '--wpfed-username-font-size' => $styles['username_font_size'] ?? '14px',
            '--wpfed-username-font-weight' => $styles['username_font_weight'] ?? 'bold',
            '--wpfed-username-font-style' => $styles['username_font_style'] ?? 'normal',
            '--wpfed-email-font-size'    => $styles['email_font_size'] ?? '14px',
            '--wpfed-email-font-weight'  => $styles['email_font_weight'] ?? 'normal',
            '--wpfed-email-font-style'   => $styles['email_font_style'] ?? 'normal',
            '--wpfed-field-labels-font-size' => $styles['field_labels_font_size'] ?? '14px',
            '--wpfed-field-labels-font-weight' => $styles['field_labels_font_weight'] ?? 'bold',
            '--wpfed-field-labels-font-style' => $styles['field_labels_font_style'] ?? 'normal',
            '--wpfed-comment-font-size'  => $styles['comment_font_size'] ?? '16px',
            '--wpfed-comment-font-weight' => $styles['comment_font_weight'] ?? 'normal',
            '--wpfed-comment-font-style' => $styles['comment_font_style'] ?? 'normal',
        );

        $css = ':root {';
        foreach ($css_vars as $var => $value) {
            $css .= esc_attr($var) . ': ' . esc_attr($value) . ';';
        }
        $css .= '}';

        // Add enhanced CSS with !important declarations to override external theme editors
        $css .= '
        /* Vertical alignment for entry content - DISABLED
        .wpfed-comment-content {
            display: flex !important;
            flex-direction: column !important;
        }
        
        .wpfed-comment-content {
            justify-content: ' . ($styles['vertical_alignment'] === 'middle' ? 'center' : ($styles['vertical_alignment'] === 'bottom' ? 'flex-end' : 'flex-start')) . ' !important;
        }
        */
        
        /* Date Typography */
        .wpfed-comment-date {
            font-size: var(--wpfed-date-font-size) !important;
            font-weight: var(--wpfed-date-font-weight) !important;
            font-style: var(--wpfed-date-font-style) !important;
        }
        
        /* Username Typography */
        .wpfed-username {
            font-size: var(--wpfed-username-font-size) !important;
            font-weight: var(--wpfed-username-font-weight) !important;
            font-style: var(--wpfed-username-font-style) !important;
        }
        
        /* Field Labels Typography */
        .wpfed-field-label {
            font-size: var(--wpfed-field-labels-font-size) !important;
            font-weight: var(--wpfed-field-labels-font-weight) !important;
            font-style: var(--wpfed-field-labels-font-style) !important;
        }
        
        /* Email Field Typography */
        .wpfed-field-email .wpfed-field-value {
            font-size: var(--wpfed-email-font-size) !important;
            font-weight: var(--wpfed-email-font-weight) !important;
            font-style: var(--wpfed-email-font-style) !important;
        }
        
        /* Comment Field Typography */
        .wpfed-field-comment .wpfed-field-value {
            font-size: var(--wpfed-comment-font-size) !important;
            font-weight: var(--wpfed-comment-font-weight) !important;
            font-style: var(--wpfed-comment-font-style) !important;
        }
        
        /* General Field Typography (fallback for other fields) */
        .wpfed-field-general .wpfed-field-value {
            font-size: var(--wpfed-comment-font-size) !important;
            font-weight: var(--wpfed-comment-font-weight) !important;
            font-style: var(--wpfed-comment-font-style) !important;
        }
        
        /* Ensure typography overrides work with common page builders */
        .elementor .wpfed-comment-date,
        .elementor-widget .wpfed-comment-date,
        .et_pb_module .wpfed-comment-date,
        .vc_row .wpfed-comment-date {
            font-size: var(--wpfed-date-font-size) !important;
            font-weight: var(--wpfed-date-font-weight) !important;
            font-style: var(--wpfed-date-font-style) !important;
        }
        
        .elementor .wpfed-username,
        .elementor-widget .wpfed-username,
        .et_pb_module .wpfed-username,
        .vc_row .wpfed-username {
            font-size: var(--wpfed-username-font-size) !important;
            font-weight: var(--wpfed-username-font-weight) !important;
            font-style: var(--wpfed-username-font-style) !important;
        }
        
        .elementor .wpfed-field-label,
        .elementor-widget .wpfed-field-label,
        .et_pb_module .wpfed-field-label,
        .vc_row .wpfed-field-label {
            font-size: var(--wpfed-field-labels-font-size) !important;
            font-weight: var(--wpfed-field-labels-font-weight) !important;
            font-style: var(--wpfed-field-labels-font-style) !important;
        }
        
        .elementor .wpfed-field-email .wpfed-field-value,
        .elementor-widget .wpfed-field-email .wpfed-field-value,
        .et_pb_module .wpfed-field-email .wpfed-field-value,
        .vc_row .wpfed-field-email .wpfed-field-value {
            font-size: var(--wpfed-email-font-size) !important;
            font-weight: var(--wpfed-email-font-weight) !important;
            font-style: var(--wpfed-email-font-style) !important;
        }
        
        .elementor .wpfed-field-comment .wpfed-field-value,
        .elementor-widget .wpfed-field-comment .wpfed-field-value,
        .et_pb_module .wpfed-field-comment .wpfed-field-value,
        .vc_row .wpfed-field-comment .wpfed-field-value {
            font-size: var(--wpfed-comment-font-size) !important;
            font-weight: var(--wpfed-comment-font-weight) !important;
            font-style: var(--wpfed-comment-font-style) !important;
        }
        ';

        return $css;
    }

    /**
     * Generates an HTML-formatted error message.
     *
     * @param string $message The error message text.
     * @return string The formatted error message.
     */
    private function get_error_message($message) {
        return '<div class="wpfed-error">' . $message . '</div>';
    }

    /**
     * Determine field type class for styling
     *
     * @param array $form_field The form field data
     * @return string CSS class for field type
     */
    private function get_field_type_class($form_field) {
        $field_type = $form_field['type'] ?? '';
        $field_label = strtolower($form_field['label'] ?? '');
        
        // Check for email fields
        if ($field_type === 'email' || stripos($field_label, 'email') !== false) {
            return 'wpfed-field-email';
        }
        
        // Check for comment/message fields
        if ($field_type === 'textarea' || 
            stripos($field_label, 'comment') !== false || 
            stripos($field_label, 'message') !== false ||
            stripos($field_label, 'description') !== false) {
            return 'wpfed-field-comment';
        }
        
        return 'wpfed-field-general';
    }
}

// Initialize the shortcode handler class.
new WPFED_Shortcode();