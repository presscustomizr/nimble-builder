<?php
namespace Nimble;
if ( did_action('nimble_skope_loaded') ) {
    if ( ( defined( 'CZR_DEV' ) && CZR_DEV ) || ( defined( 'NIMBLE_DEV' ) && NIMBLE_DEV ) ) {
        error_log( __FILE__ . '  => The skope has already been loaded' );
    }
    return;
}

// Set the namsepace as a global so we can use it when fired from another theme/plugin using the fmk
global $czr_skope_namespace;
$czr_skope_namespace = __NAMESPACE__ . '\\';

do_action( 'nimble_skope_loaded' );

//Creates a new instance
function Flat_Skop_Base( $params = array() ) {
    return Flat_Skop_Base::skp_get_instance( $params );
}

/////////////////////////////////////////////////////////////////
// <DEFINITIONS>
// THE SKOPE MODEL
function skp_get_default_skope_model() {
    return array(
        'title'       => '',
        'long_title'  => '',
        'ctx_title'   => '',
        'id'          => '',
        'skope'       => '',
        //'level'       => '',
        'obj_id'      => '',
        'skope_id'     => '',
        'values'      => ''
    );
}


// those contexts have no group
function skp_get_no_group_skope_list() {
    return array( 'home', 'search', '404', 'date' );
}

/////////////////////////////////////////////////////////////////
// </DEFINITIONS>


/////////////////////////////////////////////////////////////////
// HELPERS

function skp_trim_text( $text, $text_length, $more ) {
    if ( !$text )
      return '';

    $text       = trim( strip_tags( $text ) );

    if ( !$text_length )
      return $text;

    $end_substr = $_text_length = strlen( $text );

    if ( $_text_length > $text_length ){
      $end_substr = strpos( $text, ' ' , $text_length);
      $end_substr = ( $end_substr !== FALSE ) ? $end_substr : $text_length;
      $text = substr( $text , 0 , $end_substr );
    }
    return ( ( $end_substr < $text_length ) && $more ) ? $text : $text . ' ' .$more ;
  }







/////////////////////////////////////////////////////////////////
// SKOPE HELPERS
/**
* Return the current skope
* Front / Back agnostic.
* @param $_requesting_wot is a string with the following possible values : 'meta_type' (like post) , 'type' (like page), 'id' (like page id)
* @param $_return_string string param stating if the return value should be a string or an array
* @param $requested_parts is an array of parts looking like
* Array
* (
*     [meta_type] => post
*     [type] => page
*     [obj_id] => 9
* )
* USE CASE when $requested_parts is passed : when a post gets deleted, we need to clean any skope posts associated. That's when we invoke skp_get_skope( null, true, $requested_parts )
* @return a string of all concatenated ctx parts (default) 0R an array of the ctx parts
*/
function skp_get_skope( $_requesting_wot = null, $_return_string = true, $requested_parts = array() ) {
    //skope builder from the wp $query
    //=> returns :
    // the meta_type : post, tax, user
    // the type : post_type, taxonomy name, author
    // the id : post id, term id, user id

    // if $parts are provided, use them.
    // Note that the value of skp_get_query_skope() is cached when get the first time for better performance
    $parts    = ( is_array( $requested_parts ) && !empty( $requested_parts ) ) ? $requested_parts : skp_get_query_skope();

    // error_log( '<SKOPE PARTS>' );
    // error_log( print_r( $parts, true ) );
    // error_log( '</SKOPE PARTS>' );

    $_return  = array();
    $meta_type = $type = $obj_id = false;

    if ( is_array( $parts ) && !empty( $parts ) ) {
        $meta_type  = isset( $parts['meta_type'] ) ? $parts['meta_type'] : false;
        $type       = isset( $parts['type'] ) ? $parts['type'] : false;
        $obj_id     = isset( $parts['obj_id'] ) ? $parts['obj_id'] : false;
    }

    switch ( $_requesting_wot ) {
        case 'meta_type':
            if ( false !== $meta_type ) {
                $_return = array( "meta_type" => "{$meta_type}" );
            }
        break;

        case 'type':
            if ( false !== $type ) {
                $_return = array( "type" => "{$type}" );
            }
        break;

        case 'id':
            if ( false !== $obj_id ) {
                $_return = array( "id" => "{$obj_id}" );
            }
        break;

        default:
            //LOCAL
            //here we don't check if there's a type this is the case where there must be one when a meta type (post, tax, user) is defined.
            //typically the skope will look like post_page_25
            if  ( false !== $meta_type && false !== $obj_id ) {
                $_return = array( "meta_type" => "{$meta_type}" , "type" => "{$type}", "id" => "{$obj_id}" );
            }
            //GROUP
            else if ( false !== $meta_type && !$obj_id ) {
                $_return = array( "meta_type" => "{$meta_type}", "type" => "{$type}" );
            }
            //LOCAL WITH NO GROUP : home ( when home displays "Your latests posts" ) , 404, search, date, post type archive
            else if ( false !== $obj_id ) {
                $_return = array( "id" => "{$obj_id}" );
            }
            else {
                // don't print the skope error log if not in dev mode
                // fixes : https://github.com/presscustomizr/czr-skope/issues/1
                if ( defined( 'NIMBLE_DEV' ) && NIMBLE_DEV ) {
                    // the favicon request break skope building, so skip this case
                    // see https://github.com/presscustomizr/nimble-builder/issues/658
                    if ( '/favicon.ico' !== $_SERVER['REQUEST_URI'] ) {
                        error_log( __FUNCTION__ . ' error when building the local skope, no object_id provided.');
                        error_log( print_r( $parts, true) );
                    }
                }
            }
        break;
    }

    //return the parts array if not a string requested
    if ( !$_return_string ) {
      return $_return;
    }

    //don't go further if not an array or empty
    if ( !is_array( $_return ) || ( is_array( $_return ) && empty( $_return ) ) ) {
      return '';
    }

    //if a specific part of the ctx is requested, don't concatenate
    //return the part if exists
    if ( !is_null( $_requesting_wot ) ) {
      return isset( $_return[ $_requesting_wot ] ) ? $_return[ $_requesting_wot ] : '';
    }

    //generate the ctx string from the array of ctx_parts
    $_concat = "";
    foreach ( $_return as $_key => $_part ) {
        if ( empty( $_concat) ) {
            $_concat .= $_part;
        } else {
            $_concat .= '_'. $_part;
        }
    }
    return $_concat;
}


