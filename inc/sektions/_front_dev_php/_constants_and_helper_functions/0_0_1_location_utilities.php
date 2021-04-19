<?php
/* ------------------------------------------------------------------------- *
 *  LOCATIONS UTILITIES
/* ------------------------------------------------------------------------- */
// @return array
function sek_get_locations() {
    if ( !is_array( Nimble_Manager()->registered_locations ) ) {
        sek_error_log( __FUNCTION__ . ' error => the registered locations must be an array');
        return Nimble_Manager()->default_locations;
    }
    //sek_error_log( __FUNCTION__ .' => locations ?',  array_merge( Nimble_Manager()->default_locations, Nimble_Manager()->registered_locations ) );
    return apply_filters( 'sek_get_locations', Nimble_Manager()->registered_locations );
}

// @return array of "local" content locations => locations with the following characterictics :
// - sections in this location are specific to a given skope id
// - header and footer locations are excluded
function sek_get_local_content_locations() {
    $locations = array();
    $all_locations = sek_get_locations();
    if ( is_array( $all_locations ) ) {
        foreach ( $all_locations as $loc_id => $loc_data) {
            // Normalizes with the default model used to register a location
            // public $default_registered_location_model = [
            //   'priority' => 10,
            //   'is_global_location' => false,
            //   'is_header_location' => false,
            //   'is_footer_location' => false
            // ];
            $loc_data = wp_parse_args( $loc_data, Nimble_Manager()->default_registered_location_model );
            if ( true === $loc_data['is_header_location'] || true === $loc_data['is_footer_location'] )
              continue;

            if ( !sek_is_global_location( $loc_id ) ) {
                $locations[$loc_id] = $loc_data;
            }
        }
    }
    return $locations;
}

// DEPRECATED IN V1.4.0.
// Kept for retro compatibility
function sek_get_local_locations() {
    return sek_get_local_content_locations();
}

// @return an array of "global" locations => in which the sections are displayed site wide
function sek_get_global_locations() {
    $locations = array();
    $all_locations = sek_get_locations();
    if ( is_array( $all_locations ) ) {
        foreach ( $all_locations as $loc_id => $loc_data) {
            if ( sek_is_global_location( $loc_id ) ) {
                $locations[$loc_id] = $loc_data;
            }
        }
    }
    return $locations;
}


// @param location_id (string)
function sek_get_registered_location_property( $location_id, $property_name = '' ) {
    $all_locations = sek_get_locations();
    $default_property_val = 'not_set';
    //sek_error_log( __FUNCTION__ .' => locations ?',  $all_locations );
    if ( !isset( $all_locations[$location_id] ) || !is_array( $all_locations[$location_id] ) ) {
        sek_error_log( __FUNCTION__ . ' error => the location ' . $location_id . ' is invalid or not registered.');
        return $default_property_val;
    }

    if ( empty( $property_name ) || !is_string( $property_name ) ) {
        sek_error_log( __FUNCTION__ . ' error => the requested property for location ' . $location_id . ' is invalid');
        return $default_property_val;
    }

    $location_params = wp_parse_args( $all_locations[$location_id], Nimble_Manager()->default_registered_location_model );
    return array_key_exists($property_name, $location_params) ? $location_params[$property_name] : $default_property_val;
}

// @return bool
function sek_is_global_location( $location_id ) {
    if ( !is_string( $location_id ) || empty( $location_id ) ) {
        sek_error_log( __FUNCTION__ . ' error => missing or invalid location_id param' );
        return false;
    }
    $is_global_location = sek_get_registered_location_property( $location_id, 'is_global_location' );
    return 'not_set' === $is_global_location ? false : true === $is_global_location;
}

// @param $location_id ( string ). Example '__after_header'
function register_location( $location_id, $params = array() ) {
    $params = is_array( $params ) ? $params : array();
    $params = wp_parse_args( $params, Nimble_Manager()->default_registered_location_model );
    $registered_locations = Nimble_Manager()->registered_locations;
    if ( is_array( $registered_locations ) ) {
        $registered_locations[$location_id] = $params;
    }
    Nimble_Manager()->registered_locations = $registered_locations;
    //sek_error_log( __FUNCTION__ .' => Nimble_Manager()->registered_locations', Nimble_Manager()->registered_locations );
}


// @return array
// @used when populating the customizer localized params
// @param $skope_id optional. Specified when we need to differentiate the local and global locations
function sek_get_default_location_model( $skope_id = null ) {
    $is_global_skope = NIMBLE_GLOBAL_SKOPE_ID === $skope_id;
    if ( $is_global_skope ) {
        $defaut_sektions_value = [ 'collection' => [], 'fonts' => [] ];//global_options are saved in a specific option => NIMBLE_OPT_NAME_FOR_GLOBAL_OPTIONS
    } else {
        // March 2021 property '__inherits_group_skope_tmpl_when_exists__' added for site templates #478
        $defaut_sektions_value = [ 'collection' => [], 'local_options' => [], 'fonts' => [], '__inherits_group_skope_tmpl_when_exists__' => true ];
    }
    foreach( sek_get_locations() as $location_id => $params ) {
        $is_global_location = sek_is_global_location( $location_id );
        if ( $is_global_skope && !$is_global_location )
          continue;
        if ( !$is_global_skope && $is_global_location )
          continue;

        $location_model = wp_parse_args( [ 'id' => $location_id ], Nimble_Manager()->default_location_model );
        if ( $is_global_location ) {
            $location_model[ 'is_global_location' ] = true;
        }

        $defaut_sektions_value['collection'][] = $location_model;
    }
    return $defaut_sektions_value;
}

?>