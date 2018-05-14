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
        add_filter( 'sek_add_css_rules_for_font-family', array( $this, 'sek_add_rules_for_font_families' ), 10, 3 );
        $this->sek_css_rules_sniffer_walker();
    }


    // Fired in the constructor
    // Walk the level tree and build rules when needed
    public function sek_css_rules_sniffer_walker( $level = null, $stylesheet = null ) {
        $level      = is_null( $level ) ? $this->sek_model : $level;
        $level      = is_array( $level ) ? $level : array();

        $stylesheet = is_null( $stylesheet ) ? $this->stylesheet : $stylesheet;
        $rules = array();
        //$collection = empty( $level[ 'collection' ] ) ? array() : $level[ 'collection' ];

        foreach ( $level as $key => $entry ) {
            // Populate rules for sections / columns / modules
            if ( !empty( $entry[ 'level' ] ) && ( !empty( $entry[ 'options' ] ) || !empty( $entry[ 'width' ] ) ) ) {
                // build rules for level options => section / column / module
                $rules = apply_filters( 'sek_add_css_rules_for_level_options', $rules, $entry );
            }
            if ( !empty( $entry[ 'level' ] ) && 'module' === $entry['level'] ) {
                // build rules for modules
                $rules = apply_filters( 'sek_add_css_rules_for_modules', $rules, $entry );
            }
            // When we are inside the associative arrays of the module 'value' or the level 'options' entries
            // the keys are not integer.
            // We want to filter each input
            // which makes it possible to target for example the font-family. Either in module values or in level options
            if ( empty( $entry[ 'level' ] ) && is_string( $key ) && 1 < strlen( $key ) ) {
                $rules = apply_filters( "sek_add_css_rules_for_{$key}", $rules, $entry, $this -> parent_level );
            }

            //fill the stylesheet
            if ( !empty( $rules ) ) {
                //TODO: MAKE SURE RULE ARE NORMALIZED
                foreach( $rules as $rule ) {
                    $this->stylesheet->sek_add_rule(
                        $rule[ 'selector' ],
                        $rule[ 'style_rules' ],
                        $rule[ 'mq' ]
                    );
                }
            }

            // if ( !empty( $level[ 'collection' ] ) ) {
            //     $this->sek_css_rules_sniffer_walker( $level, $stylesheet );
            // }
            if ( is_array( $entry ) ) {
                if ( !empty( $entry['level'] ) && in_array( $entry['level'], array( 'location', 'section', 'column', 'module' ) ) ) {
                    $this -> parent_level = $entry;
                }
                $this->sek_css_rules_sniffer_walker( $entry, $stylesheet );
            }

        }
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


    // hook : sek_dyn_css_builder_rules
    // @return array() of css rules
    public function sek_add_rules_for_font_families( array $rules, $value, array $parent_level ) {
        error_log('<' . __CLASS__ . ' ' . __FUNCTION__ . ' => $parent_level>');
        error_log( print_r( $parent_level, true ) );
        error_log('</' . __CLASS__ . ' ' . __FUNCTION__ . ' => $parent_level>');
        error_log('<' . __CLASS__ . ' ' . __FUNCTION__ . ' => $value>');
        error_log( print_r( $value, true ) );
        error_log('</' . __CLASS__ . ' ' . __FUNCTION__ . ' => $value>');
        $font_family = str_replace('[cfont]', '', $value );
        $rules[] = array(
            'selector'      => '[data-sek-id="'.$parent_level['id'].'"]',
            'style_rules'   => 'font-family:' . $font_family,
            'mq'            => null
        );
        return $rules;
    }
}//end class

?>