/**
* skope builder from the wp $query
* !!has to be fired after 'template_redirect'
* Used on front ( not customizing preview ? => @todo make sure of this )
* @return  array of ctx parts
*/
function skp_get_query_skope() {
    //don't call get_queried_object if the $query is not defined yet
    global $wp_the_query;
    if ( !isset( $wp_the_query ) || empty( $wp_the_query ) ) {
      return array();
    }
    // is it cached already ?
    if ( !empty( Flat_Skop_Base()->query_skope ) ) {
      return Flat_Skop_Base()->query_skope;
    }

    $queried_object  = get_queried_object();

    // error_log( '<GET QUERIED OBJECT>' . gettype( $queried_object ) );
    // error_log( print_r( $wp_the_query , true ) );
    // error_log( '</GET QUERIED OBJECT>' );

    $meta_type = $type = $obj_id = false;

    // The queried object is NULL on
    // - home when displaying the latest posts
    // - date archives
    // - 404 page
    // - search page
    if ( !is_null( $queried_object ) && is_object( $queried_object ) ) {
        //post, custom post types, page
        if ( isset($queried_object->post_type) ) {
            $meta_type  = 'post';
            $type       = $queried_object->post_type;
            $obj_id     = $queried_object->ID;
        }

        //taxinomies : tags, categories, custom tax type
        if ( isset($queried_object->taxonomy) && isset($queried_object->term_id) ) {
            $meta_type  = 'tax';
            $type       = $queried_object->taxonomy;
            $obj_id     = $queried_object->term_id;
        }
    }

    //author archive page
    if ( is_author() ) {
        $meta_type  = 'user';
        $type       = 'author';
        $obj_id     = $wp_the_query->get( 'author' );
    }

    //SKOPES WITH NO GROUPS
    //post type archive object
    if ( is_post_type_archive() ) {
        $obj_id     = 'post_type_archive' . '_'. $wp_the_query->get( 'post_type' );
    }
    if ( is_404() ) {
        $obj_id  = '404';
    }
    if ( is_search() ) {
        $obj_id  = 'search';
    }
    if ( is_date() ) {
        $obj_id  = 'date';
    }

    if ( skp_is_real_home() ) {
        $obj_id  = 'home';
        // December 2018
        // when the home page is a page, the skope now includes the page id, instead of being generic as it was since then : skp__post_page_home
        // This has been introduced to facilitate the compatibility of Nimble Builder with multilanguage plugins like polylang
        // => Allows user to create a different home page for each languages
        //
        // To summarize,
        // Before dec 2018 :
        // - home page is blog page => skope_id = skp___home
        // - home page is page => skope_id = skp__post_page_home
        //
        // After Dec 2018
        // - hope page is blog page => skope_id is unchanged = skp__home
        // - home page is a page => skope_id is changed to = skp__post_page_{$static_home_page_id}
        //
        // This means that if Nimble sections, or any other contextualizations had been made on home when 'page' === get_option( 'show_on_front' ),
        // for which the corresponding skope_id was skp__post_page_home,
        // those settings have to be copied in the skp__post_page_{$static_home_page_id} skope settings

        // If we are on the real home page, but displaying a static page then set the static page id as obj_id
        if ( !is_home() && 'page' === get_option( 'show_on_front' ) ) {
            $home_page_id = get_option( 'page_on_front' );
            if ( 0 < $home_page_id ) {
                $obj_id = $home_page_id;
            }
        }
    }

    // cache now
    if ( did_action( 'wp' ) ) {
        Flat_Skop_Base()->query_skope = apply_filters( 'skp_get_query_skope' , array( 'meta_type' => $meta_type , 'type' => $type , 'obj_id' => $obj_id ), $queried_object );
    }

    return Flat_Skop_Base()->query_skope;
}


