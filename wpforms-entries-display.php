<?php
/**
 * Plugin Name: Entries Display for WPForms
 * Description: Display WPForms entries as styled comments with customizable options.
 * Version: 0.1
 * Author: YD:dev
 * Author URI: https://deliamure.com/
 * Text Domain: entries-display-for-wpforms
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

// Exit if accessed directly to prevent unauthorized access.
if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('WP_Filesystem')) {
    require_once ABSPATH . 'wp-admin/includes/file.php';
}
WP_Filesystem();
global $wp_filesystem;

// Define plugin constants for directory paths and version control.
define('WPFED_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WPFED_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WPFED_VERSION', '1.0.0');

// Include necessary files for plugin functionality.
require_once WPFED_PLUGIN_DIR . 'includes/admin-settings.php';
require_once WPFED_PLUGIN_DIR . 'includes/shortcode.php';

// Initialize the plugin by registering styles and setting up text domain for translations.
function wpfed_init() {
    // Register frontend styles for the plugin.
    wp_register_style(
        'wpfed-frontend-styles',
        WPFED_PLUGIN_URL . 'assets/css/frontend.css',
        array(),
        WPFED_VERSION
    );
    
    // Load plugin text domain for localization support.
    load_plugin_textdomain('entries-display-for-wpforms', false, dirname(plugin_basename(__FILE__)) . '/languages');
}
add_action('init', 'wpfed_init');

// Add a hidden nonce field in the admin footer for security in AJAX requests.
function wpfed_admin_footer() {
    $screen = get_current_screen();
    if ($screen && $screen->id === 'settings_page_entries-display-for-wpforms') {
        echo '<input type="hidden" id="wpfed_nonce" value="' . esc_attr(wp_create_nonce('wpfed_get_fields_nonce')) . '">';    
    }
}
add_action('admin_footer', 'wpfed_admin_footer');

// Activation hook to set default options and create necessary directories.
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
        )
    );
    
    // Set default options if not already configured.
    if (!get_option('wpfed_options')) {
        update_option('wpfed_options', $default_options);
    }
    
    // Create required folders for assets if they do not exist.
    if (!file_exists(WPFED_PLUGIN_DIR . 'assets')) {
        WP_Filesystem();
        global $wp_filesystem;
        $wp_filesystem->mkdir($upload_dir['basedir'] . '/wpforms-entries-display', 0755);
    }
    
    if (!file_exists(WPFED_PLUGIN_DIR . 'assets/css')) {
        WP_Filesystem();
        global $wp_filesystem;
        $wp_filesystem->mkdir($upload_dir['basedir'] . '/wpforms-entries-display/css', 0755);
    }
    
    if (!file_exists(WPFED_PLUGIN_DIR . 'assets/js')) {
        WP_Filesystem();
        global $wp_filesystem;
        $wp_filesystem->mkdir($upload_dir['basedir'] . '/wpforms-entries-display/js', 0755);
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