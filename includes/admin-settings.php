<?php
// Exit if accessed directly to prevent unauthorized access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Admin settings class for Entries Display for WPForms
 * Handles adding and registering admin settings page and options
 */
class WPFED_Admin_Settings {
    
    /**
     * Constructor to initialize action hooks
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('wp_ajax_wpfed_get_form_fields', array($this, 'get_form_fields_ajax'));
    }
    
    /**
     * Add admin submenu page under Settings
     */
    public function add_admin_menu() {
        add_submenu_page(
            'options-general.php',
            esc_html__('Entries Display for WPForms', 'entries-display-for-wpforms'),
            esc_html__('Entries Display for WPForms', 'entries-display-for-wpforms'),
            'manage_options',
            'entries-display-for-wpforms',
            array($this, 'display_settings_page')
        );
    }
    
    /**
     * Register plugin settings and sections
     */
    public function register_settings() {
        register_setting(
            'wpfed_options_group',
            'wpfed_options',
            array(
                'type' => 'array',
                'description' => esc_html__('Settings for Entries Display for WPForms plugin', 'entries-display-for-wpforms'),
                'sanitize_callback' => array($this, 'sanitize_options'),
                'default' => array(
                    'form_id' => 0,
                    'display_fields' => array(),
                    'entries_per_page' => 30,
                    'show_date' => 'yes',
                    'date_format' => 'F j, Y g:i a',
                    'show_username' => 'no',
                    'hide_field_labels' => 'no',
                    'hide_empty_fields' => 'no',
                    'styles' => array(
                        'background_color' => '#f9f9f9',
                        'border_color' => '#e0e0e0',
                        'text_color' => '#333333',
                        'header_color' => '#444444',
                        'border_radius' => '5px',
                        'padding' => '15px',
                        'box_shadow' => '0 2px 5px rgba(0,0,0,0.1)',
                        'vertical_alignment' => 'top',
                        'date_font_size' => '14px',
                        'date_font_weight' => 'bold',
                        'date_font_style' => 'normal',
                        'username_font_size' => '14px',
                        'username_font_weight' => 'bold',
                        'username_font_style' => 'normal',
                        'email_font_size' => '14px',
                        'email_font_weight' => 'normal',
                        'email_font_style' => 'normal',
                        'field_labels_font_size' => '14px',
                        'field_labels_font_weight' => 'bold',
                        'field_labels_font_style' => 'normal',
                        'comment_font_size' => '16px',
                        'comment_font_weight' => 'normal',
                        'comment_font_style' => 'normal',
                    )
                )
            )
        );
        
        // General Settings Section
        add_settings_section(
            'wpfed_main_section',
            __('General Settings', 'entries-display-for-wpforms'),
            array($this, 'main_section_callback'),
            'wpfed_settings'
        );
        
        add_settings_field(
            'form_id',
            __('Default Form', 'entries-display-for-wpforms'),
            array($this, 'form_id_callback'),
            'wpfed_settings',
            'wpfed_main_section'
        );
        
        add_settings_field(
            'display_fields',
            __('Default Fields to Display', 'entries-display-for-wpforms'),
            array($this, 'display_fields_callback'),
            'wpfed_settings',
            'wpfed_main_section'
        );
        
        add_settings_field(
            'entries_per_page',
            __('Default Entries Per Page', 'entries-display-for-wpforms'),
            array($this, 'entries_per_page_callback'),
            'wpfed_settings',
            'wpfed_main_section'
        );
        
        // Style Settings Section
        add_settings_section(
            'wpfed_style_section',
            __('Style Settings', 'entries-display-for-wpforms'),
            array($this, 'style_section_callback'),
            'wpfed_settings'
        );
        
        add_settings_field(
            'background_color',
            __('Background Color', 'entries-display-for-wpforms'),
            array($this, 'background_color_callback'),
            'wpfed_settings',
            'wpfed_style_section'
        );
        
        add_settings_field(
            'border_color',
            __('Border Color', 'entries-display-for-wpforms'),
            array($this, 'border_color_callback'),
            'wpfed_settings',
            'wpfed_style_section'
        );
        
        add_settings_field(
            'text_color',
            __('Text Color', 'entries-display-for-wpforms'),
            array($this, 'text_color_callback'),
            'wpfed_settings',
            'wpfed_style_section'
        );
        
        add_settings_field(
            'header_color',
            __('Header Color', 'entries-display-for-wpforms'),
            array($this, 'header_color_callback'),
            'wpfed_settings',
            'wpfed_style_section'
        );
        
        add_settings_field(
            'border_radius',
            __('Border Radius', 'entries-display-for-wpforms'),
            array($this, 'border_radius_callback'),
            'wpfed_settings',
            'wpfed_style_section'
        );
        
        add_settings_field(
            'padding',
            __('Padding', 'entries-display-for-wpforms'),
            array($this, 'padding_callback'),
            'wpfed_settings',
            'wpfed_style_section'
        );
        
        add_settings_field(
            'box_shadow',
            __('Box Shadow', 'entries-display-for-wpforms'),
            array($this, 'box_shadow_callback'),
            'wpfed_settings',
            'wpfed_style_section'
        );
        
        /*
        add_settings_field(
            'vertical_alignment',
            __('Vertical Alignment', 'entries-display-for-wpforms'),
            array($this, 'vertical_alignment_callback'),
            'wpfed_settings',
            'wpfed_style_section'
        );
        */
        
        // Typography Settings Section
        add_settings_section(
            'wpfed_typography_section',
            __('Typography Settings', 'entries-display-for-wpforms'),
            array($this, 'typography_section_callback'),
            'wpfed_settings'
        );
        
        add_settings_field(
            'date_typography',
            __('Date Typography', 'entries-display-for-wpforms'),
            array($this, 'date_typography_callback'),
            'wpfed_settings',
            'wpfed_typography_section'
        );
        
        add_settings_field(
            'username_typography',
            __('Username Typography', 'entries-display-for-wpforms'),
            array($this, 'username_typography_callback'),
            'wpfed_settings',
            'wpfed_typography_section'
        );
        
        add_settings_field(
            'email_typography',
            __('Email Typography', 'entries-display-for-wpforms'),
            array($this, 'email_typography_callback'),
            'wpfed_settings',
            'wpfed_typography_section'
        );
        
        add_settings_field(
            'field_labels_typography',
            __('Field Labels Typography', 'entries-display-for-wpforms'),
            array($this, 'field_labels_typography_callback'),
            'wpfed_settings',
            'wpfed_typography_section'
        );
        
        add_settings_field(
            'comment_typography',
            __('Comment Typography', 'entries-display-for-wpforms'),
            array($this, 'comment_typography_callback'),
            'wpfed_settings',
            'wpfed_typography_section'
        );
        
        // Display Settings Section
        add_settings_section(
            'wpfed_display_section',
            __('Display Settings', 'entries-display-for-wpforms'),
            array($this, 'display_section_callback'),
            'wpfed_settings'
        );
        
        add_settings_field(
            'show_date',
            __('Show Entry Date', 'entries-display-for-wpforms'),
            array($this, 'show_date_callback'),
            'wpfed_settings',
            'wpfed_display_section'
        );
        
        add_settings_field(
            'date_format',
            __('Date Format', 'entries-display-for-wpforms'),
            array($this, 'date_format_callback'),
            'wpfed_settings',
            'wpfed_display_section'
        );
        
        add_settings_field(
            'show_username',
            __('Show Username', 'entries-display-for-wpforms'),
            array($this, 'show_username_callback'),
            'wpfed_settings',
            'wpfed_display_section'
        );
        
        add_settings_field(
            'hide_field_labels',
            __('Hide Field Labels', 'entries-display-for-wpforms'),
            array($this, 'hide_field_labels_callback'),
            'wpfed_settings',
            'wpfed_display_section'
        );
        
        add_settings_field(
            'hide_empty_fields',
            __('Hide Empty Fields', 'entries-display-for-wpforms'),
            array($this, 'hide_empty_fields_callback'),
            'wpfed_settings',
            'wpfed_display_section'
        );
    }
    