//@return the skope prefix used both when customizing and on front
function skp_get_skope_id( $level = 'local' ) {
    // CACHE THE SKOPE WHEN 'wp' DONE
    // the skope id is used when filtering the options, called hundreds of times.
    // We get higher performances with a cached value instead of using the skp_get_skope_id() function on each call.
    $new_skope_ids = array( 'local' => '_skope_not_set_', 'group' => '_skope_not_set_' );
    if ( did_action( 'wp' ) ) {
        if ( empty( Flat_Skop_Base()->current_skope_ids ) ) {
            $new_skope_ids['local'] = skp_build_skope_id( array( 'skope_string' => skp_get_skope(), 'skope_level' => 'local' ) );
            $new_skope_ids['group'] = skp_build_skope_id( array( 'skope_level' => 'group' ) );

            Flat_Skop_Base()->current_skope_ids = $new_skope_ids;

            $skope_id_to_return = $new_skope_ids[ $level ];
            // error_log('<SKOPE ID cached in skp_get_skope_id>');
            // error_log( print_r( $new_skope_ids, true ) );
            // error_log('</SKOPE ID cached in skp_get_skope_id>');
        } else {
            $new_skope_ids = Flat_Skop_Base()->current_skope_ids;
            $skope_id_to_return = $new_skope_ids[ $level ];
        }
    } else {
        $skope_id_to_return = array_key_exists( $level, $new_skope_ids ) ? $new_skope_ids[ $level ] : '_skope_not_set_';
    }
    // if ( !(bool)did_action( 'wp' ) ) {
    //     sek_error_log('ACTION WP NOT FIRED', did_action('wp'));
    // }
    // Jan 2021, while working on https://github.com/presscustomizr/nimble-builder-pro/issues/81
    // when customizing and firing this function during ajax calls, the check for did_action('wp') will return 0.
    // => which will lead to skope_id set to '_skope_not_set_'
    // in order to prevent this, let's get the skope_id value from the customizer posted value when available.
    if ( skp_is_customizing() && '_skope_not_set_' === $skope_id_to_return && 'local' === $level && !empty($_POST['local_skope_id']) ) {
        $skope_id_to_return = sanitize_text_field($_POST['local_skope_id']);
    }
    // Feb 2021 => added for https://github.com/presscustomizr/nimble-builder/issues/478
    if ( skp_is_customizing() && '_skope_not_set_' === $skope_id_to_return && 'group' === $level && !empty($_POST['group_skope_id']) ) {
        $skope_id_to_return = sanitize_text_field($_POST['group_skope_id']);
    }

    $skope_id_to_return = apply_filters( 'skp_get_skope_id', $skope_id_to_return, $level );

    // At this point, the skope_id should be set
    if ( '_skope_not_set_' === $skope_id_to_return ) {
        //error_log( __FUNCTION__ . ' error => skope_id not set for level ' . $level );
    }
    // error_log('$skope_id_to_return => ' . $level . ' ' . $skope_id_to_return );
    // error_log( print_r( Flat_Skop_Base()->current_skope_ids , true ) );
    return $skope_id_to_return;
}

//@param args = array(
//  'skope_string' => skp_get_skope(),
//  'skope_type' => $skp_type,
//  'skope_level' => 'local'
//)
//@return string
function skp_build_skope_id( $args = array() ) {
    $skope_id = '_skope_not_set_';

    // normalizes
    $args = is_array( $args ) ? $args : array();
    $args = wp_parse_args(
        $args,
        array( 'skope_string' => '', 'skope_type' => '', 'skope_level' => '' )
    );

    // set params if not provided
    $args['skope_level']  = empty( $args['skope_level'] ) ? 'local' : $args['skope_level'];
    $args['skope_string'] = ( 'local' == $args['skope_level'] && empty( $args['skope_string'] ) ) ? skp_get_skope() : $args['skope_string'];
    $args['skope_type']   = ( 'group' == $args['skope_level'] && empty( $args['skope_type'] ) ) ? skp_get_skope( 'type' ) : $args['skope_type'];

    // generate skope_id for two cases : local or group
    switch( $args[ 'skope_level'] ) {
          case 'local' :
              $skope_id = strtolower( NIMBLE_SKOPE_ID_PREFIX . $args[ 'skope_string' ] );
          break;
          case 'group' :
              if ( !empty( $args[ 'skope_type' ] ) ) {
                  $skope_id = strtolower( NIMBLE_SKOPE_ID_PREFIX . 'all_' . $args[ 'skope_type' ] );
              }
          break;
    }
    return $skope_id;
}


