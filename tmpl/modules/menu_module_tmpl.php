<?php
namespace Nimble;
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
$model = SEK_Fire() -> model;
$value = array_key_exists( 'value', $model ) ? $model['value'] : array();
//sek_error_log('MODEL ?', $model );
$menu_content = $value['content'];
?>
 <div class="sek-nav-wrap">
  <?php
     //error_log( print_r( get_terms( 'nav_menu', array( 'hide_empty' => true ) ), true) );
    wp_nav_menu(
        array(
          'theme_location'  => '__nimble__',//<= if no theme location is specified, WP assigns one in some cases ( I don't know why ) which we don't want.
          'menu'            => empty( $menu_content['menu-id'] ) ? '' : $menu_content['menu-id'],// Ex : 'top-menu',//(int|string|WP_Term) Desired menu. Accepts a menu ID, slug, name, or object.
          'menu_class'      => 'sek-menu-module',//CSS class to use for the ul element which forms the menu. Default 'menu'.
          'container'       => '',//Whether to wrap the ul, and what to wrap it with. Default 'div'.
          'menu_id'         => '',//The ID that is applied to the ul element which forms the menu. Default is the menu slug, incremented.
          'fallback_cb'     => '\Nimble\sek_page_menu_fallback'//(callable|bool) If the menu doesn't exists, a callback function will fire. Default is 'wp_page_menu'. Set to false for no fallback.
      )
    );
  ?>
</div>