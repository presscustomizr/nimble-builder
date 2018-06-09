# Nimble Builder v1.0.3 [![Built with Grunt](https://cdn.gruntjs.com/builtwith.png)](http://gruntjs.com/)
![Nimble Builder](/nimble.jpg)

> Drag and drop page builder for the WordPress live customizer.

== Description ==
The Nimble Builder is a lightweight drag and drop page builder designed to work in the WordPress live customizer.

== Features ==
* Easily create responsive layouts composed of sections and colums.
* Drag and drop content modules in your pages.
* Create content, style, move / duplicate / remove elements in live preview.
* Leverage the customizer auto-drafting and schedule publishing features, and safely build and save drafts of your content before deploying in production.
* Works in any WordPress contexts : home, pages, posts, custom post types, archives, author pages, search page, ...

== Installation ==
1. Install the plugin through the WordPress plugins screen. Or download the plugin, unzip the package and upload it to your /wp-content/plugins/ directory
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Navigate to the WordPress live customizer
4. Look for a drop zone candidate in the previewed page and click on the "+" button to start building your first section of content
5. Drag a module from the left panel and drop it in a drop zone of the previewed page

== Changelog ==
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
* fixed : the background overlay should not be applied to a level when there's no background image
* added : a border-radius css rule to the pickable modules
* added : the location type printed at the bottom of the dynamic ui when hovering
* improved : the ui icon size gets smaller when number of columns is >= 4
* improved : the "Insert new section" is revealed when mouse is coming 50 pixels around

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
