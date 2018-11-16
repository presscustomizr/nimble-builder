<?php
if ( ! class_exists( 'SEK_Front_Render_Css' ) ) :
    class SEK_Front_Render_Css extends SEK_Front_Render {
        // Fired in __construct()
        function _setup_hook_for_front_css_printing_or_enqueuing() {
            add_action( 'wp_enqueue_scripts', array( $this, 'print_or_enqueue_seks_style') );
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
            //
            // in a front normal context, the css is enqueued from the already written file.
            // AJAX REQUESTED STYLESHEET
            if ( ( ! is_null( $skope_id ) && ! empty( $skope_id ) ) && ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
                $this->_instantiate_css_handler( $skope_id );
            } else {
                $skope_id = skp_build_skope_id();
                // LOCAL SECTIONS STYLESHEET
                $this->_instantiate_css_handler( skp_build_skope_id() );
                if ( sek_has_global_sections() ) {
                    // GLOBAL SECTIONS STYLESHEET
                    $this->_instantiate_css_handler( NIMBLE_GLOBAL_SKOPE_ID );
                }
            }
            if ( empty( $skope_id ) ) {
                sek_error_log(  __CLASS__ . '::' . __FUNCTION__ . ' =>the skope_id should not be empty' );
            }
        }//print_or_enqueue_seks_style


        private function _instantiate_css_handler( $skope_id ) {
            new Sek_Dyn_CSS_Handler( array(
                'id'             => $skope_id,
                'skope_id'       => $skope_id,
                'mode'           => is_customize_preview() ? Sek_Dyn_CSS_Handler::MODE_INLINE : Sek_Dyn_CSS_Handler::MODE_FILE,
                //these are taken in account only when 'mode' is 'file'
                'force_write'    => true, //<- write if the file doesn't exist
                'force_rewrite'  => is_user_logged_in() && current_user_can( 'customize' ), //<- write even if the file exists
                'hook'           => ( ! defined( 'DOING_AJAX' ) && is_customize_preview() ) ? 'wp_head' : ''
            ));
        }

    }//class
endif;

?>