    /**
     * Sanitize user input before saving to the database
     *
     * @param array $input Raw input data
     * @return array Sanitized input
     */
    public function sanitize_options($input) {
        $sanitized_input = array();
        
        // Sanitize form ID
        $sanitized_input['form_id'] = isset($input['form_id']) ? absint($input['form_id']) : 0;
        
        // Sanitize display fields
        if (isset($input['display_fields']) && is_array($input['display_fields'])) {
            $sanitized_input['display_fields'] = array_map('absint', $input['display_fields']);
        } else {
            $sanitized_input['display_fields'] = array();
        }
        
        // Sanitize entries per page
        $sanitized_input['entries_per_page'] = isset($input['entries_per_page']) ? absint($input['entries_per_page']) : 30;
        if ($sanitized_input['entries_per_page'] < 1) {
            $sanitized_input['entries_per_page'] = 30;
        }
        
        // Sanitize style settings including typography
        $sanitized_input['styles'] = array(
            'background_color' => isset($input['styles']['background_color']) ? sanitize_hex_color($input['styles']['background_color']) : '#f9f9f9',
            'border_color' => isset($input['styles']['border_color']) ? sanitize_hex_color($input['styles']['border_color']) : '#e0e0e0',
            'text_color' => isset($input['styles']['text_color']) ? sanitize_hex_color($input['styles']['text_color']) : '#333333',
            'header_color' => isset($input['styles']['header_color']) ? sanitize_hex_color($input['styles']['header_color']) : '#444444',
            'border_radius' => isset($input['styles']['border_radius']) ? sanitize_text_field($input['styles']['border_radius']) : '5px',
            'padding' => isset($input['styles']['padding']) ? sanitize_text_field($input['styles']['padding']) : '15px',
            'box_shadow' => isset($input['styles']['box_shadow']) ? sanitize_text_field($input['styles']['box_shadow']) : '0 2px 5px rgba(0,0,0,0.1)',
            'vertical_alignment' => isset($input['styles']['vertical_alignment']) ? sanitize_text_field($input['styles']['vertical_alignment']) : 'top',
            // Updated typography settings
            'date_font_size' => isset($input['styles']['date_font_size']) ? sanitize_text_field($input['styles']['date_font_size']) : '14px',
            'date_font_weight' => isset($input['styles']['date_font_weight']) ? sanitize_text_field($input['styles']['date_font_weight']) : 'bold',
            'date_font_style' => isset($input['styles']['date_font_style']) ? sanitize_text_field($input['styles']['date_font_style']) : 'normal',
            'username_font_size' => isset($input['styles']['username_font_size']) ? sanitize_text_field($input['styles']['username_font_size']) : '14px',
            'username_font_weight' => isset($input['styles']['username_font_weight']) ? sanitize_text_field($input['styles']['username_font_weight']) : 'bold',
            'username_font_style' => isset($input['styles']['username_font_style']) ? sanitize_text_field($input['styles']['username_font_style']) : 'normal',
            'email_font_size' => isset($input['styles']['email_font_size']) ? sanitize_text_field($input['styles']['email_font_size']) : '14px',
            'email_font_weight' => isset($input['styles']['email_font_weight']) ? sanitize_text_field($input['styles']['email_font_weight']) : 'normal',
            'email_font_style' => isset($input['styles']['email_font_style']) ? sanitize_text_field($input['styles']['email_font_style']) : 'normal',
            'field_labels_font_size' => isset($input['styles']['field_labels_font_size']) ? sanitize_text_field($input['styles']['field_labels_font_size']) : '14px',
            'field_labels_font_weight' => isset($input['styles']['field_labels_font_weight']) ? sanitize_text_field($input['styles']['field_labels_font_weight']) : 'bold',
            'field_labels_font_style' => isset($input['styles']['field_labels_font_style']) ? sanitize_text_field($input['styles']['field_labels_font_style']) : 'normal',
            'comment_font_size' => isset($input['styles']['comment_font_size']) ? sanitize_text_field($input['styles']['comment_font_size']) : '16px',
            'comment_font_weight' => isset($input['styles']['comment_font_weight']) ? sanitize_text_field($input['styles']['comment_font_weight']) : 'normal',
            'comment_font_style' => isset($input['styles']['comment_font_style']) ? sanitize_text_field($input['styles']['comment_font_style']) : 'normal',
        );
        
        // Sanitize other settings
        $sanitized_input['show_date'] = isset($input['show_date']) && $input['show_date'] === 'yes' ? 'yes' : 'no';
        $sanitized_input['date_format'] = sanitize_text_field($input['date_format']);
        $sanitized_input['show_username'] = isset($input['show_username']) && $input['show_username'] === 'yes' ? 'yes' : 'no';
        $sanitized_input['hide_field_labels'] = isset($input['hide_field_labels']) && $input['hide_field_labels'] === 'yes' ? 'yes' : 'no';
        $sanitized_input['hide_empty_fields'] = isset($input['hide_empty_fields']) && $input['hide_empty_fields'] === 'yes' ? 'yes' : 'no';
        
        return $sanitized_input;
    }

    /**
     * Display section callback description
     */
    public function display_section_callback() {
        echo '<p>' . esc_html__('Configure how entries are displayed on your site.', 'entries-display-for-wpforms') . '</p>';
    }

    /**
     * Box shadow setting callback for selecting shadow intensity
     */
    public function box_shadow_callback() {
        $options = get_option('wpfed_options');
        $box_shadow = isset($options['styles']['box_shadow']) ? $options['styles']['box_shadow'] : 'none';
        
        echo '<select name="wpfed_options[styles][box_shadow]" id="wpfed_box_shadow" class="regular-text">';
        echo '<option value="none" ' . selected($box_shadow, 'none', false) . '>' . esc_html__('None', 'entries-display-for-wpforms') . '</option>';
        echo '<option value="0 2px 5px rgba(0,0,0,0.1)" ' . selected($box_shadow, '0 2px 5px rgba(0,0,0,0.1)', false) . '>' . esc_html__('Light', 'entries-display-for-wpforms') . '</option>';
        echo '<option value="0 3px 10px rgba(0,0,0,0.15)" ' . selected($box_shadow, '0 3px 10px rgba(0,0,0,0.15)', false) . '>' . esc_html__('Medium', 'entries-display-for-wpforms') . '</option>';
        echo '<option value="0 5px 15px rgba(0,0,0,0.2)" ' . selected($box_shadow, '0 5px 15px rgba(0,0,0,0.2)', false) . '>' . esc_html__('Strong', 'entries-display-for-wpforms') . '</option>';
        echo '<option value="0 10px 25px rgba(0,0,0,0.25)" ' . selected($box_shadow, '0 10px 25px rgba(0,0,0,0.25)', false) . '>' . esc_html__('Extra Strong', 'entries-display-for-wpforms') . '</option>';
        echo '</select>';
        echo '<p class="description">' . esc_html__('Select the shadow effect for comment boxes.', 'entries-display-for-wpforms') . '</p>';
    }

