<?php
namespace Nimble;
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

/**
 *
 *
 * @see WP_Customize_Setting
 */
// Setting class for NB global options and Site Template options
// Note : the backslash before WP_Customize_Setting is here to indicate the global namespace
// otherwise "PHP Fatal error:  Class 'Nimble\WP_Customize_Setting'" not found will be triggered by the php engine
// This Setting class has been introduced in March 2021 for #799, to set option to autoload 'no' in wp_options
final class Nimble_Options_Setting extends \WP_Customize_Setting {
  public function __construct( $manager, $id, $args = array() ) {
    parent::__construct( $manager, $id, $args );
    // Make sure NB doesn't override the wrong setting
    if ( 0 !== strpos( $this->id_data['base'], NIMBLE_OPT_NAME_FOR_GLOBAL_OPTIONS ) ) {
        throw new \Exception( 'Nimble_Options_Customizer_Setting => __construct => invalid ID base' );
    }
  }

  // March 2021 => set autoload to "no" for #799
  public function update( $value ) {
    // Make sure cached objects are cleaned
    wp_cache_flush();

    // When a site template is modified, the following action allows NB to remove the skoped post + removes the corresponding CSS stylesheet
    // For example, when the page site template is changed, we need to remove the associated skoped post named 'nimble___skp__all_page'
    // This post has been inserted when running sek_maybe_get_seks_for_group_site_template(), fired from sek_get_skoped_seks()
    do_action('nb_on_save_customizer_global_options', $this->id_data['base'], $value );
    update_option( $this->id_data['base'], $value, 'no' );
  }
}



/**
 * This handles validation, sanitization and saving of the value.
 */

/**
 *
 *
 * @see WP_Customize_Setting
 */
// Setting class for NB sektion collections => shall start with 'nimble___'
// Note : the backslash before WP_Customize_Setting is here to indicate the global namespace
// otherwise "PHP Fatal error:  Class 'Nimble\WP_Customize_Setting'" not found will be triggered by the php engine
final class Nimble_Collection_Setting extends \WP_Customize_Setting {

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
  public $capability = 'customize';


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
        throw new \Exception( 'Nimble_Collection_Setting => __construct => Expected ' . NIMBLE_OPT_PREFIX_FOR_SEKTION_COLLECTION . ' id_base.' );
    }


    //there can be only one skope_id key instantiated at a time
    if ( 1 !== count( $this->id_data['keys'] ) || empty( $this->id_data['keys'][0] ) ) {
        throw new \Exception( 'Nimble_Collection_Setting => __construct => Expected single option key.' );
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
   * Note that the $_POST['customized'] param is set by core WP customizer-preview.js with $.ajaxPrefilter
   * This is how NB can access the customized dirty values while ajaxing during customization
   *
   * This is used in the preview when `sek_get_skoped_seks()` is called for rendering the styles.
   *
   * @see sek_get_skoped_seks()
   *
   * @param array $seks_collection        Original
   * @param string $location Current location.
   * @return array of skope settings
   */
  public function filter_previewed_sek_get_skoped_seks( $seks_collection, $skope_id, $location = '' ) {
    if ( $skope_id === $this->skope_id ) {
        $customized_value = $this->post_value( null );
        if ( !is_null( $customized_value ) ) {
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

    //error_log('id_base in Nimble_Collection_Setting class => ' . $this->id_data['base'] );

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
      // Make sure cached objects are cleaned
      wp_cache_flush();

      if ( !is_array( $seks_collection ) ) {
          $seks_collection = array();
      }


      if ( empty( $this->skope_id ) || !is_string( $this->skope_id ) ) {
          throw new \Exception( 'Nimble_Collection_Setting => update => invalid skope id' );
      }

      // Added march 2021 for #478
      // Property __inherits_group_skope_tmpl_when_exists__ is set to "true" on a local reset case

      // How does it work ?
      // When a page has not been locally customized, property __inherits_group_skope_tmpl_when_exists__ is true ( @see sek_get_default_location_model() )
      // As soon as the main local setting id is modified, __inherits_group_skope_tmpl_when_exists__ is set to false ( see js control::updateAPISetting )
      // After a reset case, NB sets __inherits_group_skope_tmpl_when_exists__ back to true ( see js control::resetCollectionSetting )
      // Note : If this property is set to true => NB removes the local skope post in Nimble_Collection_Setting::update()
      if ( NIMBLE_GLOBAL_SKOPE_ID !== $this->skope_id ) {
        if ( array_key_exists( '__inherits_group_skope_tmpl_when_exists__', $seks_collection ) && $seks_collection['__inherits_group_skope_tmpl_when_exists__'] ) {
          sek_remove_seks_post( $this->skope_id  );
          //sek_error_log( __CLASS__. ' => NOT SAVING LOCAL SETTING BECAUSE INHERITED FROM GROUP SKOPE + REMOVED SKOPED POST');
          return;
        }
      }


      $r = sek_update_sek_post( $seks_collection, array(
          'skope_id' => $this->skope_id
      ));
      //sek_error_log( __CLASS__. ' $_POST' , $_POST );
      // sek_error_log( __CLASS__. ' in update => ' . $this->skope_id );
      // sek_error_log( __CLASS__. ' AFTER $seks_collection' , $seks_collection );
      // Write the local stylesheet
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

      // Cache post ID in option for performance to avoid additional DB query.
      // $seks_options = get_option( $option_name );
      // $seks_options = is_array( $seks_options ) ? $seks_options : array();

      // //$seks_options[ $this->skope_id ] = $post_id;//$r is the post ID
      // $seks_options = (int)$post_id;//$r is the post ID

      sek_set_nb_post_id_in_index( $this->skope_id, (int)$post_id );
      //sek_error_log( __CLASS__ . '::' . __FUNCTION__ . ' => $seks_options', (int)$post_id);

      return $post_id;
  }
}

?>