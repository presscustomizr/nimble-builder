<?php
if ( !class_exists( 'SEK_Front_Assets' ) ) :
    class SEK_Front_Assets extends SEK_Front_Ajax {
        // Fired in __construct()
        function _schedule_front_and_preview_assets_printing() {
            // Load Front Assets
            add_action( 'wp_enqueue_scripts', array( $this, 'sek_enqueue_front_assets' ) );

            // Maybe load Font Awesome icons if needed ( sniff first )
            add_action( 'wp_enqueue_scripts', array( $this, 'sek_maybe_enqueue_font_awesome_icons' ), PHP_INT_MAX );

            // Adds `async` and `defer` support for scripts registered or enqueued
            // and for which we've added an attribute with sek_wp_script_add_data( $_hand, 'async', true );
            // inspired from Twentytwenty WP theme
            // @see https://core.trac.wordpress.org/ticket/12009
            add_filter( 'script_loader_tag', array( $this, 'sek_filter_script_loader_tag' ), 10, 2 );

            // added March 2020 when experimenting for https://github.com/presscustomizr/nimble-builder/issues/626
            add_action( 'wp_default_scripts', array( $this, 'sek_maybe_dequeue_jquery' ) );

            // Maybe print split module stylesheet inline
            // introduced in march 2020 for https://github.com/presscustomizr/nimble-builder/issues/612
            add_action( 'wp_head', array( $this, 'sek_maybe_print_inline_split_module_stylesheets' ), PHP_INT_MAX  );

            // initialize Nimble front js app
            add_action( 'wp_head', array( $this, 'sek_initialize_front_js_app' ), 0  );

            // Emit an event when jQuery is detected. 'nimble-jquery-loaded'
            // maybe fetch jQuery from a CDN when dequeued
            add_action( 'wp_footer', array( $this, 'sek_handle_jquery' ));

            add_action( 'wp_footer', array( $this, 'sek_handle_customizer_previewed_device_js' ), PHP_INT_MAX  );

            // Load customize preview js
            add_action( 'customize_preview_init' , array( $this, 'sek_schedule_customize_preview_assets' ) );
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
            $is_stylesheet_split_for_performance = sek_use_split_stylesheets_on_front();
            $is_split_module_stylesheets_inline_for_performance = $is_stylesheet_split_for_performance && sek_inline_stylesheets_on_front();

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
            if ( $is_stylesheet_split_for_performance && !$is_split_module_stylesheets_inline_for_performance ) {
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
                ( !skp_is_customizing() && sek_is_front_jquery_dequeued() ) ? array() : array( 'jquery'),
                NIMBLE_ASSETS_VERSION,
                true
            );
            // added for https://github.com/presscustomizr/nimble-builder/issues/583
            // not added when customizing
            sek_wp_script_add_data( 'sek-main-js', 'async', true );

            // SMART LOAD
            // only load on front
            if ( !sek_load_front_js_assets_on_scroll() && sek_is_img_smartload_enabled() ) {
                wp_enqueue_script(
                    'sek-smartload',
                    sek_is_dev_mode() ? NIMBLE_BASE_URL . '/assets/front/js/libs/nimble-smartload.js' : NIMBLE_BASE_URL . '/assets/front/js/libs/nimble-smartload.min.js',
                    ( !skp_is_customizing() && sek_is_front_jquery_dequeued() ) ? array() : array( 'jquery'),
                    NIMBLE_ASSETS_VERSION,
                    true
                );
                // not added when customizing
                sek_wp_script_add_data( 'sek-smartload', 'async', true );
            }

            // PARALLAX BG
            // front : Load if js not loaded dynamically + we detect the need for the script
            // customizing : load if not loaded dynamically
            if ( ( !sek_load_front_js_assets_on_scroll() && sek_front_needs_parallax_bg() ) || skp_is_customizing() ) {
                wp_enqueue_script(
                    'sek-parallax-bg',
                    sek_is_dev_mode() ? NIMBLE_BASE_URL . '/assets/front/js/libs/nimble-parallax-bg.js' : NIMBLE_BASE_URL . '/assets/front/js/libs/nimble-parallax-bg.min.js',
                    ( !skp_is_customizing() && sek_is_front_jquery_dequeued() ) ? array() : array( 'jquery'),
                    NIMBLE_ASSETS_VERSION,
                    true
                );
                // not added when customizing
                sek_wp_script_add_data( 'sek-parallax-bg', 'async', true );
            }


            /* ------------------------------------------------------------------------- *
             *  LIGHT BOX WITH MAGNIFIC POPUP
             /* ------------------------------------------------------------------------- */
            // Magnific Popup is loaded when needed only
            // front : Load if js not loaded dynamically + we detect the need for the script
            // customizing : load if not loaded dynamically
            if ( ( !sek_load_front_js_assets_on_scroll() && sek_front_needs_magnific_popup() ) || skp_is_customizing() ) {
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
                    ( !skp_is_customizing() && sek_is_front_jquery_dequeued() ) ? array() : array( 'jquery'),
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
            if ( ( !sek_load_front_js_assets_on_scroll() && array_key_exists('czr_img_slider_module' , $contextually_active_modules) ) || skp_is_customizing() ) {
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
            if ( ( !sek_load_front_js_assets_on_scroll() && array_key_exists('czr_menu_module' , $contextually_active_modules ) ) || skp_is_customizing() ) {
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
            if ( ( !sek_load_front_js_assets_on_scroll() && sek_front_needs_video_bg() ) || skp_is_customizing() ) {
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

                    'video_bg_lazyload_enabled' => sek_is_video_bg_lazyload_enabled(),
                    'load_front_module_js_on_scroll' => sek_load_front_js_assets_on_scroll(),
                    'load_front_partial_css_on_scroll' => sek_load_front_partial_css_assets_on_scroll(),
                    'assetVersion' => NIMBLE_ASSETS_VERSION,
                    'frontAssetsPath' => NIMBLE_BASE_URL . '/assets/front/',
                    'contextuallyActiveModules' => sek_get_collection_of_contextually_active_modules(),
                    'fontAwesomeAlreadyEnqueued' => wp_style_is('customizr-fa', 'enqueued') || wp_style_is('hueman-font-awesome', 'enqueued')
                )
            );

        }//sek_enqueue_front_assets


        // hook : 'wp_enqueue_scripts:PHP_INT_MAX'
        // Feb 2020 => now check if Hueman or Customizr has already loaded font awesome
        // @see https://github.com/presscustomizr/nimble-builder/issues/600
        function sek_maybe_enqueue_font_awesome_icons() {
            // if active theme is Hueman or Customizr, Font Awesome may already been enqueued.
            // asset handle for Customizr => 'customizr-fa'
            // asset handle for Hueman => 'hueman-font-awesome'
            if ( wp_style_is('customizr-fa', 'enqueued') || wp_style_is('hueman-font-awesome', 'enqueued') )
              return;

            // Font awesome is always loaded when customizing
            // when not customizing, sek_front_needs_font_awesome() sniffs if the collection include a module using an icon
            if ( !skp_is_customizing() && sek_front_needs_font_awesome() && !sek_load_front_partial_css_assets_on_scroll() ) {
                wp_enqueue_style(
                    'czr-font-awesome',
                    NIMBLE_BASE_URL . '/assets/front/fonts/css/fontawesome-all.min.css',
                    array(),
                    NIMBLE_ASSETS_VERSION,
                    $media = 'all'
                );
            }
        }




        // @'wp_head'0
        // Loading sequence :
        // 1) window.nb_ utils starts being populated
        // 2) 'nimble-jquery-loaded' => fired in footer when jQuery is defined <= window.nb_ utils is completed with jQuery dependant helper properties and methods
        // 3) 'nimble-app-ready' => fired in footer on 'nimble-jquery-loaded' <= all module scripts are fired on this event
        // 4) 'nimble-magnific-popup-loaded', ... are emitted in each script files
        function sek_initialize_front_js_app() {
            ?>
            <script>window.nb_ = {};
// Jquery agnostic
(function(w, d){
    //https://underscorejs.org/docs/underscore.html#section-17
    var restArguments = function(func, startIndex) {
      startIndex = startIndex == null ? func.length - 1 : +startIndex;
      return function() {
        var length = Math.max(arguments.length - startIndex, 0),
            rest = Array(length),
            index = 0;
        for (; index < length; index++) {
          rest[index] = arguments[index + startIndex];
        }
        switch (startIndex) {
          case 0: return func.call(this, rest);
          case 1: return func.call(this, arguments[0], rest);
          case 2: return func.call(this, arguments[0], arguments[1], rest);
        }
        var args = Array(startIndex + 1);
        for (index = 0; index < startIndex; index++) {
          args[index] = arguments[index];
        }
        args[startIndex] = rest;
        return func.apply(this, args);
      };
    };
    var has = function(obj, path) {
      return obj != null && hasOwnProperty.call(obj, path);
    };
    // helper for nb_.throttle()
    var _now = Date.now || function() {
      return new Date().getTime();
    };

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
        has : function(obj, path) {
          if (!_.isArray(path)) {
            return has(obj, path);
          }
          var length = path.length;
          for (var i = 0; i < length; i++) {
            var key = path[i];
            if (obj == null || !Object.prototype.hasOwnProperty.call(obj, key)) {
              return false;
            }
            obj = obj[key];
          }
          return !!length;
        },
        // https://davidwalsh.name/javascript-debounce-function
        debounce : function(func, wait, immediate) {
          var timeout;
          return function() {
            var context = this, args = arguments;
            var later = function() {
              timeout = null;
              if (!immediate) func.apply(context, args);
            };
            var callNow = immediate && !timeout;
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
            if (callNow) func.apply(context, args);
          };
        },
        // https://underscorejs.org/docs/underscore.html#section-85
        throttle : function(func, wait, options) {
          var timeout, context, args, result;
          var previous = 0;
          if (!options) options = {};

          var later = function() {
            previous = options.leading === false ? 0 : _now();
            timeout = null;
            result = func.apply(context, args);
            if (!timeout) context = args = null;
          };

          var throttled = function() {
            var now = _now();
            if (!previous && options.leading === false) previous = now;
            var remaining = wait - (now - previous);
            context = this;
            args = arguments;
            if (remaining <= 0 || remaining > wait) {
              if (timeout) {
                clearTimeout(timeout);
                timeout = null;
              }
              previous = now;
              result = func.apply(context, args);
              if (!timeout) context = args = null;
            } else if (!timeout && options.trailing !== false) {
              timeout = setTimeout(later, remaining);
            }
            return result;
          };

          throttled.cancel = function() {
            clearTimeout(timeout);
            previous = 0;
            timeout = context = args = null;
          };

          return throttled;
        },
        delay : restArguments(function(func, wait, args) {
          return setTimeout(function() {
            return func.apply(null, args);
          }, wait);
        }),
        // safe console log for
        errorLog : function() {
            //fix for IE, because console is only defined when in F12 debugging mode in IE
            if ( nb_.isUndefined( console ) || !nb_.isFunction( window.console.log ) )
              return;
            console.log.apply(console,arguments);
        },
        scriptsLoadingStatus : {},// <= will be populated with the script loading promises
        listenTo : function( evt, func ) {
            var bools = {
                'nimble-jquery-loaded' : typeof undefined !== typeof jQuery,
                'nimble-app-ready' : typeof undefined !== typeof window.nb_ && nb_.wasListenedTo('nimble-jquery-loaded'),
                'nimble-magnific-popup-loaded' : typeof undefined !== typeof jQuery && typeof undefined !== typeof jQuery.fn.magnificPopup,
                'nimble-swiper-script-loaded' : typeof undefined !== typeof window.Swiper
            };
            var _executeAndLog = function() {
                func();
                console.log('LISTENED TO', evt );
                // store it, so if the event has been emitted before the listener is fired, we know it's been emitted
                nb_.eventsListenedTo.push(evt);
            };
            //if ( '')
            if ( 'function' === typeof func ) {
                // For event without a boolean check, if the event has been emitted before the listener is fired, we know it's been emitted if stored in [] eventsEmitted
                if ( true === bools[evt] ) {
                    _executeAndLog();
                } else if ( nb_.wasListenedTo(evt) ) {
                    _executeAndLog();
                } else if ( nb_.wasEmitted(evt) ) {
                    _executeAndLog();
                } else {
                    document.addEventListener(evt,_executeAndLog);
                }
            } else {
              nb_.errorLog('Nimble error => listenTo func param is not a function for event => ', evt, func );
            }
        },
        eventsEmitted : [],
        eventsListenedTo : [],
        emit : function( evt ) {
            var _evt = document.createEvent('Event');
            _evt.initEvent(evt, true, true); //can bubble, and is cancellable
            document.dispatchEvent(_evt);
            console.log('EMITTED', evt );
            nb_.eventsEmitted.push(evt);
        },
        wasListenedTo : function( evt ) {
            return ('string' === typeof evt) && nb_.inArray( nb_.eventsListenedTo, evt );
        },
        wasEmitted : function( evt ) {
            return ('string' === typeof evt) && nb_.inArray( nb_.eventsEmitted, evt );
        }
    };//window.nb_
}(window, document ));</script>
            <?php
        }

        // @'wp_footer'PHP_INT_MAX
        // introduced for https://github.com/presscustomizr/nimble-builder/issues/626
        function sek_handle_jquery() {
            ?>
            <script id="nimble-detect-jquery">!function(){var e=function(n){n=n||0,"undefined"!=typeof jQuery?nb_.emit("nimble-jquery-loaded"):n<30?setTimeout(function(){e(++n)},200):alert("Nimble Builder problem : jQuery.js was not detected on your website")};e()}();</script>
            <?php
            if( skp_is_customizing() || !sek_is_front_jquery_dequeued() )
              return;
            ?>
            <script id="nimble-load-jquery">setTimeout(function(){var e=document.createElement("script");e.setAttribute("src","https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"),e.setAttribute("type","text/javascript"),e.setAttribute("id","nimble-jquery"),e.setAttribute("defer","defer"),document.getElementsByTagName("head")[0].appendChild(e)},0);</script>
            <?php
        }//sek_handle_jquery()


        // @'wp_default_scripts'
        // see https://wordpress.stackexchange.com/questions/291700/how-to-stop-jquery-migrate-manually
        // https://stackoverflow.com/questions/18421404/how-do-i-stop-wordpress-loading-jquery-and-jquery-migrate#25977181
        function sek_maybe_dequeue_jquery( &$scripts ) {
            if ( sek_is_front_jquery_dequeued() && !skp_is_customizing() && !is_admin() && !empty( $scripts->registered['jquery'] ) ) {
                $scripts->registered['jquery']->deps = array_diff(
                    $scripts->registered['jquery']->deps,
                    [ 'jquery-migrate' ]
                );
                $scripts->remove( 'jquery');
                //$scripts->add( 'jquery', false, array( 'jquery-core' ), '1.2.1' );
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


        // @'wp_footer'
        function sek_handle_customizer_previewed_device_js() {
            if( !skp_is_customizing() )
              return;
            ?>
            <script id="nimble-customizer-previewed-device-handler">window,document,nb_.listenTo("nimble-app-ready",function(){jQuery(function(e){if(nb_.isCustomizing()){var i=function(){wp.customize.preview.bind("previewed-device",function(e){nb_.previewedDevice=e})};wp.customize.preview?i():wp.customize.bind("preview-ready",function(){i()})}})});</script>
            <?php
        }

        // enqueue / print customize preview assets
        // hook : 'customize_preview_init'
        function sek_schedule_customize_preview_assets() {
            // we don't need those assets when previewing a customize changeset
            // added when fixing https://github.com/presscustomizr/nimble-builder/issues/351
            if ( sek_is_customize_previewing_a_changeset_post() )
              return;

            // Load preview ui js tmpl
            add_action( 'wp_footer', array( $this, 'sek_print_ui_tmpl' ) );

            wp_enqueue_style(
                'sek-preview',
                sprintf(
                    '%1$s/assets/czr/sek/css/%2$s' ,
                    NIMBLE_BASE_URL,
                    sek_is_dev_mode() ? 'sek-preview.css' : 'sek-preview.min.css'
                ),
                array( 'sek-base' ),
                NIMBLE_ASSETS_VERSION,
                'all'
            );
            wp_enqueue_style(
                'czr-font-awesome',
                NIMBLE_BASE_URL . '/assets/front/fonts/css/fontawesome-all.min.css',
                array(),
                NIMBLE_ASSETS_VERSION,
                $media = 'all'
            );
            // Communication between preview and customizer panel
            wp_enqueue_script(
                'sek-customize-preview',
                sprintf(
                    '%1$s/assets/czr/sek/js/%2$s' ,
                    NIMBLE_BASE_URL,
                    sek_is_dev_mode() ? 'ccat-sek-preview.js' : 'ccat-sek-preview.min.js'
                ),
                array( 'customize-preview', 'underscore'),
                NIMBLE_ASSETS_VERSION,
                true
            );

            wp_localize_script(
                'sek-customize-preview',
                'sekPreviewLocalized',
                array(
                    'i18n' => array(
                        "You've reached the maximum number of columns allowed in this section." => __( "You've reached the maximum number of columns allowed in this section.", 'text_doma'),
                        "Moving elements between global and local sections is not allowed." => __( "Moving elements between global and local sections is not allowed.", 'text_doma'),
                        'Something went wrong, please refresh this page.' => __('Something went wrong, please refresh this page.', 'text_doma'),
                        'Insert here' => __('Insert here', 'text_doma'),
                        'This content has been created with the WordPress editor.' => __('This content has been created with the WordPress editor.', 'text_domain' ),

                        'Insert a new section' => __('Insert a new section', 'text_doma' ),
                        '@location' => __('@location', 'text_domain_to_be'),
                        'Insert a new global section' => __('Insert a new global section', 'text_doma' ),

                        'section' => __('section', 'text_doma'),
                        'header section' => __('header section', 'text_doma'),
                        'footer section' => __('footer section', 'text_doma'),
                        '(global)' => __('(global)', 'text_doma'),
                        'nested section' => __('nested section', 'text_doma'),

                        'Shift-click to visit the link' => __('Shift-click to visit the link', 'text_doma'),
                        'External links are disabled when customizing' => __('External links are disabled when customizing', 'text_doma'),
                        'Link deactivated while previewing' => __('Link deactivated while previewing', 'text_doma')
                    ),
                    'isDevMode' => sek_is_dev_mode(),
                    'isPreviewUIDebugMode' => isset( $_GET['preview_ui_debug'] ) || NIMBLE_IS_PREVIEW_UI_DEBUG_MODE,
                    'ajaxUrl' => admin_url( 'admin-ajax.php' ),
                    'frontNonce' => array( 'id' => 'SEKFrontNonce', 'handle' => wp_create_nonce( 'sek-front-nonce' ) ),

                    'registeredModules' => CZR_Fmk_Base()->registered_modules,

                    // introduced for https://github.com/presscustomizr/nimble-builder/issues/494
                    // september 2019
                    // this guid is used to differentiate dynamically rendered content from static content that may include a Nimble generated HTML structure
                    // an attribute "data-sek-preview-level-guid" is added to each rendered level when customizing or ajaxing
                    // when generating the ui, we check if the localized guid matches the one rendered server side
                    // otherwise the preview UI can be broken
                    'previewLevelGuid' => $this->sek_get_preview_level_guid()
                )
            );

            wp_enqueue_script( 'jquery-ui-sortable' );

            wp_enqueue_style(
                'ui-sortable',
                '//code.jquery.com/ui/1.12.1/themes/smoothness/jquery-ui.css',
                array(),
                null,//time(),
                $media = 'all'
            );
            wp_enqueue_script( 'jquery-ui-resizable' );

            // March 2020
            if ( sek_get_feedback_notif_status() ) {
                wp_enqueue_script(
                  'sek-confettis',
                  sprintf( '%1$s/assets/front/css/libs/confetti.browser.min.js', NIMBLE_BASE_URL ),
                  array(),
                  NIMBLE_ASSETS_VERSION,
                  true
                );
            }
        }


        // hook : wp_head@PHP_INT_MAX
        // introduced in march 2020 for https://github.com/presscustomizr/nimble-builder/issues/612
        function sek_maybe_print_inline_split_module_stylesheets() {
            $is_stylesheet_split_for_performance = !skp_is_customizing() && defined('NIMBLE_USE_SPLIT_STYLESHEETS') && NIMBLE_USE_SPLIT_STYLESHEETS;
            $is_split_module_stylesheets_inline_for_performance = !skp_is_customizing() && $is_stylesheet_split_for_performance && defined('NIMBLE_PRINT_MODULE_STYLESHEETS_INLINE') && NIMBLE_PRINT_MODULE_STYLESHEETS_INLINE;
            if ( !$is_split_module_stylesheets_inline_for_performance )
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


        //'wp_footer' in the preview frame
        function sek_print_ui_tmpl() {
            ?>
              <script type="text/html" id="sek-tmpl-add-content-button">
                  <# //console.log( 'data', data ); #>
                  <div class="sek-add-content-button <# if ( data.is_last ) { #>is_last<# } #>">
                    <div class="sek-add-content-button-wrapper">
                     <# var hook_location = '', btn_title = true !== data.is_global_location ? sekPreviewLocalized.i18n['Insert a new section'] : sekPreviewLocalized.i18n['Insert a new global section'], addContentBtnWidth = true !== data.is_global_location ? '83px' : '113px' #>
                      <# if ( data.location ) {
                          hook_location = ['(' , sekPreviewLocalized.i18n['@location'] , ':"',data.location , '")'].join('');
                      } #>
                      <button title="{{btn_title}} {{hook_location}}" data-sek-click-on="add-content" data-sek-add="section" class="sek-add-content-btn" style="--sek-add-content-btn-width:{{addContentBtnWidth}};">
                        <span class="sek-click-on-button-icon sek-click-on">+</span><span class="action-button-text">{{btn_title}}</span>
                      </button>
                    </div>
                  </div>
              </script>

              <?php
                  $icon_right_side_class = is_rtl() ? 'sek-dyn-left-icons' : 'sek-dyn-right-icons';
                  $icon_left_side_class = is_rtl() ? 'sek-dyn-right-icons' : 'sek-dyn-left-icons';
              ?>

              <script type="text/html" id="sek-dyn-ui-tmpl-section">
                  <?php //<# console.log( 'data', data ); #> ?>
                  <div class="sek-dyn-ui-wrapper sek-section-dyn-ui">
                    <div class="sek-dyn-ui-inner <?php echo $icon_left_side_class; ?>">
                      <div class="sek-dyn-ui-icons">

                        <?php if ( sek_is_dev_mode() ) : ?>
                          <i class="sek-to-json fas fa-code"></i>
                        <?php endif; ?>
                        <?php
                          // Code before implementing https://github.com/presscustomizr/nimble-builder/issues/521 :
                          /* <# if ( true !== data.is_first_section_in_parent ) { #>
                          <i data-sek-click-on="move-section-up" class="material-icons sek-click-on" title="<?php _e( 'Move section up', 'text_domain' ); ?>">keyboard_arrow_up</i>
                        <# } #>
                        <# if ( true !== data.is_last_section_in_parent ) { #>
                          <i data-sek-click-on="move-section-down" class="material-icons sek-click-on" title="<?php _e( 'Move section down', 'text_domain' ); ?>">keyboard_arrow_down</i>
                        <# } #>*/
                        ?>
                        <i data-sek-click-on="move-section-up" class="material-icons sek-click-on" title="<?php _e( 'Move section up', 'text_domain' ); ?>">keyboard_arrow_up</i>
                        <i data-sek-click-on="move-section-down" class="material-icons sek-click-on" title="<?php _e( 'Move section down', 'text_domain' ); ?>">keyboard_arrow_down</i>


                        <?php // if this is a nested section, it has the is_nested property set to true. We don't want to make it draggable for the moment. @todo ?>
                        <# if ( !data.is_nested ) { #>
                          <# if ( true !== data.is_global_location ) { #>
                            <i class="fas fa-arrows-alt sek-move-section" title="<?php _e( 'Drag section', 'text_domain' ); ?>"></i>
                           <# } #>
                        <# } #>
                        <i data-sek-click-on="edit-options" class="material-icons sek-click-on" title="<?php _e( 'Edit section settings', 'text_domain' ); ?>">tune</i>
                        <# if ( data.can_have_more_columns ) { #>
                          <i data-sek-click-on="add-column" class="material-icons sek-click-on" title="<?php _e( 'Add a column', 'text_domain' ); ?>">view_column</i>
                        <# } #>
                        <i data-sek-click-on="duplicate" class="material-icons sek-click-on" title="<?php _e( 'Duplicate section', 'text_domain' ); ?>">filter_none</i>
                        <?php if ( defined( 'NIMBLE_SAVED_SECTIONS_ENABLED' ) && NIMBLE_SAVED_SECTIONS_ENABLED ) : ?>
                          <i data-sek-click-on="toggle-save-section-ui" class="sek-save far fa-save" title="<?php _e( 'Save this section', 'text_domain' ); ?>"></i>
                        <?php endif; ?>
                        <i data-sek-click-on="pick-content" data-sek-content-type="module" class="material-icons sek-click-on" title="<?php _e( 'Add a module', 'text_domain' ); ?>">add_circle_outline</i>
                        <i data-sek-click-on="remove" class="material-icons sek-click-on" title="<?php _e( 'Remove section', 'text_domain' ); ?>">delete_forever</i>
                      </div>
                    </div><?php // .sek-dyn-ui-inner ?>
                    <div class="sek-dyn-ui-location-type" data-sek-click-on="edit-options" title="<?php _e( 'Edit section settings', 'text_domain' ); ?>">
                      <div class="sek-dyn-ui-location-inner">
                        <div class="sek-dyn-ui-hamb-menu-wrapper sek-collapsed">
                          <div class="sek-ham__toggler-spn-wrapper"><span class="line line-1"></span><span class="line line-2"></span><span class="line line-3"></span></div>
                        </div>
                        <#
                          var section_title = true !== data.is_global_location ? sekPreviewLocalized.i18n['section'] : sekPreviewLocalized.i18n['section (global)'];
                          var section_title = !data.is_nested ? sekPreviewLocalized.i18n['section'] : sekPreviewLocalized.i18n['nested section'];
                          if ( true === data.is_header_location && !data.is_nested ) {
                                section_title = sekPreviewLocalized.i18n['header section'];
                          } else if ( true === data.is_footer_location && !data.is_nested ) {
                                section_title = sekPreviewLocalized.i18n['footer section'];
                          }

                          section_title = true !== data.is_global_location ? section_title : [ section_title, sekPreviewLocalized.i18n['(global)'] ].join(' ');
                        #>
                        <div class="sek-dyn-ui-level-type">{{section_title}}</div>
                      </div><?php // .sek-dyn-ui-location-inner ?>
                      <div class="sek-minimize-ui" title="<?php _e('Hide this menu if you need to access behind', 'text-domain'); ?>"><i class="far fa-eye-slash"></i></div>
                    </div><?php // .sek-dyn-ui-location-type ?>
                  </div><?php // .sek-dyn-ui-wrapper ?>
              </script>

              <script type="text/html" id="sek-dyn-ui-tmpl-column">
                  <?php //<# console.log( 'data', data ); #> ?>
                  <?php
                    // when a column has nested section(s), its ui might be hidden by deeper columns.
                    // that's why a CSS class is added to position it on the top right corner, instead of bottom right
                    // @see https://github.com/presscustomizr/nimble-builder/issues/488
                  ?>
                  <# var has_nested_section_class = true === data.has_nested_section ? 'sek-col-has-nested-section' : ''; #>
                  <div class="sek-dyn-ui-wrapper sek-column-dyn-ui {{has_nested_section_class}}">
                    <div class="sek-dyn-ui-inner <?php echo $icon_right_side_class; ?>">
                      <div class="sek-dyn-ui-icons">
                        <i class="fas fa-arrows-alt sek-move-column" title="<?php _e( 'Move column', 'text_domain' ); ?>"></i>
                        <i data-sek-click-on="edit-options" class="material-icons sek-click-on" title="<?php _e( 'Edit column settings', 'text_domain' ); ?>">tune</i>
                        <# if ( !data.parent_is_last_allowed_nested ) { #>
                          <i data-sek-click-on="add-section" class="material-icons sek-click-on" title="<?php _e( 'Add a nested section', 'text_domain' ); ?>">account_balance_wallet</i>
                        <# } #>
                        <# if ( data.parent_can_have_more_columns ) { #>
                          <i data-sek-click-on="duplicate" class="material-icons sek-click-on" title="<?php _e( 'Duplicate column', 'text_domain' ); ?>">filter_none</i>
                        <# } #>

                        <i data-sek-click-on="pick-content" data-sek-content-type="module" class="material-icons sek-click-on" title="<?php _e( 'Add a module', 'text_domain' ); ?>">add_circle_outline</i>
                        <# if ( !data.parent_is_single_column ) { #>
                          <i data-sek-click-on="remove" class="material-icons sek-click-on" title="<?php _e( 'Remove column', 'text_domain' ); ?>">delete_forever</i>
                        <# } #>
                      </div>
                    </div><?php // .sek-dyn-ui-inner ?>

                    <div class="sek-dyn-ui-location-type" data-sek-click-on="edit-options" title="<?php _e( 'Edit column settings', 'text_domain' ); ?>">
                      <div class="sek-minimize-ui" title="<?php _e('Hide this menu if you need to access behind', 'text-domain'); ?>"><i class="far fa-eye-slash"></i></div>
                      <div class="sek-dyn-ui-location-inner">
                        <div class="sek-dyn-ui-hamb-menu-wrapper sek-collapsed">
                          <div class="sek-ham__toggler-spn-wrapper"><span class="line line-1"></span><span class="line line-2"></span><span class="line line-3"></span></div>
                        </div>
                        <div class="sek-dyn-ui-level-type"><?php _e( 'column', 'text_domain' ); ?></div>
                      </div><?php // .sek-dyn-ui-location-inner ?>
                    </div><?php // .sek-dyn-ui-location-type ?>
                  </div><?php // .sek-dyn-ui-wrapper ?>
              </script>

              <script type="text/html" id="sek-dyn-ui-tmpl-module">
                  <div class="sek-dyn-ui-wrapper sek-module-dyn-ui">
                    <div class="sek-dyn-ui-inner <?php echo $icon_left_side_class; ?>">
                      <div class="sek-dyn-ui-icons">
                        <i class="fas fa-arrows-alt sek-move-module" title="<?php _e( 'Move module', 'text_domain' ); ?>"></i>
                        <i data-sek-click-on="edit-module" class="fas fa-pencil-alt sek-tip sek-click-on" title="<?php _e( 'Edit module content', 'text_domain' ); ?>"></i>
                        <i data-sek-click-on="edit-options" class="material-icons sek-click-on" title="<?php _e( 'Edit module settings', 'text_domain' ); ?>">tune</i>
                        <i data-sek-click-on="duplicate" class="material-icons sek-click-on" title="<?php _e( 'Duplicate module', 'text_domain' ); ?>">filter_none</i>
                        <i data-sek-click-on="remove" class="material-icons sek-click-on" title="<?php _e( 'Remove module', 'text_domain' ); ?>">delete_forever</i>
                      </div>
                    </div><?php // .sek-dyn-ui-inner ?>
                    <#
                      var module_name = !_.isEmpty( data.module_name ) ? data.module_name + ' ' + '<?php _e("module", "text_domain"); ?>' : '<?php _e("module", "text_domain"); ?>';
                    #>
                    <div class="sek-dyn-ui-location-type" data-sek-click-on="edit-module" title="<?php _e( 'Edit module settings', 'text_domain' ); ?>">
                      <div class="sek-dyn-ui-location-inner">
                        <div class="sek-dyn-ui-hamb-menu-wrapper sek-collapsed">
                          <div class="sek-ham__toggler-spn-wrapper"><span class="line line-1"></span><span class="line line-2"></span><span class="line line-3"></span></div>
                        </div>
                        <div class="sek-dyn-ui-level-type">{{module_name}}</div>
                      </div>
                      <div class="sek-minimize-ui" title="<?php _e('Hide this menu if you need to access behind', 'text-domain'); ?>"><i class="far fa-eye-slash"></i></div>
                    </div>
                  </div><?php // .sek-dyn-ui-wrapper ?>
              </script>

              <script type="text/html" id="sek-dyn-ui-tmpl-wp-content">
                  <div class="sek-dyn-ui-wrapper sek-wp-content-dyn-ui">
                    <div class="sek-dyn-ui-inner">
                      <div class="sek-dyn-ui-icons">
                        <i class="fas fa-pencil-alt sek-edit-wp-content" title="<?php _e( 'Edit this WordPress content', 'text_domain' ); ?>"></i>
                      </div>
                    </div><?php // .sek-dyn-ui-inner ?>

                    <span class="sek-dyn-ui-location-type" title="<?php _e( 'Edit module settings', 'text_domain' ); ?>">
                      <i class="fab fa-wordpress sek-edit-wp-content" title="<?php _e( 'Edit this WordPress content', 'text_domain' ); ?>"> <?php _e( 'WordPress content', 'text_domain'); ?></i>
                    </span>
                  </div><?php // .sek-dyn-ui-wrapper ?>
              </script>
            <?php
        }
    }//class
endif;
?>