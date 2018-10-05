=== Nimble Builder - Drag and Drop Builder for the WordPress Customizer ===
Contributors: nikeo, d4z_c0nf
Author URI: https://presscustomizr.com
Plugin URI: https://wordpress.org/plugins/nimble-builder/
Tags: customizer, editor, page builder, drag and drop
Requires at least: 4.7
Requires PHP: 5.4
Tested up to: 4.9.6
Stable tag: 1.1.0
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Drag-and-drop section builder companion of the Customizr and Hueman themes.

== Description ==
The Nimble Builder is a lightweight section builder intended to be the content creation companion of the Customizr and Hueman themes. It allows you to drag and drop pre-designed sections, or create your own sections, in live preview from the WordPress customizer.

== Features ==
* Drag and drop beautiful and ready-to-use sections in any pages.
* Easily create responsive column layouts.
* Create content, style, move / duplicate / remove elements in live preview.
* Leverage the customizer auto-drafting and schedule publishing features, and safely build and save drafts of your content before deploying in production.
* Works in any WordPress contexts : home, pages, posts, custom post types, archives, author pages, search page, ...

== Screenshots ==
1. Dragging and dropping a pre-designed section
2. Editing content in live preview
3. Creating columns layouts
4. Customizing a section with an image background

== Installation ==
1. Install the plugin through the WordPress plugins screen. Or download the plugin, unzip the package and upload it to your /wp-content/plugins/ directory
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Navigate to the WordPress live customizer
4. Look for a drop zone candidate in the previewed page and click on the "+" button to start building your first section of content
5. Drag a module from the left panel and drop it in a drop zone of the previewed page

== Frequently Asked Questions ==
= Can I use the Nimble Builder with any theme ? =

The Nimble builder has been optimized to work with the Customizr and Hueman theme, but it works with any WordPress theme. If you experience any problem with a specific theme, please report it in the [plugin support forum](https://wordpress.org/support/plugin/nimble-builder).

== Upgrade Notice ==
= 1.1.1 : October 5th, 2018 =
* This version includes major improvements. Many new modules and pre-designed sections are now available to create your pages.
* New modules : heading, icon, button, Google map, Html content, quote, spacer, divider, contact form.
* The user interface has been enhanced with a non intrusive top bar, including do/undo buttons, and global settings for the Nimble builder.

= 1.0.4 : June 14th, 2018 =
* fixed : when margins and paddings are not defined ( number field emptied ), no related CSS properties should be printed.
* fixed : sek-sektion-inner should not have a padding of 15px on front.
* fixed : a nested sektion should reset its parent column padding.
* fixed : empty sektions wrapper should only be printed when customizing.
* fixed : prevent element in the wp content to be displayed out of the wp-content-wrapper when previewing.
* fixed : dynamic CSS can be printed twice : inline and enqueued as CSS file when user logged in.

== Changelog ==
= 1.0.4 : June 14th, 2018 =
* fixed : when margins and paddings are not defined ( number field emptied ), no related CSS properties should be printed.
* fixed : sek-sektion-inner should not have a padding of 15px on front.
* fixed : a nested sektion should reset its parent column padding.
* fixed : empty sektions wrapper should only be printed when customizing.
* fixed : prevent element in the wp content to be displayed out of the wp-content-wrapper when previewing.
* fixed : dynamic CSS can be printed twice : inline and enqueued as CSS file when user logged in.

= 1.0.3 : June 9th, 2018 =
* fixed : missing dropzones around nested sections
* fixed : reseting the spacing of a level was not changing the main setting.
* fixed : the tinyMceEditor not collapsing on 'sek-notify'
* improved : tinyMce text editor => attach callbacks on 'input' instead of 'change keyup'
* improved : module dynamic ui => print the module name instead of 'module' at the bottom
* improved : when clicking more than one time one the + ui icon, visually remind the user that a module should be dragged, with a light animation on the module picker container
* added : encapsulate the singular post / page content inside a dom element so we can generate a dynamic ui around it when customizing + add an edit link to the post or page
* added : introduced a loader overlay printed when the markup of any level being refreshed.
* added : a "+" icon to add module from the sections dynamic UI

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