    /**
     * Show entry date setting callback
     */
    public function show_date_callback() {
        $options = get_option('wpfed_options');
        $value = isset($options['show_date']) ? $options['show_date'] : 'yes';
        echo '<label class="wpfed-toggle-switch">';
        echo '<input type="checkbox" name="wpfed_options[show_date]" value="yes" ' . checked($value, 'yes', false) . '>';
        echo '<span class="wpfed-toggle-slider"></span>';
        echo '</label>';
    }
    
    /**
     * Date format setting callback
     */
    public function date_format_callback() {
        $options = get_option('wpfed_options');
        $date_format = isset($options['date_format']) ? $options['date_format'] : 'F j, Y g:i a';
        
        $date_formats = array(
            'F j, Y g:i a' => date_i18n('F j, Y g:i a'),
            'Y-m-d H:i:s' => date_i18n('Y-m-d H:i:s'),
            'm/d/Y g:i a' => date_i18n('m/d/Y g:i a'),
            'd/m/Y g:i a' => date_i18n('d/m/Y g:i a'),
            'F j, Y' => date_i18n('F j, Y'),
            'j F Y' => date_i18n('j F Y'),
            'g:i a - F j, Y' => date_i18n('g:i a - F j, Y'),
        );
        
        echo '<select name="wpfed_options[date_format]" id="wpfed_date_format" class="regular-text">';
        foreach ($date_formats as $format => $display) {
            echo '<option value="' . esc_attr($format) . '" ' . selected($date_format, $format, false) . '>' . esc_html($display) . '</option>';
        }
        echo '</select>';
        echo '<p class="description">' . esc_html__('Select the format for displaying the entry date.', 'entries-display-for-wpforms') . '</p>';
        
        /*
        echo '<div class="wpfed-custom-date-format">';
        echo '<label for="wpfed_custom_date_format">' . esc_html__('Custom Format:', 'entries-display-for-wpforms') . '</label>';
        echo '<input type="text" id="wpfed_custom_date_format" class="regular-text" placeholder="Y-m-d H:i:s">';
        echo '<button type="button" id="wpfed_apply_custom_date" class="button button-secondary">' . esc_html__('Apply Custom Format', 'entries-display-for-wpforms') . '</button>';
        echo '</div>';
        */
    }

    /**
     * Main section callback description
     */
    public function main_section_callback() {
        echo '<p>' . esc_html__('Configure default settings for displaying WPForms entries. These settings can be overridden using shortcode attributes.', 'entries-display-for-wpforms') . '</p>';
    }
    
    /**
     * Style section callback description
     */
    public function style_section_callback() {
        echo '<p>' . esc_html__('Customize the appearance of the entries display.', 'entries-display-for-wpforms') . '</p>';
    }
    
    /**
     * Callback for default form ID setting, including AJAX field loading
     */
    public function form_id_callback() {
        $options = get_option('wpfed_options');
        $form_id = isset($options['form_id']) ? $options['form_id'] : '';
        $forms = $this->get_wpforms();
        
        echo '<select name="wpfed_options[form_id]" id="wpfed_form_id" class="regular-text">';
        echo '<option value="">' . esc_html__('Select a form', 'entries-display-for-wpforms') . '</option>';
        
        foreach ($forms as $id => $title) {
            echo '<option value="' . esc_attr($id) . '" ' . selected($form_id, $id, false) . '>' . esc_html($title) . '</option>';
        }
        
        echo '</select>';
        echo '<p class="description">' . esc_html__('Select the default WPForms form to display entries from.', 'entries-display-for-wpforms') . '</p>';
    }
        
        /**
         * Callback for default fields selection
         */
        public function display_fields_callback() {
            $options = get_option('wpfed_options');
            $form_id = isset($options['form_id']) ? $options['form_id'] : '';
            $display_fields = isset($options['display_fields']) ? $options['display_fields'] : array();
        
        echo '<div id="wpfed_display_fields_container">';
        
        if (!empty($form_id)) {
            $fields = $this->get_form_fields($form_id);
            if (!empty($fields)) {
                foreach ($fields as $field) {
                    $checked = in_array($field['id'], $display_fields) ? 'checked' : '';
                    echo '<label><input type="checkbox" name="wpfed_options[display_fields][]" value="' . esc_attr($field['id']) . '" ' . esc_attr($checked) . ' class="wpfed-field-checkbox"> ' . esc_html($field['label']) . ' (ID: ' . esc_html($field['id']) . ')</label><br>';
                }
            } else {
                echo '<p>' . esc_html__('No fields found in this form.', 'entries-display-for-wpforms') . '</p>';
            }
        } else {
            echo '<p>' . esc_html__('Please select a form first.', 'entries-display-for-wpforms') . '</p>';
        }
        
        echo '</div>';
        echo '<p class="description">' . esc_html__('Select which fields to display in the entries view by default.', 'entries-display-for-wpforms') . '</p>';
    }
    
    /**
     * Callback for entries per page setting
     */
    public function entries_per_page_callback() {
        $options = get_option('wpfed_options');
        $entries_per_page = isset($options['entries_per_page']) ? $options['entries_per_page'] : 30;
        
        echo '<input type="number" name="wpfed_options[entries_per_page]" id="wpfed_entries_per_page" value="' . esc_attr($entries_per_page) . '" class="small-text" min="1">';
        echo '<p class="description">' . esc_html__('Default number of entries to display per page.', 'entries-display-for-wpforms') . '</p>';
    }
    
    /**
     * Callback for background color setting using a color picker
     */
    public function background_color_callback() {
        $options = get_option('wpfed_options');
        $background_color = isset($options['styles']['background_color']) ? $options['styles']['background_color'] : '#f9f9f9';
        
        echo '<input type="text" name="wpfed_options[styles][background_color]" id="wpfed_background_color" value="' . esc_attr($background_color) . '" class="wpfed-color-picker">';
    }
    
    /**
     * Callback for border color setting using a color picker
     */
    public function border_color_callback() {
        $options = get_option('wpfed_options');
        $border_color = isset($options['styles']['border_color']) ? $options['styles']['border_color'] : '#e0e0e0';
        
        echo '<input type="text" name="wpfed_options[styles][border_color]" id="wpfed_border_color" value="' . esc_attr($border_color) . '" class="wpfed-color-picker">';
    }
    
    /**
     * Callback for text color setting using a color picker
     */
    public function text_color_callback() {
        $options = get_option('wpfed_options');
        $text_color = isset($options['styles']['text_color']) ? $options['styles']['text_color'] : '#333333';
        
        echo '<input type="text" name="wpfed_options[styles][text_color]" id="wpfed_text_color" value="' . esc_attr($text_color) . '" class="wpfed-color-picker">';
    }
    