/**
* Used when localizing the customizer js params
* Can be a post ( post, pages, CPT) , tax(tag, cats, custom taxs), author, date, search page, 404.
* @param $args : array(
*    'level'       => string,
*    'meta_type'   => string
*    'long'        => bool
*    'is_prefixed' => bool //<= indicated if we should add the "Options for" prefix
* )
* @return string title of the current ctx if exists. If not => false.
*/
function skp_get_skope_title( $args = array() ) {
    $defaults = array(
        'level'       =>  '',
        'meta_type'   => null,
        'long'        => false,
        'is_prefixed' => true
    );

    $args = wp_parse_args( $args, $defaults );

    $level        = $args['level'];
    $meta_type    = $args['meta_type'];
    $long         = $args['long'];
    $is_prefixed  = $args['is_prefixed'];

    $_dyn_type = ( skp_is_customize_preview_frame() && isset( $_POST['dyn_type']) ) ? sanitize_text_field($_POST['dyn_type']) : '';
    $type = skp_get_skope('type');
    $skope = skp_get_skope();
    $title = '';

    if( 'local' == $level ) {
        $type = skp_get_skope( 'type' );
        $title = $is_prefixed ? __( 'Options for', 'text_doma') . ' ' : $title;
        if ( skp_skope_has_a_group( $meta_type ) ) {
            $_id = skp_get_skope('id');
            switch ($meta_type) {
                case 'post':
                  $type_obj = get_post_type_object( $type );
                  $title .= sprintf( '%1$s "%3$s" (id : %2$s)', strtolower( $type_obj->labels->singular_name ), $_id, get_the_title( $_id ) );
                  break;

                case 'tax':
                  $type_obj = get_taxonomy( $type );
                  $term = get_term( $_id, $type );
                  $title .= sprintf( '%1$s "%3$s" (id : %2$s)', strtolower( $type_obj->labels->singular_name ), $_id, $term->name );
                  break;

                case 'user':
                  $author = get_userdata( $_id );
                  $title .= sprintf( '%1$s "%3$s" (id : %2$s)', __('user', 'text_doma'), $_id, $author->user_login );
                  break;
            }
        } else if ( ( 'trans' == $_dyn_type || skp_skope_has_no_group( $skope ) ) ) {
            if ( is_post_type_archive() ) {
                global $wp_the_query;
                $title .= sprintf( __( '%1$s archive page', 'text_doma'), $wp_the_query->get( 'post_type' ) );
            } else {
                $title .= strtolower( $skope );
            }
        } else {
            $title .= __( 'Undefined', 'text_doma');
        }
    }
    if ( 'group' == $level || 'special_group' == $level ) {
        $title = $is_prefixed ? __( 'Options for all', 'text_doma') . ' ' : __( 'All' , 'text_doma' ) . ' ';
        switch( $meta_type ) {
            case 'post' :
                $type_obj = get_post_type_object( $type );
                $title .= strtolower( $type_obj->labels->name );
            break;

            case 'tax' :
                $type_obj = get_taxonomy( $type );
                $title .= strtolower( $type_obj->labels->name );
            break;

            case 'user' :
                $title .= __('users', 'text_doma');
            break;
        }
    }
    if ( 'global' == $level ) {
        $title = __( 'Sitewide options', 'text_doma');
    }
    $title = ucfirst( $title );
    return skp_trim_text( $title, $long ? 45 : 28, '...');
}

//@return bool
//=> tells if the current skope is part of the ones without group
function skp_skope_has_no_group( $meta_type ) {
    return in_array(
      $meta_type,
      skp_get_no_group_skope_list()
    ) || is_post_type_archive();
}

//@return bool
//Tells if the current skope has a group level
function skp_skope_has_a_group( $meta_type ) {
    return in_array(
      $meta_type,
      array('post', 'tax', 'user')
    );
}



