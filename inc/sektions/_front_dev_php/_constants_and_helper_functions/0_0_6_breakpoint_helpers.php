<?php
/* ------------------------------------------------------------------------- *
 *  BREAKPOINTS HELPER
/* ------------------------------------------------------------------------- */
function sek_get_global_custom_breakpoint() {
    $global_breakpoint_data = sek_get_global_option_value('breakpoint');
    if ( is_null( $global_breakpoint_data ) || empty( $global_breakpoint_data['global-custom-breakpoint'] ) )
      return;

    if ( empty( $global_breakpoint_data[ 'use-custom-breakpoint'] ) || false === sek_booleanize_checkbox_val( $global_breakpoint_data[ 'use-custom-breakpoint'] ) )
      return;

    return intval( $global_breakpoint_data['global-custom-breakpoint'] );
}


// @return bool
// introduced for https://github.com/presscustomizr/nimble-builder/issues/564
// Let us know if we need to apply the user defined custom breakpoint to all by-device customizations, like alignment
// false by default.
function sek_is_global_custom_breakpoint_applied_to_all_customizations_by_device() {
    $global_breakpoint_data = sek_get_global_option_value('breakpoint');
    if ( is_null( $global_breakpoint_data ) || empty( $global_breakpoint_data['global-custom-breakpoint'] ) )
      return false;

    if ( empty( $global_breakpoint_data[ 'use-custom-breakpoint'] ) || false === sek_booleanize_checkbox_val( $global_breakpoint_data[ 'use-custom-breakpoint'] ) )
      return false;

    // We need a custom breakpoint > 1
    if ( intval( $global_breakpoint_data['global-custom-breakpoint'] ) <= 1 )
      return;

    // apply-to-all option is unchecked by default
    // returns true when user has checked the apply to all option
    return array_key_exists('apply-to-all', $global_breakpoint_data ) && sek_booleanize_checkbox_val( $global_breakpoint_data[ 'apply-to-all' ] ) ;
}


// invoked when filtering 'sek_add_css_rules_for__section__options'
// param 'for_responsive_columns' has been introduced for https://github.com/presscustomizr/nimble-builder/issues/564
// so we can differentiate when the custom breakpoint is requested for column responsiveness or for css rules generation
// when for columns, we always apply the custom breakpoint defined by the user
// otherwise, when generating CSS rules like alignment, the custom breakpoint is applied if user explicitely checked the 'apply_to_all' option
// 'for_responsive_columns' is set to true when sek_get_closest_section_custom_breakpoint() is invoked from Nimble_Manager()::render()
// @param params array(
//  'section_model' => array(),
//  'for_responsive_columns' => bool
// )
function sek_get_section_custom_breakpoint( $params ) {
    if ( !is_array( $params ) )
      return;

    $params = wp_parse_args( $params, array(
        'section_model' => array(),
        'for_responsive_columns' => false
    ));

    $section = $params['section_model'];

    if ( !is_array( $section ) )
      return;

    if ( empty($section['id']) )
      return;

    $options = empty( $section[ 'options' ] ) ? array() : $section['options'];
    if ( empty( $options[ 'breakpoint' ] ) )
      return;

    if ( empty( $options[ 'breakpoint' ][ 'use-custom-breakpoint'] ) || false === sek_booleanize_checkbox_val( $options[ 'breakpoint' ][ 'use-custom-breakpoint'] ) )
      return;

    // assign default value if use-custom-breakpoint is checked but there's no breakpoint set.
    // this can also occur if the custom breakpoint is left to default in the customizer ( default values are not considered when saving )
    if ( empty( $options[ 'breakpoint' ][ 'custom-breakpoint' ] ) ) {
        if ( array_key_exists('custom-breakpoint', $options[ 'breakpoint' ] ) ) {
            // this is the case when user has emptied the setting
            $custom_breakpoint = 1;// added when fixing https://github.com/presscustomizr/nimble-builder/issues/623
        } else {
            $custom_breakpoint = Sek_Dyn_CSS_Builder::$breakpoints['md'];//768
        }
    } else {
        $custom_breakpoint = intval( $options[ 'breakpoint' ][ 'custom-breakpoint' ] );
    }

    if ( $custom_breakpoint <= 0 )
      return 1;

    // 1) When the breakpoint is requested for responsive columns, we always return the custom value
    if ( $params['for_responsive_columns'] )
      return $custom_breakpoint;

    // 2) Otherwise ( other CSS rules generation case, like alignment ) we make sure that user want to apply the custom breakpoint also to other by-device customizations
    return sek_is_section_custom_breakpoint_applied_to_all_customizations_by_device( $options[ 'breakpoint' ] ) ? $custom_breakpoint : null;
}