    /**
     * Callback for header color setting using a color picker
     */
    public function header_color_callback() {
        $options = get_option('wpfed_options');
        $header_color = isset($options['styles']['header_color']) ? $options['styles']['header_color'] : '#444444';
        
        echo '<input type="text" name="wpfed_options[styles][header_color]" id="wpfed_header_color" value="' . esc_attr($header_color) . '" class="wpfed-color-picker">';
    }
    
    /**
     * Callback for border radius setting
     */
    public function border_radius_callback() {
        $options = get_option('wpfed_options');
        $border_radius = isset($options['styles']['border_radius']) ? $options['styles']['border_radius'] : '5px';
        
        echo '<select name="wpfed_options[styles][border_radius]" id="wpfed_border_radius" class="regular-text">';
        for ($i = 0; $i <= 50; $i += 5) {
            echo '<option value="' . esc_attr($i . 'px') . '" ' . selected($border_radius, $i . 'px', false) . '>' . esc_html($i . 'px') . '</option>';
        }
        echo '</select>';
        echo '<p class="description">' . esc_html__('Select the border radius for comment boxes.', 'entries-display-for-wpforms') . '</p>';
    }
    
    /**
     * Callback for padding setting
     */
    public function padding_callback() {
        $options = get_option('wpfed_options');
        $padding = isset($options['styles']['padding']) ? $options['styles']['padding'] : '15px';
        
        echo '<select name="wpfed_options[styles][padding]" id="wpfed_padding" class="regular-text">';
        for ($i = 5; $i <= 50; $i += 5) {
            echo '<option value="' . esc_attr($i . 'px') . '" ' . selected($padding, $i . 'px', false) . '>' . esc_html($i . 'px') . '</option>';
        }
        echo '</select>';
        echo '<p class="description">' . esc_html__('Select the padding for comment boxes.', 'entries-display-for-wpforms') . '</p>';
    }
    
    /**
     * Callback for the setting to show or hide username
     */
    public function show_username_callback() {
        $options = get_option('wpfed_options');
        $value = isset($options['show_username']) ? $options['show_username'] : 'no';
        echo '<label class="wpfed-toggle-switch">';
        echo '<input type="checkbox" name="wpfed_options[show_username]" value="yes" ' . checked($value, 'yes', false) . '>';
        echo '<span class="wpfed-toggle-slider"></span>';
        echo '</label>';
        echo '<p class="description">' . esc_html__('Show the username of the user who submitted the entry (if available).', 'entries-display-for-wpforms') . '</p>';
    }
    
    /**
     * Callback for the setting to hide field labels
     */
    public function hide_field_labels_callback() {
        $options = get_option('wpfed_options');
        $value = isset($options['hide_field_labels']) ? $options['hide_field_labels'] : 'no';
        echo '<label class="wpfed-toggle-switch">';
        echo '<input type="checkbox" name="wpfed_options[hide_field_labels]" value="yes" ' . checked($value, 'yes', false) . '>';
        echo '<span class="wpfed-toggle-slider"></span>';
        echo '</label>';
        echo '<p class="description">' . esc_html__('Hide field labels in the comment display (e.g., "Single Line Text:").', 'entries-display-for-wpforms') . '</p>';
    }
    
    /**
     * Callback for the setting to hide empty fields
     */
    public function hide_empty_fields_callback() {
        $options = get_option('wpfed_options');
        $value = isset($options['hide_empty_fields']) ? $options['hide_empty_fields'] : 'no';
        echo '<label class="wpfed-toggle-switch">';
        echo '<input type="checkbox" name="wpfed_options[hide_empty_fields]" value="yes" ' . checked($value, 'yes', false) . '>';
        echo '<span class="wpfed-toggle-slider"></span>';
        echo '</label>';
        echo '<p class="description">' . esc_html__('Hide fields that have no value.', 'entries-display-for-wpforms') . '</p>';
    }
    
    /**
     * Callback for vertical alignment setting
     */
    public function vertical_alignment_callback() {
        $options = get_option('wpfed_options');
        $vertical_alignment = isset($options['styles']['vertical_alignment']) ? $options['styles']['vertical_alignment'] : 'top';
        
        echo '<select name="wpfed_options[styles][vertical_alignment]" id="wpfed_vertical_alignment" class="regular-text">';
        echo '<option value="top" ' . selected($vertical_alignment, 'top', false) . '>' . esc_html__('Top', 'entries-display-for-wpforms') . '</option>';
        echo '<option value="middle" ' . selected($vertical_alignment, 'middle', false) . '>' . esc_html__('Middle', 'entries-display-for-wpforms') . '</option>';
        echo '<option value="bottom" ' . selected($vertical_alignment, 'bottom', false) . '>' . esc_html__('Bottom', 'entries-display-for-wpforms') . '</option>';
        echo '</select>';
        echo '<p class="description">' . esc_html__('Select the vertical alignment for the entry content.', 'entries-display-for-wpforms') . '</p>';
    }
    
    /**
     * Typography section callback description
     */
    public function typography_section_callback() {
        echo '<p>' . esc_html__('Customize typography settings for different elements. These settings use !important CSS to override theme and page builder styles.', 'entries-display-for-wpforms') . '</p>';
    }
    
    /**
     * Callback for date typography settings
     */
    public function date_typography_callback() {
        $options = get_option('wpfed_options');
        $date_font_size = isset($options['styles']['date_font_size']) ? $options['styles']['date_font_size'] : '14px';
        $date_font_weight = isset($options['styles']['date_font_weight']) ? $options['styles']['date_font_weight'] : 'bold';
        $date_font_style = isset($options['styles']['date_font_style']) ? $options['styles']['date_font_style'] : 'normal';
        
        echo '<div class="wpfed-typography-controls">';
        
        // Font Size
        echo '<div class="wpfed-typography-control">';
        echo '<label for="wpfed_date_font_size">' . esc_html__('Size', 'entries-display-for-wpforms') . '</label>';
        echo '<select name="wpfed_options[styles][date_font_size]" id="wpfed_date_font_size">';
        $font_sizes = array('10px', '12px', '14px', '16px', '18px', '20px', '22px', '24px', '26px', '28px', '30px');
        foreach ($font_sizes as $size) {
            echo '<option value="' . esc_attr($size) . '" ' . selected($date_font_size, $size, false) . '>' . esc_html($size) . '</option>';
        }
        echo '</select>';
        echo '</div>';
        
        // Font Weight
        echo '<div class="wpfed-typography-control">';
        echo '<label for="wpfed_date_font_weight">' . esc_html__('Weight', 'entries-display-for-wpforms') . '</label>';
        echo '<select name="wpfed_options[styles][date_font_weight]" id="wpfed_date_font_weight">';
        $font_weights = array('normal' => 'Normal', 'bold' => 'Bold', '100' => '100', '200' => '200', '300' => '300', '400' => '400', '500' => '500', '600' => '600', '700' => '700', '800' => '800', '900' => '900');
        foreach ($font_weights as $weight => $label) {
            echo '<option value="' . esc_attr($weight) . '" ' . selected($date_font_weight, $weight, false) . '>' . esc_html($label) . '</option>';
        }
        echo '</select>';
        echo '</div>';
        
        // Font Style
        echo '<div class="wpfed-typography-control">';
        echo '<label for="wpfed_date_font_style">' . esc_html__('Style', 'entries-display-for-wpforms') . '</label>';
        echo '<select name="wpfed_options[styles][date_font_style]" id="wpfed_date_font_style">';
        $font_styles = array('normal' => 'Normal', 'italic' => 'Italic');
        foreach ($font_styles as $style => $label) {
            echo '<option value="' . esc_attr($style) . '" ' . selected($date_font_style, $style, false) . '>' . esc_html($label) . '</option>';
        }
        echo '</select>';
        echo '</div>';
        
        echo '</div>';
        echo '<p class="description">' . esc_html__('Typography settings for entry dates.', 'entries-display-for-wpforms') . '</p>';
    }
    
