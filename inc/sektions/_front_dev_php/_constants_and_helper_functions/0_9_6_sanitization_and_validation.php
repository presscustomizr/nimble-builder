<?php
/* ------------------------------------------------------------------------- *
 *  SANIIZATION AND VALIDATION HELPERS
 *  used before saving NB main settings in DB
 *  used before saving user template in DB
 *  added March 2021 for https://github.com/presscustomizr/nimble-builder/issues/792, after fixing formatting issues : #544 #791
/* ------------------------------------------------------------------------- */
// Uses the sanitize_callback function specified on module registration if any
// Recursively loop on the local or global main NB collection and fire the sanitize callback
// the $setting_instance param is passed when sanitizing the customizer settings. Not used when sanitizing a user template
function sek_sektion_collection_sanitize_cb( $setting_data, $setting_instance = null ) {
    if ( !is_array( $setting_data ) ) {
        return $setting_data;
    } else {
        if ( !is_array( $setting_data ) ) {
            return $setting_data;
        } else {
            if ( array_key_exists('module_type', $setting_data ) ) {
                $san_callback = sek_get_registered_module_type_property( $setting_data['module_type'], 'sanitize_callback' );
                if ( !empty( $san_callback ) && is_string( $san_callback ) && function_exists( $san_callback ) && array_key_exists('value', $setting_data ) ) {
                    //sek_error_log('SANITIZE ??', $san_callback );
                    $setting_data['value'] = $san_callback( $setting_data['value'] );
                }
            } else {
                foreach( $setting_data as $k => $data ) {
                    $setting_data[$k] = sek_sektion_collection_sanitize_cb( $data, $setting_instance );
                }
            }
        }
    }
    //return new \WP_Error( 'required', __( 'Error in a sektion', 'text_doma' ), $setting_data );
    return $setting_data;
}

// Uses the validate_callback function specified on module registration if any
// @return validity object
function sek_sektion_collection_validate_cb( $validity, $setting_data, $setting_instance = null ) {
    $validated = true;
    if ( !is_array( $setting_data ) ) {
        return $setting_data;
    } else {
        if ( !is_array( $setting_data ) ) {
            return $setting_data;
        } else {
            if ( array_key_exists('module_type', $setting_data ) ) {
                $validation_callback = sek_get_registered_module_type_property( $setting_data['module_type'], 'validate_callback' );
                if ( !empty( $validation_callback ) && is_string( $validation_callback ) && function_exists( $validation_callback ) && array_key_exists('value', $setting_data ) ) {
                    $validated = $validation_callback( $setting_data );
                }
            } else {
                foreach( $setting_data as $k => $data ) {
                    $validated = sek_sektion_collection_validate_cb($validity, $data, $setting_instance);
                }
            }
        }
    }

    //return new \WP_Error( 'required', __( 'Error in a sektion', 'text_doma' ), $setting_data );
    if ( true !== $validated ) {
        if ( is_wp_error( $validated ) ) {
            $validation_msg = $validation_msg->get_error_message();
            $validity->add(
                is_null( $setting_instance ) ? 'nimble_validation_error' : 'nimble_validation_error_in_' . $setting_instance->id,
                $validation_msg
            );
        }

    }
    return $validity;
}




// @return bool
function sek_is_json( $string ){
    if ( !is_string( $string ) )
      return false;
    json_decode($string);
    return (json_last_error() == JSON_ERROR_NONE);
}
  
// @return string
function sek_maybe_decode_richtext( $string ){
    if ( !is_string($string) )
    return $string;

    $json_decoded_candidate = json_decode($string, true);
    if ( json_last_error() == JSON_ERROR_NONE ) {
        // https://stackoverflow.com/questions/6465263/how-to-reverse-htmlentities
        // added to fix regression https://github.com/presscustomizr/nimble-builder/issues/791
        $json_decoded_candidate = html_entity_decode($json_decoded_candidate, ENT_QUOTES, get_bloginfo( 'charset' ) );
        //sek_error_log('DECODED DECODED ?', $json_decoded_candidate );
        return $json_decoded_candidate;
    }
    
    return $string;
}

// @return string
function sek_maybe_encode_richtext( $string ){
    if ( !is_string($string) )
    return $string;
    // only encode if not already encoded
    if ( !sek_is_json($string) ) {
        // https://stackoverflow.com/questions/6465263/how-to-reverse-htmlentities
        // added to fix regression https://github.com/presscustomizr/nimble-builder/issues/791
        $string = htmlentities($string, ENT_COMPAT, get_bloginfo( 'charset' ) );//reversed with html_entity_decode
        //$string = wp_encode_emoji( $string );
        $string = wp_json_encode($string);
        //sek_error_log('JSON ENCODED ?', $string );
    }
    return $string;
}




// Feb 2021 added to fix regression https://github.com/presscustomizr/nimble-builder/issues/791
// Recursive
function sek_sniff_and_decode_richtext( $seks_data ) {
    if ( is_array( $seks_data ) ) {
        foreach( $seks_data as $key => $data ) {
            if ( is_array( $data ) ) {
                $seks_data[$key] = sek_sniff_and_decode_richtext( $data );
            } else {
                if ( is_string($data) ) {
                    $seks_data[$key] = sek_maybe_decode_richtext( $data );
                }
            }
        }
    }
    return $seks_data;
  }

?>