// @return bool
// introduced for https://github.com/presscustomizr/nimble-builder/issues/564
// Let us know if we need to apply the user defined custom breakpoint to all by-device customizations, like alignment
// false by default.
// @param $section_breakpoint_options = array(
//    'use-custom-breakpoint' => bool
//    'custom-breakpoint' => int
//    'apply-to-all' => bool
// )
function sek_is_section_custom_breakpoint_applied_to_all_customizations_by_device( $section_breakpoint_options ) {
    if ( !is_array( $section_breakpoint_options ) || empty( $section_breakpoint_options ) )
      return;

    if ( empty( $section_breakpoint_options[ 'use-custom-breakpoint'] ) || false === sek_booleanize_checkbox_val( $section_breakpoint_options[ 'use-custom-breakpoint'] ) )
      return;

    // We need a custom breakpoint > 1
    // Make sure the custom breakpoint has not been emptied, otherwise assign a minimal value of 1px
    // fixes : https://github.com/presscustomizr/nimble-builder/issues/623
    $custom_breakpoint = empty( $section_breakpoint_options['custom-breakpoint'] ) ? 1 : $section_breakpoint_options['custom-breakpoint'];
    if ( intval( $custom_breakpoint ) <= 1 )
      return;

    // apply-to-all option is unchecked by default
    // returns true when user has checked the apply to all option
    return array_key_exists('apply-to-all', $section_breakpoint_options ) && sek_booleanize_checkbox_val( $section_breakpoint_options[ 'apply-to-all' ] );
}


