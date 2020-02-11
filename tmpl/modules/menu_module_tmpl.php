<?php
/* Developers : you can override this template from a theme with a file that has this path : 'nimble_templates/modules/{original-module-template-file-name}.php' */
namespace Nimble;
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
$model = Nimble_Manager()->model;
$value = array_key_exists( 'value', $model ) ? $model['value'] : array();
$menu_content = $value['content'];
$mobile_options = $value['mobile_options'];
//test
$sek_nav_collapse_additional_classes = 'sek-submenu-fade sek-submenu-move';
$expand_below = true === sek_booleanize_checkbox_val( $mobile_options['expand_below'] ) ? 'yes' : 'no';
?>
 <nav class="sek-nav-wrap" data-sek-expand-below="<?php echo $expand_below; ?>">
    <button class="sek-nav-toggler sek-collapsed" type="button" data-sek-toggle="sek-collapse" data-target="#<?php echo $model['id'] ?>" aria-controls="<?php echo $model['id'] ?>" aria-expanded="false" aria-label="<?php esc_attr_e( 'Toggle navigation', 'textdomain_to_be_replaced' ) ?>">
        <span class="sek-ham__span-wrapper">
          <span class="line line-1"></span>
          <span class="line line-2"></span>
          <span class="line line-3"></span>
        </span>
    </button>
    <div class="sek-nav-collapse sek-collapse <?php echo $sek_nav_collapse_additional_classes ?>" id="<?php echo $model['id'] ?>">
  <?php
     //error_log( print_r( get_terms( 'nav_menu', array( 'hide_empty' => true ) ), true) );
    wp_nav_menu(
        array(
          'theme_location'  => '__nimble__',//<= if no theme location is specified, WP assigns the first non empty one available (see wp-includes/nav-menu-template.php:106, the comment reads: // get the first menu that has items if we still can't find a menu ) which we don't want.
          'menu'            => empty( $menu_content['menu-id'] ) ? '' : $menu_content['menu-id'],// Ex : 'top-menu',//(int|string|WP_Term) Desired menu. Accepts a menu ID, slug, name, or object.
          'menu_class'      => 'sek-menu-module sek-nav',//CSS class to use for the ul element which forms the menu. Default 'menu'.
          'container'       => '',//Whether to wrap the ul, and what to wrap it with. Default 'div'.
          'menu_id'         => '',//The ID that is applied to the ul element which forms the menu. Default is the menu slug, incremented.
          'fallback_cb'     => '\Nimble\sek_page_menu_fallback',//(callable|bool) If the menu doesn't exists, a callback function will fire. Default is 'wp_page_menu'. Set to false for no fallback.
          'link_before'     => '<span class="sek-nav__title">',
          'link_after'      => '</span>'
      )
    );
  ?>
    </div>
</nav>