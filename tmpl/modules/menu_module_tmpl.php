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
sek_emit_js_event('nb-needs-menu-js');
?>

<?php do_action('nb_menu_module_before_nav', $model ); ?>
<?php 
// janv 2021 => added data-sek-mobile-menu-breakpoint attribute which is hard coded to min-width:768px for the moment
// in the future, this value could be set by users, see $grid-breakpoints in scss variables
?>
<nav class="<?php echo esc_attr($nav_classes); ?>" data-sek-expand-below="<?php echo esc_attr($expand_below); ?>" data-sek-mobile-menu-breakpoint=768>
    <button class="sek-nav-toggler sek-collapsed" type="button" data-target="#<?php echo esc_attr($model['id']); ?>" aria-controls="<?php echo esc_attr($model['id']); ?>" aria-expanded="false" aria-label="<?php esc_attr_e( 'Toggle navigation', 'textdomain_to_be_replaced' ) ?>">
        <span class="sek-ham__span-wrapper">
          <span class="line line-1"></span>
          <span class="line line-2"></span>
          <span class="line line-3"></span>
        </span>
    </button>
    <?php 
      // WHY DO WE ADD this inline style display:none ?
      // in the stylesheet, .sek-nav-collapse {display: flex!important;} => This hack allows us to prevent a Content Layout Shift on page load
    ?>
    <div class="sek-nav-collapse <?php echo esc_attr($sek_nav_collapse_additional_classes); ?>" id="<?php echo esc_attr($model['id']); ?>" data-sek-mm-state="collapsed" style="display:none">
      <?php
        // june 2020 : filter menu classes => added for https://github.com/presscustomizr/nimble-builder-pro/issues/9
        $menu_classes = apply_filters( 'nb_wp_menu_classes', array( 'sek-menu-module', 'sek-nav' ), $model );
        if ( is_array( $menu_classes ) ) {
          $menu_classes = implode(' ', $menu_classes );
        }
        $menu_classes = is_string($menu_classes) ? $menu_classes : 'sek-menu-module sek-nav';

        do_action('nb_menu_module_before_wp_menu', $model );


        /* ------------------------------------------------------------------------- *
        *  PRINT THE MENU + prevent running a filter used in twenty twenty one ( nov 2020 )
        /* ------------------------------------------------------------------------- */
        // Twenty Twenty One filters 'walker_nav_menu_start_el' and adds a button
        // see https://github.com/WordPress/twentytwentyone/blob/trunk/inc/menu-functions.php
        global $wp_filter;
        $menu_wp_hook = null;
        // Remove and cache the filter
        if ( isset( $wp_filter[ 'walker_nav_menu_start_el' ] ) && is_object($wp_filter[ 'walker_nav_menu_start_el' ] ) ) {
            $menu_wp_hook = $wp_filter[ 'walker_nav_menu_start_el' ];
            unset( $wp_filter[ 'walker_nav_menu_start_el' ] );
        }
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

        // Add back the filter
        if ( is_object($menu_wp_hook) ) {
            $wp_filter[ 'walker_nav_menu_start_el' ] = $menu_wp_hook;
        }

      ?>
    </div>
</nav>