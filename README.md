=== Entries Display for WPForms ===

Display WPForms entries as beautifully styled comments with advanced typography controls and theme override capabilities. Perfect for testimonials, reviews, and user feedback.

== Description ==

WPForms Entries Display allows you to showcase form submissions from WPForms as beautifully styled comments on any page or post. This plugin bridges the gap between form collection and public display, making it perfect for testimonials, reviews, feedback, and other user-generated content.

= Key Features =

* **Simple Shortcode Implementation** - Display entries with a simple shortcode: [wpforms_entries_display]
* **Advanced Typography Controls** - Granular font styling for dates, usernames, emails, field labels, and content
* **Theme Override Protection** - Typography settings use !important CSS to override external theme editors like Elementor
* **Smart Field Detection** - Automatically detects and styles email fields, comments, and general content differently
* **Customizable Styling** - Control colors, borders, shadows, and spacing to match your site's design
* **Field Selection** - Choose which form fields to display and in what order
* **Entry Filtering** - Filter entries by user, entry status (read/unread/starred), and more
* **Pagination** - Control how many entries appear per page
* **Date Formatting** - Customize how submission dates are displayed
* **User Information** - Optionally show the username of the person who submitted the form
* **Field Labels** - Show or hide field labels for cleaner display
* **Empty Fields** - Option to hide fields with no content
* **Live Preview** - Real-time preview of your styling changes in the admin area

= Advanced Typography System =

Our comprehensive typography system gives you complete control over text styling:

* **Date Typography** - Customize font size, weight, and style for entry dates
* **Username Typography** - Style WordPress usernames independently 
* **Email Typography** - Specific styling for email field values
* **Field Labels Typography** - Control appearance of all field labels (e.g., "Name:", "Email:")
* **Content Typography** - Style comment/message fields and general content

Each typography setting includes font size (10px-30px), weight (100-900), and style (normal/italic) options with real-time preview.

= Theme & Page Builder Compatibility =

Built with modern WordPress development in mind, this plugin ensures your typography choices always take precedence:

* **Override Protection** - Uses !important CSS declarations to override theme styling
* **Page Builder Support** - Specifically targets Elementor, Divi, and Visual Composer
* **Dynamic Form Handling** - Automatically adapts to any form structure with any number of fields
* **Smart Field Recognition** - Intelligently detects field types for appropriate styling

= Intuitive Shortcode Generator =

Say goodbye to manual shortcode typing! Our built-in visual shortcode generator makes configuration a breeze. Simply select your options from dropdown menus, see a live preview of your shortcode, and copy it with a single click. This powerful tool eliminates guesswork and ensures perfect implementation every time - even for WordPress beginners!

= Enhanced Admin Experience =

* **Live Preview Widget** - Floating sticky preview that updates in real-time as you adjust settings
* **Organized Settings** - Clean, card-based layout with logical grouping
* **Typography Controls** - Grid-based typography controls for easy configuration
* **Visual Feedback** - Immediate preview of changes without page refresh

= Requirements =

* WordPress 5.0 or higher
* WPForms Pro with Entries functionality
* PHP 7.0 or higher

= Usage =

After installation, go to Settings > WPForms Entries to configure your default display options. Then use the shortcode generator to create a customized shortcode for your specific needs.

Basic usage:
[wpforms_entries_display id="123"]

Advanced usage:
[wpforms_entries_display id="123" fields="1,3,5" number="10" user="current" type="starred" show_date="yes" hide_field_labels="yes"]

= Perfect For =

* Testimonials
* Reviews
* Feedback display
* Q&A sections
* Community comments
* Event registrations
* Job applications
* Contest entries

== Installation ==

1. Upload the `wpforms-entries-display` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Settings > WPForms Entries to configure your display options
4. Use the shortcode generator to create a customized shortcode
5. Add the shortcode to any page or post where you want to display form entries

== Frequently Asked Questions ==

= Does this plugin work with the free version of WPForms? =
No, this plugin requires WPForms Pro or higher with the Entries functionality enabled.

= Can I display entries from multiple forms on the same page? =
Yes, you can use multiple shortcodes on the same page, each referencing a different form.

= Can I filter entries to show only those from the current logged-in user? =
Yes, use the shortcode parameter `user="current"` to display only entries submitted by the current user.

= Is it possible to sort entries by a specific field? =
Yes, use the `sort` parameter with the field ID and `order` parameter with either "asc" or "desc" to control sorting.

= Can I customize the appearance of the entries? =
Absolutely! The plugin offers extensive styling options including colors, borders, shadows, spacing, and more.

== Screenshots ==

1. Frontend display of form entries as styled comments
2. Admin settings page with customization options
3. Visual shortcode generator for easy implementation
4. Style customization options for perfect integration with your theme

== Changelog ==

= 0.5 =

* **NEW: Advanced Typography System**
    * Added comprehensive typography controls for dates, usernames, emails, field labels, and content
    * Each typography setting includes font size, weight, and style options
    * Real-time preview updates as you adjust typography settings
    * Smart field detection automatically applies appropriate styling to different field types

* **NEW: Theme Override Protection**
    * Typography settings use !important CSS to override external theme editors
    * Specific targeting for Elementor, Divi, and Visual Composer page builders
    * Ensures your styling choices always take precedence over theme defaults

* **Enhanced Admin Experience**
    * Added floating sticky Live Preview widget with real-time updates
    * Reorganized settings into logical card-based sections
    * Improved typography controls with grid-based layout
    * Enhanced visual feedback and user experience

* **Improved Form Handling**
    * Dynamic form handling works with any number of fields
    * Smart field type detection for emails, comments, and general content
    * Prevents duplicate username display when form has name fields
    * Better separation of WordPress usernames from form field data

* **Code Improvements**
    * Enhanced CSS generation with field-specific styling
    * Improved admin JavaScript for better typography preview
    * Better code organization and documentation

= 0.4 =

* Security & Stability Enhancements:
    * Strengthened security by replacing a manual nonce field with `wp_nonce_field()`.
    * Improved AJAX security with stricter nonce validation and proper error responses (`wp_send_json_error`).
    * Enhanced input sanitization to prevent potential vulnerabilities.
    * Modernized the clipboard copy functionality with fallback for older browsers and clear user feedback.
* Code Refactoring & Best Practices:
    * Refactored the admin settings page and AJAX handlers into a more organized, class-based structure.
    * Corrected script enqueuing logic to follow WordPress best practices.
    * Removed unreliable directory creation logic from the activation hook.
    * Improved AJAX error handling in the admin JavaScript to provide clearer feedback on failure.
* Bug Fixes:
    * Fixed a critical PHP error caused by a missing closing brace in `admin-settings.php`.

= 0.3 =

* UI/UX Improvements:
  * Added live preview section to visualize style settings in real-time
  * Reorganized settings into collapsible sections for better organization
  * Fixed color picker alignment and display issues
  * Improved spacing and layout in style settings
  * Enhanced responsive design for better mobile experience
  * Optimized CSS code by removing duplicates and improving structure

= 0.2 =

Fix WordPress.org plugin review issues and improve admin UI

- Fixed JS/CSS enqueuing using wp_enqueue_script() and wp_localize_script() instead of inline scripts
- Removed deprecated load_plugin_textdomain() call as it's no longer needed for WordPress.org plugins
- Fixed date format selector UI issues with duplicated text and missing options
- Improved admin UI layout with better spacing and alignment
- Added proper display for custom date format input and button
- Fixed responsive design issues in the admin settings page
- Updated CSS with better organization and comments

= 0.1 =

* Initial release