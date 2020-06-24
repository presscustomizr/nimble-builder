<?php
/* Developers : you can override this template from a theme with a file that has this path : 'nimble_templates/modules/{original-module-template-file-name}.php' */
namespace Nimble;
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
$model = Nimble_Manager()->model;
$value = array_key_exists( 'value', $model ) ? $model['value'] : array();
$menu_content = $value['content'];
$mobile_options = $value['mobile_options'];
//test
$sek_nav_collapse_additional_classes = 'sek-submenu-fade sek-submenu-move';
$expand_below = true === sek_booleanize_checkbox_val( $mobile_options['expand_below'] ) ? 'yes' : 'no';

// June 2020 => nav classes filter added for https://github.com/presscustomizr/nimble-builder-pro/issues/12
$nav_classes = apply_filters( 'nb_nav_menu_classes', array( 'sek-nav-wrap' ), $model );
if ( is_array( $nav_classes ) ) {
  $nav_classes = implode(' ', $nav_classes );
}
$nav_classes = is_string($nav_classes) ? $nav_classes : 'sek-nav-wrap';
?>
<script>nb_.emit('nb-needs-menu-js');</script>

<?php do_action('nb_menu_module_before_nav', $model ); ?>

<nav class="<?php echo $nav_classes; ?>" data-sek-expand-below="<?php echo $expand_below; ?>">
    <button class="sek-nav-toggler sek-collapsed" type="button" data-sek-toggle="sek-collapse" data-target="#<?php echo $model['id'] ?>" aria-controls="<?php echo $model['id'] ?>" aria-expanded="false" aria-label="<?php esc_attr_e( 'Toggle navigation', 'textdomain_to_be_replaced' ) ?>">
        <span class="sek-ham__span-wrapper">
          <span class="line line-1"></span>
          <span class="line line-2"></span>
          <span class="line line-3"></span>
        </span>
    </button>
    <div class="sek-nav-collapse sek-collapse <?php echo $sek_nav_collapse_additional_classes ?>" id="<?php echo $model['id'] ?>">
  <?php
    // june 2020 : filter menu classes => added for https://github.com/presscustomizr/nimble-builder-pro/issues/9
    $menu_classes = apply_filters( 'nb_wp_menu_classes', array( 'sek-menu-module', 'sek-nav' ), $model );
    if ( is_array( $menu_classes ) ) {
      $menu_classes = implode(' ', $menu_classes );
    }
    $menu_classes = is_string($menu_classes) ? $menu_classes : 'sek-menu-module sek-nav';

     //error_log( print_r( get_terms( 'nav_menu', array( 'hide_empty' => true ) ), true) );
    wp_nav_menu(
        array(
          'theme_location'  => '__nimble__',//<= if no theme location is specified, WP assigns the first non empty one available (see wp-includes/nav-menu-template.php:106, the comment reads: // get the first menu that has items if we still can't find a menu ) which we don't want.
          'menu'            => empty( $menu_content['menu-id'] ) ? '' : $menu_content['menu-id'],// Ex : 'top-menu',//(int|string|WP_Term) Desired menu. Accepts a menu ID, slug, name, or object.
          'menu_class'      => $menu_classes,//CSS class to use for the ul element which forms the menu. Default 'menu'.
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