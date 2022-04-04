<?php
if ( !class_exists( 'SEK_Front_Render_Css' ) ) :
    class SEK_Front_Render_Css extends SEK_Front_Render {
        // Fired in __construct()
        function _setup_hook_for_front_css_printing_or_enqueuing() {
            add_action( 'wp_enqueue_scripts', array( $this, 'print_or_enqueue_seks_style'), PHP_INT_MAX );

            // wp_add_inline_style for global CSS
            add_action( 'wp_head', array( $this, 'sek_enqueue_global_css' ) );
            
        }


        // Can be fired :
        // 1) on wp_enqueue_scripts or wp_head
        // 2) when ajaxing, for actions 'sek-resize-columns', 'sek-refresh-stylesheet'
        function print_or_enqueue_seks_style( $skope_id = null ) {
            // when this method is fired in a customize preview context :
            //    - the skope_id has to be built. Since we are after 'wp', this is not a problem.
            //    - the css rules are printed inline in the <head>
            //    - we set to hook to wp_head
            //
            // when the method is fired in an ajax refresh scenario, like 'sek-refresh-stylesheet'
            //    - the skope_id must be passed as param
            //    - the css rules are printed inline in the <head>
            //    - we set the hook to ''

            // AJAX REQUESTED STYLESHEET
            if ( ( !is_null( $skope_id ) && !empty( $skope_id ) ) && ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
                if ( !isset($_POST['local_skope_id']) ) {
                    sek_error_log( __CLASS__ . '::' . __FUNCTION__ . ' => error missing local_skope_id');
                    return;
                }
                $local_skope_id = sanitize_text_field($_POST['local_skope_id']);

                 // Feb 2021 => for site template #478
                $local_skope_id = apply_filters( 'nb_set_skope_id_before_generating_local_front_css', $local_skope_id );

                $css_handler_instance = $this->_instantiate_css_handler( array( 'skope_id' => $skope_id, 'is_global_stylesheet' => NIMBLE_GLOBAL_SKOPE_ID === $skope_id ) );
                $this->sek_get_global_css_for_ajax();
            }
            // in a front normal context, the css is enqueued from the already written file.
            else {
                // Feb 2021 => for site template #478
                $local_skope_id = apply_filters( 'nb_set_skope_id_before_generating_local_front_css', skp_build_skope_id() );

                // LOCAL SECTIONS STYLESHEET
                $this->_instantiate_css_handler( array( 'skope_id' => $local_skope_id ) );
                // GLOBAL SECTIONS STYLESHEET
                // Can hold rules for global sections and global styling
                $this->_instantiate_css_handler( array( 'skope_id' => NIMBLE_GLOBAL_SKOPE_ID, 'is_global_stylesheet' => true ) );
            }
            $google_fonts_print_candidates = $this->sek_get_gfont_print_candidates( $local_skope_id );
            // GOOGLE FONTS
            if ( !empty( $google_fonts_print_candidates ) ) {
                // When customizing we get the google font content
                if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
                    $this->sek_get_gfont_in_ajax( $google_fonts_print_candidates );
                } else {
                    // preload implemented for https://github.com/presscustomizr/nimble-builder/issues/629
                    if ( !skp_is_customizing() && sek_preload_google_fonts_on_front() ) {
                        add_action( 'wp_head', array( $this, 'sek_gfont_print_with_preload') );
                    } else {
                        // March 2020 added param display=swap => Ensure text remains visible during webfont load #572
                        wp_enqueue_style(
                            NIMBLE_GOOGLE_FONTS_STYLESHEET_ID,
                            sprintf( '//fonts.googleapis.com/css?family=%s&display=swap', $google_fonts_print_candidates ),
                            array(),
                            null,
                            'all'
                        );
                    }
                }
            }

            if ( defined( 'DOING_AJAX' ) && DOING_AJAX && empty( $local_skope_id ) ) {
                sek_error_log(  __CLASS__ . '::' . __FUNCTION__ . ' => the skope_id should not be empty' );
            }
        }//print_or_enqueue_seks_style


        


        
        // @param params = array( array( 'skope_id' => NIMBLE_GLOBAL_SKOPE_ID, 'is_global_stylesheet' => true ) )
        // fired @'wp_enqueue_scripts'
        private function _instantiate_css_handler( $params = array() ) {
            $params = wp_parse_args( $params, array( 'skope_id' => '', 'is_global_stylesheet' => false ) );

            // Print inline or enqueue ?
            $print_mode = Sek_Dyn_CSS_Handler::MODE_FILE;
            if ( is_customize_preview() ) {
              $print_mode = Sek_Dyn_CSS_Handler::MODE_INLINE;
            }
            // Which hook ?
            $fire_at_hook = '';
            if ( !defined( 'DOING_AJAX' ) && is_customize_preview() ) {
              $fire_at_hook = 'wp_head';
            }

            $css_handler_instance = new Sek_Dyn_CSS_Handler( array(
                'id'             => $params['skope_id'],
                'skope_id'       => $params['skope_id'],
                // property "is_global_stylesheet" has been added when fixing https://github.com/presscustomizr/nimble-builder/issues/273
                'is_global_stylesheet' => $params['is_global_stylesheet'],
                'mode'           => $print_mode,
                //these are taken in account only when 'mode' is 'file'
                'force_write'    => true, //<- write if the file doesn't exist
                'force_rewrite'  => is_user_logged_in() && current_user_can( 'customize' ), //<- write even if the file exists
                'hook'           => $fire_at_hook
            ));
            return $css_handler_instance;
        }
        

        // When ajaxing, the link#sek-gfonts-{$this->id} gets removed from the dom and replaced by this string
        // March 2020 added param display=swap => Ensure text remains visible during webfont load #572
        function sek_get_gfont_in_ajax( $print_candidates ) {
            if ( !empty( $print_candidates ) ) {
                printf('<link rel="stylesheet" id="%1$s" href="%2$s">',
                    esc_attr(NIMBLE_GOOGLE_FONTS_STYLESHEET_ID),
                    esc_url("//fonts.googleapis.com/css?family={$print_candidates}&display=swap")
                );
            }
        }

        // hook : wp_footer
        // fired on front only when not customizing
        // March 2020 preload implemented for https://github.com/presscustomizr/nimble-builder/issues/629
        // March 2020 added param display=swap => Ensure text remains visible during webfont load #572
        function sek_gfont_print_with_preload( $print_candidates = '' ) {
            // Check that current page has Nimble content before printing any Google fonts
            // For https://github.com/presscustomizr/nimble-builder/issues/649
            if ( !Nimble_Manager()->page_has_local_or_global_sections )
              return;
            // print candidates must be fetched when sek_preload_google_fonts_on_front()
            $print_candidates = $this->sek_get_gfont_print_candidates();

            if ( !empty( $print_candidates ) ) {
                ob_start();
                ?>
                nb_.preloadOrDeferAsset( { id : '<?php echo esc_attr(NIMBLE_GOOGLE_FONTS_STYLESHEET_ID); ?>', as : 'style', href : '//fonts.googleapis.com/css?family=<?php echo esc_attr($print_candidates); ?>&display=swap', scriptEl : document.currentScript } );
                <?php
                $script = ob_get_clean();
                wp_register_script( 'nb_preload_gfonts', '');
                wp_enqueue_script( 'nb_preload_gfonts' );
                wp_add_inline_script( 'nb_preload_gfonts', $script );
            }
        }



        // invoked when ajaxing during customization
        function sek_get_global_css_for_ajax() {
            // During customization, always rebuild the css from fresh values instead of relying on the saved option
            // because on first call we get the customized option value, but on another one quickly after, we get the current option value in the database
            if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
                $global_css = $this->sek_build_global_options_inline_css();
                if ( is_string( $global_css ) && !empty( $global_css ) ) {
                    printf('<style id="%1$s">%2$s</style>', NIMBLE_GLOBAL_OPTIONS_STYLESHEET_ID, $global_css );
                }
            }
        }


        // hook wp_enqueue_script
        function sek_enqueue_global_css() {
            $global_css = get_option(NIMBLE_OPT_FOR_GLOBAL_CSS);
            // following https://developer.wordpress.org/reference/functions/wp_add_inline_script/#comment-5304
            wp_register_style( NIMBLE_GLOBAL_OPTIONS_STYLESHEET_ID, false );
            wp_enqueue_style( NIMBLE_GLOBAL_OPTIONS_STYLESHEET_ID );
            wp_add_inline_style( NIMBLE_GLOBAL_OPTIONS_STYLESHEET_ID, $global_css );
        }


        // Maybe update global inline style with a filter
        // @return a css string
        // This CSS is the one generated by global options like global text, global width, global breakpoint
        function sek_build_global_options_inline_css() {
              return apply_filters('nimble_set_global_inline_style', '');
        }


        //@return string
        // sek_model is passed when customizing in SEK_Front_Render_Css::print_or_enqueue_seks_style()
        function sek_get_gfont_print_candidates( $local_skope_id = null ) {
            // return the cache version if already set
            if ( 'not_set' !== Nimble_Manager()->google_fonts_print_candidates )
              return Nimble_Manager()->google_fonts_print_candidates;

            $local_skope_id = is_null( $local_skope_id ) ? apply_filters( 'maybe_set_skope_id_for_site_template_css', skp_build_skope_id() ) : $local_skope_id;
            // local sections
            $local_seks = sek_get_skoped_seks( $local_skope_id );
            // global sections
            $global_seks = sek_get_skoped_seks( NIMBLE_GLOBAL_SKOPE_ID );
            // global options
            $global_options = get_option( NIMBLE_OPT_NAME_FOR_GLOBAL_OPTIONS );

            $print_candidates = '';
            $ffamilies = array();

            // Let's build the collection of google fonts from local sections, global sections, global options
            if ( is_array( $local_seks ) && !empty( $local_seks['fonts'] ) && is_array( $local_seks['fonts'] ) ) {
                $ffamilies = $local_seks['fonts'];
            }
            if ( is_array( $global_seks ) && !empty( $global_seks['fonts'] ) && is_array( $global_seks['fonts'] ) ) {
                $ffamilies = array_merge( $ffamilies, $global_seks['fonts'] );
            }
            if ( is_array( $global_options ) && !empty( $global_options['fonts'] ) && is_array( $global_options['fonts'] ) ) {
                $ffamilies = array_merge( $ffamilies, $global_options['fonts'] );
            }

            // remove duplicate if any
            $ffamilies = array_unique( $ffamilies );

            if ( !empty( $ffamilies ) ) {
                $ffamilies = implode( "|", $ffamilies );
                $print_candidates = str_replace( '|', '%7C', $ffamilies );
                $print_candidates = str_replace( '[gfont]', '' , $print_candidates );
            }
            // cache now
            Nimble_Manager()->google_fonts_print_candidates = $print_candidates;
            return Nimble_Manager()->google_fonts_print_candidates;
        }
    }//class
endif;

?>