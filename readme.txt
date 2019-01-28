=== Nimble Page Builder ===
Contributors: nikeo, d4z_c0nf
Author URI: https://nimblebuilder.com
Plugin URI: https://wordpress.org/plugins/nimble-builder/
Tags: page builder, customizer, drag and drop, header, footer, landing page
Requires at least: 4.7
Requires PHP: 5.4
Tested up to: 5.0.3
Stable tag: 1.4.5
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Powerful drag and drop page builder using the native WordPress customizer.

== Description ==
= What is the Nimble Page Builder ? =
The **[Nimble Page Builder](https://nimblebuilder.com/?utm_source=wp-org&utm_campaign=nimble-builder-page&utm_medium=link)** is a powerful and easy to use page builder plugin. It takes the native WordPress customizer to a level you've never seen before.

Nimble allows you to drag and drop content modules, or pre-built section templates, into any context of your site, including search results or 404 pages. You can edit your sections in real time from the live customizer, and then publish when you are happy of the result, or save for later.

You can also start designing from a blank page, and create your header, footer, and custom content.

The plugin automatically creates fluid and responsive sections for a pixel-perfect rendering on smartphones and tablets, without the need to add complex code.

= Features =
* Drag and drop carefully crafted pre-designed sections into any pages.
* Content modules: classic text editor, image, column layouts, contact form, button, icons, map, html code, and more.
* Add image background to your sections, and activate a parallax effect.
* Easily create responsive column layouts.
* Create content, style, move / duplicate / remove elements in live preview.
* Embed shortcodes from other plugins, and see the result in live preview.
* Embed WordPress blocks, videos, tweets or any embed types supported by WordPress, and see the result in live preview.
* Leverage the customizer auto-drafting and schedule publishing features, and safely build and save drafts of your content before deploying in production.
* Works in any WordPress contexts : home, pages, posts, custom post types, archives, author pages, search page, ...

== Documentation ==
You'll find an online knowledge base for the Nimble builder here : [Nimble builder documentation](https://docs.presscustomizr.com/collection/334-nimble-builder/?utm_source=wp-org&utm_medium=link&utm_campaign=nimble-builder-page).

== Screenshots ==
1. Creating a page with 3 sections
2. Dragging and dropping a pre-designed section
3. Editing content in live preview
4. Creating columns layouts
5. Customizing a section with an image background

== Installation ==
1. Install the plugin through the WordPress plugins screen. Or download the plugin, unzip the package and upload it to your /wp-content/plugins/ directory
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Navigate to the WordPress live customizer
4. Look for a drop zone candidate in the previewed page and click on the "+" button to start building your first section of content
5. Drag a module from the left panel and drop it in a drop zone of the previewed page

== Frequently Asked Questions ==
= Can I use the Nimble Builder with any theme ? =

The Nimble builder works with any WordPress theme. If you experience any problem with a specific theme, please report it in the [plugin support forum](https://wordpress.org/support/plugin/nimble-builder).

== Upgrade Notice ==
= 1.4.5 =
Sections and modules can now be inserted with a double-click. Added minor new options to modules : button, icon, images, heading.

== Changelog ==
= 1.4.5 : January 26th, 2019 =
* fixed : Dynamic CSS : the font-size input should not have a default value
* fixed : when the value is a string of an object, no need to write an empty value
* fixed : Twenty nineteen theme heading styling, adding a border ::before Hx
* fixed : wrong selectors for the scrolling animation of the menu anchor
* fixed : [html module] removed the potentially misleading placeholder pre tag.
* improved : [button module] a title attribute can be displayed on hover
* improved : added a set of default CSS rules for headings in order to be less dependant of the theme style
* improved : reset the base font-size at location level. Ensure a consistent size in em for child nodes. Break the inheritance from the theme rules
* improved : during customization added support for undo / redo with keyboard combination ctrl + z/y
* improved : default CSS rules for links and images
* improved : allow modules and pre-built sections to be inserted by double-clicking on module / section icon
* improved : when a template is used indicate it in the customization topbar
* improved : help users created real round icon borders
* added : page building support for Internet Explorer by allowing double-click insertion
* added : [heading module] a link option
* added : a title attribute option to the heading module
* added : [image module] an option to set a title attribute

= 1.4.4 : January 17th, 2019 =
* fixed : another PHP warning when using an rgba color for an icon, a button or in a form
* improved : compatibility with the Customizr theme

= 1.4.3 : January 16th, 2019 =
* fixed : a PHP warning when using an rgba color for an icon, a button or in a form
* improved : support for Firefox.

= 1.4.2 : December 22nd, 2018 =
* added : a new global option to try the beta features
* improved : header_one pre-build section

= 1.4.1 : December 21st, 2018 =
* fixed : possible php notice when generating CSS rules for column width
* fixed : replace array_filter() which expects at most 2 parameters in version oho PHP < 5.6, by a foreach loop
* fixed : style of the column resizable handle broken in the Hueman theme
* fixed : dynamic stylesheet not refreshed when dropping a section in a global location
* fixed : fix loop_start and loop_end duplication in infinite-scroll loops. Could occur with JetPack, Hueman Pro and Customizr Pro.
* added : a dismissable welcome notice when no sections has been created yet
* added : a specific placeholder for header and footer locations, when customizing only
* added [beta] : a widget area module
* added [beta] : a new group of pre-built sections for header and footer
* added : a "selectOptions" param to the signature of api.czr_sektions::setupSelectInput, allowing us to provide a set of options
* added [beta] : implemented a parser for template tags inside double curly braces {{...}}
* improved [beta] : modify ::dnd_canDrop with the case when user tries to drop a content header / footer section in a footer / header location => prevent + print an alert msg

= 1.4.0 : December 15th, 2018 =
* fixed : use a different name for the various global inline stylesheets : breakpoint and inner/outer widths
* fixed : when appending CSS rules by filtering 'nimble_get_dynamic_stylesheet', in Sek_Dyn_CSS_Builder::get_stylesheet, there's no way to know if we are writing a local or a global stylesheet
* fixed : UI of the global option is being re-generated when skope changes
* updated : Font Awesome to v5.5.0
* improved : deprecation of skope id 'skp__post_page_home'. Now, when the home page is a static page, the Nimble options are the same as the page ones. Only the home with latests posts option has a specific set of Nimble options.
* improved : various improvement of the UI and UX : clearer explanations of what the settings are doing, dynamic resizing of the UI icons when sections and columns are too narrows.
* improved : deprecation of the "blank Nimble Builder template" in favor of a new set of options, fine-grained header and footer
* added : the fundations for a header and footer customization

= 1.3.3 : December 5th, 2018 =
* fixed : line breaks not automatically added when setting the content in the WP editor module
* updated Nimble Builder logo

= 1.3.2 : December 4th, 2018 =
* fixed : button module => set a default links hover color to avoid the default theme's one to be applied
* fixed : button module, icon module, image module, FP module => invalid pointer and title attribute when customizing
* fixed : icon module => themes like Twenty Seventeen styling the link underline with a box-shadow instead of the regular "text-decoration:underline" rule
* fixed : When typing fast in a number input, the last value is not taken into account
* added : a gutenberg-like way to move sections up and down. convenient for big sections, painful to drag with the regular sortable handle

= 1.3.1 : November 26th, 2018 =
* fixed : WP editor module, editor content not updated when clicking on the module UI hamburger menu
* fixed : video embedded not displayed when using the WP editor module, Add Media > Insert from Url
* fixed : use the_nimble_tinymce_module_content instead of the_content when handling the "autop" option
* fixed : video embed iframe overflowing the module wrapper
* fixed : impossible to move a module in a freshly created new section
* improved : added a default underline style for links inside the WP editor module
* improved : during drag and drop, better proximity detection + only one drop candidate highlighted a time. See support topic https://wordpress.org/support/topic/few-improvement-suggestions/
* improved : refined the way links are handled in the preview. Two cases : 1-internal link ( <=> api.isLinkPreviewable(... ) = true ) : navigation allowed with shift + click, 2-extenal link => navigation is disabled
* improved : performances of the customizer UI, significant speed improvements when rendering the various controls for level options
* updated : Google font list to the latest version : https://fonts.google.com/
* renamed "Full Nimble Builder template (beta)" to "Full Nimble Builder template (beta)"
* improved : picking content logic has been improved. The section picker is opened when adding a section ( click on + insert a new section button ), the module picker opened in all other cases

= 1.3.0 : November 26th, 2018 =
* fixed : the customizer UI was not loaded on WordPress network installs
* fixed : columns layout randomly broken
* fixed : tinymce editor module could be not accessible in some cases
* improved : use a namespaced version of the select2 javascript library to avoid collision with other plugins or themes using select2
* improved : make sure the tinyMce module is always accessible
* added : introduced a new Nimble full page template (beta) using global header and footer locations

= 1.2.2 : November 11th, 2018 =
* fixed : php function_exists( '\Nimble\ ... ) breaks in some version of php ( 5.6.38 )
* fixed : always check if 'do_blocks' exists for retrocompatibility with WP < 5.0

= 1.2.1 : November 10th, 2018 =
* fixed : php function function_exists() can return false when the tested namespaced function starts with a backslash.
* fixed : parallax background only applied to section level
* fixed : background smart load only applied to section level
* improved : when dragging content, no need to print dropzones before or after empty sections
* improved : introduce a Nimble content filter for the TinyMce editor module, in order to prevent a content "corruption" by third party plugins

= 1.2.0 : November 8th, 2018 =
* fixed : added compatibility patch for WordPress 5.0. Waiting for core decision on : https://core.trac.wordpress.org/ticket/45292
* fixed : parallax effect not being applied on preset section drop
* improved : image module margins are not inherited from the theme

= 1.1.9 : October 31st, 2018 =
* fixed : broken column width in mobiles for columns with a custom horizontal margin.
* fixed : conflict with Anspress plugin when uploading an image on front.
* fixed : the content picker input ( for link creation ) was broken : "Set custom url" could be printed multiple times, no search results was generating an error, some pages or posts could not be listed.
* fixed : conflict with HappyForms plugin when customizing a form.
* improved : simple form module, added sender's email in the body of the message.
* improved : simple form module, animate with a scroll action to focus on the message after a send action.
* added : an option for a parallax effect on section's background image. Compatible with lazy loading.

= 1.1.8 : October 23rd, 2018 =
* improved : performance improvements with new options to lazy load images
* added : an admin page for Nimble Builder, to display the system informations
* added : a dismissable update notifications in admin

= 1.1.7 : October 11th, 2018 =
* fixed : check on php and wordpress version not preventing some plugin functions to be fired.

= 1.1.6 : October 11th, 2018 =
* fixed : normalized the text style of the user interface when previewing, so it's not impacted by the theme or other's plugins style
* improved : added a way to make <a> links unclickable. partially fixes #193
* added : a "Contact-us" category of sections, including 2 new pre-designed sections

= 1.1.5 : October 10th, 2018 =
* fixed : columns of a pre-designed sections not resizable after a drop
* fixed : don't animate when duplicating a column or a module
* fixed : added the missing button text option for the form module
* improved : better support for https secure protocol when building the stylesheet URL

= 1.1.4 : October 9th, 2018 =
* fixed : code typo generating a php error ( https://wordpress.org/support/topic/unable-to-activate-44/ )
* fixed : use 'https' when building the dynamic stylesheet url when is_ssl()

= 1.1.3 : October 9th, 2018 =
* fixed : user interface not generated on the first click in some cases

= 1.1.2 : October 8th, 2018 =
* improved : the content picker should be available when expanding the main Nimble panel for the first time.
* improved : the collection of pre-designed sections is fetched earlier for better perforamnces.
* improved : a set of params can now be passed to a custom location when registering.

= 1.1.1 : October 7th, 2018 =
* fixed : wrong error message, indicating a missing "ver_ini" property for column and module generated when dropping a module in a section to create
* added : a filter 'nimble_get_locale_template_path', used for example in the Hueman theme to define a custom Nimble template path

= 1.1.0 : October 5th, 2018 =
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
