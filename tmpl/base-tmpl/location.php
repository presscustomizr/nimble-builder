<?php
/* Developers : you can override this template from a theme with a file that has this path : 'nimble_templates/modules/{original-module-template-file-name}.php' */
namespace Nimble;
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

$model = Nimble_Manager()->model;
$id = $model['id'];
$collection = array_key_exists( 'collection', $model ) ? $model['collection'] : array();


$is_header_location = true === sek_get_registered_location_property( $id, 'is_header_location' );
$is_footer_location = true === sek_get_registered_location_property( $id, 'is_footer_location' );

//sek_error_log( __FUNCTION__ . ' WHAT ARE WE RENDERING? ' . $id , $collection );

// Store the header-footer location boolean in the manager
// Used to determine if we are allowed to lazyload
// @see https://github.com/presscustomizr/nimble-builder/issues/705
Nimble_Manager()->current_location_is_header = $is_header_location;
Nimble_Manager()->current_location_is_footer = $is_footer_location;

// PASSWORD PROTECTION see #673 and #679
// If the page/post is password protect, and this is not a header or footer location,
// => stop the recursive walker here and print the password form
// for https://github.com/presscustomizr/nimble-builder/issues/673
// Nimble_Manager()->is_content_restricted is set at 'wp', see ::sek_maybe_empty_password_form

// 1) we want to protect content added with Nimble Builder, but not if header or footer
// 2) we want to apply the protection on front, not when customizing
// 3) we need to check if the single page or post is password protected
// 4) we don't want to render the password form multiple times
$has_content_restriction_for_location = Nimble_Manager()->is_content_restricted && !$is_header_location && !$is_footer_location;
$location_needs_css_class_to_style_password_form = false;

if ( $has_content_restriction_for_location ) {
    // in the case of the built-in WP password form, we only print it once, so we don't need to add the CSS class to each locations
    $location_needs_css_class_to_style_password_form = !did_action('nimble_wp_pwd_form_rendered');
}

// NOTE : empty sektions wrapper are only printed when customizing
?>
<?php if ( skp_is_customizing() || ( !skp_is_customizing() && !empty( $collection ) ) ) : ?>
    <?php
        Nimble_Manager()->nimble_customizing_or_content_is_printed_on_this_page = true;
        printf( '<div class="sektion-wrapper nb-loc %6$s" data-sek-level="location" data-sek-id="%1$s" %2$s %3$s %4$s %5$s>',
            $id,
            esc_attr(sprintf('data-sek-is-global-location="%1$s"', sek_is_global_location( $id ) ? 'true' : 'false')),
            $is_header_location ? 'data-sek-is-header-location="true"' : '',
            $is_footer_location ? 'data-sek-is-footer-location="true"' : '',
            // introduced for https://github.com/presscustomizr/nimble-builder/issues/494
            ( skp_is_customizing() || ( defined('DOING_AJAX') && DOING_AJAX ) ) ? sprintf( 'data-sek-preview-level-guid="%1$s"', esc_attr( Nimble_Manager()->sek_get_preview_level_guid() ) ) : '' ,
            $location_needs_css_class_to_style_password_form ? 'sek-password-protected' : ''//<= added for #673
        );
    ?>
    <?php
        if ( $has_content_restriction_for_location ) {
            // april 2020 : added for https://github.com/presscustomizr/nimble-builder/issues/685
            do_action('nimble_content_restriction_for_location', $model );
        } else {
            Nimble_Manager()->parent_model = $model;
            foreach ( $collection as $_key => $sec_model ) { Nimble_Manager()->render( $sec_model ); }
        }
    ?>
    <?php
        // empty global locations placeholders are only printed when customizing But not previewing a changeset post
        // since https://github.com/presscustomizr/nimble-builder/issues/351
    ?>
    <?php if ( empty( $collection ) && !sek_is_customize_previewing_a_changeset_post() ) : ?>
        <div class="sek-empty-location-placeholder">
            <?php
            if ( $is_header_location || $is_footer_location ) {
                printf('<span class="sek-header-footer-location-placeholder">%1$s %2$s</span>',
                    sprintf( '<span class="sek-nimble-icon"><img src="%1$s"/></span>',
                        esc_url(NIMBLE_BASE_URL.'/assets/img/nimble/nimble_icon.svg?ver='.NIMBLE_VERSION)
                    ),
                    $is_header_location ? __('Start designing the header', 'text_doma') : __('Start designing the footer', 'text_doma')
                );
            }
            ?>
        </div>
    <?php endif; ?>
    </div><?php //class="sektion-wrapper" ?>
<?php endif; ?>