=== Nimble Builder ===
Contributors: nikeo, d4z_c0nf
Author URI: https://presscustomizr.com
Plugin URI: https://wordpress.org/plugins/nimble-builder/
Tags: customizer, editor, page builder, drag and drop
Requires at least: 4.7
Tested up to: 4.9.6
Stable tag: 1.0.1
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

== Installation ==
1. Install the plugin from your WordPress admin in plugins > Add New. Or download the plugin, unzip the package and upload it to your /wp-content/plugins/ directory
2. Activate the plugin
3. Navigate to the WordPress live customizer
4. Click on the "+" button in the previewed page and insert your first section of content
5. Drag a module in the section

== Changelog ==
= 1.0.1 : June 6th, 2018 =
* fixed : submission issue on wordpress.org. The plugin must use unique function names, namespaces, defines, and classnames.
* fixed : location levels need the css rule clear:both
* fixed : clicking on the pencil icon of the tiny_mce_module should expand the editor
* fixed : impossible to resize a fresh new column
* fixed : impossible to move a fresh new module
* fixed : alpha color input unwanted expansion when 2 instances displayed at the same time
* fixed : before (after) loop sections might be duplicated in some edge cases
* improved : reconsider behavior on mouse click (release) in the preview

= 1.0.0 : June 1st, 2018 =
* initial submission to the wordpress.org plugins repository
