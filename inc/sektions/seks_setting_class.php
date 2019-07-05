<?php
namespace Nimble;
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
/**
 * This handles validation, sanitization and saving of the value.
 */

/**
 *
 *
 * @see WP_Customize_Setting
 */
// Note : the backslash before WP_Customize_Setting is here to indicate the global namespace
// otherwise "PHP Fatal error:  Class 'Nimble\WP_Customize_Setting'" not found will be triggered by the php engine
final class Nimble_Customizer_Setting extends \WP_Customize_Setting {

  /**
   * The setting type.
   * @var string
   */
  public $type = 'seks_collection';
  public $transport = 'refresh';

  /**
   * Capability required to edit this setting.
   * @var string
   */
  public $capability = 'edit_theme_options';


  public $skope_id = '';
  /**
   * constructor.
   *
   * @throws Exception If the setting ID does not match the pattern NIMBLE_OPT_PREFIX_FOR_SEKTION_COLLECTION.
   *
   * @param WP_Customize_Manager $manager The Customize Manager class.
   * @param string               $id      An specific ID of the setting. Can be a
   *                                      theme mod or option name.
   * @param array                $args    Setting arguments.
   */
  public function __construct( $manager, $id, $args = array() ) {
    parent::__construct( $manager, $id, $args );
    // shall start with "nimble___"
    if ( 0 !== strpos( $this->id_data['base'], NIMBLE_OPT_PREFIX_FOR_SEKTION_COLLECTION ) ) { //$this->id_data['base'] looks like "nimble___"
        throw new Exception( 'Nimble_Customizer_Setting => __construct => Expected ' . NIMBLE_OPT_PREFIX_FOR_SEKTION_COLLECTION . ' id_base.' );
    }


    //there can be only one skope_id key instantiated at a time
    if ( 1 !== count( $this->id_data['keys'] ) || empty( $this->id_data['keys'][0] ) ) {
        throw new Exception( 'Nimble_Customizer_Setting => __construct => Expected single option key.' );
    }
    $this->skope_id = $this->id_data['keys'][0];
  }

  /**
   * Add filter to preview post value.
   *
   * @return bool False when preview short-circuits due no change needing to be previewed.
   */
  public function preview() {
      if ( $this->is_previewed ) {
        return false;
      }
      $this->is_previewed = true;
      add_filter( 'sek_get_skoped_seks', array( $this, 'filter_previewed_sek_get_skoped_seks' ), 9, 3 );
      return true;
  }

  /**
   * Filter `sek_get_skoped_seks` for applying the customized value.
   *
   * This is used in the preview when `sek_get_skoped_seks()` is called for rendering the styles.
   *
   * @see sek_get_skoped_seks()
   *
   * @param array $seks_collection        Original
   * @param string $location Current location.
   * @return array of skope settings
   */
  public function filter_previewed_sek_get_skoped_seks( $seks_collection, $skope_id, $location ) {
    if ( $skope_id === $this->skope_id ) {
        $customized_value = $this->post_value( null );
        if ( ! is_null( $customized_value ) ) {
          $seks_collection = $customized_value;
        }
    }
    return $seks_collection;
  }

  /**
   * Fetch the value of the setting. Will return the previewed value when `preview()` is called.
   *
   * @since 4.7.0
   * @see WP_Customize_Setting::value()
   *
   * @return string
   */
  public function value() {
    if ( $this->is_previewed ) {
      $post_value = $this->post_value( null );
      if ( null !== $post_value ) {
        return $post_value;
      }
    }
    $id_base = $this->id_data['base'];

    //error_log('id_base in Nimble_Customizer_Setting class => ' . $this->id_data['base'] );

    $value = '';
    $post = sek_get_seks_post( $this->skope_id );
    if ( $post ) {
      $value = $post->post_content;
    }
    if ( empty( $value ) ) {
      $value = $this->default;
    }

    /** This filter is documented in wp-includes/class-wp-customize-setting.php */
    $value = apply_filters( "customize_value_{$id_base}", $value, $this );

    return $value;
  }


  /**
   * Store the $seks_collection value in the skp-post-type custom post type for the stylesheet.
   *
   * @since 4.7.0
   *
   * @param array $seks_collection The input value.
   * @return int|false The post ID or false if the value could not be saved.
   */
  public function update( $seks_collection ) {
      if ( ! is_array( $seks_collection ) ) {
          $seks_collection = array();
      }
      // sek_error_log( __CLASS__. ' in update => ' . $this->skope_id );
      // sek_error_log( __CLASS__. ' $seks_collection' , $seks_collection );

      if ( empty( $this->skope_id ) || ! is_string( $this->skope_id ) ) {
          throw new Exception( 'Nimble_Customizer_Setting => update => invalid skope id' );
      }

      $r = sek_update_sek_post( $seks_collection, array(
          'skope_id' => $this->skope_id
      ) );

      if ( NIMBLE_GLOBAL_SKOPE_ID !== $this->skope_id ) {
          // Try to write the CSS
          new Sek_Dyn_CSS_Handler( array(
              'id'             => $this->skope_id,
              'skope_id'       => $this->skope_id,
              'mode'           => Sek_Dyn_CSS_Handler::MODE_FILE,
              'customizer_save' => true,//<= indicating that we are in a customizer_save scenario will tell the dyn css class to only write the css file + save the google fonts, not schedule the enqueuing
              'force_rewrite'  => true //<- write even if the file exists
          ) );
      }

      // sek_error_log( __CLASS__ . '::' . __FUNCTION__ . ' => $seks_collection', $seks_collection);

      if ( $r instanceof WP_Error ) {
          return false;
      }
      $post_id = $r->ID;

      $option_name = NIMBLE_OPT_PREFIX_FOR_SEKTION_COLLECTION . $this->skope_id;

      // Cache post ID in option for performance to avoid additional DB query.
      // $seks_options = get_option( $option_name );
      // $seks_options = is_array( $seks_options ) ? $seks_options : array();

      // //$seks_options[ $this->skope_id ] = $post_id;//$r is the post ID
      // $seks_options = (int)$post_id;//$r is the post ID

      update_option( $option_name, (int)$post_id );
      // sek_error_log( __CLASS__ . '::' . __FUNCTION__ . ' => $seks_options', (int)$post_id);

      return $post_id;
  }
}

?>