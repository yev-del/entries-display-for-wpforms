<?php
/**
 * Plugin Name: Entries Display for WPForms
 * Description: Display WPForms entries as styled comments with customizable options.
 * Version: 0.4
 * Author: YD:dev
 * Author URI: https://deliamure.com/
 * Text Domain: entries-display-for-wpforms
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

// Exit if accessed directly to prevent unauthorized access.
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants for directory paths and version control.
if (!defined('WPFED_VERSION')) {
    $plugin_data = get_file_data(__FILE__, array('Version' => 'Version'), false);
    define('WPFED_VERSION', $plugin_data['Version']);
}

if (!defined('WPFED_PLUGIN_DIR')) {
    define('WPFED_PLUGIN_DIR', plugin_dir_path(__FILE__));
}

if (!defined('WPFED_PLUGIN_URL')) {
    define('WPFED_PLUGIN_URL', plugin_dir_url(__FILE__));
}

// Include necessary files for plugin functionality.
require_once WPFED_PLUGIN_DIR . 'includes/admin-settings.php';
require_once WPFED_PLUGIN_DIR . 'includes/shortcode.php';

// Initialize the plugin by registering styles.
function wpfed_init() {
    // Register frontend styles for the plugin.
    wp_register_style(
        'wpfed-frontend-styles',
        WPFED_PLUGIN_URL . 'assets/css/frontend.css',
        array(),
        WPFED_VERSION
    );
    
    // Translations are automatically loaded by WordPress for plugins on WordPress.org
    // No need to call load_plugin_textdomain() since WordPress 4.6
}
add_action('init', 'wpfed_init');

// Add a hidden nonce field in the admin footer for security in AJAX requests.
function wpfed_admin_footer() {
    $screen = get_current_screen();
    if ($screen && $screen->id === 'settings_page_entries-display-for-wpforms') {
        wp_nonce_field('wpfed_get_fields_nonce', 'wpfed_nonce');
    }
}
add_action('admin_footer', 'wpfed_admin_footer');

// Activation hook to set default options.
function wpfed_activate() {
    // Define default options for the plugin.
    $default_options = array(
        'form_id' => '',
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
            // New vertical alignment option
            'vertical_alignment' => 'top',
            // Updated text styling options with field-specific typography
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
    );
    
    // Set default options if not already configured.
    if (!get_option('wpfed_options')) {
        update_option('wpfed_options', $default_options);
    }
}
register_activation_hook(__FILE__, 'wpfed_activate');

// Deactivation hook for plugin cleanup tasks.
function wpfed_deactivate() {
    // Perform cleanup tasks if necessary.
}
register_deactivation_hook(__FILE__, 'wpfed_deactivate');

// Add a settings link on the plugin page to access plugin options quickly.
function wpfed_settings_link($links) {
    $settings_link = '<a href="options-general.php?page=entries-display-for-wpforms">' . esc_html__('Settings', 'entries-display-for-wpforms') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
}
$plugin = plugin_basename(__FILE__);
add_filter("plugin_action_links_$plugin", 'wpfed_settings_link');