    /**
     * Callback for username typography settings
     */
    public function username_typography_callback() {
        $options = get_option('wpfed_options');
        $username_font_size = isset($options['styles']['username_font_size']) ? $options['styles']['username_font_size'] : '14px';
        $username_font_weight = isset($options['styles']['username_font_weight']) ? $options['styles']['username_font_weight'] : 'bold';
        $username_font_style = isset($options['styles']['username_font_style']) ? $options['styles']['username_font_style'] : 'normal';
        
        echo '<div class="wpfed-typography-controls">';
        
        // Font Size
        echo '<div class="wpfed-typography-control">';
        echo '<label for="wpfed_username_font_size">' . esc_html__('Size', 'entries-display-for-wpforms') . '</label>';
        echo '<select name="wpfed_options[styles][username_font_size]" id="wpfed_username_font_size">';
        $font_sizes = array('10px', '12px', '14px', '16px', '18px', '20px', '22px', '24px', '26px', '28px', '30px');
        foreach ($font_sizes as $size) {
            echo '<option value="' . esc_attr($size) . '" ' . selected($username_font_size, $size, false) . '>' . esc_html($size) . '</option>';
        }
        echo '</select>';
        echo '</div>';
        
        // Font Weight
        echo '<div class="wpfed-typography-control">';
        echo '<label for="wpfed_username_font_weight">' . esc_html__('Weight', 'entries-display-for-wpforms') . '</label>';
        echo '<select name="wpfed_options[styles][username_font_weight]" id="wpfed_username_font_weight">';
        $font_weights = array('normal' => 'Normal', 'bold' => 'Bold', '100' => '100', '200' => '200', '300' => '300', '400' => '400', '500' => '500', '600' => '600', '700' => '700', '800' => '800', '900' => '900');
        foreach ($font_weights as $weight => $label) {
            echo '<option value="' . esc_attr($weight) . '" ' . selected($username_font_weight, $weight, false) . '>' . esc_html($label) . '</option>';
        }
        echo '</select>';
        echo '</div>';
        
        // Font Style
        echo '<div class="wpfed-typography-control">';
        echo '<label for="wpfed_username_font_style">' . esc_html__('Style', 'entries-display-for-wpforms') . '</label>';
        echo '<select name="wpfed_options[styles][username_font_style]" id="wpfed_username_font_style">';
        $font_styles = array('normal' => 'Normal', 'italic' => 'Italic');
        foreach ($font_styles as $style => $label) {
            echo '<option value="' . esc_attr($style) . '" ' . selected($username_font_style, $style, false) . '>' . esc_html($label) . '</option>';
        }
        echo '</select>';
        echo '</div>';
        
        echo '</div>';
        echo '<p class="description">' . esc_html__('Typography settings for usernames.', 'entries-display-for-wpforms') . '</p>';
    }
    
    /**
     * Callback for email typography settings
     */
    public function email_typography_callback() {
        $options = get_option('wpfed_options');
        $email_font_size = isset($options['styles']['email_font_size']) ? $options['styles']['email_font_size'] : '14px';
        $email_font_weight = isset($options['styles']['email_font_weight']) ? $options['styles']['email_font_weight'] : 'normal';
        $email_font_style = isset($options['styles']['email_font_style']) ? $options['styles']['email_font_style'] : 'normal';
        
        echo '<div class="wpfed-typography-controls">';
        
        // Font Size
        echo '<div class="wpfed-typography-control">';
        echo '<label for="wpfed_email_font_size">' . esc_html__('Size', 'entries-display-for-wpforms') . '</label>';
        echo '<select name="wpfed_options[styles][email_font_size]" id="wpfed_email_font_size">';
        $font_sizes = array('10px', '12px', '14px', '16px', '18px', '20px', '22px', '24px', '26px', '28px', '30px');
        foreach ($font_sizes as $size) {
            echo '<option value="' . esc_attr($size) . '" ' . selected($email_font_size, $size, false) . '>' . esc_html($size) . '</option>';
        }
        echo '</select>';
        echo '</div>';
        
        // Font Weight
        echo '<div class="wpfed-typography-control">';
        echo '<label for="wpfed_email_font_weight">' . esc_html__('Weight', 'entries-display-for-wpforms') . '</label>';
        echo '<select name="wpfed_options[styles][email_font_weight]" id="wpfed_email_font_weight">';
        $font_weights = array('normal' => 'Normal', 'bold' => 'Bold', '100' => '100', '200' => '200', '300' => '300', '400' => '400', '500' => '500', '600' => '600', '700' => '700', '800' => '800', '900' => '900');
        foreach ($font_weights as $weight => $label) {
            echo '<option value="' . esc_attr($weight) . '" ' . selected($email_font_weight, $weight, false) . '>' . esc_html($label) . '</option>';
        }
        echo '</select>';
        echo '</div>';
        
        // Font Style
        echo '<div class="wpfed-typography-control">';
        echo '<label for="wpfed_email_font_style">' . esc_html__('Style', 'entries-display-for-wpforms') . '</label>';
        echo '<select name="wpfed_options[styles][email_font_style]" id="wpfed_email_font_style">';
        $font_styles = array('normal' => 'Normal', 'italic' => 'Italic');
        foreach ($font_styles as $style => $label) {
            echo '<option value="' . esc_attr($style) . '" ' . selected($email_font_style, $style, false) . '>' . esc_html($label) . '</option>';
        }
        echo '</select>';
        echo '</div>';
        
        echo '</div>';
        echo '<p class="description">' . esc_html__('Typography settings for email field values.', 'entries-display-for-wpforms') . '</p>';
    }

