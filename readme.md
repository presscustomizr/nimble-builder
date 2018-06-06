# Nimble Builder v1.0.1 [![Built with Grunt](https://cdn.gruntjs.com/builtwith.png)](http://gruntjs.com/)
![Nimble Builder](/nimble.jpg)

> Drag and drop page builder for the WordPress customizer.

== Description ==
The Nimble Builder is a lightweight page builder that works from the WordPress live customizer.

== Features ==
* Organize your content in responsive sections and columns layouts.
* Works in any WordPress contexts : home, pages, posts, custom post types, archives, author pages, search page.

== Installation ==
1. Install the plugin right from your WordPress admin in plugins > Add New.
1-bis. Download the plugin, unzip the package and upload it to your /wp-content/plugins/ directory
2. Activate the plugin
3. Navigate to the live customizer
4. Click on the "+" button in the previewed page and insert your first section of content
5. Drag a module in your section

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