//@return bool
function skp_is_real_home() {
  // Warning : when show_on_front is a page, but no page_on_front has been picked yet, is_home() is true
  // beware of https://github.com/presscustomizr/nimble-builder/issues/349
  return ( is_home() && ( 'posts' == get_option( 'show_on_front' ) || '__nothing__' == get_option( 'show_on_front' ) ) )
  || ( is_home() && 0 == get_option( 'page_on_front' ) && 'page' == get_option( 'show_on_front' ) )//<= this is the case when the user want to display a page on home but did not pick a page yet
  || is_front_page();
}


/**
 * Returns a boolean
*/
function skp_is_customizing() {
    //checks if is customizing : two contexts, admin and front (preview frame)
    global $pagenow;
    $_is_ajaxing_from_customizer = isset( $_POST['customized'] ) || isset( $_POST['wp_customize'] );

    $is_customizing = false;
    // the check on $pagenow does NOT work on multisite install @see https://github.com/presscustomizr/nimble-builder/issues/240
    // That's why we also check with other global vars
    // @see wp-includes/theme.php, _wp_customize_include()
    $is_customize_php_page = ( is_admin() && 'customize.php' == basename( $_SERVER['PHP_SELF'] ) );
    $is_customize_admin_page_one = (
      $is_customize_php_page
      ||
      ( isset( $_REQUEST['wp_customize'] ) && 'on' == sanitize_text_field($_REQUEST['wp_customize']) )
      ||
      ( !empty( $_GET['customize_changeset_uuid'] ) || !empty( $_POST['customize_changeset_uuid'] ) )
    );
    $is_customize_admin_page_two = is_admin() && isset( $pagenow ) && 'customize.php' == $pagenow;

    if ( $is_customize_admin_page_one || $is_customize_admin_page_two ) {
        $is_customizing = true;
    //hu_is_customize_preview_frame() ?
    // Note : is_customize_preview() is not able to differentiate when previewing in the customizer and when previewing a changeset draft.
    // @todo => change this
    } else if ( is_customize_preview() || ( !is_admin() && isset($_REQUEST['customize_messenger_channel']) ) ) {
        $is_customizing = true;
    // hu_doing_customizer_ajax()
    } else if ( $_is_ajaxing_from_customizer && ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
        $is_customizing = true;
    }
    return $is_customizing;
}

/**
* Is the customizer preview panel being displayed ?
* @return  boolean
*/
function skp_is_customize_preview_frame() {
  return is_customize_preview() || ( !is_admin() && isset($_REQUEST['customize_messenger_channel']) );
}

/**
* @return  boolean
*/
function skp_is_previewing_live_changeset() {
  return !isset( $_POST['customize_messenger_channel']) && is_customize_preview();
}
?><?php

////////////////////////////////////////////////////////////////
// FLAT SKOPE BASE
if ( !class_exists( 'Flat_Skop_Base' ) ) :
    class Flat_Skop_Base {
        static $instance;
        public $query_skope = array();//<= will cache the query skope ( otherwise called multiple times ) on the first invokation of skp_get_query_skope() IF 'wp' done
        public $current_skope_ids = array();// will cache the skope ids on the first invokation of skp_get_skope_id, if 'wp' done

        public static function skp_get_instance( $params ) {
            if ( !isset( self::$instance ) && !( self::$instance instanceof Flat_Skop_Base ) )
              self::$instance = new Flat_Skope_Clean_Final( $params );
            return self::$instance;
        }
        function __construct( $params = array() ) {
            $defaults = array(
                'base_url_path' => ''//NIMBLE_BASE_URL . '/inc/czr-skope/'
            );
            $params = wp_parse_args( $params, $defaults );
            if ( !defined( 'NIMBLE_SKOPE_BASE_URL' ) ) { define( 'NIMBLE_SKOPE_BASE_URL' , $params['base_url_path'] ); }
            if ( !defined( 'NIMBLE_SKOPE_ID_PREFIX' ) ) { define( 'NIMBLE_SKOPE_ID_PREFIX' , "skp__" ); }

            $this->skp_register_and_load_control_assets();
            $this->skp_export_skope_data_and_schedule_sending_to_panel();
            $this->skp_schedule_cleaning_on_object_delete();
        }//__construct
    }
