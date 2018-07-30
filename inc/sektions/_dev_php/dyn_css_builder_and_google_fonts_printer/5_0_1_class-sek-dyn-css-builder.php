<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
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
        // set the css rules for columns
        /* ------------------------------------------------------------------------- *
         *  SCHEDULE CSS RULES FILTERING
        /* ------------------------------------------------------------------------- */
        // filter fired in sek_css_rules_sniffer_walker()
        add_filter( 'sek_add_css_rules_for_level_options', array( $this, 'sek_add_rules_for_column_width' ), 10, 2 );

        $this->sek_css_rules_sniffer_walker();

        // add user local custom css
        add_filter( 'nimble_get_dynamic_stylesheet', array( $this, 'sek_add_raw_local_custom_css'));
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

        // If the current level is a module, check if the module has more specific css selectors specified on registration
        $module_level_css_selectors = null;
        $registered_input_list = null;
        if ( ! empty( $parent_level['module_type'] ) ) {
            $module_level_css_selectors = sek_get_registered_module_type_property( $parent_level['module_type'], 'css_selectors' );
            $registered_input_list = sek_get_registered_module_input_list( $parent_level['module_type'] );
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
                if ( ! empty( $entry['module_type'] ) ) {
                    $module_type = $entry['module_type'];
                    // build rules for modules
                    // applying sek_normalize_module_value_with_defaults() allows us to access all the value properties of the module without needing to check their existence
                    $rules = apply_filters( "sek_add_css_rules_for_module_type___{$module_type}", $rules, sek_normalize_module_value_with_defaults( $entry ) );
                }
            }

            // When we are inside the associative arrays of the module 'value' or the level 'options' entries
            // the keys are not integer.
            // We want to filter each input
            // which makes it possible to target for example the font-family. Either in module values or in level options
            if ( empty( $entry[ 'level' ] ) && is_string( $key ) && 1 < strlen( $key ) ) {
                // we need to have a level model set
                if ( !empty( $parent_level ) && is_array( $parent_level ) && ! empty( $parent_level['module_type'] ) ) {
                    // the input_id candidate to filter is the $key
                    $input_id_candidate = $key;
                    // let's skip the $key that are reserved for the structure of the sektion tree
                    // ! in_array( $key, [ 'level', 'collection', 'id', 'module_type', 'options', 'value' ] )
                    // The generic rules must be suffixed with '_css'
                    if ( false !== strpos( $input_id_candidate, '_css') ) {
                        if ( is_array( $registered_input_list ) && ! empty( $registered_input_list[ $input_id_candidate ] ) && ! empty( $registered_input_list[ $input_id_candidate ]['css_identifier'] ) ) {
                            $rules = apply_filters(
                                "sek_add_css_rules_for_input_id",
                                $rules,// <= the in-progress array of css rules to be populated
                                $entry,// <= the css property value
                                $input_id_candidate, // <= the unique input_id as it as been declared on module registration
                                $registered_input_list,// <= the full list of input for the module
                                $parent_level,// <= the parent module level. can be one of those array( 'location', 'section', 'column', 'module' )
                                $module_level_css_selectors // <= if the parent is a module, a default set of css_selectors might have been specified on module registration
                            );
                        } else {
                            sek_error_log( __FUNCTION__ . ' => missing the css_identifier param when registering module ' . $parent_level['module_type'] . ' for a css input candidate : ' . $key, $parent_level );
                        }
                    }
                }//if
            }//if

            // populates the rules collection
            if ( !empty( $rules ) ) {

                //TODO: MAKE SURE RULE ARE NORMALIZED
                foreach( $rules as $rule ) {
                    if ( ! is_array( $rule ) ) {
                        sek_error_log( __CLASS__ . '::' . __FUNCTION__ . ' => a css rule should be represented by an array', $rule );
                        continue;
                    }
                    if ( empty( $rule['selector']) ) {
                        sek_error_log(  __CLASS__ . '::' . __FUNCTION__ . '=> a css rule is missing the selector param', $rule );
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
    public function sek_populate( $selector, $css_rules, $mq = '' ) {
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
        if ( false === strpos($mq_device, '(') || false === strpos($mq_device, ')') ) {
            sek_error_log( __CLASS__ . '::' . __FUNCTION__ . ' => missing parenthesis in the media queries', $mq_device );
            return $css;
        }
        return sprintf( '@media%1$s{%2$s}', $mq_device, $css);
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
        if ( is_array( $this->collection ) && !empty( $this->collection ) ) {
            // Sort the collection by media queries
            uksort( $this->collection, array( $this, 'user_defined_array_key_sort_fn' ) );

            // process
            foreach ( $this->collection as $mq_device => $selectors ) {
                $_css = '';
                foreach ( $selectors as $selector => $css_rules ) {
                    $css_rules = is_array( $css_rules ) ? implode( ';', $css_rules ) : $css_rules;
                    $_css .=  $selector . '{' . $css_rules . '}';
                    $_css =  str_replace(';;', ';', $_css);//@fixes https://github.com/presscustomizr/nimble-builder/issues/137
                }
                $_css = $this->sek_maybe_wrap_in_media_query( $_css, $mq_device );
                $css .= $_css;
            }
        }
        return apply_filters( 'nimble_get_dynamic_stylesheet', $css );
    }








    // hook : sek_add_css_rules_for_level_options
    public function sek_add_rules_for_column_width( $rules, $column ) {
        if ( ! is_array( $column ) )
          return $rules;

        if ( empty( $column['level'] ) || 'column' !== $column['level'] )
          return $rules;

        $width   = empty( $column[ 'width' ] ) || !is_numeric( $column[ 'width' ] ) ? '' : $column['width'];

        // width
        if ( empty( $width ) )
          return $rules;

        $breakpoint = self::$breakpoints[ self::COLS_MOBILE_BREAKPOINT ];

        // Does the parent section have a custom breakpoint set ?
        $parent_section = sek_get_parent_level_model( $column['id'] );
        if ( is_array( $parent_section ) && !empty( $parent_section[ 'options' ] ) && !empty( $parent_section[ 'options' ][ 'breakpoint' ] ) && !empty( $parent_section[ 'options' ][ 'breakpoint' ][ 'custom-breakpoint' ] ) ) {
            $custom_parent_breakpoint = intval( $parent_section [ 'options' ][ 'breakpoint' ][ 'custom-breakpoint' ] );
            $breakpoint = $custom_parent_breakpoint < 0 ? $breakpoint : $custom_parent_breakpoint;
        }

        // Note : the css selector must be specific enough to override the possible parent section ( or global ) custom breakpoint one.
        // @see sek_add_css_rules_for_level_breakpoint()
        $rules[] = array(
            'selector'      => sprintf( '[data-sek-id="%1$s"] .sek-sektion-inner > .sek-column[data-sek-id="%2$s"]', $parent_section['id'], $column['id'] ),
            'css_rules'     => sprintf( '-ms-flex: 0 0 %1$s%%;flex: 0 0 %1$s%%;max-width: %1$s%%', $width ),
            'mq'            => "(min-width:{$breakpoint}px)"
        );
        return $rules;
    }


    //@filter 'nimble_get_dynamic_stylesheet'
    public function sek_add_raw_local_custom_css( $css ) {
        // we use the ajaxily posted skope_id when available <= typically in a customizing ajax action 'sek-refresh-stylesheet'
        // otherwise we fallback on the normal utility skp_build_skope_id()
        $localSkopeNimble = sek_get_skoped_seks( !empty( $_POST['skope_id'] ) ? $_POST['skope_id'] : skp_build_skope_id()  );
        if ( is_array( $localSkopeNimble ) && !empty( $localSkopeNimble['options']) && ! empty( $localSkopeNimble['options']['general'] ) && ! empty( $localSkopeNimble['options']['general']['local_custom_css'] ) ) {
            $css .= $localSkopeNimble['options']['general']['local_custom_css'];
        }
        return $css;
    }

}//end class

?>