=== Notion Content ===
Contributors: yourusername
Tags: Notion, content management, Notion API, custom fields, CSS, WordPress plugin
Requires at least: 5.5
Tested up to: 6.3
Stable tag: 1.0.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Sync and display content from a Notion database in your WordPress site. Easily customize element styles and add custom CSS.

== Description ==

**Notion Content** is a powerful WordPress plugin that allows you to pull content from a Notion database directly into your WordPress site. This plugin provides a flexible way to display and style Notion pages using shortcodes, manage individual page refreshes, and configure custom styles in the WordPress admin.

### Key Features
* **Sync Notion Content**: Pull content from any Notion database using your API Key and Database URL.
* **Content Shortcodes**: Generate shortcodes for individual Notion pages to display them easily in posts or pages.
* **Flexible Styling**: Customize styles for tables, lists, and list items, and add global custom CSS.
* **Local Storage**: Stores Notion content locally, reducing API calls and improving performance.
* **Customizable Admin Interface**: Set up tabs for easy style and custom CSS management.

== Installation ==

1. Upload the `notion-content` folder to the `/wp-content/plugins/` directory or install the plugin via the WordPress Plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Go to **Notion Content > Settings** to enter your Notion API key and Database URL.
4. Use **Notion Content > Notion Pages** to list and manage synced pages, view shortcodes, and refresh content as needed.

== Usage ==

1. After setup, navigate to **Notion Content > Notion Pages** to see a list of pages from your connected Notion database.
2. Copy the shortcode for a page and paste it into any WordPress post or page.
3. Customize styles and global CSS under **Notion Content > Styles**.

== Frequently Asked Questions ==

= How do I find my Notion API Key and Database URL? =
Refer to the Notion API documentation to create an integration and find the necessary keys. Make sure the integration has access to the database you want to sync.

= Can I style the Notion content differently for each page? =
Yes! Use the **Classes** section in **Notion Content > Styles** to customize CSS for each element. You can also add custom CSS in the **Custom CSS** tab.

= Does the plugin cache Notion content? =
Yes, it stores content locally in a custom database table to minimize Notion API calls. Content can be refreshed manually for specific pages or all pages at once.

== Screenshots ==

1. **Settings Page**: Enter API Key and Database URL to connect to Notion.
2. **Notion Pages List**: View synced pages and copy shortcodes.
3. **Styles Tab - Classes**: Customize CSS classes for Notion content elements.
4. **Styles Tab - Custom CSS**: Add global CSS for all Notion content.

== Changelog ==

= 1.0.0 =
* Initial release of Notion Content.
* Sync content from Notion database to WordPress.
* Customizable classes and custom CSS for enhanced styling.
* Local storage for efficient page loading.

== Upgrade Notice ==

= 1.0.0 =
Initial release of the plugin.

== License ==

This plugin is licensed under the GPLv2 or later. See https://www.gnu.org/licenses/gpl-2.0.html for details.

