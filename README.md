=== Entries Display for WPForms ===

Display WPForms entries as beautifully styled comments with full customization options. Perfect for testimonials, reviews, and user feedback.

== Description ==

WPForms Entries Display allows you to showcase form submissions from WPForms as beautifully styled comments on any page or post. This plugin bridges the gap between form collection and public display, making it perfect for testimonials, reviews, feedback, and other user-generated content.

= Key Features =

* **Simple Shortcode Implementation** - Display entries with a simple shortcode: [wpforms_entries_display]
* **Customizable Styling** - Control colors, borders, shadows, and spacing to match your site's design
* **Field Selection** - Choose which form fields to display and in what order
* **Entry Filtering** - Filter entries by user, entry status (read/unread/starred), and more
* **Pagination** - Control how many entries appear per page
* **Date Formatting** - Customize how submission dates are displayed
* **User Information** - Optionally show the username of the person who submitted the form
* **Field Labels** - Show or hide field labels for cleaner display
* **Empty Fields** - Option to hide fields with no content

= Intuitive Shortcode Generator =

Say goodbye to manual shortcode typing! Our built-in visual shortcode generator makes configuration a breeze. Simply select your options from dropdown menus, see a live preview of your shortcode, and copy it with a single click. This powerful tool eliminates guesswork and ensures perfect implementation every time - even for WordPress beginners!

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

= 0.1 =

* Initial release


== Upgrade Notice ==

= 0.2 =

Fix WordPress.org plugin review issues and improve admin UI

- Fixed JS/CSS enqueuing using wp_enqueue_script() and wp_localize_script() instead of inline scripts
- Removed deprecated load_plugin_textdomain() call as it's no longer needed for WordPress.org plugins
- Fixed date format selector UI issues with duplicated text and missing options
- Improved admin UI layout with better spacing and alignment
- Added proper display for custom date format input and button
- Fixed responsive design issues in the admin settings page
- Updated CSS with better organization and comments