// Recursive helper
// Is also used when building the dyn_css or when firing sek_add_css_rules_for_spacing()
// @param id : mandatory
// @param collection : optional <= that's why if missing we must walk all collections : local and global
function sek_get_closest_section_custom_breakpoint( $params ) {
    $params = wp_parse_args( $params, array(
        'searched_level_id' => '',
        'collection' => 'not_set',
        'skope_id' => '',

        'last_section_breakpoint_found' => 0,
        'last_regular_section_breakpoint_found' => 0,
        'last_nested_section_breakpoint_found' => 0,

        'searched_level_id_found' => false,

        // the 'for_responsive_columns' param has been introduced for https://github.com/presscustomizr/nimble-builder/issues/564
        // so we can differentiate when the custom breakpoint is requested for column responsiveness or for css rules generation
        // when for columns, we always apply the custom breakpoint defined by the user
        // otherwise, when generating CSS rules like alignment, the custom breakpoint is applied if user explicitely checked the 'apply_to_all' option
        // 'for_responsive_columns' is set to true when sek_get_closest_section_custom_breakpoint() is invoked from Nimble_Manager()::render()
        'for_responsive_columns' => false
    ) );

    extract( $params, EXTR_OVERWRITE );

    if ( !is_string( $searched_level_id ) || empty( $searched_level_id ) ) {
        sek_error_log( __FUNCTION__ . ' => missing or invalid child_level_id param.');
        return $last_section_breakpoint_found;;
    }
    if ( $searched_level_id_found ) {
        return $last_section_breakpoint_found;
    }

    // When no collection is provided, we must walk all collections, local and global.
    if ( 'not_set' === $collection  ) {
        if ( empty( $skope_id ) ) {
            if ( is_array( $_POST ) && !empty( $_POST['location_skope_id'] ) ) {
                $skope_id = sanitize_text_field($_POST['location_skope_id']);
            } else {
                // When fired during an ajax 'customize_save' action, the skp_get_skope_id() is determined with $_POST['local_skope_id']
                // @see add_filter( 'skp_get_skope_id', '\Nimble\sek_filter_skp_get_skope_id', 10, 2 );
                $skope_id = skp_get_skope_id();
            }
        }
        if ( empty( $skope_id ) || '_skope_not_set_' === $skope_id ) {
            sek_error_log( __FUNCTION__ . ' => the skope_id should not be empty.');
        }
        $local_skope_settings = sek_get_skoped_seks( $skope_id );
        $local_collection = ( is_array( $local_skope_settings ) && !empty( $local_skope_settings['collection'] ) ) ? $local_skope_settings['collection'] : array();
        $global_skope_settings = sek_get_skoped_seks( NIMBLE_GLOBAL_SKOPE_ID );
        $global_collection = ( is_array( $global_skope_settings ) && !empty( $global_skope_settings['collection'] ) ) ? $global_skope_settings['collection'] : array();

        $collection = array_merge( $local_collection, $global_collection );
    }

    // Loop collections
    foreach ( $collection as $level_data ) {
        //sek_error_log($last_section_breakpoint_found . ' MATCH ?  => LEVEL ID AND TYPE => ' . $level_data['level'] . ' | ' . $level_data['id'] );
        // stop here and return if a match was recursively found
        if ( $searched_level_id_found )
          break;

        if ( 'section' == $level_data['level'] ) {
            $section_maybe_custom_breakpoint = intval( sek_get_section_custom_breakpoint( array( 'section_model' => $level_data, 'for_responsive_columns' => $for_responsive_columns ) ) );

            if ( !empty( $level_data['is_nested'] ) && $level_data['is_nested'] ) {
                $last_nested_section_breakpoint_found = $section_maybe_custom_breakpoint;
            } else {
                $last_nested_section_breakpoint_found = 0;//reset last nested breakpoint
                $last_regular_section_breakpoint_found = $section_maybe_custom_breakpoint;
            }
        }

        if ( array_key_exists( 'id', $level_data ) && $searched_level_id == $level_data['id'] ) {
            //match found, break this loop
            if ( $last_nested_section_breakpoint_found >= 1 ) {
                $last_section_breakpoint_found = $last_nested_section_breakpoint_found;
            } else if ( $last_regular_section_breakpoint_found >= 1 ) {
                $last_section_breakpoint_found = $last_regular_section_breakpoint_found;
            } else {
                $last_section_breakpoint_found = 0;
            }

            $searched_level_id_found = true;
            break;
        }
        if ( !$searched_level_id_found && array_key_exists( 'collection', $level_data ) && is_array( $level_data['collection'] ) ) {
            $collection = $level_data['collection'];

            $recursive_params = compact(
                'searched_level_id',
                'collection',
                'skope_id',
                'last_section_breakpoint_found',
                'last_regular_section_breakpoint_found',
                'last_nested_section_breakpoint_found',
                'searched_level_id_found',
                'for_responsive_columns'
            );
            $recursive_values = sek_get_closest_section_custom_breakpoint( $recursive_params );

            if ( is_array($recursive_values) ) {
                extract( $recursive_values );
            } else {
                $last_section_breakpoint_found = $recursive_values;
                $searched_level_id_found = true;
                break;
            }
        }
    }

    // Returns a breakpoint int if found or an array
    // => this way we can determine if we continue or not to walk recursively
    return $searched_level_id_found ? $last_section_breakpoint_found : compact(
        'searched_level_id_found',
        'last_section_breakpoint_found',
        'last_regular_section_breakpoint_found',
        'last_nested_section_breakpoint_found'
    );
}

?>