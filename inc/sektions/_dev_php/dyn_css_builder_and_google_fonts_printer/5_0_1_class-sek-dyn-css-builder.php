<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}


/**
 *  Sek Dyn CSS Builder: class responsible for building Stylesheet from a sek model
 */
class Sek_Dyn_CSS_Builder {

    /*min widths, considering CSS min widths BP:
    $grid-breakpoints: (
        xs: 0,
        sm: 576px,
        md: 768px,
        lg: 992px,
        xl: 1200px
    )

    we could have a constant array since php 5.6
    */
    public static $breakpoints = [
        'xs' => 0,
        'sm' => 576,
        'md' => 768,
        'lg' => 992,
        'xl' => 1200
    ];

    const COLS_MOBILE_BREAKPOINT  = 'md';

    private $collection;//the collection of css rules
    private $sek_model;
    private $parent_level_model = array();

    public function __construct( $sek_model = array() ) {
        $this->sek_model  = $sek_model;

        // error_log('<' . __CLASS__ . ' ' . __FUNCTION__ . ' =>saved sektions>');
        // error_log( print_r( $this -> sek_model, true ) );
        // error_log('</' . __CLASS__ . ' ' . __FUNCTION__ . ' =>saved sektions>');

        // set the css rules for columns
        /* ------------------------------------------------------------------------- *
         *  SCHEDULE CSS RULES FILTERING
        /* ------------------------------------------------------------------------- */
        add_filter( 'sek_add_css_rules_for_level_options', array( $this, 'sek_add_rules_for_column_width' ), 10, 2 );

        $this->sek_css_rules_sniffer_walker();
    }


    // Fired in the constructor
    // Walk the level tree and build rules when needed
    // The rules are filtered when some conditions are met.
    // This allows us to schedule the css rules addition remotely :
    // - from the module registration php file
    // - from the generic input types ( @see sek_add_css_rules_for_generic_css_input_types() )
    public function sek_css_rules_sniffer_walker( $level = null, $parent_level = array() ) {
        $level      = is_null( $level ) ? $this->sek_model : $level;
        $level      = is_array( $level ) ? $level : array();

        // The parent level is set when the function is invoked recursively, from a level where we actually have a 'level' property
        if ( ! empty( $parent_level ) ) {
            $this -> parent_level_model = $parent_level;
        }

        foreach ( $level as $key => $entry ) {
             $rules = array();
            // Populate rules for sections / columns / modules
            if ( !empty( $entry[ 'level' ] ) && ( !empty( $entry[ 'options' ] ) || !empty( $entry[ 'width' ] ) ) ) {
                // build rules for level options => section / column / module
                $rules = apply_filters( 'sek_add_css_rules_for_level_options', $rules, $entry );
            }

            // populate rules for modules values
            if ( !empty( $entry[ 'level' ] ) && 'module' === $entry['level'] ) {
                // build rules for modules
                $rules = apply_filters( 'sek_add_css_rules_for_modules', $rules, $entry );
            }

            // When we are inside the associative arrays of the module 'value' or the level 'options' entries
            // the keys are not integer.
            // We want to filter each input
            // which makes it possible to target for example the font-family. Either in module values or in level options
            if ( empty( $entry[ 'level' ] ) && is_string( $key ) && 1 < strlen( $key ) ) {
                // we need to have a level model set
                if ( !empty( $this -> parent_level_model ) ) {
                    // the input_id candidate to filter is the $key
                    $input_id_candidate = $key;
                    // let's skip the $key that are reserved for the structure of the sektion tree
                    if ( ! in_array( $key, [ 'level', 'collection', 'id', 'module_type', 'options'] ) ) {
                        $rules = apply_filters( "sek_add_css_rules_for_input_id", $rules, $entry, $input_id_candidate, $this -> parent_level_model );
                    }
                }
            }

            // populates the rules collection
            if ( !empty( $rules ) ) {
                /*error_log('<ALOORS RULE ?>');
                error_log(print_r( $rules, true ) );
                error_log('<ALOORS RULE ?>');*/
                //TODO: MAKE SURE RULE ARE NORMALIZED
                foreach( $rules as $rule ) {
                    if ( ! is_array( $rule ) ) {
                        error_log( '<' . __CLASS__ . '::' . __FUNCTION__ . '>');
                        error_log( ' => a css rule should be represented by an array' );
                        error_log( print_r( $rule, true ) );
                        error_log( '</' . __CLASS__ . '::' . __FUNCTION__ . '>');
                        continue;
                    }
                    if ( empty( $rule['selector']) ) {
                        error_log( '<' . __CLASS__ . '::' . __FUNCTION__ . '>');
                        error_log( ' => a css rule is missing the selector param' );
                        error_log( print_r( $rule, true ) );
                        error_log( '</' . __CLASS__ . '::' . __FUNCTION__ . '>');
                        continue;
                    }
                    $this->sek_populate(
                        $rule[ 'selector' ],
                        $rule[ 'css_rules' ],
                        $rule[ 'mq' ]
                    );
                }//foreach
            }

            // keep walking if the current $entry is an array
            // make sure that the parent_level_model is set right before jumping down to the next level
            if ( is_array( $entry ) ) {
                // Can we set a parent level ?
                if ( !empty( $entry['level'] ) && in_array( $entry['level'], array( 'location', 'section', 'column', 'module' ) ) ) {
                    $parent_level = $entry;
                }
                // Let's go recursive
                $this->sek_css_rules_sniffer_walker( $entry, $parent_level );


            }
            // Reset the parent level model because it might have been modified after walking the sublevels
            if ( ! empty( $parent_level ) ) {
                $this -> parent_level_model = $parent_level;
            }
        }//foreach
    }//sek_css_rules_sniffer_walker()



