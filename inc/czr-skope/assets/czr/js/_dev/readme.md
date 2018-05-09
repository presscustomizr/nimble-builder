# Customizr v4.0.17 [![Built with Grunt](https://cdn.gruntjs.com/builtwith.png)](http://gruntjs.com/)
![Customizr - Free Wordpress Theme](/screenshot.png) 

> Customizr is a simple and fast WordPress theme designed to help you attract and engage more visitors. Provides a perfect user experience on smartphones. Powers more than 100K active sites around the world. Hundreds of 5-stars reviews received on WordPress.org.

## Copyright
**Customizr** is a free WordPress theme designed by Nicolas Guillaume in Nice, France. ([website : Press Customizer](http://presscustomizr.com>)) 
Feel free to use, modify and redistribute this theme as you like.
You may remove any copyright references (unless required by third party components) and crediting is not necessary, but very appreciated... ;-D. 
Customizr is distributed under the terms of the [GNU GPL v2.0 or later](http://www.gnu.org/licenses/gpl-3.0.en.html)


## Demo, Documentation, FAQs and Support
* DEMO : https://demo.presscustomizr.com/
* DOCUMENTATION : http://docs.presscustomizr.com/collection/76-customizr-theme
* FAQs : http://docs.presscustomizr.com/category/90-faq-and-common-issues
* SUPPORT FORUM : https://wordpress.org/support/theme/customizr

## Licenses
Customizr is distributed under the terms of the [GNU GPL v2.0 or later](http://www.gnu.org/licenses/gpl-3.0.en.html)
All images included in the theme are either created for the theme and inheriting its license, or licensed under CC0.
All other theme assets like scripts, stylesheets are licensed under GNU General Public License version 2, see file license.txt, or GPL compatible licenses like MIT, WTFPL.
See headers of each files for further details.

## Changelog
= 4.0.17 January 28th 2018 =
* Fix: display the 404 content only when we're really in the 404 context. fixes #266
* Fix: improve RTL submenu compliance 1) submenu animations -> fixes #1414  2) caret rotation adjustment when is RTL
* Imp: Woocommerce - allow lightbox effect and smartload in product short description. fixes #1394
* Imp: Woocommerce - better u/o list styling for the product short description. fixes #1393
* Added : The topbar can now be displayed on mobile devices

= 4.0.16 January 16th 2018 =
* Fix: tagline not displayed in the header. fixes #1389
* Fix: wording typo in the featured pages description placeholder

= 4.0.15 January 16th 2018 =
* Fix: modern - fix animated underline not removable in some navigation menus. Also do not underline current menu item when the underline hover effect option is disabled. fixes #1363
* Fix: modern - remove useless @import rules for unused gfonts. fixes #1366
* Fix: Wp icon font-family possible override with the pro Font Customizer. Fixes #1350
* Fix: List in wc product description missing list style type. Fixes #1354
* Fix: slider caption not centered in ipad Mini. Fixes #1356
* Fix: clicking menu items with children and no URL bring to 404. Fixes #1358
* Fix: remove unwanted vertical separator before comments link in single posts. Fixes #1381
* Improved: improve mobile menu horizontal alignment. fixes #1380
* Improved: full page search form focus/blur on overlay open/close. fixes #1374
* Improved: upgraded the Font Awesome icon set to its latest version. adresses #1364

= 4.0.14 December 22nd 2017 =
* Fix: decrease regular submenu top to 15px. fixes #1333
* Fix: apply margin-bottom to the right wrapper element, as part of the fix for #1331
* Fix: avoid adding slider metabox to attachment which are not images. fixes #1317
* Fix: widget categories title transformed to uppercase. apply the uppercase rule only to its list items also remove the bold font-weight. fixes #1309
* Fix: always display the comment form before comment list (if any)
* Fix: slider cta hiding text. fixes #1299
* Fix: related posts height after the content with two sidebars fixes #1304
* Fix: fix search icon not appearing in the topbar. fixes #1324
* Fix: fix WooCommerce Terms and Condition checkbox in Checkout page is not really visible. fixes #1340
* Fix : dropdown menu-item word-break property set to break-word. Fixes #1339
* added: a boxed layout options for the header, the content and the footer
* added: new option to allow a menu dropdown on click for mobile menu and side menu. Enabled by default on mobile devices

= 4.0.13 November 20th 2017 =
* Fix : WP 4.9 Code Editor issue could impact the custom css customizer option when checking errors in the code

= 4.0.12 November 15th 2017 =
* Fix: post/page layout meta box must show the contextualized default layout. fixes #1266
* Fix: fix header/overlay search label not custom skinned. fixes #1295
* Fix: fix potential slider captions overlapping on page load. fixes #1293
* Imp : more precis title of the topbar menu
* Imp: add new options to control the singular blocks location. author box (single post), related posts (single post), comments ( single posts and pages )
* Imp: add mobile header search location option

= 4.0.11 November 6th 2017 =
* Fix : label padding for comment text area applied to other inputs. Fixes #1287
* Fix : input white background applied to unwanted selectors. fixes #1288
* Fix : mCustomScrollbar => the scrollers are positionned at the bottom on the first instantiation. Fixes #1285

= 4.0.10 November 4th 2017 =
* Fix : WooCommerce, make wc-cart header button act like a dropdown only when displaying the wc-cart widget (so not when displayed in the mobile header). fixes #1274
* Fix : Some Woocommerce form layout issues. fixes #1243
* Fix : WP 4.9 compatibility => in WP v4.9, the control options are not wrapped in the params property but passed directly instead
* Fix : image description in attachment not correctly displayed. fixes #1231
* Fix : single attachment date meta should not be a link. fixes #1233
* Fix : added missing retina (x2) placeholder images
* Fix : don't collapse mobile mene when scrolling fixes #1226
* Fix : slider caption text size still too big on mobile. fixes #1235
* Fix : update the way we get woocommerce cart url according to the new api + backward compatibility. fixes #1223
* Fix : fix possible wrong responsive images URL for sliders in multisite installs. fixes #1247
* Fix : remove the active callback of the front page content section in the customizer. Fixes #1252
* Fix : WP4.9 compat customizer additional CSS default. fixes #1255
* Fix : hover style on comment date links. Fixes #1114
* Fix : if comments are displayed by the user ( option is checked ) always show comment history, even if comment are closed. Fixes #1253
* Fix : removed no results and 404 quotations, probably wrongly attributed to authors. Fixes #1142
* Fix : make sure the 404 content is always displayed when is_404() even if have_posts() is true. Fixes #1260
* Imp : color style of the comment text area. Fixes #1268.
* Imp : better the way to store when the user started using the theme
* Imp : performance improvement by implementing lazyloading for the main slider. Images are loaded when the become visible. New specific option in advanced > performance
* Imp : always close the mobile menu expanded when resizing
* Imp : increased the .comment_link font-size in related posts
* Imp : an anchor 'linear' scroll effect can be set by adding the attribute data-anchor-link="true" to a link
* Imp : add custom page template for the modern style. fixes #1209
* Imp : footer credits translation strings and link title.
* Imp : footer colophon is now text-align:center for smartphones in portrait mode <=> media-breakpoint-down(xs)
* Imp : bulleted and numbered lists formatting. Second line should be indented to the start of the text of the first line. added more space before and around. fixes #1102 #1183 #1224 #1228
* Imp : wraps the_content() in the div.czr-wp-the-content element in the templates printing the wp content : singulars ( including attachment ) and plain post grid
* Imp : add specific model and templates to render the post attachment of type image content
* Imp : slider nav dots made smaller for mobiles @media-breakpoint-down(xs)
* Imp : better styling for post metas. Fixes #1113
* Added : a layout field meta box option for attachments. => if the layout is not set for a particular attachment, it will be inherited from the parent ( which is the current behaviour )
* Added : the anchor scroll for the comment link in single posts
* Added : new option to control the visibility of the slider navigation bullets. Implement #1207
* Added : Doc search in the theme admin page "About Customizr"

= 4.0.9 October 9th 2017 =
* Fix : global skin CSS not printed when no custom header skin. Fixes #1215.
* Fix : various html fixes like duplicated ids or data attributes
* Fix : featured pages not translated by wpml. Fixes #1205
* Fix : don't update the defaults when wp_installing()
* Fix : preview error on singulars. Fixes #1194
* Fix : typo in customizer controls
* Fix : slider caption elements, default Fittext minsizes too high. Fixes #1191
* Fix : link whole slide not including the caption. Fixes #1140
* Fix : colors of the search form in dark overlay. Fixes #1185
* Fix : closing slide's title h1 html tag. Fixes #1188
* Fix : hamburger too dark on hover. fixes #1200
* Fix : hamburger lines taking a 2px height randomly
* Fix : logo / title stays shrinked when slowly scrolling up. fixes #1199. fixes #1192
* Imp : slider bullets closer to the bottom and margin set in em
* Imp : improve ol/ul margins in .tc-content-inner (.entry-content). Fixes #1183. Also slightly improve the cite element style.
* Imp : set shrinked logo height with max-height instead of height => to inherit the animation
* Imp : snaked submenu caret moved and rotated on the relevant side when "snaking"
* Updated : footer credit links to customizr theme page instead of presscustomizr home page
* Added : header custom back/fore-ground color options in modern style

= 4.0.8 September 17th 2017 =
* Fix: handle user's singular featured image height in singular. Fixes #1166.
* Fix: correct horizontal positioning of the primary navbar menu. Fixes #1175.
* Fix: fix slider textual fields wrong truncation. Fixes #1168.
* Imp : Gallery img sizes. Fixes #1165.
* Imp : improved customizer js code
* Updated : about admin page

= 4.0.7 September 7th 2017 =
* Fix: menu centered wrongly displayed in ie/edge. Fixes #1163
* Fix: Menu centered in desktop => scrolling up and down close to top is not well handled. Fixes #1161
* Fix: RTL : search icon not properly left aligned on full screen search. Fixes #1159
* Imp: Submenus items on mouse hover - reveal faster. Fixes #1154

= 4.0.6 August 31st 2017 =
* Fix: add menu btn was not shown when secondary menu associated but no sidenav shown. fixes #1125
* Fix: fix singular thumbnail vertical spacing. fixes #1127
* Fix: fix tagline cut off. fixes #1128
* Fix: fp imgs always centered, fix handling slider not js centered
* Fix: js-centering class to the classical grid figure to better target them in js
* Fix: fix author meta displaying nicename instead of displayname. fixes #1148
* Imp: allow search full page close on escape key pressed
* Imp: implement new form style. fixes #1122
* Imp: add entry-media__holder class to the grid figure (homogeneity)

= 4.0.5 August 2nd 2017 =
* Fix: fix woocommerce generatinc php notice. fixes #1120
* Fix: fix CSS conflict with ui-datepicker-calendar. fixes #1123
* Fix: sticky logo option was not displayed in the customizer. fixes #1119
* Fix: display notice for socials in header in the right context. fixes #1118

= 4.0.4 July 26th 2017 =
* Fix: fix fp noy showing up in old php versions
* Fix: Fix slider loader gif path can be parsed by Google bots
* Fix: Potential submenu viewport overflow in firefox when fading fixes #1083
* Fix: fix missing max-width style for logo w forced dims fixes #1101
* Fix: same indentation for ul and ol
* Fix: fix sidebars list widgets indetation + various rtl fixes
* Fix: CSS handling of screen reader text fixes #1103 bullet 3
* Imp: code improvements related to the post lists layout dependency
* Imp: add menu button if not menu visible in the header
* Imp: allow loading magnific-popup js in footer and minified
* Add: add magnific popup js minified version
* Add: an option to make the dark overlay optional in the modern style slider

= 4.0.3 July 24th 2017 =
* Fix: fix grid 1 column max height fixes #1088
* Fix: submenus not sensible to the hover while fading + correctly handle the header z-index (user option)

= 4.0.2 July 23rd 2017 =
* fixed : child theme stylesheet wrongly enqueued
* Imp: main content mobile blocks reorder via flexbox
* Imp: fix comments date alignment fixes #1073

= 4.0.1 July 23rd 2017 =
* Fix : WooCommerce compatibility : Grid title truncation might affect products in product archives ( fixes #996 )
* Fix : Added back the menu locations customizer section
* Fix : grid caption background issue on mouse hover
* Fix : fix non existing function as __ID filter callback
* Added : style option in the customizer

= 3.5.18 June 20th 2017 =
* Fix: in singulars, no full width featured image if slider on fixes #988
* Fix: typo producing Class 'CZR__' not found in classical retro compat

= 3.5.17 June 18th 2017 =
* Fix: fix access to undefined tc_rectangular_size class property fixes #971
* Fix: typo producing Class 'CZR__' not found in classical retro compat fixes #972
* Imp: exclude helpblock elements from allowed dropcap elements

= 3.5.16 June 17th 2017 =
* Imp: improve deploying process to avoid headers already sent issues
* Fix: missing front js custom events: tc-resize, partialRefresh.czr fixes #961

= 3.5.15 June 14th 2017 =
* Fix: single slide sliders must not be draggable fixes #941
* Fix: fix menu center resulting aligned to the left on IE fixes #944
* Fix: fix position of structural hook __before_main_container
* Fix: fix fpc-container alignment when in #content
* Fix: by default the loop model should not register the loop item model if 
* Fix: remove old theme favicon control - is handled in js fixes #954
* Fix: remove new lines at the end of czr_ classes fixes #957
* Imp: improve classical grid CSS
* Imp: fp and grid images always js centered
* Add: add related posts options for single post

= 3.5.14 June 9th 2017 =
* fix: use of the add_editor_style wp function : needs relative paths fixes #926
* fix: php 5.2.* when trying to access to a static property of a class which is actually a variable. fixes #928
* fix: menu style for users started after 3.4.0 is 'aside'
* fix: fp in static front page not displaying first attachment as thumb
* improved : add rtl class to the inline font style in the wp editor

= 3.5.13 June 7th 2017 =
* improved : update select2 plugin to version 4.0.3 the same we use in the customizer
* improved : improve customizr post meta boxes. Do not add in post types which are not visibile in front. Fire actions when meta boxes are added so that we can enqueue related resources (js/css) only when needed by checking on did_action('relevant_action_hook')
* added : php, css and js code for modern style design

= 3.5.12 May 5th 2017 =
* Fix: use wp_sprintf to avoid Warning sprintf(): Too few arguments. Fixes #875
* Fix: fix header layout center - menu position deps in the customizer. Fixes #879
* Fix: don't display the single post featured image if a slider is displayed

= 3.5.11 April 7th 2017 =
* Fix: fix logo centered - menu centered not working non front ( fixes #868 )
* Imp: add theme support for wc gallery zoom lightbox and slider ( fixes #871
see: https://woocommerce.wordpress.com/2017/02/28/adding-support-for-woocommerce-2-7s-new-gallery-feature-to-your-theme/ )

= 3.5.10 March 31st 2017 =
* Imp : allow menu centering when logo/title centered #861
* Imp : added a utility czr_fn_is_front_help_enabled() in init.php

= 3.5.9 March 17th 2017 =
* fix : prevdem mode should be turned off when user starts customizing
* fix : user defined WP core settings like show_on_front should be preserved if customizing in prevdem mode
* fix: slider of posts revert - show post attachment if no featured image fixes #850
* fix : bugs on partialRefresh.czr. some placement have no containers
* fix: allow using first attachment as thumb for slider of posts in pages
* Fix: prevent colophon inner container enlarging page in certain vps see #853
* improved czr_fn_get_raw_option : added an option to retrieve the not cached option value from database. Typically used when getting the template name. Should fix #844
* added match Media polyfill.
* Updated the parallax jquery plugin => added a matchMedia option set to 'only screen and (max-width: 768px)' by default
* imp : allow child theme user to override the slide-placeholder image
* updated it_IT translation fix #842
* Imp: display thumbnail in single pages like we do in single posts fixes #738
* added : theme support for selective refresh for widgets

= 3.5.8 March 11th 2017 =
* Fix: amend img_size param passed the wrong way to the posts slider builder fixes #840
* Fix : removed li+li list style with 0.25em top margin

= 3.5.7 March 8th 2017 =
* improved : li+li list style with 0.25em top margin
* fix : default slider should show the demo slides when previewing

= 3.5.6 March 8th 2017 =
* Fix: fix preview demo content should fix #785
* Fix: fix wrong text domain in customizer files fixes #832
* Fix: fix passing a non array to implode in the headings rendering fixes #830
* Fix: fix small comment bubble borders issue introduced fixes #833
* Fix: serverControlParams.translatedStrings by serverControlParams.i18n
* Imp: improve slider loader visibility handling. Add javascript detection inline script in wp_head (ref. twentyseventeen and below)
* Imp: init parallax slider in js files
* Imp: WordPress theme guidelines, moved all hardcoded scripts in enqueued js file
* Imp: in the customizer, display the front page layout control in the post layout section
* Imp: posts slider, prevent get_posts if asking for n<1 posts
* Changed : moved translated languages on polyglots : pt_BR, uk, tr_TR to lang_pro
* Changed : the default slider is now the posts slider
* Added : a default thumbnail for posts slides with no thumbnails

= 3.5.5 February 26th 2017 =
* Fix: do not show first attachment as thumb in single context fixes #815
* Fix: fix reference to maybe undefined wp in front js fixes #820
* Fix: fix comment bubble bottom arrow when custom color selected fixes #822
* Imp : added aria-label to button - support accessibility
* Imp : enabled accessibility for social icons
* Imp: move theme Custom CSS option to WordPress embedded one fixes #818

= 3.5.4 February 18th 2017 =
* Fix: customizer javascript error when customizing the social links
* Improved: customizer social links module user interface

= 3.5.3 February 17th 2017 =
* Fix: fix potential option inconsistencies with some hosts. fixes #810
* Imp: rtl - fix update notice positioning in admin. fixes #800
* Imp: improve bootstrap plugin compatibility with the slider. fixes #737
* Removed : Swedish translations sv_SE, now part of the translation pack automatically downloaded from wordpress.org.
* Imp : add Array.from js polyfill for customizer control js
* Fix : IE11 (at least) : potential customizer breaks when trying log in the console

= 3.5.2 February 8th 2017 =
* Fix: second menu responsive actions on header partial refresh
* Fix: fix rtl carousel-caption positioning and text alignment. fixes #797
* Fix: fix btn-toggle-nav position on sticky-enabled when tagline-off #799
* Imp: fix btn-toggle-nav positioning when sticky enabled and no socials
* Imp: improve overall plugins compatibility : do not render the comments template more than once should fix #774
* Imp: customizer - fonts/skins select on focus instead of on click
* Imp: customizer - improve slider control dependencies some controls should not be displayed when demo slider is selected
* Imp: try to avoid double social icons see #787
* Imp: compliancy with WP theme guidelines : one customizr pro link in the customizer, a simpler screenshot, no donate button in the customizer

= 3.5.1 February 1st 2017 =
* fixed : compatibility issue with PHP<5.5
* fixed: customizer header partial refresh not correctly working

= 3.5.0 January 31st 2017 =
* fixed : Trying to get property of non-object php notice when setting up WooCommerce
* Imp : introduced Poppins as the new default Google font
* Imp : changed default body line-height in pixelds to 1.6em
* Imp: new screenshot
* Imp: implemented an enhanced social links module in the customizer
* Imp: improved compatibility with Woothemes Sensei plugin fixes #759
* Imp: use lower tc-page-wrap and tc-sn z-index for compatibility reasons fixes #762
* Imp: button toggle nav positioning improvments
* Imp : added a notice for for freshly created menu not yet visible in the header main location
* Imp: improve side menu positioning depending on the header layout

= 3.4.38 January 21st 2017 =
* Fix: fix default page menu behavior when dropdown on click submenu open #730
* Imp: fix plugin php7 checker (wrong) compatibility issue #727 , #719
* Imp: improve WooCommerce compatibility + allow shop layout selection #733
* Fix: small tweak to the header cart WooCommerce CSS #733
* Fix: add tc-center-images body class only when tc_center_img option true #735
* Fix: fix superfluous bracket in font-awesome icons inline style #739
* Fix: escape title attributes used in fp round-div and readmore button #743
* Imp: various bootstrap>2.3.2 compatibility improvements fix #742 #737 #746
* Imp: img to smartload must have an src which matches an allowed ext #747

= 3.4.37 January 5th 2017 =
* fixed : correctly handle sizes attribute when smartloading resp imgs
* fixed : back to top arrow position option
* fixed : prevent paging info duplication in wc breadcrumb
* fixed : avoid unbreakable woocommerce product labels ( #713 )
* improved : encode pipes when requesting multiple gfont families
* improved : avoid img smartload php parsing in ajax requests
* improved : rightly handle sizes/data-sizes attribute replacement in php
* improved : use modern window.matchMedia do determine the viewport's width ( #711 )
* improved : removed language packs already translated on translate.wordpress.org. German (Formal) (de_DE_formal), English (Canada) (en_CA), English (UK) (en_GB), Finnish (fi), French (Belgium) (fr_BE), French (France) (fr_FR), French (Canada) (fr_CA), Hebrew (he_IL), Italian (it_IT), Norwegian (Bokmål) (nb_NO), Polish (pl_PL), Romanian (ro_RO), Russian (ru_RU)

= 3.4.36 December 6th 2016 =
* improved : compatibility with WP 4.7
* added : minor UI change in the customizer, new home button added

= 3.4.35 November 17th 2016 =
* fixed : display dropdown submenu caret only for relevant '.nav' elements
* improved : update FontAwesome to v4.7.0 + add Snapchat social link

= 3.4.34 October 28th 2016 =
* fixed : compatibility issue with php7
* fixed : better check for is customize preview()
* fixed : correctly instantiate front classes in admin building slider of posts fixes #662
* improved : add requestAnimationFrame polyfill required by several jquery-plugins fixes #665
* improved : get rid of the outdated menu item first letter styling
* improved : Imp: add back langpacks completed on translate.wordpress.org. Needed for Customizr-Pro. note: german GTE considered our german translation as formal german. The current de_DE.po(mo) is just a copy of the de_DE_formal.po(mo) pack.

= 3.4.33 October 17th 2016 =
* fixed : compatibility problems with plugins using the WP tinyMCE editor. Example : Black studio tinyMCE

= 3.4.32 October 17th 2016 =
* fixed : added back function tc__f() for retro-compatibility
* updated : grey.css is now the default skin, color #5A5A5A. No impact for previous version users using the old default skin (blue3.css)

= 3.4.31 October 16th 2016 =
* fixed : wrong class name in czr-init.php
* fixed : footer widgets not displayed

= 3.4.30 October 15th 2016 =
* added : in single post, new wp filter : apply_filters( 'tc_single_post_section_class', array( 'entry-content' )
* added : in single post, new wp action : do_action( '__after_single_entry_inner' )
* added : demo singleton class CZR_prevdem
* added : demo images in inc/assets/img/demo
* Imp: group files in 4 main files to load in inc/
* Imp: add fp and round-div comp code with plugins running bootsrap3+ fixes #640
* Fix: fix js issue for pages with no header (dropdown placement)fixes #643
* Imp: replace class prefixes from TC to CZR
* Imp: change function names prefix from tc to czr
* Imp: add parallax classes and js only when slider exists
* Removed : language packs translated on wp.org

= 3.4.23 September 14th 2016 =
* added : a parallax scrolling option for sliders. Enabled by default.
* added : the waypoint js library (v4.0.0)
* changed : slide loader icon is enabled by default
* fixed : center the rectangular thumbs with golden ration only in post lists, not in single posts

= 3.4.22 August 23rd 2016 =
* Fix: tinymce custom style conflicts (detected with the ACF WP plugin ) with tinymce 4.x api. Fixes #626
* Fix : customizer terms array (categories/tags pickers options) not being updated on term delettion. fixes #620
* Improved : use WooCommerce breadcrumb code in wc contexts
* Improved : remove it lang files - will use the ones available on wp.org
* Added : new option - customize back to top - arrow position left or right

= 3.4.21 May 6th 2016 =
* Fix: remove grunt live reload script fixes #611
* Fix: border-collapse specify 'separate' instead of 'initial': Opera fix should work with IE too, needs further tests
* Fix: fix wp media insert in front fixes #605
* Fix: fix woocommerce variation not visible on a product page fixes #601
* Fix : rename Finnish translations to match Wordpress core
* Fix: fix potential issue with the dropdown limit to viewport and some plugins (maybe) fixes #593
* Imp: be sure drodpown are displayed when overflowing the header/navbar fixes #608
* Imp: allow wc-cart in the header ajax update

= 3.4.20 March 14th 2016 =
* Update: translations ru_RU and id_ID
* Fix: calendar widget style in footer with multiple widget instances
* Fix: disable smoothscroll touchpad support by default
* Imp: move the woocommerce header options, option do display the shopping cart on scroll
* Imp: wp 4.5 compatibility better rendering of the rating block in the customizer
* Fix: do not display menu-button when mobile+scrolling+sticky+not_allowed fixes #571

= 3.4.19 February 16th 2016 =
* Add : 3 new social icons: VKontakte, Yelp, Xing
* Imp: woocommerce icon cart now rendered with font-awesome
* Imp: remove outdated old ie fixes in the head
* Imp: move front and back icons to Font Awesome set
* Add: add customizer setting to optionally load font-awesome resources
* Imp: logo - replace previous upload control in the customizer with cropped images control
* Updated: translation fr_CA
* Fix: slider - avoid caption increasing slides height
* Fix: amend missing comma in the previous commit
* Fix: better rendering of the social-block in sidebar and colophon when they take up more than one line
* Fix: amend typo in the new control css
* Fix: refine compatibility with old customizr versions
* Fix: make icons in singular post / page contexts skin based
* Fix: never display edit links in the customizer fixes #361
* Fix: avoid outline showing up on links click in ff (v44) fixes #538
* Fix: fix potential warning when using custom skins fixes #540 

= 3.4.18 January 30th 2016 =
* Updated Italian translation plus a typo
* Add: new option - display woocommerce cart in the header when sticky fixes #499
* Fix: fix grid not considering custom max height in left sidebar layout
* Fix: fix smartloaded img not correctly displayed in some browsers fixes #534
* Fix: fix slider link with QTranslate X in pre-path mode thanks to @justinbb fixes #531
* Fix: fix broken link to the header's navigation doc
* Fix: fix broken links to theme's faq
* Fix: fix broken links to the slider docs
* Fix: fix broken links to the docs in class-fire-utils
* Fix: fix grid expanded post edit link not reachable fixes #286
* Fix: refine alignment when tagline not shown

= 3.4.17 January 23rd 2016 =
* Add: a few translation tr_TR thanks to @ghost
* Add : Indonesian translation. Thanks to Rio Bermano
* Fix : some Swedish translation strings. Thanks to Mia Oberg.
* Fix: fix post-metas hierarchical tax check when building button class
* Fix: prefer mysqli api to the mysql ones (deprecated) in sys-info fixes #508
* Fix: amend wrong documentation link in sidebar widget placeholder fixes #502
* Fix: fix jetpack's photon - theme smartload compatibility issue 
* Fix: fix btt-arrow and scroll-down issue Also use more descriptive variable names. fixes #477
* Fix: fix disabling wc-header-cart to reset tc_user_options_style
* Fix: avoid smartload conflict with buddypress setting avatar img fixes #467 a)
* Fix: better html comments fix rare cases when some html comments were considered as server's directives. fixes #470
* Fix: skip URIS images among imgs to smartload fixes #463
* Fix: smarload preg callback - reverse strpos param order
* Fix: apply border bottom only to theme sidebars widget list item

= 3.4.16 December 10th 2015 =
* Added: WooCommerce cart in the header
* Added: Turkish (tr_TR) translation files
* Added: Front js - new js class to better place dropdowns submenus avoiding overflowing the window
* Added: Images compatibility with wp 4.4+. Added customizer options to disable the default responsive images behaviour for slider and thumbnails.
* Fix: allow smartload in wp 4.4 (responsive images)
* Fix: alternate layout is available only if thumb position is right/left. Also fixes the alternate layout thumbnail's height control visibility
* Fix: do not display colophon's back to top text when back to top arrow button is enabled
* Fix: rtl colophon's layout fixes
* Fix: remove unneeded css code for the secondary menu dropdown top arrow
* Fix: allow smartload when jetpack's sharing enabled
* Fix: fix some issues regarding to smartload and centering imgs
* Fix: show second-menu dropdown-submenu arrow anyways
* Fix: WPML customizr option name
* Fix: allow logo transition only when both w,h are available.
* Fix: fix padding for the first menu-item in responsive menu
* Fix: remove unsupported turkish translation files
* Fix: Front-js - new js event triggered for sticky header and sidenav
* Fix: limit dropdown top arrow adjustment to the tc-wc-menu
* Fix: some CSS fixes for parent dropdown submenus in secondary menu
* Fix: allow slides translations in post/pages
* Updated : Dutch translation. Thanks to Helena Handa
* Updated: Brazilian Portuguese translation
* Updated: japanese translation
* Updated Farsi translation
* Improved : disable all front end notices when customizing

= 3.4.15 November 14th 2015 =
* Fix: wrong variable declaration in the main front js file
* Updated: Norwegian translation. Thanks to Marvin Wiik.
* Fix: better way to check if the search query has results
* Fix: the-events-calendar: fix showing the content twice in single event pages fixes #396
* Fix: display header in multisite signup/activate pages
* updated Brazilan Portuguese. Thanks to Helena Handa.
* Fix: fix column layouts in Help and About admin pages
* Add: WPML compatibility
* Improved: disable the front end notices when customizing

= 3.4.14 November 4th 2015 =
* Add: Added a dismissable help notice on front-end in post lists about using img smart-load
* Add : add Powered by Wordpress in footer credits
* updated : German translation, thanks to Martin Bangermann
* Fix: menu display element, cast element classes to array fixes #372
* Fix: Deep link to customizer menu panel in a control description fixes #244
* Fix: Deep link to the featured page control in the removable front end block fixes #246
* Fix: better handling of the simple-load event triggered on holders fixes #377

= 3.4.13 October 16th 2015 =
* fix : better support for php versions < 5.4.0

= 3.4.12 October 16th 2015 =
* added : performance help notice on front-end for posts/pages showing more than 2 images
* updated : Italian Translation thanks to Giorgio Riccardi http://www.giorgioriccardi.com/
* fix: better support for Visual Composer, prevent conflicts with anchor links in visual composer elements 
* fix: better support for The Events Calendar, events list view fixes #353
* fix: better support for JetPack's photon, load imgs from cdn
updated Polish translation. Thanks to Krzysztof Busłowicz
* fix : better retro compatibility for the customizer preview for WP version under 4.1
* fix: Select a submenu expansion option disappears #340
* fix: allow control deeplink in the customizer
* fix: limit previous fix to ie9 and below
* fix: slider-controls always visible in ie9- In such browsers the opacity+transition doesn't really work fine, let's make them always visible

= 3.4.11 September 30th 2015 =
* added: new social link - email

= 3.4.10 September 29th 2015 =
* added: new option, filter home/blog posts by categories
* added : info box at the bottom of the slides table on how to add another slide
* added : in customizer > Front Page > Slider, make some "sub" slider height options dependant on the actual user define slider height
* updated: translation he_IL.po
* fix: let sticky-footer detect the golden-ratio is applied to the grid
* fix: exclude links wrapped in an underlined span from the eligible externals
* fix: sticky-header: logo re-size on scrolling when needed fixes #314
* fix: avoid navbar-wrapper overlapping title/logo in mobiles when no site-description is shown for ww<767px

= 3.4.9 September 20th 2015 =
* added: New feature - display a slider of recent posts on home
* fix: RTL initial position of the small arrow in the accordion(JTS)
* fix: broken update notice in edit attachment page fixes issue #248
* fix: display slider notice only on the demo slider fixes issue #251
* fix: Add back the Google Font img in the Customizer fixes issue #285
* fix: Woocommerce's product tabs not showing if Smooth scroll on click enabled Fixes issue #258 
* fix: Allow the expanded grid title to be translated with qtranslate 
* fix: include pages in search results when including cpt in post lists Fixes issue #280
* fix: expand last published sticky post in the grid
* fix: disable link smoothscroll in woocommerce contexts See issue #258

= 3.4.8 August 24th 2015 =
* fix : issue #242 https://github.com/Nikeo/customizr/issues/242 : Effects common to regular menu and second horizontal menu where not visible when the regular menu was selected
* fix: qTranslate-X compat code improved (issue : https://wordpress.org/support/topic/featured-pages-and-qtranslate?replies=4 )

= 3.4.7 August 23rd 2015 =
* update : translations files
* fix : replace Customizr favicon by the WP 4.3 site icon. Handle the transition on front end and in the customizer for users already using the Customizr favicon. Retro compat : not applied if WP version < 4.3 + Check if function_exists('has_site_icon')
* fix : for control visibility bug since wp4.3
* add : a boolean filter to control the colophon back to top link
* fix : properly remove box around navbar when no menu is set Bug reported here: https://wordpress.org/support/topic/dispay-menu-in-a-box?replies=6
* fix : do not update the post meta as soon as you enter the add post page

= 3.4.6 August 4th 2015 =
* fixed : polylang compat code according to the new customizer settings
* fixed : use original sizes (full) for logo and favicon attachments

= 3.4.5 July 31st 2015 =
* fixed : various css issues for the vertical menu items

= 3.4.3 July 31st 2015 =
* fixed : minor css adjustements for the menus
* added : a dismissable help notice under the main regular menu, on front-end, for logged-in admin users (edit options cap)

= 3.4.2 July 30th 2015 =
* fixed : expand on click not working for the secondary menu.

= 3.4.1 July 30th 2015 =
* fix : a missing text domain for a translation string

= 3.4.0 July 28th 2015 =
* added : new features for sliders : use a custom link, possibility to link the entire slide and to open the page in a new tab
* added : new default sidenav menu
* added : new optional secondary menu
* added : new default page menu
* added : new feature smoothscroll option in customize > Global Settings
* added : new feature Sticky Footer in customize > Footer
* added : a "sidebars" panel in the customizer including a social links section. (moved from global settings > Social links). Header and Footer social links checkboxes have been also moved into their respective panels.
* added : a theme updated notice that can be dismissed. Automatically removed after 5 prints.
* added : various optional front end help notices and placeholder blocks for first time users.
* fix : avoid blocks reordering when they contain at least one iframe (avoid some reported plugin conflicts)
* fix : video post format, show full content in alternate layout
* fix : display slider-loading-gif only if js enabled
* fix : display a separator after the heading in the page for posts (when not home)
* fix : html5shiv is loaded only for ie9-
* fix : dynamic sidebar reordering of the sidebar was not triggered since latest front js framework implementation improved : used of the tc-resize event for all resize related actions added : secondary menu items re-location for responsivereplaced : (js) 'resize' event by the custom 'tc-resize'
* fix : anchors smooth scroll - exclude ultimate members anchor links
* changed : customize transport of the header layout setting is now 'refresh'
* improved : modernizr upgraded to the latest version
* improved : customizer preview is refreshed faster

= 3.3.28 June 25th 2015 =
* fix : re-introduce btt-arrow handling in new front js
* fix : fix external link on multiple occurrences and exclude parents

= 3.3.27 June 19th 2015 =
* fix : drop cap not working with composed words including the '-' character
* fix: allow img smartload in mobiles
* fix: new emoji core script collision with svg tags => falls back to classic smileys if <svg> are loaded on the pages (by holder.js)
* fix: do not add no-effect class to round-divs when center images on
* fix: prevent hiding of selecter dropdown
* fix: use original img sizes
* fix: some ie8 fixes for the new front-js
* fix : reset margin for sticky header was not using the right variable
* fix : close tc-page-wrapper before wp_footer() to avoid issues with wp admin bar
* fix: when unhooking tc_parse_imgs for nextgen compatibility use proper priority
* fix: better rtl slide controls and swiping(js)
* changed : replace load function by loadCzr() => load might be a reserved word
* updated Hebrew translion for V 3.3.26
* updated translations for v 3.3.26
* updated Hebrew translations f v3.3.26
* added : split main front js into parts
* added : js czrapp extendable object
* added : sticky header as a sub class of Czr_Base
* added : js event handlers for sidebar reordering actions
* added : cleaner class inheritance framework for front end js
* added : a div#tc-page-wrap including header, content and footer
* added : oldBrowserCompat.js file including map + object.create
* added : filter method to the Array.prototype for old browsers
* added : a simple event manager set of methods in the front czrapp js

= 3.3.26 May 22nd 2015 =
* fix : post-navigation regression introduced while merging rtl code

= 3.3.25 May 21st 2015  | Customizr is 2 y/o ! =
* fix: better check whether print or not the widget placeholder script
* added : option filter and better contx retro compat to default
* updated : Swedish translation sv_SE.po

= 3.3.24 May 15th 2015 =
* fix: store empty() function bool in a var to fix a PHP version compatibility issue
* fix: use proper priority for tc_parse_imgs callback of the_content filter
* fix: when deleting retina images don't forget the original attachment's retina version
* fix: remove btt-arrow inline style, rule moved in the skin css
* fix : fancybox in post images is 100% independant of fancybox in galleries
* added : japanese translation (ja). Thanks to Toshiyuki Tsuchiya.
* fix : remove smartload noscript tag
* fix : theme switcher visibility issue on preview frame ready event
* fix: properly filter get_the_content() for special post formats
* fix : localized params assigned to wrong script handle in dev mode
* fix : hide donate button ajax action not triggered
* fix : change order of elements on RTL sites. using is_rtl() to determine the order of specific elements, instead of creating dedicated rules in CSSFIX : correcting the left/right css rules for RTL sited. Thanks to Yaacov Glezer.
* added : less files updated with new rtl vars and conditional statements
* improved : code handling RTL priority for colophon blocks
* updated : Spanish es_ES translation. Thanks to Angel Calzado.
* improved : donate customizer call to action visibility
* improved : widget placeholder code

= 3.3.23 May 4th 2015 =
* fix : don't show slider in home when no home slider is set

= 3.3.22 May 3rd 2015 =
* fix : revert private taxonomy not printed. Needs more tests.

= 3.3.21 April 29th 2015 =
* fix : no post thumbnail option was not working for the post grid layout
* added: support for the map method in the array prototype for old ie browsers -ie8
* Fix: use the correct post id when retrieving the grid layout
* Improved : jquery.fancybox.js loaded separately when required
* Updated : underscore to 1.8.3
* Added : helper methods to normalize the front scripts enqueuing args
* Updated : name of front enqueue scripts / style callbacks
* Fix: use amatic weight 400 instead of 700, workaround for missing question mark
* Fix: remove reference to the tag, use site-description tag
* Fix: display unknown archive types headings; use if/else statement when retrieving archive headings/classes immediatily return the archive class when asked for and achiFix: amend typo in the last commit
* Fix: disable fade hover links for first level menu items in ie
* Fix: add customize code and fix previous errors
* Fix: add gallery options, remove useless rewrite of gallery code
* Fix: scroll top when no dropdown menu sized to viewport and no back-to-top, don't refer to not existing variable
* Fix: consider both header borders and eventual margins when retrieving its height
* Fix : RTL-ing Pre-Phase : setting the correct direction of arrows
* Fix: disabling global tc_post_metas didn't hide metas
* Fix: cache and use cached common jquery elements
* Fix: don't print private taxonomies in post metas tags
* Fix: display other grid options and jumb to the blog design options in customize
* Add: sensei woothemes addon compatibility
* improved : single options can now be filtered individually with tc_opt_{$option_name}
* Add: optimizepress compatibility
* Add: basic buddypress support (don't show comments in buddypress pages)
* Add: partial nextgen gallery compatibility
* Add: tc-mainwrappers methods for plugin compatibilities
* Updated: class-content-post_navigation.php
* changed: method TC___::tc_unset_core_classes set to public
* Correcting arrows on tranlated phrases

= 3.3.20 April 17th 2015 =
* Fix: in the customizer display other grid options

= 3.3.19 April 13th 2015 =
* fixed : Black Studio TinyMCE Plugin issue. Load TC_resource class when tinymce_css callback is fired from the customizer

= 3.3.18 April 11th 2015 =
* added : support for polylang and qtranslate-x
* improved : load only necessary classes depending on the context : admin / front / customize
* changed : class-admin-customize.php and class-admin-meta_boxes.php now loaded from init.php.
* updated site name

= 3.3.17 April 10th 2015 =
* fix: reset navbar-inner padding-right when logo centered
* fix: override bootstrap thumbnails left margin for woocommerce ones
* added : helpers tc_is_plugin_active to avoid the inclusion of wp-admin/includes/plugin.php on front end
* added : new class file dedicated to plugin compatibility class-fire-plugin_compat.php
* updated : copyright dates

= 3.3.16 April 9th 2015 =
* fixed : minor hotcrumble css margin bug fix
* fixed : use the css class instead of h2 and remove duplicate
* fixed : Few corrections in the Italian translation
* fixed : allow customizr slider in the woocommerce shop page
* fixed : collapsed customizer panel
* fixed : gallery - handle the case "link to none" (was previously linked to the attachment page)
* added : sidebar and footer widgets removable placeholders
* added : better customizer options for comments. Allow more controls on comment display. Page comments are disabled by default.
* added : customizer link to ratings
* updated : he_IL.po Hebrew translation

= 3.3.15 March 30th 2015 =
* updated : readme changelog

= 3.3.14 March 30th 2015 =
* fixed : rtl customizer new widths and margins
* fixed : use '===' to compare with '0'.
* fixed : fix logo ratio, apply only when no sticky-logo set
* fixed : avoid plugin's conflicts with the centering slides feature: replace the #customizr-slider's 'slide' class with 'customizr-slide'
* fixed : user defined comments setting for a single page in quick edit mode 
* fixed : pre_get_posts as action instead of filter
* fixed : hook post-metas and headings early actions to wp_head instead of wp
* fixed : minor css issues due to the larger width for the customizer controls
* fixed : infinite loop issue with woocommerce compatibility function
* added : tc-smart-loaded class for img loaded with smartloadjs
* added : make grid font-size also dependant of the current layout
* added : css classes filter in index : tc_article_container_class
* added : grid customizer in pro
* added : skin css class to body
* added : disabled WooCommerce default breadcrumb
* improved : better css grid icons
* changed : themesandco to presscustomizr
* updated : Swedish translation. Thanks to Tommy Wikström.
* updated : genericons to v3.3
* changed : attachment in search results is now disabled by default
* updated : layout css class added to body
* changed : .tc-gc class is now attached to the .article-container element
* changed : golden ratio can be overriden (follows the previous commit about this)
* changed : tc__f ( '__ID' ) replaced by TC_utils::tc_id()
* changed : tc__f( '__screen_layout' ) replaced by TC_utils::tc_get_layout( )
* changed : css classes filter 'tc_main_wrapper_classes' and 'tc_column_content_wrapper_classes' now handled as array
* improved : grid thumb golden ratio can be overriden
* updated : disable live icon rendering in post list titles if grid customizer on
* improved : customizer control panel width
* changed : grid controls priorities
* changed : class .tc-grid-excerpt-content to .tc-g-cont
* improved : larger customizer zone + some titles styling
* improved : get the theme name from TC___::$theme_name in system infos
* changed : split the edit link callback. Separate the view and the boolean check into 2 new public methods
* changed : some priority changes in the customizer controls
* improved : grid font sizes now uses ratios

= 3.3.13 March 18th 2015 =
* fixed : potential 'division by zero' issue with the grid layout if users applies a custom layout Initially reported here : https://wordpress.org/support/topic/division-by-zero-5
* added : customizer previewer filter for custom skins
* improved : various js code improvement for scrolling actions
* improved : allow user to use their custom date format (defined in settings > general) for post metas date
* improved : menu caret alignment
* added : tc_carousel_inner_classes with .center-slides-enabled when option is checked by user
* fixed : tc_set_grid_hooks are fired in 'wp_head' => 'wp' was too early
* added : user can set a custom logo alt attr with the filter 'tc_logo_alt'
* fixed : missing global wp_query declaration
* updated : screenshot and demo slide#1
* added : user can specify a custom meta date format with filter 'tc_meta_date_format'
* changed : navbar not boxed by default anymore for users starting with v3.3.13
* improved : translated few strings in Italian thanks to https://github.com/giorgioriccardi
* fixed : CenterImages js avoid using ir8 'class' reserved words
* fixed : social icon unwanted underline text-decoration on hover
* added : a use_default boolean param to TC_utils::tc_opt()
* improved : slider php class better code structure
* improved : js smoothscroll disabled if #anchor element doesn't exist.
* added : new filter 'tc_title_text' for easier and safer pre processing before tc_the_title => avoid filter priority potential issues
* added : new option to set the max length of post titles (in # of words) in grid
* fixed : firefox and old browsers compatibility issue with the .tc-grid-fade_expt background
* added : cta button in customizer footer section
* improved : jqueryextLinks : if link not eligible, then remove any remaining icon element and return //important => the element to remove is right after the current link element ( => use of '+' CSS operator )
* added : Galician Spanish translation. Thanks to <a href="http://rubenas.com">Ruben</a>
* added : custom skins customizer preview hack
* added : custom skins grunt code

= 3.3.12 March 9th 2015 =
* fixed : smooth scroll new excluded selectors not properly set

= 3.3.11 March 9th 2015 =
* fix : tc_set_post_list_hooks hooked on wp_head. wp was too early => fixes bbpress compatibility
* improved : tc_user_options_style filter now declared in the classes constructor
* fix : bbpress issue with single user profiles not showing up ( initially reported here : https://wordpress.org/support/topic/bbpress-problems-with-versions-avove-3217?replies=7#post-6669693 )

= 3.3.10 March 9th 2015 =
* fixed : better insertion of font icons and custom css in the custom inline stylesheet
* fixed : bbpress conflict with the post grid

= 3.3.9 March 9th 2015 =
* fixed : the_content and the_excerpt WP filters missing in post list content model
* fixed : smart load issue when .hentry class is missing (in WooCommerce search results for example)
* added : has-thumb class to the grid > figure element
* added : make the expanded class optional with a filter : tc_grid_add_expanded_class
* added : fade background effect for the excerpt in the no-thumbs grid blocks
* fixes : adjustments for the grid customizer
* changed : TC_post_list_grid::tc_is_grid_enabled shifted from private to public
* improved : jqueryextLinks.js check if the tc-external element already exists before appending it
* fixed : .tc-grid-icon:before better centering

= 3.3.8 March 4th 2015 =
* Fix slider img centering bug

= 3.3.7 March 4th 2015 =
* Fix: array dereferencing fix for PHP<5.4.0
* Fix: typos in webkit transition/transform properties

= 3.3.6 March 2nd 2015 =
* fix a potential bug in the thumbnail types

= 3.3.5 February 28th 2015 =
* improved : trigger simple_load event (=> fire centering) on relevant img if smartload disabled

= 3.3.4 February 28th 2015 =
* fix : ignore tc_sliders option on contx check

= 3.3.3 February 28th 2015 =
* improved hover transition for expanded grid post
* added a note about the grid column forced for specific sidebar(s) layout
* fix minor bubble comment css issues
* added js custom smartload event to trigger image centerering

= 3.3.2 February 28th 2015 =
* added : TC___::tc_doing_customizer_ajax()
* added contx retro compat
* replaced TC_utils::tc_is_customizing() by TC___::tc_is_customizing()
* updated : tc__f( '__get_option' ) replaced by TC_utils::$inst->tc_opt() method TC_utils::tc_get_option() replaced by TC_utils::tc_opt()
added grid design option as a specific set to be revealed on click
* improved : featured pages thumbnails server side code
* improved : post and page thumbnails id are now stored in a post meta field
* Fix: amend conflicting actions when dropdowntoViewport false and link to an anchor clicked. Perform those actions just when clicking on the menu button (responsive modeFixed in drop cap : skip tags or selectors and parents of those
* improved : better structure for the code in thumbnails, post and post list
* added : new post metas design option in customizer
* improved : re-engineering of the post metas class with a cleaner model / view structure
* added : tc-post-list-context class to body when relevant
* Improved : cleaner post metas callback, adapted for the grid layout
* improved : display metas for post format with no titles (links for example)
* fixed : font icons genericons and entypo are now written in the wp admin editor iframe head
* Fix: hide/don't show social icons in footer when option unchecked
* Added : new comment bubbles
* changed tc_bubble_comment and tc_content_header_class now handled in TC_comment class
* updated : comment bubble callback moved in class-content-comments.php
* added : apply a dynamic js golden ratio for figure height / width on load + resize
* improved the _build_setId, fix the featured pages controls visibility in customizer
* improved : possible user defined tags, classes and ids to skip in js external links skip img tags and btn classes by default
* Fix quotes issue for web safe fonts inline css
* improved ext links options. Now handled with a separate jQuery plugin

= 3.3.1 February 16th 2015 =
* added : pro parameter to the version check
* updated : menu open on hover by default if user started after v3.1+
* improved : better sanitization cb for custom css https://make.wordpress.org/themes/2015/02/10/custom-css-boxes-Fix typos in website performance options description texts
* fix : typos in website performance options description texts

= 3.3.0 February 15th 2015 =
* Fix: dropcap skips also ul,ol tags
* Updated : dropcaps are disabled by default in pages and posts
* Added smart img load script
* Added new option "Website Performance" in a new section  Advanced Settings > Performances
* Added lang zh_TW. Thanks to https://github.com/pppdog
* Changed : tc_menu_item_style_first_letter set to false for new users
* Updated thai and hebrew lang
* Improved : increased headings line height for accessibility (font-size x 1.25)
* Fix infinite loop potential issue on resize
* Updated theme doc link
* Moved date functions in TC_utils and added : check on date format validity
* Fix: better check on when changing default slider's height
* Fix: set sticky offsets after resizing logo
* Fix: fp-button display nothing if empty
* Fix: logo image stretched when sticky header enabled, handled for both logos (normal and sticky) in js.
* Fix: tagline navbar-wrapper. Navbar h2 => use .site-description instead of H2 and split the CSS rule in two parts
* Fix: add clear:both to boostrap .nav-collapse fix the misplacing .nav-collapse when no socials are displayed in
* Fix: re-add comment near the closing brace for compatibility function
* Fix: new filter tc_the_title, apply filters to the_title in Customizr contexts strictly
* Fix: handle mobile menu display in tc_common (tc_custom)
* Fix: add body class sticky-disabled, with php, by default when sticky header selected
* Fix: tc_has_update() catch exceptions use bool false if no updates
* updated stylesheets
* Improved : skip tc_common.css when scan the skins folder
* Added : tc_common.css
* Fix: in post metas don't call tc_has_update() when not needed

= 3.2.17 January 27th 2015 =
* removed : console.log() in addDropCap script

= 3.2.16 January 27th 2015 =
* d1207b1 updated : list of selectors to skip when applying drop caps
* 6512345 changed : external links icon and new tab (_target = blank) are disabled by default
* 6d0f725 Fix drop cap feature : html tags are not striped out anymore. Instead, the dropcap plugin search the first
* h0145a45 add : front javascript underscore.js dependency ~+5.5kb
* 57a7a6f Fix: handle boolean types in default options
* bb355ec Imp: handle .tc-no-title-logo in css (instead of js), when sticky header is enabled,
* 95b3e6f Fix: force hiding tc-reset-margin-top when no sticky header (css way)

= 3.2.15 January 23rd 2015 =
* Fix: don't re-add edit link button for tribe-events events

= 3.2.13 January 22nd 2015 =
* c583b19 Fix warning when attempting to load Google font in the admin editor

= 3.2.12 January 22nd 2015 =
* cbe6780 Update : theme description

= 3.2.11 January 21st 2015 =
* 7df1282 update : translation files
* cbd0c0c New theme description
* 24e1ae3 add missing icon parameter to wp_get_attachment_image()
* 2719d65 Apply a drop cap to paragraphs including at least 50 words by default The min number of words can be cu
* 05fc1ae Fix: .tc-open-on click submenus typo
* 19336e0 New drop cap options for paragraphs in post / page content
* 04a56cd New option added in Global settings > Authors, to display the author's infos box after post content
* 9f43181 changed hook : 'tc_hide_front_page_title' to 'tc_display_customizr_headings' by default don't display t
* 4a57b7d sticky header : additional refresh on scroll top for some edge cases like big logos
* 483178d Merge branch 'eri-trabiccolo-tribe-events' into dev
* 5b6c8f8 Fix: check if wp_query var exists otherwise a notice will be displayed (plug-in not running) when WP_DE
* fafc579 Better customizer settings organization : Logo, favicon, site title and tagline have been moved to global settings
* 4afdbfc added a title for the global post lists settings
* 75efa6a post metas customizer control. The recent update notice after post titles is now a separate subsection
* 0416b16 Fix a rtl issue for the slider control arrows
* b106e02 Fix some rtl issues in the customizer
* 1caa268 Add The Event Calendar plugin compatibility : fixes an issue with the titles.
* 3ff11e2 style of the first letter menu items made optional
* 69ec886 fix the issue with the front js params when scripts are not concatenated
* 61f1439 missing '%' escape when sprintf tc-thumb inline style
* 086b923 handle the new css classes filter with implode()
* 2962a2d options for external links style and target is only applied to external links => url must be different
* d54f73c Fix issue with tc_post_list_thumb_height : check if exist in options
* f5111ca better customizer js part files structure
* dc8b0a1 post content links : add 2 new options activated by default, except for users already using a previous
* ed5c490 better front js file organization
* 75938ad Better way to check the user start version of the theme
* 4dda608 add user defined fonts to the admin editor
* 04d4505 Fix donate and cta in subpanel
* 994abc7 remove useless string var for "Recent update" in title
* b697085 Add a fallback value to tc_user_started_before_version
* 34d4bcb Fix: retrieve correct ID in posts page
* b584148 Fix: fix typos and missing matching visibility condition
* a57ec96 Fix tc_check_filetype: we want just the basename
* 9604be2 Fix logo print: don't use wrong attachment's height & width
* 5149c3d Add new filters for fp and footer widget areas
* 1c30809 Fix: dropdown menu on click
* b349192 Fix: make slides centering compatible with 'link the whole slide' snippet

= 3.2.10 December 23rd 2014 =
* f404eda Add a fallback false value to tc_user_started_before_version()
* 1577dfb Add Google fonts to the theme description
*   349ee57 Merge branch 'eri-trabiccolo-fix_tccta-in-subpanel' into dev
|\
| *   72d22a6 Merge branch 'fix_tccta-in-subpanel' of https://github.com/eri-trab| |\
|/ /
| * be4b067 Fix: 'hide' tc-cta when in sub-panel
* | 7414a5e Fix the double title bug add backward compatibility with wp_title()
|/

= 3.2.9 December 22nd 2014 =
* 4602677 add more single fonts check on _g_ prefix to determine if google font
* 76282b8 Customizer : Add Google logo in fonts title
* b1d9a66 add call to actions in the customizer
* 3e3d7d6 separate controls and call to action scripts in the customizer
* 9316c01 add underscore dependency to js control
* f0be9ee adapt admin pages if pro context
* 2d712e2 use of a timer instead of attaching handler directly to the window scroll event @uses TCParams.timerOnScrollAllBrowsers : boolean set to true by default
* b01a1fc translation updates
* 95785d7 check if php version supports DateTime ( must be >= 5.2 ) Bug initially reported here : https://wordpress.org/support/topic/fatal-error-call-to-undefined?repl* 90cee9a fix hard coded link issue
*   3a95a23 Merge branch 'eri-trabiccolo-multitier-menu' into dev
|\
| * 87ab91a remove the expand submenus for tablets in landscape mode ( selector : .tc-is-mobile .navbar .nav li > ul ul )
| * d326123 fix the issue for parent with depth > 0 for tablets in landscape mode In a context like : Parent0 > Parent1 > child, clicking on Parent0 now opens Parent1 C| * 4f2d376 added a $ prefix to jQuery vars
| |\
| | * 7df8265 Fix dropdown on click for multitier menus
* | | cd6eb04 fix post formats metas hook order issues add an "open" link to post after the metas of post formats with no headings when displayed in a list
* | | f5e7614 fix a notice with bbpress tags view : make sure that get_the_ID() is not false before adding the edit link in heading
|/ /
* | ffa1635 minor fixes on the new font picker feature
* |   b85d547 Merge branch 'googlefonts' into dev
|\ \
| * | 829fa5c google fonts enqueued on front end + generated custom CSS code
| * | 9ac6d21 remove the uselesss group parameter in the toStyle function
| * | 00437b5 change tc_get_font_lists to tc_get_font() with 2 parameters list / single and name or code
| * | 431dbcd added properties : font_selectors, font_pairs
| * | eaca195 add filters 'tc_gfont_pairs' and 'tc_wsfont_pairs'
| * | b1f1779 set the the default font depending on the user start theme version
| * | 3463729 Fix google font weight issues Reorder the font pairs
| * | ab8f4d9 property db_options is instanciated with the raw db option array new transient : started_using_customizr => used to stored the theme version number when t| * | 09de2fa added a static property to TC_init : tc_option_group (default is tc_theme_options)
| * | 618007d customizer font setup : admin control + preview php, js, css devs
| * | f21c58a the font pairs are localized to preview with TCPreviewParams.fontPairs
| * | 42cbe54 added font list as a TC_init property
| |/
* | cff5dd3 Fix $logos_img instanciation bug in class TC_header_main#148
|/
* f806c18 pages with comments : enable the comment bubble after the title in headings
* 57ac308 admin css : change help buttons and icon to the new set of colors : #27CDA5 #1B8D71
* 786bbbc expand submenus for tablets in landscape mode
* d4bc5eb add a tc-is-mobile class to the body tag if wp_is_mobile()
* d3bb703 Fix the skin dropdown not closing when clicking outside the dropdown
* 094e0b2 Changed author URL to http://presscustomizr.com/
*   c6611bb Merge branch 'eri-trabiccolo-android-menu' into dev
|\
| *   7b08e52 Merge branch 'android-menu' of https://github.com/eri-trabiccolo/customizr into eri-trabiccolo-android-menu
| |\
|/ /
| * d94fff6 Fix collapsed menu on android devices
* |   605a462 Merge branch 'eri-trabiccolo-fp-edit-link' into dev
|\ \
| * \   eb6e05c Merge branch 'fp-edit-link' of https://github.com/eri-trabiccolo/customizr into eri-trabiccolo-fp-edit-link
| |\ \
|/ / /
| * | 63c6aa0 Featured Pages: fix edit link
| |/
* |   8e24584 Merge branch 'eri-trabiccolo-parent-menu-item' into dev
|\ \
| * \   0205df3 Merge branch 'parent-menu-item' of https://github.com/eri-trabiccolo/customizr into eri-trabiccolo-parent-menu-item
| |\ \
|/ / /
| * | 0ccdc04 Fix: add href(=#) attribute to menu parent item which doesn't have it
* | |   708b7b1 Merge branch 'eri-trabiccolo-hammer-issue' into dev
|\ \ \
| * \ \   81aaa17 Merge branch 'hammer-issue' of https://github.com/eri-trabiccolo/customizr into eri-trabiccolo-hammer-issue
| |\ \ \
|/ / / /
| * | | 01bca14 Fix click on slide's call to action buttons in mobile devs
| | |/
| |/|
* | | bc5f118 minor changes to https://github.com/eri-trabiccolo dev on the sticky logo
* | | cb77df3 add the title and notice to TC_Customize_Upload_Control
* | |   7b31410 Merge branch 'eri-trabiccolo-dev' into dev
|\ \ \
| * \ \   30aeb72 Merge branch 'dev' of https://github.com/eri-trabiccolo/customizr into eri-trabiccolo-dev
| |\ \ \
|/ / / /
| * | | 937dc04 Add sticky logo option: a different logo when sticky header enabled
* | | | d0671a7 donate message strings are now translation ready and passed as js parameters
* | | | 4c1eebb add theme_name as a static property of TC___ check if theme is customizr-pro before instanciating init-pro.php
* | | | 8c6f3db fix footer customizer addon issues
* | | | 62bce18 add the title rendering for some control types
* | | | d3bc218 add the footer-customizer as an addon in init-pro
* | | | dd24119 remove the ftp_push skin task on prod build (too long and useless)
* | | | 07f04ab instanciates only for the following classes 'TC_activation_key' && 'TC_theme_check_updates'
* | | | fd70517 add TC_init_pro class in inc/init-pro.php. Not part of the free version
* | | | c997f65 setup the pro build in a parent folder
* | | | 11affc1 remove the patches/ folder on build
* | | | 94200f6 separate free and pro builds
* | | | 85ebbb6 require init-pro file in pro context
| |/ /
|/| |
* | | 4311c40 add a tc_is_customizing method and a pro conditional class instanciation
* | | 0c0e46a customizer controls : update jquery plugins path
* | | 48891bb fix the $default_title_length hard coded value
* | | 058993f icheck control : added the flat grey skin
* | | 70cbf61 French translation update
| |/
|/|
* | f1ebe3d fix the hide donate button not hiding. The tc_hide_donate method had been removed from TC_customize.
* | bc4526b updated cs_CZ, thanks to Martin Filák
* |   2cd88b2 Merge branch 'czech-lang-update' into dev
|\ \
| * | 849c53b updated cs_CZ translation
| * |   8704dd0 Merge branch 'master' of https://github.com/mejatysek/customizr into czech-lang-update
| |\ \
| | |/
| |/|
| | * 8547045 Updated czech translation Translated new strings added in newer template versions Corrected some bad translations
* | | be57dea updated lang files
|/ /
* |   b03f80f Merge branch 'fix-reordering-bug' into dev
|\ \
| * | f3e6700 fix the block reordering issue with Rocco's patch => cache the reordering status in a var and checking the status before doing anything
| |/
* | 08fc163 added a patches folder (ignored by git)
|/

= 3.2.8 November 24th 2014 =
* a4c6ad2 date_diff bug fix. TC_utils::tc_date_diff uses date_diff and falls back to new class TC_DateInterval if php version < 5.3.0

= 3.2.7 November 23rd 2014 =
* 936abcb Set update notice default to true and 10 days interval
* d242b79 Fix archive title bug. A unique dynamic filter is used now to render the heading html in single or archive contexts : "tc_headings_{$_heading_type}_html"

= 3.2.6 November 23rd 2014 =
* 936abcb Set update notice default to true and 10 days interval
* d242b79 Fix archive title bug. A unique dynamic filter is used now to render the heading html in single or archive
 a3cfea7 v3.2.6 built and tested
* 1247841 when live previewing skin, add a new link for the live stylesheet instead of replacing the actual skin link => avoid the flash of unstyle content during the skin load
* 88be803 Add style option for the update status notice next to the title
* 9353cb7 added filter to display metas and update notice based on post type 'tc_show_metas_for_post_types' 'tc_post_metas_update_notice_in_title'
* 805dc78 fix comment bubbles in all post types. Now check post type is in the eligible post type list : default = array('post'). The post list can be modified with a new filter hook named : 'tc_show_comment_bubbles_for_post_types
* bc3aca5 add a todo note in gruntfile.js
* b5e3e93 update exclude folders / files from build and .gitignore
* a0db200 updated number of lang to 24 (+1 with Korean)
* dd83aec tc_menu_resp_dropdown_limit_to_viewport option set to false by default
* a442b41 slider lead text not hidden anymore for small devices viewports
* 3e286fb slider controls revealed on hover with a fade effect. Uses jQuery hover() addClass
* e62cead add the template folder as localized param in preview context => used to build the dynamic skin urls
* ffdea2d updated korean lang (following pull request from to https://github.com/puyo061)
* 0afec6f korean lang merge
*   5fc6220 Merge branch 'add-korean-lang' into korean-merge-test
|\
| *   bad820f Merge pull request #14 from MMKP/master
| |\
| | * 156cd17 Korean(WIP)
| | * f4d6bdf Create ko_KR.po
| |/
* | 07f1a18 No inset shadow for the selected skin
* | 30256a0 added select2.min.js in theme-customizer-control.js concatenated script select2.min.css is loaded separetely => not included in control style sheet
* | 38d1720 updated skin order in customizer
* | 6386e81 added : live skin preview and select with select2.js jQuery plugin
* | d63dee2 added uglify file on demand
* | 67369af remove the update_git_branch_in_readme in watch => fix infinite loop refresh bug
* | 5dcb2e9 Fix post metas elements inconsitencies (when option is author alone for example)
* | 3201223 Delete customizr/ folder in build/ when customizr_dev task is fired => avoid mistakes when editing files.
* | 1e35baf fixed width of thumbnail with rectangular shape => width must be 100%
* | d8dc880 updated grunt task replace in readme triggered on each watch grunt task => always up to date for Travis build pass status link
* | 24c16a7 updated user defined inline css is now handled with callback on 'tc_user_options_style' hook
* |   7169b48 Merge branch 'custom-comment-bubble' into dev
|\ \
| * | b23594f added comment bubble options and stylings
| * | 4c669cb Added new option in the customizer + filter on tc_bubble_comment
* | | 4b7a583 exclude bin folder
* | | f11df30 add grunt badge
* | | 0bcf1b3 updated copy grunt task
* | | f1bbbfa add grunt travis task
* | | f01b940 fix issues with WordPress coding standard rules custom page
* | | 14a9f3b test php syntax error
|/ /
* | 9fd56c2 updated gitignore with travis-example folder
* | 5d5b9d4 removed tc_archives_headings and tc_content_headings replaced by tc_headings_view
* | 6b08e0f updated : filters tc_content_header_class and tc_archive_header_class are now handled as array of classes with implode();
* |   098135f Merge branch 'fix-subtitle-bug' into dev
|\ \
| * | 7df37fa Fixed : headings are now handled by filtering the_title => fixes the subtitle plugins issue
* | | 0e2a247 travis updated
|/ /
* | 1aaea26 Fix WP coding standard issues
* | 0af06c2 updated gitignore with wpcs
* | 178cf9e updated travis added echoes
* | 8ae0616 changed path to bootstrap
* | 602b009 new bootstrap for wp-php-unit
* | e051557 Travis _s config test
* | 876c2ac added travis phpunit test first test
* | 158bf8c added gitinfo => automatic update of the branch url parameter for Travis ci build
* | 269c001 added travis task updated gruntfile credentials are get from .ftpauth only if the context option passed == travis
* | eb1142b fixed .travis.yml
* | 833e160 added travis build
* | b4bc3b5 updated version number
* | 59f56b5 updated GNU GPL link
* | e3d61c4 fixed layout issues
* | 75f77dd updated : grunt setup, add a note about .ftpauth
* | 73e2960 Added : all changelog dates
* | a0540bb updated : readme.md now includes the Grunt setup (deleted from the gruntfile.js)
* | 4a6cec0 updated grunt : task registering is more "dry"
* |   cd31224 Merge branch 'fix-fp-holder' into dev
|\ \
| * | 0fb6b85 updated holder.js to version v2.4.1. => fixes the featured pages holder image bug
* | | 9658751 updated : always load holder minified
* | | 66ffb37 updated holder.js to version v2.4.1. => fixes the featured pages holder image bug
|/ /
* | 3037f8f added : post metas options. Last update date and customizable notice in title.
* | cc798fb updated metas settings conditional display
* | e444982 updated stepper style overrides default WP number input css
* | b0206ff added customizer settings to select the metas : taxonomies, date, author
* | 7c21a51 updated customizer style : smaller titles
|/
*   3826c72 Merge branch 'grunt-versionning' into dev
|\
| * abcb0da added grunt automatic versionning with replace package
* | 42682f4 updated version to 3.2.6
|/
* 85063c7 added grunt build workflow : clean => copy => compress
* 32e3bdb added grunt translation tasks
*   46bb285 Merge branch 'grunt-tasks-organization' into dev
|\
| * cc7dc38 added each grunt task split by files. Uses load-grunt-config npm package.
* | 2140786 added each grunt task split by files. Uses load-grunt-config npm package.
|/
* 1897c5d added in gruntfile.js : paths global var

= 3.2.5 November 15th 2014 =
* added (lang) Thai language (th), thanks to <a href="http://new.forest.go.th" target="_blank">Wee Sritippho</a>
* updated (lang) French translation
* improved (grunt) skin generation
* updated (css) rtl skins
* updated (css) set outline : none for a.tc-carousel-control. Fixes a visual bug reported on Firefox.

= 3.2.4 November 13th 2014 =
* added customizer : new header z-index option
* fixed Logo centered link bug fix. Added a clear both property to .navbar-wrapper
* fixed menu on tablets landscape, if menu was set to "open on hover", submenus could not be revealed. Fixed by forcing the click behaviour if wp_is_mobile()
* improved  front scripts concatenation boolean filter : 'tc_load_concatenated_front_scripts' default to true. js files can be loaded loaded separetely in dev mode Load bootstrap param not used anymore
* improved customizer sections for wp<4.0 : set conditional priorities ( based on is_wp_version_before_4_0) to reoder the section more consistently skin, header, content, footer....
* fixed Customizer frozen bug. case default control : falls back to no input attr if wp version < 4.0 because input_attrs() was introduced in 4.0
* improved customizer panels : remove useless check if wp version >= 4.0 new private property : is_wp_version_before_4_0
* added Grunt : dev mode, customizer control script is a concatenation of libraries and _control.js
* added Grunt : in dev mode, tc-scripts is a concatenation of main.js + params-dev-mode.js + fancybox + bootstrap
* added Livereload script loaded on dev mode TC_DEV constant is true added in customize_controls_print_scripts when customizing and in wp_head when live
* added Grunt : ftp push enabled for all files Grunt : tc-scripts.min.js concatenates params-dev-mode.js, bootstrap.js, jquery.fancybox-1.3.4.min.js, tc-scripts.js Grunt : tc-script.js jshint @to finish
* fixed menu : 'tc-submenu-fade' is applied if option 'tc_menu_submenu_fade_effect' is true AND ! wp_is_mobile()
* fixed TCparams (localized params) was not defined as a js var
* updated lang : pl_PL, thanks to Marcin Paweł Sadowski
* updated lang : de_DE , thanks to Martin Bangemann

= 3.2.3 November 5th 2014 =
* fixed (php, class-header-header_main.php) remove space after filter declaration for tc_tagline_text
* added (php, class-content-post_list.php) new boolean filter tc_show_post_in_post_list + condition on $post global variable
* added (php, class-fire-admin_page.php) New action hooks__system_config_before, __system_config_after
* fixed (php, class-content-featured_pages.php, class-content-post_thumbnails.php, class-header-header_main.php) JetPack photon bug fixed on the wp_get_attachment_image_src() return value array
* changed (php, class-header-header_main.php) New method : tc_prepare_logo_title_display() hooked on '__header' in place of tc_logo_title_display(), fires 2 new methods tc_logo_view() and tc_title_view()
* fixed (php, class-header-header_main.php) in tc_prepare_logo_title_display() the logo filetype is now checked with a custom function TC_utils::tc_check_filetype(), instead of wp_check_filetype(). This new method checks the filetype on the whole string instead of at the very end of it => fixes the JetPack photon bug for logo
* added (php, class-fire-utils) tc_check_filetype() method
* added (php, class-content-post_thumbnails.php) new filter named tc_thumbnail_link_class => array of css classes
* removed (php, class-content-post_thumbnails.php) 'tc_no_round_thumb' filter, now handled by the 'tc_thumbnail_link_class'  filter
* added (php, class-content-post_thumbnails.php) new filter 'tc_post_thumbnail_img_attributes'
* improved (php, class-content-post_thumbnails.php ) better handling of dynamic inline style for thumbnails img with height || width < to default thumbnails dimensions
* improved (php) get_the_title() has been replaced by esc_attr( strip_tags( get_the_title() ) ) when used as title attribute
* improved (css) set a high z-index (10000) to header.tc-header
* improved (js, tc-script.js) localized params (TCParams) falls back to a default object if they are not loaded (=> typically happens whith a misconfigured cache plugin with combined js files)
* improved (css,php:class-fire-resources.php) font icons have been extracted from the skin stylesheet and are now inlining early in head. New filters : 'tc_font_icon_priority' (default = 0 ), tc_font_icons_path (default : TC_BASE_URL . 'inc/assets/css'), 'tc_inline_font_icons' (default = html string of the inline style)
* improved (js, php:class-fire-resources.php) when debug mode enabled : tc-script.js is loaded not minified. Boostrap is loaded separately and not minified
* added (js:bootstrap.js, php:class-fire-utils_settings_map.php,class-fire-resources.php) new checkbox option in the customizer 'tc_menu_resp_dropdown_limit_to_viewport'.In responsive mode, users can now choose whether the dropdown menu has to be fully deployed or limited to the viewport's height.
* updated (lang) nl_NL : thanks to Joris Dutmer
* added (php:class-fire-utils_settings_map.php) New checkbox option in the customizer 'tc_sticky_transparent_on_scroll' => allow user to disable the semi-transparency of the sticky header on scroll. Default => Enabled (true)
* added (php:class-content-comments.php) New filter 'tc_list_comments_args'. Default value = array( 'callback' => array ( $this , 'tc_comment_callback' ) , 'style' => 'ul' )
* added (php:class-fire-init.php) Added add_theme_support( 'title-tag' ) recommended way for themes to display titles as of WP4.1. source : https://make.wordpress.org/core/2014/10/29/title-tags-in-4-1/
* fixed (css) Bug : since v3.2.1 upgrade, left sidebar was not displayed under 980px https://wordpress.org/support/topic/left-sidebar-disappeared-in-responsive-design-after-todays-upgrade?replies=3
* fixed (lang, php:class-content-comments.php) plural translation string wrapped in _n() where not translated
* improved (js) In customizing mode, jQuery plugins icheck, stepper, selecter are loaded only when necessary. For example : 'function' != typeof(jQuery.fn.stepper) => avoir double loading if a plugin already uses this $ module.
* improved (js, theme-customizr-control.js) icheck : init only if necessary (  0 == $(this).closest('div[class^="icheckbox"]').length )=> beacause it can have been already initiated by a plugin.
* improved (css, class-fire-admin_init.php) admincss handle for enqueuing has been prefixed with tc-, like all other resources of the theme
* improved (css, tc_admin.css) Now minified
* fixed (php, class-fire-utils.php) bbPress compatibility issue. Was generating a notice bbp_setup_current_user was called incorrectly. The current user is being initialized without using $wp->init(). This was due to the tc_get_default_options(), using is_user_logged_in(), called too early. Now hooked in "after_setup_theme" and compatible with bbPress
* updated (lang) es_ES : thanks to María Digo
* improved (js, tc-script.js) Smooth Scrolling option : to avoid potential conflicts with plugins using the 'click' event on anchor's links, the scope of targeted links has been limited to the the #content wrapper : $('a[href^="#"]', '#content')
* fixed (css) Back to top arrow : Better backgroundstyle for ie9+
* fixed (css) ie9- Support : fixed tagline displayed twice issue
* fixed (css) .social-block is displayed and centered for @media (max-width: 320px)
* updated(css) blue3.css is now the default skin, color #27CDA5
* fixed (php, class-fire-init.php) Better handling of the retina mode. the original file is now generated in high definition @x2
* updated : the default slider images have been re-designed and their @x2 version (for high definitation devices) has been added in inc/assets/img
* updated : screenshot of the theme

= 3.2.2 October 30th 2014 =
* fixed (js, tc-script.js) the 'touchstart' event don't trigger the responsive menu toggle anymore => was generating a major bug on responsive devices reported here : https://wordpress.org/support/topic/321-responsive-menu-wont-stay-open?replies=18, and here : https://wordpress.org/support/topic/bug-report-44?replies=4
* added (php, class-fire-admin_page.php) New hooks in admin : '__before_welcome_panel' '__after_welcome_panel
* added (php) new class TC_admin_page handling the welcome panel including the changelog and user system infos
* updated (lang) ru_RU : thanks to <a href="http://bootwalksnews.com/" target="_blank">Evgeny Sudakov</a>
* updated (lang) es_ES : thanks to María Digo
* updated (lang) zh_CN : thanks to Luckie Joy
* updated (lang) hu_HU : thanks to Ferencz Székely
* updated (lang) ca_ES : thanks to Jaume Albaigès
* updated (lang) sk_SK : thanks to <a href="http://www.pcipservis.eu/" target="_blank">Tomáš Lojek</a>
* updated (lang) de_DE : thanks to <a href="http://foerde-mentor.de" target="_blank">Bernd Troba</a>

= 3.2.1 October 20th 2014 =
* fixed (css) Featured pages recentering for max-width 979px
* fixed (css) Sticky header menu background
* improved (js, tc-scripts.js) Scroll event timer only for ie

= 3.2.0 October 20th 2014 =
* added (php, class-content-slider.php) New action hooked : __after_carousel_inner. Used to render the slider controls.
* added (js) slider swipe support with hammer.js. Controls not renderd for mobile devices.
* fixed (php, class-content-comments.php, comments.php) Comment title was not included in the translation strings (out of the poedit source paths). New filter on comment_form_defaults filter 
* added (css, php : class-fire-init.php) css : class 'is-customizing' is added to the body tag in a customization context
* changed (css) transition: width 0.2s ease-in-out, left 0.25s ease-in-out, right 0.25s ease-in-out; is only applied in a customization context.
* changed : (php, class-header-header_main.php) tc_logo_class filter is now handled as an array of css classes instead of a string : implode( " ", apply_filters( 'tc_logo_class', array( 'brand', 'span3') ) )
* added : (php, class-fire-utils.php, class-header-header_main.php) Navbar new customizer option tc_header_layout
* added : (php, class-fire-utils.php, class-header-header_main.php) Navbar new customizer option tc_display_boxed_navbar
* added : (php, class-fire-utils.php, class-header-header_main.php) Tagline ew customizer option tc_show_tagline
* added : (php, class-fire-utils.php, class-content-post4
4141.p14hp) Single post view : new filter tc_single_post_thumbnail_view
* added : (php, class-content-post_thumbnail.php) new class dedicated to the thumbnail view and control : TC_post_thumbnails
* changed : (php, class-content-post_thumbnails.php) thumbnails : filter name tc_post_list_thumbnail changed to tc_display_post_thumbnail. tc_get_post_list_thumbnail changed to tc_get_thumbnail_data
* added : (php, class-fire-utils.php, class-content-post.php) Thumbnails : new option in the customizer tc_single_post_show_thumb
* added : (php, class-content-post_list.php) New filter : tc_attachment_as_thumb_query_args.
* added : (php, class-fire-utils.php, class-content-post_list.php) Thumbnails : new option in the customizer tc_post_list_show_thumb, tc_post_list_use_attachment_as_thumb, tc_post_list_thumb_shape, tc_post_list_thumb_height, tc_post_list_thumb_position, tc_post_list_thumb_alternate
* added : (php, class-fire-utils.php, class-content-footer_main.php) Back to top link : new option in the customizer tc_show_back_to_top
* added : (php, class-fire-utils.php, class-fire-init.php ) Links : new option in the customizer tc_link_hover_effect.
* added : (php, class-content-post_list.php) New filter : tc_thumb_size_name. Default value : 'tc-thumb'
* added : (php, class-fire-utils_settings_map.php) Creation of class-fire-utils_settings_map.php for the customizer settings. Instanciated before TC_utils().
* added : (php, class-content-post_metas.php, class-fire-utils.php ) Post metas : 3 new options in the customizer : tc_show_post_metas_home, tc_show_post_metas_single_post, tc_show_post_metas_post_lists. View implemented with a new callback : add_action( 'template_redirect', array( $this , 'tc_set_post_metas' ));
* added : (php, class-content-headings.php, class-fire-utils.php ) Icons in title : new options in the customizer : tc_show_page_title_icon, tc_show_post_title_icon, tc_show_archive_title_icon, tc_show_post_list_title_icon, tc_show_sidebar_widget_icon, tc_show_footer_widget_icon. View implemented with 2 new callbacks  : add_filter ( 'tc_content_title_icon' , array( $this , 'tc_set_post_page_icon' )), add_filter ( 'tc_archive_icon', array( $this , 'tc_set_archive_icon' ))
* added : (php, class-content-breadcrumb.php, class-fire-utils.php ) Breadcrumb : 4 new optionw in the customizer : tc_show_breadcrumb_home, tc_show_breadcrumb_in_pages, tc_show_breadcrumb_in_single_posts, tc_show_breadcrumb_in_post_lists. Implemented with a new filter and callback :  add_filter( 'tc_show_breadcrumb_in_context'   , array( $this , 'tc_set_breadcrumb_display_in_context' ) )
* added : (lang) Hebrew (he_IL) translation added. Thanks to <a href="http://www.glezer.co.il/">Yaacov Glezer</a>.
* updated : (lang) Russian translation, thanks to <a href="http://webmotya.com/">Evgeny</a>.
* added : (php, class-content-slider.php) new hooks before and after each slides : __before_all_slides, __after_all_slides, __before_slide_{$id}, __after_slide_{$id}
* added : (php, class-content-sidebar.php) new hook for the social links title : tc_sidebar_socials_title
* improvement : (php, class-header_main.php) remove getimagesize() responsible for many bug reports. The logo width and height are now get directly from the WP attachement object which is way more reliable. New filters : 'tc_logo_attachment_img', 'tc_fav_attachment_img'. Backward compatibility is ensured by testing if the option is numeric (id) and falls back to the path src type if not.
* improvement : (php, class-fire-utils.php) logo and favicon upload options are now handled with a specific type of control tc_upload, which has its own rendering class (extension of WP_Customize_Control)
* improvement : (js, theme-customizer-control.js) new constructor added to wp.customize object. Inspired from the WP built-in UploadControl constructor. It uses the id instead of the url attribute of the attachement backbone model.
* fixed : (css) replaced .tc-hover-menu.nav.nav li:hover > ul by .tc-hover-menu.nav li:hover > ul
* improved (css) footer top border changed to 12px to 10px, same as header bottom border
* improved (js, bootstrap) for mobile viewports, apply max-height = viewport to the revealed submenus+ make it scrollable
* improved (php, class-content-post_list.php) round thumb : if size is not set for media, then falls back to medium and force max-width and max-height.

= 3.1.24 September 21th 2014 =
* fixed : (php, class-fire-init.php#393 ) check if defined( 'WPLANG'). WPLANG has to be defined in wp-config.php, but it might not be defined sometimes.
* fixed : (php, class-content-slider.php) the slider loader block has been taken out of the carousel inner wrapper. Fixes the issue reported here : http://presscustomizr.com/customizr-theme-v3-1-23-tested-wordpress-v4-0/#li-comment-235017. The slider loader is diplayed by default for the demo slider.
* added : (php, class-fire-init.php) new option in Customizer > Images => checkbox to display a gif loader on slides setup. Default == false.
* added : (php, class-content-post_navigation.php) 4 new filters to get control on all the options of the single and archive post navigation links : tc_previous_single_post_link_args, tc_next_single_post_link_args, tc_next_posts_link_args, tc_previous_posts_link_args
* improved : (php, class-fire-utils.php#315 ) cleaner code for the fancybox filter on 'the_content'
* improved : (php, class-fire-ressources.php) performance : holder.min.js is now loaded when featured pages are enabled AND FP are set to show images

= 3.1.23 September 6th 2014 =
* improved : (php, class-fire-ressources.php, js : tc-scripts.js ) Performances : tc-scripts.js now includes all front end scripts in one file. 1) Twitter Bootstrap scripts, 2) Holder.js , 3) FancyBox - jQuery Plugin, 4) Retina.js, 5) Customizr scripts. New boolean filters to control each scripts load : tc_load_bootstrap, tc_load_modernizr, tc_load_holderjs, tc_load_customizr_script.
* added : (php, class-footer-footer_main.php#55) 2 new action hooks before and after the footer widgets row : '__before_footer_widgets' , '__after_footer_widgets'
* added : (php, class-footer-footer_main.php#142) Colophon center block : 2 new filter hooks : tc_copyright_link, tc_credit_link
* improved : (php, class-footer-footer_main.php#55) before and after footer widgets hooks have been moved out of the active_sidebar condition in order to be used even with widget free footer
* changed : (php, class-content-breadcrumb.php#581 ) filter hook name has been changed from 'breadcrumb_trail_items' to 'tc_breadcrumb_trail_items'
* changed : (php, class-content-featured_pages.php#112) filter name changed from 'fp_holder_img' to 'tc_fp_holder_img' for namespace consistency reasons
* improved : (php, class-content-featured_pages.php) filter hooks missing parameters ( $fp_single_id and / or $featured_page_id) have been added to 'tc_fp_title', 'tc_fp_text_length', 'fp_img_src, 'tc_fp_img_size', 'tc_fp_round_div', 'tc_fp_title_tag', 'tc_fp_title_block', 'tc_fp_text_block', 'tc_fp_button_block', 'tc_fp_single_display'
* improved : (php, class-content-featured_pages.php) new holder image style. Foreground color is the main skin color.
* updated (js, holder.js) version 2.4 of the script.
* improved : (php, class-fire-init.php#386) replace the disable_for_jetpack() callback by the built-in wp function __return_false()
* added : (php : class-fire-init.php, css) 2 new social networks :  tumblr and flickr.
* added : (php : class-fire-init.php, css) new skin_color_map property
* improved : (php, class-content-post_list.php#240) use apply_filters_ref_array instead of apply_filters for some filters
* improved : (php, class-content-post_list.php#240) 'tc_get_post_list_thumbnail' filter : the current post id has been included in the array of parameters
* improved : (php, class-content-post_list.php#259) 'tc_post_thumb_img' filter : the current post id has been included in the parameters
* improved : (php, class-content-post_metas.php#189) use apply_filters_ref_array instead of apply_filters
* added : (php, class-content-post_metas.php) entry-date meta : new filter to use the modified date instead of the actual post date : 'tc_use_the_post_modified_date'. Default to false. Note : get_the_modified_date() == get_the_date() if the post has never been updated.
* improved : (php, class-content-sidebar.php#115) current_filter() added as parameter of the 'tc_social_in_sidebar' filter hook
* improved : (php, class-content-slider#193) $slider_name_id parameter added to the following filter hooks : tc_slide_background, tc_slide_title, tc_slide_text, tc_slide_color, tc_slide_link_id, tc_slide_link_url, tc_slide_button_text, tc_slide_title_tag, tc_slide_button_class
* added : (php : class-content-slider.php, js : tc-scripts.js, css) Slider : for a better experience, when the re-center option is checked in Appearance > Customizer > Responsive settings, a gif loader is displayed while recentering.
* fixed : (php, class-fire-admin_init.php#312) Changelog was not displayed in ?page=welcome.php#customizr-changelog. Now look for '= '.CUSTOMIZR_VER to match the current version changelog
* improved : (php, class-header-header_main.php#223) action hook 'before_navbar' renamed to '__before_navbar' for namespace consistency reasons
* added : (php, class-header-header_main.php) added 'tc_head_display' filter
* improved : (php, class-header-header_main.php) tc_favicon_display filter is now handled with a sprintf()
* added : (php, class-header-header_main.php) new filters tc_logo_link_title , tc_site_title_link_title
* changed : (php, class-header-header_main.php ) filter names : __max_logo_width => tc_logo_max_width and __max_logo_height => tc_logo_max_height
* changed : (php, class-header-header_menu.php#97) filter menu_wrapper_class renamed in tc_menu_wrapper_class
* changed : (php, class-header-nav_walker.php#41 ) filter menu_open_on_clicks renamed in tc_menu_open_on_click
* added : (php, comments.php) new filter : tc_comments_wrapper_class inside div#comments
* changed : (php, comments.php) filter comment_separator renamed to tc_comment_separator
* improved : (php, comments.php) cleaner code
* changed : (php, init.php#47) Class loading order. Utils are now loaded before resources.
* changed : (php, class-fire-resources.php) localized params filter renamed 'tc_customizr_script_params'. Left and Right sidebars classes are now set dynamically form the global layout params.
* changed : (php, class-fire-utils.php#497) added the $key parameter to tc_social_link_class
* improved : (php , class-fire-utils.php#207)tc_get_the_ID() : now check the wp_version global to avoid the get_post() whitout parameter issue. ( $post parameter became optional after v3.4.1 )
* added : (php, class-controls.php) 2 new action hooks : __before_setting_control, __after_setting_control, using the setting id as additional parameter.
* fixed : (css) .navbar-inner .nav li : 1px hack for chrome to not loose the focus on menu item hovering
 
= 3.1.22 August 16th 2014 =
* added : (css, class-fire-init.php#75) 9 new minified css skins
* fixed : (php, class-content-breadcrumb.php#443) added a check is_array(get_query_var( 'post_type' ) in archive context
(bug reported here : https://wordpress.org/support/topic/illegal-offset-type-in-isset-or-empty-in-postphp-after-upgrade-to-custom3120)
* improved : (php, class-content-headings.php#224) added a boolean filter named 'tc_display_link_for_post_titles' (default = true) to control whether the post list titles have to be a link or not

= 3.1.21 August 11th 2014 =
* fixed : (php, class-content-post_list.php) boolean filter 'tc_include_cpt_in_archives' is set to false. Following a bug reported here http://wordpress.org/support/topic/content-removedchanged-after-updating-to-3120?replies=8 Thanks to http://wordpress.org/support/profile/le-formateur for reporting it.

= 3.1.20 August 9th 2014 =
* added : (lang) Ukrainian translation. Many thanks to <a href="http://akceptor.org/">Aceptkor!</a>
* added : (php, class-content-post_list.php) new filter to control if attachment have to be included in search results or not : tc_include_attachments_in_search_results. Default : true.
* added : (php, class-content-post_list.php) Custom Post Types : new pre_get_posts action. Now includes Custom Posts Types (set to public and excluded_from_search_result = false) in archives and search results. In archives, it handles the case where a CPT has been registered and associated with an existing built-in taxonomy like category or post_tag
* added : (php, class-content-post_metas.php) Now handles any custom or built-in taxonomies associated with built-in or custom post types. Displays the taxonomy terms like post category if hierarchical, and like post tags if not hierarchical. Uses a new helper (private method) : _get_terms_of_tax_type(). New filter created : tc_exclude_taxonomies_from_metas, with default value : array('post_format') => allows to filter which taxonomy to displays in metas with a customizable granularity since it accepts 2 parameters : post type and post id.
* added : (php, class-fire-utils.php) added the social network key to the target filter : apply_filters( 'tc_socials_target', 'target=_blank', $key )
* added : (php, class-header-header_main.php) favicon and logo src are ssl compliant => fixes the "insecure content" warning in url missing 'https' in an ssl context
* added : (php, class-fire-utils.php) new placeholder image for the demo slider customizr.jpg
* added : ( php, class-content-featured_pages.php ) add edit link to featured pages titles when user is logged in and has the capabilities to do so
* improved : (php, class-content-breadcrumb.php) now displays all levels of any hierarchical taxinomies by default and for all types of post (including hierarchical CPT). This feature can be disabled with a the filter : tc_display_taxonomies_in_breadcrumb (set to true by default). In the case of hierarchical post types (like page or hierarchical CPT), the taxonomy trail is only displayed for the higher parent.
* improved : (php, class-fire-utils.php and class-controls.php) moved the slider-check control message if no slider created yet to tc_theme_options[tc_front_slider] control

= 3.1.19 July 14th 2014 =
* improved : (php, class-admin-meta_boxes) code structure
* improved : (js, meta boxes) better code structure
* added : (php, class-fire-init.php) support for svg and svgz in media upload
* added : (php, class-header-header_main.php) new filter 'tc_logo_img_formats'
* fixed : (php, class-content-breadcrumb#291) check existence of rewrite['with_front']

= 3.1.18 July 11th 2014 =
* added : (lang) Czech translation. Many thanks to Martin Filák!
* added : (php , class-content_slider.php) two new action hooks (filters) for a better control of the slider layout class (tc_slider_layout_class) and the slider image size (tc_slider_img_size)
* added : (php, class-fire-resources.php) new filter named "tc_custom_css_priority" to take control over the custom css writing priority in head
* added : (php) empty index.php added in all folders
* improved : (php) Every class is now "pluggable" and can be overriden
* improved : (php, class-content-post_list.php) the missing $layout parameter has been added to the "tc_post_list_thumbnail" filter
* improved : (php, class-content-headings.php) headings of the page for post is now displayed by default (if not front page). Action hook (filter) : tc_page_for_post_header_content
* improved : (php, class-content-sidebar.php) before and after sidebars hooks have been moved out of the active_sidebar condition in order to be used even with widget free sidebars

= 3.1.17 July 6th 2014 =
* fixed : back to previous screenshot

= 3.1.16 July 3rd 2014 =
* improved : (php, css, js) better file structure. Init and front end files have been moved in /inc folder
* improved : new theme screenshot
* fixed : (php, class-content-slider.php#102) missing icon parameter has been added to wp_get_attachment_image()

= 3.1.15 May 31st 2014 =
* fixed : (css : editor-style.css) background default color flagged as !important
* fixed : (php : class-content-headings.php) post edit button is displayed to author of the post and admin profiles Thanks to <a href="http://presscustomizr.com/author/eri_trabiccolo/">Rocco</a>
* fixed : (php : class-content-slider.php) slider edit button is displayed for users with the upload_files capability
* fixed : (php : class-content-comments.php) class comment-{id} has been added to the article comment wrapper to ensure compatibility with the recent comment WP built-in widget

= 3.1.14 May 15th, 2014 =
* added : (js : theme-customizer-control.js, css : theme-customizer-control.css, php : class-admin-customize.php) Donate block can be disable forever in admin.

= 3.1.13 May 5th, 2014 =
* added : (lang) Danish translation. Thanks to <a href="http://teknikalt.dk">Peter Wiwe</a>
* added : (css, js) Donate link in admin

= 3.1.12 April 23rd, 2014 =
* fixed : (css) category archive icon now displayed again in chrome
* fixed : (php : TC_init::tc_add_retina_support) retina bug fixed by <a href="http://wordpress.org/support/profile/electricfeet" target="_blank">electricfeet</a>
* improved : (php : TC_breadcrumb ) breadcrumb trail for single posts, category and tag archive now includes the page_for_posts rewrited if defined.
* improved : (php) Better handling of the comment reply with the add_below parameter. Thanks to <a href="http://presscustomizr.com/author/eri_trabiccolo/">Rocco</a>.
* improved : (php) TC_Utils::tc_get_option() returns false if option not set
* removed : (php) Customiz'it button has been taken off


= 3.1.11 April 21st, 2014 =
* added : (php , css) customizer : new option in the Skin Settings, enable/disable the minified version of skin
* added : (php) customizer : new option in the Responsive Settings, enable/disable the automatic centering of slides
* added : (js, php) automatic centering of the slider's slides on any devices. Thanks to <a href="http://presscustomizr.com/author/eri_trabiccolo/">Rocco</a>.
* improved : (css) skins have been minified to speed up load time (~ saved 80Ko)
* improved : (php) logo and favicon are now saved as relative path => avoid server change issues.
* improved : (php) better class loading. Check the context and loads only the necessary classes.
* improved : (php) customizer map has been moved into the class-fire-utils.php
* improved : (php) performance improvement for options. Default options are now generated once from the customizer map and saved into database as default_options
* improved : (js) block repositioning is only triggered on load for responsive devices
* updated : (translation) Slovak translation has been updated. Thanks to <a href="www.pcipservis.eu">Michal Hranicky</a>.

= 3.1.10 March 31st, 2014 =
* fixed : (php : TC_init::tc_plugins_compatibility() , custom-page.php) WooCommerce compatibility issue fixed.
* added : (TC_customize::tc_customize_register() , TC_resources::tc_enqueue_customizr_scripts() , tc_script.js ) New option in customizer : Enable/Disable block reordering for smartphone viewport.

= 3.1.9 March 27th, 2014 =
* fixed : (js  : tc_scripts.js , php : index.php ) responsive : dynamic content block position bug fixed in tc_script.js, the wrapper had to be more specific to avoid block duplication when inserting other .row inside main content. Thanks to <a href="http://presscustomizr.com/author/eri_trabiccolo/" target="_blank">Rocco Aliberti</a>.
* fixed : (php : TC_resources::tc_enqueue_customizr_scripts() ) comment : notice on empty archives due to the function comments_open(). A test on  0 != $wp_query -> post_count has been added in TC_resources::tc_enqueue_customizr_scripts(). Thanks to <a href="http://presscustomizr.com/author/eri_trabiccolo/" target="_blank">Rocco Aliberti</a>.
* improved : (js  : tc_scripts.js) responsive : the sidebar classes are set dynamically with a js localized var using the tc_{$position}_sidebar_class filter

= 3.1.8 March 3rd, 2014 =
* fixed : (js) responsive : dynamic content block position bug fixed in tc_script.js


= 3.1.7 February 6th, 2014 =
* fixed : (css) : icons rendering for chrome
* improved : (css) : footer white icons also for black skin
* added : (php) utils : new filter with 2 parameters to tc_get_option
* added : (php) featured pages : new filter tc_fp_id for the featured pages
* added : (php) featured pages : new parameters added to the fp_img_src filter
* improved : (php) metaboxes : no metaboxes for acf post types
* improved : (js) responsive : dynamic content block position on resize hase been improved in tc_script.js
* fixed : (php) Image size : slider full size sets to 9999 instead of 99999 => was no compatible with Google App engine
* improved : (php) slider : make it easier to target individual slides with a unique class/or id
* added : (php) footer : dynamic actions added inside the widget wrapper
* improved : (php) footer : additional parameter for the tc_widgets_footer filter
* improved : (php)(js) comments : Comment reply link the whole button is now clickable
* fixed : (html) Google Structured Data : addition of the "updated" class in entry-date


= 3.1.6 December 15th, 2013 =
* added : (php)(js) customizer controls : new filter for localized params
* added : (php) featured pages : new filters for title, excerpt and button blocks
* added : (php) search : form in the header if any results are found
* improved : (php) body tag : "itemscope itemtype="http://schema.org/WebPage" included in the 'tc_body_attributes' filter hook
* improved : (php) overall code : check added on ob_end_clean()
* improved : (php) headings : new filters by conditional tags
* improved : (php) comments : 'comment_text' WP built-in filter has been added in the comment callback function
* fixed : (js) submenu opening on click problem : [data-toggle="dropdown"] links are excluded from smoothscroll function
* fixed : (php) compatibility with NEXTGEN plugin : fixed ob_start() in class-content-headings::tc_content_headings()

= 3.1.5 December 14th, 2013 =
* fixed : (php) child themes bug : child theme users can now override the Customizr files with same path/name.php.

= 3.1.4 December 14th, 2013 =
* fixed : (css) featured pages : another responsive thumbnails alignment for max-width: 979px

= 3.1.3 December 14th, 2013 =
* fixed : (css) featured pages : responsive thumbnails alignment

= 3.1.2 December 14th, 2013 =
* improved : (php) minor file change : the class__.php content has been moved in functions.php

= 3.1.1 December 14th, 2013 =
* added : (language) Turkish : thanks to <a href="http://www.ahmethakanergun.com/">Ahmet Hakan Ergün</a>
* added : (css) customizer : some styling
* fixed : (css) post thumbnails : minor alignment issues
* fixed : (php) translations in customizer for dropdown lists

= 3.1.0 December 13rd, 2013 =
* added : (language) Hungarian : thanks to Ferencz Székely
* added : (php) Woocommerce : full compatibility
* added : (php) BBpress : full compatibilty
* added : (php) Qtranslate : full compatibilty. Thanks to <a href="http://websiter.ro" target="_blank">Andrei Gheorghiu</a>
* added : (js)(php)(css) Retina new option : customizr now supports retina devices. Uses Ben Atkin's retina.js script.
* added : (js)(php)(css) new option : Optional smooth scroll effect on anchor links in the same page
* added : (php)(css) Menu : new option to select hover/click expansion mode of submenus
* added : (css) Twitter Bootstrap : Glyphicons are now available
* added : (php) site title : filter on the html tag
* added : (php) archives (categories, tags, author, dates) and search results titles can be filtered
* added : (php) posts : 2 new hooks before and after post titles. Used for post metas.
* added : (php) logo and site title : new filter for link url (allowing to change the link on a per page basis)
* added : (php) featured pages : filters for page link url and text length
* added : (php) featured pages : new filter for the button text (allowing to change the title on a per page basis)
* added : (php) slider : new filters allowing a full control of img, title, text, link, button, color
* added : (php) slider : new function to easily get slides out of a slider
* added : (php) Slider : New edit link on each slides
* added : (php) comments : filter on the boolean controlling display
* added : (php) comments : direct link from post lists to single post comments section
* added : (php) comments : new filters allowing more control on the comment bubble
* added : (php) metaboxes : filter on display priority below WYSIWYG editor
* added : (php) footer : filters on widgets area : more controls on number of widgets and classes
* added : (php) sidebars : filters on column width classes
* added : (php) content : filters on the layout
* added : (php) page : support for excerpt
* added : (js)(php) slider : new filter for an easier control of the stop on hover
* added : (php) Social Networks : possibility to easily add any social networks in option with a custom icon on front end
* added : (php) Social Networks : filter allowing additional link attributes like rel="publisher" for a specific social network
* added : (php) Posts/pages headings : new filters to enable/disable icons
* added : (php) Post lists : edit link in post titles for admin and edit_posts allowed users
* added : (php)(css) Intra post pagination : better styling with buttons
* added : (php) sidebars : two sidebar templates are back. needed by some plugins
* changed : (php) Featured page : name of the text filter is now 'fp_text'
* improved : (css) Menu : style has been improved
* improved : (php) slider : controls are not displayed if only on slide.
* improved : (php) fancybox : checks if isset $post before filtering content
* improved : (css) widgets : arrow next to widget's list is only displayed for default WP widgets
* improved : (javascript) fancybox : now sets the image's title or alt as fancybox popin title (instead of page or post title)
* fixed : (php) blog page layout : when blog was set to a page, the specific page layout was not active anymore
* fixed : (php) menu : the tc_menu_display filter was missing a parameter
* fixed : (php) comments : removed the useless permalink for the comments link in pages and posts
* updated : (lang) pt_BR : Thanks to Roner Marcelo and Rodrigo Stuchi
* updated : (lang) nl_NL : Thanks to Joris Dutmer
* updated : (lang) es_ES : Thanks to María
* updated : (lang) de_DE : Thanks to Martin Bangemann
* updated : (lang) zh_CN : Thanks to Luckie Joy
* updated : (lang) ar : Thanks to Ramez Bdiwi
* updated : (lang) ca_ES : Thanks to Jaume Albaigès
* updated : (lang) ru_RU : Thanks to <a href="http://bootwalksnews.com/" target="_blank">Evgeny Sudakov</a>
* removed : (php) Dev Tools : the developer tools have been removed from the theme for perfomances issues.


= 3.0.15 November 26th, 2013 =
* added : (language) Catalan : thanks to <a href="https://twitter.com/jaume_albaiges" target="_blank">Jaume Albaig&egrave;s</a>
* fixed : (js) Slider : ie7/ie8/ie9 hack (had to be re-implemented) : thanks to @barryvdh (https://github.com/twbs/bootstrap/pull/3052)


= 3.0.14 November 5th, 2013 =
* added : (language) Arabic : thanks to Ramez Bdiwi
* added : (language) RTL support : thanks to Ramez Bdiwi
* added : (language) Romanian : thanks to <a href="http://websiter.ro" target="_blank">Andrei Gheorghiu</a>
* added : (php) two hooks in index.php before article => allowing to add sections
* added : (php) new customizer option : select the length of posts in lists : excerpt of full length
* added : (php) add_size_images : new filters for image sizes
* added : (php) rtl : check on WPLANG to register the appropriate skin
* added : (php) featured pages : new filter for featured pages areas
* added : (php) featured pages : new filter for featured page text
* added : (php) slider : 3 filters have been added in class-admin-meta_boxes.php to modify the text, title and button length __slide_text_length, __slide_title_length, __slide_button_length
* added : (php) logo : 2 new filters to control max width and max height values (if logo resize options is enabled) : '__max_logo_width' , '__max_logo_height'
* added : (php) body tag : a new action hook '__body_attributes'
* added : (php) header tag : new '__header_classes' filter
* added : (php) #main-wrapper : new 'tc_main_wrapper_classes' filter
* added : (php) footer : new '__footer_classes' filter
* added : (js) scrollspy from Bootstrap
* added : (js) Scrollspy : updated version from Bootstrap v3.0. handles submenu spy.
* added : (css) back to top link colored with the skin color links
* added : (css) bootstrap : alerts, thumbnails, labels-badges, progress-bars, accordion stylesheets
* added : (css) Editor style support for skins, user style.css, specific post formats and rtl.
* improved : (css) performance : Avoid AlphaImageLoader filter for IE and css minified for fancybox stylesheet
* improved : (css) (php) logo : useless h1 tag has been removed if logo img. Better rendering function with printf. Better filters of logo function. 2 new actions have been added before and after logo : '__before_logo' , '__after_logo'
* removed : (php) Post list content : removed the useless buble $style var
* removed : (css) featured pages : useless p tag wrap for fp-button removed
* removed : (php) User experience : redirection to welcome screen on activation/update
* removed : (php) Security : Embedded video, Google+, and Twitter buttons
* fixed : (php) breadcrumb class : add a check isset on the $post_type_object->rewrite['with_front'] for CPT
* fixed : (php) a check on is_archive() has been added to tc_get_the_ID() function in class fire utils
* fixed : (php) we used tc__f('__ID') instead of get_the_ID() in class-header-slider
* fixed : (php) we add a hr separator after header only for search results and archives
* fixed : (php) comments : 'tc_comment_callback' filter hook was missing parameters
* fixed : (php) featured pages : filter  'tc_fp_single_display' was missing parameters
* fixed : (css) comments avatar more responsive
* fixed : (css) ie9 and less : hack to get rid of the gradient effect => was bugging the responsive menu.

= 3.0.13 September 20th, 2013 =
* fixed : (php) Logo upload : we check if the getimagesize() function generates any warning (due to restrictions of use on some servers like 1&1) before using it. Thanks to <a href="http://wordpress.org/support/profile/svematec" target="_blank">svematec</a>, <a href="http://wordpress.org/support/profile/electricfeet" target="_blank">electricfeet</a> and <a href="http://wordpress.org/support/profile/heronswalk" target="_blank">heronswalk</a> for reporting this issue so quickly!

= 3.0.12 September 18th, 2013 =
* fixed : (php) the slider is now also displayed on the blog page. Thanks to <a href="http://wordpress.org/support/profile/defttester" target="_blank">defttester</a> and <a href="http://wordpress.org/support/profile/rdellconsulting" target="_blank">rdellconsulting</a>

= 3.0.11 September 16th, 2013 =
* added : (php) filter to the skin choices (in customizer options class), allowing to add new skins in the drop down list
* added : (php) filter for enqueuing the styles (in class ressources), allowing a better control for child theme
* added : (css) current menu item or current menu ancestor is colored with the skin color
* added : (php) function to check if we are using a child theme. Handles WP version <3.4.
* improved : (css) new conditional stylesheets ie8-hacks : icon sizes for IE8
* improved : (css) better table styling
* improved : (php) logo dimensions are being rendered in the img tag
* improved : (php) class group instanciation is faster, using the class group array instead of each singular group of class.
* improved : (php) the search and archive headers are easier to filter now with dedicated functions
* fixed : (css) archives and search icons color were all green for all skins
* fixed : (php) 404 content was displayed several times in a nested url rewrite context thanks to <a href="http://wordpress.org/support/profile/electricfeet" target="_blank">electricfeet</a>
* fixed : (php) attachment meta data dimensions : checks if are set $metadata['height'] && $metadata['width'] before rendering
* fixed : (php) attachment post type : checks if $post is set before getting the type
* fixed : (php) left and right sidebars are rendered even if they have no widgets hooked in thanks to <a href="http://wordpress.org/support/profile/pereznat" target="_blank">pereznat</a>.

= 3.0.10 September 10th, 2013 =
* CHILD THEME USERS, templates have been modified : index.php, header.php, footer.php, comments.php*
* added : (php) (css) (html) New option : Draggable help box and clickable tooltips to easily display some contextual information and help for developers
* added : (php) support for custom post types for the slider meta boxes
* added : (php) new filter to get the post type
* added : polish translation. thanks to Marcin Sadowski from <a href="http://www.sadowski.edu.pl" target="_blank">http://www.sadowski.edu.pl</a>
* added : (php) (html) attachments are now listed in the search results with their thumbnails and descriptions, just like posts or pages
* added : (css) comment navigation styling, similar to post navigation
* added : (php) (css) author box styling (if bio field not empty)
* added : (css) comment bubble for pages
* added : (js) smooth transition for "back to top" link. Thanks to Nikolov : <a href="https://github.com/nikolov-tmw" target="_blank">https://github.com/nikolov-tmw</a>
* added : (js) smooth image loading on gallery attachment navigation
* added : (lang) Dutch translation. Thanks to Joris Dutmer.
* added : (css) icon to title of archive, search, 404
* improved : (js) (php) tc-scripts.js : now includes fancybox + carousel scripts with dynamic variables (localized)
* improved : (php) attachment screen layout based on the parent
* improved : (php) simpler action hooks structure in the main templates : index, header, footer, comments, sidebars
* improved : (css) responsive behaviour : slider caption now visible for devices < 480px wide, thumbnail/content layout change for better display, body extra padding modified
* improved : (php) For better performances : options (single and full array) are now get from the TC_utils class instance instead of querying the database. (except for the customization context where they have to be retrieved dynamically from database on refresh)
* improved : (js) performance : tc_scripts and ajax_slider have been minified
* fixed : (css) IE fix : added z-index to active slide to fix slides falling below each other on transition. Thanks to PMStanley <a href="https://github.com/PMStanley">https://github.com/PMStanley</a>
* fixed : (css) IE fix : added 'top: 25%' to center align slide caption on older versions of IE. Thanks to PMStanley <a href="https://github.com/PMStanley" target="_blank">https://github.com/PMStanley</a>
* fixed : (php) empty reply button in comment threads : now checks if we reach the max level of threaded comment to render the reply button
* fixed : (php) empty nav buttons in single posts are not displayed anymore
* fixed : (css) font-icons compatibility with Safari is fixed for : page, formats (aside, link; image, video) and widgets (recent post, page menu, categories) thanks to <a href="http://wordpress.org/support/profile/electricfeet" target="_blank">electricfeet</a>
* fixed : (css) ordered list margin were not consistent in the theme thanks to <a href="http://wordpress.org/support/profile/electricfeet" target="_blank">electricfeet</a>
* fixed : (css) slider text overflow
* removed : sidebars templates. Sidebar content is now rendered with the class-content-sidebar.php

= 3.0.9 August 19th, 2013 =
* ! SAFE UPGRADE FOR CHILD THEME USERS (v3.0.8 => v3.0.9) ! *
* fixed : function tc_is_home() was not checking the case where display nothing on home page. No impact for child theme users. Thanks to <a href="http://wordpress.org/support/profile/monten01">monten01</a>, <a href="http://wordpress.org/support/profile/rdellconsulting" target="_blank">rdellconsulting</a>
* fixed : When the permalink structure was not set to default, conditional tags is_page() and is_attachement() stopped working. They are now replaced by tests on $post -> post_type in class-main-content.php
* fixed : test if jet_pack is enabled before filtering post_gallery hook => avoid conflict
* fixed : @media print modified to remove links thanks to <a href="http://wordpress.org/support/profile/electricfeet" target="_blank">electricfeet</a>
* fixed : btn-info style is back to original Bootstrap style thanks to <a href="http://wordpress.org/support/profile/jo8192" target="_blank">jo8192</a>
* fixed : featured pages text => html tags are removed from page excerpt
* improved : custom css now allows special characters
* improved : better css structure, media queries are grouped at the end of the css files
* added : two new social networks in Customizer options : Instagram and WordPress
* added : help button and page in admin with links to FAQ, documentation and forum
* added : new constant TC_WEBSITE for author URI
* added :  Swedish translation : thanks to Johnny Nyström

= 3.0.8 August 6th, 2013 =
* fixed : function tc_is_home() was missing a test. No impact for child theme users. Thanks to <a href="http://wordpress.org/support/profile/ldanielpour962gmailcom">http://wordpress.org/support/profile/ldanielpour962gmailcom</a>, <a href="http://wordpress.org/support/profile/rdellconsulting">http://wordpress.org/support/profile/rdellconsulting</a>, <a href="http://wordpress.org/support/profile/andyblackburn">http://wordpress.org/support/profile/andyblackburn</a>, <a href="http://wordpress.org/support/profile/chandlerleighcom">http://wordpress.org/support/profile/chandlerleighcom</a>

= 3.0.7 August 5th, 2013 =
* fixed : the "force default layout" option was returning an array instead of a string. Thanks to http://wordpress.org/support/profile/edwardwilliamson and http://wordpress.org/support/profile/henry12345 for pointing this out!
* improved : get infos from parent theme if using a child theme in customizr-__ class constructor
* improved : enhanced filter for footer credit
* added : a notice about changelog if using a child theme
* improved : use esc_html tags in featured page text and slider captions

= 3.0.6 August 4th, 2013 =
* fixed : Spanish translation has been fixed. Many thanks again to Maria del Mar for her great job!
* fixed : Pages protected with password will not display any thumbnail or excerpt when used in a featured page home block (thanks to rocketpopgames http://wordpress.org/support/profile/rocketpopgames)
* improved : performance : jquery.fancybox.1.3.4.js and modernizr have been minified
* added : footer credits can now be filtered with add_filter( 'footer_credits') and hooked with add_action ('__credits' )
* added : new customizer option to personnalize the featured page buttons
* added : brazilian portugues translation! Many thanks to Roner Marcelo  (http://ligaht.com.br/)

= 3.0.5 July 29th, 2013 =
* fixed : breadcrumb translation domain was not right
* fixed : domain translation for comment title was not set
* fixed : in v3.0.4, a slider could disappeared only if some slides had been inserted at one time and then deleted or disabled afterward. Thanks to Dave http://wordpress.org/support/profile/rdellconsulting!
* fixed : holder.js script bug in IE v8 and lower. Fixed by updating holder.js v1.9 to v2.0. Thanks to Joel (http://wordpress.org/support/profile/jrisberg) and Ivan (http://wordpress.org/support/profile/imsky).
* improved : better handling of comment number bubble everywhere : check if comments are opened AND if there are comments to display
* improved : welcome screen on update/activate : changelog automatic update, new tweet button
* improved : lightbox navigation is now enabled for galleries with media link option choosen (new filters on post gallery and attachment_link)
* improved : better code organization : split of content class in specific classes by content type
* added : customizr option for images : enable/disable autoscale on lightbox zoom
* added : jQuery fallback for CSS Transitions in carousel (ie. Internet Explorer) : https://github.com/twbs/bootstrap/pull/3052/files
* added : spanish translation. Thanks to Maria del Mar

= 3.0.4 July 22nd, 2013 =
* fixed : minor css correction on responsive thumbnail hover effect
* fixed : minor syntaxic issue on comment title (printf)
* fixed : translation domain was wrong for social networks
* fixed : slider arrows were still showing up if slides were deleted but not the slider itself. Added a routine to check if slides have attachment.
* improved : image galleries : if fancybox active, lightbox navigation is now enabled
* improved : better capability control of edit page button. Only appears if user_can edit_pages (like for posts)
* added : Activation welcome screen
* added : new action in admin_init hook to load the meta boxes class

= 3.0.3 July 20th, 2013 =
* added : german translation. Thanks to Martin Bangemann <design@humane-wirtschaft.de> !
* changed : default option are now based on customizer settings
* fixed : reordering slides was deleting the slides

= 3.0.2 July 20th, 2013 =
* fixed : problem fixed on theme zipping and upload in repository 

= 3.0.1 July 4th, 2013 =
* fixed : 'header already sent' error fixed (space before php opening markup in an admin class) was generating an error on log out  

= 3.0 June 28th, 2013 =
* changed : global code structure has changed. Classes are instanciated by a singleton factory, html is rendered with actions, values are called through filters
* fixed : favicon rendering, $__options was not defined in head
* fixed : sidebars reordering on responsive display, tc_script.js file


= 2.1.8 June 19rd, 2013 =
* changed : activation options are disable for posts_per_page and show_on_front
* changed : redirect to customizr screen on first theme activation only


= 2.1.7 June 19rd, 2013 =
* fixed : home page slider was checking a $slider_active variable not correctly defined
* fixed : slider name for page and post was only ajax saved. Now also regular save on post update.


= 2.1.6 June 19rd, 2013 =
* improved : Menu title padding
* fixed : front office : page and post sliders could not be disable once created
* removed : some unnecessary favicon settings
* fixed : function wp_head() moved just before the closing <head> tag
* added : filter on wp_filter function
* added : russion translation, thanks to Evgeny Sudakov <flounder-1@yandex.ru>!
* improved : thumbnail and content layout for posts lists
* fixed : ajax saving was not working properly for page/page slider, a switch case was not breaked.

= 2.1.5 June 13rd, 2013 =
* fixed     : When deleted from a slider, the first slide was not cleared out from option array
* added     : Titles in customizer sections
* added     : checkbox to enable/disable featured pages images
* added     : Optional colored top border in customizer options
* added     : new black skin
* removed   : text-rendering: optimizelegibility for hx, in conflict with icon fonts in chrome version 27.0.1453.94
* improved  : blockquote styling
* fixed     : in tc_script.js insertbefore() target is more precise
* improved  : font icons are now coded in CSS Value (Hex)
* added     : add_action hooks in the templates index and sidebars

= 2.1.4 June 6th, 2013 =
* fixed : in tc_meta_boxes.php, line 766, a check on the existence of $slide object has been added
* fixed : iframe content was dissapearing when reordering divs on resize. Now  handled properly in tc_scripts.js
* fixed : breadcrumb menu was getting covered (not clickable) in pages. fixed in css with z-index.
* fixed : thumbnails whith no-effect class are now having property min-height:initial => prevents stretching effect
* fixed : revelead images of featured page were stretched when displayed with @media (max-width: 979px) query
* fixed : better vertical alignment of the responsive menu
* changed : color of slider arrows on hover
* changed : text shadow of titles
* changed : color and shadow of site description

= 2.1.3 May 29th, 2013 =
* fixed : in tc_voila_slider, jump to next loop if attachment has been deleted
* removed : title text in footer credit
* fixed : image in full width slider are displayed with CSS properties max-width: 100%, like before v2.0.9

= 2.1.2 May 29th, 2013 =
* fixed : new screenshot.png

= 2.1.1 May 28th, 2013 =
* fixed : new set of images licensed under Creative Commons CC0 1.0 Universal Public Domain Dedication (GPL Compatible)

= 2.1.0 May 28th, 2013 =
* fixed : slide was still showing up when 'add to a slider' button was unchecked and a slider selected
* fixed : new set of images with compliant licenses

= 2.0.9 May 27th, 2013 =
* replaced : jquery fancybox with a GPL compatible version
* removed : icon set non GPL compatible
* added : icon sets Genericons and Entypo GPL compatible
* fixed : image in full width slider are now displayed with CSS properties height:100% et width: auto
* added : function hooked on wp_head to render the custom CSS

= 2.0.8 May 26th, 2013 =
* removed : minor issue, the function tc_write_custom_css() was written twice in header.php

= 2.0.7 May 25th, 2013 =
* fixed : custom featured text (for featured pages) on front page was not updated when updated from customizer screen
* fixed : title of page was displayed when selected as static page for front page
* fixed : border-width of the status post-type box
* added : custom css field in customizer option screen
* added : lightbox checkbox option in customizer option screen

= 2.0.6 May 22nd, 2013 =
* added : new customizer option to enable/disable comments in page. Option is tested in index.php before rendering comment_templates for pages
* fixed : in the stylesheets, the right border of tables was unnecessary

= 2.0.5 May 17th, 2013  FIRST WP.ORG APPROVED VERSION ! =
* fixed : printf php syntax in footer.php

= 2.0.4 May 16th, 2013 =
* fixed : test on current_user_can( 'edit_post' ) in template part content-page.php was generating a Notice: Undefined offset: 0 in ~/wp-includes/capabilities.php on line 1067
* added : copyright and license declaration in style.css

= 2.0.3 May 15th, 2013=
* fixed : same unique slug as prefix for all custom function names, classes, public/global variables, database entries.

= 2.0.2 May 6th, 2013 =
* fixed : CSS image gallery navigation arrows
* removed : the_content() in attachment templates
* fixed : bullet list in content now visible
* added : hover effect on widget lists
* fixed : skin colors when hovering and focusing form fields
* fixed : skin colors when hovering social icons

= 2.0.1 April 29th, 2013 =
* Removal of meta description (plugin territory)
* Page edit button is only visible for users logged in and with edit_post capabilities

= 2.0 April 28th, 2013 =
* Replace the previous custom post type slider feature (was plugin territory) with a custom fields and options slider generator
* Addition of ajax powered meta boxes in post/page/attachment for the sliders

= 1.1.7 April 17th, 2013 =
* file structure simplification : one core loop in index.php
* 

= 1.1.6 April 17th, 2013 =
* Removal of add_editor_style()
* Addition of image.php and content-attachemnt.php for the images galleries and attachement rendering

= 1.1.5 April 17th, 2013 =
* Sanitization of home_url() in some files (with esc_url)
* Clearing of warning message in slides list : check on the $_GET['action'] index
* Addition of some localized strings
* Removal of the optional WP footer credit links

= 1.1.4 April 17th, 2013 =
* addition of selected() and checked() functions in metaboxes input
* better sanitization of WP customizer inputs : 3 sanitization callbacks added in tc_customizr_control_class for number, textarea and url

= 1.1 April 16th, 2013 =
* Better stylesheets enqueuing
* Fix the quick mode edit for slide custom post : add a script to disable the clearing of metas fields on update
* Add a fallback screen on activation if WP version < 3.4 => WP Customizer not supported
* Fix the slide caption texts rendering change the conditions (&& => ||)

= 1.0 April 16th, 2013 =
* Initial Release