endif;
?><?php
/////////////////////////////////////////////////////////////////
// PRINT CUSTOMIZER JAVASCRIPT + LOCALIZED DATA
if ( !class_exists( 'Flat_Skop_Register_And_Load_Control_Assets' ) ) :
    class Flat_Skop_Register_And_Load_Control_Assets extends Flat_Skop_Base {
          // Fired in Flat_Skop_Base::__construct()
          public function skp_register_and_load_control_assets() {
              add_action( 'customize_controls_enqueue_scripts', array( $this, 'skp_enqueue_controls_js_css' ), 20 );
          }

          public function skp_enqueue_controls_js_css() {
              $_use_unminified = defined('CZR_DEV')
                  && true === CZR_DEV
                  // && false === strpos( dirname( dirname( dirname (__FILE__) ) ) , 'inc/wfc' )
                  && file_exists( sprintf( '%s/assets/czr/js/czr-skope-base.js' , dirname( __FILE__ ) ) );

              $_prod_script_path = sprintf(
                  '%1$s/assets/czr/js/%2$s' ,
                  NIMBLE_SKOPE_BASE_URL,
                  $_use_unminified ? 'czr-skope-base.js' : 'czr-skope-base.min.js'
              );

              wp_enqueue_script(
                  'czr-skope-base',
                  //dev / debug mode mode?
                  $_prod_script_path,
                  array('customize-controls' , 'jquery', 'underscore'),
                  ( defined('WP_DEBUG') && true === WP_DEBUG ) ? time() :  wp_get_theme()->version,
                  $in_footer = true
              );

              wp_localize_script(
                  'czr-skope-base',
                  'FlatSkopeLocalizedData',
                  array(
                      'noGroupSkopeList' => skp_get_no_group_skope_list(),
                      'defaultSkopeModel' => skp_get_default_skope_model(),
                      'i18n' => array()
                  )
              );
          }
    }//class
endif;