    // @return void()
    // populates the css rules ::collection property, organized by media queries
    public function sek_populate( $selector, $css_rules, string $mq = null ) {
        if ( ! is_string( $selector ) )
            return;
        if ( ! is_string( $css_rules ) )
            return;

        // Assign a default media device
        //TODO: allowed media query?
        $mq_device = 'all_devices';

        // If a media query is requested, build it
        if ( !empty( $mq ) ) {
            if ( false === strpos($mq, 'max') && false === strpos($mq, 'min')) {
                error_log( __FUNCTION__ . ' ' . __CLASS__ . ' => the media queries only accept max-width and min-width rules');
            } else {
                $mq_device = $mq;
            }
        }

        // if the media query for this device is not yet added, add it
        if ( !isset( $this->collection[ $mq_device ] ) ) {
            $this->collection[ $mq_device ] = array();
        }

        if ( !isset( $this->collection[ $mq_device ][ $selector ] ) ) {
            $this->collection[ $mq_device ][ $selector ] = array();
        }

        $this->collection[ $mq_device ][ $selector ][] = $css_rules;
    }//sek_populate



    // @return string
    private function sek_maybe_wrap_in_media_query( $css,  $mq_device = 'all_devices' ) {
        if ( 'all_devices' === $mq_device ) {
            return $css;
        }
        return sprintf( '@media(%1$s){%2$s}', $mq_device, $css);
    }


    // sorts the media queries from all_devices to the smallest width
    // This doesn't make the difference between max-width and min-width
    // @return integer
    private function user_defined_array_key_sort_fn($a, $b) {
        if ( 'all_devices' === $a ) {
            return -1;
        }
        if ( 'all_devices' === $b ) {
            return 1;
        }
        $a_int = (int)preg_replace('/[^0-9]/', '', $a) * 1;
        $b_int = (int)preg_replace('/[^0-9]/', '', $b) * 1;

        return $b_int - $a_int;
    }

    //@returns a stringified stylesheet, ready to be printed on the page or in a file
    public function get_stylesheet() {
        $css = '';
        // error_log('<mq collection>');
        // error_log( print_r( $this->collection, true ) );
        // error_log('</mq collection>');
        if ( ! is_array( $this->collection ) || empty( $this->collection ) )
          return $css;
        // Sort the collection by media queries
        uksort( $this->collection, array( $this, 'user_defined_array_key_sort_fn' ) );

        // process
        foreach ( $this->collection as $mq_device => $selectors ) {
            $_css = '';
            foreach ( $selectors as $selector => $css_rules ) {
                $css_rules = is_array( $css_rules ) ? implode( ';', $css_rules ) : $css_rules;
                $_css .=  $selector . '{' . $css_rules . '}';
            }
            $_css = $this->sek_maybe_wrap_in_media_query( $_css, $mq_device );
            $css .= $_css;
        }
        return $css;
    }








    // hook : sek_add_css_rules_for_level_options
    public function sek_add_rules_for_column_width( array $rules, array $level ) {
        $width   = empty( $level[ 'width' ] ) || !is_numeric( $level[ 'width' ] ) ? '' : $level['width'];

        //width
        if ( empty( $width ) )
          return $rules;

        $css_rules = sprintf( '-ms-flex: 0 0 %1$s%%;flex: 0 0 %1$s%%;max-width: %1$s%%', $width );
        $rules[] = array(
            'selector'      => '.sek-column[data-sek-id="'.$level['id'].'"]',
            'css_rules'     => $css_rules,
            'mq'            => 'min-width:' . self::$breakpoints[ self::COLS_MOBILE_BREAKPOINT ] .'px'
        );
        return $rules;
    }


}//end class

?>