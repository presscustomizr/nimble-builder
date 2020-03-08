<?php
if ( ! class_exists( 'SEK_Front_Render_Css' ) ) :
    class SEK_Front_Render_Css extends SEK_Front_Render {
        // Fired in __construct()
        function _setup_hook_for_front_css_printing_or_enqueuing() {
            add_action( 'wp_enqueue_scripts', array( $this, 'print_or_enqueue_seks_style'), PHP_INT_MAX );
        }

        // Can be fired :
        // 1) on wp_enqueue_scripts or wp_head
        // 2) when ajaxing, for actions 'sek-resize-columns', 'sek-refresh-stylesheet'
        function print_or_enqueue_seks_style( $skope_id = null ) {
            $google_fonts_print_candidates = '';
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
            if ( ( ! is_null( $skope_id ) && ! empty( $skope_id ) ) && ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
                if ( ! isset($_POST['local_skope_id']) ) {
                    sek_error_log( __CLASS__ . '::' . __FUNCTION__ . ' => error missing local_skope_id');
                    return;
                }
                $local_skope_id = $_POST['local_skope_id'];
                $css_handler_instance = $this->_instantiate_css_handler( array( 'skope_id' => $skope_id, 'is_global_stylesheet' => NIMBLE_GLOBAL_SKOPE_ID === $skope_id ) );
            }
            // in a front normal context, the css is enqueued from the already written file.
            else {
                $local_skope_id = skp_build_skope_id();
                // LOCAL SECTIONS STYLESHEET
                $this->_instantiate_css_handler( array( 'skope_id' => skp_build_skope_id() ) );
                // GLOBAL SECTIONS STYLESHEET
                // Can hold rules for global sections and global styling
                $this->_instantiate_css_handler( array( 'skope_id' => NIMBLE_GLOBAL_SKOPE_ID, 'is_global_stylesheet' => true ) );
            }
            $google_fonts_print_candidates = $this->sek_get_gfont_print_candidates( $local_skope_id );

            // GOOGLE FONTS
            if ( !empty( $google_fonts_print_candidates ) ) {
                // When customizing we get the google font content
                if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
                    $this->sek_gfont_print( $google_fonts_print_candidates );
                } else {
                    if ( in_array( current_filter(), array( 'wp_footer', 'wp_head' ) ) ) {
                        $this->sek_gfont_print( $google_fonts_print_candidates );
                    } else {
                        wp_enqueue_style(
                            'sek-gfonts-local-and-global',
                            sprintf( '//fonts.googleapis.com/css?family=%s', $google_fonts_print_candidates ),
                            array(),
                            null,
                            'all'
                        );
                    }
                }
            }

            if ( defined( 'DOING_AJAX' ) && DOING_AJAX && empty( $skope_id ) ) {
                sek_error_log(  __CLASS__ . '::' . __FUNCTION__ . ' =>the skope_id should not be empty' );
            }
        }//print_or_enqueue_seks_style

        // hook : wp_head
        // or fired directly when ajaxing
        // When ajaxing, the link#sek-gfonts-{$this->id} gets removed from the dom and replaced by this string
        function sek_gfont_print( $print_candidates ) {
            if ( ! empty( $print_candidates ) ) {
                printf('<link rel="stylesheet" id="%1$s" href="%2$s">',
                    'sek-gfonts-local-and-global',
                    "//fonts.googleapis.com/css?family={$print_candidates}"
                );
            }
        }

        //@return string
        // sek_model is passed when customizing in SEK_Front_Render_Css::print_or_enqueue_seks_style()
        function sek_get_gfont_print_candidates( $local_skope_id ) {
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

            if ( ! empty( $ffamilies ) ) {
                $ffamilies = implode( "|", $ffamilies );
                $print_candidates = str_replace( '|', '%7C', $ffamilies );
                $print_candidates = str_replace( '[gfont]', '' , $print_candidates );
            }
            return $print_candidates;
        }

        // @param params = array( array( 'skope_id' => NIMBLE_GLOBAL_SKOPE_ID, 'is_global_stylesheet' => true ) )
        // fired @'wp_enqueue_scripts'
        private function _instantiate_css_handler( $params = array() ) {
            $params = wp_parse_args( $params, array( 'skope_id' => '', 'is_global_stylesheet' => false ) );

            // Print inline or enqueue ?
            $print_mode = Sek_Dyn_CSS_Handler::MODE_FILE;
            if ( is_customize_preview() ) {
              $print_mode = Sek_Dyn_CSS_Handler::MODE_INLINE;
            } else if ( sek_inline_dynamic_stylesheets_on_front() ) {
              $print_mode = Sek_Dyn_CSS_Handler::MODE_INLINE;
            }
            // Which hook ?
            $fire_at_hook = '';
            if ( !defined( 'DOING_AJAX' ) && is_customize_preview() ) {
              $fire_at_hook = 'wp_head';
            }
            // introduced for https://github.com/presscustomizr/nimble-builder/issues/612
            else if ( !defined( 'DOING_AJAX' ) && !is_customize_preview() && sek_inline_dynamic_stylesheets_on_front() ) {
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

    }//class
endif;

?>