?><?php
/* ------------------------------------------------------------------------- *
*  CUSTOMIZE PREVIEW : export skope data and send skope to the panel
/* ------------------------------------------------------------------------- */
if ( !class_exists( 'Flat_Export_Skope_Data_And_Send_To_Panel' ) ) :
    class Flat_Export_Skope_Data_And_Send_To_Panel extends Flat_Skop_Register_And_Load_Control_Assets {
          // Fired in Flat_Skop_Base::__construct()
          public function skp_export_skope_data_and_schedule_sending_to_panel() {
              add_action( 'wp_head', array( $this, 'skp_print_server_skope_data') , 30 );
          }


          //hook : 'wp_footer'
          public function skp_print_server_skope_data() {
              if ( !skp_is_customize_preview_frame() )
                  return;

              global $wp_query, $wp_customize;
              $_meta_type = skp_get_skope( 'meta_type', true );

              // $_czr_scopes = array( );
              $_czr_skopes            = $this->_skp_get_json_export_ready_skopes();
              $_czr_query_data        = $this->_skp_get_json_export_ready_query_data();
            
              ob_start();
              ?>
                    var _doSend = function() {
                        // December 2020 : it may happen that the 'sync' event was already sent and that we missed it
                      // Typically when the site is slow.
                      // So we need to check if the "sync" event has fired already ( see customize-base.js, ::bind method )
                      // For more security, let's introduce a marker and attempt to re-sent after a moment if needed
                      window.czr_skopes_sent = false;
                      var _send = function() {
                            wp.customize.preview.send( 'czr-new-skopes-synced', {
                                czr_new_skopes : _wpCustomizeSettings.czr_new_skopes || [],
                                czr_stylesheet : _wpCustomizeSettings.czr_stylesheet || '',
                                isChangesetDirty : _wpCustomizeSettings.isChangesetDirty || false,
                                skopeGlobalDBOpt : _wpCustomizeSettings.skopeGlobalDBOpt || [],
                            } );
                            window.czr_skopes_sent = true;
                      };

                        jQuery( function() {
                            if ( wp.customize.preview.topics && wp.customize.preview.topics.sync && wp.customize.preview.topics.sync.fired() ) {
                                _send();
                            } else {
                                wp.customize.preview.bind( 'sync', function( events ) {
                                    _send();
                                });
                            }
                            setTimeout( function() {
                                    if ( !window.czr_skopes_sent ) {
                                        _send();
                                    }
                            }, 2500 );
                        });
                    };
                    
                    
                    // recursively try to load jquery every 200ms during 6s ( max 30 times )
                    var _doWhenCustomizeSettingsReady = function( attempts ) {
                        attempts = attempts || 0;
                        if ( typeof undefined !== typeof window._wpCustomizeSettings ) {
                            _wpCustomizeSettings.czr_new_skopes        = <?php echo wp_json_encode( $_czr_skopes ); ?>;
                            _wpCustomizeSettings.czr_stylesheet    = '<?php echo get_stylesheet(); ?>';
                            _wpCustomizeSettings.czr_query_params  = <?php echo wp_json_encode($_czr_query_data); ?>;
                            _doSend();
                        } else if ( attempts < 30 ) {
                            setTimeout( function() {
                                attempts++;
                                _doWhenCustomizeSettingsReady( attempts );
                            }, 20 );
                        } else {
                            if ( window.console && window.console.log ) {
                                console.log('Nimble Builder problem : _wpCustomizeSettings is not defined');
                            }
                        }
                    };

                    _doWhenCustomizeSettingsReady();
              <?php
              $script = ob_get_clean();
              wp_register_script( 'nb_print_skope_data_js', '');
              wp_enqueue_script( 'nb_print_skope_data_js' );
              wp_add_inline_script( 'nb_print_skope_data_js', $script );
          }


          // introduced in october 2019 for https://github.com/presscustomizr/nimble-builder/issues/401
          private function _skp_get_json_export_ready_query_data() {
              global $wp_query;
              global $authordata;
              add_filter('get_the_archive_title_prefix', '__return_false');
              $archive_title = get_the_archive_title();
              remove_filter('get_the_archive_title_prefix', '__return_false');
              return [
                'is_singular' => $wp_query->is_singular,
                'is_archive' => $wp_query->is_archive,
                'is_search' => $wp_query->is_search,
                'is_attachment' => $wp_query->is_attachment,
                'is_front_page' => is_front_page(),
                'the_archive_title' => $archive_title,
                'the_archive_description' => get_the_archive_description(),
                'the_previous_post_link' => is_singular() ? get_previous_post_link( $format = '%link' ) : '',
                'the_next_post_link' => is_singular() ? get_next_post_link( $format = '%link' ) : '',
                'the_search_query' => get_search_query(),
                'the_search_results_nb' => (int) $wp_query->found_posts,
                'the_author_id' => isset( $authordata->ID ) ? $authordata->ID : 0,
                'post_id' => get_the_ID(),
                'query_vars' => $wp_query->query_vars
              ];
          }

          /* ------------------------------------------------------------------------- *
              *  CUSTOMIZE PREVIEW : BUILD SKOPES JSON
          /* ------------------------------------------------------------------------- */
          //generates the array of available scopes for a given context
          //ex for a single post tagged #tag1 and #tag2 and categroized #cat1 :
          //global
          //all posts
          //local
          //posts tagged #tag1
          //posts tagged #tag2
          //posts categorized #cat1
          //@return array()
          //
          //skp_get_skope_title() takes the following default args
          //array(
          //  'level'       =>  '',
          //  'meta_type'   => null,
          //  'long'        => false,
          //  'is_prefixed' => true
          //)
          private function _skp_get_json_export_ready_skopes() {
              $skopes = array();
              $_meta_type = skp_get_skope( 'meta_type', true );

              //default properties of the scope object
              $defaults = skp_get_default_skope_model();
              //global and local and always sent
              $skopes[] = wp_parse_args(
                  array(
                      'title'       => skp_get_skope_title( array( 'level' => 'global' ) ),
                      'long_title'  => skp_get_skope_title( array( 'level' => 'global', 'meta_type' => null, 'long' => true ) ),
                      'ctx_title'   => skp_get_skope_title( array( 'level' => 'global', 'meta_type' => null, 'long' => true, 'is_prefixed' => false ) ),
                      'skope'       => 'global',
                      'level'       => '_all_'
                  ),
                  $defaults
              );


              //SPECIAL GROUPS
              //@todo


              //GROUP
              //Do we have a group ? => if yes, then there must be a meta type
              if ( skp_get_skope('meta_type') ) {
                  $skopes[] = wp_parse_args(
                      array(
                          'title'       => skp_get_skope_title( array( 'level' => 'group', 'meta_type' => $_meta_type  ) ),
                          'long_title'  => skp_get_skope_title( array( 'level' => 'group', 'meta_type' => $_meta_type, 'long' => true ) ),
                          'ctx_title'   => skp_get_skope_title( array( 'level' => 'group', 'meta_type' => $_meta_type, 'long' => true, 'is_prefixed' => false ) ),
                          'skope'       => 'group',
                          'level'       => 'all_' . skp_get_skope('type'),
                          'skope_id'    => skp_get_skope_id( 'group' )
                      ),
                      $defaults
                  );
              }


              //LOCAL
              $skopes[] = wp_parse_args(
                  array(
                      'title'       => skp_get_skope_title( array( 'level' => 'local', 'meta_type' => $_meta_type ) ),
                      'long_title'  => skp_get_skope_title( array( 'level' => 'local', 'meta_type' => $_meta_type, 'long' => true ) ),
                      'ctx_title'   => skp_get_skope_title( array( 'level' => 'local', 'meta_type' => $_meta_type, 'long' => true, 'is_prefixed' => false ) ),
                      'skope'       => 'local',
                      'level'       => skp_get_skope(),
                      'obj_id'      => skp_get_skope('id'),
                      'skope_id'    => skp_get_skope_id( 'local' )
                  ),
                  $defaults
              );
              return apply_filters( 'skp_json_export_ready_skopes', $skopes );
          }
    }//class
endif;

?><?php

