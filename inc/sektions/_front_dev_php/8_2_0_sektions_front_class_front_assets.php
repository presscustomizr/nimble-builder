<?php
if ( !class_exists( 'SEK_Front_Assets' ) ) :
    class SEK_Front_Assets extends SEK_Front_Ajax {
        // Fired in __construct()
        function _schedule_front_assets_printing() {
            // Maybe adds `defer` support for scripts registered or enqueued
            // and for which we've added an attribute with sek_maybe_defer_script( $_hand, 'defer', true );
            // inspired from Twentytwenty WP theme
            // @see https://core.trac.wordpress.org/ticket/12009
            add_filter( 'script_loader_tag', array( $this, 'sek_filter_script_loader_tag' ), 10, 2 );

            // Load Front Assets
            add_action( 'wp_enqueue_scripts', array( $this, 'sek_enqueue_front_assets' ) );

            // added March 2020 when experimenting for https://github.com/presscustomizr/nimble-builder/issues/626
            add_action( 'wp_default_scripts', array( $this, 'sek_maybe_dequeue_jquery_and_schedule_jquery_migrate' ) );

            // Maybe print split module stylesheet inline
            // introduced in march 2020 for https://github.com/presscustomizr/nimble-builder/issues/612
            add_action( 'wp_head', array( $this, 'sek_maybe_print_inline_split_module_stylesheets' ), PHP_INT_MAX  );

            // initialize Nimble front js app
            add_action( 'wp_head', array( $this, 'sek_initialize_front_js_app' ), 0  );

            // Inform Nimble app that jQuery is loaded
            add_action( 'wp_head', array( $this, 'sek_detect_jquery' ), PHP_INT_MAX );

            // Maybe print a CSS loader for img and background lazy loaded
            add_action( 'wp_head', array( $this, 'sek_print_style_for_css_loader' ), PHP_INT_MAX );

            // Emit an event when jQuery is detected. 'nb-jquery-loaded'
            // maybe fetch jQuery from a CDN when dequeued
            add_action( 'wp_footer', array( $this, 'sek_preload_jquery_from_dns' ));

            // Maybe preload Font Awesome icons when really needed ( sniff first ) + nb_.listenTo('nb-needs-fa')
            add_action( 'wp_footer', array( $this, 'sek_maybe_preload_font_awesome' ), PHP_INT_MAX );

            add_action( 'wp_head', function() {
                ?>
                <script>
                var _testload = function() {
                    console.log('THIS LAZY LOAD TEST?', this, this.getAttribute('href') );
                    var script = document.createElement('script');
                    script.setAttribute('src', this.getAttribute('href'));
                    //script.setAttribute('id', 'nb-query-migrate');
                    //script.setAttribute('defer', 'defer');//https://html.spec.whatwg.org/multipage/scripting.html#attr-script-defer
                    document.getElementsByTagName('head')[0].appendChild(script);

                    console.log('MERDE LAZY LOAD TEST');
                };
                </script>
                <link id="nb-smartload" rel="preload" as="script" href="<?php echo NIMBLE_BASE_URL . '/assets/front/js/libs/nimble-smartload.js?' . NIMBLE_ASSETS_VERSION; ?>" onload="window._testload.call(this);" onerror="nb_.errorLog('error loading smartload'">

                <?php
            }, 10 );
        }//_schedule_front_and_preview_assets_printing




        // hook : 'wp_enqueue_scripts'
        function sek_enqueue_front_assets() {
            /* ------------------------------------------------------------------------- *
             *  MAIN STYLESHEET
            /* ------------------------------------------------------------------------- */
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


            /* ------------------------------------------------------------------------- *
             *  STOP HERE IF NOTHING TO PRINT
            /* ------------------------------------------------------------------------- */
            // We don't need Nimble Builder assets when no local or global sections have been created
            // see https://github.com/presscustomizr/nimble-builder/issues/586
            if ( !$has_local_sections && !$has_global_sections )
              return;


            /* ------------------------------------------------------------------------- *
             *  MODULE PARTIAL STYLESHEETS
            /* ------------------------------------------------------------------------- */
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

             Nimble_Manager()->big_module_stylesheet_map = [
                'czr_quote_module' => 'quote-module',
                'czr_icon_module' => 'icon-module',
                'czr_img_slider_module' => 'img-slider-module-with-swiper',
                'czr_accordion_module' => 'accordion-module',
                'czr_menu_module' => 'menu-module',
                'czr_post_grid_module' => 'post-grid-module',
                'czr_simple_form_module' => 'simple-form-module'
            ];
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



            /* ------------------------------------------------------------------------- *
             *  FONT AWESOME STYLESHEET
            /* ------------------------------------------------------------------------- */
            if ( sek_front_needs_font_awesome() && !sek_preload_font_awesome() ) {
                wp_enqueue_style(
                    'czr-font-awesome',
                    NIMBLE_BASE_URL . '/assets/front/fonts/css/fontawesome-all.min.css',
                    array(),
                    NIMBLE_ASSETS_VERSION,
                    $media = 'all'
                );
            }


            /* ------------------------------------------------------------------------- *
             *  MAIN SCRIPT
             /* ------------------------------------------------------------------------- */
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
            sek_maybe_defer_script( 'sek-main-js', 'defer', true );




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
                sek_maybe_defer_script( 'sek-magnific-popups', 'defer', true );
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
                sek_maybe_defer_script( 'czr-swiper', 'defer', true );
                wp_enqueue_script(
                    'sek-slider-module',
                    sek_is_dev_mode() ? NIMBLE_BASE_URL . '/assets/front/js/prod-front-simple-slider-module.js' : NIMBLE_BASE_URL . '/assets/front/js/prod-front-simple-slider-module.min.js',
                    array('czr-swiper'),
                    NIMBLE_ASSETS_VERSION,
                    true
                );
                // not added when customizing
                sek_maybe_defer_script( 'sek-slider-module', 'defer', true );
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
                sek_maybe_defer_script( 'sek-menu-module', 'defer', true );
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
                sek_maybe_defer_script( 'sek-video-bg', 'defer', true );
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
        function sek_maybe_preload_font_awesome() {
            // if active theme is Hueman or Customizr, Font Awesome may already been enqueued.
            // asset handle for Customizr => 'customizr-fa'
            // asset handle for Hueman => 'hueman-font-awesome'
            if ( wp_style_is('customizr-fa', 'enqueued') || wp_style_is('hueman-font-awesome', 'enqueued') )
              return;

            // Font awesome is always loaded when customizing
            // when not customizing, sek_front_needs_font_awesome() sniffs if the collection include a module using an icon
            if ( skp_is_customizing() || ( sek_preload_font_awesome() && sek_front_needs_font_awesome() ) ) {
                ?>
                <script id="nimble-load-fa">nb_.listenTo('nb-needs-fa', function() { nb_.preloadAsset( { id : 'czr-font-awesome', as : 'style', href : '<?php echo NIMBLE_BASE_URL . "/assets/front/fonts/css/fontawesome-all.min.css?" . NIMBLE_ASSETS_VERSION; ?>', onEvent : 'nb-docready', scriptEl : document.currentScript } );});</script>
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
        // 2) 'nb-jquery-loaded' => fired in footer when jQuery is defined <= window.nb_ utils is completed with jQuery dependant helper properties and methods
        // 3) 'nb-app-ready' => fired in footer on 'nb-jquery-loaded' <= all module scripts are fired on this event
        // 4) 'nb-jmp-parsed', ... are emitted in each script files
        function sek_initialize_front_js_app() {
            ?>
            <script id="nimble-app-init">window.nb_ = {};
// Jquery agnostic
(function(w, d){
    window.nb_ = {
        isArray : function(obj) {
            return Array.isArray(obj) || toString.call(obj) === '[object Array]';
        },
        inArray : function(obj, value) {
          if ( !nb_.isArray(obj) || nb_.isUndefined(value) )
            return false;
          return obj.indexOf(value) > -1;
        },
        isUndefined : function(obj) {
          return obj === void 0;
        },
        isObject : function(obj) {
          var type = typeof obj;
          return type === 'function' || type === 'object' && !!obj;
        },
        // safe console log for
        errorLog : function() {
            //fix for IE, because console is only defined when in F12 debugging mode in IE
            if ( nb_.isUndefined( console ) || 'function' != typeof window.console.log )
              return;
            console.log.apply(console,arguments);
        },
        hasPreloadSupport : function( browser ) {
          var link = document.createElement('link');
          var relList = link.relList;
          if (!relList || !relList.supports)
            return false;
          return relList.supports('preload');
        },
        listenTo : function( evt, func ) {
            var canWeFireCallbackForEvent = {
                'nb-jquery-loaded' : function() { return typeof undefined !== typeof jQuery; },
                'nb-app-ready' : function() { return ( typeof undefined !== typeof window.nb_ ) && nb_.wasListenedTo('nb-jquery-loaded'); },
                'nb-jmp-parsed' : function() { return ( typeof undefined !== typeof jQuery ) && ( typeof undefined !== typeof jQuery.fn.magnificPopup ); },
                'nb-main-swiper-parsed' : function() { return typeof undefined !== typeof window.Swiper; }
            };
            // e is the event object passed
            // it is possible to add params but we need to use new CustomEvent with a polyfill for IE
            // see : https://stackoverflow.com/questions/18613456/trigger-event-with-parameters
            var _executeAndLog = function(e) {
                if ( !nb_.isUndefined(canWeFireCallbackForEvent[evt]) && false === canWeFireCallbackForEvent[evt]() ) {
                    nb_.errorLog('Nimble error => an event callback could not be fired because conditions not met => ', evt, nb_.eventsListenedTo );
                    return;
                }
                func();
                // console.log('LISTENED TO', evt );
                // store it, so if the event has been emitted before the listener is fired, we know it's been emitted
                nb_.eventsListenedTo.push(evt);
            };
            // if the event requires a condition to be executed let's check it
            // if the event has alreay been listened to, let's fire the func, otherwise wait for its emission
            if ( 'function' === typeof func ) {
                if ( nb_.wasEmitted(evt) ) {
                    _executeAndLog();
                } else {
                    document.addEventListener(evt,_executeAndLog);
                }
            } else {
              nb_.errorLog('Nimble error => listenTo func param is not a function for event => ', evt );
            }
        },
        eventsEmitted : [],
        eventsListenedTo : [],
        emit : function(evt) {
            // it is possible to add params when dispatching the event, but we need to use new CustomEvent with a polyfill for IE
            // see : https://stackoverflow.com/questions/18613456/trigger-event-with-parameters
            var _evt = document.createEvent('Event');
            _evt.initEvent(evt, true, true); //can bubble, and is cancellable
            document.dispatchEvent(_evt);
            nb_.eventsEmitted.push(evt);
        },
        wasListenedTo : function( evt ) {
            return ('string' === typeof evt) && nb_.inArray( nb_.eventsListenedTo, evt );
        },
        wasEmitted : function( evt ) {
            return ('string' === typeof evt) && nb_.inArray( nb_.eventsEmitted, evt );
        },
        isInScreen : function(el, threshold) {
            var wt = window.pageYOffset || document.documentElement.scrollTop,
                wb = wt + window.innerHeight,
                it  = el.offsetTop,
                ib  = it + el.clientHeight,
                th = threshold || 0;

            return ib >= wt - th && it <= wb + th;
        },
        isCustomizing : function() {
            return true == '<?php echo skp_is_customizing(); ?>';
        },
        isLazyLoadEnabled : function() {
            return !nb_.isCustomizing() && true == '<?php echo sek_is_img_smartload_enabled(); ?>';
        },
        // params = {id, as, href, onEvent, scriptEl }
        preloadAsset : function(params) {
            params = params || {};
            var link = document.createElement('link'),
                rel = 'style' === params.as ? 'stylesheet' : 'script';
            link.setAttribute('href', params.href);
            link.setAttribute('rel', nb_.hasPreloadSupport() ? 'preload' : rel );
            link.setAttribute('id', params.id );
            link.setAttribute('as', params.as);
            link.onload = function() {
                this.onload=null;
                // if no preload support ( firefox, IE as of March 2020 ), final rel attribute has been set before, no need to set it now
                if ( !nb_.hasPreloadSupport() )
                  return;
                if ( params.onEvent ) {
                    nb_.listenTo( params.onEvent, function() { link.rel = rel; } );
                } else {
                    this.rel = rel;
                }
            };
            link.onerror = function() {
                nb_.errorLog('Nimble preloadAsset error', er, params );
            }
            document.getElementsByTagName('head')[0].appendChild(link);
            // clean the script element from which preload has been requested
            if ( params.scriptEl ) {
                params.scriptEl.parentNode.removeChild(params.scriptEl);
            }
        }
    };//window.nb_

    // forEach not supported by IE
    // This polyfill adds compatibility to all Browsers supporting ES5:
    if (window.NodeList && !NodeList.prototype.forEach) {
        NodeList.prototype.forEach = function (callback, thisArg) {
            thisArg = thisArg || window;
            for (var i = 0; i < this.length; i++) {
                callback.call(thisArg, this[i], i, this);
            }
        };
    }

    // handle bg images when lazyloading off
    nb_.listenTo('nb-docready', function() {
        var matches = document.querySelectorAll('div.sek-has-bg');
        if ( !nb_.isObject( matches ) || matches.length < 1 )
          return;
        var imgSrc, isInScreen = false;
        matches.forEach( function(el) {
            if ( !nb_.isObject(el) )
              return;

            if ( !nb_.isLazyLoadEnabled() || ( nb_.isInScreen(el) && nb_.isLazyLoadEnabled() ) ) {
                imgSrc = el.getAttribute('data-sek-src');
                if ( imgSrc ) {
                    el.setAttribute( 'style', 'background-image:url("' + el.getAttribute('data-sek-src') +'")' );
                    el.className += ' smartload-skip';//<= so we don't parse it twice when lazyload is active
                    // clean css loader
                    var css_loaders = el.querySelectorAll('.sek-css-loader');
                    css_loaders.forEach( function(_cssl) {
                        if ( nb_.isObject(_cssl) ) {
                            _cssl.parentNode.removeChild(_cssl);
                        }
                    });
                }
            }
        });
    });

    // Add an internal document ready listener the jquery way
    // Catch cases where $(document).ready() is called
    // after the browser event has already occurred.
    // Support: IE <=9 - 10 only
    // Older IE sometimes signals "interactive" too soon
    if ( document.readyState === "complete" || ( document.readyState !== "loading" && !document.documentElement.doScroll ) ) {
        nb_.emit('nb-docready');
    } else {
        var _docReady = function() {
            nb_.emit('nb-docready');
        };
        // Use the handy event callback
        document.addEventListener( "DOMContentLoaded", _docReady );
        // A fallback to window.onload, that will always work
        window.addEventListener( "load", _docReady );
    }

}(window, document ));
</script>
            <?php
        }

        // @'wp_footer'PHP_INT_MAX
        // introduced for https://github.com/presscustomizr/nimble-builder/issues/626
        function sek_preload_jquery_from_dns() {
            if( sek_is_jquery_replaced() && !skp_is_customizing() ) {
            ?>
            <script id="nimble-load-jquery">setTimeout(function(){var e=function(){var e=document.createElement("script");e.setAttribute("src","<?php echo NIMBLE_JQUERY_LATEST_CDN_URL; ?>"),e.setAttribute("id","<?php echo NIMBLE_JQUERY_ID; ?>"),e.setAttribute("defer","defer"),document.getElementsByTagName("head")[0].appendChild(e);var t=document.getElementById("nimble-load-jquery");t.parentNode.removeChild(t)};if(nb_.hasPreloadSupport()){var t=document.createElement("link");t.setAttribute("href","<?php echo NIMBLE_JQUERY_LATEST_CDN_URL; ?>"),t.setAttribute("rel","preload"),t.setAttribute("id","<?php echo NIMBLE_JQUERY_ID; ?>"),t.setAttribute("as","script"),t.onload=function(){this.onload=null,this.rel="script",e()},document.getElementsByTagName("head")[0].appendChild(t)}else e()},1e3);</script>
            <?php
            }
        }//sek_preload_jquery_from_dns()


        // @'wp_head'PHP_INT_MAX
        // introduced for https://github.com/presscustomizr/nimble-builder/issues/626
        // jQuery can potentially be loaded async, so let's react to its load or the presence of window.jQuery
        function sek_detect_jquery() {
            ?>
            <script id="nimble-detect-jquery">!function(){var e=function(){var e="nb-jquery-loaded";nb_.wasEmitted(e)||nb_.emit(e)},n=function(t){t=t||0,void 0!==window.jQuery?e():t<30?setTimeout(function(){n(++t)},200):alert("Nimble Builder problem : jQuery.js was not detected on your website")},t=document.getElementById("<?php echo NIMBLE_JQUERY_ID; ?>");t&&t.addEventListener("load",function(){e()}),n()}();</script>
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
            // when jquery is loaded async and not replaced we need to dequeue jquery-migrate and load it dynamically on 'nb-jquery-loaded'
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
            <script id="nimble-load-jquery-migrate">!function(){var e=function(){var e=document.createElement("script");e.setAttribute("src","<?php echo NIMBLE_JQUERY_MIGRATE_URL; ?>"),e.setAttribute("id","nb-query-migrate"),e.setAttribute("defer","defer"),document.getElementsByTagName("head")[0].appendChild(e);var t=document.getElementById("nimble-load-jquery-migrate");t.parentNode.removeChild(t)};nb_.listenTo("nb-jquery-loaded",function(){if(nb_.hasPreloadSupport()){var t=document.createElement("link");t.setAttribute("href","<?php echo NIMBLE_JQUERY_MIGRATE_URL; ?>"),t.setAttribute("rel","preload"),t.setAttribute("id","nb-query-migrate"),t.setAttribute("as","script"),t.onload=function(){this.onload=null,this.rel="script",e()},document.getElementsByTagName("head")[0].appendChild(t)}else e()})}();</script>
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
         * works with sek_maybe_defer_script()
         * see https://html.spec.whatwg.org/multipage/scripting.html#attr-script-defer
         * based on a solution found in Twentytwenty
         * and for which we've added an attribute with sek_maybe_defer_script( $_hand, 'defer', true );
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