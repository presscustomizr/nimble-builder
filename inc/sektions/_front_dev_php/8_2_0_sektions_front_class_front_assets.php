<?php
if ( !class_exists( 'SEK_Front_Assets' ) ) :
    class SEK_Front_Assets extends SEK_Front_Ajax {
        // Fired in __construct()
        function _schedule_front_assets_printing() {
            // Maybe adds `defer` support for scripts registered or enqueued
            // and for which we've added an attribute with sek_defer_script( $_hand, 'defer', true );
            // inspired from Twentytwenty WP theme
            // @see https://core.trac.wordpress.org/ticket/12009
            add_filter( 'script_loader_tag', array( $this, 'sek_filter_script_loader_tag' ), 10, 2 );

            add_action( 'template_redirect', array( $this, 'sek_check_if_page_has_nimble_content' ) );
            
            // Load Front CSS
            // Contextual stylesheets for local and global sections are loaded with ::print_or_enqueue_seks_style()
            // see inc\sektions\_front_dev_php\8_4_1_sektions_front_class_render_css.php
            add_action( 'wp_enqueue_scripts', array( $this, 'sek_maybe_enqueue_front_css_assets' ) );

            // Load Front JS
            add_action( 'wp_enqueue_scripts', array( $this, 'sek_enqueue_front_js_assets' ) );

            // added March 2020 when experimenting for https://github.com/presscustomizr/nimble-builder/issues/626
            add_action( 'wp_default_scripts', array( $this, 'sek_maybe_dequeue_jquery_and_schedule_jquery_migrate' ) );

            // replace wp_localize because we don't need to indicate a dependency to any scripts for local data
            add_action( 'wp_head', array( $this, 'sek_add_local_script_data' ), 0  );

            // initialize Nimble front js app
            add_action( 'wp_head', array( $this, 'sek_initialize_front_js_app' ), 0  );

            // Inform Nimble app that jQuery is loaded
            add_action( 'wp_head', array( $this, 'sek_detect_jquery' ), PHP_INT_MAX );

            // Maybe print a CSS loader for img and background lazy loaded
            add_action( 'wp_head', array( $this, 'sek_print_style_for_css_loader' ), PHP_INT_MAX );

            // Emit an event when jQuery is detected. 'nb-jquery-loaded'
            // maybe fetch jQuery from a CDN when dequeued
            add_action( 'wp_footer', array( $this, 'sek_preload_jquery_from_dns' ));

            // Scheduling loading of needed js files
            add_action( 'wp_head', array( $this, 'sek_main_front_js_preloading_when_not_customizing') );

            // If supported by the browser, woff2 fonts of Font Awesome will be preloaded
            add_action( 'wp_head', array( $this, 'sek_maybe_preload_fa_fonts') );
            // Maybe preload Font assets when really needed ( sniff first ) + nb_.listenTo('nb-needs-fa')
            add_action( 'wp_footer', array( $this, 'sek_maybe_preload_front_assets_when_not_customizing' ), PHP_INT_MAX );

            // Maybe load scripts on scroll
            add_action( 'wp_footer', array( $this, 'sek_maybe_load_scripts_in_ajax' ), PHP_INT_MAX );
        }//_schedule_front_and_preview_assets_printing


        //@template redirect
        function sek_check_if_page_has_nimble_content() {
            // do we have local or global sections to render in this page ?
            // see https://github.com/presscustomizr/nimble-builder/issues/586
            // we know the skope_id because 'wp' has been fired
            // October 2020
            if ( 'not_set' === Nimble_Manager()->page_has_local_or_global_sections ) {
                Nimble_Manager()->page_has_local_or_global_sections = sek_local_skope_has_nimble_sections( skp_get_skope_id() ) || sek_has_global_sections();
            }
        }


        // hook : 'wp_enqueue_scripts'
        // Contextual stylesheets for local and global sections are loaded with ::print_or_enqueue_seks_style()
        // see inc\sektions\_front_dev_php\8_4_1_sektions_front_class_render_css.php
        function sek_maybe_enqueue_front_css_assets() {
            /* ------------------------------------------------------------------------- *
             *  MAIN STYLESHEET
            /* ------------------------------------------------------------------------- */
            // Oct 2020 => use sek-base ( which includes all module stylesheets ) if Nimble could not concatenate module stylesheets when generating the dynamic stylesheet
            // for https://github.com/presscustomizr/nimble-builder/issues/749
            if ( 'failed' === get_option(NIMBLE_OPT_FOR_MODULE_CSS_READING_STATUS) ) {
                $main_stylesheet_name = 'sek-base';
            } else {
                // the light split stylesheet is never used when customizing
                $main_stylesheet_name = !skp_is_customizing() ? 'sek-base-light' : 'sek-base';
            }


            // Always load the base Nimble style when user logged in so we can display properly the button in the top admin bar.
            if ( is_user_logged_in() || false != Nimble_Manager()->page_has_local_or_global_sections ) {
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
             *  STOP HERE IF NOT CUSTOMIZING AND THERE IS NOTHING TO PRINT
            /* ------------------------------------------------------------------------- */
            // We don't need Nimble Builder assets when no local or global sections have been created
            // see https://github.com/presscustomizr/nimble-builder/issues/586
            if ( !skp_is_customizing() && !Nimble_Manager()->page_has_local_or_global_sections )
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


            // public $big_module_stylesheet_map = [
            //     'czr_quote_module' => 'quote-module',
            //     'czr_icon_module' => 'icon-module',
            //     'czr_img_slider_module' => 'img-slider-module',
            //     'czr_accordion_module' => 'accordion-module',
            //     'czr_menu_module' => 'menu-module',
            //     'czr_post_grid_module' => 'post-grid-module',
            //     'czr_simple_form_module' => 'simple-form-module'
            // ];
            // SPLIT STYLESHEETS
            // introduced march 2020 for https://github.com/presscustomizr/nimble-builder/issues/612
            // if the module stylesheets are inline, see wp_head action
            // October 2020 => modules stylesheets are now concatenated in the dynamically generated stylesheet
            // if ( !skp_is_customizing() && $is_stylesheet_split_for_performance ) {
            //     // loop on the map module type (candidates for split) => stylesheet file name
            //     foreach (Nimble_Manager()->big_module_stylesheet_map as $module_type => $stylesheet_name ) {
            //         if ( !array_key_exists($module_type , $contextually_active_modules ) )
            //           continue;

            //         wp_enqueue_style(
            //             $module_type,
            //             sprintf( '%1$s%2$s%3$s',
            //                 NIMBLE_BASE_URL . '/assets/front/css/modules/',
            //                 $stylesheet_name,
            //                 sek_is_dev_mode() ? '.css' : '.min.css'
            //             ),
            //             array( $main_stylesheet_name ),
            //             NIMBLE_ASSETS_VERSION,
            //             $media = 'all'
            //         );
            //     }
            // }


            /* ------------------------------------------------------------------------- *
             *  LIGHT BOX WITH MAGNIFIC POPUP
            /* ------------------------------------------------------------------------- */
            // the stylesheet is always preloaded on front
            if ( skp_is_customizing() ) {
                wp_enqueue_style(
                    'nb-magnific-popup',
                    NIMBLE_BASE_URL . '/assets/front/css/libs/magnific-popup.min.css',
                    array(),
                    NIMBLE_ASSETS_VERSION,
                    $media = 'all'
                );
            }

            /* ------------------------------------------------------------------------- *
             *  SWIPER FOR SLIDERS
            /* ------------------------------------------------------------------------- */
            if ( array_key_exists('czr_img_slider_module' , $contextually_active_modules) || skp_is_customizing() ) {
                // march 2020 :
                // when loading assets in ajax, swiper stylesheet is loaded dynamically
                // so we don't need to enqueue it
                // added for https://github.com/presscustomizr/nimble-builder/issues/612
                // added for https://github.com/presscustomizr/nimble-builder/issues/635
                if ( skp_is_customizing() || !sek_load_front_assets_in_ajax() ) {
                      wp_enqueue_style(
                          'nb-swiper',
                          sek_is_dev_mode() ? NIMBLE_BASE_URL . '/assets/front/css/libs/swiper.css' : NIMBLE_BASE_URL . '/assets/front/css/libs/swiper.min.css',
                          array(),
                          NIMBLE_ASSETS_VERSION,
                          $media = 'all'
                      );
                }
            }

            /* ------------------------------------------------------------------------- *
             *  FONT AWESOME STYLESHEET
            /* ------------------------------------------------------------------------- */
            if ( skp_is_customizing() ) {
                wp_enqueue_style(
                    'nb-font-awesome',
                    NIMBLE_BASE_URL . '/assets/front/fonts/css/fontawesome-all.min.css',
                    array(),
                    NIMBLE_ASSETS_VERSION,
                    $media = 'all'
                );
            }

        }//sek_enqueue_front_assets









        //@'wp_enqueue_scripts'
        // ==>>> when not customizing, assets are injected with javascript <<===
        function sek_enqueue_front_js_assets() {
            // when front scripts are preloaded or loaded in ajax, jquery is not declared as dependency
            // we need to make sure its enqueued, unless it's replaced by a cdn version
            if ( !sek_is_jquery_replaced() ) {
                wp_enqueue_script('jquery');
            }

            // When not customizing, the main and partial front scripts are loaded only when needed on front, with nb_.listenTo('{event_name}')
            // When customizing, we need to enqueue them the regular way
            if ( !skp_is_customizing() )
              return;
            /* ------------------------------------------------------------------------- *
            *  FRONT MAIN SCRIPT
            /* ------------------------------------------------------------------------- */
            wp_enqueue_script(
                'nb-main-js',
                sek_is_dev_mode() ? NIMBLE_BASE_URL . '/assets/front/js/ccat-nimble-front.js' : NIMBLE_BASE_URL . '/assets/front/js/ccat-nimble-front.min.js',
                //array( 'jquery', 'underscore'),
                // october 2018 => underscore is concatenated in the main front js file.
                ( !skp_is_customizing() && sek_is_jquery_replaced() ) ? array() : array( 'jquery'),
                NIMBLE_ASSETS_VERSION,
                false
            );
            // added for https://github.com/presscustomizr/nimble-builder/issues/583
            sek_defer_script('nb-main-js');


            /* ------------------------------------------------------------------------- *
             *  FRONT PARTIAL SCRIPTS
            /* ------------------------------------------------------------------------- */
            // public $partial_front_scripts = [
            //     'slider-module' => 'nb-needs-swiper',
            //     'menu-module' => 'nb-needs-menu-js',
            //     'front-parallax' => 'nb-needs-parallax',
            //     'accordion-module' => 'nb-needs-accordion'
            // ];
            foreach (Nimble_Manager()->partial_front_scripts as $name => $event) {
                $handle = "nb-{$name}";
                wp_enqueue_script(
                    $handle,
                    sprintf('%1$s/assets/front/js/partials/%2$s.%3$s', NIMBLE_BASE_URL, $name, sek_is_dev_mode() ? 'js' : 'min.js'),
                    array(), //( !skp_is_customizing() && sek_is_jquery_replaced() ) ? array() : array( 'jquery'),
                    NIMBLE_ASSETS_VERSION,
                    false
                );
                sek_defer_script($handle);
            }



            /* ------------------------------------------------------------------------- *
             *  LIGHT BOX WITH MAGNIFIC POPUP
            /* ------------------------------------------------------------------------- */
            wp_enqueue_script(
                'nb-magnific-popups',
                sek_is_dev_mode() ? NIMBLE_BASE_URL . '/assets/front/js/libs/jquery-magnific-popup.js' : NIMBLE_BASE_URL . '/assets/front/js/libs/jquery-magnific-popup.min.js',
                array(), //( !skp_is_customizing() && sek_is_jquery_replaced() ) ? array() : array( 'jquery'),
                NIMBLE_ASSETS_VERSION,
                false
            );
            sek_defer_script('nb-magnific-popups');


            /* ------------------------------------------------------------------------- *
             *  SWIPER FOR SLIDERS
             /* ------------------------------------------------------------------------- */
            // SWIPER JS LIB + MODULE SCRIPT
            // Swiper js is needed for the czr_img_slider_module
            // front : Load if js not loaded dynamically + we detect the need for the script
            // customizing : load if not loaded dynamically
            wp_enqueue_script(
                'nb-swiper',
                sek_is_dev_mode() ? NIMBLE_BASE_URL . '/assets/front/js/libs/swiper.js' : NIMBLE_BASE_URL . '/assets/front/js/libs/swiper.min.js',
                array(),
                NIMBLE_ASSETS_VERSION,
                false
            );
            // not added when customizing
            sek_defer_script('nb-swiper');


            /* ------------------------------------------------------------------------- *
             *  VIDEO BG
             /* ------------------------------------------------------------------------- */
            // front : Load if js not loaded dynamically + we detect the need for the script
            // customizing : load if not loaded dynamically
            wp_enqueue_script(
                'nb-video-bg-plugin',
                sek_is_dev_mode() ? NIMBLE_BASE_URL . '/assets/front/js/libs/nimble-video-bg.js' : NIMBLE_BASE_URL . '/assets/front/js/libs/nimble-video-bg.min.js',
                array(),
                NIMBLE_ASSETS_VERSION,
                false
            );
            // not added when customizing
            sek_defer_script('nb-video-bg-plugin');
        }//sek_enqueue_front_js_assets













        //@wp_head
        // ==>>> when customizing, all assets are enqueued the wp way <<===
        function sek_main_front_js_preloading_when_not_customizing() {
            if ( skp_is_customizing() )
              return;
            if ( !Nimble_Manager()->page_has_local_or_global_sections )
              return;
            if ( sek_load_front_assets_in_ajax() )
              return;

            // Load main script on nb-docready event
            $script_url = sprintf('%1$s/assets/front/js/ccat-nimble-front.%2$s?v=%3$s', NIMBLE_BASE_URL, sek_is_dev_mode() ? 'js' : 'min.js', NIMBLE_ASSETS_VERSION);
            ?>
            <script id='<?php echo "nb-load-main-script"; ?>'>
              nb_.listenTo('nb-docready', function() {
                  nb_.preloadOrDeferAsset( {
                    id : 'nb-main-js',
                    as : 'script',
                    href : "<?php echo $script_url; ?>",
                    scriptEl : document.getElementById('<?php echo "nb-load-main-script"; ?>')
                  });
              });
            </script>
            <?php


            // Schedule loading of partial scripts
            $partial_front_scripts = Nimble_Manager()->partial_front_scripts;
            foreach ($partial_front_scripts as $name => $event) {
                $url = sprintf('%1$s/assets/front/js/partials/%2$s.%3$s?v=%4$s', NIMBLE_BASE_URL, $name, sek_is_dev_mode() ? 'js' : 'min.js', NIMBLE_ASSETS_VERSION);
                ?>
                <script id='<?php echo "nb-load-script-{$name}"; ?>'>
                  nb_.listenTo('<?php echo $event; ?>', function() {
                      nb_.preloadOrDeferAsset( {
                        id : "<?php echo $name; ?>",
                        as : 'script',
                        href : "<?php echo $url; ?>",
                        scriptEl : document.getElementById('<?php echo "nb-load-script-{$name}"; ?>')
                      });
                  });
                </script>
                <?php
            }
        }





        //@wp_footer
        // preload is applied when 'load_assets_in_ajax' is not active
        // ==>>> when customizing, all assets are enqueued the wp way <<===
        function sek_maybe_preload_front_assets_when_not_customizing() {
            if ( skp_is_customizing() )
              return;

            // Check that current page has Nimble content before printing anything
            // For https://github.com/presscustomizr/nimble-builder/issues/649
            // When customizing, all assets are enqueued the WP way
            if ( !Nimble_Manager()->page_has_local_or_global_sections )
              return;

            // When we load assets in ajax, we stop here
            if ( sek_load_front_assets_in_ajax() )
              return;

            /* ------------------------------------------------------------------------- *
             *  PRELOAD FRONT SCRIPT
            /* ------------------------------------------------------------------------- */
            $assets_urls = [
                'nb-magnific-popup' => sek_is_dev_mode() ? '/assets/front/js/libs/jquery-magnific-popup.js' : '/assets/front/js/libs/jquery-magnific-popup.min.js',
                'nb-swiper' => sek_is_dev_mode() ? '/assets/front/js/libs/swiper.js' : '/assets/front/js/libs/swiper.min.js',
                'nb-video-bg-plugin' => sek_is_dev_mode() ? '/assets/front/js/libs/nimble-video-bg.js' : '/assets/front/js/libs/nimble-video-bg.min.js',

                'nb-magnific-popup-style' => '/assets/front/css/libs/magnific-popup.min.css',
            ];

            // add version
            foreach( $assets_urls as $k => $path ) {
                $assets_urls[$k] = NIMBLE_BASE_URL .$path .'?'.NIMBLE_ASSETS_VERSION;
            }
            ?>
            <script id="nb-load-front-script-and-styles">nb_.listenTo("nb-needs-magnific-popup",function(){nb_.preloadOrDeferAsset({id:"nb-magnific-popup",as:"script",href:"<?php echo $assets_urls['nb-magnific-popup']; ?>",onEvent:"nb-docready"}),nb_.preloadOrDeferAsset({id:"nb-magnific-popup-style",as:"style",href:"<?php echo $assets_urls['nb-magnific-popup-style']; ?>",onEvent:"nb-docready"})}),nb_.listenTo("nb-needs-swiper",function(){nb_.preloadOrDeferAsset({id:"nb-swiper",as:"script",href:"<?php echo $assets_urls['nb-swiper']; ?>",onEvent:"nb-docready"})}),nb_.listenTo("nb-needs-videobg-js",function(){nb_.preloadOrDeferAsset({id:"nb-video-bg-plugin",as:"script",href:"<?php echo $assets_urls['nb-video-bg-plugin']; ?>",onEvent:"nb-docready"})});</script>
            <?php

            /* ------------------------------------------------------------------------- *
             *  FONT AWESOME
            /* ------------------------------------------------------------------------- */
            // if active theme is Hueman or Customizr, Font Awesome may already been enqueued.
            // asset handle for Customizr => 'customizr-fa'
            // asset handle for Hueman => 'hueman-font-awesome'
            if ( !wp_style_is('customizr-fa', 'enqueued') && !wp_style_is('hueman-font-awesome', 'enqueued') ) {
                // Font awesome is always loaded when customizing
                ?>
                <?php $fa_style_url = NIMBLE_BASE_URL .'/assets/front/fonts/css/fontawesome-all.min.css?'.NIMBLE_ASSETS_VERSION; ?>
                <script id="nb-load-fa-style">
                  nb_.listenTo('nb-needs-fa', function() {
                      nb_.preloadOrDeferAsset( {
                        id : 'nb-font-awesome',
                        as : 'style',
                        href : "<?php echo $fa_style_url; ?>",
                        onEvent : 'nb-docready',
                        scriptEl : document.currentScript
                      });
                  });
                </script>

                <?php
            }
        }



        // hook : wp_head
        // October 2020 Better preload implementation
        // As explained here https://stackoverflow.com/questions/49268352/preload-font-awesome
        // FA fonts can be preloaded. the crossorigin param has to be added
        // => this removes Google Speed tests message "preload key requests"
        // important => the url of the font must be exactly the same as in font awesome stylesheet, including the query param at the end fa-brands-400.woff2?5.15.2
        // note that we could preload all other types available ( eot, woff, ttf, svg )
        // but NB focus on preloading woff2 which is the type used by most recent browsers
        // see https://css-tricks.com/snippets/css/using-font-face/
        function sek_maybe_preload_fa_fonts() {
            if ( !skp_is_customizing() && !Nimble_Manager()->page_has_local_or_global_sections )
              return;
            $fonts = [
                'fa-brands' => 'fa-brands-400.woff2?5.15.2',
                'fa-regular' => 'fa-regular-400.woff2?5.15.2',
                'fa-solid' => 'fa-solid-900.woff2?5.15.2'
            ];
            ?>
              <?php foreach( $fonts as $id => $name ) : ?>
                <?php $font_url = NIMBLE_BASE_URL .'/assets/front/fonts/webfonts/'.$name; ?>
                <script id='<?php echo "nb-load-{$id}"; ?>'>
                  nb_.listenTo('nb-needs-fa', function() {
                      nb_.preloadOrDeferAsset( {
                        id : "<?php echo $id; ?>",
                        as : 'font',
                        href : "<?php echo $font_url; ?>",
                        type : 'font/woff2',
                        //onEvent : 'nb-docready',
                        scriptEl : document.getElementById('<?php echo "nb-load-{$id}"; ?>')
                      });
                  });
                </script>
              <?php endforeach; ?>
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




        /**
         * Fired @'script_loader_tag'
         * Adds async/defer attributes to enqueued / registered scripts.
         * works with sek_defer_script()
         * see https://html.spec.whatwg.org/multipage/scripting.html#attr-script-defer
         * based on a solution found in Twentytwenty
         * and for which we've added an attribute with sek_defer_script( $_hand, 'defer', true );
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

            // if ( skp_is_customizing() )
            //   return $tag;

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


        // @wp_head0
        // replaces wp_localize because we don't need to indicate a dependency to any scripts for local data
        function sek_add_local_script_data() {
            if ( !skp_is_customizing() && !Nimble_Manager()->page_has_local_or_global_sections )
              return;

            // Google reCAPTCHA
            $global_recaptcha_opts = sek_get_global_option_value('recaptcha');
            $global_recaptcha_opts = is_array( $global_recaptcha_opts ) ? $global_recaptcha_opts : array();

            $l10n = array(
                'isDevMode' => sek_is_dev_mode(),
                'isCustomizing' => skp_is_customizing(),
                //'ajaxUrl' => admin_url( 'admin-ajax.php' ),
                'frontNonce' => array( 'id' => 'SEKFrontNonce', 'handle' => wp_create_nonce( 'sek-front-nonce' ) ),
                // 'localSeks' => sek_is_debug_mode() ? wp_json_encode( sek_get_skoped_seks() ) : '',
                // 'globalSeks' => sek_is_debug_mode() ? wp_json_encode( sek_get_skoped_seks( NIMBLE_GLOBAL_SKOPE_ID ) ) : '',
                'skope_id' => skp_get_skope_id(), //added for debugging purposes
                'recaptcha_public_key' => !empty( $global_recaptcha_opts['public_key'] ) ? $global_recaptcha_opts['public_key'] : '',

                'lazyload_enabled' => sek_is_img_smartload_enabled(),
                'video_bg_lazyload_enabled' => sek_is_video_bg_lazyload_enabled(),
                'load_front_assets_on_scroll' => sek_load_front_assets_in_ajax(),

                'assetVersion' => NIMBLE_ASSETS_VERSION,
                'frontAssetsPath' => NIMBLE_BASE_URL . '/assets/front/',
                'contextuallyActiveModules' => sek_get_collection_of_contextually_active_modules(),
                'fontAwesomeAlreadyEnqueued' => wp_style_is('customizr-fa', 'enqueued') || wp_style_is('hueman-font-awesome', 'enqueued'),

                'partialFrontScripts' => Nimble_Manager()->partial_front_scripts,

                // Debug for https://github.com/presscustomizr/nimble-builder/issues/795
                // 'debug' => [
                //   'nb_debug_save' => get_transient('nb_debug_save'),
                //   'nb_debug_get' => get_transient('nb_debug_get')
                // ]
            );
            $l10n = apply_filters( 'nimble-localized-js-front', $l10n );

            foreach ( (array) $l10n as $key => $value ) {
                if ( !is_scalar( $value ) ) {
                  continue;
                }
                $l10n[ $key ] = html_entity_decode( (string) $value, ENT_QUOTES, 'UTF-8' );
            }

            printf('<script id="nb-front-localized">%1$s</script>', "var sekFrontLocalized = " . wp_json_encode( $l10n ) . ';' );
        }




        // @'wp_head'0
        // Loading sequence :
        // 1) window.nb_ utils starts being populated
        // 2) 'nb-jquery-loaded' => fired in footer when jQuery is defined <= window.nb_ utils is completed with jQuery dependant helper properties and methods
        // 3) 'nb-app-ready' => fired in footer on 'nb-jquery-loaded' <= all module scripts are fired on this event
        // 4) 'nb-jmp-parsed', ... are emitted in each script files
        function sek_initialize_front_js_app() {
            if ( !skp_is_customizing() && !Nimble_Manager()->page_has_local_or_global_sections )
              return;
            ?>
            <script id="nimble-app-init">window.nb_={},function(e,t){if(window.nb_={isArray:function(e){return Array.isArray(e)||"[object Array]"===toString.call(e)},inArray:function(e,t){return!(!nb_.isArray(e)||nb_.isUndefined(t))&&e.indexOf(t)>-1},isUndefined:function(e){return void 0===e},isObject:function(e){var t=typeof e;return"function"===t||"object"===t&&!!e},errorLog:function(){nb_.isUndefined(console)||"function"!=typeof window.console.log||console.log.apply(console,arguments)},hasPreloadSupport:function(e){var t=document.createElement("link").relList;return!(!t||!t.supports)&&t.supports("preload")},listenTo:function(e,t){nb_.eventsListenedTo.push(e);var n={"nb-jquery-loaded":function(){return"undefined"!=typeof jQuery},"nb-app-ready":function(){return void 0!==window.nb_&&nb_.wasListenedTo("nb-jquery-loaded")},"nb-jmp-parsed":function(){return"undefined"!=typeof jQuery&&void 0!==jQuery.fn.magnificPopup},"nb-main-swiper-parsed":function(){return void 0!==window.Swiper}},o=function(o){nb_.isUndefined(n[e])||!1!==n[e]()?t():nb_.errorLog("Nimble error => an event callback could not be fired because conditions not met => ",e,nb_.eventsListenedTo,t)};"function"==typeof t?nb_.wasEmitted(e)?o():document.addEventListener(e,o):nb_.errorLog("Nimble error => listenTo func param is not a function for event => ",e)},eventsEmitted:[],eventsListenedTo:[],emit:function(e,t){if(!(nb_.isUndefined(t)||t.fire_once)||!nb_.wasEmitted(e)){var n=document.createEvent("Event");n.initEvent(e,!0,!0),document.dispatchEvent(n),nb_.eventsEmitted.push(e)}},wasListenedTo:function(e){return"string"==typeof e&&nb_.inArray(nb_.eventsListenedTo,e)},wasEmitted:function(e){return"string"==typeof e&&nb_.inArray(nb_.eventsEmitted,e)},isInScreen:function(e){if(!nb_.isObject(e))return!1;var t=e.getBoundingClientRect(),n=Math.max(document.documentElement.clientHeight,window.innerHeight);return!(t.bottom<0||t.top-n>=0)},isCustomizing:function(){return!1},isLazyLoadEnabled:function(){return!nb_.isCustomizing()&&!1},preloadOrDeferAsset:function(e){if(e=e||{},nb_.preloadedAssets=nb_.preloadedAssets||[],!nb_.inArray(nb_.preloadedAssets,e.id)){var t,n=document.getElementsByTagName("head")[0],o=function(){if("style"===e.as)this.setAttribute("rel","stylesheet"),this.setAttribute("type","text/css"),this.setAttribute("media","all");else{var t=document.createElement("script");t.setAttribute("src",e.href),t.setAttribute("id",e.id),"script"===e.as&&t.setAttribute("defer","defer"),n.appendChild(t),this&&this.parentNode&&this.parentNode.removeChild(this)}e.eventOnLoad&&nb_.emit(e.eventOnLoad)};("font"!==e.as||nb_.hasPreloadSupport())&&(t=document.createElement("link"),"script"===e.as?e.onEvent?nb_.listenTo(e.onEvent,function(){o.call(t)}):o.call(t):(t.setAttribute("href",e.href),"style"===e.as?t.setAttribute("rel",nb_.hasPreloadSupport()?"preload":"stylesheet"):"font"===e.as&&nb_.hasPreloadSupport()&&t.setAttribute("rel","preload"),t.setAttribute("id",e.id),t.setAttribute("as",e.as),"font"===e.as&&(t.setAttribute("type",e.type),t.setAttribute("crossorigin","anonymous")),t.onload=function(){this.onload=null,"font"!==e.as&&(e.onEvent?nb_.listenTo(e.onEvent,function(){o.call(t)}):o.call(t))},t.onerror=function(t){nb_.errorLog("Nimble preloadOrDeferAsset error",t,e)}),n.appendChild(t),nb_.preloadedAssets.push(e.id),e.scriptEl&&e.scriptEl.parentNode&&e.scriptEl.parentNode.removeChild(e.scriptEl))}},mayBeRevealBG:function(){this.getAttribute("data-sek-src")&&(this.setAttribute("style",'background-image:url("'+this.getAttribute("data-sek-src")+'")'),this.className+=" sek-lazy-loaded",this.querySelectorAll(".sek-css-loader").forEach(function(e){nb_.isObject(e)&&e.parentNode.removeChild(e)}))}},window.NodeList&&!NodeList.prototype.forEach&&(NodeList.prototype.forEach=function(e,t){t=t||window;for(var n=0;n<this.length;n++)e.call(t,this[n],n,this)}),nb_.listenTo("nb-docready",function(){var e=document.querySelectorAll("div.sek-has-bg");!nb_.isObject(e)||e.length<1||e.forEach(function(e){nb_.isObject(e)&&(window.sekFrontLocalized&&window.sekFrontLocalized.lazyload_enabled?nb_.isInScreen(e)&&nb_.mayBeRevealBG.call(e):nb_.mayBeRevealBG.call(e))})}),"complete"===document.readyState||"loading"!==document.readyState&&!document.documentElement.doScroll)nb_.emit("nb-docready");else{var n=function(){nb_.wasEmitted("nb-docready")||nb_.emit("nb-docready")};document.addEventListener("DOMContentLoaded",n),window.addEventListener("load",n)}}(window,document);</script>
            <?php
        }




        //@'wp_head'10  when sek_load_jquery_async() && !skp_is_customizing()
        function sek_maybe_inject_jquery_migrate() {
            global $wp_scripts;
            if ( isset($wp_scripts->registered['jquery-migrate']) )
              return;
            ?>
            <script id="nb-load-jquery-migrate">!function(){var e=function(){var e=document.createElement("script");e.setAttribute("src","<?php echo NIMBLE_JQUERY_MIGRATE_URL; ?>"),e.setAttribute("id","nb-query-migrate"),e.setAttribute("defer","defer"),document.getElementsByTagName("head")[0].appendChild(e);var t=document.getElementById("nb-load-jquery-migrate");t.parentNode.removeChild(t)};nb_.listenTo("nb-jquery-loaded",function(){if(nb_.hasPreloadSupport()){var t=document.createElement("link");t.setAttribute("href","<?php echo NIMBLE_JQUERY_MIGRATE_URL; ?>"),t.setAttribute("rel","preload"),t.setAttribute("id","nb-query-migrate"),t.setAttribute("as","script"),t.onload=function(){this.onload=null,this.rel="script",e()},document.getElementsByTagName("head")[0].appendChild(t)}else e()})}();</script>
            <?php
        }



        //@'wp_head'PHP_INT_MAX
        function sek_print_style_for_css_loader() {
          if ( !skp_is_customizing() && !Nimble_Manager()->page_has_local_or_global_sections )
            return;

          // if ( !sek_is_img_smartload_enabled() || skp_is_customizing() )
          //   return;
          ?>
          <style id="nb-lazyload-css-loader">@-webkit-keyframes sek-mr-loader{0%{-webkit-transform:scale(.1);transform:scale(.1);opacity:1}70%{-webkit-transform:scale(1);transform:scale(1);opacity:.7}100%{opacity:0}}@keyframes sek-mr-loader{0%{-webkit-transform:scale(.1);transform:scale(.1);opacity:1}70%{-webkit-transform:scale(1);transform:scale(1);opacity:.7}100%{opacity:0}}.sek-css-loader{width:50px;height:50px;position:absolute;-webkit-transform:translate3d(-50%,-50%,0);transform:translate3d(-50%,-50%,0);top:50%;left:50%;z-index: 1000;}.csstransforms3d .sek-css-loader{display:block}.sek-mr-loader>div:nth-child(0){-webkit-animation-delay:-.8s;animation-delay:-.8s}.sek-mr-loader>div:nth-child(1){-webkit-animation-delay:-.6s;animation-delay:-.6s}.sek-mr-loader>div:nth-child(2){-webkit-animation-delay:-.4s;animation-delay:-.4s}.sek-mr-loader>div:nth-child(3){-webkit-animation-delay:-.2s;animation-delay:-.2s}.sek-mr-loader>div{-webkit-animation-fill-mode:both;animation-fill-mode:both;position:absolute;top:0;left:0;width:100%;height:100%;border-radius:100%;border:2px solid #777;-webkit-animation:sek-mr-loader 1.25s 0s infinite cubic-bezier(.21,.53,.56,.8);animation:sek-mr-loader 1.25s 0s infinite cubic-bezier(.21,.53,.56,.8)}.white-loader>.sek-mr-loader>div{border:2px solid #fff}</style>
          <?php
        }


        // @'wp_head'PHP_INT_MAX
        // introduced for https://github.com/presscustomizr/nimble-builder/issues/626
        // jQuery can potentially be loaded async, so let's react to its load or the presence of window.jQuery
        function sek_detect_jquery() {
            if ( !skp_is_customizing() && !Nimble_Manager()->page_has_local_or_global_sections )
              return;
            ?>
            <script id="nimble-detect-jquery">!function(){var e=function(){var e="nb-jquery-loaded";nb_.wasEmitted(e)||nb_.emit(e)},o=function(n){n=n||0,void 0!==window.jQuery?e():n<30?setTimeout(function(){o(++n)},200):window.console&&window.console.log&&console.log("Nimble Builder problem : jQuery.js was not detected on your website")},n=document.getElementById("<?php echo NIMBLE_JQUERY_ID; ?>");n&&n.addEventListener("load",function(){e()}),o()}();</script>
            <?php
        }


        // @'wp_footer'PHP_INT_MAX
        // introduced for https://github.com/presscustomizr/nimble-builder/issues/626
        function sek_preload_jquery_from_dns() {
            // Check that current page has Nimble content before printing anything
            // For https://github.com/presscustomizr/nimble-builder/issues/649
            if ( !Nimble_Manager()->page_has_local_or_global_sections )
              return;

            if( sek_is_jquery_replaced() && !skp_is_customizing() ) {
            ?>
            <script id="nb-load-jquery">setTimeout( function() {
                nb_.preloadOrDeferAsset( {
                    id : '<?php echo NIMBLE_JQUERY_ID; ?>',
                    as : 'script',
                    href : '<?php echo NIMBLE_JQUERY_LATEST_CDN_URL; ?>',
                    scriptEl : document.currentScript
                });
            }, 0 );
            </script>
            <?php
            }
        }//sek_preload_jquery_from_dns()


        //@'wp_footer'PHP_INT_MAX
        function sek_maybe_load_scripts_in_ajax() {
            // Check that current page has Nimble content before printing anything
            // For https://github.com/presscustomizr/nimble-builder/issues/649
            if ( !Nimble_Manager()->page_has_local_or_global_sections )
              return;

            if ( !sek_load_front_assets_in_ajax() )
              return;
            ?>
            <script id="nb-load-assets-dynamically">window,document,nb_.listenTo("nb-jquery-loaded",function(){nb_.scriptsLoadingStatus={},nb_.ajaxLoadScript=function(t){jQuery(function(a){t=a.extend({path:"",complete:"",loadcheck:!1},t),nb_.scriptsLoadingStatus[t.path]&&"pending"===nb_.scriptsLoadingStatus[t.path].state()||(nb_.scriptsLoadingStatus[t.path]=nb_.scriptsLoadingStatus[t.path]||a.Deferred(),jQuery.ajax({url:sekFrontLocalized.frontAssetsPath+t.path+"?"+sekFrontLocalized.assetVersion,cache:!0,dataType:"script"}).done(function(){"function"!=typeof t.loadcheck||t.loadcheck()?"function"==typeof t.complete&&t.complete():nb_.errorLog("ajaxLoadScript success but loadcheck failed for => "+t.path)}).fail(function(){nb_.errorLog("ajaxLoadScript failed for => "+t.path)}))})}});</script>

            <?php // Load main script ?>
            <script id="nb-load-front-script-and-styles">
              nb_.listenTo('nb-jquery-loaded', function() {
                  jQuery(function($){
                      if ( !sekFrontLocalized.load_front_assets_on_scroll )
                          return;
                      // Main script
                      nb_.ajaxLoadScript({ path : sekFrontLocalized.isDevMode ? 'js/ccat-nimble-front.js' : 'js/ccat-nimble-front.min.js'});

                      // Partial scripts
                      $.each( sekFrontLocalized.partialFrontScripts, function( _name, _event ){
                          nb_.listenTo( _event, function() {
                              nb_.ajaxLoadScript({ path : sekFrontLocalized.isDevMode ? 'js/partials/' + _name + '.js' : 'js/partials/' + _name + '.min.js'});
                          });
                      });

                  });
              });
            </script>
            <?php
        }
    }//class
endif;
?>