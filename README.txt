=== Leira Transients ===
Contributors: arielhr1987
Donate link: https://github.com/arielhr1987
Tags: transients, admin tools, developer tools, optimization
Requires at least: 4.1
Tested up to: 6.9
Stable tag: 1.0.5
Requires PHP: 8.3
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Inspect, edit, and delete WordPress transients from Tools -> Transients.

== Description ==

Leira Transients gives you direct visibility and control over transient data stored by WordPress and plugins.

From a single admin screen, you can:

* Browse standard and site transients.
* Search, sort, and filter by status (All, Active, Expired, Persistent).
* Quick-edit transient value and expiration.
* Delete one or many transients safely from the UI.
* Use per-page screen options for large datasets.

This plugin is built for developers and advanced site administrators.

**Important**: changing transient values or expiration dates can affect plugin behavior and cache logic. Use carefully on production sites.

== Installation ==

1. Upload the `leira-transients` folder to `/wp-content/plugins/`, or install it from the WordPress plugin directory.
2. Activate the plugin from the Plugins screen in WordPress.
3. Go to Tools -> Transients.
4. Manage transient values, expiration dates, and cleanup actions.

== Frequently Asked Questions ==

= Can I create new transients with this plugin? =
No. The plugin currently supports viewing, editing, and deleting existing transients only.

= What can I edit? =
You can edit transient value and expiration time.

= Is it safe to use on production? =
Yes, but use caution. Incorrect edits can change runtime behavior for themes/plugins that depend on those transients.

= Does it support multisite/site transients? =
Yes. It lists both transient and site transient records.

== Screenshots ==

1. Open the Transients screen from the Tools menu.
2. Browse, search, and filter transients.
3. Quick-edit transient value and expiration.
4. Use Screen Options to customize pagination.

== Support and Contributions ==

GitHub repository: https://github.com/arielhr1987/leira-transients

Suggestions, feature requests, bug reports, and code improvements are all welcome.
If you have an idea to improve the plugin, please open an issue or submit a pull request.

== Changelog ==

= 1.0.5 =
* Improved query performance for count/list view calculations.
* Preserved current search/sort values when switching filter views.
* Improved quick-edit validation for invalid expiration dates.
* Hardened admin notification cookies (`HttpOnly`, `Secure` when applicable, `SameSite=Lax`).
* Improved transient value type detection (JSON primitives, serialized scalar subtypes, and null).
* Updated plugin readme text and documentation clarity.

= 1.0.4 =
* WordPress 6.9 compatibility update.
* Added `blueprint.json` for live preview support.

= 1.0.3 =
* Deployment updates.

= 1.0.2 =
* Improvements requested by the WordPress.org plugin review team.

= 1.0.1 =
* Bug fixes.

= 1.0.0 =
* Initial release.
