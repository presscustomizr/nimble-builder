<?php
/* Developers : you can override this template from a theme with a file that has this path : 'nimble_templates/modules/{original-module-template-file-name}.php' */
namespace Nimble;
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

$model = Nimble_Manager()->model;
$id = $model['id'];
$collection = array_key_exists( 'collection', $model ) ? $model['collection'] : array();


$is_nested = array_key_exists( 'is_nested', $model ) && true == $model['is_nested'];
$has_at_least_one_module = sek_section_has_modules( $collection );
$column_container_class = 'sek-container-fluid';
//when boxed use proper container class
if ( !empty( $model[ 'options' ][ 'layout' ][ 'boxed-wide' ] ) && 'boxed' == $model[ 'options' ][ 'layout' ][ 'boxed-wide' ] ) {
    $column_container_class = 'sek-container';
}
// if there's a video background or a parallax bg we need to inform js api
$bg_attributes = Nimble_Manager()->sek_maybe_add_bg_attributes( $model );
$stringified_bg_attributes = implode(' ', array_map(function ($k, $v) {return $k . '="' . $v . '"'; },array_keys($bg_attributes), array_values($bg_attributes)) );
// if there's a lazy loaded img background let's print a CSS loader removed when lazy loaded
$has_bg_img = false;
if ( false !== strpos( $stringified_bg_attributes, 'data-sek-src="http') ) {
    $has_bg_img = true;
}

// June 2020 : introduced for https://github.com/presscustomizr/nimble-builder-pro/issues/6
$section_classes = apply_filters( 'nimble_section_level_css_classes', array(), $model );
array_push( $section_classes, Nimble_Manager()->level_css_classes );

$level_custom_attr = Nimble_Manager()->level_custom_attr;

printf('<div data-sek-level="section" data-sek-id="%1$s" %2$s class="sek-section %3$s %4$s %5$s %6$s" %7$s %8$s %9$s %10$s>%11$s',
    esc_attr($id),
    $is_nested ? 'data-sek-is-nested="true"' : '',
    $has_at_least_one_module ? 'sek-has-modules' : '',
    esc_attr(Nimble_Manager()->get_level_visibility_css_class( $model )),
    $has_bg_img ? 'sek-has-bg' : '',
    esc_attr(implode(' ', $section_classes)),

    is_null( Nimble_Manager()->level_custom_anchor ) ? '' : 'id="' . ltrim( esc_attr(Nimble_Manager()->level_custom_anchor) , '#' ) . '"',// make sure we clean the hash if user left it
    // add smartload + parallax attributes
    implode(' ', array_map(function ($k, $v) {return $k . '="' . esc_attr($v) . '"'; }, array_keys($bg_attributes), array_values($bg_attributes) )),
    // introduced for https://github.com/presscustomizr/nimble-builder/issues/494
    ( skp_is_customizing() || ( defined('DOING_AJAX') && DOING_AJAX ) ) ? sprintf( 'data-sek-preview-level-guid="%1$s"', esc_attr( Nimble_Manager()->sek_get_preview_level_guid() ) ) : '' ,
    is_array($level_custom_attr) ? implode(' ', array_map(function ($k, $v) {return $k . '="' . esc_attr($v) . '"'; }, array_keys($level_custom_attr), array_values($level_custom_attr))) : (!empty($level_custom_attr) ? wp_kses_post( $level_custom_attr ) : '' ),
    ( $has_bg_img && !skp_is_customizing() && sek_is_img_smartload_enabled() ) ? '<div class="sek-css-loader sek-mr-loader"><div></div><div></div><div></div></div>' : ''
);
if ( false !== strpos($stringified_bg_attributes, 'data-sek-video-bg-src') ) {
    sek_emit_js_event('nb-needs-videobg-js');
}
if ( false !== strpos($stringified_bg_attributes, 'data-sek-bg-parallax="true"') ) {
    sek_emit_js_event('nb-needs-parallax');
}
?>

      <div class="<?php echo esc_attr($column_container_class); ?>">
        <div class="sek-row sek-sektion-inner">
            <?php
              // Set the parent model now
              Nimble_Manager()->parent_model = $model;
              foreach ( $collection as $col_model ) {Nimble_Manager()->render( $col_model ); }
            ?>
        </div>
      </div>
  </div><?php //data-sek-level="section" ?>