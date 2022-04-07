<?php
/* Developers : you can override this template from a theme with a file that has this path : 'nimble_templates/modules/{original-module-template-file-name}.php' */
namespace Nimble;
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

$model = Nimble_Manager()->model;
$id = $model['id'];
$collection = array_key_exists( 'collection', $model ) ? $model['collection'] : array();

// Store the parent model
// => used when calculating the width of the column to be added
$parent_model = Nimble_Manager()->parent_model;
// if ( defined('DOING_AJAX') && DOING_AJAX ) {
//     error_log( print_r( $parent_model, true ) );
// }
// sek_error_log( 'PARENT MODEL WHEN RENDERING', $parent_model );

// SETUP THE DEFAULT CSS CLASS
// Note : the css rules for custom width are generated in Sek_Dyn_CSS_Builder::sek_add_rules_for_column_width
$col_number = ( array_key_exists( 'collection', $parent_model ) && is_array( $parent_model['collection'] ) ) ? count( $parent_model['collection'] ) : 1;
$col_number = 12 < $col_number ? 12 : $col_number;
$col_width_in_percent = 100/$col_number;

//@note : we use the same logic in the customizer preview js to compute the column css classes when dragging them
//@see sek_preview::makeColumnsSortableInSektion
//TODO, we might want to be sure the $col_suffix is related to an allowed size
$col_suffix = floor( $col_width_in_percent );

// SETUP THE GLOBAL CUSTOM BREAKPOINT CSS CLASS
$global_custom_breakpoint = intval( sek_get_global_custom_breakpoint() );

// SETUP THE LEVEL CUSTOM BREAKPOINT CSS CLASS
// nested section should inherit the custom breakpoint of the parent
// @fixes https://github.com/presscustomizr/nimble-builder/issues/554

// the 'for_responsive_columns' param has been introduced for https://github.com/presscustomizr/nimble-builder/issues/564
// so we can differentiate when the custom breakpoint is requested for column responsiveness or for css rules generation
// when for columns, we always apply the custom breakpoint defined by the user
// otherwise, when generating CSS rules like alignment, the custom breakpoint is applied if user explicitely checked the 'apply_to_all' option
// 'for_responsive_columns' is set to true when sek_get_closest_section_custom_breakpoint() is invoked from Nimble_Manager()::render()
$section_custom_breakpoint =  intval( sek_get_closest_section_custom_breakpoint( array(
    'searched_level_id' => $parent_model['id'],
    'for_responsive_columns' => true
)));

$grid_column_class = "sek-col-{$col_suffix}";
if ( is_int($section_custom_breakpoint) && $section_custom_breakpoint >= 1 ) {
    $grid_column_class = "sek-section-custom-breakpoint-col-{$col_suffix}";
} else if ( $global_custom_breakpoint >= 1 ) {
    $grid_column_class = "sek-global-custom-breakpoint-col-{$col_suffix}";
}
$bg_attributes = Nimble_Manager()->sek_maybe_add_bg_attributes( $model );
$stringified_bg_attributes = implode(' ', array_map(function ($k, $v) {return $k . '="' . $v . '"'; },array_keys($bg_attributes), array_values($bg_attributes)) );

// if there's a lazy loaded img background let's print a CSS loader removed when lazy loaded
$has_bg_img = false;
if ( false !== strpos( $stringified_bg_attributes, 'data-sek-src="http') ) {
    $has_bg_img = true;
}

$level_custom_attr = Nimble_Manager()->level_custom_attr;

printf('<div data-sek-level="column" data-sek-id="%1$s" class="sek-column sek-col-base %2$s %3$s %4$s %5$s" %6$s %7$s %8$s %9$s %10$s>%11$s',
    esc_attr($id),
    esc_attr($grid_column_class),
    esc_attr(Nimble_Manager()->get_level_visibility_css_class( $model )),
    $has_bg_img ? 'sek-has-bg' : '',
    esc_attr(Nimble_Manager()->level_css_classes),

    empty( $collection ) ? 'data-sek-no-modules="true"' : '',
    // add smartload + parallax attributes
    implode(' ', array_map(function ($k, $v) {return $k . '="' . esc_attr($v) . '"'; }, array_keys($bg_attributes), array_values($bg_attributes))),
    is_null( Nimble_Manager()->level_custom_anchor ) ? '' : 'id="' . ltrim( esc_attr(Nimble_Manager()->level_custom_anchor) , '#' ) . '"',// make sure we clean the hash if user left it
    // introduced for https://github.com/presscustomizr/nimble-builder/issues/494
    ( skp_is_customizing() || ( defined('DOING_AJAX') && DOING_AJAX ) ) ? sprintf( 'data-sek-preview-level-guid="%1$s"', esc_attr( Nimble_Manager()->sek_get_preview_level_guid() ) ) : '' ,
    is_array($level_custom_attr) ? implode(' ', array_map(function ($k, $v) {return $k . '="' . esc_attr($v) . '"'; }, array_keys($level_custom_attr), array_values($level_custom_attr))) : wp_kses_post( $level_custom_attr ),
    ( $has_bg_img && !skp_is_customizing() && sek_is_img_smartload_enabled() ) ? '<div class="sek-css-loader sek-mr-loader"><div></div><div></div><div></div></div>' : ''
);
if ( false !== strpos($stringified_bg_attributes, 'data-sek-video-bg-src') ) {
    sek_emit_js_event('nb-needs-videobg-js');
}
if ( false !== strpos($stringified_bg_attributes, 'data-sek-bg-parallax="true"') ) {
    sek_emit_js_event('nb-needs-parallax');
}
    ?>
    <?php
    // Drop zone : if no modules, the drop zone is wrapped in sek-no-modules-columns
    // if at least one module, the sek-drop-zone is the .sek-column-inner wrapper
    ?>
    <div class="sek-column-inner <?php echo empty( $collection ) ? 'sek-empty-col' : ''; ?>">
        <?php
            // the drop zone is inserted when customizing but not when previewing a changeset post
            // since https://github.com/presscustomizr/nimble-builder/issues/351
            if ( skp_is_customizing() && !sek_is_customize_previewing_a_changeset_post() && empty( $collection ) ) {
                //$content_type = 1 === $col_number ? 'section' : 'module';
                $content_type = 'module';
                $title = 'section' === $content_type ? __('Drag and drop a section or a module here', 'text_doma' ) : __('Drag and drop a block of content here', 'text_doma' );
                ?>
                <div class="sek-no-modules-column">
                <div class="sek-module-drop-zone-for-first-module sek-content-module-drop-zone sek-drop-zone">
                    <i data-sek-click-on="pick-content" data-sek-content-type="<?php echo esc_attr($content_type); ?>" class="material-icons sek-click-on" title="<?php echo esc_html($title); ?>">add_circle_outline</i>
                    <span class="sek-injection-instructions"><?php _e('Drag and drop or double-click the content that you want to insert here.', 'text_domain_to_rep'); ?></span>
                </div>
                </div>
                <?php
            } else {
                // Set the parent model now
                Nimble_Manager()->parent_model = $model;
                foreach ( $collection as $module_or_nested_section_model ) {
                    ?>
                    <?php
                    Nimble_Manager()->render( $module_or_nested_section_model );
                }
                ?>
                <?php
            }
        ?>
    </div>
    </div><?php //data-sek-level="column" ?>