    /**
     * Callback for field labels typography settings
     */
    public function field_labels_typography_callback() {
        $options = get_option('wpfed_options');
        $field_labels_font_size = isset($options['styles']['field_labels_font_size']) ? $options['styles']['field_labels_font_size'] : '14px';
        $field_labels_font_weight = isset($options['styles']['field_labels_font_weight']) ? $options['styles']['field_labels_font_weight'] : 'bold';
        $field_labels_font_style = isset($options['styles']['field_labels_font_style']) ? $options['styles']['field_labels_font_style'] : 'normal';
        
        echo '<div class="wpfed-typography-controls">';
        
        // Font Size
        echo '<div class="wpfed-typography-control">';
        echo '<label for="wpfed_field_labels_font_size">' . esc_html__('Size', 'entries-display-for-wpforms') . '</label>';
        echo '<select name="wpfed_options[styles][field_labels_font_size]" id="wpfed_field_labels_font_size">';
        $font_sizes = array('10px', '12px', '14px', '16px', '18px', '20px', '22px', '24px', '26px', '28px', '30px');
        foreach ($font_sizes as $size) {
            echo '<option value="' . esc_attr($size) . '" ' . selected($field_labels_font_size, $size, false) . '>' . esc_html($size) . '</option>';
        }
        echo '</select>';
        echo '</div>';
        
        // Font Weight
        echo '<div class="wpfed-typography-control">';
        echo '<label for="wpfed_field_labels_font_weight">' . esc_html__('Weight', 'entries-display-for-wpforms') . '</label>';
        echo '<select name="wpfed_options[styles][field_labels_font_weight]" id="wpfed_field_labels_font_weight">';
        $font_weights = array('normal' => 'Normal', 'bold' => 'Bold', '100' => '100', '200' => '200', '300' => '300', '400' => '400', '500' => '500', '600' => '600', '700' => '700', '800' => '800', '900' => '900');
        foreach ($font_weights as $weight => $label) {
            echo '<option value="' . esc_attr($weight) . '" ' . selected($field_labels_font_weight, $weight, false) . '>' . esc_html($label) . '</option>';
        }
        echo '</select>';
        echo '</div>';
        
        // Font Style
        echo '<div class="wpfed-typography-control">';
        echo '<label for="wpfed_field_labels_font_style">' . esc_html__('Style', 'entries-display-for-wpforms') . '</label>';
        echo '<select name="wpfed_options[styles][field_labels_font_style]" id="wpfed_field_labels_font_style">';
        $font_styles = array('normal' => 'Normal', 'italic' => 'Italic');
        foreach ($font_styles as $style => $label) {
            echo '<option value="' . esc_attr($style) . '" ' . selected($field_labels_font_style, $style, false) . '>' . esc_html($label) . '</option>';
        }
        echo '</select>';
        echo '</div>';
        
        echo '</div>';
        echo '<p class="description">' . esc_html__('Typography settings for all field labels (e.g., "Name:", "Email:").', 'entries-display-for-wpforms') . '</p>';
    }

    /**
     * Callback for comment typography settings
     */
    public function comment_typography_callback() {
        $options = get_option('wpfed_options');
        $comment_font_size = isset($options['styles']['comment_font_size']) ? $options['styles']['comment_font_size'] : '16px';
        $comment_font_weight = isset($options['styles']['comment_font_weight']) ? $options['styles']['comment_font_weight'] : 'normal';
        $comment_font_style = isset($options['styles']['comment_font_style']) ? $options['styles']['comment_font_style'] : 'normal';
        
        echo '<div class="wpfed-typography-controls">';
        
        // Font Size
        echo '<div class="wpfed-typography-control">';
        echo '<label for="wpfed_comment_font_size">' . esc_html__('Size', 'entries-display-for-wpforms') . '</label>';
        echo '<select name="wpfed_options[styles][comment_font_size]" id="wpfed_comment_font_size">';
        $font_sizes = array('10px', '12px', '14px', '16px', '18px', '20px', '22px', '24px', '26px', '28px', '30px');
        foreach ($font_sizes as $size) {
            echo '<option value="' . esc_attr($size) . '" ' . selected($comment_font_size, $size, false) . '>' . esc_html($size) . '</option>';
        }
        echo '</select>';
        echo '</div>';
        
        // Font Weight
        echo '<div class="wpfed-typography-control">';
        echo '<label for="wpfed_comment_font_weight">' . esc_html__('Weight', 'entries-display-for-wpforms') . '</label>';
        echo '<select name="wpfed_options[styles][comment_font_weight]" id="wpfed_comment_font_weight">';
        $font_weights = array('normal' => 'Normal', 'bold' => 'Bold', '100' => '100', '200' => '200', '300' => '300', '400' => '400', '500' => '500', '600' => '600', '700' => '700', '800' => '800', '900' => '900');
        foreach ($font_weights as $weight => $label) {
            echo '<option value="' . esc_attr($weight) . '" ' . selected($comment_font_weight, $weight, false) . '>' . esc_html($label) . '</option>';
        }
        echo '</select>';
        echo '</div>';
        
        // Font Style
        echo '<div class="wpfed-typography-control">';
        echo '<label for="wpfed_comment_font_style">' . esc_html__('Style', 'entries-display-for-wpforms') . '</label>';
        echo '<select name="wpfed_options[styles][comment_font_style]" id="wpfed_comment_font_style">';
        $font_styles = array('normal' => 'Normal', 'italic' => 'Italic');
        foreach ($font_styles as $style => $label) {
            echo '<option value="' . esc_attr($style) . '" ' . selected($comment_font_style, $style, false) . '>' . esc_html($label) . '</option>';
        }
        echo '</select>';
        echo '</div>';
        
        echo '</div>';
        echo '<p class="description">' . esc_html__('Typography settings for comment/message field values.', 'entries-display-for-wpforms') . '</p>';
    }

