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

?>