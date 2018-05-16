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
    private static $breakpoints = [
        'xs' => 0,
        'sm' => 576,
        'md' => 768,
        'lg' => 992,
        'xl' => 1200
    ];

    const COLS_MOBILE_BREAKPOINT  = 'md';

    private $stylesheet;
    private $sek_model;
    private $parent_level = array();
    //public $gfonts = array();

    public function __construct( $sek_model = array(), Sek_Stylesheet $stylesheet ) {
        $this->stylesheet = $stylesheet;
        $this->sek_model  = $sek_model;

        // error_log('<' . __CLASS__ . ' ' . __FUNCTION__ . ' =>saved sektions>');
        // error_log( print_r( $this -> sek_model, true ) );
        // error_log('</' . __CLASS__ . ' ' . __FUNCTION__ . ' =>saved sektions>');

        // set the css rules for columns
        /* ------------------------------------------------------------------------- *
         *  SCHEDULE CSS RULES FILTERING
        /* ------------------------------------------------------------------------- */
        add_filter( 'sek_add_css_rules_for_level_options', array( $this, 'sek_add_rules_for_column_width' ), 10, 2 );

        do_action('sek_dyn_css_builder_initialized');

        $this->sek_css_rules_sniffer_walker();
    }


    // Fired in the constructor
    // Walk the level tree and build rules when needed
    public function sek_css_rules_sniffer_walker( $level = null, $stylesheet = null ) {
        $level      = is_null( $level ) ? $this->sek_model : $level;
        $level      = is_array( $level ) ? $level : array();

        $stylesheet = is_null( $stylesheet ) ? $this->stylesheet : $stylesheet;
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
                // we need to have a parent level set
                if ( !empty( $this -> parent_level ) ) {
                    // the input_id candidate to filter is the $key
                    $input_id_candidate = $key;
                    // let's skip the $key that are reserved for the structure of the sektion tree
                    if ( ! in_array( $key, [ 'level', 'collection', 'id', 'module_type', 'options'] ) ) {
                        $rules = apply_filters( "sek_add_css_rules_for_input_id", $rules, $entry, $input_id_candidate, $this -> parent_level );
                    }
                }
            }

            //fill the stylesheet
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
                    $this->stylesheet->sek_add_rule(
                        $rule[ 'selector' ],
                        $rule[ 'style_rules' ],
                        $rule[ 'mq' ]
                    );
                }//foreach
            }

            // keep walking if the current $entry is an array
            // make sure that the parent_level is set right before jumping down the next level
            if ( is_array( $entry ) ) {
                if ( !empty( $entry['level'] ) && in_array( $entry['level'], array( 'location', 'section', 'column', 'module' ) ) ) {
                    $this -> parent_level = $entry;
                }
                $this->sek_css_rules_sniffer_walker( $entry, $stylesheet );
                // Reset the parent level after walking the sublevels
                if ( !empty( $entry['level'] ) && in_array( $entry['level'], array( 'location', 'section', 'column', 'module' ) ) ) {
                    $this -> parent_level = $entry;
                }
            }
        }//foreach
    }


    // hook : sek_add_css_rules_for_level_options
    public function sek_add_rules_for_column_width( array $rules, array $level ) {
        $width   = empty( $level[ 'width' ] ) || !is_numeric( $level[ 'width' ] ) ? '' : $level['width'];

        //width
        if ( empty( $width ) )
          return $rules;

        $style_rules = sprintf( '-ms-flex: 0 0 %1$s%%;flex: 0 0 %1$s%%;max-width: %1$s%%', $width );
        $rules[] = array(
            'selector'      => '.sek-column[data-sek-id="'.$level['id'].'"]',
            'style_rules'   => $style_rules,
            'mq'            => array( 'min' => self::$breakpoints[ self::COLS_MOBILE_BREAKPOINT ] )
        );

        return $rules;
    }

}//end class

?>