    /**
     * Display the main settings page in the admin area
     */
    public function display_settings_page() {
        ?>
        <div class="wrap wpfed-admin-wrap">
            <div class="wpfed-admin-header">
                <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            </div>
            
            <div class="wpfed-admin-container">
                <div class="wpfed-admin-main">
                    <form method="post" action="options.php">
                        <?php settings_fields('wpfed_options_group'); ?>
                        
                        <!-- General Settings Card -->
                        <div class="wpfed-settings-card">
                            <div class="wpfed-settings-card-header">
                                <h2><?php esc_html_e('General Settings', 'entries-display-for-wpforms'); ?></h2>
                                <p><?php esc_html_e('Configure default settings for displaying WPForms entries.', 'entries-display-for-wpforms'); ?></p>
                            </div>
                            <div class="wpfed-settings-card-content">
                                <table class="form-table">
                                    <tr>
                                        <th scope="row"><?php esc_html_e('Default Form', 'entries-display-for-wpforms'); ?></th>
                                        <td><?php $this->form_id_callback(); ?></td>
                                    </tr>
                                    <tr>
                                        <th scope="row"><?php esc_html_e('Default Fields to Display', 'entries-display-for-wpforms'); ?></th>
                                        <td><?php $this->display_fields_callback(); ?></td>
                                    </tr>
                                    <tr>
                                        <th scope="row"><?php esc_html_e('Default Entries Per Page', 'entries-display-for-wpforms'); ?></th>
                                        <td><?php $this->entries_per_page_callback(); ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        
                        <!-- Display Settings Card -->
                        <div class="wpfed-settings-card">
                            <div class="wpfed-settings-card-header">
                                <h2><?php esc_html_e('Display Settings', 'entries-display-for-wpforms'); ?></h2>
                                <p><?php esc_html_e('Configure how entries are displayed on your site.', 'entries-display-for-wpforms'); ?></p>
                            </div>
                            <div class="wpfed-settings-card-content">
                                <table class="form-table">
                                    <tr>
                                        <th scope="row"><?php esc_html_e('Show Entry Date', 'entries-display-for-wpforms'); ?></th>
                                        <td><?php $this->show_date_callback(); ?></td>
                                    </tr>
                                    <tr>
                                        <th scope="row"><?php esc_html_e('Date Format', 'entries-display-for-wpforms'); ?></th>
                                        <td><?php $this->date_format_callback(); ?></td>
                                    </tr>
                                    <tr>
                                        <th scope="row"><?php esc_html_e('Show Username', 'entries-display-for-wpforms'); ?></th>
                                        <td><?php $this->show_username_callback(); ?></td>
                                    </tr>
                                    <tr>
                                        <th scope="row"><?php esc_html_e('Hide Field Labels', 'entries-display-for-wpforms'); ?></th>
                                        <td><?php $this->hide_field_labels_callback(); ?></td>
                                    </tr>
                                    <tr>
                                        <th scope="row"><?php esc_html_e('Hide Empty Fields', 'entries-display-for-wpforms'); ?></th>
                                        <td><?php $this->hide_empty_fields_callback(); ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        
                        <!-- Style Settings Card -->
                        <div class="wpfed-settings-card">
                             <div class="wpfed-settings-card-header">
                                <h2><?php esc_html_e('Style Settings', 'entries-display-for-wpforms'); ?></h2>
                                 <p><?php esc_html_e('Customize the appearance of the entries display.', 'entries-display-for-wpforms'); ?></p>
                            </div>
                            <div class="wpfed-settings-card-content">
                                <table class="form-table">
                                    <tr>
                                        <th scope="row"><?php esc_html_e('Colors', 'entries-display-for-wpforms'); ?></th>
                                        <td>
                                            <div class="wpfed-colors-grid">
                                                <div class="wpfed-color-option">
                                                    <label for="wpfed_background_color"><?php esc_html_e('Background', 'entries-display-for-wpforms'); ?></label>
                                                    <?php $this->background_color_callback(); ?>
                                                </div>
                                                <div class="wpfed-color-option">
                                                    <label for="wpfed_border_color"><?php esc_html_e('Border', 'entries-display-for-wpforms'); ?></label>
                                                    <?php $this->border_color_callback(); ?>
                                                </div>
                                                <div class="wpfed-color-option">
                                                    <label for="wpfed_text_color"><?php esc_html_e('Text', 'entries-display-for-wpforms'); ?></label>
                                                    <?php $this->text_color_callback(); ?>
                                                </div>
                                                <div class="wpfed-color-option">
                                                    <label for="wpfed_header_color"><?php esc_html_e('Header', 'entries-display-for-wpforms'); ?></label>
                                                    <?php $this->header_color_callback(); ?>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row"><?php esc_html_e('Dimensions & Effects', 'entries-display-for-wpforms'); ?></th>
                                        <td>
                                            <div class="wpfed-dimensions-grid">
                                                <div class="wpfed-dimension-option">
                                                    <label for="wpfed_border_radius"><?php esc_html_e('Border Radius', 'entries-display-for-wpforms'); ?></label>
                                                    <?php $this->border_radius_callback(); ?>
                                                </div>
                                                <div class="wpfed-dimension-option">
                                                    <label for="wpfed_padding"><?php esc_html_e('Padding', 'entries-display-for-wpforms'); ?></label>
                                                    <?php $this->padding_callback(); ?>
                                                </div>
                                                 <div class="wpfed-effect-option">
                                                    <label for="wpfed_box_shadow"><?php esc_html_e('Box Shadow', 'entries-display-for-wpforms'); ?></label>
                                                    <?php $this->box_shadow_callback(); ?>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <!--
                                    <tr>
                                        <th scope="row"><?php esc_html_e('Vertical Alignment', 'entries-display-for-wpforms'); ?></th>
                                        <td><?php $this->vertical_alignment_callback(); ?></td>
                                    </tr>
                                    -->
                                </table>
                            </div>
                        </div>
                        
                        <!-- Typography Settings Card -->
                        <div class="wpfed-settings-card">
                            <div class="wpfed-settings-card-header">
                                <h2><?php esc_html_e('Typography Settings', 'entries-display-for-wpforms'); ?></h2>
                                <p><?php esc_html_e('Customize typography settings for different elements. These settings use !important CSS to override theme and page builder styles.', 'entries-display-for-wpforms'); ?></p>
                            </div>
                            <div class="wpfed-settings-card-content">
                                <table class="form-table">
                                    <tr>
                                        <th scope="row"><?php esc_html_e('Date Typography', 'entries-display-for-wpforms'); ?></th>
                                        <td><?php $this->date_typography_callback(); ?></td>
                                    </tr>
                                    <tr>
                                        <th scope="row"><?php esc_html_e('Username Typography', 'entries-display-for-wpforms'); ?></th>
                                        <td><?php $this->username_typography_callback(); ?></td>
                                    </tr>
                                    <tr>
                                        <th scope="row"><?php esc_html_e('Email Typography', 'entries-display-for-wpforms'); ?></th>
                                        <td><?php $this->email_typography_callback(); ?></td>
                                    </tr>
                                    <tr>
                                        <th scope="row"><?php esc_html_e('Field Labels Typography', 'entries-display-for-wpforms'); ?></th>
                                        <td><?php $this->field_labels_typography_callback(); ?></td>
                                    </tr>
                                    <tr>
                                        <th scope="row"><?php esc_html_e('Comment Typography', 'entries-display-for-wpforms'); ?></th>
                                        <td><?php $this->comment_typography_callback(); ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        
                        <?php submit_button(); ?>
                    </form>
                </div>
                
                <div class="wpfed-admin-sidebar">
                    <!-- Shortcode Generator -->
                    <div class="wpfed-sidebar-widget wpfed-shortcode-generator">
                        <h2><?php esc_html_e('Shortcode Generator', 'entries-display-for-wpforms'); ?></h2>
                        
                        <div class="wpfed-shortcode-builder">
                            <div class="wpfed-shortcode-option">
                                <label for="wpfed_sc_form_id"><?php esc_html_e('Form', 'entries-display-for-wpforms'); ?></label>
                                <select id="wpfed_sc_form_id" class="wpfed-sc-param">
                                    <option value=""><?php esc_html_e('Use default', 'entries-display-for-wpforms'); ?></option>
                                    <?php
                                    // Populate form dropdown
                                    $forms = $this->get_wpforms();
                                    if (!empty($forms)) {
                                        foreach ($forms as $id => $title) {
                                            echo '<option value="' . esc_attr($id) . '">' . esc_html($title) . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                            
                            <div class="wpfed-shortcode-option">
                                <label for="wpfed_sc_fields"><?php esc_html_e('Fields (comma-separated IDs)', 'entries-display-for-wpforms'); ?></label>
                                <input type="text" id="wpfed_sc_fields" class="wpfed-sc-param" placeholder="e.g., 1,3,5">
                            </div>
                            
                            <div class="wpfed-shortcode-option">
                                <label for="wpfed_sc_number"><?php esc_html_e('Number of entries', 'entries-display-for-wpforms'); ?></label>
                                <input type="number" id="wpfed_sc_number" class="wpfed-sc-param" min="1" placeholder="e.g., 10">
                            </div>

                            <div class="wpfed-shortcode-option">
                                <label for="wpfed_sc_user"><?php esc_html_e('User', 'entries-display-for-wpforms'); ?></label>
                                <select id="wpfed_sc_user" class="wpfed-sc-param">
                                    <option value=""><?php esc_html_e('All users', 'entries-display-for-wpforms'); ?></option>
                                    <option value="current"><?php esc_html_e('Current logged-in user', 'entries-display-for-wpforms'); ?></option>
                                    <?php
                                    // Populate user dropdown with all users
                                    $users = get_users(array('fields' => array('ID', 'display_name')));
                                    foreach ($users as $user) {
                                        echo '<option value="' . esc_attr($user->ID) . '">' . esc_html($user->display_name) . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            
                            <div class="wpfed-shortcode-option">
                                <label for="wpfed_sc_type"><?php esc_html_e('Entry type', 'entries-display-for-wpforms'); ?></label>
                                <select id="wpfed_sc_type" class="wpfed-sc-param">
                                    <option value="all"><?php esc_html_e('All', 'entries-display-for-wpforms'); ?></option>
                                    <option value="unread"><?php esc_html_e('Unread', 'entries-display-for-wpforms'); ?></option>
                                    <option value="read"><?php esc_html_e('Read', 'entries-display-for-wpforms'); ?></option>
                                    <option value="starred"><?php esc_html_e('Starred', 'entries-display-for-wpforms'); ?></option>
                                </select>
                            </div>
                            
                            <div class="wpfed-shortcode-option">
                                <label for="wpfed_sc_sort"><?php esc_html_e('Sort by field ID', 'entries-display-for-wpforms'); ?></label>
                                <input type="text" id="wpfed_sc_sort" class="wpfed-sc-param" placeholder="e.g., 2">
                            </div>
                            
                            <div class="wpfed-shortcode-option">
                                <label for="wpfed_sc_order"><?php esc_html_e('Sort order', 'entries-display-for-wpforms'); ?></label>
                                <select id="wpfed_sc_order" class="wpfed-sc-param">
                                    <option value="desc"><?php esc_html_e('Descending', 'entries-display-for-wpforms'); ?></option>
                                    <option value="asc"><?php esc_html_e('Ascending', 'entries-display-for-wpforms'); ?></option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="wpfed-shortcode-preview">
                            <h3><?php esc_html_e('Your Shortcode', 'entries-display-for-wpforms'); ?></h3>
                            <div class="wpfed-shortcode-result">
                                <code id="wpfed_shortcode_result">[wpforms_entries_display]</code>
                                <button type="button" id="wpfed_copy_shortcode" class="button">
                                    <span class="dashicons dashicons-clipboard"></span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Live Preview Widget -->
                    <div class="wpfed-sidebar-widget wpfed-preview-widget">
                        <h2><?php esc_html_e('Live Preview', 'entries-display-for-wpforms'); ?></h2>
                        <div class="wpfed-live-preview">
                            <div class="wpfed-preview-entry">
                                <div class="wpfed-preview-entry-header">
                                    <span class="wpfed-preview-date"><?php echo date_i18n('F j, Y'); ?></span>
                                </div>
                                <div class="wpfed-preview-field">
                                    <span class="wpfed-preview-label"><?php esc_html_e('Name:', 'entries-display-for-wpforms'); ?></span>
                                    <span class="wpfed-preview-value"><?php esc_html_e('John Appleseed', 'entries-display-for-wpforms'); ?></span>
                                </div>
                                <div class="wpfed-preview-field">
                                    <span class="wpfed-preview-label"><?php esc_html_e('Message:', 'entries-display-for-wpforms'); ?></span>
                                    <span class="wpfed-preview-value"><?php esc_html_e('This is a sample entry.', 'entries-display-for-wpforms'); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Enqueue admin scripts and styles for settings page
     *
     * @param string $hook The current admin page hook
     */
    public function enqueue_admin_scripts($hook) {
        if ('settings_page_entries-display-for-wpforms' !== $hook) {
            return;
        }
        
        // Enqueue color picker style and script
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');
        
        // Enqueue custom admin script
        wp_enqueue_script(
            'wpfed-admin-script',
            WPFED_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery', 'wp-color-picker'),
            WPFED_VERSION,
            true
        );

        // Properly enqueue script using WordPress API
        wp_enqueue_script(
            'wpfed-admin-settings',
            WPFED_PLUGIN_URL . 'assets/js/admin-settings.js',
            array('jquery'),
            WPFED_VERSION,
            true
        );
        
        // Pass variables to JavaScript
        wp_localize_script(
            'wpfed-admin-settings',
            'wpfed_admin_vars',
            array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('wpfed_get_fields_nonce'),
                'select_form_text' => esc_js(__('Please select a form first.', 'entries_display_for_wpforms'))
            )
        );
        
        // Enqueue custom admin stylesheet
        wp_enqueue_style(
            'wpfed-admin-style',
            WPFED_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            WPFED_VERSION
        );
    }

    /**
     * Helper function to get all WPForms forms
     *
     * @return array Array of forms with ID and title
     */
    private function get_wpforms() {
        $forms = array();
        if (function_exists('wpforms')) {
            $wpforms = wpforms();
            if (is_object($wpforms) && method_exists($wpforms->form, 'get')) {
                $all_forms = $wpforms->form->get();
                if (!empty($all_forms)) {
                    foreach ($all_forms as $form) {
                        $forms[$form->ID] = $form->post_title;
                    }
                }
            }
        }
        return $forms;
    }

    /**
     * Helper function to get fields for a specific form
     *
     * @param int $form_id The ID of the form
     * @return array Array of form fields
     */
    private function get_form_fields($form_id) {
        $fields = array();
        if (function_exists('wpforms')) {
            $wpforms = wpforms();
            if (is_object($wpforms) && method_exists($wpforms->form, 'get')) {
                $form = $wpforms->form->get($form_id);
                if (!empty($form)) {
                    $form_data = wpforms_decode($form->post_content);
                    if (!empty($form_data['fields'])) {
                        foreach ($form_data['fields'] as $field) {
                            $disallowed_types = array('divider', 'html', 'pagebreak', 'captcha');
                            if (!in_array($field['type'], $disallowed_types)) {
                                $fields[] = array(
                                    'id' => $field['id'],
                                    'label' => $field['label']
                                );
                            }
                        }
                    }
                }
            }
        }
        return $fields;
    }

    /**
     * AJAX handler for retrieving form fields for a selected form
     */
    public function get_form_fields_ajax() {
        // Verify nonce for security
        if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'wpfed_get_fields_nonce')) {
            wp_send_json_error('Invalid nonce', 403);
        }
        
        // Validate form ID
        if (!isset($_POST['form_id']) || empty($_POST['form_id'])) {
            wp_send_json_error('Invalid form ID', 400);
        }
        
        $form_id = absint($_POST['form_id']);
        $fields = $this->get_form_fields($form_id);
        
        if (!empty($fields)) {
            wp_send_json_success($fields);
        } else {
            wp_send_json_error('No fields found for this form.', 404);
        }
    }
}

// Initialize the admin settings class
$wpfed_admin_settings = new WPFED_Admin_Settings();