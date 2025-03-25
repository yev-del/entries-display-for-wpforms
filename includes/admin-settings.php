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
        $sanitized_input['form_id'] = absint($input['form_id']);
        
        // Sanitize display fields
        if (isset($input['display_fields']) && is_array($input['display_fields'])) {
            $sanitized_input['display_fields'] = array_map('absint', $input['display_fields']);
        } else {
            $sanitized_input['display_fields'] = array();
        }
        
        // Sanitize entries per page
        $sanitized_input['entries_per_page'] = absint($input['entries_per_page']);
        if ($sanitized_input['entries_per_page'] < 1) {
            $sanitized_input['entries_per_page'] = 30;
        }
        
        // Sanitize style settings
        $sanitized_input['styles'] = array(
            'background_color' => sanitize_hex_color($input['styles']['background_color']),
            'border_color' => sanitize_hex_color($input['styles']['border_color']),
            'text_color' => sanitize_hex_color($input['styles']['text_color']),
            'header_color' => sanitize_hex_color($input['styles']['header_color']),
            'border_radius' => sanitize_text_field($input['styles']['border_radius']),
            'padding' => sanitize_text_field($input['styles']['padding']),
            'box_shadow' => sanitize_text_field($input['styles']['box_shadow']),
        );
        
        // Sanitize other settings
        $sanitized_input['show_date'] = isset($input['show_date']) ? sanitize_text_field($input['show_date']) : 'yes';
        $sanitized_input['date_format'] = sanitize_text_field($input['date_format']);
        $sanitized_input['show_username'] = isset($input['show_username']) ? sanitize_text_field($input['show_username']) : 'no';
        $sanitized_input['hide_field_labels'] = isset($input['hide_field_labels']) ? sanitize_text_field($input['hide_field_labels']) : 'no';
        $sanitized_input['hide_empty_fields'] = isset($input['hide_empty_fields']) ? sanitize_text_field($input['hide_empty_fields']) : 'no';
        
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
        $show_date = isset($options['show_date']) ? $options['show_date'] : 'yes';
        
        echo '<select name="wpfed_options[show_date]" id="wpfed_show_date" class="regular-text">';
        echo '<option value="yes" ' . selected($show_date, 'yes', false) . '>' . esc_html__('Yes', 'entries-display-for-wpforms') . '</option>';
        echo '<option value="no" ' . selected($show_date, 'no', false) . '>' . esc_html__('No', 'entries-display-for-wpforms') . '</option>';
        echo '</select>';
        echo '<p class="description">' . esc_html__('Show the date when the entry was submitted.', 'entries-display-for-wpforms') . '</p>';
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
        
        echo '<div class="wpfed-custom-date-format">';
        echo '<label for="wpfed_custom_date_format">' . esc_html__('Custom Format:', 'entries-display-for-wpforms') . '</label>';
        echo '<input type="text" id="wpfed_custom_date_format" class="regular-text" placeholder="Y-m-d H:i:s">';
        echo '<button type="button" id="wpfed_apply_custom_date" class="button button-secondary">' . esc_html__('Apply Custom Format', 'entries-display-for-wpforms') . '</button>';
        echo '</div>';
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
        
        // Retrieve and display available WPForms forms
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
        
        echo '<select name="wpfed_options[form_id]" id="wpfed_form_id" class="regular-text">';
        echo '<option value="">' . esc_html__('Select a form', 'entries-display-for-wpforms') . '</option>';
        
        foreach ($forms as $id => $title) {
            echo '<option value="' . esc_attr($id) . '" ' . selected($form_id, $id, false) . '>' . esc_html($title) . '</option>';
        }
        
        echo '</select>';
        echo '<p class="description">' . esc_html__('Select the default WPForms form to display entries from.', 'entries-display-for-wpforms') . '</p>';
        
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
                'select_form_text' => esc_js(__('Please select a form first.', 'entries-display-for-wpforms'))
            )
        );
        } // <-- Эта закрывающая скобка отсутствовала!
        
        /**
         * Callback for default fields selection
         */
        public function display_fields_callback() {
            $options = get_option('wpfed_options');
            $form_id = isset($options['form_id']) ? $options['form_id'] : '';
            $display_fields = isset($options['display_fields']) ? $options['display_fields'] : array();
        
        echo '<div id="wpfed_display_fields_container">';
        
        if (!empty($form_id) && function_exists('wpforms')) {
            $wpforms = wpforms();
            if (is_object($wpforms) && method_exists($wpforms->form, 'get')) {
                $form = $wpforms->form->get($form_id);
                if (!empty($form)) {
                    $form_data = wpforms_decode($form->post_content);
                    if (!empty($form_data['fields'])) {
                        foreach ($form_data['fields'] as $field) {
                            // Skip undesirable field types
                            $disallowed_types = array('divider', 'html', 'pagebreak', 'captcha');
                            if (in_array($field['type'], $disallowed_types)) {
                                continue;
                            }
                            
                            $checked = in_array($field['id'], $display_fields) ? 'checked' : '';
                            echo '<label><input type="checkbox" name="wpfed_options[display_fields][]" value="' . esc_attr($field['id']) . '" ' . esc_attr($checked) . ' class="wpfed-field-checkbox"> ' . esc_html($field['label']) . ' (ID: ' . esc_html($field['id']) . ')</label><br>';
                        }
                    } else {
                        echo '<p>' . esc_html__('No fields found in this form.', 'entries-display-for-wpforms') . '</p>';
                    }
                } else {
                    echo '<p>' . esc_html__('Form not found.', 'entries-display-for-wpforms') . '</p>';
                }
            } else {
                echo '<p>' . esc_html__('WPForms API is not available.', 'entries-display-for-wpforms') . '</p>';
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
        $show_username = isset($options['show_username']) ? $options['show_username'] : 'no';
        
        echo '<select name="wpfed_options[show_username]" id="wpfed_show_username" class="regular-text">';
        echo '<option value="yes" ' . selected($show_username, 'yes', false) . '>' . esc_html__('Yes', 'entries-display-for-wpforms') . '</option>';
        echo '<option value="no" ' . selected($show_username, 'no', false) . '>' . esc_html__('No', 'entries-display-for-wpforms') . '</option>';
        echo '</select>';
        echo '<p class="description">' . esc_html__('Show the username of the user who submitted the entry (if available).', 'entries-display-for-wpforms') . '</p>';
    }
    
    /**
     * Callback for the setting to hide field labels
     */
    public function hide_field_labels_callback() {
        $options = get_option('wpfed_options');
        $hide_field_labels = isset($options['hide_field_labels']) ? $options['hide_field_labels'] : 'no';
        
        echo '<select name="wpfed_options[hide_field_labels]" id="wpfed_hide_field_labels" class="regular-text">';
        echo '<option value="yes" ' . selected($hide_field_labels, 'yes', false) . '>' . esc_html__('Yes', 'entries-display-for-wpforms') . '</option>';
        echo '<option value="no" ' . selected($hide_field_labels, 'no', false) . '>' . esc_html__('No', 'entries-display-for-wpforms') . '</option>';
        echo '</select>';
        echo '<p class="description">' . esc_html__('Hide field labels in the comment display (e.g., "Single Line Text:").', 'entries-display-for-wpforms') . '</p>';
    }
    
    /**
     * Callback for the setting to hide empty fields
     */
    public function hide_empty_fields_callback() {
        $options = get_option('wpfed_options');
        $hide_empty_fields = isset($options['hide_empty_fields']) ? $options['hide_empty_fields'] : 'no';
        
        echo '<select name="wpfed_options[hide_empty_fields]" id="wpfed_hide_empty_fields" class="regular-text">';
        echo '<option value="yes" ' . selected($hide_empty_fields, 'yes', false) . '>' . esc_html__('Yes', 'entries-display-for-wpforms') . '</option>';
        echo '<option value="no" ' . selected($hide_empty_fields, 'no', false) . '>' . esc_html__('No', 'entries-display-for-wpforms') . '</option>';
        echo '</select>';
        echo '<p class="description">' . esc_html__('Hide fields that have no value.', 'entries-display-for-wpforms') . '</p>';
    }
    
    /**
 * Display the main settings page in the admin area
 */
public function display_settings_page() {
    ?>
    <div class="wrap wpfed-admin-wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        
        <div class="wpfed-admin-container">
            <div class="wpfed-admin-main">
                <form method="post" action="options.php">
                    <?php settings_fields('wpfed_options_group'); ?>
                    
                    <!-- General Settings Section -->
                    <div class="wpfed-settings-section">
                        <h2><?php esc_html_e('General Settings', 'entries-display-for-wpforms'); ?></h2>
                        <div class="wpfed-settings-section-content">
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
                    
                    <!-- Display Settings Section -->
                    <div class="wpfed-settings-section">
                        <h2><?php esc_html_e('Display Settings', 'entries-display-for-wpforms'); ?></h2>
                        <div class="wpfed-settings-section-content">
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
                    
                    <!-- Style Settings Section -->
                    <div class="wpfed-settings-section">
                        <h2><?php esc_html_e('Style Settings', 'entries-display-for-wpforms'); ?></h2>
                        <div class="wpfed-settings-section-content">
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
                                    <th scope="row"><?php esc_html_e('Dimensions', 'entries-display-for-wpforms'); ?></th>
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
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php esc_html_e('Effects', 'entries-display-for-wpforms'); ?></th>
                                    <td>
                                        <div class="wpfed-effect-option">
                                            <label for="wpfed_box_shadow"><?php esc_html_e('Box Shadow', 'entries-display-for-wpforms'); ?></label>
                                            <?php $this->box_shadow_callback(); ?>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Live Preview Section -->
                    <div class="wpfed-settings-section wpfed-preview-section">
                        <h2><?php esc_html_e('Live Preview', 'entries-display-for-wpforms'); ?></h2>
                        <div class="wpfed-settings-section-content">
                            <div class="wpfed-live-preview">
                                <div class="wpfed-preview-entry">
                                    <div class="wpfed-preview-entry-header">
                                        <?php esc_html_e('Entry from', 'entries-display-for-wpforms'); ?> 
                                        <span class="wpfed-preview-date"><?php echo date_i18n('F j, Y g:i a'); ?></span>
                                        <?php esc_html_e('by', 'entries-display-for-wpforms'); ?> 
                                        <span class="wpfed-preview-user"><?php esc_html_e('John Doe', 'entries-display-for-wpforms'); ?></span>
                                    </div>
                                    
                                    <div class="wpfed-preview-field">
                                        <span class="wpfed-preview-label"><?php esc_html_e('Name:', 'entries-display-for-wpforms'); ?></span>
                                        <span class="wpfed-preview-value"><?php esc_html_e('John Doe', 'entries-display-for-wpforms'); ?></span>
                                    </div>
                                    
                                    <div class="wpfed-preview-field">
                                        <span class="wpfed-preview-label"><?php esc_html_e('Email:', 'entries-display-for-wpforms'); ?></span>
                                        <span class="wpfed-preview-value"><?php esc_html_e('john.doe@example.com', 'entries-display-for-wpforms'); ?></span>
                                    </div>
                                    
                                    <div class="wpfed-preview-field">
                                        <span class="wpfed-preview-label"><?php esc_html_e('Message:', 'entries-display-for-wpforms'); ?></span>
                                        <span class="wpfed-preview-value"><?php esc_html_e('This is a sample message that would appear in your entries display.', 'entries-display-for-wpforms'); ?></span>
                                    </div>
                                </div>
                            </div>
                            <p class="description"><?php esc_html_e('This is a preview of how your entries will appear on your site based on the current style settings.', 'entries-display-for-wpforms'); ?></p>
                        </div>
                    </div>
                    
                    <?php submit_button(); ?>
                </form>
            </div>
            
            <div class="wpfed-admin-sidebar">
                <!-- Shortcode Generator -->
                <div class="wpfed-shortcode-generator">
                    <h2><?php esc_html_e('Shortcode Generator', 'entries-display-for-wpforms'); ?></h2>
                    
                    <div class="wpfed-shortcode-builder">
                        <div class="wpfed-shortcode-option">
                            <label for="wpfed_sc_form_id"><?php esc_html_e('Form:', 'entries-display-for-wpforms'); ?></label>
                            <select id="wpfed_sc_form_id" class="wpfed-sc-param">
                                <option value=""><?php esc_html_e('Use default', 'entries-display-for-wpforms'); ?></option>
                                <?php
                                // Populate form dropdown
                                if (function_exists('wpforms')) {
                                    $wpforms = wpforms();
                                    if (is_object($wpforms) && method_exists($wpforms->form, 'get')) {
                                        $all_forms = $wpforms->form->get();
                                        if (!empty($all_forms)) {
                                            foreach ($all_forms as $form) {
                                                echo '<option value="' . esc_attr($form->ID) . '">' . esc_html($form->post_title) . '</option>';
                                            }
                                        }
                                    }
                                }
                                ?>
                            </select>
                        </div>
                        
                        <div class="wpfed-shortcode-option">
                            <label for="wpfed_sc_fields"><?php esc_html_e('Fields (comma-separated IDs):', 'entries-display-for-wpforms'); ?></label>
                            <input type="text" id="wpfed_sc_fields" class="wpfed-sc-param" placeholder="e.g., 1,3,5">
                            <p class="description"><?php esc_html_e('Leave empty to use default fields', 'entries-display-for-wpforms'); ?></p>
                        </div>
                        
                        <div class="wpfed-shortcode-option">
                            <label for="wpfed_sc_number"><?php esc_html_e('Number of entries:', 'entries-display-for-wpforms'); ?></label>
                            <input type="number" id="wpfed_sc_number" class="wpfed-sc-param" min="1" placeholder="e.g., 10">
                        </div>
                        
                        <div class="wpfed-shortcode-option">
                            <label for="wpfed_sc_show_date"><?php esc_html_e('Show Entry Date:', 'entries-display-for-wpforms'); ?></label>
                            <select id="wpfed_sc_show_date" class="wpfed-sc-param">
                                <option value=""><?php esc_html_e('Use default', 'entries-display-for-wpforms'); ?></option>
                                <option value="yes"><?php esc_html_e('Yes', 'entries-display-for-wpforms'); ?></option>
                                <option value="no"><?php esc_html_e('No', 'entries-display-for-wpforms'); ?></option>
                            </select>
                        </div>

                        <div class="wpfed-shortcode-option">
                            <label for="wpfed_sc_date_format"><?php esc_html_e('Date Format:', 'entries-display-for-wpforms'); ?></label>
                            <select id="wpfed_sc_date_format" class="wpfed-sc-param">
                                <option value=""><?php esc_html_e('Use default', 'entries-display-for-wpforms'); ?></option>
                                <option value="F j, Y g:i a"><?php echo esc_html(date_i18n('F j, Y g:i a')); ?></option>
                                <option value="Y-m-d H:i:s"><?php echo esc_html(date_i18n('Y-m-d H:i:s')); ?></option>
                                <option value="m/d/Y g:i a"><?php echo esc_html(date_i18n('m/d/Y g:i a')); ?></option>
                                <option value="d/m/Y g:i a"><?php echo esc_html(date_i18n('d/m/Y g:i a')); ?></option>
                                <option value="F j, Y"><?php echo esc_html(date_i18n('F j, Y')); ?></option>
                            </select>
                        </div>
                        
                        <div class="wpfed-shortcode-option">
                            <label for="wpfed_sc_show_username"><?php esc_html_e('Show Username:', 'entries-display-for-wpforms'); ?></label>
                            <select id="wpfed_sc_show_username" class="wpfed-sc-param">
                            <option value=""><?php esc_html_e('Use default', 'entries-display-for-wpforms'); ?></option>
                                <option value="yes"><?php esc_html_e('Yes', 'entries-display-for-wpforms'); ?></option>
                                <option value="no"><?php esc_html_e('No', 'entries-display-for-wpforms'); ?></option>
                            </select>
                        </div>

                        <div class="wpfed-shortcode-option">
                            <label for="wpfed_sc_hide_field_labels"><?php esc_html_e('Hide Field Labels:', 'entries-display-for-wpforms'); ?></label>
                            <select id="wpfed_sc_hide_field_labels" class="wpfed-sc-param">
                                <option value=""><?php esc_html_e('Use default', 'entries-display-for-wpforms'); ?></option>
                                <option value="yes"><?php esc_html_e('Yes', 'entries-display-for-wpforms'); ?></option>
                                <option value="no"><?php esc_html_e('No', 'entries-display-for-wpforms'); ?></option>
                            </select>
                        </div>

                        <div class="wpfed-shortcode-option">
                            <label for="wpfed_sc_hide_empty_fields"><?php esc_html_e('Hide Empty Fields:', 'entries-display-for-wpforms'); ?></label>
                            <select id="wpfed_sc_hide_empty_fields" class="wpfed-sc-param">
                                <option value=""><?php esc_html_e('Use default', 'entries-display-for-wpforms'); ?></option>
                                <option value="yes"><?php esc_html_e('Yes', 'entries-display-for-wpforms'); ?></option>
                                <option value="no"><?php esc_html_e('No', 'entries-display-for-wpforms'); ?></option>
                            </select>
                        </div>
                        
                        <div class="wpfed-shortcode-option">
                            <label for="wpfed_sc_user"><?php esc_html_e('User:', 'entries-display-for-wpforms'); ?></label>
                            <select id="wpfed_sc_user" class="wpfed-sc-param">
                                <option value=""><?php esc_html_e('All users', 'entries-display-for-wpforms'); ?></option>
                                <option value="current"><?php esc_html_e('Current logged-in user', 'entries-display-for-wpforms'); ?></option>
                                <?php
                                // Populate user dropdown with all users
                                $users = get_users(array('fields' => array('ID', 'display_name')));
                                foreach ($users as $user) {
                                    echo '<option value="' . esc_attr($user->ID) . '">' . esc_html($user->display_name) . ' (ID: ' . esc_html($user->ID) . ')</option>';
                                }
                                ?>
                            </select>
                        </div>
                        
                        <div class="wpfed-shortcode-option">
                            <label for="wpfed_sc_type"><?php esc_html_e('Entry type:', 'entries-display-for-wpforms'); ?></label>
                            <select id="wpfed_sc_type" class="wpfed-sc-param">
                                <option value="all"><?php esc_html_e('All entries', 'entries-display-for-wpforms'); ?></option>
                                <option value="unread"><?php esc_html_e('Unread entries', 'entries-display-for-wpforms'); ?></option>
                                <option value="read"><?php esc_html_e('Read entries', 'entries-display-for-wpforms'); ?></option>
                                <option value="starred"><?php esc_html_e('Starred entries', 'entries-display-for-wpforms'); ?></option>
                            </select>
                        </div>
                        
                        <div class="wpfed-shortcode-option">
                            <label for="wpfed_sc_sort"><?php esc_html_e('Sort by field ID:', 'entries-display-for-wpforms'); ?></label>
                            <input type="text" id="wpfed_sc_sort" class="wpfed-sc-param" placeholder="e.g., 2">
                        </div>
                        
                        <div class="wpfed-shortcode-option">
                            <label for="wpfed_sc_order"><?php esc_html_e('Sort order:', 'entries-display-for-wpforms'); ?></label>
                            <select id="wpfed_sc_order" class="wpfed-sc-param">
                                <option value="asc"><?php esc_html_e('Ascending (A-Z, 0-9)', 'entries-display-for-wpforms'); ?></option>
                                <option value="desc"><?php esc_html_e('Descending (Z-A, 9-0)', 'entries-display-for-wpforms'); ?></option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="wpfed-shortcode-preview">
                        <h3><?php esc_html_e('Your Shortcode', 'entries-display-for-wpforms'); ?></h3>
                        <div class="wpfed-shortcode-result">
                            <code id="wpfed_shortcode_result">[wpforms_entries_display]</code>
                            <button type="button" id="wpfed_copy_shortcode" class="button">
                                <span class="dashicons dashicons-clipboard"></span> <?php esc_html_e('Copy', 'entries-display-for-wpforms'); ?>
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Help Box -->
                <div class="wpfed-help-box">
                    <h3><?php esc_html_e('Need Help?', 'entries-display-for-wpforms'); ?></h3>
                    <p><?php esc_html_e('This plugin allows you to display WPForms entries as styled comments on your website.', 'entries-display-for-wpforms'); ?></p>
                    <p><?php esc_html_e('To use it, simply configure the settings on this page and use the generated shortcode on any page or post.', 'entries-display-for-wpforms'); ?></p>
                    <p><strong><?php esc_html_e('Note:', 'entries-display-for-wpforms'); ?></strong> <?php esc_html_e('This feature requires WPForms Pro with the Entries functionality.', 'entries-display-for-wpforms'); ?></p>
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
        
        // Enqueue custom admin stylesheet
        wp_enqueue_style(
            'wpfed-admin-style',
            WPFED_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            WPFED_VERSION
        );
    }
}

// Initialize the admin settings class
$wpfed_admin_settings = new WPFED_Admin_Settings();

/**
 * AJAX handler for retrieving form fields for a selected form
 */
function wpfed_get_form_fields_ajax() {
    // Verify nonce for security
    if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'wpfed_get_fields_nonce')) {
        wp_send_json_error('Invalid nonce');
    }
    
    // Validate form ID
    if (!isset($_POST['form_id']) || empty($_POST['form_id'])) {
        wp_send_json_error('Invalid form ID');
    }
    
    $form_id = absint($_POST['form_id']);
    $options = get_option('wpfed_options');
    $display_fields = isset($options['display_fields']) ? $options['display_fields'] : array();
    
    $output = '';
    
    // Retrieve and display form fields
    if (function_exists('wpforms')) {
        $wpforms = wpforms();
        if (is_object($wpforms) && method_exists($wpforms->form, 'get')) {
            $form = $wpforms->form->get($form_id);
            if (!empty($form)) {
                $form_data = wpforms_decode($form->post_content);
                if (!empty($form_data['fields'])) {
                    foreach ($form_data['fields'] as $field) {
                        // Exclude certain field types
                        $disallowed_types = array('divider', 'html', 'pagebreak', 'captcha');
                        if (in_array($field['type'], $disallowed_types)) {
                            continue;
                        }
                        
                        $checked = in_array($field['id'], $display_fields) ? 'checked' : '';
                        $output .= '<label><input type="checkbox" name="wpfed_options[display_fields][]" value="' . esc_attr($field['id']) . '" ' . $checked . ' class="wpfed-field-checkbox"> ' . esc_html($field['label']) . ' (ID: ' . esc_html($field['id']) . ')</label><br>';
                    }
                } else {
                    $output = '<p>' . esc_html__('No fields found in this form.', 'entries-display-for-wpforms') . '</p>';
                }
            } else {
                $output = '<p>' . esc_html__('Form not found.', 'entries-display-for-wpforms') . '</p>';
            }
        } else {
            $output = '<p>' . esc_html__('WPForms API is not available.', 'entries-display-for-wpforms') . '</p>';
        }
    } else {
        $output = '<p>' . esc_html__('WPForms plugin is not active.', 'entries-display-for-wpforms') . '</p>';
    }
    
    echo wp_kses_post($output);
    wp_die();
}
add_action('wp_ajax_wpfed_get_form_fields', 'wpfed_get_form_fields_ajax');