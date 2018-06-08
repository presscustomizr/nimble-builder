=== Nimble Builder - Drag and Drop Builder for the WordPress Customizer ===
Contributors: nikeo, d4z_c0nf
Author URI: https://presscustomizr.com
Plugin URI: https://wordpress.org/plugins/nimble-builder/
Tags: customizer, editor, page builder, drag and drop
Requires at least: 4.7
Requires PHP: 5.4
Tested up to: 4.9.6
Stable tag: 1.0.3-beta
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Drag and drop page builder for the WordPress live customizer.

== Description ==
The Nimble Builder is a lightweight drag and drop page builder designed to work in the WordPress live customizer.

== Features ==
* Easily create responsive layouts composed of sections and colums.
* Drag and drop content modules in your pages.
* Create content, style, move / duplicate / remove elements in live preview.
* Leverage the customizer auto-drafting and schedule publishing features, and safely build and save drafts of your content before deploying in production.
* Works in any WordPress contexts : home, pages, posts, custom post types, archives, author pages, search page, ...

== Screenshots ==
1. Inserting a module
2. Editing and styling text content live
3. Creating layouts with columns
4. Customizing a section with an image background

== Installation ==
1. Install the plugin through the WordPress plugins screen. Or download the plugin, unzip the package and upload it to your /wp-content/plugins/ directory
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Navigate to the WordPress live customizer
4. Look for a drop zone candidate in the previewed page and click on the "+" button to start building your first section of content
5. Drag a module from the left panel and drop it in a drop zone of the previewed page

== Frequently Asked Questions ==
= Can I use the Nimble Builder with any theme ? =

Yes, the Nimble Builder works with any WordPress theme. If you experience any problem with a particular theme, please report it in the [plugin support forum](https://wordpress.org/support/plugin/nimble-builder).

== Upgrade Notice ==
= 1.0.2 =
Nimble has been approved to be hosted on the wordpress.org repository.
This version fixes a minor bug related to the level's image background and bring some improvements in the user interface on the previewed page.

== Changelog ==
= 1.0.2 : June 7th, 2018 =
* info : Nimble has been approved to be hosted on the wordpress.org plugin repository!
* fixed : the background overlay should not be applied to a level when there's no background image
* added : a border-radius css rule to the pickable modules
* added : the location type printed at the bottom of the dynamic ui when hovering
* improved : the ui icon size gets smaller when number of columns is >= 4
* improved : the "Insert new section" is revealed when mouse is coming 50 pixels around

= 1.0.1 : June 6th, 2018 =
* fixed : submission issue on wordpress.org. Apply various fixes to the code in order to use unique function names, namespaces, defines, and classnames.
* fixed : location levels need the css rule clear:both
* fixed : clicking on the pencil icon of the tiny_mce_module should expand the editor
* fixed : impossible to resize a fresh new column
* fixed : impossible to move a fresh new module
* fixed : alpha color input unwanted expansion when 2 instances displayed at the same time
* fixed : before (after) loop sections might be duplicated in some edge cases
* improved : reconsider behavior on mouse click (release) in the preview

= 1.0.0 : June 1st, 2018 =
* initial submission to the wordpress.org plugins repository
