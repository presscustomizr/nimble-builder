<?php
/* ------------------------------------------------------------------------- *
 *  SETUP DYNAMIC SERVER REGISTRATION FOR SETTING
/* ------------------------------------------------------------------------- */
// Schedule the loading the skoped settings class
add_action( 'customize_register', '\Nimble\load_nimble_setting_class' );
function load_nimble_setting_class() {
      require_once(  dirname( __FILE__ ) . '/customizer/seks_setting_class.php' );
}

add_filter( 'customize_dynamic_setting_args', '\Nimble\set_dyn_setting_args', 10, 2 );
function set_dyn_setting_args( $setting_args, $setting_id ) {
    // shall start with "sek__"
    if ( 0 === strpos( $setting_id, SEK_OPT_PREFIX_FOR_SEKTION_COLLECTION ) ) {
        //error_log( 'DYNAMICALLY REGISTERING SEK SETTING => ' . $setting_id );
        return array(
            'transport' => 'refresh',
            'type' => 'option',
            'default' => array()
        );
    } else if ( 0 === strpos( $setting_id, SEK_OPT_PREFIX_FOR_SEKTIONS_NOT_SAVED ) ) {
        //error_log( 'DYNAMICALLY REGISTERING SEK SETTING => ' . $setting_id );
        return array(
            'transport' => 'refresh',
            'type' => '_no_intended_to_be_saved_',
            'default' => array(),
            'sanitize_callback'    => '\Nimble\sek_sanitize_callback',
            'validate_callback'    => '\Nimble\sek_validate_callback'
        );
    }

    //error_log( print_r( $setting_args, true ) );
    return $setting_args;
    //return wp_parse_args( array( 'default' => array() ), $setting_args );
}

function sek_sanitize_callback( $sektion_data ) {
    //error_log( 'in_sek_sanitize_callback' );
    return $sektion_data;
}

function sek_validate_callback( $validity, $sektion_data ) {
    //error_log( 'in_sek_validate_callback' );
    return null;
    //return new WP_Error( 'required', __( 'Error in a sektion', 'text_domain_to_be_replaced' ), $sektion_data );
}


add_filter( 'customize_dynamic_setting_class', '\Nimble\set_dyn_setting_class', 10, 3 );
function set_dyn_setting_class( $class, $setting_id, $args ) {
  // shall start with 'sek___'
  if ( 0 !== strpos( $setting_id, SEK_OPT_PREFIX_FOR_SEKTION_COLLECTION ) )
    return $class;
  //error_log( 'REGISTERING CLASS DYNAMICALLY for setting =>' . $setting_id );
  return '\Nimble\Nimble_Customizer_Setting';
}
?>