<?php
namespace Nimble;
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
    if ( ! $text )
      return '';

    $text       = trim( strip_tags( $text ) );

    if ( ! $text_length )
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
* @param $_requesting_wot is a string with the follwing possible values : 'meta_type' (like post) , 'type' (like page), 'id' (like page id)
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
    $parts    = ( is_array( $requested_parts ) && ! empty( $requested_parts ) ) ? $requested_parts : skp_get_query_skope();
    // if ( is_array( $requested_parts ) && ! empty( $requested_parts ) ) {
    //   error_log( '<SKOPE PARTS>' );
    //   error_log( print_r( $parts, true ) );
    //   error_log( '</SKOPE PARTS>' );
    // }
    $_return  = array();
    $meta_type = $type = $obj_id = '';

    if ( is_array( $parts ) && ! empty( $parts ) ) {
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
            else if ( false !== $meta_type && ! $obj_id ) {
                $_return = array( "meta_type" => "{$meta_type}", "type" => "{$type}" );
            }
            //LOCAL WITH NO GROUP : home, 404, search, date, post type archive
            else if ( false !== $obj_id ) {
                $_return = array( "id" => "{$obj_id}" );
            }
        break;
    }

    //return the parts array if not a string requested
    if ( ! $_return_string ) {
      return $_return;
    }

    //don't go further if not an array or empty
    if ( ! is_array( $_return ) || ( is_array( $_return ) && empty( $_return ) ) ) {
      return '';
    }

    //if a specific part of the ctx is requested, don't concatenate
    //return the part if exists
    if ( ! is_null( $_requesting_wot ) ) {
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
* !! has to be fired after 'template_redirect'
* Used on front ( not customizing preview ? => @todo make sure of this )
* @return  array of ctx parts
*/
function skp_get_query_skope() {
    //don't call get_queried_object if the $query is not defined yet
    global $wp_the_query;
    if ( ! isset( $wp_the_query ) || empty( $wp_the_query ) )
      return array();

    $current_obj  = get_queried_object();
    $meta_type    = false;
    $type         = false;
    $obj_id       = false;


    if ( is_object( $current_obj ) ) {
        //post, custom post types, page
        if ( isset($current_obj -> post_type) ) {
            $meta_type  = 'post';
            $type       = $current_obj -> post_type;
            $obj_id     = $current_obj -> ID;
        }

        //taxinomies : tags, categories, custom tax type
        if ( isset($current_obj -> taxonomy) && isset($current_obj -> term_id) ) {
            $meta_type  = 'tax';
            $type       = $current_obj -> taxonomy;
            $obj_id     = $current_obj -> term_id;
        }
    }

    //author archive page
    if ( is_author() ) {
        $meta_type  = 'user';
        $type       = 'author';
        $obj_id     = $wp_the_query ->get( 'author' );
    }

    //SKOPES WITH NO GROUPS
    //post type archive object
    if ( is_post_type_archive() ) {
        $obj_id     = 'post_type_archive' . '_'. $wp_the_query ->get( 'post_type' );
    }
    if ( is_404() )
      $obj_id  = '404';
    if ( is_search() )
      $obj_id  = 'search';
    if ( is_date() )
      $obj_id  = 'date';
    if ( skp_is_real_home() )
      $obj_id  = 'home';

    return apply_filters( 'skp_get_query_skope' , array( 'meta_type' => $meta_type , 'type' => $type , 'obj_id' => $obj_id ) , $current_obj );
}


//@return the skope prefix used both when customizing and on front
function skp_get_skope_id( $level = 'local' ) {
    // CACHE THE SKOPE WHEN 'wp' DONE
    // the skope id is used when filtering the options, called hundreds of times.
    // We'll get hight performances with a cached value instead of using the skp_get_skope_id() function on each call.
    $new_skope_ids = array( 'local' => '_skope_not_set_', 'group' => '_skope_not_set_' );
    if ( did_action( 'wp' ) ) {
        if ( empty( Flat_Skop_Base() -> current_skope_ids ) ) {
            $new_skope_ids['local'] = skp_build_skope_id( array( 'skope_string' => skp_get_skope(), 'skope_level' => 'local' ) );
            $new_skope_ids['group'] = skp_build_skope_id( array( 'skope_level' => 'group' ) );

            Flat_Skop_Base() -> current_skope_ids = $new_skope_ids;

            $skope_id_to_return = $new_skope_ids[ $level ];
            // error_log('<SKOPE ID cached in skp_get_skope_id>');
            // error_log( print_r( $new_skope_ids, true ) );
            // error_log('</SKOPE ID cached in skp_get_skope_id>');
        } else {
            $new_skope_ids = Flat_Skop_Base() -> current_skope_ids;
            $skope_id_to_return = $new_skope_ids[ $level ];
        }
    } else {
        $skope_id_to_return = array_key_exists( $level, $new_skope_ids ) ? $new_skope_ids[ $level ] : '_skope_not_set_';
    }
    // error_log('$skope_id_to_return => ' . $level . ' ' . $skope_id_to_return );
    // error_log( print_r( Flat_Skop_Base() -> current_skope_ids , true ) );
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
              $skope_id = strtolower( SKOPE_ID_PREFIX . $args[ 'skope_string' ] );
          break;
          case 'group' :
              if ( ! empty( $args[ 'skope_type' ] ) ) {
                  $skope_id = strtolower( SKOPE_ID_PREFIX . 'all_' . $args[ 'skope_type' ] );
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

    $_dyn_type = ( skp_is_customize_preview_frame() && isset( $_POST['dyn_type']) ) ? $_POST['dyn_type'] : '';
    $type = skp_get_skope('type');
    $skope = skp_get_skope();
    $title = '';

    if( 'local' == $level ) {
        $type = skp_get_skope( 'type' );
        $title = $is_prefixed ? __( 'Options for', 'text_domain_to_be_replaced') . ' ' : $title;
        if ( skp_skope_has_a_group( $meta_type ) ) {
            $_id = skp_get_skope('id');
            switch ($meta_type) {
                case 'post':
                  $type_obj = get_post_type_object( $type );
                  $title .= sprintf( '%1$s "%3$s" (id : %2$s)', strtolower( $type_obj -> labels -> singular_name ), $_id, get_the_title( $_id ) );
                  break;

                case 'tax':
                  $type_obj = get_taxonomy( $type );
                  $term = get_term( $_id, $type );
                  $title .= sprintf( '%1$s "%3$s" (id : %2$s)', strtolower( $type_obj -> labels -> singular_name ), $_id, $term -> name );
                  break;

                case 'user':
                  $author = get_userdata( $_id );
                  $title .= sprintf( '%1$s "%3$s" (id : %2$s)', __('user', 'text_domain_to_be_replaced'), $_id, $author -> user_login );
                  break;
            }
        } else if ( ( 'trans' == $_dyn_type || skp_skope_has_no_group( $skope ) ) ) {
            if ( is_post_type_archive() ) {
                global $wp_the_query;
                $title .= sprintf( __( '%1$s archive page', 'text_domain_to_be_replaced'), $wp_the_query ->get( 'post_type' ) );
            } else {
                $title .= strtolower( $skope );
            }
        } else {
            $title .= __( 'Undefined', 'text_domain_to_be_replaced');
        }
    }
    if ( 'group' == $level || 'special_group' == $level ) {
        $title = $is_prefixed ? __( 'Options for all', 'text_domain_to_be_replaced') . ' ' : __( 'All' , 'hueman-adons' ) . ' ';
        switch( $meta_type ) {
            case 'post' :
                $type_obj = get_post_type_object( $type );
                $title .= strtolower( $type_obj -> labels -> name );
            break;

            case 'tax' :
                $type_obj = get_taxonomy( $type );
                $title .= strtolower( $type_obj -> labels -> name );
            break;

            case 'user' :
                $title .= __('users', 'text_domain_to_be_replaced');
            break;
        }
    }
    if ( 'global' == $level ) {
        $title = __( 'Sitewide options', 'text_domain_to_be_replaced');
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
  return ( is_home() && ( 'posts' == get_option( 'show_on_front' ) || '__nothing__' == get_option( 'show_on_front' ) ) )
  || ( 0 == get_option( 'page_on_front' ) && 'page' == get_option( 'show_on_front' ) )//<= this is the case when the user want to display a page on home but did not pick a page yet
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
    //hu_is_customize_left_panel() ?
    if ( is_admin() && isset( $pagenow ) && 'customize.php' == $pagenow ) {
        $is_customizing = true;
    //hu_is_customize_preview_frame() ?
    } else if ( is_customize_preview() || ( ! is_admin() && isset($_REQUEST['customize_messenger_channel']) ) ) {
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
  return is_customize_preview() || ( ! is_admin() && isset($_REQUEST['customize_messenger_channel']) );
}

/**
* @return  boolean
*/
function skp_is_previewing_live_changeset() {
  return ! isset( $_POST['customize_messenger_channel']) && is_customize_preview();
}
?>