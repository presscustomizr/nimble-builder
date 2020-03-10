<?php
if ( !class_exists( 'SEK_Front_Assets' ) ) :
    class SEK_Front_Assets extends SEK_Front_Ajax {
        // Fired in __construct()
        function _schedule_front_assets_printing() {
            // Load Front Assets
            add_action( 'wp_enqueue_scripts', array( $this, 'sek_enqueue_front_assets' ) );

            // Adds `async` and `defer` support for scripts registered or enqueued
            // and for which we've added an attribute with sek_wp_script_add_data( $_hand, 'async', true );
            // inspired from Twentytwenty WP theme
            // @see https://core.trac.wordpress.org/ticket/12009
            add_filter( 'script_loader_tag', array( $this, 'sek_filter_script_loader_tag' ), 10, 2 );

            // added March 2020 when experimenting for https://github.com/presscustomizr/nimble-builder/issues/626
            add_action( 'wp_default_scripts', array( $this, 'sek_maybe_dequeue_jquery_and_schedule_jquery_migrate' ) );

            // Maybe print split module stylesheet inline
            // introduced in march 2020 for https://github.com/presscustomizr/nimble-builder/issues/612
            add_action( 'wp_head', array( $this, 'sek_maybe_print_inline_split_module_stylesheets' ), PHP_INT_MAX  );

            // initialize Nimble front js app
            add_action( 'wp_head', array( $this, 'sek_initialize_front_js_app' ), 0  );

            // Inform Nimble app that jQuery is loaded
            add_action( 'wp_head', array( $this, 'sek_detect_jquery' ), PHP_INT_MAX );

            // Maybe load Font Awesome icons if needed ( sniff first )
            add_action( 'wp_head', array( $this, 'sek_maybe_load_font_awesome_icons' ), PHP_INT_MAX );

            // Maybe print a CSS loader for img and background lazy loaded
            add_action( 'wp_head', array( $this, 'sek_print_style_for_css_loader' ), PHP_INT_MAX );

            // Emit an event when jQuery is detected. 'nimble-jquery-loaded'
            // maybe fetch jQuery from a CDN when dequeued
            add_action( 'wp_footer', array( $this, 'sek_preload_jquery_from_dns' ));
        }//_schedule_front_and_preview_assets_printing




        // hook : 'wp_enqueue_scripts'
        function sek_enqueue_front_assets() {
            Nimble_Manager()->big_module_stylesheet_map = [
                'czr_quote_module' => 'quote-module',
                'czr_icon_module' => 'icon-module',
                'czr_img_slider_module' => 'img-slider-module-with-swiper',
                'czr_accordion_module' => 'accordion-module',
                'czr_menu_module' => 'menu-module',
                'czr_post_grid_module' => 'post-grid-module',
                'czr_simple_form_module' => 'simple-form-module'
            ];

            // do we have local or global sections to render in this page ?
            // see https://github.com/presscustomizr/nimble-builder/issues/586
            // we know the skope_id because 'wp' has been fired
            $has_local_sections = sek_local_skope_has_nimble_sections( skp_get_skope_id() );
            $has_global_sections = sek_has_global_sections();

            // the light split stylesheet is never used when customizing
            $is_stylesheet_split_for_performance = !skp_is_customizing() && sek_use_split_stylesheets_on_front();
            $is_inline_stylesheets_for_performance = $is_stylesheet_split_for_performance && sek_inline_module_stylesheets_on_front();

            $main_stylesheet_name = $is_stylesheet_split_for_performance ? 'sek-base-light' : 'sek-base';

            // Always load the base Nimble style when user logged in so we can display properly the button in the top admin bar.
            if ( is_user_logged_in() || $has_local_sections || $has_global_sections ) {
                $rtl_suffix = is_rtl() ? '-rtl' : '';

                //wp_enqueue_style( 'google-material-icons', '//fonts.googleapis.com/icon?family=Material+Icons', array(), null, 'all' );
                //base custom CSS bootstrap inspired
                wp_enqueue_style(
                    $main_stylesheet_name,
                    sprintf(
                        '%1$s/assets/front/css/%2$s' ,
                        NIMBLE_BASE_URL,
                        sek_is_dev_mode() ? "{$main_stylesheet_name}{$rtl_suffix}.css" : "{$main_stylesheet_name}{$rtl_suffix}.min.css"
                    ),
                    array(),
                    NIMBLE_ASSETS_VERSION,
                    'all'
                );
            }

            // populate the collection of module displayed in current context : local + global
            // introduced march 2020 for https://github.com/presscustomizr/nimble-builder/issues/612
            // formed like :
            // [czr_heading_module] => Array
            //     (
            //         [0] => __nimble__9a02775e86ec
            //         [1] => __nimble__01f1e8d56415
            //         [2] => __nimble__8fc8dac22299
            //         [3] => __nimble__b71c69fd674d
            //         [4] => __nimble__b74a63e1dc57
            //         [5] => __nimble__ca13a73ca586
            //         [6] => __nimble__e66b407f0f2b
            //         [7] => __nimble__7d6526ab1812
            //     )

            // [czr_img_slider_module] => Array
            //     (
            //         [0] => __nimble__3a38fe3587b2
            //     )

            // [czr_accordion_module] => Array
            //     (
            //         [0] => __nimble__ec3d7956fe17
            //     )

            // [czr_social_icons_module] => Array
            //     (
            //         [0] => __nimble__c1526193134e
            //     )
            $contextually_active_modules = sek_get_collection_of_contextually_active_modules();

            //sek_error_log('$contextually_active_modules ?', $contextually_active_modules );

            // We don't need Nimble Builder assets when no local or global sections have been created
            // see https://github.com/presscustomizr/nimble-builder/issues/586
            if ( !$has_local_sections && !$has_global_sections )
              return;

            // SPLIT STYLESHEETS
            // introduced march 2020 for https://github.com/presscustomizr/nimble-builder/issues/612
            // if the module stylesheets are inline, see wp_head action
            if ( !skp_is_customizing() && $is_stylesheet_split_for_performance && !$is_inline_stylesheets_for_performance) {
                // loop on the map module type (candidates for split) => stylesheet file name
                foreach (Nimble_Manager()->big_module_stylesheet_map as $module_type => $stylesheet_name ) {
                    if ( !array_key_exists($module_type , $contextually_active_modules ) )
                      continue;

                    wp_enqueue_style(
                        $module_type,
                        sprintf( '%1$s%2$s%3$s',
                            NIMBLE_BASE_URL . '/assets/front/css/modules/',
                            $stylesheet_name,
                            sek_is_dev_mode() ? '.css' : '.min.css'
                        ),
                        array( $main_stylesheet_name ),
                        NIMBLE_ASSETS_VERSION,
                        $media = 'all'
                    );
                }
            }

            // wp_register_script(
            //     'sek-front-fmk-js',
            //     NIMBLE_BASE_URL . '/assets/front/js/_front_js_fmk.js',
            //     array( 'jquery', 'underscore'),
            //     time(),
            //     true
            // );
            wp_enqueue_script(
                'sek-main-js',
                sek_is_dev_mode() ? NIMBLE_BASE_URL . '/assets/front/js/ccat-nimble-front.js' : NIMBLE_BASE_URL . '/assets/front/js/ccat-nimble-front.min.js',
                //array( 'jquery', 'underscore'),
                // october 2018 => underscore is concatenated in the main front js file.
                ( !skp_is_customizing() && sek_is_jquery_replaced() ) ? array() : array( 'jquery'),
                NIMBLE_ASSETS_VERSION,
                true
            );
            // added for https://github.com/presscustomizr/nimble-builder/issues/583
            // not added when customizing
            sek_wp_script_add_data( 'sek-main-js', 'async', true );




            /* ------------------------------------------------------------------------- *
             *  LIGHT BOX WITH MAGNIFIC POPUP
             /* ------------------------------------------------------------------------- */
            // Magnific Popup is loaded when needed only
            // front : Load if js not loaded dynamically + we detect the need for the script
            // customizing : load if not loaded dynamically
            if ( ( !sek_load_front_assets_on_scroll() && sek_front_needs_magnific_popup() ) || skp_is_customizing() ) {
                wp_enqueue_style(
                    'czr-magnific-popup',
                    NIMBLE_BASE_URL . '/assets/front/css/libs/magnific-popup.min.css',
                    array(),
                    NIMBLE_ASSETS_VERSION,
                    $media = 'all'
                );
                wp_enqueue_script(
                    'sek-magnific-popups',
                    sek_is_dev_mode() ? NIMBLE_BASE_URL . '/assets/front/js/libs/jquery-magnific-popup.js' : NIMBLE_BASE_URL . '/assets/front/js/libs/jquery-magnific-popup.min.js',
                    ( !skp_is_customizing() && sek_is_jquery_replaced() ) ? array() : array( 'jquery'),
                    NIMBLE_ASSETS_VERSION,
                    true
                );
                // not added when customizing
                sek_wp_script_add_data( 'sek-magnific-popups', 'async', true );
            }


            /* ------------------------------------------------------------------------- *
             *  SWIPER FOR SLIDERS
             /* ------------------------------------------------------------------------- */
            // SWIPER JS LIB + MODULE SCRIPT
            // Swiper js + css is needed for the czr_img_slider_module
            // front : Load if js not loaded dynamically + we detect the need for the script
            // customizing : load if not loaded dynamically
            if ( ( !sek_load_front_assets_on_scroll() && array_key_exists('czr_img_slider_module' , $contextually_active_modules) ) || skp_is_customizing() ) {
                // march 2020 : when using split stylesheet, swiper css is already included in assets/front/css/modules/img-slider-module-with-swiper.css
                // so we don't need to enqueue it
                // added for https://github.com/presscustomizr/nimble-builder/issues/612
                if ( !$is_stylesheet_split_for_performance ) {
                      wp_enqueue_style(
                          'czr-swiper',
                          sek_is_dev_mode() ? NIMBLE_BASE_URL . '/assets/front/css/libs/swiper.css' : NIMBLE_BASE_URL . '/assets/front/css/libs/swiper.min.css',
                          array(),
                          NIMBLE_ASSETS_VERSION,
                          $media = 'all'
                      );
                }

                wp_register_script(
                  'czr-swiper',
                  sek_is_dev_mode() ? NIMBLE_BASE_URL . '/assets/front/js/libs/swiper.js' : NIMBLE_BASE_URL . '/assets/front/js/libs/swiper.min.js',
                  array(),
                  NIMBLE_ASSETS_VERSION,
                  true
                );
                // not added when customizing
                sek_wp_script_add_data( 'czr-swiper', 'async', true );
                wp_enqueue_script(
                    'sek-slider-module',
                    sek_is_dev_mode() ? NIMBLE_BASE_URL . '/assets/front/js/prod-front-simple-slider-module.js' : NIMBLE_BASE_URL . '/assets/front/js/prod-front-simple-slider-module.min.js',
                    array('czr-swiper'),
                    NIMBLE_ASSETS_VERSION,
                    true
                );
                // not added when customizing
                sek_wp_script_add_data( 'sek-slider-module', 'async', true );
            }



            /* ------------------------------------------------------------------------- *
             *  MENU MODULE
             /* ------------------------------------------------------------------------- */
            // front : Load if js not loaded dynamically + we detect the need for the script
            // customizing : load if not loaded dynamically
            if ( ( !sek_load_front_assets_on_scroll() && array_key_exists('czr_menu_module' , $contextually_active_modules ) ) || skp_is_customizing() ) {
                wp_enqueue_script(
                    'sek-menu-module',
                    sek_is_dev_mode() ? NIMBLE_BASE_URL . '/assets/front/js/prod-front-menu-module.js' : NIMBLE_BASE_URL . '/assets/front/js/prod-front-menu-module.min.js',
                    array(),
                    NIMBLE_ASSETS_VERSION,
                    true
                );
                // not added when customizing
                sek_wp_script_add_data( 'sek-menu-module', 'async', true );
            }

            /* ------------------------------------------------------------------------- *
             *  MENU MODULE
             /* ------------------------------------------------------------------------- */
            // front : Load if js not loaded dynamically + we detect the need for the script
            // customizing : load if not loaded dynamically
            if ( ( !sek_load_front_assets_on_scroll() && sek_front_needs_video_bg() ) || skp_is_customizing() ) {
                wp_enqueue_script(
                    'sek-video-bg',
                    sek_is_dev_mode() ? NIMBLE_BASE_URL . '/assets/front/js/prod-front-video-bg.js' : NIMBLE_BASE_URL . '/assets/front/js/prod-front-video-bg.min.js',
                    array(),
                    NIMBLE_ASSETS_VERSION,
                    true
                );
                // not added when customizing
                sek_wp_script_add_data( 'sek-video-bg', 'async', true );
            }


            // Google reCAPTCHA
            $global_recaptcha_opts = sek_get_global_option_value('recaptcha');
            $global_recaptcha_opts = is_array( $global_recaptcha_opts ) ? $global_recaptcha_opts : array();

            wp_localize_script(
                'sek-main-js',
                'sekFrontLocalized',
                array(
                    'isDevMode' => sek_is_dev_mode(),
                    //'ajaxUrl' => admin_url( 'admin-ajax.php' ),
                    'frontNonce' => array( 'id' => 'SEKFrontNonce', 'handle' => wp_create_nonce( 'sek-front-nonce' ) ),
                    'localSeks' => sek_is_debug_mode() ? wp_json_encode( sek_get_skoped_seks() ) : '',
                    'globalSeks' => sek_is_debug_mode() ? wp_json_encode( sek_get_skoped_seks( NIMBLE_GLOBAL_SKOPE_ID ) ) : '',
                    'skope_id' => skp_get_skope_id(), //added for debugging purposes
                    'recaptcha_public_key' => !empty ( $global_recaptcha_opts['public_key'] ) ? $global_recaptcha_opts['public_key'] : '',

                    'lazyload_enabled' => sek_is_img_smartload_enabled(),
                    'video_bg_lazyload_enabled' => sek_is_video_bg_lazyload_enabled(),

                    'load_front_module_assets_on_scroll' => sek_load_front_assets_on_scroll(),
                    'load_font_awesome_on_scroll' => sek_load_front_font_awesome_on_scroll(),

                    'assetVersion' => NIMBLE_ASSETS_VERSION,
                    'frontAssetsPath' => NIMBLE_BASE_URL . '/assets/front/',
                    'contextuallyActiveModules' => sek_get_collection_of_contextually_active_modules(),
                    'fontAwesomeAlreadyEnqueued' => wp_style_is('customizr-fa', 'enqueued') || wp_style_is('hueman-font-awesome', 'enqueued')
                )
            );

        }//sek_enqueue_front_assets


        // hook : 'wp_head:PHP_INT_MAX'
        // Feb 2020 => now check if Hueman or Customizr has already loaded font awesome
        // @see https://github.com/presscustomizr/nimble-builder/issues/600
        function sek_maybe_load_font_awesome_icons() {
            // if active theme is Hueman or Customizr, Font Awesome may already been enqueued.
            // asset handle for Customizr => 'customizr-fa'
            // asset handle for Hueman => 'hueman-font-awesome'
            if ( wp_style_is('customizr-fa', 'enqueued') || wp_style_is('hueman-font-awesome', 'enqueued') )
              return;

            // Font awesome is always loaded when customizing ( see ::sek_schedule_customize_preview_assets )
            // when not customizing, sek_front_needs_font_awesome() sniffs if the collection include a module using an icon
            if ( !skp_is_customizing() && sek_front_needs_font_awesome() && !sek_load_front_font_awesome_on_scroll() ) {
                // wp_enqueue_style(
                //     'czr-font-awesome',
                //     NIMBLE_BASE_URL . '/assets/front/fonts/css/fontawesome-all.min.css',
                //     array(),
                //     NIMBLE_ASSETS_VERSION,
                //     $media = 'all'
                // );

                ?>
                <script id="nimble-load-fa">!function(){var e=document.createElement("link");e.setAttribute("href",'<?php echo NIMBLE_BASE_URL . "/assets/front/fonts/css/fontawesome-all.min.css"; ?>'),e.setAttribute("rel",nb_.assetPreloadSupported()?"preload":"stylesheet"),e.setAttribute("id","czr-font-awesome"),e.setAttribute("as","style"),e.onload=function(){this.onload=null,nb_.assetPreloadSupported()&&(this.rel="stylesheet");var e=document.getElementById("nimble-load-fa");e.parentNode.removeChild(e)},document.getElementsByTagName("head")[0].appendChild(e)}();</script>
                <?php
            }
        }


        //@'wp_head'PHP_INT_MAX
        function sek_print_style_for_css_loader() {
          // if ( !sek_is_img_smartload_enabled() || skp_is_customizing() )
          //   return;
          ?>
          <style id="nb-lazyload-css-loader">@-webkit-keyframes sek-mr-loader{0%{-webkit-transform:scale(.1);transform:scale(.1);opacity:1}70%{-webkit-transform:scale(1);transform:scale(1);opacity:.7}100%{opacity:0}}@keyframes sek-mr-loader{0%{-webkit-transform:scale(.1);transform:scale(.1);opacity:1}70%{-webkit-transform:scale(1);transform:scale(1);opacity:.7}100%{opacity:0}}.sek-css-loader{width:50px;height:50px;position:absolute;-webkit-transform:translate3d(-50%,-50%,0);transform:translate3d(-50%,-50%,0);top:50%;left:50%}.csstransforms3d .sek-css-loader{display:block}.sek-mr-loader>div:nth-child(0){-webkit-animation-delay:-.8s;animation-delay:-.8s}.sek-mr-loader>div:nth-child(1){-webkit-animation-delay:-.6s;animation-delay:-.6s}.sek-mr-loader>div:nth-child(2){-webkit-animation-delay:-.4s;animation-delay:-.4s}.sek-mr-loader>div:nth-child(3){-webkit-animation-delay:-.2s;animation-delay:-.2s}.sek-mr-loader>div{-webkit-animation-fill-mode:both;animation-fill-mode:both;position:absolute;top:0;left:0;width:100%;height:100%;border-radius:100%;border:2px solid #777;-webkit-animation:sek-mr-loader 1.25s 0s infinite cubic-bezier(.21,.53,.56,.8);animation:sek-mr-loader 1.25s 0s infinite cubic-bezier(.21,.53,.56,.8)}.white-loader>.sek-mr-loader>div{border:2px solid #fff}</style>
          <?php
        }


        // @'wp_head'0
        // Loading sequence :
        // 1) window.nb_ utils starts being populated
        // 2) 'nimble-jquery-loaded' => fired in footer when jQuery is defined <= window.nb_ utils is completed with jQuery dependant helper properties and methods
        // 3) 'nimble-app-ready' => fired in footer on 'nimble-jquery-loaded' <= all module scripts are fired on this event
        // 4) 'nimble-magnific-popup-loaded', ... are emitted in each script files
        function sek_initialize_front_js_app() {
            ?>
            <script id="nimble-app-init">window.nb_={},function(e,n){window.nb_={isArray:function(e){return Array.isArray(e)||"[object Array]"===toString.call(e)},inArray:function(e,n){return!(!nb_.isArray(e)||nb_.isUndefined(n))&&e.indexOf(n)>-1},isUndefined:function(e){return void 0===e},isObject:function(e){var n=typeof e;return"function"===n||"object"===n&&!!e},errorLog:function(){nb_.isUndefined(console)||"function"!=typeof window.console.log||console.log.apply(console,arguments)},browserIs:function(e){var n=!1,t=!!document.documentMode;switch(e){case"safari":n=/constructor/i.test(window.HTMLElement)||"[object SafariRemoteNotification]"===(!window.safari||"undefined"!=typeof safari&&safari.pushNotification).toString();break;case"firefox":n="undefined"!=typeof InstallTrigger;break;case"IE":n=t;break;case"edge":n=!t&&!!window.StyleMedia;break;case"chrome":n=!(!window.chrome||!window.chrome.webstore&&!window.chrome.runtime)}return n},assetPreloadSupported:function(){return!nb_.browserIs("firefox")&&!nb_.browserIs("IE")&&!nb_.browserIs("edge")},listenTo:function(e,n){var t={"nimble-jquery-loaded":function(){return"undefined"!=typeof jQuery},"nimble-app-ready":function(){return void 0!==window.nb_&&nb_.wasListenedTo("nimble-jquery-loaded")},"nimble-magnific-popup-loaded":function(){return"undefined"!=typeof jQuery&&void 0!==jQuery.fn.magnificPopup},"nimble-swiper-script-loaded":function(){return void 0!==window.Swiper}},o=function(o){nb_.isUndefined(t[e])||!1!==t[e]()?(n(),nb_.eventsListenedTo.push(e)):nb_.errorLog("Nimble error => an event callback could not be fired because conditions not met => ",e,nb_.eventsListenedTo)};"function"==typeof n?nb_.wasEmitted(e)?o():document.addEventListener(e,o):nb_.errorLog("Nimble error => listenTo func param is not a function for event => ",e)},eventsEmitted:[],eventsListenedTo:[],emit:function(e){var n=document.createEvent("Event");n.initEvent(e,!0,!0),document.dispatchEvent(n),nb_.eventsEmitted.push(e)},wasListenedTo:function(e){return"string"==typeof e&&nb_.inArray(nb_.eventsListenedTo,e)},wasEmitted:function(e){return"string"==typeof e&&nb_.inArray(nb_.eventsEmitted,e)},isInScreen:function(e,n){var t=window.pageYOffset||document.documentElement.scrollTop,o=t+window.innerHeight,i=e.offsetTop,r=n||0;return i+e.clientHeight>=t-r&&i<=o+r},isCustomizing:function(){return!1},isLazyLoadEnabled:function(){return!nb_.isCustomizing()&&!1}},window.NodeList&&!NodeList.prototype.forEach&&(NodeList.prototype.forEach=function(e,n){n=n||window;for(var t=0;t<this.length;t++)e.call(n,this[t],t,this)});var t=!1;_revealBGImages=function(){if(!t){t=!0;var e=document.querySelectorAll("div.sek-has-bg");if(nb_.isObject(e)&&e.length>0){e.forEach(function(e){nb_.isObject(e)&&((!nb_.isLazyLoadEnabled()||nb_.isInScreen(e)&&nb_.isLazyLoadEnabled())&&e.getAttribute("data-sek-src")&&(e.setAttribute("style",'background-image:url("'+e.getAttribute("data-sek-src")+'")'),e.className+=" smartload-skip",e.querySelectorAll(".sek-css-loader").forEach(function(e){nb_.isObject(e)&&e.parentNode.removeChild(e)})))})}}},document.addEventListener("DOMContentLoaded",_revealBGImages),window.addEventListener("load",_revealBGImages)}(window,document);</script>
            <?php
        }

        // @'wp_footer'PHP_INT_MAX
        // introduced for https://github.com/presscustomizr/nimble-builder/issues/626
        function sek_preload_jquery_from_dns() {
            if( sek_is_jquery_replaced() && !skp_is_customizing() ) {
            ?>
            <script id="nimble-load-jquery">setTimeout(function(){var e=function(){var e=document.createElement("script");e.setAttribute("src","<?php echo NIMBLE_JQUERY_LATEST_CDN_URL; ?>"),e.setAttribute("id","<?php echo NIMBLE_JQUERY_ID; ?>"),e.setAttribute("defer","defer"),document.getElementsByTagName("head")[0].appendChild(e);var t=document.getElementById("nimble-load-jquery");t.parentNode.removeChild(t)};if(nb_.assetPreloadSupported()){var t=document.createElement("link");t.setAttribute("href","<?php echo NIMBLE_JQUERY_LATEST_CDN_URL; ?>"),t.setAttribute("rel","preload"),t.setAttribute("id","<?php echo NIMBLE_JQUERY_ID; ?>"),t.setAttribute("as","script"),t.onload=function(){this.onload=null,this.rel="script",e()},document.getElementsByTagName("head")[0].appendChild(t)}else e()},1e3);</script>
            <?php
            }
        }//sek_preload_jquery_from_dns()


        // @'wp_head'PHP_INT_MAX
        // introduced for https://github.com/presscustomizr/nimble-builder/issues/626
        // jQuery can potentially be loaded async, so let's react to its load or the presence of window.jQuery
        function sek_detect_jquery() {
            ?>
            <script id="nimble-detect-jquery">!function(){var e=function(){var e="nimble-jquery-loaded";nb_.wasEmitted(e)||nb_.emit(e)},n=function(t){t=t||0,void 0!==window.jQuery?e():t<30?setTimeout(function(){n(++t)},200):alert("Nimble Builder problem : jQuery.js was not detected on your website")},t=document.getElementById("<?php echo NIMBLE_JQUERY_ID; ?>");t&&t.addEventListener("load",function(){e()}),n()}();</script>
            <?php
        }

        // @'wp_default_scripts'
        // see https://wordpress.stackexchange.com/questions/291700/how-to-stop-jquery-migrate-manually
        // https://stackoverflow.com/questions/18421404/how-do-i-stop-wordpress-loading-jquery-and-jquery-migrate#25977181
        function sek_maybe_dequeue_jquery_and_schedule_jquery_migrate( &$scripts ) {
            if ( !skp_is_customizing() && !is_admin() && sek_is_jquery_replaced() && !empty( $scripts->registered['jquery'] ) ) {
                $scripts->registered['jquery']->deps = array_diff(
                    $scripts->registered['jquery']->deps,
                    [ 'jquery-migrate' ]
                );
                $scripts->remove( 'jquery');
                //$scripts->add( 'jquery', false, array( 'jquery-core' ), '1.2.1' );
            }
            // when jquery is loaded async and not replaced we need to dequeue jquery-migrate and load it dynamically on 'nimble-jquery-loaded'
            if ( !skp_is_customizing() && sek_load_jquery_async() && !sek_is_jquery_replaced() ) {
                $scripts->registered['jquery']->deps = array_diff(
                    $scripts->registered['jquery']->deps,
                    [ 'jquery-migrate' ]
                );
                $scripts->remove('jquery-migrate');
                // Inform Nimble app that jQuery is loaded
                add_action( 'wp_head', array( $this, 'sek_maybe_inject_jquery_migrate' ), 10 );
            }
        }

        //@'wp_head'0  when sek_load_jquery_async() && !skp_is_customizing()
        function sek_maybe_inject_jquery_migrate() {
            global $wp_scripts;
            if ( isset($wp_scripts->registered['jquery-migrate']) )
              return;
            ?>
            <script id="nimble-load-jquery-migrate">!function(){var e=function(){var e=document.createElement("script");e.setAttribute("src","<?php echo NIMBLE_JQUERY_MIGRATE_URL; ?>"),e.setAttribute("id","nb-query-migrate"),e.setAttribute("defer","defer"),document.getElementsByTagName("head")[0].appendChild(e);var t=document.getElementById("nimble-load-jquery-migrate");t.parentNode.removeChild(t)};nb_.listenTo("nimble-jquery-loaded",function(){if(nb_.assetPreloadSupported()){var t=document.createElement("link");t.setAttribute("href","<?php echo NIMBLE_JQUERY_MIGRATE_URL; ?>"),t.setAttribute("rel","preload"),t.setAttribute("id","nb-query-migrate"),t.setAttribute("as","script"),t.onload=function(){this.onload=null,this.rel="script",e()},document.getElementsByTagName("head")[0].appendChild(t)}else e()})}();</script>
            <?php
        }





        // hook : wp_head@PHP_INT_MAX
        // introduced in march 2020 for https://github.com/presscustomizr/nimble-builder/issues/612
        function sek_maybe_print_inline_split_module_stylesheets() {
            $is_stylesheet_split_for_performance = !skp_is_customizing() && sek_use_split_stylesheets_on_front();
            $is_inline_stylesheets_for_performance= !skp_is_customizing() && $is_stylesheet_split_for_performance && sek_inline_module_stylesheets_on_front();
            if ( !$is_inline_stylesheets_for_performance)
              return;
            // css assets are always enqueued when customizing
            global $wp_filesystem;
            $contextually_active_modules = sek_get_collection_of_contextually_active_modules();
            // loop on the map module type (candidates for split) => stylesheet file name
            foreach (Nimble_Manager()->big_module_stylesheet_map as $module_type => $stylesheet_name ) {
                if ( !array_key_exists($module_type , $contextually_active_modules) )
                  continue;
                $uri = NIMBLE_BASE_PATH . '/assets/front/css/modules/' . $stylesheet_name .'.min.css';
                //sek_error_log( __CLASS__ . '::' . __FUNCTION__ . ' SOOO ? => ' . $this->uri . $wp_filesystem->exists( $this->uri ), empty( $file_content ) );
                if ( $wp_filesystem->exists( $uri ) && $wp_filesystem->is_readable( $uri ) ) {
                    $file_content = $wp_filesystem->get_contents( $uri );
                    printf( '<style id="%1$s-stylesheet" type="text/css" media="all">%2$s</style>', $stylesheet_name, $file_content );
                }
            }
        }





        /**
         * Fired @'script_loader_tag'
         * Adds async/defer attributes to enqueued / registered scripts.
         * works with sek_wp_script_add_data()
         * see https://html.spec.whatwg.org/multipage/scripting.html#attr-script-defer
         * based on a solution found in Twentytwenty
         * and for which we've added an attribute with sek_wp_script_add_data( $_hand, 'async', true );
         * If #12009 lands in WordPress, this function can no-op since it would be handled in core.
         *
         * @param string $tag    The script tag.
         * @param string $handle The script handle.
         * @return string Script HTML string.
        */
        public function sek_filter_script_loader_tag( $tag, $handle ) {
            // adds an id to jquery core so we can detect when it's loaded
            if ( 'jquery-core' === $handle ) {
                // tag is a string and looks like <script src='http://customizr-dev.test/wp-includes/js/jquery/jquery-migrate.js?ver=1.4.1'></script>
                $tag = str_replace('src=', 'id="'.NIMBLE_JQUERY_ID.'" src=', $tag);
                if ( sek_load_jquery_async() && !skp_is_customizing() ) {
                    $tag = str_replace('src=', 'async src=', $tag);
                }
            }

            if ( skp_is_customizing() )
              return $tag;

            foreach ( [ 'async', 'defer' ] as $attr ) {
              if ( !wp_scripts()->get_data( $handle, $attr ) ) {
                continue;
              }
              // Prevent adding attribute when already added in #12009.
              if ( !preg_match( ":\s$attr(=|>|\s):", $tag ) ) {
                $tag = preg_replace( ':(?=></script>):', " $attr", $tag, 1 );
              }
              // Only allow async or defer, not both.
              break;
            }
            return $tag;
        }
    }//class
endif;
?>