if ( !class_exists( 'Flat_Skope_Clean_Final' ) ) :
    final class Flat_Skope_Clean_Final extends Flat_Export_Skope_Data_And_Send_To_Panel {
          // Fired in Flat_Skop_Base::__construct()
          public function skp_schedule_cleaning_on_object_delete() {
              add_action( 'delete_post', array( $this, 'skp_clean_skopified_posts' ) );
              add_action( 'delete_term_taxonomy', array( $this, 'skp_clean_skopified_taxonomies' ) );
              add_action( 'delete_user', array( $this, 'skp_clean_skopified_users' ) );
          }


          // Clean any associated skope post for all public post types : post, page, public cpt
          // 'delete_post' Fires immediately before a post is deleted from the database.
          // @see wp-includes/post.php
          // don't have to return anything
          public function skp_clean_skopified_posts( $postid ) {
              $deletion_candidate = get_post( $postid );
              if ( !$deletion_candidate || !is_object( $deletion_candidate ) )
                return;

              // Stop here if the post type is not considered "viewable".
              // For built-in post types such as posts and pages, the 'public' value will be evaluated.
              // For all others, the 'publicly_queryable' value will be used.
              // For example, the 'revision' post type, which is purely internal and not skopable, won't pass this test.
              if ( !is_post_type_viewable( $deletion_candidate->post_type ) )
                return;

              // Force the skope parts normally retrieved with skp_get_query_skope()
              $skope_string = skp_get_skope( null, true, array(
                  'meta_type' => 'post',
                  'type'      => $deletion_candidate->post_type,
                  'obj_id'    => $postid
              ) );

              // build a skope_id with the normalized function
              $skope_id = skp_build_skope_id( array( 'skope_string' => $skope_string, 'skope_level' => 'local' ) );

              // fetch the skope post id which, if exists, is set as a theme mod
              $skope_post_id_candidate = get_theme_mod( $skope_id );
              if ( $skope_post_id_candidate > 0 && get_post( $skope_post_id_candidate ) ) {
                  // permanently delete the skope post from db
                  wp_delete_post( $skope_post_id_candidate );
                  // remove the theme_mod
                  remove_theme_mod( $skope_id );
              }
          }


          // 'delete_term_taxonomy' Fires immediately before a term taxonomy ID is deleted.
          public function skp_clean_skopified_taxonomies( $term_id ) {
              $deletion_candidate = get_term( $term_id );
              if ( !$deletion_candidate || !is_object( $deletion_candidate ) )
                return;

              //error_log( print_r( $deletion_candidate, true ) );

              // Force the skope parts normally retrieved with skp_get_query_skope()
              $skope_string = skp_get_skope( null, true, array(
                  'meta_type' => 'tax',
                  'type'      => $deletion_candidate->taxonomy,
                  'obj_id'    => $term_id
              ) );

              // build a skope_id with the normalized function
              $skope_id = skp_build_skope_id( array( 'skope_string' => $skope_string, 'skope_level' => 'local' ) );

              // fetch the skope post id which, if exists, is set as a theme mod
              $skope_post_id_candidate = get_theme_mod( $skope_id );
              if ( $skope_post_id_candidate > 0 && get_post( $skope_post_id_candidate ) ) {
                  // permanently delete the skope post from db
                  wp_delete_post( $skope_post_id_candidate );
                  // remove the theme_mod
                  remove_theme_mod( $skope_id );
                  //error_log( 'SUCCESSFULLY REMOVED SKOPE POST ID ' . $skope_post_id_candidate . ' AND THEME MOD ' . $skope_id );
              }
          }


          // 'delete_user' Fires immediately before a user is deleted from the database.
          public function skp_clean_skopified_users( $user_id ) {
              // Force the skope parts normally retrieved with skp_get_query_skope()
              $skope_string = skp_get_skope( null, true, array(
                  'meta_type' => 'user',
                  'type'      => 'author',
                  'obj_id'    => $user_id
              ) );

              // build a skope_id with the normalized function
              $skope_id = skp_build_skope_id( array( 'skope_string' => $skope_string, 'skope_level' => 'local' ) );

              // fetch the skope post id which, if exists, is set as a theme mod
              $skope_post_id_candidate = get_theme_mod( $skope_id );
              if ( $skope_post_id_candidate > 0 && get_post( $skope_post_id_candidate ) ) {
                  // permanently delete the skope post from db
                  wp_delete_post( $skope_post_id_candidate );
                  // remove the theme_mod
                  remove_theme_mod( $skope_id );
                  //error_log( 'SUCCESSFULLY REMOVED SKOPE POST ID ' . $skope_post_id_candidate . ' AND THEME MOD ' . $skope_id );
              }
          }
    }//class
endif;

?>