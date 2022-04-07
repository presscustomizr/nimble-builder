<?php
/* Developers : you can override this template from a theme with a file that has this path : 'nimble_templates/modules/{original-module-template-file-name}.php' */
namespace Nimble;
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

$model = Nimble_Manager()->model;
$id = $model['id'];


if ( empty( $model['module_type'] ) ) {
    sek_error_log( __CLASS__ . '::' . __FUNCTION__ . ' => missing module_type for a module', $model );
    return;
}

$module_type = $model['module_type'];

if ( !CZR_Fmk_Base()->czr_is_module_registered($module_type) ) {
    sek_error_log( __CLASS__ . '::' . __FUNCTION__ . ' => module_type not registered', $module_type );
    return;
}

$model = sek_normalize_module_value_with_defaults( $model );


// update the current cached model
Nimble_Manager()->model = $model;
$title_attribute = '';
if ( skp_is_customizing() ) {
    $title_attribute = __('Edit module settings', 'text-domain');
}

// SETUP MODULE TEMPLATE PATH
// introduced for #532, october 2019
// Default tmpl path looks like : NIMBLE_BASE_PATH . "/tmpl/modules/image_module_tmpl.php",
//
// Important note :
// @fixes https://github.com/presscustomizr/nimble-builder/issues/537
// since #532, module registered in Nimble Builder core have a render_tmpl_path property looking like 'render_tmpl_path' => "simple_html_module_tmpl.php",
// But if a developer wants to register a custom module with a specific template path, it is still possible by using a full path
// 1) We first check if the file exists, if it is a full path this will return TRUE and the render tmpl path will be set this way
// , for example, we use a custom gif module on presscustomizr.com, for which the render_tmpl_path is a full path:
// 'render_tmpl_path' => TC_BASE_CHILD . "inc/nimble-modules/modules-registration/tmpl/modules/gif_image_module_tmpl.php",
// 2) then we check if there's an override
// 3) finally we use the default Nimble Builder path

// render_tmpl_path can be
// 1) simple_html_module_tmpl.php <= most common case, the module is registered by Nimble Builder
// 2) srv/www/pc-dev/htdocs/wp-content/themes/tc/inc/nimble-modules/modules-registration/tmpl/modules/gif_image_module_tmpl.php <= case of a custom module
$template_name_or_path = sek_get_registered_module_type_property( $module_type, 'render_tmpl_path' );

$template_name = basename( $template_name_or_path );
$template_name = ltrim( $template_name_or_path, '/' );

if ( file_exists( $template_name_or_path ) ) {
    $template_path = $template_name_or_path;
} else {
    $template_path = sek_get_templates_dir() . "/modules/{$template_name}";
}

// make this filtrable
$render_tmpl_path = apply_filters( 'nimble_module_tmpl_path', $template_path, $module_type );

// Then check if there's an override
$overriden_template_path = Nimble_Manager()->sek_maybe_get_overriden_template_path_for_module( $template_name );

$is_module_template_overriden = false;
if ( !empty( $overriden_template_path ) ) {
    $render_tmpl_path = $overriden_template_path;
    $is_module_template_overriden = true;
}
// if there's a lazy loaded img background let's print a CSS loader removed when lazy loaded
$bg_attributes = Nimble_Manager()->sek_maybe_add_bg_attributes( $model );
$stringified_bg_attributes = implode(' ', array_map(function ($k, $v) {return $k . '="' . $v . '"'; },array_keys($bg_attributes), array_values($bg_attributes)) );

// if there's a lazy loaded img background let's print a CSS loader removed when lazy loaded
$has_bg_img = false;
if ( false !== strpos( $stringified_bg_attributes, 'data-sek-src="http') ) {
    $has_bg_img = true;
}

if ( false !== strpos($stringified_bg_attributes, 'data-sek-bg-parallax="true"') ) {
    sek_emit_js_event('nb-needs-parallax');
}

$module_classes = [
    Nimble_Manager()->get_level_visibility_css_class( $model ),
    $has_bg_img ? 'sek-has-bg' : '',
    Nimble_Manager()->level_css_classes
];

$level_custom_attr = Nimble_Manager()->level_custom_attr;

printf('<div data-sek-level="module" data-sek-id="%1$s" data-sek-module-type="%2$s" class="sek-module %3$s" %4$s %5$s %6$s %7$s %8$s %9$s>%10$s',
    esc_attr($id),
    esc_attr($module_type),
    esc_attr(implode(' ', $module_classes )),

    'title="'.esc_html($title_attribute).'"',
    // add smartload + parallax attributes
    implode(' ', array_map(function ($k, $v) {return $k . '="' . esc_attr($v) . '"'; }, array_keys($bg_attributes), array_values($bg_attributes))),
    is_null( Nimble_Manager()->level_custom_anchor ) ? '' : 'id="' . ltrim( esc_attr(Nimble_Manager()->level_custom_anchor ) , '#' ) . '"',// make sure we clean the hash if user left it
    // introduced for https://github.com/presscustomizr/nimble-builder/issues/494
    ( skp_is_customizing() || ( defined('DOING_AJAX') && DOING_AJAX ) ) ? sprintf( 'data-sek-preview-level-guid="%1$s"', esc_attr( Nimble_Manager()->sek_get_preview_level_guid() ) ) : '' ,
    $is_module_template_overriden ? 'data-sek-module-template-overriden="true"': '',// <= added for #532
    is_array($level_custom_attr) ? implode(' ', array_map(function ($k, $v) {return $k . '="' . esc_attr($v) . '"'; }, array_keys($level_custom_attr), array_values($level_custom_attr))) : (!empty($level_custom_attr) ? wp_kses_post( $level_custom_attr ) : '' ),
    ( $has_bg_img && !skp_is_customizing() && sek_is_img_smartload_enabled() ) ? '<div class="sek-css-loader sek-mr-loader"><div></div><div></div><div></div></div>' : ''
);
  ?>
    <div class="sek-module-inner">
      <?php
        if ( skp_is_customizing() && sek_is_debug_mode() ) {
            // added for https://github.com/presscustomizr/nimble-builder/issues/688
            // allows us to print the structure without the potentially broken javascript content ( hard coded or generated by a shortcode )
            printf('<p class="sek-debug-modules">Module type : %1$s | id : %2$s</p>',
                ucfirst( str_replace( array( 'czr_', '_' ), array( '', ' ' ), esc_attr($module_type) ) ),
                esc_html($id)
            );
        } else if ( !empty( $render_tmpl_path ) && file_exists( $render_tmpl_path ) ) {
            load_template( $render_tmpl_path, false );
        } else {
            error_log( __FUNCTION__ . ' => no template found for module type ' . $module_type  );
        }
      ?>
    </div>
</div><?php //data-sek-level="module" ?>