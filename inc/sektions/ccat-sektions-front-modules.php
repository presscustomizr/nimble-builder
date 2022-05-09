<?php
namespace Nimble;
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

/* ------------------------------------------------------------------------- *
 *  LOAD AND REGISTER SIMPLE HTML MODULE
/* ------------------------------------------------------------------------- */
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );

function sek_get_module_params_for_czr_simple_html_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_simple_html_module',
        'name' => __( 'Html Content', 'text_doma' ),
        'sanitize_callback' => '\Nimble\sanitize_callback__czr_simple_html_module',
        'tmpl' => array(
            'item-inputs' => array(
                'html_content' => array(
                    'input_type'  => 'code_editor',
                    'title'       => __( 'HTML Content' , 'text_doma' ),
                    'refresh_markup' => '.sek-module-inner'
                    //'code_type' => 'text/html' //<= use 'text/css' to instantiate the code mirror as CSS editor, which by default will be an HTML editor
                ),
                'h_alignment_css'        => array(
                    'input_type'  => 'horizAlignmentWithDeviceSwitcher',
                    'title'              => __( 'Horizontal text alignment', 'text_doma' ),
                    'default'     => array( 'desktop' => 'left' ),
                    'refresh_markup'     => false,
                    'refresh_stylesheet' => true,
                    'css_identifier'     => 'h_alignment',
                    //'css_selectors'      => '.sek-module-inner',
                    'title_width' => 'width-100',
                    'width-100'   => true,
                    'html_before' => '<hr/><h3>' . __('ALIGNMENT') .'</h3>'
                ),
                'font_family_css' => array(
                    'input_type'  => 'font_picker',
                    'title'       => __('Font family', 'text_doma'),
                    'default'     => '',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'refresh_fonts' => true,
                    'css_identifier' => 'font_family',
                    'html_before' => '<hr/><h3>' . __('TEXT OPTIONS') .'</h3>',
                    'html_after' => sprintf('<span class="czr-notice"><i class="far fa-lightbulb"></i> %1s => <a href="%2s" target="_blank" rel="noopener noreferrer">%2$s</a></span><hr/>',
                        __('Find inspiration'),
                        'https://fonts.google.com/?sort=popularity'
                    )
                ),
                'font_size_css'       => array(
                    'input_type'  => 'range_with_unit_picker_device_switcher',
                    'title'       => __( 'Font size', 'text_doma' ),
                    // the default value is commented to fix https://github.com/presscustomizr/nimble-builder/issues/313
                    // => as a consequence, when a module uses the font child module, the default font-size rule must be defined in the module SCSS file.
                    //'default'     => array( 'desktop' => '16px' ),
                    'min' => 0,
                    'max' => 100,
                    'title_width' => 'width-100',
                    'width-100'         => true,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'font_size'
                ),//16,//"14px",
                'line_height_css'     => array(
                    'input_type'  => 'range_with_unit_picker',
                    'title'       => __( 'Line height', 'text_doma' ),
                    'default'     => '1.5em',
                    'min' => 0,
                    'max' => 10,
                    'step' => 0.1,
                    'width-100'         => true,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'line_height'
                ),//24,//"20px",
                'color_css'           => array(
                    'input_type'  => 'wp_color_alpha',
                    'title'       => __('Text color', 'text_doma'),
                    'default'     => '',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'width-100'   => true,
                    'css_identifier' => 'color'
                ),//"#000000",
                'color_hover_css'     => array(
                    'input_type'  => 'wp_color_alpha',
                    'title'       => __('Text color on mouse over', 'text_doma'),
                    'default'     => '',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'css_identifier' => 'color_hover'
                ),//"#000000",
                'font_weight_css'     => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __('Font weight', 'text_doma'),
                    'default'     => 400,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'font_weight',
                    'choices'            => sek_get_select_options_for_input_id( 'font_weight_css' )
                ),//null,
                'font_style_css'      => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __('Font style', 'text_doma'),
                    'default'     => 'inherit',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'font_style',
                    'choices'            => sek_get_select_options_for_input_id( 'font_style_css' )
                ),//null,
                'text_decoration_css' => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __('Text decoration', 'text_doma'),
                    'default'     => 'none',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'text_decoration',
                    'choices'            => sek_get_select_options_for_input_id( 'text_decoration_css' )
                ),//null,
                'text_transform_css'  => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __('Text transform', 'text_doma'),
                    'default'     => 'none',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'text_transform',
                    'choices'            => sek_get_select_options_for_input_id( 'text_transform_css' )
                ),//null,

                'letter_spacing_css'  => array(
                    'input_type'  => 'range_simple',
                    'title'       => __( 'Letter spacing', 'text_doma' ),
                    'default'     => 0,
                    'min'         => 0,
                    'step'        => 1,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'letter_spacing',
                    'width-100'   => true,
                ),//0,
                // Note : always use the suffix '_flag_important' to name an input controling the !important css flag @see Nimble\sek_add_css_rules_for_css_sniffed_input_id
                'fonts___flag_important'  => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Apply the style options in priority (uses !important).', 'text_doma'),
                    'default'     => 0,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    // declare the list of input_id that will be flagged with !important when the option is checked
                    // @see sek_add_css_rules_for_css_sniffed_input_id
                    // @see Nsek_is_flagged_important
                    'important_input_list' => array(
                        'font_family_css',
                        'font_size_css',
                        'line_height_css',
                        'font_weight_css',
                        'font_style_css',
                        'text_decoration_css',
                        'text_transform_css',
                        'letter_spacing_css',
                        'color_css',
                        'color_hover_css'
                    )
                )
            )
        ),
        'render_tmpl_path' => "simple_html_module_tmpl.php",
        'placeholder_icon' => 'code'
    );
}

/* ------------------------------------------------------------------------- *
 *  SANITIZATION
/* ------------------------------------------------------------------------- */
function sanitize_callback__czr_simple_html_module( $value ) {
    if ( is_array($value) && array_key_exists( 'html_content', $value ) && is_string( $value[ 'html_content' ] ) ) {
        if ( !current_user_can( 'unfiltered_html' ) ) {
            $value[ 'html_content' ] = wp_kses_post( $value[ 'html_content' ] );
        }
        // convert into a json to prevent emoji breaking global json data structure
        // fix for https://github.com/presscustomizr/nimble-builder/issues/544
        $value[ 'html_content' ] = sek_maybe_encode_richtext( $value[ 'html_content' ] );
    }
    return $value;
}
?>
<?php
/* ------------------------------------------------------------------------- *
 *  TEXT EDITOR FATHER MODULE
/* ------------------------------------------------------------------------- */
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_czr_tiny_mce_editor_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_tiny_mce_editor_module',
        'is_father' => true,
        'children' => array(
            'main_settings'   => 'czr_tinymce_child',
            'font_settings' => 'czr_font_child'
        ),
        'name' => __('Text Editor', 'text_doma'),
        'starting_value' => array(
            'main_settings' => array(
                'content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed non risus. Suspendisse lectus tortor, dignissim sit amet, adipiscing nec, ultricies sed, dolor.'
            )
        ),
        'sanitize_callback' => '\Nimble\sek_sanitize_czr_tiny_mce_editor_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'css_selectors' => array(
            // this list is limited to the most commonly used tags in the editor.
            // note that Hx headings have a default style set in _heading.scss
            '.sek-module-inner',
            '.sek-module-inner p',
            '.sek-module-inner a',
            '.sek-module-inner li'
        ),
        'render_tmpl_path' => "tinymce_editor_module_tmpl.php",
        'placeholder_icon' => 'short_text'
    );
}

/* ------------------------------------------------------------------------- *
 *  SANITIZATION
/* ------------------------------------------------------------------------- */
// convert into a json to prevent emoji breaking global json data structure
// fix for https://github.com/presscustomizr/nimble-builder/issues/544
function sek_sanitize_czr_tiny_mce_editor_module( $content ) {
    if ( is_array($content) && !empty($content['main_settings']) && is_array($content['main_settings']) ) {
        $editor_content = !empty($content['main_settings']['content']) ? $content['main_settings']['content'] : '';
        $content['main_settings']['content'] = sek_maybe_encode_richtext($editor_content);
    }
    //sek_error_log( 'ALORS MODULE CONTENT ?', $content );
    return $content;
}

/* ------------------------------------------------------------------------- *
 *  TEXT EDITOR CONTENT CHILD
/* ------------------------------------------------------------------------- */
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_czr_tinymce_child() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_tinymce_child',
        'name' => __('Content', 'text_doma'),
        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'tmpl' => array(
            'item-inputs' => array(
                'content' => array(
                    'input_type'  => 'detached_tinymce_editor',
                    'title'       => __('Content', 'text_doma'),
                    'default'     => '',
                    'refresh_markup' => '.sek-module-inner [data-sek-input-type="detached_tinymce_editor"]',
                ),
                'h_alignment_css' => array(
                    'input_type'  => 'horizTextAlignmentWithDeviceSwitcher',
                    'title'       => __('Alignment', 'text_doma'),
                    'default'     => array( 'desktop' => is_rtl() ? 'right' : 'left' ),
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'h_alignment',
                    'title_width' => 'width-100',
                    'width-100'   => true,
                ),
                'autop' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Automatically convert text into paragraph', 'text_doma'),
                    'default'     => true,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'notice_after' => __('WordPress wraps the editor text inside "p" tags by default. You can disable this behaviour by unchecking this option.', 'text-domain')
                ),
            )
        ),
        'render_tmpl_path' =>'',
    );
}


?><?php
/* ------------------------------------------------------------------------- *
 *  LOAD AND REGISTER IMAGE MODULE
/* ------------------------------------------------------------------------- */
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_czr_image_module() {
    $css_selectors = '.sek-module-inner img';
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_image_module',
        'is_father' => true,
        'children' => array(
            'main_settings'   => 'czr_image_main_settings_child',
            'borders_corners' => 'czr_image_borders_corners_child'
        ),
        'name' => __('Image', 'text_doma'),
        'starting_value' => array(
            'main_settings' => array(
                'img' =>  NIMBLE_BASE_URL . '/assets/img/default-img.png',
                'custom_width' => ''
            )
        ),
        'sanitize_callback' => '\Nimble\sanitize_cb__czr_image_module',
        // 'validate_callback' => '\Nimble\czr_image_module_sanitize_validate',
        'render_tmpl_path' => "image_module_tmpl.php",
        'placeholder_icon' => 'short_text'
    );
}


/* ------------------------------------------------------------------------- *
 *  SANITIZATION
/* ------------------------------------------------------------------------- */
// convert into a json to prevent emoji breaking global json data structure
// fix for https://github.com/presscustomizr/nimble-builder/issues/544
    function sanitize_cb__czr_image_module( $value ) {
        if ( !is_array( $value ) )
            return $value;
            if ( is_array( $value ) && !empty($value['main_settings']) && is_array( $value['main_settings'] ) && array_key_exists( 'heading_title', $value['main_settings'] ) ) {
                //$value['content'][ 'button_text' ] = sanitize_text_field( $value['content'][ 'button_text' ] );
                // convert into a json to prevent emoji breaking global json data structure
                // fix for https://github.com/presscustomizr/nimble-builder/issues/544
                $value['main_settings']['heading_title'] = sek_maybe_encode_richtext($value['main_settings']['heading_title']);
            }
        return $value;
    }




/* ------------------------------------------------------------------------- *
 *  MAIN SETTINGS
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_image_main_settings_child() {
    $pro_text = '';
    if ( !sek_is_pro() ) {
        $pro_text = sek_get_pro_notice_for_czr_input( __('set a specific header logo on mobiles, shrink header logo when scrolling down the page, ...', 'text-doma') );
    }
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_image_main_settings_child',
        'name' => __( 'Image main settings', 'text_doma' ),
        //'sanitize_callback' => '\Nimble\sanitize_callback__czr_simple_form_module',
        // 'starting_value' => array(
        //     'button_text' => __('Click me','text_doma'),
        //     'color_css'  => '#ffffff',
        //     'bg_color_css' => '#020202',
        //     'bg_color_hover' => '#151515', //lighten 15%,
        //     'use_custom_bg_color_on_hover' => 0,
        //     'border_radius_css' => '2',
        //     'h_alignment_css' => 'center',
        //     'use_box_shadow' => 1,
        //     'push_effect' => 1
        // ),
        //'css_selectors' => array( '.sek-module-inner .sek-simple-form-wrapper' ),
        'tmpl' => array(
            'item-inputs' => array(
                'img' => array(
                    'input_type'  => 'upload',
                    'title'       => __('Pick an image', 'text_doma'),
                    'default'     => ''
                ),
                'use-post-thumb' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Use the contextual post thumbnail', 'text_doma'),
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'refresh_markup' => true,
                    'default'     => 0,
                    'notice_after' => __('When enabled and possible, Nimble will use the post thumbnail.', 'text_doma'),
                ),
                'img-size' => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __('Select the image size', 'text_doma'),
                    'default'     => 'large',
                    'choices'     => sek_get_select_options_for_input_id( 'img-size' ),
                    'notice_before' => __('Select a size for this image among those generated by WordPress.', 'text_doma' )
                ),
                'link-to' => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __('Schedule an action on click or tap', 'text_doma'),
                    'default'     => 'no-link',
                    'choices'     => array(
                        'no-link' => __('No click action', 'text_doma' ),
                        'img-lightbox' =>__('Lightbox : enlarge the image, and dim out the rest of the content', 'text_doma' ),
                        'url' => __('Link to site content or custom url', 'text_doma' ),
                        'img-file' => __('Link to image file', 'text_doma' ),
                        'img-page' =>__('Link to image page', 'text_doma' )
                    ),
                    'title_width' => 'width-100',
                    'width-100'   => true,
                    'notice_after' => __('Note that some click actions are disabled during customization.', 'text_doma' ),
                ),
                'link-pick-url' => array(
                    'input_type'  => 'content_picker',
                    'title'       => __('Link url', 'text_doma'),
                    'default'     => array()
                ),
                'link-custom-url' => array(
                    'input_type'  => 'text',
                    'title'       => __('Custom link url', 'text_doma'),
                    'default'     => ''
                ),
                'link-target' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Open link in a new browser tab', 'text_doma'),
                    'default'     => false,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                ),
                'h_alignment_css' => array(
                    'input_type'  => 'horizAlignmentWithDeviceSwitcher',
                    'title'       => __('Alignment', 'text_doma'),
                    'default'     => array( 'desktop' => 'center' ),
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'h_alignment',
                    'title_width' => 'width-100',
                    'width-100'   => true,
                    'css_selectors'=> 'figure'
                ),
                'use_custom_title_attr' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Set the text displayed when the mouse is held over', 'text_doma'),
                    'default'     => false,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'notice_after' => __('If not specified, Nimble will use by order of priority the caption, the description, and the image title. Those properties can be edited for each image in the media library.')
                ),
                'heading_title' => array(
                    'input_type'         => 'text',
                    'title' => __('Custom text displayed on mouse hover', 'text_domain_to' ),
                    'default'            => '',
                    'title_width' => 'width-100',
                    'width-100'         => true
                ),
                'use_custom_width' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __( 'Custom image width', 'text_doma' ),
                    'default'     => 0,
                    'refresh_stylesheet' => true,
                    'html_before' => '<hr/>'
                ),
                'custom_width' => array(
                    'input_type'  => 'range_with_unit_picker_device_switcher',
                    'title'       => __('Width', 'text_doma'),
                    'min' => 1,
                    'max' => 100,
                    //'unit' => '%',
                    'default'     => array( 'desktop' => '100%' ),
                    'max'     => 500,
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true
                ),
                'use_custom_height' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __( 'Custom image max height', 'text_doma' ),
                    'default'     => 0,
                    'refresh_stylesheet' => true
                ),
                'custom_height' => array(
                    'input_type'  => 'range_with_unit_picker_device_switcher',
                    'title'       => __('Height', 'text_doma'),
                    'min' => 1,
                    'max' => 100,
                    //'unit' => '%',
                    'default'     => array( 'desktop' => '100%' ),
                    'max'     => 500,
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true
                ),
                'use_box_shadow' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __( 'Apply a shadow', 'text_doma' ),
                    'default'     => 0,
                    'html_before' => '<hr/>'
                ),
                'img_hover_effect' => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __('Mouse over effect', 'text_doma'),
                    'default'     => 'none',
                    'choices'     => sek_get_select_options_for_input_id( 'img_hover_effect' ),
                    'html_after' => $pro_text
                )
            )
        ),
        'render_tmpl_path' => '',
    );
}






/* ------------------------------------------------------------------------- *
 *  IMAGE BORDERS AND BORDER RADIUS
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_image_borders_corners_child() {
    $css_selectors = '.sek-module-inner img';
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_image_borders_corners_child',
        'name' => __( 'Borders and corners', 'text_doma' ),
        //'sanitize_callback' => '\Nimble\sanitize_callback__czr_simple_form_module',
        // 'starting_value' => array(
        //     'button_text' => __('Click me','text_doma'),
        //     'color_css'  => '#ffffff',
        //     'bg_color_css' => '#020202',
        //     'bg_color_hover' => '#151515', //lighten 15%,
        //     'use_custom_bg_color_on_hover' => 0,
        //     'border_radius_css' => '2',
        //     'h_alignment_css' => 'center',
        //     'use_box_shadow' => 1,
        //     'push_effect' => 1
        // ),
        //'css_selectors' => array( '.sek-module-inner .sek-simple-form-wrapper' ),
        'tmpl' => array(
            'item-inputs' => array(
                'border-type' => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __('Border', 'text_doma'),
                    'default' => 'none',
                    'choices'     => sek_get_select_options_for_input_id( 'border-type' ),
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true
                ),
                'borders' => array(
                    'input_type'  => 'borders',
                    'title'       => __('Borders', 'text_doma'),
                    'min' => 0,
                    'max' => 100,
                    'default' => array(
                        '_all_' => array( 'wght' => '1px', 'col' => '#000000' )
                    ),
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'css_selectors'=> $css_selectors
                ),
                'border_radius_css'       => array(
                    'input_type'  => 'border_radius',
                    'title'       => __( 'Rounded corners', 'text_doma' ),
                    'default' => array( '_all_' => '0px' ),
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'min'         => 0,
                    'max'         => 500,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'border_radius',
                    'css_selectors'=> $css_selectors
                ),
            )
        ),
        'render_tmpl_path' => '',
    );
}








/* ------------------------------------------------------------------------- *
 *  SCHEDULE CSS RULES FILTERING
/* ------------------------------------------------------------------------- */
add_filter( 'sek_add_css_rules_for_module_type___czr_image_module', '\Nimble\sek_add_css_rules_for_czr_image_module', 10, 2 );
// filter documented in Sek_Dyn_CSS_Builder::sek_css_rules_sniffer_walker
// Note : $complete_modul_model has been normalized
// @return populated $rules
function sek_add_css_rules_for_czr_image_module( $rules, $complete_modul_model ) {
    if ( empty( $complete_modul_model['value'] ) )
      return $rules;

    $value = $complete_modul_model['value'];
    $main_settings = $complete_modul_model['value']['main_settings'];
    $borders_corners_settings = $complete_modul_model['value']['borders_corners'];

    // WIDTH
    if ( sek_booleanize_checkbox_val( $main_settings['use_custom_width'] ) ) {
        $width = $main_settings[ 'custom_width' ];
        $css_rules = '';
        if ( isset( $width ) && FALSE !== $width ) {
            $numeric = sek_extract_numeric_value( $width );
            if ( !empty( $numeric ) ) {
                $unit = sek_extract_unit( $width );
                $css_rules .= 'width:' . $numeric . $unit . ';';
            }
            // same treatment as in sek_add_css_rules_for_css_sniffed_input_id() => 'width'
            if ( is_string( $width ) ) {
                  $numeric = sek_extract_numeric_value($width);
                  if ( !empty( $numeric ) ) {
                      $unit = sek_extract_unit( $width );
                      $css_rules .= 'width:' . $numeric . $unit . ';';
                  }
            } else if ( is_array( $width ) ) {
                  $width = wp_parse_args( $width, array(
                      'desktop' => '100%',
                      'tablet' => '',
                      'mobile' => ''
                  ));
                  // replace % by vh when needed
                  $ready_value = $width;
                  foreach ($width as $device => $num_unit ) {
                      $numeric = sek_extract_numeric_value( $num_unit );
                      if ( !empty( $numeric ) ) {
                          $unit = sek_extract_unit( $num_unit );
                          $ready_value[$device] = $numeric . $unit;
                      }
                  }

                  $rules = sek_set_mq_css_rules(array(
                      'value' => $ready_value,
                      'css_property' => 'width',
                      'selector' => '[data-sek-id="'.$complete_modul_model['id'].'"] .sek-module-inner img',
                      'is_important' => false,
                      'level_id' => $complete_modul_model['id']
                  ), $rules );

                  // to fix https://github.com/presscustomizr/nimble-builder/issues/754
                  $rules = sek_set_mq_css_rules(array(
                      'value' => $ready_value,
                      'css_property' => 'max-width',
                      'selector' => '[data-sek-id="'.$complete_modul_model['id'].'"] .sek-module-inner img',
                      'is_important' => false,
                      'level_id' => $complete_modul_model['id']
                  ), $rules );
            }
        }//if


        if ( !empty( $css_rules ) ) {
            $rules[] = array(
                'selector' => '[data-sek-id="'.$complete_modul_model['id'].'"] .sek-module-inner img',
                'css_rules' => $css_rules,
                'mq' =>null
            );
        }
    }// Width


    // HEIGHT
    if ( sek_booleanize_checkbox_val( $main_settings['use_custom_height'] ) ) {
        $height = $main_settings[ 'custom_height' ];
        $css_rules = '';
        if ( isset( $height ) && FALSE !== $height ) {
            $numeric = sek_extract_numeric_value( $height );
            if ( !empty( $numeric ) ) {
                $unit = sek_extract_unit( $height );
                $css_rules .= 'max-height:' . $numeric . $unit . ';';
            }
            // same treatment as in sek_add_css_rules_for_css_sniffed_input_id() => 'width'
            if ( is_string( $height ) ) {
                  $numeric = sek_extract_numeric_value($height);
                  if ( !empty( $numeric ) ) {
                      $unit = sek_extract_unit( $height );
                      $css_rules .= 'max-height:' . $numeric . $unit . ';';
                  }
            } else if ( is_array( $height ) ) {
                  $height = wp_parse_args( $height, array(
                      'desktop' => '100%',
                      'tablet' => '',
                      'mobile' => ''
                  ));
                  // replace % by vh when needed
                  $ready_value = $height;
                  foreach ($height as $device => $num_unit ) {
                      $numeric = sek_extract_numeric_value( $num_unit );
                      if ( !empty( $numeric ) ) {
                          $unit = sek_extract_unit( $num_unit );
                          $ready_value[$device] = $numeric . $unit;
                      }
                  }

                  $rules = sek_set_mq_css_rules(array(
                      'value' => $ready_value,
                      'css_property' => 'max-height',
                      'selector' => '[data-sek-id="'.$complete_modul_model['id'].'"] .sek-module-inner figure',
                      'is_important' => false,
                      'level_id' => $complete_modul_model['id']
                  ), $rules );
            }
        }//if


        if ( !empty( $css_rules ) ) {
            $rules[] = array(
                'selector' => '[data-sek-id="'.$complete_modul_model['id'].'"] .sek-module-inner img',
                'css_rules' => $css_rules,
                'mq' =>null
            );
        }
    }// height


    // BORDERS
    $border_settings = $borders_corners_settings[ 'borders' ];
    $border_type = $borders_corners_settings[ 'border-type' ];
    $has_border_settings  = 'none' != $border_type && !empty( $border_type );

    //border width + type + color
    if ( $has_border_settings ) {
        $rules = sek_generate_css_rules_for_multidimensional_border_options(
            $rules,
            $border_settings,
            $border_type,
            '[data-sek-id="'.$complete_modul_model['id'].'"] .sek-module-inner img'
        );
    }

    return $rules;
}
?><?php
/* ------------------------------------------------------------------------- *
 *  LOAD AND REGISTER SOCIAL ICONS MODULE
/* ------------------------------------------------------------------------- */
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_czr_social_icons_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_social_icons_module',
        'is_father' => true,
        'children' => array(
            'icons_collection' => 'czr_social_icons_settings_child',
            'icons_style' => 'czr_social_icons_style_child'
        ),
        'name' => __('Social Icons', 'text_doma'),
        'starting_value' => array(
            'icons_collection' => array(
                array( 'icon' => 'fab fa-facebook', 'color_css' => '#3b5998' ),
                array( 'icon' => 'fab fa-twitter', 'color_css' => '#1da1f2' ),
                array( 'icon' => 'fab fa-instagram', 'color_css' => '#262626' )
            )
        ),
        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'css_selectors' => array( '.sek-social-icons-wrapper' ),//array( '.sek-icon i' ),
        'render_tmpl_path' => "social_icons_tmpl.php",
        // Nimble will "sniff" if we need font awesome
        // No need to enqueue font awesome here
        // 'front_assets' => array(
        //       'czr-font-awesome' => array(
        //           'type' => 'css',
        //           //'handle' => 'czr-font-awesome',
        //           'src' => NIMBLE_BASE_URL . '/assets/front/fonts/css/fontawesome-all.min.css'
        //           //'deps' => array()
        //       )
        // )
    );
}


/* ------------------------------------------------------------------------- *
 *  MAIN SETTINGS
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_social_icons_settings_child() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_social_icons_settings_child',
        'is_crud' => true,
        'name' => __( 'Icon collection', 'text_doma' ),
        //'sanitize_callback' => '\Nimble\sanitize_callback__czr_simple_form_module',
        'css_selectors' => array( '.sek-social-icon' ),//array( '.sek-icon i' ),
        'tmpl' => array(
            'pre-item' => array(
                // 'page-id' => array(
                //     'input_type'  => 'content_picker',
                //     'title'       => __('Pick a page', 'text_doma')
                // ),
                'icon' => array(
                    'input_type'  => 'fa_icon_picker',
                    'title'       => __('Select an icon', 'text_doma')
                ),
                'link'  => array(
                    'input_type'  => 'text',
                    'title'       => __('Social link url', 'text_doma'),
                    'notice_after'      => __('Enter the full url of your social profile (must be valid url).', 'text_doma'),
                    'placeholder' => __('http://...,mailto:...,...', 'text_doma')
                )
            ),
            'item-inputs' => array(
                'icon' => array(
                    'input_type'  => 'fa_icon_picker',
                    'title'       => __('Select an icon', 'text_doma')
                ),
                'link'  => array(
                    'input_type'  => 'text',
                    'default'     => '',
                    'title'       => __('Social link url', 'text_doma'),
                    'notice_after'      => __('Enter the full url of your social profile (must be valid url).', 'text_doma'),
                    'placeholder' => __('http://...,mailto:...,...', 'text_doma')
                ),
                'title_attr'  => array(
                    'input_type'  => 'text',
                    'default'     => '',
                    'title'       => __('Title', 'text_domain_to_be_replaced'),
                    'notice_after'      => __('This is the text displayed on mouse over.', 'text_domain_to_be_replaced'),
                ),
                'link_target' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Open link in a new browser tab', 'text_doma'),
                    'default'     => false,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                ),
                'color_css' => array(
                    'input_type'  => 'wp_color_alpha',
                    'title'       => __('Color', 'text_doma'),
                    'width-100'   => true,
                    'default'    => '#707070',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'color'
                ),
                'use_custom_color_on_hover' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __( 'Set a custom icon color on mouse hover', 'text_doma' ),
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'default'     => false,
                ),
                'social_color_hover' => array(
                    'input_type'  => 'wp_color_alpha',
                    'title'       => __('Hover color', 'text_doma'),
                    'width-100'   => true,
                    'default'    => '#969696',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    //'css_identifier' => 'color_hover'
                )
            )
        ),
        'render_tmpl_path' => '',
    );
}


/* ------------------------------------------------------------------------- *
 *  SOCIAL ICONS STYLING
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_social_icons_style_child() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_social_icons_style_child',
        'name' => __( 'Design options : size, spacing, alignment,...', 'text_doma' ),
        //'sanitize_callback' => '\Nimble\sanitize_callback__czr_simple_form_module',
        // 'starting_value' => array(
        //     'button_text' => __('Click me','text_doma'),
        //     'color_css'  => '#ffffff',
        //     'bg_color_css' => '#020202',
        //     'bg_color_hover' => '#151515', //lighten 15%,
        //     'use_custom_bg_color_on_hover' => 0,
        //     'border_radius_css' => '2',
        //     'h_alignment_css' => 'center',
        //     'use_box_shadow' => 1,
        //     'push_effect' => 1
        // ),
        'css_selectors' => array( '.sek-social-icons-wrapper' ),//array( '.sek-icon i' ),
        'tmpl' => array(
            'item-inputs' => array(
                'font_size_css'       => array(
                    'input_type'  => 'range_with_unit_picker_device_switcher',
                    'title'       => __( 'Icons size', 'text_doma' ),
                    // the default value is commented to fix https://github.com/presscustomizr/nimble-builder/issues/313
                    // => as a consequence, when a module uses the font child module, the default font-size rule must be defined in the module SCSS file.
                    //'default'     => array( 'desktop' => '16px' ),
                    'min' => 0,
                    'max' => 100,
                    'title_width' => 'width-100',
                    'width-100'         => true,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'font_size',
                    'css_selectors'      => '.sek-module-inner .sek-social-icons-wrapper > li .sek-social-icon',
                ),//16,//"14px",
                'line_height_css'     => array(
                    'input_type'  => 'range_with_unit_picker',
                    'title'       => __( 'Line height', 'text_doma' ),
                    'default'     => '1.5em',
                    'min' => 0,
                    'max' => 10,
                    'step' => 0.1,
                    'width-100'         => true,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'line_height',
                    'css_selectors'      => '.sek-module-inner .sek-social-icons-wrapper > li .sek-social-icon',
                ),//24,//"20px",
                'h_alignment_css'        => array(
                    'input_type'  => 'horizAlignmentWithDeviceSwitcher',
                    'title'              => __( 'Horizontal alignment', 'text_doma' ),
                    'default'     => array( 'desktop' => 'center' ),//consistent with SCSS
                    'refresh_markup'     => false,
                    'refresh_stylesheet' => true,
                    'css_identifier'     => 'h_alignment',
                    //'css_selectors'      => '.sek-module-inner',
                    'title_width' => 'width-100',
                    'width-100'   => true,
                ),
                'space_between_icons' => array(
                    'input_type'  => 'range_with_unit_picker_device_switcher',
                    'title'       => __('Space between icons', 'text_doma'),
                    'min' => 1,
                    'max' => 100,
                    //'unit' => 'px',
                    'default' => array( 'desktop' => '8px' ),
                    'width-100'   => true,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'title_width' => 'width-100'
                ),
                'spacing_css'     => array(
                    'input_type'  => 'spacingWithDeviceSwitcher',
                    'title'       => __( 'Spacing of the icons wrapper', 'text_doma' ),
                    'default'     => array('desktop' => array('margin-bottom' => '10', 'margin-top' => '10', 'unit' => 'px')),//consistent with SCSS
                    'title_width' => 'width-100',
                    'width-100'   => true,
                    'refresh_markup'     => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'spacing_with_device_switcher',
                    'css_selectors'      => '.sek-module-inner .sek-social-icons-wrapper',
                    // 'css_selectors'=> '.sek-icon i'
                )
            )
        ),
        'render_tmpl_path' => '',
    );
}

/* ------------------------------------------------------------------------- *
 *  SCHEDULE CSS RULES FILTERING
/* ------------------------------------------------------------------------- */
// PER ITEM CSS DESIGN => FILTERING OF EACH ITEM MODEL, TARGETING THE ID ( [data-sek-item-id="893af157d5e3"] )
add_filter( 'sek_add_css_rules_for_single_item_in_module_type___czr_social_icons_settings_child', '\Nimble\sek_add_css_rules_for_items_in_czr_social_icons_settings_child', 10, 2 );
// filter documented in Sek_Dyn_CSS_Builder::sek_css_rules_sniffer_walker
// Note : $complete_modul_model has been normalized
// @return populated $rules
// @param $params
// Array
// (
//     [input_list] => Array
//         (
//             [icon] => fab fa-acquisitions-incorporated
//             [link] => https://twitter.com/home
//             [title_attr] => Follow me on twitter
//             [link_target] =>
//             [color_css] => #dd9933
//             [use_custom_color_on_hover] =>
//             [social_color_hover] => #dd3333
//             [id] => 62316ab99b4d
//         )
//     [parent_module_id] =>
//     [module_type] => czr_social_icons_settings_child
//     [module_css_selector] => Array
//         (
//             [0] => .sek-social-icon
//         )

// )
function sek_add_css_rules_for_items_in_czr_social_icons_settings_child( $rules, $params ) {
    //sek_error_log('SOCIAL ITEMS PARAMS?', $params );

    // $item_input_list = wp_parse_args( $item_input_list, $default_value_model );
    $item_model = isset( $params['input_list'] ) ? $params['input_list'] : array();

    // COLOR ON HOVER
    $icon_color = $item_model['color_css'];
    if ( sek_booleanize_checkbox_val( $item_model['use_custom_color_on_hover'] ) ) {
        $color_hover = $item_model['social_color_hover'];
    } else {
        // Build the lighter rgb from the user picked bg color
        if ( 0 === strpos( $icon_color, 'rgba' ) ) {
            list( $rgb, $alpha ) = sek_rgba2rgb_a( $icon_color );
            $color_hover_rgb  = sek_lighten_rgb( $rgb, $percent=15, $array = true );
            $color_hover      = sek_rgb2rgba( $color_hover_rgb, $alpha, $array = false, $make_prop_value = true );
        } else if ( 0 === strpos( $icon_color, 'rgb' ) ) {
            $color_hover      = sek_lighten_rgb( $icon_color, $percent=15 );
        } else {
            $color_hover      = sek_lighten_hex( $icon_color, $percent=15 );
        }
    }
    $color_hover_selector = sprintf( '[data-sek-id="%1$s"]  [data-sek-item-id="%2$s"] .sek-social-icon:hover', $params['parent_module_id'], $item_model['id'] );
    $rules[] = array(
        'selector' => $color_hover_selector,
        'css_rules' => 'color:' . $color_hover . ';',
        'mq' =>null
    );
    return $rules;
}

// GLOBAL CSS DESIGN => FILTERING OF THE ENTIRE MODULE MODEL
add_filter( 'sek_add_css_rules_for_module_type___czr_social_icons_module', '\Nimble\sek_add_css_rules_for_czr_social_icons_module', 10, 2 );
// filter documented in Sek_Dyn_CSS_Builder::sek_css_rules_sniffer_walker
// Note : $complete_modul_model has been normalized
// @return populated $rules
function sek_add_css_rules_for_czr_social_icons_module( $rules, $complete_modul_model ) {
    if ( empty( $complete_modul_model['value'] ) || !is_array( $complete_modul_model['value'] ) )
      return $rules;

    $value = $complete_modul_model['value'];
    $icons_style = $value['icons_style'];

    // HORIZONTAL SPACE BETWEEN ICONS
    $padding_right = $icons_style['space_between_icons'];
    $padding_right = is_array( $padding_right ) ? $padding_right : array();
    $defaults = array(
        'desktop' => '15px',// <= this value matches the static CSS rule and the input default for the module
        'tablet' => '',
        'mobile' => ''
    );
    $padding_right = wp_parse_args( $padding_right, $defaults );
    $padding_right_ready_val = $padding_right;
    foreach ($padding_right as $device => $num_unit ) {
        $num_val = sek_extract_numeric_value( $num_unit );
        $padding_right_ready_val[$device] = '';
        // Leave the device value empty if === to default
        // Otherwise it will print a duplicated dynamic css rules, already hardcoded in the static stylesheet
        // fixes https://github.com/presscustomizr/nimble-builder/issues/419
        if ( !empty( $num_unit ) && $num_val.'px' !== $defaults[$device].'' ) {
            $unit = sek_extract_unit( $num_unit );
            $num_val = $num_val < 0 ? 0 : $num_val;
            $padding_right_ready_val[$device] = $num_val . $unit;
        }
    }
    $rules = sek_set_mq_css_rules( array(
        'value' => $padding_right_ready_val,
        'css_property' => 'padding-right',
        'selector' => implode(',', array(
            '[data-sek-id="'.$complete_modul_model['id'].'"] .sek-module-inner .sek-social-icons-wrapper > *:not(:last-child)',
        )),
        'is_important' => false,
        'level_id' => $complete_modul_model['id']
    ), $rules );

    return $rules;
}
?><?php
/* ------------------------------------------------------------------------- *
 *  TEXT EDITOR FATHER MODULE
/* ------------------------------------------------------------------------- */
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_czr_heading_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_heading_module',
        'is_father' => true,
        'children' => array(
            'main_settings'   => 'czr_heading_child',
            'font_settings' => 'czr_font_child',
            'spacing' => 'czr_heading_spacing_child'
        ),
        'name' => __('Heading', 'text_doma'),
        'starting_value' => array(
            'main_settings' => array(
                'heading_text' => 'This is a heading.'
            )
        ),
        'css_selectors' => array( '.sek-module-inner > .sek-heading' ),
        'sanitize_callback' => '\Nimble\sek_sanitize_czr_heading_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'render_tmpl_path' => "heading_module_tmpl.php",
        'placeholder_icon' => 'short_text'
    );
}


/* ------------------------------------------------------------------------- *
 *  TEXT EDITOR CONTENT CHILD
/* ------------------------------------------------------------------------- */
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_czr_heading_child() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_heading_child',
        'name' => __('Content', 'text_doma'),
        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'tmpl' => array(
            'item-inputs' => array(
                'heading_text' => array(
                    'input_type'        => 'nimble_tinymce_editor',
                    'editor_params'     => array(
                        'media_button' => false,
                        'includedBtns' => 'basic_btns',
                        'height' => 50
                    ),
                    'title'              => __( 'Heading text', 'text_doma' ),
                    'default'            => '',
                    'width-100'         => true,
                    'refresh_markup'    => '.sek-heading [data-sek-input-type="textarea"]'
                    //'notice_before'      => __( 'You may use some html tags like a, br, span with attributes like style, id, class ...', 'text_doma'),
                ),
                'heading_tag' => array(
                    'input_type'         => 'simpleselect',
                    'title'              => __( 'Heading tag', 'text_doma' ),
                    'default'            => 'h1',
                    'choices'            => sek_get_select_options_for_input_id( 'heading_tag' )
                ),
                'h_alignment_css' => array(
                    'input_type'  => 'horizTextAlignmentWithDeviceSwitcher',
                    'title'       => __('Alignment', 'text_doma'),
                    'default'     => array( 'desktop' => 'center'),
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'h_alignment',
                    'title_width' => 'width-100',
                    'width-100'   => true,
                ),
                'heading_title' => array(
                    'input_type'         => 'text',
                    'title' => __('Display a tooltip text when the mouse is held over', 'text_domain_to' ),
                    'default'            => '',
                    'title_width' => 'width-100',
                    'width-100'         => true,
                    'notice_after' => __('Not previewable during customization', 'text_domain_to')
                ),
                'link-to' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Turn into a link', 'text_doma'),
                    'default'     => false,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20'
                ),
                'link-pick-url' => array(
                    'input_type'  => 'content_picker',
                    'title'       => __('Link url', 'text_doma'),
                    'default'     => array()
                ),
                'link-custom-url' => array(
                    'input_type'  => 'text',
                    'title'       => __('Custom link url', 'text_doma'),
                    'default'     => ''
                ),
                'link-target' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Open link in a new browser tab', 'text_doma'),
                    'default'     => false,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                )
            )
        ),
        'render_tmpl_path' =>'',
    );
}


/* ------------------------------------------------------------------------- *
 *  SANITIZATION
/* ------------------------------------------------------------------------- */
function sek_sanitize_czr_heading_module( $content ) {
    if ( is_array($content) && is_array($content['main_settings']) ) {
        // main heading text
        if ( !empty($content['main_settings']['heading_text']) ) {
            // https://wordpress.org/support/article/roles-and-capabilities/#unfiltered_html
            if ( !current_user_can( 'unfiltered_html' ) ) {
                $value['main_settings'][ 'heading_text' ] = wp_kses_post( $content['main_settings']['heading_text'] );
            }
            // convert into a json to prevent emoji breaking global json data structure
            // fix for https://github.com/presscustomizr/nimble-builder/issues/544
            $content['main_settings']['heading_text'] = sek_maybe_encode_richtext($content['main_settings']['heading_text']);
        }
        if ( !empty($content['main_settings']['heading_title']) ) {
            $content['main_settings']['heading_title'] = sek_maybe_encode_richtext($content['main_settings']['heading_title']);
        }
    }
    return $content;
}


/* ------------------------------------------------------------------------- *
 *  HEADING SPACING CHILD
/* ------------------------------------------------------------------------- */
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_czr_heading_spacing_child() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_heading_spacing_child',
        'name' => __('Spacing', 'text_doma'),
        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'tmpl' => array(
            'item-inputs' => array(
                'spacing_css'     => array(
                    'input_type'  => 'spacingWithDeviceSwitcher',
                    'title'       => __( 'Margin and padding', 'text_doma' ),
                    'default'     => array('desktop' => array('margin-bottom' => '0.6', 'margin-top' => '0.6', 'unit' => 'em')),
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'refresh_markup'     => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'spacing_with_device_switcher',
                    //'css_selectors'=> ''
                )
            )
        ),
        'render_tmpl_path' =>'',
    );
}

?>
<?php
/* ------------------------------------------------------------------------- *
 *  LOAD AND REGISTER SPACER MODULE
/* ------------------------------------------------------------------------- */
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );


function sek_get_module_params_for_czr_spacer_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_spacer_module',
        'name' => __('Spacer', 'text_doma'),
        'css_selectors' => array( '.sek-module-inner > *' ),
        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'tmpl' => array(
            'item-inputs' => array(
                'height_css' => array(
                    'input_type'  => 'range_with_unit_picker_device_switcher',
                    'min'         => 0,
                    'max'         => 100,
                    'step'        => 1,
                    'title'       => __('Space', 'text_doma'),
                    'default'     => array( 'desktop' => '20px' ),
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_selectors' => array( '.sek-spacer' ),
                    'css_identifier' => 'height'
                ),
            )
        ),
        'render_tmpl_path' => "spacer_module_tmpl.php",
    );
}
?><?php
/* ------------------------------------------------------------------------- *
 *  LOAD AND REGISTER DIVIDER MODULE
/* ------------------------------------------------------------------------- */
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );

function sek_get_module_params_for_czr_divider_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_divider_module',
        'name' => __('Divider', 'text_doma'),
        'css_selectors' => array( '.sek-divider' ),
        'tmpl' => array(
            'item-inputs' => array(
                'border_top_width_css' => array(
                    'input_type'  => 'range_with_unit_picker',
                    'title'       => __('Weight', 'text_doma'),
                    'min' => 1,
                    'max' => 50,
                    //'unit' => 'px',
                    'default' => '1px',
                    'width-100'   => true,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'border_top_width'
                ),
                'border_top_style_css' => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __('Style', 'text_doma'),
                    'default' => 'solid',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'border_top_style',
                    'choices'    => sek_get_select_options_for_input_id( 'border-type' )
                ),
                'border_top_color_css' => array(
                    'input_type'  => 'wp_color_alpha',
                    'title'       => __('Color', 'text_doma'),
                    'width-100'   => true,
                    'default'    => '#5a5a5a',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'border_top_color'
                ),
                'width_css' => array(
                    'input_type'  => 'range_with_unit_picker',
                    'title'       => __('Width', 'text_doma'),
                    'min' => 1,
                    'max' => 100,
                    //'unit' => '%',
                    'default' => '100%',
                    'width-100'   => true,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'width'
                ),
                'border_radius_css'       => array(
                    'input_type'  => 'border_radius',
                    'title'       => __( 'Rounded corners', 'text_doma' ),
                    'default' => array( '_all_' => '0px' ),
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'min'         => 0,
                    'max'         => 500,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'border_radius'
                    //'css_selectors'=> ''
                ),
                'h_alignment_css' => array(
                    'input_type'  => 'horizAlignmentWithDeviceSwitcher',
                    'title'       => __('Alignment', 'text_doma'),
                    'default'     => array( 'desktop' => 'center' ),
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_selectors' => '.sek-module-inner',
                    'css_identifier' => 'h_alignment',
                    'title_width' => 'width-100',
                    'width-100'   => true,
                ),
                'v_spacing_css' => array(
                    'input_type'  => 'range_with_unit_picker_device_switcher',
                    'title'       => __('Space before and after', 'text_doma'),
                    'min'         => 1,
                    'max'         => 100,
                    'default'     => array( 'desktop' => '15px' ),
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'v_spacing'
                ),
            )
        ),
        'render_tmpl_path' => "divider_module_tmpl.php",
    );
}
?><?php
/* ------------------------------------------------------------------------- *
 *  LOAD AND REGISTER ICON MODULE
/* ------------------------------------------------------------------------- */
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );

function sek_get_module_params_for_czr_icon_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_icon_module',
        'is_father' => true,
        'children' => array(
            'icon_settings' => 'czr_icon_settings_child',
            'spacing_border' => 'czr_icon_spacing_border_child'
        ),
        'name' => __('Icon', 'text_doma'),
        'starting_value' => array(
            'icon_settings' => array(
                'icon' =>  'far fa-star',
                'font_size_css' => '40px',
                'color_css' => '#707070',
                'color_hover' => '#969696'
            )
        ),
        // 'sanitize_callback' => '\Nimble\sanitize_callback__czr_icon_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'css_selectors' => array( '.sek-icon-wrapper' ),//array( '.sek-icon i' ),
        'render_tmpl_path' => "icon_module_tmpl.php",
        // Nimble will "sniff" if we need font awesome
        // 'front_assets' => array(
        //       'czr-font-awesome' => array(
        //           'type' => 'css',
        //           //'handle' => 'czr-font-awesome',
        //           'src' => NIMBLE_BASE_URL . '/assets/front/fonts/css/fontawesome-all.min.css'
        //           //'deps' => array()
        //       )
        // )
    );
}





/* ------------------------------------------------------------------------- *
 *  MAIN ICON SETTINGS : ICON, SIZE, COLOR, LINK
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_icon_settings_child() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_icon_settings_child',
        'name' => __( 'Icon settings', 'text_doma' ),
        //'sanitize_callback' => '\Nimble\sanitize_callback__czr_simple_form_module',
        // 'starting_value' => array(
        //     'button_text' => __('Click me','text_doma'),
        //     'color_css'  => '#ffffff',
        //     'bg_color_css' => '#020202',
        //     'bg_color_hover' => '#151515', //lighten 15%,
        //     'use_custom_bg_color_on_hover' => 0,
        //     'border_radius_css' => '2',
        //     'h_alignment_css' => 'center',
        //     'use_box_shadow' => 1,
        //     'push_effect' => 1
        // ),
        'css_selectors' => array( '.sek-icon-wrapper' ),//array( '.sek-icon i' ),
        'tmpl' => array(
            'item-inputs' => array(
                'icon' => array(
                    'input_type'  => 'fa_icon_picker',
                    'title'       => __('Select an Icon', 'text_doma'),
                    //'default'     => 'no-link'
                ),
                'link-to' => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __('Link to', 'text_doma'),
                    'default'     => 'no-link',
                    'choices'     => sek_get_select_options_for_input_id( 'link-to' )
                ),
                'link-pick-url' => array(
                    'input_type'  => 'content_picker',
                    'title'       => __('Link url', 'text_doma'),
                    'default'     => array()
                ),
                'link-custom-url' => array(
                    'input_type'  => 'text',
                    'title'       => __('Custom link url', 'text_doma'),
                    'default'     => ''
                ),
                'link-target' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Open link in a new browser tab', 'text_doma'),
                    'default'     => false,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                ),
                'font_size_css' => array(
                    'input_type'  => 'range_with_unit_picker_device_switcher',
                    'title'       => __('Size', 'text_doma'),
                    'default'     => array( 'desktop' => '16px' ),
                    'min' => 0,
                    'max' => 100,
                    'title_width' => 'width-100',
                    'width-100'       => true,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'font_size'
                ),
                'h_alignment_css' => array(
                    'input_type'  => 'horizAlignmentWithDeviceSwitcher',
                    'title'       => __('Alignment', 'text_doma'),
                    'default'     => array( 'desktop' => 'center' ),
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'h_alignment',
                    'css_selectors' => '.sek-icon',
                    'title_width' => 'width-100',
                    'width-100'   => true,
                ),
                'color_css' => array(
                    'input_type'  => 'wp_color_alpha',
                    'title'       => __('Color', 'text_doma'),
                    'width-100'   => true,
                    'default'    => '#707070',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'color'
                ),
                'use_custom_color_on_hover' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __( 'Set a custom icon color on mouse hover', 'text_doma' ),
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'default'     => 0,
                ),
                'color_hover' => array(
                    'input_type'  => 'wp_color_alpha',
                    'title'       => __('Hover color', 'text_doma'),
                    'width-100'   => true,
                    'default'    => '#969696',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    //'css_identifier' => 'color_hover'
                )
            )
        ),
        'render_tmpl_path' => '',
    );
}








/* ------------------------------------------------------------------------- *
 *  ICON SPACING BORDER SHADOW
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_icon_spacing_border_child() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_icon_spacing_border_child',
        'name' => __( 'Icon options for background, spacing, border, shadow', 'text_doma' ),
        //'sanitize_callback' => '\Nimble\sanitize_callback__czr_simple_form_module',
        // 'starting_value' => array(
        //     'button_text' => __('Click me','text_doma'),
        //     'color_css'  => '#ffffff',
        //     'bg_color_css' => '#020202',
        //     'bg_color_hover' => '#151515', //lighten 15%,
        //     'use_custom_bg_color_on_hover' => 0,
        //     'border_radius_css' => '2',
        //     'h_alignment_css' => 'center',
        //     'use_box_shadow' => 1,
        //     'push_effect' => 1
        // ),
        'css_selectors' => array( '.sek-icon-wrapper' ),//array( '.sek-icon i' ),
        'tmpl' => array(
            'item-inputs' => array(
                'spacing_css'     => array(
                    'input_type'  => 'spacingWithDeviceSwitcher',
                    'title'       => __( 'Spacing', 'text_doma' ),
                    'default'     => array( 'desktop' => array() ),
                    'width-100'   => true,
                    'refresh_markup'     => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'spacing_with_device_switcher',
                    // 'css_selectors'=> '.sek-icon i'
                ),
                'bg_color_css' => array(
                    'input_type'  => 'wp_color_alpha',
                    'title'       => __( 'Background color', 'text_doma' ),
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'default'    => '',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'background_color',
                    // 'css_selectors'=> '.sek-icon i'
                ),
                'border-type' => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __('Border', 'text_doma'),
                    'default' => 'none',
                    'choices'     => sek_get_select_options_for_input_id( 'border-type' ),
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true
                ),
                'borders' => array(
                    'input_type'  => 'borders',
                    'title'       => __('Borders', 'text_doma'),
                    'min' => 0,
                    'max' => 100,
                    'default' => array(
                        '_all_' => array( 'wght' => '1px', 'col' => '#000000' )
                    ),
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    // 'css_selectors'=> '.sek-icon i'
                ),
                'border_radius_css'       => array(
                    'input_type'  => 'border_radius',
                    'title'       => __( 'Rounded corners', 'text_doma' ),
                    'default' => array( '_all_' => '0px' ),
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'min'         => 0,
                    'max'         => 500,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'border_radius',
                    // 'css_selectors'=> '.sek-icon i'
                ),
                'use_box_shadow' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __( 'Apply a shadow', 'text_doma' ),
                    'default'     => 0,
                ),
            )
        ),
        'render_tmpl_path' => '',
    );
}











/* ------------------------------------------------------------------------- *
 *  SCHEDULE CSS RULES FILTERING
/* ------------------------------------------------------------------------- */
add_filter( 'sek_add_css_rules_for_module_type___czr_icon_module', '\Nimble\sek_add_css_rules_for_icon_front_module', 10, 2 );
// filter documented in Sek_Dyn_CSS_Builder::sek_css_rules_sniffer_walker
// Note : $complete_modul_model has been normalized
// @return populated $rules
function sek_add_css_rules_for_icon_front_module( $rules, $complete_modul_model ) {
    if ( empty( $complete_modul_model['value'] ) )
      return $rules;

    $value = $complete_modul_model['value'];

    $icon_settings = $value['icon_settings'];

    // COLOR ON HOVER
    $icon_color = $icon_settings['color_css'];
    if ( sek_booleanize_checkbox_val( $icon_settings['use_custom_color_on_hover'] ) ) {
        $color_hover = $icon_settings['color_hover'];
        $rules[] = array(
            'selector' => '[data-sek-id="'.$complete_modul_model['id'].'"] .sek-icon i:hover',
            'css_rules' => 'color:' . $color_hover . ';',
            'mq' =>null
        );
    }

    // BORDERS
    $border_settings = $value[ 'spacing_border' ][ 'borders' ];
    $border_type = $value[ 'spacing_border' ][ 'border-type' ];
    $has_border_settings  = 'none' != $border_type && !empty( $border_type );

    //border width + type + color
    if ( $has_border_settings ) {
        $rules = sek_generate_css_rules_for_multidimensional_border_options(
            $rules,
            $border_settings,
            $border_type,
            '[data-sek-id="'.$complete_modul_model['id'].'"] .sek-icon-wrapper'
        );
    }

    return $rules;
}
?><?php
/* ------------------------------------------------------------------------- *
 *  LOAD AND REGISTER MAP MODULE
/* ------------------------------------------------------------------------- */
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );

function sek_get_module_params_for_czr_map_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_map_module',
        'name' => __('Map', 'text_doma'),
        // 'sanitize_callback' => '\Nimble\sanitize_callback__czr_gmap_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        //'css_selectors' => array( '.sek-module-inner' ),
        'starting_value' => array(
            'address'       => 'Nice, France',
            'zoom'          => 10,
            'height_css'    => '200px'
        ),
        'tmpl' => array(
            'item-inputs' => array(
                'address' => array(
                    'input_type'  => 'text',
                    'title'       => __( 'Address', 'text_doma'),
                    'width-100'   => true,
                    'default'    => '',
                ),
                'zoom' => array(
                    'input_type'  => 'range_simple',
                    'title'       => __( 'Zoom', 'text_doma' ),
                    'min' => 1,
                    'max' => 20,
                    'unit' => '',
                    'default' => 10,
                    'width-100'   => true
                ),
                'height_css' => array(
                    'input_type'  => 'range_with_unit_picker_device_switcher',
                    'title'       => __( 'Height', 'text_doma' ),
                    'min' => 1,
                    'max' => 600,
                    'default'     => array( 'desktop' => '200px' ),
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'css_selectors' => array( '.sek-embed::before' ),
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'height'
                ),
                'lazyload' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Lazy load', 'text_doma'),
                    'default'     => true,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'notice_after' => sprintf('%1$s <br/><strong>%2$s</strong>',
                        __( 'With the lazy load option enabled, Nimble loads the map when it becomes visible while scrolling. This improves your page load performances.', 'text_dom'),
                        __( 'If you use a cache plugin, make sure that this option does not conflict with your caching options.', 'text_dom')
                    ),
                )
            )
        ),
        'render_tmpl_path' => "map_module_tmpl.php",
    );
}
?><?php
/* ------------------------------------------------------------------------- *
 *  LOAD AND REGISTER QUOTE MODULE
/* ------------------------------------------------------------------------- */
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );

function sek_get_module_params_for_czr_quote_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_quote_module',
        'is_father' => true,
        'children' => array(
            'quote_content' => 'czr_quote_quote_child',
            'cite_content' => 'czr_quote_cite_child',
            'design' => 'czr_quote_design_child'
        ),
        'name' => __('Quote', 'text_doma' ),
        'sanitize_callback' => __NAMESPACE__ . '\sanitize_callback__czr_quote_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'starting_value' => array(
            'quote_content' => array(
                'quote_text'  => __('Hey, careful, man, there\'s a beverage here!','text_doma'),
            ),
            'cite_content' => array(
                'cite_text'   => sprintf( __('The Dude in %1s', 'text_doma'), '<a href="https://www.imdb.com/title/tt0118715/quotes/qt0464770" rel="nofollow noopener noreferrer" target="_blank">The Big Lebowski</a>' ),
                'cite_font_style_css' => 'italic',
            ),
            'design' => array(
                'quote_design' => 'border-before'
            )
        ),
        'css_selectors' => array( '.sek-module-inner' ),
        'render_tmpl_path' => "quote_module_tmpl.php",
        // Nimble will "sniff" if we need font awesome
        // No need to enqueue font awesome here
        // 'front_assets' => array(
        //       'czr-font-awesome' => array(
        //           'type' => 'css',
        //           'src' => NIMBLE_BASE_URL . '/assets/front/fonts/css/fontawesome-all.min.css'
        //       )
        // )
    );
}








/* ------------------------------------------------------------------------- *
 *  QUOTE CONTENT AND FONT
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_quote_quote_child() {
    $quote_font_selectors = array( '.sek-quote .sek-quote-content', '.sek-quote .sek-quote-content *');
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_quote_quote_child',
        'name' => __( 'Quote content', 'text_doma' ),
        //'sanitize_callback' => '\Nimble\sanitize_callback__czr_simple_form_module',
        //'css_selectors' =>'',
        'tmpl' => array(
            'item-inputs' => array(
                'quote_text' => array(
                    'input_type'        => 'nimble_tinymce_editor',
                    'editor_params'     => array(
                        'media_button' => false,
                        'includedBtns' => 'basic_btns',
                    ),
                    'title'             => __( 'Main quote content', 'text_doma' ),
                    'default'           => '',
                    'width-100'         => true,
                    //'notice_before'     => __( 'You may use some html tags like a, br,p, div, span with attributes like style, id, class ...', 'text_doma'),
                    'refresh_markup'    => '.sek-quote-content'
                ),
                'quote_font_family_css' => array(
                    'input_type'  => 'font_picker',
                    'title'       => __( 'Font family', 'text_doma' ),
                    'default'     => '',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'refresh_fonts' => true,
                    'css_identifier' => 'font_family',
                    'css_selectors' => $quote_font_selectors,
                    'html_after' => sprintf('<span class="czr-notice"><i class="far fa-lightbulb"></i> %1s => <a href="%2s" target="_blank" rel="noopener noreferrer">%2$s</a></span><hr/>',
                        __('Find inspiration'),
                        'https://fonts.google.com/?sort=popularity'
                    )
                ),
                'quote_font_size_css'       => array(
                    'input_type'  => 'range_with_unit_picker_device_switcher',
                    'title'       => __( 'Font size', 'text_doma' ),
                    'default'     => array( 'desktop' => '1.2em' ),
                    'min' => 0,
                    'max' => 100,
                    'width-100'         => true,
                    'title_width' => 'width-100',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'font_size',
                    'css_selectors' => $quote_font_selectors,
                ),//16,//"14px",
                'quote_line_height_css'     => array(
                    'input_type'  => 'range_with_unit_picker',
                    'title'       => __( 'Line height', 'text_doma' ),
                    'default'     => '1.5em',
                    'min' => 0,
                    'max' => 10,
                    'step' => 0.1,
                    'width-100'         => true,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'line_height',
                    'css_selectors' => $quote_font_selectors,
                ),//24,//"20px",
                'quote_color_css'           => array(
                    'input_type'  => 'wp_color_alpha',
                    'title'       => __( 'Text color', 'text_doma' ),
                    'default'     => '',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'width-100'   => true,
                    'css_identifier' => 'color',
                    'css_selectors' => $quote_font_selectors,
                ),//"#000000",
                'quote_color_hover_css'     => array(
                    'input_type'  => 'wp_color_alpha',
                    'title'       => __( 'Text color on mouse over', 'text_doma' ),
                    'default'     => '',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'css_identifier' => 'color_hover',
                    'css_selectors' => $quote_font_selectors,
                ),//"#000000",
                'quote_font_weight_css'     => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __( 'Font weight', 'text_doma' ),
                    'default'     => 400,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'font_weight',
                    'css_selectors' => $quote_font_selectors,
                    'choices'            => sek_get_select_options_for_input_id( 'font_weight_css' )
                ),//null,
                'quote_font_style_css'      => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __( 'Font style', 'text_doma' ),
                    'default'     => 'inherit',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'font_style',
                    'css_selectors' => $quote_font_selectors,
                    'choices'            => sek_get_select_options_for_input_id( 'font_style_css' )
                ),//null,
                'quote_text_decoration_css' => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __( 'Text decoration', 'text_doma' ),
                    'default'     => 'none',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'text_decoration',
                    'css_selectors' => $quote_font_selectors,
                    'choices'            => sek_get_select_options_for_input_id( 'text_decoration_css' )
                ),//null,
                'quote_text_transform_css'  => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __( 'Text transform', 'text_doma' ),
                    'default'     => 'none',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'text_transform',
                    'css_selectors' => $quote_font_selectors,
                    'choices'            => sek_get_select_options_for_input_id( 'text_transform_css' )
                ),//null,
                'quote_letter_spacing_css'  => array(
                    'input_type'  => 'range_simple',
                    'title'       => __( 'Letter spacing', 'text_doma' ),
                    'default'     => 0,
                    'min'         => 0,
                    'step'        => 1,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'letter_spacing',
                    'css_selectors' => $quote_font_selectors,
                    'width-100'   => true,
                ),//0,
                // Note : always use the suffix '_flag_important' to name an input controling the !important css flag @see Nimble\sek_add_css_rules_for_css_sniffed_input_id
                'quote___flag_important'       => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __( 'Make those style options win if other rules are applied.', 'text_doma' ),
                    'default'     => 0,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    // declare the list of input_id that will be flagged with !important when the option is checked
                    // @see sek_add_css_rules_for_css_sniffed_input_id
                    // @see sek_is_flagged_important
                    'important_input_list' => array(
                        'quote_font_family_css',
                        'quote_font_size_css',
                        'quote_line_height_css',
                        'quote_font_weight_css',
                        'quote_font_style_css',
                        'quote_text_decoration_css',
                        'quote_text_transform_css',
                        'quote_letter_spacing_css',
                        'quote_color_css',
                        'quote_color_hover_css'
                    )
                )
            )
        ),
        'render_tmpl_path' => '',
    );
}





/* ------------------------------------------------------------------------- *
 *  CITE CONTENT AND FONT
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_quote_cite_child() {
    $cite_font_selectors  = array( '.sek-cite', '.sek-cite *');
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_quote_cite_child',
        'name' => __( 'Cite content', 'text_doma' ),
        //'sanitize_callback' => '\Nimble\sanitize_callback__czr_simple_form_module',
        //'css_selectors' =>'',
        'tmpl' => array(
            'item-inputs' => array(
                'cite_text' => array(
                    'input_type'        => 'nimble_tinymce_editor',
                    'editor_params'     => array(
                        'media_button' => false,
                        'includedBtns' => 'basic_btns',
                        'height' => 50
                    ),
                    'refresh_markup' => '.sek-cite',
                    'title'              => __( 'Cite text', 'text_doma' ),
                    'default'            => '',
                    'width-100'         => true,
                    //'notice_before'      => __( 'You may use some html tags like a, br, span with attributes like style, id, class ...', 'text_doma'),
                ),
                'cite_font_family_css' => array(
                    'input_type'  => 'font_picker',
                    'title'       => __( 'Font family', 'text_doma' ),
                    'default'     => '',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'refresh_fonts' => true,
                    'css_identifier' => 'font_family',
                    'css_selectors' => $cite_font_selectors,
                    'html_after' => sprintf('<span class="czr-notice"><i class="far fa-lightbulb"></i> %1s => <a href="%2s" target="_blank" rel="noopener noreferrer">%2$s</a></span><hr/>',
                        __('Find inspiration'),
                        'https://fonts.google.com/?sort=popularity'
                    )
                ),
                'cite_font_size_css'       => array(
                    'input_type'  => 'range_with_unit_picker_device_switcher',
                    'title'       => __( 'Font size', 'text_doma' ),
                    'default'     => array( 'desktop' => '14px' ),
                    'min' => 0,
                    'max' => 100,
                    'width-100'         => true,
                    'title_width' => 'width-100',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'font_size',
                    'css_selectors' => $cite_font_selectors,
                ),//16,//"14px",
                'cite_line_height_css'     => array(
                    'input_type'  => 'range_with_unit_picker',
                    'title'       => __( 'Line height', 'text_doma' ),
                    'default'     => '1.5em',
                    'min' => 0,
                    'max' => 10,
                    'step' => 0.1,
                    'width-100'         => true,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'line_height',
                    'css_selectors' => $cite_font_selectors,
                ),//24,//"20px",
                'cite_color_css'           => array(
                    'input_type'  => 'wp_color_alpha',
                    'title'       => __( 'Text color', 'text_doma' ),
                    'default'     => '',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'width-100'   => true,
                    'css_identifier' => 'color',
                    'css_selectors' => $cite_font_selectors,
                ),//"#000000",
                'cite_color_hover_css'     => array(
                    'input_type'  => 'wp_color_alpha',
                    'title'       => __( 'Text color on mouse over', 'text_doma' ),
                    'default'     => '',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'css_identifier' => 'color_hover',
                    'css_selectors' => $cite_font_selectors,
                ),//"#000000",
                'cite_font_weight_css'     => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __( 'Font weight', 'text_doma' ),
                    'default'     => 'normal',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'font_weight',
                    'css_selectors' => $cite_font_selectors,
                    'choices'            => sek_get_select_options_for_input_id( 'font_weight_css' )
                ),//null,
                'cite_font_style_css'      => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __( 'Font style', 'text_doma' ),
                    'default'     => 'inherit',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'font_style',
                    'css_selectors' => $cite_font_selectors,
                    'choices'       => sek_get_select_options_for_input_id( 'font_style_css' )
                ),//null,
                'cite_text_decoration_css' => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __( 'Text decoration', 'text_doma' ),
                    'default'     => 'none',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'text_decoration',
                    'css_selectors' => $cite_font_selectors,
                    'choices'            => sek_get_select_options_for_input_id( 'text_decoration_css' )
                ),//null,
                'cite_text_transform_css'  => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __( 'Text transform', 'text_doma' ),
                    'default'     => 'none',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'text_transform',
                    'css_selectors' => $cite_font_selectors,
                    'choices'            => sek_get_select_options_for_input_id( 'text_transform_css' )
                ),//null,
                'cite_letter_spacing_css'  => array(
                    'input_type'  => 'range_simple',
                    'title'       => __( 'Letter spacing', 'text_doma' ),
                    'default'     => 0,
                    'min'         => 0,
                    'step'        => 1,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'letter_spacing',
                    'css_selectors' => $cite_font_selectors,
                    'width-100'   => true,
                ),//0,
                // Note : always use the suffix '_flag_important' to name an input controling the !important css flag @see Nimble\sek_add_css_rules_for_css_sniffed_input_id
                'cite___flag_important'       => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __( 'Make those style options win if other rules are applied.', 'text_doma' ),
                    'default'     => 0,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    // declare the list of input_id that will be flagged with !important when the option is checked
                    // @see sek_add_css_rules_for_css_sniffed_input_id
                    // @see Nsek_is_flagged_important
                    'important_input_list' => array(
                        'cite_font_family_css',
                        'cite_font_size_css',
                        'cite_line_height_css',
                        'cite_font_weight_css',
                        'cite_font_style_css',
                        'cite_text_decoration_css',
                        'cite_text_transform_css',
                        'cite_letter_spacing_css',
                        'cite_color_css',
                        'cite_color_hover_css'
                    )
                )
            )
        ),
        'render_tmpl_path' => '',
    );
}










/* ------------------------------------------------------------------------- *
 *  DESIGN
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_quote_design_child() {
    $cite_font_selectors  = array( '.sek-quote-design .sek-cite', '.sek-quote-design .sek-cite a' );
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_quote_design_child',
        'name' => __( 'Design', 'text_doma' ),
        //'sanitize_callback' => '\Nimble\sanitize_callback__czr_simple_form_module',
        //'css_selectors' =>'',
        'tmpl' => array(
            'item-inputs' => array(
                'quote_design' => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __( 'Design', 'text_doma' ),
                    'default'     => 'none',
                    'choices'     => sek_get_select_options_for_input_id( 'quote_design' )
                ),
                'border_width_css' => array(
                    'input_type'  => 'range_with_unit_picker',
                    'title'       => __( 'Border weight', 'text_doma' ),
                    'min' => 1,
                    'max' => 80,
                    'default' => '5px',
                    'width-100'   => true,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'border_width',
                    'css_selectors' => '.sek-quote.sek-quote-design.sek-border-before'
                ),
                'border_color_css' => array(
                    'input_type'  => 'wp_color_alpha',
                    'title'       => __( 'Border Color', 'text_doma' ),
                    'width-100'   => true,
                    'default'     => 'rgba(0,0,0,0.1)',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'border_color',
                    'css_selectors' => '.sek-quote.sek-quote-design.sek-border-before'
                ),
                'icon_size_css' => array(
                    'input_type'  => 'range_with_unit_picker',
                    'title'       => __( 'Icon Size', 'text_doma' ),
                    'default'     => '50px',
                    'min' => 0,
                    'max' => 100,
                    'width-100'         => true,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'font_size',
                    'css_selectors' => array( '.sek-quote.sek-quote-design.sek-quote-icon-before::before' )
                ),
                'icon_color_css' => array(
                    'input_type'  => 'wp_color_alpha',
                    'title'       => __( 'Icon Color', 'text_doma' ),
                    'width-100'   => true,
                    'default'     => '#ccc',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'color',
                    'css_selectors' => '.sek-quote.sek-quote-design.sek-quote-icon-before::before'
                )
            )
        ),
        'render_tmpl_path' => '',
    );
}



/* ------------------------------------------------------------------------- *
 *  SCHEDULE CSS RULES FILTERING
/* ------------------------------------------------------------------------- */
add_filter( 'sek_add_css_rules_for_module_type___czr_quote_module', '\Nimble\sek_add_css_rules_for_czr_quote_module', 10, 2 );
// filter documented in Sek_Dyn_CSS_Builder::sek_css_rules_sniffer_walker
// Note : $complete_modul_model has been normalized
// @return populated $rules
function sek_add_css_rules_for_czr_quote_module( $rules, $complete_modul_model ) {
    if ( empty( $complete_modul_model['value'] ) )
      return $rules;

    // BACKGROUND
    $value = $complete_modul_model['value'];
    $design_settings = $value['design'];

    if ( 'quote-icon-before' === $design_settings['quote_design'] && '50px' !== $design_settings['icon_size_css'] ) {
        if ( is_rtl() ) {
            $css_rule = sprintf('padding-right: calc( 10px + 0.7 * %1$s )', $design_settings['icon_size_css']);
        } else {
            $css_rule = sprintf('padding-left: calc( 10px + 0.7 * %1$s )', $design_settings['icon_size_css']);
        }
        $rules[] = array(
            'selector' => '[data-sek-id="'.$complete_modul_model['id'].'"] .sek-module-inner .sek-quote-icon-before .sek-quote-inner',
            'css_rules' => $css_rule,
            'mq' =>null
        );
    }
    return $rules;
}





/* ------------------------------------------------------------------------- *
 *  SANITIZATION
/* ------------------------------------------------------------------------- */
// convert into a json to prevent emoji breaking global json data structure
// fix for https://github.com/presscustomizr/nimble-builder/issues/544
function sanitize_callback__czr_quote_module( $value ) {
    if ( !is_array( $value ) )
        return $value;

    if ( array_key_exists( 'quote_content', $value ) && is_array( $value['quote_content'] ) && !empty($value['quote_content']['quote_text']) ) {
        //sanitize quote_text
        if ( !current_user_can( 'unfiltered_html' ) ) {
            $value['quote_content']['quote_text'] = wp_kses_post( $value['quote_content']['quote_text'] );
        }
        // convert into a json to prevent emoji breaking global json data structure
        // fix for https://github.com/presscustomizr/nimble-builder/issues/544
        $value['quote_content']['quote_text'] = sek_maybe_encode_richtext($value['quote_content']['quote_text']);
    }
    if ( array_key_exists( 'cite_content', $value ) && is_array( $value['cite_content'] ) && !empty($value['cite_content']['cite_text']) ) {
        //sanitize quote_text
        if ( !current_user_can( 'unfiltered_html' ) ) {
            $value['cite_content']['cite_text'] = wp_kses_post( $value['cite_content']['cite_text'] );
        }
        // convert into a json to prevent emoji breaking global json data structure
        // fix for https://github.com/presscustomizr/nimble-builder/issues/544
        $value['cite_content']['cite_text'] = sek_maybe_encode_richtext($value['cite_content']['cite_text']);
    }
    return $value;
}

?>
<?php
/* ------------------------------------------------------------------------- *
 *  LOAD AND REGISTER BUTTON MODULE
/* ------------------------------------------------------------------------- */
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );

function sek_get_module_params_for_czr_button_module() {
    $css_selectors = '.sek-btn';
    $css_font_selectors = '.sek-btn';
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_button_module',
        'is_father' => true,
        'children' => array(
            'content' => 'czr_btn_content_child',
            'design' => 'czr_btn_design_child',
            'font' => 'czr_font_child'
        ),
        'name' => __( 'Button', 'text_doma' ),
        'sanitize_callback' => '\Nimble\sanitize_callback__czr_button_module',
        'starting_value' => array(
            'content' => array(
                'button_text' => __('Click me','text_doma'),
            ),
            'design' => array(
                'bg_color_css' => '#020202',
                'bg_color_hover' => '#151515', //lighten 15%,
                'use_custom_bg_color_on_hover' => 0,
                'border_radius_css' => '2',
                'h_alignment_css' => 'center',
                'use_box_shadow' => 1,
                'push_effect' => 1,
            ),
            'font' => array(
                'color_css'  => '#ffffff',
            )
        ),
        'css_selectors' => array( '.sek-module-inner .sek-btn' ),
        'render_tmpl_path' => "button_module_tmpl.php"
    );
}



/* ------------------------------------------------------------------------- *
 *  BUTTON CONTENT
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_btn_content_child() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_btn_content_child',
        'name' => __( 'Button content', 'text_doma' ),
        //'sanitize_callback' => '\Nimble\sanitize_callback__czr_simple_form_module',
        //'css_selectors' =>'',
        'tmpl' => array(
            'item-inputs' => array(
                'button_text' => array(
                    'input_type'        => 'nimble_tinymce_editor',
                    'editor_params'     => array(
                        'media_button' => false,
                        'includedBtns' => 'basic_btns_nolink',
                        'height' => 45
                    ),
                    'title'              => __( 'Button text', 'text_doma' ),
                    'default'            => '',
                    'width-100'         => true,
                    'refresh_markup'    => '.sek-btn-text'
                ),
                'btn_text_on_hover' => array(
                    'input_type'         => 'text',
                    'title'              => __( 'Tooltip text on mouse hover', 'text_doma' ),
                    'default'            => '',
                    'width-100'         => true,
                    'title_width' => 'width-100',
                    'notice_after'       => __( 'Not previewable when customizing.', 'text_doma')
                ),
                'link-to' => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __('Link to', 'text_doma'),
                    'default'     => 'no-link',
                    'choices'     => sek_get_select_options_for_input_id( 'link-to' )
                ),
                'link-pick-url' => array(
                    'input_type'  => 'content_picker',
                    'title'       => __('Link url', 'text_doma'),
                    'default'     => array()
                ),
                'link-custom-url' => array(
                    'input_type'  => 'text',
                    'title'       => __('Custom link url', 'text_doma'),
                    'default'     => ''
                ),
                'link-target' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Open link in a new browser tab', 'text_doma'),
                    'default'     => false,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                ),
                'icon' => array(
                    'input_type'  => 'fa_icon_picker',
                    'title'       => __( 'Icon next to the button text', 'text_doma' ),
                    //'default'     => 'no-link'
                ),
                'icon-side' => array(
                    'input_type'  => 'buttons_choice',
                    'title'       => __("Icon's position", 'text_doma'),
                    'default'     => 'left',
                    'choices'     => array( 'left' => __('Left', 'text-domain'), 'right' => __('Right', 'text-domain') )
                ),
            )
        ),
        'render_tmpl_path' => '',
    );
}




/* ------------------------------------------------------------------------- *
 *  BUTTON DESIGN
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_btn_design_child() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_btn_design_child',
        'name' => __( 'Button design', 'text_doma' ),
        //'sanitize_callback' => '\Nimble\sanitize_callback__czr_simple_form_module',
        //'css_selectors' =>'',
        'tmpl' => array(
            'item-inputs' => array(
                'bg_color_css' => array(
                    'input_type'  => 'wp_color_alpha',
                    'title'       => __( 'Background color', 'text_doma' ),
                    'width-100'   => true,
                    'default'    => '#020202',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'background_color',
                    //'css_selectors'=> $css_selectors
                ),
                'use_custom_bg_color_on_hover' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __( 'Set a custom background color on mouse hover', 'text_doma' ),
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'default'     => 0,
                ),
                'bg_color_hover' => array(
                    'input_type'  => 'wp_color_alpha',
                    'title'       => __( 'Background color on mouse hover', 'text_doma' ),
                    'width-100'   => true,
                    'default'    => '',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'notice_after' => __( 'You can also customize the text color on mouseover in the group of text settings below.', 'text_doma')
                    //'css_identifier' => 'background_color_hover',
                    //'css_selectors'=> $css_selectors
                ),
                'border-type' => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __('Border', 'text_doma'),
                    'default' => 'none',
                    'choices'     => sek_get_select_options_for_input_id( 'border-type' ),
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true
                ),
                'borders' => array(
                    'input_type'  => 'borders',
                    'title'       => __('Borders', 'text_doma'),
                    'min' => 0,
                    'max' => 100,
                    'default' => array(
                        '_all_' => array( 'wght' => '1px', 'col' => '#000000' )
                    ),
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'css_selectors'=> '.sek-icon i'
                ),
                'border_radius_css'       => array(
                    'input_type'  => 'border_radius',
                    'title'       => __( 'Rounded corners', 'text_doma' ),
                    'default' => array( '_all_' => '0px' ),
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'min'         => 0,
                    'max'         => 500,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'border_radius',
                    //'css_selectors'=> $css_selectors
                ),
                'h_alignment_css'        => array(
                    'input_type'  => 'horizAlignmentWithDeviceSwitcher',
                    'title'              => __( 'Button alignment', 'text_doma' ),
                    'default'     => array( 'desktop' => 'center' ),
                    'refresh_markup'     => false,
                    'refresh_stylesheet' => true,
                    'css_identifier'     => 'h_alignment',
                    'css_selectors'      => '.sek-module-inner',
                    'title_width' => 'width-100',
                    'width-100'   => true,
                ),
                'spacing_css'        => array(
                    'input_type'         => 'spacing',
                    'title'              => __( 'Spacing', 'text_doma' ),
                    'default'            => array(
                        'padding-top'    => .5,
                        'padding-bottom' => .5,
                        'padding-right'  => 1,
                        'padding-left'   => 1,
                        'margin-top'    => .5,
                        'margin-bottom' => .5,
                        'unit' => 'em'
                    ),
                    'width-100'   => true,
                    'refresh_markup'     => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'padding_margin_spacing',
                    'css_selectors'=> '.sek-module-inner .sek-btn'
                ),
                'use_box_shadow' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __( 'Apply a shadow', 'text_doma' ),
                    'default'     => 1,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                ),
                'push_effect' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __( 'Push visual effect', 'text_doma' ),
                    'default'     => 1,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                ),
                'width-type' => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __('Width : auto or custom', 'text_doma'),
                    'default'     => 'default',
                    'choices'     => sek_get_select_options_for_input_id( 'height-type' ),
                    'html_before' => '<hr/>',
                    'refresh_markup'     => false,
                    'refresh_stylesheet' => true,
                ),
                'custom-width' => array(
                    'input_type'  => 'range_with_unit_picker_device_switcher',
                    'title'       => __('Custom width', 'text_doma'),
                    'min' => 0,
                    'max' => 500,
                    'default'     => array( 'desktop' => '150px' ),
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'refresh_markup'     => false,
                    'refresh_stylesheet' => true,
                ),
                'h_inner_align_css'        => array(
                    'input_type'  => 'horizTextAlignmentWithDeviceSwitcher',
                    'title'              => __( 'Text alignment', 'text_doma' ),
                    'default'     => array( 'desktop' => 'center' ),
                    'refresh_markup'     => false,
                    'refresh_stylesheet' => true,
                    'css_identifier'     => 'h_alignment',
                    'css_selectors'      => '.sek-btn .sek-btn-text',
                    'title_width' => 'width-100',
                    'width-100'   => true,
                ),
                'height-type' => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __('Height : auto or custom', 'text_doma'),
                    'default'     => 'default',
                    'choices'     => sek_get_select_options_for_input_id( 'height-type' ),
                    'html_before' => '<hr/>',
                    'refresh_markup'     => false,
                    'refresh_stylesheet' => true,
                ),
                'custom-height' => array(
                    'input_type'  => 'range_with_unit_picker_device_switcher',
                    'title'       => __('Custom height', 'text_doma'),
                    'min' => 0,
                    'max' => 500,
                    'default'     => array( 'desktop' => '40px' ),
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'refresh_markup'     => false,
                    'refresh_stylesheet' => true,
                ),
            )
        ),
        'render_tmpl_path' => '',
    );
}















/* ------------------------------------------------------------------------- *
 *  SANITIZATION
/* ------------------------------------------------------------------------- */
// convert into a json to prevent emoji breaking global json data structure
// fix for https://github.com/presscustomizr/nimble-builder/issues/544
function sanitize_callback__czr_button_module( $value ) {
    if ( is_array( $value ) && !empty($value['content']) && is_array( $value['content'] ) && array_key_exists( 'button_text', $value['content'] ) ) {
        //$value['content'][ 'button_text' ] = sanitize_text_field( $value['content'][ 'button_text' ] );
        // convert into a json to prevent emoji breaking global json data structure
        // fix for https://github.com/presscustomizr/nimble-builder/issues/544
        $value['content']['button_text'] = sek_maybe_encode_richtext($value['content']['button_text']);
    }
    return $value;
}

/* ------------------------------------------------------------------------- *
 *  SCHEDULE CSS RULES FILTERING
/* ------------------------------------------------------------------------- */
add_filter( 'sek_add_css_rules_for_module_type___czr_button_module', '\Nimble\sek_add_css_rules_for_button_front_module', 10, 2 );
// filter documented in Sek_Dyn_CSS_Builder::sek_css_rules_sniffer_walker
// Note : $complete_modul_model has been normalized
// @return populated $rules
function sek_add_css_rules_for_button_front_module( $rules, $complete_modul_model ) {
    if ( empty( $complete_modul_model['value'] ) )
      return $rules;

    // BACKGROUND
    $value = $complete_modul_model['value'];
    $design_settings = $value['design'];
    $bg_color = $design_settings['bg_color_css'];
    if ( sek_booleanize_checkbox_val( $design_settings['use_custom_bg_color_on_hover'] ) ) {
        $bg_color_hover = $design_settings['bg_color_hover'];
    } else {
        // Build the lighter rgb from the user picked bg color
        if ( 0 === strpos( $bg_color, 'rgba' ) ) {
            list( $rgb, $alpha ) = sek_rgba2rgb_a( $bg_color );
            $bg_color_hover_rgb  = sek_lighten_rgb( $rgb, $percent=15, $array = true );
            $bg_color_hover      = sek_rgb2rgba( $bg_color_hover_rgb, $alpha, $array = false, $make_prop_value = true );
        } else if ( 0 === strpos( $bg_color, 'rgb' ) ) {
            $bg_color_hover      = sek_lighten_rgb( $bg_color, $percent=15 );
        } else {
            $bg_color_hover      = sek_lighten_hex( $bg_color, $percent=15 );
        }
    }
    $rules[] = array(
        'selector' => '.nb-loc .sek-row [data-sek-id="'.$complete_modul_model['id'].'"] .sek-module-inner .sek-btn:hover, .nb-loc .sek-row [data-sek-id="'.$complete_modul_model['id'].'"] .sek-module-inner .sek-btn:focus',
        'css_rules' => 'background-color:' . $bg_color_hover . ';',
        'mq' =>null
    );

    // BORDERS
    $border_settings = $design_settings[ 'borders' ];
    $border_type = $design_settings[ 'border-type' ];
    $has_border_settings  = 'none' != $border_type && !empty( $border_type );

    //border width + type + color
    if ( $has_border_settings ) {
        $rules = sek_generate_css_rules_for_multidimensional_border_options(
            $rules,
            $border_settings,
            $border_type,
            '[data-sek-id="'.$complete_modul_model['id'].'"] .sek-module-inner .sek-btn'
        );
    }

    // CUSTOM WIDTH BY DEVICE
    if ( !empty( $design_settings[ 'width-type' ] ) ) {
        if ( 'custom' == $design_settings[ 'width-type' ] && array_key_exists( 'custom-width', $design_settings ) ) {
            $user_custom_width_value = $design_settings[ 'custom-width' ];
            if ( !empty( $user_custom_width_value ) && !is_array( $user_custom_width_value ) ) {
                sek_error_log( __FUNCTION__ . ' => error => the width option should be an array( {device} => {number}{unit} )');
            }
            $user_custom_width_value = is_array( $user_custom_width_value ) ? $user_custom_width_value : array();
            $user_custom_width_value = wp_parse_args( $user_custom_width_value, array(
                'desktop' => '100%',
                'tablet' => '',
                'mobile' => ''
            ));
            $width_value = $user_custom_width_value;
            foreach ( $user_custom_width_value as $device => $num_unit ) {
                $numeric = sek_extract_numeric_value( $num_unit );
                if ( !empty( $numeric ) ) {
                    $unit = sek_extract_unit( $num_unit );
                    $width_value[$device] = $numeric . $unit;
                }
            }

            $rules = sek_set_mq_css_rules(array(
                'value' => $width_value,
                'css_property' => 'width',
                'selector' => '[data-sek-id="'.$complete_modul_model['id'].'"] .sek-module-inner .sek-btn',
                'level_id' => $complete_modul_model['id']
            ), $rules );
        }
    }


    // CUSTOM HEIGHT BY DEVICE
    if ( !empty( $design_settings[ 'height-type' ] ) ) {
        if ( 'custom' === $design_settings[ 'height-type' ] ) {
            $custom_user_height = array_key_exists( 'custom-height', $design_settings ) ? $design_settings[ 'custom-height' ] : array();
            if ( !is_array( $custom_user_height ) ) {
                sek_error_log( __FUNCTION__ . ' => error => the height option should be an array( {device} => {number}{unit} )', $custom_user_height);
            }
            $custom_user_height = is_array( $custom_user_height ) ? $custom_user_height : array();
            $custom_user_height = wp_parse_args( $custom_user_height, array(
                'desktop' => '40px',//<= consistent with default
                'tablet' => '',
                'mobile' => ''
            ));
            $height_value = $custom_user_height;
            foreach ( $custom_user_height as $device => $num_unit ) {
                $numeric = sek_extract_numeric_value( $num_unit );
                if ( !empty( $numeric ) ) {
                    $unit = sek_extract_unit( $num_unit );
                    $unit = '%' === $unit ? 'vh' : $unit;
                    $height_value[$device] = $numeric . $unit;
                }
            }

            $rules = sek_set_mq_css_rules(array(
                'value' => $height_value,
                'css_property' => 'height',
                'selector' => '[data-sek-id="'.$complete_modul_model['id'].'"] .sek-module-inner .sek-btn',
                'level_id' => $complete_modul_model['id']
            ), $rules );
        }
    }

    return $rules;
}

?>
<?php
/* ------------------------------------------------------------------------- *
 *  LOAD AND REGISTER SIMPLE FORM MODULES
/* ------------------------------------------------------------------------- */
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );

function sek_get_module_params_for_czr_simple_form_module() {
    $css_selectors = '.sek-btn';
    $css_font_selectors = '.sek-btn';
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_simple_form_module',
        'is_father' => true,
        'children' => array(
            'form_fields'   => 'czr_simple_form_fields_child',
            'fields_design' => 'czr_simple_form_design_child',
            'form_button'   => 'czr_simple_form_button_child',
            'form_fonts'    => 'czr_simple_form_fonts_child',
            'form_submission'    => 'czr_simple_form_submission_child'
        ),
        'name' => __( 'Simple Form', 'text_doma' ),
        'sanitize_callback' => '\Nimble\sanitize_callback__czr_simple_form_module',
        'starting_value' => array(
            'fields_design' => array(
                'border' => array(
                    '_all_' => array( 'wght' => '1px', 'col' => '#cccccc' )
                )
            ),
            'form_button' => array(
                'bg_color_css' => '#020202',
                'bg_color_hover' => '#151515', //lighten 15%,
                'use_custom_bg_color_on_hover' => 0,
                'border_radius_css' => '2',
                'h_alignment_css' => is_rtl() ? 'right' : 'left',
                'use_box_shadow' => 1,
                'push_effect' => 1
            ),
            'form_fonts' => array(
                // 'fl_font_family_css' => '[cfont]Lucida Console,Monaco,monospace',
                'fl_font_weight_css' => 'bold',
                'btn_color_css' => '#ffffff'
            ),
            'form_submission' => array(
                'email_footer' => sprintf( __( 'This e-mail was sent from a contact form on %1$s (<a href="%2$s" target="_blank">%2$s</a>)', 'text_doma' ),
                    get_bloginfo( 'name' ),
                    get_site_url( 'url' )
                )
            )
        ),
        'css_selectors' => array( '.sek-module-inner' ),
        'render_tmpl_path' => "simple_form_module_tmpl.php",
    );
}











/* ------------------------------------------------------------------------- *
 *  FIELDS VISIBILITY AND REQUIRED
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_simple_form_fields_child() {
    $css_selectors = '.sek-btn';
    $css_font_selectors = '.sek-btn';
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_simple_form_fields_child',
        'name' => __( 'Form fields and button labels', 'text_doma' ),
        //'sanitize_callback' => '\Nimble\sanitize_callback__czr_simple_form_module',
        // 'starting_value' => array(
        //     'button_text' => __('Click me','text_doma'),
        //     'color_css'  => '#ffffff',
        //     'bg_color_css' => '#020202',
        //     'bg_color_hover' => '#151515', //lighten 15%,
        //     'use_custom_bg_color_on_hover' => 0,
        //     'border_radius_css' => '2',
        //     'h_alignment_css' => 'center',
        //     'use_box_shadow' => 1,
        //     'push_effect' => 1
        // ),
        'css_selectors' => array( '.sek-module-inner' ),
        'tmpl' => array(
            'item-inputs' => array(
                'show_name_field' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Display name field', 'text_doma'),
                    'default'     => true,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                ),
                'name_field_label' => array(
                    'input_type'  => 'text',
                    'width-100'         => true,
                    'title'       => __('Name field label', 'text_doma'),
                    'default'     => __('Name', 'translate')
                ),
                'name_field_required' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Name field is required', 'text_doma'),
                    'default'     => true,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'html_after' => '<hr/>'
                ),
                'email_field_label' => array(
                    'input_type'  => 'text',
                    'width-100'         => true,
                    'title'       => __('Email field label', 'text_doma'),
                    'default'     => __('Email', 'translate')
                ),
                'show_subject_field' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Display subject field', 'text_doma'),
                    'default'     => false,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                ),
                'subject_field_label' => array(
                    'input_type'  => 'text',
                    'width-100'         => true,
                    'title'       => __('Subject field label', 'text_doma'),
                    'default'     => __('Subject', 'translate')
                ),
                'subject_field_required' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Subject field is required', 'text_doma'),
                    'default'     => true,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'html_after' => '<hr/>'
                ),

                'show_message_field' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Display message field', 'text_doma'),
                    'default'     => true,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                ),
                'message_field_label' => array(
                    'input_type'  => 'text',
                    'width-100'         => true,
                    'title'       => __('Message field label', 'text_doma'),
                    'default'     => __('Message', 'translate')
                ),
                'message_field_required' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Message field is required', 'text_doma'),
                    'default'     => true,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'html_after' => '<hr/>'
                ),

                'show_privacy_field' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Display a checkbox for privacy policy consent', 'text_doma'),
                    'default'     => false,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                ),
                'privacy_field_label' => array(
                    'input_type'        => 'nimble_tinymce_editor',
                    'title'       => __( 'Consent message' , 'text_doma' ),
                    'default'     => __( 'I have read and agree to the privacy policy.', 'text_doma' ),
                    'width-100'         => true,
                    'refresh_markup' => '.sek-privacy-wrapper label',
                    'notice_before' => __('Html code is allowed', 'text-domain')
                ),
                'privacy_field_required' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Privacy policy consent is required', 'text_doma'),
                    'default'     => true,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'html_after' => '<hr/>'
                ),

                'button_text' => array(
                    'input_type'  => 'text',
                    'width-100'         => true,
                    'title'       => __('Button text', 'text_doma'),
                    'default'     => __('Submit', 'translate')
                ),
            )
        ),
        'render_tmpl_path' => '',
    );
}







/* ------------------------------------------------------------------------- *
 *  FIELDS DESIGN
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_simple_form_design_child() {
    $css_selectors = array( 'form input[type="text"]', 'input[type="text"]:focus', 'form textarea', 'form textarea:focus' );
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_simple_form_design_child',
        'name' => __( 'Form fields design', 'text_doma' ),
        //'sanitize_callback' => '\Nimble\sanitize_callback__czr_simple_form_module',
        // 'starting_value' => array(
        //     'button_text' => __('Click me','text_doma'),
        //     'color_css'  => '#ffffff',
        //     'bg_color_css' => '#020202',
        //     'bg_color_hover' => '#151515', //lighten 15%,
        //     'use_custom_bg_color_on_hover' => 0,
        //     'border_radius_css' => '2',
        //     'h_alignment_css' => 'center',
        //     'use_box_shadow' => 1,
        //     'push_effect' => 1
        // ),
        'css_selectors' => array( '.sek-module-inner .sek-simple-form-wrapper' ),
        'tmpl' => array(
            'item-inputs' => array(
                'bg_color_css' => array(
                    'input_type'  => 'wp_color_alpha',
                    'title'       => __( 'Fields background color', 'text_doma' ),
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'default'    => '',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'background_color',
                    'css_selectors'=> $css_selectors
                ),
                'border-type' => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __('Fields border shape', 'text_doma'),
                    'default' => 'solid',
                    'choices'     => sek_get_select_options_for_input_id( 'border-type' ),
                    'refresh_stylesheet' => true
                ),
                'borders' => array(
                    'input_type'  => 'borders',
                    'title'       => __('Borders options', 'text_doma'),
                    'min' => 0,
                    'max' => 100,
                    'default' => array(
                        '_all_' => array( 'wght' => '1px', 'col' => '#cccccc' )
                    ),
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'css_selectors'=> $css_selectors
                ),
                'border_radius_css'       => array(
                    'input_type'  => 'range_with_unit_picker',
                    'title'       => __( 'Fields rounded corners', 'text_doma' ),
                    'default'     => '3px',
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'min'         => 0,
                    'max'         => 500,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'border_radius',
                    'css_selectors'=> $css_selectors
                ),
                'use_inset_shadow' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __( 'Apply an inset shadow', 'text_doma' ),
                    'default'     => 1,
                ),
                'use_outset_shadow' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __( 'Apply an outset shadow', 'text_doma' ),
                    'default'     => 0,
                )
            )
        ),
        'render_tmpl_path' => '',
    );
}




/* ------------------------------------------------------------------------- *
 *  SUBMIT BUTTON DESIGN
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_simple_form_button_child() {
    $css_selectors = '.sek-module-inner form input[type="submit"]';
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_simple_form_button_child',
        'name' => __( 'Form button design', 'text_doma' ),
        //'sanitize_callback' => '\Nimble\sanitize_callback__czr_simple_form_module',
        // 'starting_value' => array(
        //     'button_text' => __('Click me','text_doma'),
        //     'color_css'  => '#ffffff',
        //     'bg_color_css' => '#020202',
        //     'bg_color_hover' => '#151515', //lighten 15%,
        //     'use_custom_bg_color_on_hover' => 0,
        //     'border_radius_css' => '2',
        //     'h_alignment_css' => 'center',
        //     'use_box_shadow' => 1,
        //     'push_effect' => 1
        // ),
        'css_selectors' => array( '.sek-module-inner .sek-simple-form-wrapper' ),
        'tmpl' => array(
            'item-inputs' => array(
                'bg_color_css' => array(
                      'input_type'  => 'wp_color_alpha',
                      'title'       => __( 'Background color', 'text_doma' ),
                      'width-100'   => true,
                      'default'    => '',
                      'refresh_markup' => false,
                      'refresh_stylesheet' => true,
                      'css_identifier' => 'background_color',
                      'css_selectors'=> $css_selectors
                ),
                'use_custom_bg_color_on_hover' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __( 'Set a custom background color on mouse hover', 'text_doma' ),
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'default'     => 0,
                ),
                'bg_color_hover' => array(
                    'input_type'  => 'wp_color_alpha',
                    'title'       => __( 'Background color on mouse hover', 'text_doma' ),
                    'width-100'   => true,
                    'default'    => '',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    //'css_identifier' => 'background_color_hover',
                    'css_selectors'=> $css_selectors
                ),
                'border-type' => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __('Border', 'text_doma'),
                    'default' => 'none',
                    'choices'     => sek_get_select_options_for_input_id( 'border-type' ),
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true
                ),
                'borders' => array(
                    'input_type'  => 'borders',
                    'title'       => __('Borders', 'text_doma'),
                    'min' => 0,
                    'max' => 100,
                    'default' => array(
                        '_all_' => array( 'wght' => '1px', 'col' => '#000000' )
                    ),
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'css_selectors'=> '.sek-icon i'
                ),
                'border_radius_css'       => array(
                    'input_type'  => 'range_with_unit_picker',
                    'title'       => __( 'Rounded corners', 'text_doma' ),
                    'default'     => '2px',
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'min'         => 0,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'border_radius',
                    'css_selectors'=> $css_selectors
                ),
                'h_alignment_css'        => array(
                    'input_type'  => 'horizAlignmentWithDeviceSwitcher',
                    'title'       => __( 'Button alignment', 'text_doma' ),
                    'default'     => array( 'desktop' => is_rtl() ? 'right' : 'left' ),
                    'refresh_markup'     => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'h_alignment',
                    'css_selectors'=> '.sek-form-btn-wrapper',
                    'title_width' => 'width-100',
                    'width-100'   => true,
                ),
                'spacing_css'        => array(
                    'input_type'         => 'spacing',
                    'title'              => __( 'Spacing', 'text_doma' ),
                    'default'            => array(
                        'margin-top'     => .5,
                        'padding-top'    => .5,
                        'padding-bottom' => .5,
                        'padding-right'  => 1,
                        'padding-left'   => 1,
                        'unit' => 'em'
                    ),
                    'width-100'   => true,
                    'refresh_markup'     => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'padding_margin_spacing',
                    'css_selectors'=> $css_selectors,//'.sek-module-inner .sek-btn'
                ),
                'use_box_shadow' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __( 'Apply a shadow', 'text_doma' ),
                    'default'     => 1,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                ),
                'push_effect' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __( 'Push visual effect', 'text_doma' ),
                    'default'     => 1,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                ),
            )
        ),
        'render_tmpl_path' => '',
    );
}












/* ------------------------------------------------------------------------- *
 *  FONTS
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_simple_form_fonts_child() {
    $fl_font_selectors = array( '.sek-simple-form-wrapper form label', '.sek-form-message' ); //<= .sek-form-message is the wrapper of the form status message : Thanks, etc...
    $ft_font_selectors = array( 'form input[type="text"]', 'form input[type="text"]:focus', 'form textarea', 'form textarea:focus' );
    $btn_font_selectors = array( 'form input[type="submit"]' );
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_simple_form_fonts_child',
        'name' => __( 'Form texts options : fonts, colors, ...', 'text_doma' ),
        //'sanitize_callback' => '\Nimble\sanitize_callback__czr_simple_form_module',
        // 'starting_value' => array(
        // ),
        'css_selectors' => array( '.sek-module-inner .sek-simple-form-wrapper' ),
        'tmpl' => array(
            'item-inputs' => array(
                'tabs' => array(
                    array(
                        'title' => __( 'Fields labels', 'text_doma' ),
                        'inputs' => array(
                            'fl_font_family_css' => array(
                                'input_type'  => 'font_picker',
                                'title'       => __( 'Font family', 'text_doma' ),
                                'default'     => '',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'refresh_fonts' => true,
                                'css_identifier' => 'font_family',
                                'css_selectors' => $fl_font_selectors,
                                'html_after' => sprintf('<span class="czr-notice"><i class="far fa-lightbulb"></i> %1s => <a href="%2s" target="_blank" rel="noopener noreferrer">%2$s</a></span><hr/>',
                                    __('Find inspiration'),
                                    'https://fonts.google.com/?sort=popularity'
                                )
                            ),
                            'fl_font_size_css'       => array(
                                'input_type'  => 'range_with_unit_picker_device_switcher',
                                'default'     => array( 'desktop' => '16px' ),
                                'title_width' => 'width-100',
                                'title'       => __( 'Font size', 'text_doma' ),
                                'min' => 0,
                                'max' => 100,
                                'width-100'         => true,
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'font_size',
                                'css_selectors' => $fl_font_selectors,
                            ),//16,//"14px",
                            'fl_line_height_css'     => array(
                                'input_type'  => 'range_with_unit_picker',
                                'title'       => __( 'Line height', 'text_doma' ),
                                'default'     => '1.5em',
                                'min' => 0,
                                'max' => 10,
                                'step' => 0.1,
                                'width-100'         => true,
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'line_height',
                                'css_selectors' => $fl_font_selectors,
                            ),//24,//"20px",
                            'fl_color_css'           => array(
                                'input_type'  => 'wp_color_alpha',
                                'title'       => __( 'Text color', 'text_doma' ),
                                'default'     => '',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'width-100'   => true,
                                'css_identifier' => 'color',
                                'css_selectors' => $fl_font_selectors,
                            ),//"#000000",
                            'fl_color_hover_css'     => array(
                                'input_type'  => 'wp_color_alpha',
                                'title'       => __( 'Text color on mouse over', 'text_doma' ),
                                'default'     => '',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'width-100'   => true,
                                'title_width' => 'width-100',
                                'css_identifier' => 'color_hover',
                                'css_selectors' => $fl_font_selectors,
                            ),//"#000000",
                            'fl_font_weight_css'     => array(
                                'input_type'  => 'simpleselect',
                                'title'       => __( 'Font weight', 'text_doma' ),
                                'default'     => 'bold',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'font_weight',
                                'css_selectors' => $fl_font_selectors,
                                'choices'            => sek_get_select_options_for_input_id( 'font_weight_css' )
                            ),//null,
                            'fl_font_style_css'      => array(
                                'input_type'  => 'simpleselect',
                                'title'       => __( 'Font style', 'text_doma' ),
                                'default'     => 'inherit',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'font_style',
                                'css_selectors' => $fl_font_selectors,
                                'choices'            => sek_get_select_options_for_input_id( 'font_style_css' )
                            ),//null,
                            'fl_text_decoration_css' => array(
                                'input_type'  => 'simpleselect',
                                'title'       => __( 'Text decoration', 'text_doma' ),
                                'default'     => 'none',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'text_decoration',
                                'css_selectors' => $fl_font_selectors,
                                'choices'            => sek_get_select_options_for_input_id( 'text_decoration_css' )
                            ),//null,
                            'fl_text_transform_css'  => array(
                                'input_type'  => 'simpleselect',
                                'title'       => __( 'Text transform', 'text_doma' ),
                                'default'     => 'none',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'text_transform',
                                'css_selectors' => $fl_font_selectors,
                                'choices'            => sek_get_select_options_for_input_id( 'text_transform_css' )
                            ),//null,
                            'fl_letter_spacing_css'  => array(
                                'input_type'  => 'range_simple',
                                'title'       => __( 'Letter spacing', 'text_doma' ),
                                'default'     => 0,
                                'min'         => 0,
                                'step'        => 1,
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'letter_spacing',
                                'css_selectors' => $fl_font_selectors,
                                'width-100'   => true,
                            ),//0,
                            // Note : always use the suffix '_flag_important' to name an input controling the !important css flag @see Nimble\sek_add_css_rules_for_css_sniffed_input_id
                            'fl___flag_important'       => array(
                                'input_type'  => 'nimblecheck',
                                'title'       => __( 'Make those style options win if other rules are applied.', 'text_doma' ),
                                'default'     => 0,
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'title_width' => 'width-80',
                                'input_width' => 'width-20',
                                // declare the list of input_id that will be flagged with !important when the option is checked
                                // @see sek_add_css_rules_for_css_sniffed_input_id
                                // @see Nsek_is_flagged_important
                                'important_input_list' => array(
                                    'fl_font_family_css',
                                    'fl_font_size_css',
                                    'fl_line_height_css',
                                    'fl_font_weight_css',
                                    'fl_font_style_css',
                                    'fl_text_decoration_css',
                                    'fl_text_transform_css',
                                    'fl_letter_spacing_css',
                                    'fl_color_css',
                                    'fl_color_hover_css'
                                )
                            ),
                        )
                    ),
                    array(
                        'title' => __( 'Field Text', 'text_doma' ),
                        'inputs' => array(
                            'ft_font_family_css' => array(
                                'input_type'  => 'font_picker',
                                'title'       => __( 'Font family', 'text_doma' ),
                                'default'     => '',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'refresh_fonts' => true,
                                'css_identifier' => 'font_family',
                                'css_selectors' => $ft_font_selectors,
                                'html_after' => sprintf('<span class="czr-notice"><i class="far fa-lightbulb"></i> %1s => <a href="%2s" target="_blank" rel="noopener noreferrer">%2$s</a></span><hr/>',
                                    __('Find inspiration'),
                                    'https://fonts.google.com/?sort=popularity'
                                )
                            ),
                            'ft_font_size_css'       => array(
                                'input_type'  => 'range_with_unit_picker_device_switcher',
                                'default'     => array( 'desktop' => '16px' ),
                                'title_width' => 'width-100',
                                'title'       => __( 'Font size', 'text_doma' ),
                                'min' => 0,
                                'max' => 100,
                                'width-100'         => true,
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'font_size',
                                'css_selectors' => $ft_font_selectors,
                            ),//16,//"14px",
                            'ft_line_height_css'     => array(
                                'input_type'  => 'range_with_unit_picker',
                                'title'       => __( 'Line height', 'text_doma' ),
                                'default'     => '1.5em',
                                'min' => 0,
                                'max' => 10,
                                'step' => 0.1,
                                'width-100'         => true,
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'line_height',
                                'css_selectors' => $ft_font_selectors,
                            ),//24,//"20px",
                            'ft_color_css'           => array(
                                'input_type'  => 'wp_color_alpha',
                                'title'       => __( 'Text color', 'text_doma' ),
                                'default'     => '',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'width-100'   => true,
                                'css_identifier' => 'color',
                                'css_selectors' => $ft_font_selectors,
                            ),//"#000000",
                            'ft_color_hover_css'     => array(
                                'input_type'  => 'wp_color_alpha',
                                'title'       => __( 'Text color on mouse over', 'text_doma' ),
                                'default'     => '',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'width-100'   => true,
                                'title_width' => 'width-100',
                                'css_identifier' => 'color_hover',
                                'css_selectors' => $ft_font_selectors,
                            ),//"#000000",
                            'ft_font_weight_css'     => array(
                                'input_type'  => 'simpleselect',
                                'title'       => __( 'Font weight', 'text_doma' ),
                                'default'     => 'normal',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'font_weight',
                                'css_selectors' => $ft_font_selectors,
                                'choices'            => sek_get_select_options_for_input_id( 'font_weight_css' )
                            ),//null,
                            'ft_font_style_css'      => array(
                                'input_type'  => 'simpleselect',
                                'title'       => __( 'Font style', 'text_doma' ),
                                'default'     => 'inherit',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'font_style',
                                'css_selectors' => $ft_font_selectors,
                                'choices'       => sek_get_select_options_for_input_id( 'font_style_css' )
                            ),//null,
                            'ft_text_decoration_css' => array(
                                'input_type'  => 'simpleselect',
                                'title'       => __( 'Text decoration', 'text_doma' ),
                                'default'     => 'none',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'text_decoration',
                                'css_selectors' => $ft_font_selectors,
                                'choices'            => sek_get_select_options_for_input_id( 'text_decoration_css' )
                            ),//null,
                            'ft_text_transform_css'  => array(
                                'input_type'  => 'simpleselect',
                                'title'       => __( 'Text transform', 'text_doma' ),
                                'default'     => 'none',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'text_transform',
                                'css_selectors' => $ft_font_selectors,
                                'choices'            => sek_get_select_options_for_input_id( 'text_transform_css' )
                            ),//null,
                            'ft_letter_spacing_css'  => array(
                                'input_type'  => 'range_simple',
                                'title'       => __( 'Letter spacing', 'text_doma' ),
                                'default'     => 0,
                                'min'         => 0,
                                'step'        => 1,
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'letter_spacing',
                                'css_selectors' => $ft_font_selectors,
                                'width-100'   => true,
                            ),//0,
                            // Note : always use the suffix '_flag_important' to name an input controling the !important css flag @see Nimble\sek_add_css_rules_for_css_sniffed_input_id
                            'ft___flag_important'       => array(
                                'input_type'  => 'nimblecheck',
                                'title'       => __( 'Make those style options win if other rules are applied.', 'text_doma' ),
                                'default'     => 0,
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'title_width' => 'width-80',
                                'input_width' => 'width-20',
                                // declare the list of input_id that will be flagged with !important when the option is checked
                                // @see sek_add_css_rules_for_css_sniffed_input_id
                                // @see Nsek_is_flagged_important
                                'important_input_list' => array(
                                    'ft_font_family_css',
                                    'ft_font_size_css',
                                    'ft_line_height_css',
                                    'ft_font_weight_css',
                                    'ft_font_style_css',
                                    'ft_text_decoration_css',
                                    'ft_text_transform_css',
                                    'ft_letter_spacing_css',
                                    'ft_color_css',
                                    'ft_color_hover_css'
                                )
                            ),
                        ),//inputs
                    ),//tab
                    array(
                        'title' => __( 'Button', 'text_doma' ),
                        'inputs' => array(
                            'btn_font_family_css' => array(
                                'input_type'  => 'font_picker',
                                'title'       => __( 'Font family', 'text_doma' ),
                                'default'     => '',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'refresh_fonts' => true,
                                'css_identifier' => 'font_family',
                                'css_selectors' => $btn_font_selectors,
                                'html_after' => sprintf('<span class="czr-notice"><i class="far fa-lightbulb"></i> %1s => <a href="%2s" target="_blank" rel="noopener noreferrer">%2$s</a></span><hr/>',
                                    __('Find inspiration'),
                                    'https://fonts.google.com/?sort=popularity'
                                )
                            ),
                            'btn_font_size_css'       => array(
                                'input_type'  => 'range_with_unit_picker_device_switcher',
                                'default'     => array( 'desktop' => '16px' ),
                                'title_width' => 'width-100',
                                'title'       => __( 'Font size', 'text_doma' ),
                                'min' => 0,
                                'max' => 100,
                                'width-100'         => true,
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'font_size',
                                'css_selectors' => $btn_font_selectors,
                            ),//16,//"14px",
                            'line_height_css'     => array(
                                'input_type'  => 'range_with_unit_picker',
                                'title'       => __( 'Line height', 'text_doma' ),
                                'default'     => '1.5em',
                                'min' => 0,
                                'max' => 10,
                                'step' => 0.1,
                                'width-100'         => true,
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'line_height',
                                'css_selectors' => $btn_font_selectors,
                            ),//24,//"20px",
                            'btn_color_css'           => array(
                                'input_type'  => 'wp_color_alpha',
                                'title'       => __( 'Text color', 'text_doma' ),
                                'default'     => '',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'width-100'   => true,
                                'css_identifier' => 'color',
                                'css_selectors' => $btn_font_selectors,
                            ),//"#000000",
                            'btn_color_hover_css'     => array(
                                'input_type'  => 'wp_color_alpha',
                                'title'       => __( 'Text color on mouse over', 'text_doma' ),
                                'default'     => '',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'width-100'   => true,
                                'title_width' => 'width-100',
                                'css_identifier' => 'color_hover',
                                'css_selectors' => $btn_font_selectors,
                            ),//"#000000",
                            'btn_font_weight_css'     => array(
                                'input_type'  => 'simpleselect',
                                'title'       => __( 'Font weight', 'text_doma' ),
                                'default'     => 'normal',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'font_weight',
                                'css_selectors' => $btn_font_selectors,
                                'choices'            => sek_get_select_options_for_input_id( 'font_weight_css' )
                            ),//null,
                            'btn_font_style_css'      => array(
                                'input_type'  => 'simpleselect',
                                'title'       => __( 'Font style', 'text_doma' ),
                                'default'     => 'inherit',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'font_style',
                                'css_selectors' => $btn_font_selectors,
                                'choices'       => sek_get_select_options_for_input_id( 'font_style_css' )
                            ),//null,
                            'btn_text_decoration_css' => array(
                                'input_type'  => 'simpleselect',
                                'title'       => __( 'Text decoration', 'text_doma' ),
                                'default'     => 'none',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'text_decoration',
                                'css_selectors' => $btn_font_selectors,
                                'choices'            => sek_get_select_options_for_input_id( 'text_decoration_css' )
                            ),//null,
                            'btn_text_transform_css'  => array(
                                'input_type'  => 'simpleselect',
                                'title'       => __( 'Text transform', 'text_doma' ),
                                'default'     => 'none',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'text_transform',
                                'css_selectors' => $btn_font_selectors,
                                'choices'            => sek_get_select_options_for_input_id( 'text_transform_css' )
                            ),//null,
                            'btn_letter_spacing_css'  => array(
                                'input_type'  => 'range_simple',
                                'title'       => __( 'Letter spacing', 'text_doma' ),
                                'default'     => 0,
                                'min'         => 0,
                                'step'        => 1,
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'letter_spacing',
                                'css_selectors' => $btn_font_selectors,
                                'width-100'   => true,
                            ),//0,
                            // Note : always use the suffix '_flag_important' to name an input controling the !important css flag @see Nimble\sek_add_css_rules_for_css_sniffed_input_id
                            'btn___flag_important'       => array(
                                'input_type'  => 'nimblecheck',
                                'title'       => __( 'Make those style options win if other rules are applied.', 'text_doma' ),
                                'default'     => 0,
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'title_width' => 'width-80',
                                'input_width' => 'width-20',
                                // declare the list of input_id that will be flagged with !important when the option is checked
                                // @see sek_add_css_rules_for_css_sniffed_input_id
                                // @see Nsek_is_flagged_important
                                'important_input_list' => array(
                                    'btn_font_family_css',
                                    'btn_font_size_css',
                                    'btn_line_height_css',
                                    'btn_font_weight_css',
                                    'btn_font_style_css',
                                    'btn_text_decoration_css',
                                    'btn_text_transform_css',
                                    'btn_letter_spacing_css',
                                    'btn_color_css',
                                    'btn_color_hover_css'
                                )
                            ),
                        ),//inputs
                    ),//tab
                )//tabs
            )//item-inputs
        ),//tmpl
        'render_tmpl_path' => '',
    );
}

function sanitize_callback__czr_simple_form_module( $value ) {
    if ( !is_array( $value ) )
        return $value;
    // convert into a json to prevent emoji breaking global json data structure
    // fix for https://github.com/presscustomizr/nimble-builder/issues/544
    if ( array_key_exists( 'form_fields', $value ) && is_array( $value['form_fields'] ) ) {
        if ( !empty($value['form_fields']['button_text']) ) {
            $value['form_fields']['button_text'] = sanitize_text_field( $value['form_fields']['button_text'] );
            $value['form_fields']['button_text'] = sek_maybe_encode_richtext($value['form_fields']['button_text']);
        }
        if ( !empty($value['form_fields']['privacy_field_label']) ) {
            $value['form_fields']['privacy_field_label'] = sek_maybe_encode_richtext($value['form_fields']['privacy_field_label']);
        }
    }
    if ( array_key_exists( 'form_submission', $value ) && is_array( $value['form_submission'] ) ) {
        if ( !empty($value['form_submission']['email_footer']) ) {
            $value['form_submission']['email_footer'] = sek_maybe_encode_richtext($value['form_submission']['email_footer']);
        }
    }
    return $value;
}



/* ------------------------------------------------------------------------- *
 *  FIELDS DESIGN
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_simple_form_submission_child() {
    $css_selectors = array( 'form input[type="text"]', 'input[type="text"]:focus', 'form textarea', 'form textarea:focus' );
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_simple_form_submission_child',
        'name' => __( 'Form submission options', 'text_doma' ),
        //'sanitize_callback' => '\Nimble\sanitize_callback__czr_simple_form_module',
        // 'starting_value' => array(
        //     'button_text' => __('Click me','text_doma'),
        //     'color_css'  => '#ffffff',
        //     'bg_color_css' => '#020202',
        //     'bg_color_hover' => '#151515', //lighten 15%,
        //     'use_custom_bg_color_on_hover' => 0,
        //     'border_radius_css' => '2',
        //     'h_alignment_css' => 'center',
        //     'use_box_shadow' => 1,
        //     'push_effect' => 1
        // ),
        'css_selectors' => array( '.sek-module-inner .sek-simple-form-wrapper' ),
        'tmpl' => array(
            'item-inputs' => array(
                'recipients' => array(
                    'input_type'  => 'text',
                    'width-100'         => true,
                    'title'       => __('Email recipient', 'text_doma'),
                    'default'     => get_option( 'admin_email' ),
                    'refresh_preview'  => false,
                    'refresh_markup' => false
                ),
                'success_message' => array(
                    'input_type'  => 'text',
                    'width-100'         => true,
                    'title'       => __( 'Success message on submission' , 'text_doma' ),
                    'title_width' => 'width-100',
                    'default'     => __( 'Thanks! Your message has been sent.', 'text_doma'),
                    'refresh_preview'  => false,
                    'refresh_markup' => false,
                    'notice_before' => __('Tip : replace the default messages with a blank space to not show anything.')
                ),
                'error_message' => array(
                    'input_type'  => 'text',
                    'width-100'         => true,
                    'title'       => __( 'Error message on submission' , 'text_doma' ),
                    'title_width' => 'width-100',
                    'default'     => __( 'Invalid form submission : some fields have not been entered properly.', 'text_doma'),
                    'refresh_preview'  => false,
                    'refresh_markup' => false
                ),
                'failure_message' => array(
                    'input_type'  => 'text',
                    'width-100'         => true,
                    'title'       => __( 'Failure message on submission' , 'text_doma' ),
                    'title_width' => 'width-100',
                    'default'     => __( 'Your message was not sent. Try Again.', 'text_doma'),
                    'refresh_preview'  => false,
                    'refresh_markup' => false
                ),
                'email_footer' => array(
                    'input_type'  => 'code_editor',
                    'title'       => __( 'Email footer' , 'text_doma' ),
                    'notice_before' => __('Html code is allowed', 'text-domain'),
                    'default'     => sprintf( __( 'This e-mail was sent from a contact form on %1$s (<a href="%2$s" target="_blank">%2$s</a>)', 'text_doma' ),
                        get_bloginfo( 'name' ),
                        get_site_url( 'url' )
                    ),
                    'refresh_preview'  => false,
                    'refresh_markup' => false
                ),
                'recaptcha_enabled' => array(
                    'input_type'  => 'simpleselect',
                    'title'       => sprintf( '%s %s',
                        '<i class="material-icons">security</i>',
                        __('Spam protection with Google reCAPTCHA', 'text_doma')
                    ),
                    'title_width' => 'width-100',
                    'width-100'   => true,
                    'default' => 'inherit',
                    'choices'     => array(
                        'inherit' => __('Inherit the global option', 'text_doma'),
                        'disabled' => __('Disable', 'text_doma')
                    ),
                    'refresh_preview'  => false,
                    'refresh_markup' => false,
                    'notice_after' => sprintf( __('Nimble Builder can activate the %1$s service to protect your forms against spam. You need to %2$s.'),
                        sprintf('<a href="%1$s" target="_blank">%2$s</a>', 'https://docs.presscustomizr.com/article/385-how-to-enable-recaptcha-protection-against-spam-in-your-forms-with-the-nimble-builder/?utm_source=usersite&utm_medium=link&utm_campaign=nimble-form-module', __('Google reCAPTCHA', 'text_doma') ),
                        sprintf('<a href="#" onclick="%1$s">%2$s</a>',
                            "javascript:wp.customize.section('__globalOptionsSectionId', function( _s_ ){ _s_.focus(); })",
                            __('activate it in the global settings', 'text_doma')
                        )
                    )
                )
            )
        ),
        'render_tmpl_path' => '',
    );
}







/* ------------------------------------------------------------------------- *
 *  SCHEDULE CSS RULES FILTERING FOR THE FORM MODULE
/* ------------------------------------------------------------------------- */
// FORM MODULE CHILDREN
// 'children' => array(
//       'form_fields'   => 'czr_simple_form_fields_child',
//       'fields_design' => 'czr_simple_form_design_child',
//       'form_button'   => 'czr_simple_form_button_child',
//       'form_fonts'    => 'czr_simple_form_fonts_child'
//   ),
add_filter( 'sek_add_css_rules_for_module_type___czr_simple_form_module', '\Nimble\sek_add_css_rules_for_czr_simple_form_module', 10, 2 );
// filter documented in Sek_Dyn_CSS_Builder::sek_css_rules_sniffer_walker
// Note : $complete_modul_model has been normalized
// @return populated $rules
function sek_add_css_rules_for_czr_simple_form_module( $rules, $complete_modul_model ) {
    if ( empty( $complete_modul_model['value'] ) )
      return $rules;

    $value = $complete_modul_model['value'];

    // BUTTON
    if ( !empty( $value['form_button'] ) && is_array( $value['form_button'] ) ) {
        $form_button_options = $value['form_button'];
        $bg_color = $form_button_options['bg_color_css'];
        if ( sek_booleanize_checkbox_val( $form_button_options['use_custom_bg_color_on_hover'] ) ) {
            $bg_color_hover = $form_button_options['bg_color_hover'];
        } else {
            // Build the lighter rgb from the user picked bg color
            if ( 0 === strpos( $bg_color, 'rgba' ) ) {
                list( $rgb, $alpha ) = sek_rgba2rgb_a( $bg_color );
                $bg_color_hover_rgb  = sek_lighten_rgb( $rgb, $percent=15, $array = true );
                $bg_color_hover      = sek_rgb2rgba( $bg_color_hover_rgb, $alpha, $array = false, $make_prop_value = true );
            } else if ( 0 === strpos( $bg_color, 'rgb' ) ) {
                $bg_color_hover      = sek_lighten_rgb( $bg_color, $percent=15 );
            } else {
                $bg_color_hover      = sek_lighten_hex( $bg_color, $percent=15 );
            }
        }

        $rules[] = array(
            'selector' => '[data-sek-id="'.$complete_modul_model['id'].'"] .sek-module-inner input[type="submit"]:hover',
            'css_rules' => 'background-color:' . $bg_color_hover . ';',
            'mq' =>null
        );

        // BUTTON BORDERS
        $border_settings = $form_button_options[ 'borders' ];
        $border_type = $form_button_options[ 'border-type' ];
        $has_border_settings  = 'none' != $border_type && !empty( $border_type );

        //border width + type + color
        if ( $has_border_settings ) {
            $rules = sek_generate_css_rules_for_multidimensional_border_options(
                $rules,
                $border_settings,
                $border_type,
                '[data-sek-id="'.$complete_modul_model['id'].'"] .sek-module-inner input[type="submit"]'
            );
        }
    }


    // FIELDS BORDERS
    $border_settings = $value[ 'fields_design' ][ 'borders' ];
    $border_type = $value[ 'fields_design' ][ 'border-type' ];
    $has_border_settings  = 'none' != $border_type && !empty( $border_type );

    //border width + type + color
    if ( $has_border_settings ) {
        $selector_list = array( 'form input[type="text"]', 'input[type="text"]:focus', 'form textarea', 'form textarea:focus' );
        $css_selectors = array();
        foreach( $selector_list as $selector ) {
            $css_selectors[] = '[data-sek-id="'.$complete_modul_model['id'].'"] .sek-module-inner' . ' ' . $selector;
        }
        $rules = sek_generate_css_rules_for_multidimensional_border_options(
            $rules,
            $border_settings,
            $border_type,
            implode( ', ', $css_selectors )
        );
    }
    return $rules;
}



?>
<?php
/* ------------------------------------------------------------------------- *
 *  POST GRID MODULE
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_post_grid_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_post_grid_module',
        'name' => __('Post Grid', 'text_doma'),
        'is_father' => true,
        'children' => array(
            'grid_main'   => 'czr_post_grid_main_child',
            'grid_thumb'  => 'czr_post_grid_thumb_child',
            'grid_metas'  => 'czr_post_grid_metas_child',
            'grid_fonts'  => 'czr_post_grid_fonts_child',
        ),
        'render_tmpl_path' => "post_grid_module_tmpl.php"
    );
}


/* ------------------------------------------------------------------------- *
 *  CHILD MAIN GRID SETTINGS
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_post_grid_main_child() {
    $pro_text = '';
    if ( !sek_is_pro() ) {
        $pro_text = sprintf( __( '%1$s + more controls on grid items content, like shadow, background, spacing...', 'text-doma'),
            sprintf( '<a href="%1$s" target="_blank" rel="noopener noreferrer" style="text-decoration:underline">%2$s</a>',
                'https://nimblebuilder.com/post-grid-sections/#pro-grid-one',
                __('masonry grid', 'text-doma')
            )
        );
        $pro_text = sek_get_pro_notice_for_czr_input( $pro_text );
    }
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_post_grid_main_child',
        'name' => __( 'Main grid settings : layout, number of posts, columns,...', 'text_doma' ),
        //'sanitize_callback' => '\Nimble\sanitize_callback__czr_simple_form_module',
        // 'starting_value' => array(
        //     'button_text' => __('Click me','text_doma'),
        //     'color_css'  => '#ffffff',
        //     'bg_color_css' => '#020202',
        //     'bg_color_hover' => '#151515', //lighten 15%,
        //     'use_custom_bg_color_on_hover' => 0,
        //     'border_radius_css' => '2',
        //     'h_alignment_css' => 'center',
        //     'use_box_shadow' => 1,
        //     'push_effect' => 1
        // ),
        'css_selectors' => array( '.sek-module-inner' ),
        'tmpl' => array(
            'item-inputs' => array(
                'use_current_query' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Use contextual WordPress post query', 'text_doma'),
                    'default'     => false,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'notice_after' => __('This option allows you to use the posts normally displayed by WordPress on this page.', 'text_doma')
                    //'html_before' => '<hr>'
                ),
                'replace_query' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Add custom parameters to the contextual WordPress post query', 'text_doma'),
                    'default'     => false,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20'
                    //'html_before' => '<hr>'
                ),
                'display_pagination' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Display pagination links', 'text_doma'),
                    'default'     => false,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20'
                    //'html_before' => '<hr>'
                ),
                'post_number'  => array(
                    'input_type'  => 'range_simple',
                    'title'       => __( 'Number of posts', 'text_doma' ),
                    'default'     => 3,
                    'min'         => 1,
                    'max'         => 50,
                    'step'        => 1,
                    'width-100'   => true
                ),//0,
                'posts_per_page'  => array(
                    'input_type'  => 'range_simple',
                    'title'       => __( 'Posts per page', 'text_doma' ),
                    'default'     => 10,
                    'min'         => 1,
                    'max'         => 50,
                    'step'        => 1,
                    'width-100'   => true,
                    'title_width' => 'width-100'
                ),//0,
                'order_by'  => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __( 'Order posts by', 'text_doma' ),
                    'default'     => 'date_desc',
                    'choices'      => array(
                        'date_desc' => __('Newest to oldest', 'text_doma'),
                        'date_asc' => __('Oldest to newest', 'text_doma'),
                        'title_asc' => __('A &rarr; Z', 'text_doma'),
                        'title_desc' => __('Z &rarr; A', 'text_doma')
                    )
                ),//null,
                'include_sticky' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Include "sticky" posts', 'text_doma'),
                    'default'     => false,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20'
                    //'html_before' => '<hr>'
                ),
                'categories'  => array(
                    'input_type'  => 'category_picker',
                    'title'       => __( 'Filter posts by category', 'text_doma' ),
                    'default'     => array(),
                    'choices'      => array(),
                    'title_width' => 'width-100',
                    'width-100'   => true,
                    'notice_before' => __('Display posts that have these categories. Multiple categories allowed.', 'text_doma')
                ),//null,
                'must_have_all_cats' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Display posts that have "all" of these categories', 'text_doma'),
                    'default'     => false,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20'
                    //'html_before' => '<hr>'
                ),
                'layout'  => array(
                    'input_type'  => 'grid_layout',
                    'title'       => __( 'Posts layout : list or grid', 'text_doma' ),
                    'default'     => 'list',
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'html_before' => '<hr>',
                    'refresh_stylesheet' => true, //<= some CSS rules are layout dependant
                    'html_before' => $pro_text
                ),//null,
                'columns'  => array(
                    'input_type'  => 'range_simple_device_switcher',
                    'title'       => __( 'Number of columns', 'text_doma' ),
                    'default'     => array( 'desktop' => '2', 'tablet' => '2', 'mobile' => '1' ),
                    'min'         => 1,
                    'max'         => 12,
                    'step'        => 1,
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'refresh_stylesheet' => true //<= some CSS rules are layout dependant
                ),//null,
                'img_column_width' => array(
                    'input_type'  => 'range_simple_device_switcher',
                    'title'       => __( 'Width of the image\'s column (in percent)', 'text_doma' ),
                    'default'     => array( 'desktop' => '30' ),
                    'min'         => 1,
                    'max'         => 100,
                    'step'        => 1,
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true
                ),//null,
                'has_tablet_breakpoint' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => '<i class="material-icons sek-input-title-icon">tablet_mac</i>' . __('Reorganize image and content vertically on tablet devices', 'text_doma'),
                    'default'     => false,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'refresh_stylesheet' => true
                    //'html_before' => '<hr>'
                ),
                'has_mobile_breakpoint' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => '<i class="material-icons sek-input-title-icon">phone_iphone</i>' . __('Reorganize image and content vertically on smartphones devices', 'text_doma'),
                    'default'     => true,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'refresh_stylesheet' => true
                ),

                'show_title' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Display the post title', 'text_doma'),
                    'default'     => true,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'html_before' => '<hr>'
                ),
                'show_excerpt' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Display the post excerpt', 'text_doma'),
                    'default'     => true,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                ),
                'excerpt_length'  => array(
                    'input_type'  => 'range_simple',
                    'title'       => __( 'Excerpt length in words', 'text_doma' ),
                    'default'     => 20,
                    'min'         => 1,
                    'max'         => 50,
                    'step'        => 1,
                    'width-100'   => true,
                    'title_width' => 'width-100',
                ),//0,
                'space_between_el' => array(
                    'input_type'  => 'range_with_unit_picker_device_switcher',
                    'title'       => __('Space between text blocks', 'text_doma'),
                    'min' => 1,
                    'max' => 100,
                    //'unit' => 'px',
                    'default' => array( 'desktop' => '10px' ),
                    'width-100'   => true,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'title_width' => 'width-100'
                ),

                'pg_alignment_css' => array(
                    'input_type'  => 'horizTextAlignmentWithDeviceSwitcher',
                    'title'       => __('Text blocks alignment', 'text_doma'),
                    'default'     => array( 'desktop' => is_rtl() ? 'right' : 'left' ),
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'h_alignment',
                    'title_width' => 'width-100',
                    'width-100'   => true,
                    'css_selectors' => array( '.sek-post-grid-wrapper .sek-pg-content' ),
                    'html_before' => '<hr>'
                ),

                'content_padding' => array(
                    'input_type'  => 'range_with_unit_picker_device_switcher',
                    'title'       => __('Content blocks padding', 'text_doma'),
                    'min' => 0,
                    'max' => 100,
                    //'unit' => 'px',
                    'default' => array( 'desktop' => '0px' ),
                    'width-100'   => true,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'title_width' => 'width-100'
                ),

                'custom_grid_spaces' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Define custom spaces between columns and rows', 'text_doma'),
                    'default'     => false,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'refresh_stylesheet' => true,
                    'html_before' => '<hr>'
                ),
                'column_gap'  => array(
                    'input_type'  => 'range_with_unit_picker_device_switcher',
                    'title'       => __( 'Space between columns', 'text_doma' ),
                    'min' => 0,
                    'max' => 100,
                    'default'     => array( 'desktop' => '20px' ),
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true
                ),//null,

                'row_gap'  => array(
                    'input_type'  => 'range_with_unit_picker_device_switcher',
                    'title'       => __( 'Space between rows', 'text_doma' ),
                    'min' => 0,
                    'max' => 100,
                    'default'     => array( 'desktop' => '25px' ),
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true
                ),//null,
                'apply_shadow_on_hover' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Apply a shadow effect when hovering with the cursor', 'text_doma'),
                    'default'     => false,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'html_before' => '<hr/>'
                )
            )
        ),
        'render_tmpl_path' => '',
    );
}

/* ------------------------------------------------------------------------- *
 *  CHILD IMG SETTINGS
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_post_grid_thumb_child() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_post_grid_thumb_child',
        'name' => __( 'Post thumbnail settings : size, design...', 'text_doma' ),
        //'sanitize_callback' => '\Nimble\sanitize_callback__czr_simple_form_module',
        // 'starting_value' => array(
        //     'button_text' => __('Click me','text_doma'),
        //     'color_css'  => '#ffffff',
        //     'bg_color_css' => '#020202',
        //     'bg_color_hover' => '#151515', //lighten 15%,
        //     'use_custom_bg_color_on_hover' => 0,
        //     'border_radius_css' => '2',
        //     'h_alignment_css' => 'center',
        //     'use_box_shadow' => 1,
        //     'push_effect' => 1
        // ),
        'css_selectors' => array( '.sek-module-inner' ),
        'tmpl' => array(
            'item-inputs' => array(
                // IMAGE
                'show_thumb' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Display post thumbnail', 'text_doma'),
                    'default'     => true,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'refresh_stylesheet' => true,
                    'notice_after' => __('The post thumbnail can be set as "Featured image" when creating a post.', 'text_doma')
                ),
                'img_size' => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __('Select the source image size of the thumbnail', 'text_doma'),
                    'title_width' => 'width-100',
                    'default'     => 'medium_large',
                    'choices'     => sek_get_select_options_for_input_id( 'img-size' ),
                    'notice_before' => __('This allows you to select a preferred image size among those generated by WordPress.', 'text_doma' ),
                    'notice_after' => __('Note that Nimble Builder will let browsers choose the most appropriate size for better performances.', 'text_doma' )
                ),
                'img_has_custom_height' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Apply a custom height to the thumbnail', 'text_doma'),
                    'default'     => true,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'refresh_stylesheet' => true,
                    'html_before' => '<hr>'
                ),
                'img_height' => array(
                    'input_type'  => 'range_simple_device_switcher',
                    'title'       => __( 'Thumbnail height', 'text_doma' ),
                    'default'     =>  array( 'desktop' => '65' ),
                    'min'         => 1,
                    'max'         => 300,
                    'step'        => 1,
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'notice_before' => __('Tip : the height is in percent of the image container\'s width. Applying a height of 100% makes the image square.')
                ),//null,
                'border_radius_css'       => array(
                    'input_type'  => 'border_radius',
                    'title'       => __( 'Rounded corners', 'text_doma' ),
                    'default' => array( '_all_' => '4px' ),
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'min'         => 0,
                    'max'         => 500,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'border_radius',
                    'css_selectors'=> '.sek-pg-thumbnail img'
                ),
                'use_post_thumb_placeholder' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Use a placeholder image when no post thumbnail is set', 'text_doma'),
                    'default'     => true,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                )
            )
        ),
        'render_tmpl_path' => '',
    );
}


/* ------------------------------------------------------------------------- *
 *  CHILD POST METAS
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_post_grid_metas_child() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_post_grid_metas_child',
        'name' => __( 'Post metas : author, date, category, ...', 'text_doma' ),
        //'sanitize_callback' => '\Nimble\sanitize_callback__czr_simple_form_module',
        // 'starting_value' => array(
        //     'button_text' => __('Click me','text_doma'),
        //     'color_css'  => '#ffffff',
        //     'bg_color_css' => '#020202',
        //     'bg_color_hover' => '#151515', //lighten 15%,
        //     'use_custom_bg_color_on_hover' => 0,
        //     'border_radius_css' => '2',
        //     'h_alignment_css' => 'center',
        //     'use_box_shadow' => 1,
        //     'push_effect' => 1
        // ),
        'css_selectors' => array( '.sek-module-inner' ),
        'tmpl' => array(
            'item-inputs' => array(
                'show_cats' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Display categories', 'text_doma'),
                    'default'     => false,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                ),
                'show_author' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Display author', 'text_doma'),
                    'default'     => true,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                ),
                'show_date' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Display date', 'text_doma'),
                    'default'     => true,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                ),
                'show_comments' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Display comment number', 'text_doma'),
                    'default'     => false,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                ),
            )
        ),
        'render_tmpl_path' => '',
    );
}








/* ------------------------------------------------------------------------- *
 *  FONTS
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_post_grid_fonts_child() {
    $pt_font_selectors = array( '.sek-module-inner .sek-post-grid-wrapper .sek-pg-title a', '.sek-module-inner .sek-post-grid-wrapper .sek-pg-title' );
    $pe_font_selectors = array( '.sek-module-inner  .sek-post-grid-wrapper .sek-excerpt', '.sek-module-inner  .sek-post-grid-wrapper .sek-excerpt *' );
    $cat_font_selectors = array( '.sek-module-inner .sek-pg-category', '.sek-module-inner .sek-pg-category a' );
    $metas_font_selectors = array( '.sek-module-inner .sek-pg-metas', '.sek-module-inner .sek-pg-metas span', '.sek-module-inner .sek-pg-metas a');
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_post_grid_fonts_child',
        'name' => __( 'Grid text settings : fonts, colors, ...', 'text_doma' ),
        //'sanitize_callback' => '\Nimble\sanitize_callback__czr_simple_form_module',
        // 'starting_value' => array(
        // ),
        'css_selectors' => array( '.sek-module-inner .sek-post-grid-wrapper' ),
        'tmpl' => array(
            'item-inputs' => array(
                'tabs' => array(
                    array(
                        'title' => __( 'Post titles', 'text_doma' ),
                        'inputs' => array(
                            'pt_font_family_css' => array(
                                'input_type'  => 'font_picker',
                                'title'       => __( 'Font family', 'text_doma' ),
                                'default'     => '',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'refresh_fonts' => true,
                                'css_identifier' => 'font_family',
                                'css_selectors' => $pt_font_selectors,
                                'html_after' => sprintf('<span class="czr-notice"><i class="far fa-lightbulb"></i> %1s => <a href="%2s" target="_blank" rel="noopener noreferrer">%2$s</a></span><hr/>',
                                    __('Find inspiration'),
                                    'https://fonts.google.com/?sort=popularity'
                                )
                            ),
                            'pt_font_size_css'       => array(
                                'input_type'  => 'range_with_unit_picker_device_switcher',
                                'default'     => array( 'desktop' => '28px', 'tablet' => '22px', 'mobile' => '20px' ),
                                'title_width' => 'width-100',
                                'title'       => __( 'Font size', 'text_doma' ),
                                'min' => 0,
                                'max' => 100,
                                'width-100'         => true,
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'font_size',
                                'css_selectors' => $pt_font_selectors,
                            ),//16,//"14px",
                            'pt_line_height_css'     => array(
                                'input_type'  => 'range_with_unit_picker',
                                'title'       => __( 'Line height', 'text_doma' ),
                                'default'     => '1.3em',
                                'min' => 0,
                                'max' => 10,
                                'step' => 0.1,
                                'width-100'         => true,
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'line_height',
                                'css_selectors' => $pt_font_selectors,
                            ),//24,//"20px",
                            'pt_color_css'           => array(
                                'input_type'  => 'wp_color_alpha',
                                'title'       => __( 'Text color', 'text_doma' ),
                                'default'     => '#121212',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'width-100'   => true,
                                'css_identifier' => 'color',
                                'css_selectors' => $pt_font_selectors,
                            ),//"#000000",
                            'pt_color_hover_css'     => array(
                                'input_type'  => 'wp_color_alpha',
                                'title'       => __( 'Text color on mouse over', 'text_doma' ),
                                'default'     => '#666',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'width-100'   => true,
                                'title_width' => 'width-100',
                                'css_identifier' => 'color_hover',
                                'css_selectors' => $pt_font_selectors,
                            ),//"#000000",
                            'pt_font_weight_css'     => array(
                                'input_type'  => 'simpleselect',
                                'title'       => __( 'Font weight', 'text_doma' ),
                                'default'     => 400,
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'font_weight',
                                'css_selectors' => $pt_font_selectors,
                                'choices'            => sek_get_select_options_for_input_id( 'font_weight_css' )
                            ),//null,
                            'pt_font_style_css'      => array(
                                'input_type'  => 'simpleselect',
                                'title'       => __( 'Font style', 'text_doma' ),
                                'default'     => 'inherit',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'font_style',
                                'css_selectors' => $pt_font_selectors,
                                'choices'            => sek_get_select_options_for_input_id( 'font_style_css' )
                            ),//null,
                            'pt_text_transform_css'  => array(
                                'input_type'  => 'simpleselect',
                                'title'       => __( 'Text transform', 'text_doma' ),
                                'default'     => 'none',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'text_transform',
                                'css_selectors' => $pt_font_selectors,
                                'choices'    => sek_get_select_options_for_input_id( 'text_transform_css' )
                            )
                        )
                    ),
                    array(
                        'title' => __( 'Excerpt', 'text_doma' ),
                        'inputs' => array(
                            'pe_font_family_css' => array(
                                'input_type'  => 'font_picker',
                                'title'       => __( 'Font family', 'text_doma' ),
                                'default'     => '',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'refresh_fonts' => true,
                                'css_identifier' => 'font_family',
                                'css_selectors' => $pe_font_selectors,
                                'html_after' => sprintf('<span class="czr-notice"><i class="far fa-lightbulb"></i> %1s => <a href="%2s" target="_blank" rel="noopener noreferrer">%2$s</a></span><hr/>',
                                    __('Find inspiration'),
                                    'https://fonts.google.com/?sort=popularity'
                                )
                            ),
                            'pe_font_size_css'       => array(
                                'input_type'  => 'range_with_unit_picker_device_switcher',
                                'default'     => array( 'desktop' => '14px' ),
                                'title_width' => 'width-100',
                                'title'       => __( 'Font size', 'text_doma' ),
                                'min' => 0,
                                'max' => 100,
                                'width-100'         => true,
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'font_size',
                                'css_selectors' => $pe_font_selectors,
                            ),//16,//"14px",
                            'pe_line_height_css'     => array(
                                'input_type'  => 'range_with_unit_picker',
                                'title'       => __( 'Line height', 'text_doma' ),
                                'default'     => '1.3em',
                                'min' => 0,
                                'max' => 10,
                                'step' => 0.1,
                                'width-100'         => true,
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'line_height',
                                'css_selectors' => $pe_font_selectors,
                            ),//24,//"20px",
                            'pe_color_css'           => array(
                                'input_type'  => 'wp_color_alpha',
                                'title'       => __( 'Text color', 'text_doma' ),
                                'default'     => '#555',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'width-100'   => true,
                                'css_identifier' => 'color',
                                'css_selectors' => $pe_font_selectors,
                            ),//"#000000",
                            'pe_color_hover_css'     => array(
                                'input_type'  => 'wp_color_alpha',
                                'title'       => __( 'Text color on mouse over', 'text_doma' ),
                                'default'     => '',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'width-100'   => true,
                                'title_width' => 'width-100',
                                'css_identifier' => 'color_hover',
                                'css_selectors' => $pe_font_selectors,
                            ),//"#000000",
                            'pe_font_weight_css'     => array(
                                'input_type'  => 'simpleselect',
                                'title'       => __( 'Font weight', 'text_doma' ),
                                'default'     => 'normal',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'font_weight',
                                'css_selectors' => $pe_font_selectors,
                                'choices'            => sek_get_select_options_for_input_id( 'font_weight_css' )
                            ),//null,
                            'pe_font_style_css'      => array(
                                'input_type'  => 'simpleselect',
                                'title'       => __( 'Font style', 'text_doma' ),
                                'default'     => 'inherit',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'font_style',
                                'css_selectors' => $pe_font_selectors,
                                'choices'       => sek_get_select_options_for_input_id( 'font_style_css' )
                            ),
                            'pe_text_transform_css'  => array(
                                'input_type'  => 'simpleselect',
                                'title'       => __( 'Text transform', 'text_doma' ),
                                'default'     => 'none',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'text_transform',
                                'css_selectors' => $pe_font_selectors,
                                'choices'            => sek_get_select_options_for_input_id( 'text_transform_css' )
                            )
                        ),//inputs
                    ),//tab
                    array(
                        'title' => __( 'Categories', 'text_doma' ),
                        'inputs' => array(
                            'cat_font_family_css' => array(
                                'input_type'  => 'font_picker',
                                'title'       => __( 'Font family', 'text_doma' ),
                                'default'     => '',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'refresh_fonts' => true,
                                'css_identifier' => 'font_family',
                                'css_selectors' => $cat_font_selectors,
                                'html_after' => sprintf('<span class="czr-notice"><i class="far fa-lightbulb"></i> %1s => <a href="%2s" target="_blank" rel="noopener noreferrer">%2$s</a></span><hr/>',
                                    __('Find inspiration'),
                                    'https://fonts.google.com/?sort=popularity'
                                )
                            ),
                            'cat_font_size_css'       => array(
                                'input_type'  => 'range_with_unit_picker_device_switcher',
                                'default'     => array( 'desktop' => '13px' ),
                                'title_width' => 'width-100',
                                'title'       => __( 'Font size', 'text_doma' ),
                                'min' => 0,
                                'max' => 100,
                                'width-100'         => true,
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'font_size',
                                'css_selectors' => $cat_font_selectors,
                            ),//16,//"14px",
                            'cat_line_height_css'     => array(
                                'input_type'  => 'range_with_unit_picker',
                                'title'       => __( 'Line height', 'text_doma' ),
                                'default'     => '1.2em',
                                'min' => 0,
                                'max' => 10,
                                'step' => 0.1,
                                'width-100'         => true,
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'line_height',
                                'css_selectors' => $cat_font_selectors,
                            ),//24,//"20px",
                            'cat_color_css'           => array(
                                'input_type'  => 'wp_color_alpha',
                                'title'       => __( 'Text color', 'text_doma' ),
                                'default'     => '#767676',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'width-100'   => true,
                                'css_identifier' => 'color',
                                'css_selectors' => $cat_font_selectors,
                            ),//"#000000",
                            'cat_color_hover_css'     => array(
                                'input_type'  => 'wp_color_alpha',
                                'title'       => __( 'Text color on mouse over', 'text_doma' ),
                                'default'     => '',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'width-100'   => true,
                                'title_width' => 'width-100',
                                'css_identifier' => 'color_hover',
                                'css_selectors' => $cat_font_selectors,
                            ),//"#000000",
                            'cat_font_weight_css'     => array(
                                'input_type'  => 'simpleselect',
                                'title'       => __( 'Font weight', 'text_doma' ),
                                'default'     => 'normal',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'font_weight',
                                'css_selectors' => $cat_font_selectors,
                                'choices'            => sek_get_select_options_for_input_id( 'font_weight_css' )
                            ),//null,
                            'cat_font_style_css'      => array(
                                'input_type'  => 'simpleselect',
                                'title'       => __( 'Font style', 'text_doma' ),
                                'default'     => 'inherit',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'font_style',
                                'css_selectors' => $cat_font_selectors,
                                'choices'       => sek_get_select_options_for_input_id( 'font_style_css' )
                            ),
                            'cat_text_transform_css'  => array(
                                'input_type'  => 'simpleselect',
                                'title'       => __( 'Text transform', 'text_doma' ),
                                'default'     => 'uppercase',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'text_transform',
                                'css_selectors' => $cat_font_selectors,
                                'choices'            => sek_get_select_options_for_input_id( 'text_transform_css' )
                            )
                        ),//inputs
                    ),//tab
                    array(
                        'title' => __( 'Metas', 'text_doma' ),
                        'inputs' => array(
                            'met_font_family_css' => array(
                                'input_type'  => 'font_picker',
                                'title'       => __( 'Font family', 'text_doma' ),
                                'default'     => '',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'refresh_fonts' => true,
                                'css_identifier' => 'font_family',
                                'css_selectors' => $metas_font_selectors,
                                'html_after' => sprintf('<span class="czr-notice"><i class="far fa-lightbulb"></i> %1s => <a href="%2s" target="_blank" rel="noopener noreferrer">%2$s</a></span><hr/>',
                                    __('Find inspiration'),
                                    'https://fonts.google.com/?sort=popularity'
                                )
                            ),
                            'met_font_size_css'       => array(
                                'input_type'  => 'range_with_unit_picker_device_switcher',
                                'default'     => array( 'desktop' => '13px' ),
                                'title_width' => 'width-100',
                                'title'       => __( 'Font size', 'text_doma' ),
                                'min' => 0,
                                'max' => 100,
                                'width-100'         => true,
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'font_size',
                                'css_selectors' => $metas_font_selectors,
                            ),//16,//"14px",
                            'met_line_height_css'     => array(
                                'input_type'  => 'range_with_unit_picker',
                                'title'       => __( 'Line height', 'text_doma' ),
                                'default'     => '1.2em',
                                'min' => 0,
                                'max' => 10,
                                'step' => 0.1,
                                'width-100'         => true,
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'line_height',
                                'css_selectors' => $metas_font_selectors,
                            ),//24,//"20px",
                            'met_color_css'           => array(
                                'input_type'  => 'wp_color_alpha',
                                'title'       => __( 'Text color', 'text_doma' ),
                                'default'     => '#767676',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'width-100'   => true,
                                'css_identifier' => 'color',
                                'css_selectors' => $metas_font_selectors,
                            ),//"#000000",
                            'met_color_hover_css'     => array(
                                'input_type'  => 'wp_color_alpha',
                                'title'       => __( 'Text color on mouse over', 'text_doma' ),
                                'default'     => '',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'width-100'   => true,
                                'title_width' => 'width-100',
                                'css_identifier' => 'color_hover',
                                'css_selectors' => $metas_font_selectors,
                            ),//"#000000",
                            'met_font_weight_css'     => array(
                                'input_type'  => 'simpleselect',
                                'title'       => __( 'Font weight', 'text_doma' ),
                                'default'     => 'normal',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'font_weight',
                                'css_selectors' => $metas_font_selectors,
                                'choices'            => sek_get_select_options_for_input_id( 'font_weight_css' )
                            ),//null,
                            'met_font_style_css'      => array(
                                'input_type'  => 'simpleselect',
                                'title'       => __( 'Font style', 'text_doma' ),
                                'default'     => 'inherit',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'font_style',
                                'css_selectors' => $metas_font_selectors,
                                'choices'       => sek_get_select_options_for_input_id( 'font_style_css' )
                            ),
                            'met_text_transform_css'  => array(
                                'input_type'  => 'simpleselect',
                                'title'       => __( 'Text transform', 'text_doma' ),
                                'default'     => 'uppercase',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'text_transform',
                                'css_selectors' => $metas_font_selectors,
                                'choices'            => sek_get_select_options_for_input_id( 'text_transform_css' )
                            )
                        ),//inputs
                    )//tab
                )//tabs
            )//item-inputs
        ),//tmpl
        'render_tmpl_path' => '',
    );
}



/* ------------------------------------------------------------------------- *
 *  SCHEDULE CSS RULES FILTERING
/* ------------------------------------------------------------------------- */
add_filter( 'sek_add_css_rules_for_module_type___czr_post_grid_module', '\Nimble\sek_add_css_rules_for_czr_post_grid_module', 10, 2 );
// filter documented in Sek_Dyn_CSS_Builder::sek_css_rules_sniffer_walker
// Note : $complete_modul_model has been normalized
// @return populated $rules
function sek_add_css_rules_for_czr_post_grid_module( $rules, $complete_modul_model ) {
    if ( empty( $complete_modul_model['value'] ) )
      return $rules;

    $value = $complete_modul_model['value'];
    $main_settings = $value['grid_main'];
    $thumb_settings = $value['grid_thumb'];


    // SPACE BETWEEN CONTENT ELEMENTS
    $margin_bottom = $main_settings['space_between_el'];
    $margin_bottom = is_array( $margin_bottom ) ? $margin_bottom : array();
    $defaults = array(
        'desktop' => '10px',// <= this value matches the static CSS rule and the input default for the module
        'tablet' => '',
        'mobile' => ''
    );
    $margin_bottom = wp_parse_args( $margin_bottom, $defaults );
    $margin_bottom_ready_val = $margin_bottom;
    foreach ($margin_bottom as $device => $num_unit ) {
        $num_val = sek_extract_numeric_value( $num_unit );
        $margin_bottom_ready_val[$device] = '';
        // Leave the device value empty if === to default
        // Otherwise it will print a duplicated dynamic css rules, already hardcoded in the static stylesheet
        // fixes https://github.com/presscustomizr/nimble-builder/issues/419
        if ( !empty( $num_unit ) && $num_val.'px' !== $defaults[$device].'' ) {
            $unit = sek_extract_unit( $num_unit );
            $num_val = $num_val < 0 ? 0 : $num_val;
            $margin_bottom_ready_val[$device] = $num_val . $unit;
        }
    }
    $rules = sek_set_mq_css_rules( array(
        'value' => $margin_bottom_ready_val,
        'css_property' => 'margin-bottom',
        'selector' => implode(',', array(
            '[data-sek-id="'.$complete_modul_model['id'].'"] .sek-post-grid-wrapper .sek-grid-items article .sek-pg-content > *:not(:last-child)',
            '[data-sek-id="'.$complete_modul_model['id'].'"] .sek-post-grid-wrapper .sek-grid-items.sek-list-layout article > *:not(:last-child):not(.sek-pg-thumbnail)',
            '[data-sek-id="'.$complete_modul_model['id'].'"] .sek-post-grid-wrapper .sek-grid-items.sek-grid-layout article > *:not(:last-child)'
        )),
        'is_important' => false,
        'level_id' => $complete_modul_model['id']
    ), $rules );



    // CONTENT BLOCKS PADDING
    $content_padding = $main_settings['content_padding'];
    $content_padding = is_array( $content_padding ) ? $content_padding : array();
    $defaults = array(
        'desktop' => '0px',// <= this value matches the static CSS rule and the input default for the module
        'tablet' => '',
        'mobile' => ''
    );
    $content_padding = wp_parse_args( $content_padding, $defaults );
    $content_padding_ready_val = $content_padding;
    foreach ($content_padding as $device => $num_unit ) {
        $num_val = sek_extract_numeric_value( $num_unit );
        $content_padding_ready_val[$device] = '';
        // Leave the device value empty if === to default
        // Otherwise it will print a duplicated dynamic css rules, already hardcoded in the static stylesheet
        // fixes https://github.com/presscustomizr/nimble-builder/issues/419
        if ( !empty( $num_unit ) && $num_val.'px' !== $defaults[$device].'' ) {
            $unit = sek_extract_unit( $num_unit );
            $num_val = $num_val < 0 ? 0 : $num_val;
            $content_padding_ready_val[$device] = $num_val . $unit;
        }
    }
    $rules = sek_set_mq_css_rules( array(
        'value' => $content_padding_ready_val,
        'css_property' => 'padding',
        'selector' => '[data-sek-id="'.$complete_modul_model['id'].'"] .sek-post-grid-wrapper .sek-grid-items article .sek-pg-content',
        'is_important' => false,
        'level_id' => $complete_modul_model['id']
    ), $rules );



    // IMG COLUMN WIDTH IN LIST
    // - only relevant when the thumbnail is displayed
    // - default value is array( 'desktop' => '30' )
    // - default css is .sek-list-layout article.sek-has-thumb { grid-template-columns: 30% minmax(0,1fr); }
    if ( 'list' === $main_settings['layout'] && true === sek_booleanize_checkbox_val( $thumb_settings['show_thumb'] ) ) {
        $img_column_width = $main_settings['img_column_width'];
        $img_column_width = is_array( $img_column_width ) ? $img_column_width : array();
        $defaults = array(
            'desktop' => '30%',// <= this value matches the static CSS rule and the input default for the module
            'tablet' => '',
            'mobile' => ''
        );
        $img_column_width = wp_parse_args( $img_column_width, $defaults );
        $img_column_width_ready_value = $img_column_width;
        foreach ($img_column_width as $device => $num_val ) {
            $num_val = sek_extract_numeric_value( $num_val );
            $img_column_width_ready_value[$device] = '';
            // Leave the device value empty if === to default
            // Otherwise it will print a duplicated dynamic css rules, already hardcoded in the static stylesheet
            // fixes https://github.com/presscustomizr/nimble-builder/issues/419
            if ( !empty( $num_val ) && $num_val.'%' !== $defaults[$device].'' ) {
                $num_val = $num_val > 100 ? 100 : $num_val;
                $num_val = $num_val < 1 ? 1 : $num_val;
                $img_column_width_ready_value[$device] = sprintf('%s minmax(0,1fr);', $num_val . '%');
            }
        }

        $rules = sek_set_mq_css_rules(array(
            'value' => $img_column_width_ready_value,
            'css_property' => array( 'grid-template-columns', '-ms-grid-columns' ),
            'selector' => '[data-sek-id="'.$complete_modul_model['id'].'"] .sek-post-grid-wrapper .sek-list-layout article.sek-has-thumb',
            'is_important' => false,
            'level_id' => $complete_modul_model['id']
        ), $rules );
    }

    // IMG HEIGHT
    // we set the height of the image container ( <a> tag ), with the padding property
    // because padding and margin are relative to the width in CSS
    // @see https://www.w3.org/TR/2011/REC-CSS2-20110607/box.html#padding-properties
    if ( true === sek_booleanize_checkbox_val( $thumb_settings['img_has_custom_height'] ) ) {
        $img_height = $thumb_settings['img_height'];
        $img_height = is_array( $img_height ) ? $img_height : array();
        $defaults = array(
            'desktop' => '65%',// <= this value matches the static CSS rule and the input default for the module
            'tablet' => '',
            'mobile' => ''
        );
        $img_height = wp_parse_args( $img_height, $defaults );

        $img_height_ready_value = $img_height;
        foreach ( $img_height as $device => $num_val ) {
            $num_val = sek_extract_numeric_value( $num_val );
            $img_height_ready_value[$device] = '';
            // Leave the device value empty if === to default
            // Otherwise it will print a duplicated dynamic css rules, already hardcoded in the static stylesheet
            // fixes https://github.com/presscustomizr/nimble-builder/issues/419
            if ( !empty( $num_val ) && $num_val.'%' !== $defaults[$device].'' ) {
                $num_val = $num_val < 1 ? 1 : $num_val;
                $img_height_ready_value[$device] = sprintf('%s;', $num_val .'%');
            }
        }
        $rules = sek_set_mq_css_rules(array(
            'value' => $img_height_ready_value,
            'css_property' => 'padding-top',
            'selector' => '[data-sek-id="'.$complete_modul_model['id'].'"] .sek-post-grid-wrapper .sek-thumb-custom-height figure a',
            'is_important' => false,
            'level_id' => $complete_modul_model['id']
        ), $rules );
    }


    // COLUMN AND ROW GAP
    if ( true === sek_booleanize_checkbox_val( $main_settings['custom_grid_spaces'] ) ) {
          // Horizontal Gap
          $gap = $main_settings['column_gap'];
          $gap = is_array( $gap ) ? $gap : array();
          $defaults = array(
              'desktop' => '20px',// <= this value matches the static CSS rule and the input default for the module
              'tablet' => '',
              'mobile' => ''
          );
          $gap = wp_parse_args( $gap, $defaults );
          // replace % by vh when needed
          $gap_ready_value = $gap;
          foreach ($gap as $device => $num_unit ) {
              $numeric = sek_extract_numeric_value( $num_unit );
              $numeric = $numeric < 0 ? '0' : $numeric;
              $gap_ready_value[$device] = '';
              // Leave the device value empty if === to default
              // Otherwise it will print a duplicated dynamic css rules, already hardcoded in the static stylesheet
              // fixes https://github.com/presscustomizr/nimble-builder/issues/419
              //if ( !empty( $num_unit ) && $numeric.'px' !== $defaults[$device].'' ) {
              if ( !empty( $num_unit ) ) {
                  $unit = sek_extract_unit( $num_unit );
                  $gap_ready_value[$device] = $numeric . $unit;
              }
          }

          // for grid layout => gap between columns
          // for list layout => gap between image and content
          $rules = sek_set_mq_css_rules(array(
              'value' => $gap_ready_value,
              'css_property' => 'grid-column-gap',
              'selector' => implode( ',', [
                  '.nb-loc [data-sek-id="'.$complete_modul_model['id'].'"] .sek-module-inner .sek-post-grid-wrapper .sek-grid-layout',
                  '.nb-loc [data-sek-id="'.$complete_modul_model['id'].'"] .sek-module-inner .sek-post-grid-wrapper .sek-list-layout article.sek-has-thumb'
              ] ),
              'is_important' => false,
              'level_id' => $complete_modul_model['id']
          ), $rules );

          // Vertical Gap => common to list and grid layout
          $v_gap = $main_settings['row_gap'];
          $v_gap = is_array( $v_gap ) ? $v_gap : array();
          $defaults = array(
              'desktop' => '25px',// <= this value matches the static CSS rule and the input default for the module
              'tablet' => '',
              'mobile' => ''
          );
          $v_gap = wp_parse_args( $v_gap, $defaults );
          // replace % by vh when needed
          $v_gap_ready_value = $v_gap;
          foreach ($v_gap as $device => $num_unit ) {
              $numeric = sek_extract_numeric_value( $num_unit );
              $numeric = $numeric < 0 ? 0 : $numeric;
              $v_gap_ready_value[$device] = '';
              // Leave the device value empty if === to default
              // Otherwise it will print a duplicated dynamic css rules, already hardcoded in the static stylesheet
              // fixes https://github.com/presscustomizr/nimble-builder/issues/419
              //if ( !empty( $num_unit ) && $numeric.'px' !== $defaults[$device].'' ) {
              if ( !empty( $num_unit ) ) {
                  $unit = sek_extract_unit( $num_unit );
                  $v_gap_ready_value[$device] = $numeric . $unit;
              }
          }

          $rules = sek_set_mq_css_rules(array(
              'value' => $v_gap_ready_value,
              'css_property' => 'grid-row-gap',
              'selector' => '.nb-loc [data-sek-id="'.$complete_modul_model['id'].'"] .sek-module-inner .sek-post-grid-wrapper .sek-grid-items',
              'is_important' => false,
              'level_id' => $complete_modul_model['id']
          ), $rules );
    }


    // TABLET AND MOBILE BREAKPOINT SETUP
    $mobile_breakpoint = Sek_Dyn_CSS_Builder::$breakpoints['sm'];// 576
    $tablet_breakpoint = Sek_Dyn_CSS_Builder::$breakpoints['md'];// 768

    $custom_tablet_breakpoint = $tablet_breakpoint;

    // Is there a global custom breakpoint set ?
    $global_custom_breakpoint = intval( sek_get_global_custom_breakpoint() );
    $has_global_custom_breakpoint = $global_custom_breakpoint >= 1;
    // Does the parent section have a custom breakpoint set ?
    $section_custom_breakpoint = intval( sek_get_closest_section_custom_breakpoint( array( 'searched_level_id' => $complete_modul_model['id'] ) ) );
    $has_section_custom_breakpoint = $section_custom_breakpoint >= 1;

    // Use section breakpoint in priority, then global one
    if ( $has_section_custom_breakpoint ) {
        $custom_tablet_breakpoint = $section_custom_breakpoint;
    } else if ( $has_global_custom_breakpoint ) {
        $custom_tablet_breakpoint = $global_custom_breakpoint;
    }

    $tablet_breakpoint = $custom_tablet_breakpoint;
    // If user define breakpoint ( => always for tablet ) is < to $mobile_breakpoint, make sure $mobile_breakpoint is reset to tablet_breakpoint
    $mobile_breakpoint = $mobile_breakpoint >= $tablet_breakpoint ? $tablet_breakpoint : $mobile_breakpoint;

    $tab_bp_val = $tablet_breakpoint - 1;// -1 to avoid "blind" spots @see https://github.com/presscustomizr/nimble-builder/issues/551
    $mob_bp_val = $mobile_breakpoint - 1;// -1 to avoid "blind" spots @see https://github.com/presscustomizr/nimble-builder/issues/551


    // GRID LAYOUT
    // NUMBER OF COLUMNS BY DEVICE IN CASE OF A CUSTOM BREAKPOINT, GLOBAL OR FOR THE SECTION
    // Get the default breakpoint values
    if ( 'grid' === $main_settings['layout'] ) {
        // BASE CSS RULES
        // .sek-grid-layout.sek-all-col-1 {
        //   -ms-grid-columns: minmax(0,1fr);
        //   grid-template-columns: repeat(1, minmax(0,1fr));
        // }
        // .sek-grid-layout.sek-all-col-2 {
        //   -ms-grid-columns: minmax(0,1fr) 20px minmax(0,1fr);
        //   grid-template-columns: repeat(2, minmax(0,1fr));
        //   grid-column-gap: 20px;
        //   grid-row-gap: 20px;
        // }
        $col_nb_gap_map = [
            'col-1' => null,
            'col-2' => '20px',
            'col-3' => '15px',
            'col-4' => '15px',
            'col-5' => '10px',
            'col-6' => '10px',
            'col-7' => '10px',
            'col-8' => '10px',
            'col-9' => '10px',
            'col-10' => '5px',
            'col-11' => '5px',
            'col-12' => '5px'
        ];
        if ( !isset(Nimble_Manager()->generic_post_grid_css_rules_written) ) {
            foreach ($col_nb_gap_map as $col_nb_index => $col_gap) {
                $col_nb = intval( str_replace('col-', '', $col_nb_index ) );
                $ms_grid_columns = [];
                // Up to 12 columns
                for ($j=1; $j <= $col_nb; $j++) {
                    if ( $j > 1 ) {
                        $ms_grid_columns[] = $col_gap;
                    }
                    $ms_grid_columns[] = 'minmax(0,1fr)';
                }
                $ms_grid_columns = implode(' ', $ms_grid_columns);

                $grid_template_columns = "repeat({$col_nb}, minmax(0,1fr))";

                $col_css_rules = [
                    '-ms-grid-columns:' . $ms_grid_columns,
                    'grid-template-columns:' . $grid_template_columns
                ];
                if ( $col_nb > 1 ) {
                    $col_css_rules[] = 'grid-column-gap:'.$col_gap;
                    $col_css_rules[] = 'grid-row-gap:'.$col_gap;
                }
                $rules[] = array(
                    'selector' => '.sek-post-grid-wrapper .sek-grid-layout.sek-all-col-'.$col_nb,
                    'css_rules' => implode(';', $col_css_rules),
                    'mq' =>null
                );
            }
            Nimble_Manager()->generic_post_grid_css_rules_written = true;
        }


        // MEDIA QUERIES
        $main_settings['columns'] = is_array($main_settings['columns']) ? $main_settings['columns'] : [];
        $cols_by_device = wp_parse_args(
            $main_settings['columns'],
            [ 'desktop' => '2', 'tablet' => '2', 'mobile' => '1' ]
        );
        if ( sek_is_pro() && array_key_exists('min_column_width', $main_settings ) ) {
            $min_column_width_by_device = wp_parse_args(
                $main_settings['min_column_width'],
                [ 'desktop' => '250', 'tablet' => '250', 'mobile' => '250' ]
            );
        }

        $col_css_rules = '';
        foreach ( $cols_by_device as $device => $col_nb ) {
            $col_nb = intval($col_nb);
            // First define the media queries using custom user breakpoints
            switch( $device ) {
                case 'desktop' :
                    $media_qu = "(min-width:{$tablet_breakpoint}px)";
                break;
                case 'tablet' :
                    if ( $mobile_breakpoint >= ( $tab_bp_val ) ) {
                        $media_qu = "(max-width:{$tab_bp_val}px)";
                    } else {
                        $media_qu = "(min-width:{$mob_bp_val}px) and (max-width:{$tab_bp_val}px)";
                    }
                break;
                case 'mobile' :
                    $media_qu = "(max-width:{$mob_bp_val}px)";
                break;
            }


            // Then define the selector + css rules by device
            // SELECTOR
            $selector = sprintf('[data-sek-id="%1$s"] .sek-post-grid-wrapper .sek-grid-layout.sek-%2$s-col-%3$s',
                $complete_modul_model['id'],
                $device,
                $col_nb
            );

            // CSS RULES
            //     .sek-grid-layout.sek-desktop-col-1 {
            //       -ms-grid-columns: minmax(0,1fr);
            //       grid-template-columns: repeat(1, minmax(0,1fr));
            //     }
            //     .sek-grid-layout.sek-desktop-col-2 {
            //       -ms-grid-columns: minmax(0,1fr) 20px minmax(0,1fr);
            //       grid-template-columns: repeat(2, minmax(0,1fr));
            //       grid-column-gap: 20px;
            //       grid-row-gap: 20px;
            //     }
            // July 2021 : introduction of the auto-fill rule in pro
            if ( sek_is_pro() && array_key_exists('auto_fill', $main_settings) && sek_booleanize_checkbox_val($main_settings['auto_fill']) ) {
                $min_col_width = 250;
                if ( array_key_exists($device, $min_column_width_by_device ) ) {
                    $min_col_width = intval( $min_column_width_by_device[$device] );
                }
                $grid_template_columns = "repeat(auto-fill, minmax({$min_col_width}px,1fr));";
                // in this case, no need to add '-ms-grid-columns' rule
                $col_css_rules = [
                    'grid-template-columns:' . $grid_template_columns
                ];
            } else {
                $ms_grid_columns = [];
                // Up to 12 columns
                for ($i=1; $i <= $col_nb; $i++) {
                    if ( $i > 1 ) {
                        $col_gap = array_key_exists('col-'.$col_nb, $col_nb_gap_map ) ? $col_nb_gap_map['col-'.$col_nb] : '5px';
                        $ms_grid_columns[] = $col_gap;
                    }
                    $ms_grid_columns[] = 'minmax(0,1fr)';
                }

                $ms_grid_columns = implode(' ', $ms_grid_columns);

                $grid_template_columns = "repeat({$col_nb}, minmax(0,1fr))";
                $col_css_rules = [
                    '-ms-grid-columns:' . $ms_grid_columns,
                    'grid-template-columns:' . $grid_template_columns
                ];
            }

            if ( $col_nb > 1 ) {
                $col_gap = array_key_exists('col-'.$col_nb, $col_nb_gap_map ) ? $col_nb_gap_map['col-'.$col_nb] : '5px';
                $col_css_rules[] = 'grid-column-gap:'.$col_gap;
                $col_css_rules[] = 'grid-row-gap:'.$col_gap;
            }

            $col_css_rules_ready = [];
            if ( 'desktop' != $device ) {
                foreach ($col_css_rules as $col_rule) {
                    $col_css_rules_ready[] = $col_rule .= '';//!important';
                }
            } else {
                $col_css_rules_ready = $col_css_rules;
            }
            $col_css_rules_ready = implode(';', $col_css_rules_ready);

            $rules[] = array(
                'selector' => $selector,
                'css_rules' => $col_css_rules_ready,
                'mq' => $media_qu
            );
        }// end foreach
    }// END OF GRID LAYOUT RULES


    // LIST LAYOUT RULES
    if ( 'list' === $main_settings['layout'] ) {
        $css_rules = [
            '-ms-grid-columns: minmax(0,1fr)!important;',
            'grid-template-columns: minmax(0,1fr)!important;',//<= important because this property can be customized by users for desktop
            'grid-gap: 0;',
        ];
        // TABLET RULES
        if ( $mobile_breakpoint >= ( $tab_bp_val ) ) {
            $media_qu = "(max-width:{$tab_bp_val}px)";
        } else {
            $media_qu = "(min-width:{$mob_bp_val}px) and (max-width:{$tab_bp_val}px)";
        }
        $rules[] = array(
            'selector' => '.sek-post-grid-wrapper.sek-has-tablet-breakpoint .sek-list-layout article',
            'css_rules' => implode('', $css_rules),
            'mq' => $media_qu
        );
        $rules[] = array(
            'selector' => '.sek-post-grid-wrapper.sek-has-tablet-breakpoint .sek-list-layout article .sek-pg-thumbnail',
            'css_rules' => 'margin-bottom:10px;',
            'mq' => $media_qu
        );

        // MOBILE RULES
        $media_qu = "(max-width:{$mob_bp_val}px)";
        $rules[] = array(
            'selector' => '.sek-post-grid-wrapper.sek-has-mobile-breakpoint .sek-list-layout article',
            'css_rules' => implode('', $css_rules),
            'mq' => $media_qu
        );
        $rules[] = array(
            'selector' => '.sek-post-grid-wrapper.sek-has-mobile-breakpoint .sek-list-layout article .sek-pg-thumbnail',
            'css_rules' => 'margin-bottom:10px;',
            'mq' => $media_qu
        );
    }
    return $rules;
}
?><?php
/* ------------------------------------------------------------------------- *
 *  LOAD AND REGISTER MENU MODULE
/* ------------------------------------------------------------------------- */
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );

function sek_get_module_params_for_czr_menu_module() {
    $css_selectors = '.sek-btn';
    $css_font_selectors = '.sek-btn';
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_menu_module',
        'is_father' => true,
        'children' => array(
            'content' => 'czr_menu_content_child',
            //'design' => 'czr_menu_design_child',
            'font' => 'czr_font_child',
            'mobile_options' => 'czr_menu_mobile_options'

        ),
        'name' => __( 'Menu', 'text_doma' ),
        'sanitize_callback' => '\Nimble\sanitize_callback__czr_button_module',
        'starting_value' => array(
            // 'content' => array(
            //     'button_text' => __('Click me','text_doma'),
            // ),
            // 'design' => array(
            //     'bg_color_css' => '#020202',
            //     'bg_color_hover' => '#151515', //lighten 15%,
            //     'use_custom_bg_color_on_hover' => 0,
            //     'border_radius_css' => '2',
            //     'h_alignment_css' => 'center',
            //     'use_box_shadow' => 1,
            //     'push_effect' => 1,
            // ),
            // 'font' => array(
            //     'color_css'  => '#ffffff',
            // )
        ),
        'css_selectors' => array( '.sek-menu-module li > a', '.nb-search-expand-inner input', '[data-sek-is-mobile-vertical-menu="yes"] .nb-mobile-search input', '.nb-arrow-for-mobile-menu' ),//<=@see tmpl/modules/menu_module_tmpl.php
        'render_tmpl_path' => "menu_module_tmpl.php"
    );
}

/* ------------------------------------------------------------------------- *
 *  MENU CONTENT
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_menu_content_child() {
    $pro_text = '';
    if ( !sek_is_pro() ) {
        $pro_text = sek_get_pro_notice_for_czr_input( __('search icon next to the menu, sticky header, hamburger color, ...', 'text-doma') );
    }
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_menu_content_child',
        'name' => __( 'Menu content', 'text_doma' ),
        //'sanitize_callback' => '\Nimble\sanitize_callback__czr_simple_form_module',
        //'css_selectors' =>'',
        'tmpl' => array(
            'item-inputs' => array(
                'menu-id' => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __('Select a menu', 'text_doma'),
                    'default'     => 'no-link',
                    'choices'     => sek_get_user_created_menus(),
                    'notice_after' => sprintf( __( 'You can create and edit menus in the %1$s. If you just created a new menu, publish and refresh the customizer to see in the dropdown list.', 'text_doma'),
                        sprintf( '<a href="#" onclick="%1$s">%2$s</a>',
                            "javascript:wp.customize.panel('nav_menus', function( _p_ ){ _p_.focus(); })",
                            __('menu panel', 'text_doma')
                        )
                    ),
                ),
                // alignment of items on desktops devices ( when items are horizontal ), is controled with selector .sek-nav-collapse
                // janv 2021 : alignement of menu items in the vertical mobile mnenu with '[data-sek-is-mobile-vertical-menu="yes"] .sek-nav li a'
                'h_alignment_css' => array(
                    'input_type'  => 'horizAlignmentWithDeviceSwitcher',
                    'title'       => __('Menu items alignment', 'text_doma'),
                    'default'     => array( 'desktop' => 'right', 'tablet' => 'left' ),
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'h_flex_alignment',
                    'css_selectors' => array( '.sek-nav-collapse', '[data-sek-is-mobile-vertical-menu="yes"] .sek-nav li a' ),
                    'title_width' => 'width-100',
                    'width-100'   => true,
                ),
                'hamb_h_alignment_css' => array(
                    'input_type'  => 'horizAlignmentWithDeviceSwitcher',
                    'title'       => __('Hamburger button alignment', 'text_doma'),
                    'default'     => array( 'desktop' => 'center' ),
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'h_flex_alignment',
                    'css_selectors' => array( '.sek-nav-wrap' ),
                    'title_width' => 'width-100',
                    'width-100'   => true,
                    'html_after' => $pro_text
                ),
            ),
        ),
        'render_tmpl_path' => '',
    );
}

/* ------------------------------------------------------------------------- *
 * MOBILE OPTIONS
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_menu_mobile_options() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_menu_mobile_options',
        'name' => __( 'Settings for mobile devices', 'text_doma' ),
        //'sanitize_callback' => '\Nimble\sanitize_callback__czr_simple_form_module',
        //'css_selectors' =>'',
        'tmpl' => array(
            'item-inputs' => array(
                'expand_below' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => sprintf('%s %s', '<i class="material-icons sek-level-option-icon">devices</i>', __('On mobile devices, expand the menu in full width below the menu hamburger icon.', 'text_doma') ),
                    'default'     => true,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20'
                ),
            ),
        ),
        'render_tmpl_path' => '',
    );
}
?><?php
/* ------------------------------------------------------------------------- *
 *  GENERIC FONT CHILD MODULE
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_font_child() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_font_child',
        'name' => __( 'Text settings : font, color, size, ...', 'text_doma' ),
        //'sanitize_callback' => '\Nimble\sanitize_callback__czr_simple_form_module',
        // 'starting_value' => array(
        //     'button_text' => __('Click me','text_doma'),
        //     'color_css'  => '#ffffff',
        //     'bg_color_css' => '#020202',
        //     'bg_color_hover' => '#151515', //lighten 15%,
        //     'use_custom_bg_color_on_hover' => 0,
        //     'border_radius_css' => '2',
        //     'h_alignment_css' => 'center',
        //     'use_box_shadow' => 1,
        //     'push_effect' => 1
        // ),
        //'css_selectors' => '',
        'tmpl' => array(
            'item-inputs' => array(
                'font_family_css' => array(
                    'input_type'  => 'font_picker',
                    'title'       => __('Font family', 'text_doma'),
                    'default'     => '',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'refresh_fonts' => true,
                    'css_identifier' => 'font_family',
                    'html_after' => sprintf('<span class="czr-notice"><i class="far fa-lightbulb"></i> %1s => <a href="%2s" target="_blank" rel="noopener noreferrer">%2$s</a></span><hr/>',
                        __('Find inspiration'),
                        'https://fonts.google.com/?sort=popularity'
                    )
                ),
                'font_size_css'       => array(
                    'input_type'  => 'range_with_unit_picker_device_switcher',
                    'title'       => __( 'Font size', 'text_doma' ),
                    // the default value is commented to fix https://github.com/presscustomizr/nimble-builder/issues/313
                    // => as a consequence, when a module uses the font child module, the default font-size rule must be defined in the module SCSS file.
                    //'default'     => array( 'desktop' => '16px' ),
                    'min' => 0,
                    'max' => 100,
                    'title_width' => 'width-100',
                    'width-100'         => true,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'font_size'
                ),//16,//"14px",
                'line_height_css'     => array(
                    'input_type'  => 'range_with_unit_picker',
                    'title'       => __( 'Line height', 'text_doma' ),
                    'default'     => '1.5em',
                    'min' => 0,
                    'max' => 10,
                    'step' => 0.1,
                    'width-100'         => true,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'line_height'
                ),//24,//"20px",
                'color_css'           => array(
                    'input_type'  => 'wp_color_alpha',
                    'title'       => __('Text color', 'text_doma'),
                    'default'     => '',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'width-100'   => true,
                    'css_identifier' => 'color'
                ),//"#000000",
                'color_hover_css'     => array(
                    'input_type'  => 'wp_color_alpha',
                    'title'       => __('Text color on mouse over', 'text_doma'),
                    'default'     => '',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'css_identifier' => 'color_hover'
                ),//"#000000",
                'font_weight_css'     => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __('Font weight', 'text_doma'),
                    'default'     => 400,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'font_weight',
                    'choices'            => sek_get_select_options_for_input_id( 'font_weight_css' )
                ),//null,
                'font_style_css'      => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __('Font style', 'text_doma'),
                    'default'     => 'inherit',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'font_style',
                    'choices'            => sek_get_select_options_for_input_id( 'font_style_css' )
                ),//null,
                'text_decoration_css' => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __('Text decoration', 'text_doma'),
                    'default'     => 'none',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'text_decoration',
                    'choices'            => sek_get_select_options_for_input_id( 'text_decoration_css' )
                ),//null,
                'text_transform_css'  => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __('Text transform', 'text_doma'),
                    'default'     => 'none',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'text_transform',
                    'choices'            => sek_get_select_options_for_input_id( 'text_transform_css' )
                ),//null,

                'letter_spacing_css'  => array(
                    'input_type'  => 'range_simple',
                    'title'       => __( 'Letter spacing', 'text_doma' ),
                    'default'     => 0,
                    'min'         => 0,
                    'step'        => 1,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'css_identifier' => 'letter_spacing',
                    'width-100'   => true,
                ),//0,
                // Note : always use the suffix '_flag_important' to name an input controling the !important css flag @see Nimble\sek_add_css_rules_for_css_sniffed_input_id
                'fonts___flag_important'  => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Apply the style options in priority (uses !important).', 'text_doma'),
                    'default'     => 0,
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    // declare the list of input_id that will be flagged with !important when the option is checked
                    // @see sek_add_css_rules_for_css_sniffed_input_id
                    // @see Nsek_is_flagged_important
                    'important_input_list' => array(
                        'font_family_css',
                        'font_size_css',
                        'line_height_css',
                        'font_weight_css',
                        'font_style_css',
                        'text_decoration_css',
                        'text_transform_css',
                        'letter_spacing_css',
                        'color_css',
                        'color_hover_css'
                    )
                )
            )
        ),
        'render_tmpl_path' => '',
    );
}
?><?php
/* ------------------------------------------------------------------------- *
 *  LOAD AND REGISTER WIDGET ZONE MODULE
/* ------------------------------------------------------------------------- */
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );

function sek_get_module_params_for_czr_widget_area_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_widget_area_module',
        'name' => __('Widget Zone', 'text_doma'),
        //'css_selectors' => array( '.sek-module-inner > *' ),
        // 'sanitize_callback' => 'function_prefix_to_be_replaced_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'tmpl' => array(
            'item-inputs' => array(
                'widget-area-id' => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __('Select a widget area', 'text_doma'),
                    'default'     => 'no-link',
                    'choices'     => array(),
                    'refresh_preview' => true,// <= so that the partial refresh links are displayed
                    'html_before' => '<span class="czr-notice">' . __('This module allows you to embed any WordPress widgets in your Nimble sections.', 'text_doma') . '<br/>' . __('1) Select a widget area in the dropdown list,', 'text_doma') . '<br/>' . sprintf( __( '2) once selected an area, you can add and edit the WordPress widgets in it in the %1$s.', 'text_doma'),
                        sprintf( '<a href="#" onclick="%1$s"><strong>%2$s</strong></a>',
                            "javascript:wp.customize.panel('widgets', function( _p_ ){ _p_.focus(); })",
                            __('widget panel', 'text_doma')
                        )
                    ) . '</span><br/>'
                )
            )
        ),
        'render_tmpl_path' => "widget_area_module_tmpl.php",
    );
}

?><?php

/* ------------------------------------------------------------------------- *
 *  LOAD AND REGISTER IMG SLIDER MODULE
/* ------------------------------------------------------------------------- */
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_czr_img_slider_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_img_slider_module',
        'is_father' => true,
        'children' => array(
            'img_collection' => 'czr_img_slider_collection_child',
            'slider_options' => 'czr_img_slider_opts_child'
        ),
        'name' => __('Image & Text Carousel', 'text_doma'),
        'starting_value' => array(
            'img_collection' => array(
                array( 'img' =>  NIMBLE_BASE_URL . '/assets/img/default-img.png' ),
                array( 'img' =>  NIMBLE_BASE_URL . '/assets/img/default-img.png' ),
                array( 'img' =>  NIMBLE_BASE_URL . '/assets/img/default-img.png' )
            )
        ),
        'sanitize_callback' => '\Nimble\sanitize_cb__czr_img_slider_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'css_selectors' => array( '[data-sek-swiper-id]' ),//array( '.sek-icon i' ),
        'render_tmpl_path' => "img_slider_tmpl.php",
        // 'front_assets' => array(
        //       'czr-font-awesome' => array(
        //           'type' => 'css',
        //           //'handle' => 'czr-font-awesome',
        //           'src' => NIMBLE_BASE_URL . '/assets/front/fonts/css/fontawesome-all.min.css'
        //           //'deps' => array()
        //       )
        // )
    );
}

/* ------------------------------------------------------------------------- *
 *  SANITIZATION
/* ------------------------------------------------------------------------- */
// convert into a json to prevent emoji breaking global json data structure
// fix for https://github.com/presscustomizr/nimble-builder/issues/544
function sanitize_cb__czr_img_slider_module( $value ) {
    if ( !is_array( $value ) )
        return $value;

    if ( !empty($value['img_collection']) && is_array( $value['img_collection'] ) ) {
        foreach( $value['img_collection'] as $key => $data ) {
            if ( array_key_exists( 'text_content', $data ) && is_string( $data['text_content'] ) ) {
                $value['img_collection'][$key]['text_content'] = sek_maybe_encode_richtext( $data['text_content'] );
            }
        }
    }
    return $value;
}


/* ------------------------------------------------------------------------- *
 *  MAIN SETTINGS
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_img_slider_collection_child() {
    $text_content_selector = array( '.sek-slider-text-content', '.sek-slider-text-content *' );
    $pro_text = '';
    if ( !sek_is_pro() ) {
        $pro_text = __( 'add transition options with effects like fade or flip, link slides individually to any content.', 'text-doma');
        $pro_text = sek_get_pro_notice_for_czr_input( $pro_text );
    }
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_img_slider_collection_child',
        'is_crud' => true,
        'name' => sprintf('<i class="material-icons" style="font-size: 1.2em;">photo_library</i> %1$s', __( 'Slide collection', 'text_doma' ) ),
        'starting_value' => array(
            'img' =>  NIMBLE_BASE_URL . '/assets/img/default-img.png'
        ),
        //'sanitize_callback' => '\Nimble\sanitize_callback__czr_simple_form_module',
        //'css_selectors' => array( '.sek-social-icon' ),//array( '.sek-icon i' ),
        'tmpl' => array(
            'pre-item' => array(
                // 'page-id' => array(
                //     'input_type'  => 'content_picker',
                //     'title'       => __('Pick a page', 'text_doma')
                // ),
                'img' => array(
                    'input_type'  => 'upload',
                    'title'       => __('Pick an image', 'text_doma'),
                    'default'     => ''
                ),
            ),
            'item-inputs' => array(
                'tabs' => array(
                    array(
                        'title' => __( 'Image', 'text_doma' ),
                        'inputs' => array(
                            'img' => array(
                                'input_type'  => 'upload',
                                'title'       => __('Pick an image', 'text_doma'),
                                'default'     => ''
                            ),
                            'img-size' => array(
                                'input_type'  => 'simpleselect',
                                'title'       => __('Select the image size', 'text_doma'),
                                'default'     => 'large',
                                'choices'     => sek_get_select_options_for_input_id( 'img-size' ),
                                'notice_before' => __('Select a size for this image among those generated by WordPress.', 'text_doma' )
                            ),
                            'title_attr'  => array(
                                'input_type'  => 'text',
                                'default'     => '',
                                'title'       => __('Title', 'text_domain_to_be_replaced'),
                                'notice_after' => sprintf( __('This is the text displayed on mouse over. You can use the following template tags referring to the image attributes : %1$s', 'text_domain_to_be_replaced'), '&#123;&#123;title&#125;&#125;, &#123;&#123;caption&#125;&#125;, &#123;&#123;description&#125;&#125;' ),
                                'html_after' => $pro_text
                            )
                        )
                    ),
                    array(
                        'title' => __( 'Text', 'text_doma' ),
                        'inputs' => array(
                            'enable_text' => array(
                                'input_type'  => 'nimblecheck',
                                'title'       => __('Add text content', 'text_doma'),
                                'default'     => false,
                                'title_width' => 'width-80',
                                'input_width' => 'width-20',
                                'notice_after' => __('Note : you can adjust the text color and / or use a color overlay to improve accessibility of your text content.', 'text_doma')
                            ),
                            'text_content' => array(
                                'input_type'        => 'nimble_tinymce_editor',
                                'editor_params'     => array(
                                    'media_button' => false,
                                    'includedBtns' => 'basic_btns',
                                ),
                                'title'             => __( 'Text content', 'text_doma' ),
                                'default'           => '',
                                'width-100'         => true,
                                'refresh_markup'    => '.sek-slider-text-content',
                                'notice_before' => sprintf( __('You may use some html tags in the "text" tab of the editor. You can also use the following template tags referring to the image attributes : %1$s', 'text_domain_to_be_replaced'), '&#123;&#123;title&#125;&#125;, &#123;&#123;caption&#125;&#125;, &#123;&#123;description&#125;&#125;' )
                            ),

                            'color_css'           => array(
                                'input_type'  => 'wp_color_alpha',
                                'title'       => __( 'Text color', 'text_doma' ),
                                'default'     => '#e2e2e2',// why this light grey ? => if set to white ( #fff ), the text is not visible when no image is picked, which might be difficult to understand for users
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'width-100'   => true,
                                'css_identifier' => 'color',
                                'css_selectors' => $text_content_selector,
                            ),//"#000000",

                            'font_family_css' => array(
                                'input_type'  => 'font_picker',
                                'title'       => __( 'Font family', 'text_doma' ),
                                'default'     => '',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'refresh_fonts' => true,
                                'css_identifier' => 'font_family',
                                'css_selectors' => $text_content_selector,
                                'html_before' => '<hr/><h3>' . __('FONT OPTIONS') .'</h3>',
                                'html_after' => sprintf('<span class="czr-notice"><i class="far fa-lightbulb"></i> %1s => <a href="%2s" target="_blank" rel="noopener noreferrer">%2$s</a></span><hr/>',
                                    __('Find inspiration'),
                                    'https://fonts.google.com/?sort=popularity'
                                )
                            ),
                            'font_size_css'       => array(
                                'input_type'  => 'range_with_unit_picker_device_switcher',
                                'default'     => array( 'desktop' => '16px' ),
                                'title_width' => 'width-100',
                                'title'       => __( 'Font size', 'text_doma' ),
                                'min' => 0,
                                'max' => 100,
                                'width-100'         => true,
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'font_size',
                                'css_selectors' => $text_content_selector,
                            ),//16,//"14px",
                            'line_height_css'     => array(
                                'input_type'  => 'range_with_unit_picker',
                                'title'       => __( 'Line height', 'text_doma' ),
                                'default'     => '1.5em',
                                'min' => 0,
                                'max' => 10,
                                'step' => 0.1,
                                'width-100'         => true,
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'line_height',
                                'css_selectors' => $text_content_selector,
                            ),//24,//"20px",

                            'h_alignment_css' => array(
                                'input_type'  => 'horizTextAlignmentWithDeviceSwitcher',
                                'title'       => __('Horizontal alignment', 'text_doma'),
                                'default'     => array( 'desktop' => 'center'),
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'h_alignment',
                                'title_width' => 'width-100',
                                'width-100'   => true,
                                'css_selectors' => array( '.sek-slider-text-content' ),
                                'html_before' => '<hr/><h3>' . __('ALIGNMENTS') .'</h3>'
                            ),
                            'v_alignment' => array(
                                'input_type'  => 'verticalAlignWithDeviceSwitcher',
                                'title'       => __('Vertical alignment', 'text_doma'),
                                'default'     => array( 'desktop' => 'center' ),
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                //'css_identifier' => 'v_alignment',
                                'title_width' => 'width-100',
                                'width-100'   => true,
                            ),
                            'spacing_css'     => array(
                                'input_type'  => 'spacingWithDeviceSwitcher',
                                'title'       => __( 'Spacing of the text content', 'text_doma' ),
                                'default'     => array('desktop' => array(
                                    'padding-bottom' => '5',
                                    'padding-top' => '5',
                                    'padding-right' => '5',
                                    'padding-left' => '5',
                                    'unit' => '%')
                                ),//consistent with SCSS
                                'title_width' => 'width-100',
                                'width-100'   => true,
                                'refresh_markup'     => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'spacing_with_device_switcher',
                                'css_selectors' => array( '.sek-slider-text-content' ),
                                'html_before' => '<hr/><h3>' . __('SPACING') .'</h3>'
                            )
                        )
                    ),
                    array(
                        'title' => __( 'Color overlay', 'text_doma' ),
                        'inputs' => array(
                            'apply-overlay' => array(
                                'input_type'  => 'nimblecheck',
                                'notice_after' => __('A color overlay is usually recommended when displaying text content on top of the image. You can customize the color and transparency in the global design settings of the carousel.', 'text_doma' ),
                                'title'       => __('Apply a color overlay', 'text_doma'),
                                'default'     => false,
                                'title_width' => 'width-80',
                                'input_width' => 'width-20',
                                'html_before' => '<hr/><h3>' . __('COLOR OVERLAY') .'</h3>'
                            ),
                            'color-overlay' => array(
                                'input_type'  => 'wp_color_alpha',
                                'title'       => __('Overlay Color', 'text_doma'),
                                'width-100'   => true,
                                'default'     => '#000000',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true
                            ),
                            'opacity-overlay' => array(
                                'input_type'  => 'range_simple',
                                'title'       => __('Opacity (in percents)', 'text_doma'),
                                'orientation' => 'horizontal',
                                'min' => 0,
                                'max' => 100,
                                // 'unit' => '%',
                                'default'  => '30',
                                'width-100'   => true,
                                'title_width' => 'width-100',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true
                            )
                        )
                    )
                )//'tabs'
            )//'item-inputs'
        ),
        'render_tmpl_path' => '',
    );
}


/* ------------------------------------------------------------------------- *
 *  SLIDER OPTIONS
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_img_slider_opts_child() {
    $pro_text = '';
    if ( !sek_is_pro() ) {
        $pro_text = __( 'add transition options with effects like fade and flip, link slides individually to any content.', 'text-doma');
        $pro_text = sek_get_pro_notice_for_czr_input( $pro_text );
    }
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_img_slider_opts_child',
        'name' => sprintf('<i class="material-icons" style="font-size: 1.2em;">tune</i> %1$s', __( 'Slider options : height, autoplay, navigation...', 'text_doma' ) ),
        //'sanitize_callback' => '\Nimble\sanitize_callback__czr_simple_form_module',
        // 'starting_value' => array(
        //     'button_text' => __('Click me','text_doma'),
        //     'color_css'  => '#ffffff',
        //     'bg_color_css' => '#020202',
        //     'bg_color_hover' => '#151515', //lighten 15%,
        //     'use_custom_bg_color_on_hover' => 0,
        //     'border_radius_css' => '2',
        //     'h_alignment_css' => 'center',
        //     'use_box_shadow' => 1,
        //     'push_effect' => 1
        // ),
        //'css_selectors' => array( '.sek-social-icons-wrapper' ),//array( '.sek-icon i' ),
        'tmpl' => array(
            'item-inputs' => array(
                'tabs' => array(
                    array(
                        'title' => __( 'General', 'text_doma' ),
                        'inputs' => array(
                            'image-layout' => array(
                                'input_type'  => 'simpleselect',
                                'title'       => __('Image layout', 'text_doma'),
                                'default'     => 'width-100',
                                'choices'     => array(
                                    'nimble-wizard' => __('Nimble wizard', 'text_doma' ),
                                    'cover' => __('Images fill space and are centered without being stretched', 'text_doma'),
                                    'width-100' => __('Adapt images to carousel\'s width', 'text_doma' ),
                                    'height-100' => __('Adapt images to carousel\'s height', 'text_doma' ),
                                ),
                                'title_width' => 'width-100',
                                'width-100'   => true,
                                'notice_before' => __('Nimble wizard ensures that the images fill all available space of the carousel in any devices, without blank spaces on the edges, and without stretching the images.', 'text_doma' ),
                            ),
                            'autoplay' => array(
                                'input_type'  => 'nimblecheck',
                                'title'       => __('Autoplay', 'text_doma'),
                                'default'     => false,
                                'title_width' => 'width-80',
                                'input_width' => 'width-20',
                                'notice_after' => __('Note that the autoplay is disabled during customization.', 'text_doma' ),
                            ),
                            'autoplay_delay' => array(
                                'input_type'  => 'range_simple',
                                'title'       => __( 'Delay between each slide in milliseconds (ms)', 'text_doma' ),
                                'min' => 1,
                                'max' => 30000,
                                'step' => 500,
                                'unit' => '',
                                'default' => 3000,
                                'width-100'   => true,
                                'title_width' => 'width-100'
                            ),
                            'pause_on_hover' => array(
                                'input_type'  => 'nimblecheck',
                                'title'       => __('Pause autoplay on mouse over', 'text_doma'),
                                'default'     => true,
                                'title_width' => 'width-80',
                                'input_width' => 'width-20'
                            ),
                            'infinite_loop' => array(
                                'input_type'  => 'nimblecheck',
                                'title'       => __('Infinite loop', 'text_doma'),
                                'default'     => true,
                                'title_width' => 'width-80',
                                'input_width' => 'width-20'
                            ),
                            // added dec 2019 for https://github.com/presscustomizr/nimble-builder/issues/570
                            'lazy_load' => array(
                                'input_type'  => 'nimblecheck',
                                'title'       => __('Lazy load images', 'text_doma'),
                                'default'     => true,
                                'title_width' => 'width-80',
                                'input_width' => 'width-20',
                                'notice_after' => __('Lazy loading images improves page load performances.', 'text_doma' ),
                                'html_before' => $pro_text
                            ),
                            'bg_color_css' => array(
                                'input_type'  => 'wp_color_alpha',
                                'title'       => __( 'Background color', 'text_doma' ),
                                'width-100'   => true,
                                'title_width' => 'width-100',
                                'default'    => '',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'background_color',
                                'css_selectors'=> '.swiper-slide'
                            )
                        )//inputs
                    ),
                    array(
                        'title' => __( 'Height', 'text_doma' ),
                        'inputs' => array(
                            'height-type' => array(
                                'input_type'  => 'simpleselect',
                                'title'       => __('Height : auto or custom', 'text_doma'),
                                'default'     => 'custom',
                                'choices'     => sek_get_select_options_for_input_id( 'height-type' ),// auto, custom
                                'refresh_markup'     => false,
                                'refresh_stylesheet' => true,
                                'html_before' => '<hr/><h3>' . __('SLIDER HEIGHT') .'</h3>'
                            ),
                            'custom-height' => array(
                                'input_type'  => 'range_with_unit_picker_device_switcher',
                                'title'       => __('Custom height', 'text_doma'),
                                'min' => 0,
                                'max' => 1000,
                                'default'     => array( 'desktop' => '400px', 'mobile' => '200px' ),
                                'width-100'   => true,
                                'title_width' => 'width-100',
                                'refresh_markup'     => false,
                                'refresh_stylesheet' => true,
                            )
                        )
                    ),
                    array(
                        'title' => __( 'Navigation', 'text_doma' ),
                        'inputs' => array(
                            'nav_type' => array(
                                'input_type'  => 'simpleselect',
                                'title_width' => 'width-100',
                                'width-100'   => true,
                                'default' => 'arrows',
                                'choices'     => array(
                                    'arrows_dots' => __('Arrows and bullets', 'text_doma'),
                                    'arrows' => __('Arrows only', 'text_doma'),
                                    'dots' => __('Bullets only', 'text_doma'),
                                    'none' => __('None', 'text_doma')
                                ),
                                'html_before' => '<hr/><h3>' . __('NAVIGATION') .'</h3>'
                            ),
                            'hide_nav_on_mobiles' => array(
                                'input_type'  => 'nimblecheck',
                                'title'       => __('Hide arrows and bullets on mobiles', 'text_doma'),
                                'default'     => false,
                                'title_width' => 'width-80',
                                'input_width' => 'width-20'
                            ),
                            // 'arrows_size'  => array(
                            //     'input_type'  => 'range_simple_device_switcher',
                            //     'title'       => __( 'Size of the arrows', 'text_doma' ),
                            //     'default'     => array( 'desktop' => '18'),
                            //     'min'         => 1,
                            //     'max'         => 50,
                            //     'step'        => 1,
                            //     'width-100'   => true,
                            //     'title_width' => 'width-100'
                            // ),//null,
                            'arrows_color_css' => array(
                                'input_type'  => 'wp_color_alpha',
                                'title'       => __('Color of the navigation arrows', 'text_doma'),
                                'width-100'   => true,
                                'title_width' => 'width-100',
                                'default'    => '#ffffff',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'border_color',
                                'css_selectors' => array('.sek-swiper-nav .sek-swiper-arrows .sek-chevron')
                            ),
                            // 'dots_size'  => array(
                            //     'input_type'  => 'range_simple_device_switcher',
                            //     'title'       => __( 'Size of the dots', 'text_doma' ),
                            //     'default'     => array( 'desktop' => '16'),
                            //     'min'         => 1,
                            //     'max'         => 50,
                            //     'step'        => 1,
                            //     'width-100'   => true,
                            //     'title_width' => 'width-100'
                            // ),//null,
                            'dots_color_css' => array(
                                'input_type'  => 'wp_color_alpha',
                                'title'       => __('Color of the active pagination bullet', 'text_doma'),
                                'width-100'   => true,
                                'title_width' => 'width-100',
                                'default'    => '#ffffff',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'background_color',
                                'css_selectors' => array('.swiper-pagination-bullet-active')
                            ),
                        )//inputs
                    )
                )//tabs
            )
        ),
        'render_tmpl_path' => '',
    );
}




/* ------------------------------------------------------------------------- *
 *  SCHEDULE CSS RULES FILTERING
/* ------------------------------------------------------------------------- */
// PER ITEM CSS DESIGN => FILTERING OF EACH ITEM MODEL, TARGETING THE ID ( [data-sek-item-id="893af157d5e3"] )
add_filter( 'sek_add_css_rules_for_single_item_in_module_type___czr_img_slider_collection_child', '\Nimble\sek_add_css_rules_for_items_in_czr_img_slider_collection_child', 10, 2 );

// filter documented in Sek_Dyn_CSS_Builder::sek_css_rules_sniffer_walker
// Note : $complete_modul_model has been normalized
// @return populated $rules
// @param $params
// Array
// (
//     [input_list] => Array
//         (
//             [icon] => fab fa-acquisitions-incorporated
//             [link] => https://twitter.com/home
//             [title_attr] => Follow me on twitter
//             [link_target] =>
//             [color_css] => #dd9933
//             [use_custom_color_on_hover] =>
//             [social_color_hover] => #dd3333
//             [id] => 62316ab99b4d
//         )
//     [parent_module_id] =>
//     [module_type] => czr_img_slider_collection_child
//     [module_css_selector] => Array
//         (
//             [0] => .sek-social-icon
//         )

// )
function sek_add_css_rules_for_items_in_czr_img_slider_collection_child( $rules, $params ) {
    // $item_input_list = wp_parse_args( $item_input_list, $default_value_model );
    $item_model = isset( $params['input_list'] ) ? $params['input_list'] : array();
    $all_defaults = sek_get_default_module_model( 'czr_img_slider_collection_child');
    // Default :
    // [v_alignment] => Array
    // (
    //     [desktop] => center
    // )
    // VERTICAL ALIGNMENT
    if ( !empty( $item_model[ 'v_alignment' ] ) && $all_defaults['v_alignment'] != $item_model[ 'v_alignment' ] ) {
        if ( !is_array( $item_model[ 'v_alignment' ] ) ) {
            sek_error_log( __FUNCTION__ . ' => error => the v_alignment option should be an array( {device} => {alignment} )');
        }
        $v_alignment_value = is_array( $item_model[ 'v_alignment' ] ) ? $item_model[ 'v_alignment' ] : array();
        $v_alignment_value = wp_parse_args( $v_alignment_value, array(
            'desktop' => 'center',
            'tablet' => '',
            'mobile' => ''
        ));
        $mapped_values = array();
        foreach ( $v_alignment_value as $device => $align_val ) {
            switch ( $align_val ) {
                case 'top' :
                    $mapped_values[$device] = "align-items:flex-start;-webkit-box-align:start;-ms-flex-align:start;";
                break;
                case 'center' :
                    $mapped_values[$device] = "align-items:center;-webkit-box-align:center;-ms-flex-align:center;";
                break;
                case 'bottom' :
                    $mapped_values[$device] = "align-items:flex-end;-webkit-box-align:end;-ms-flex-align:end";
                break;
            }
        }
        $rules = sek_set_mq_css_rules_supporting_vendor_prefixes( array(
            'css_rules_by_device' => $mapped_values,
            'selector' => sprintf( '[data-sek-id="%1$s"]  [data-sek-item-id="%2$s"] .sek-slider-text-wrapper', $params['parent_module_id'], $item_model['id'] ),
            'level_id' => $params['parent_module_id']
        ), $rules );
    }//Vertical alignment

    //Background overlay?
    // 1) a background image should be set
    // 2) the option should be checked
    if ( sek_is_checked( $item_model[ 'apply-overlay'] ) ) {
        //(needs validation: we need a sanitize hex or rgba color)
        $bg_color_overlay = isset( $item_model[ 'color-overlay' ] ) ? $item_model[ 'color-overlay' ] : null;
        if ( $bg_color_overlay ) {
            //overlay pseudo element
            $bg_overlay_css_rules = 'background-color:'.$bg_color_overlay;

            //opacity
            //validate/sanitize
            $bg_overlay_opacity     = isset( $item_model[ 'opacity-overlay' ] ) ? filter_var( $item_model[ 'opacity-overlay' ], FILTER_VALIDATE_INT, array( 'options' =>
                array( "min_range"=>0, "max_range"=>100 ) )
            ) : FALSE;
            $bg_overlay_opacity     = FALSE !== $bg_overlay_opacity ? filter_var( $bg_overlay_opacity / 100, FILTER_VALIDATE_FLOAT ) : $bg_overlay_opacity;

            $bg_overlay_css_rules = FALSE !== $bg_overlay_opacity ? $bg_overlay_css_rules . ';opacity:' . $bg_overlay_opacity . ';' : $bg_overlay_css_rules;

            $rules[]     = array(
                'selector' => sprintf( '[data-sek-id="%1$s"]  [data-sek-item-id="%2$s"][data-sek-has-overlay="true"] .sek-carousel-img::after', $params['parent_module_id'], $item_model['id'] ),
                'css_rules' => $bg_overlay_css_rules,
                'mq' =>null
            );
        }
    }// BG Overlay

    return $rules;
}




// GLOBAL CSS DESIGN => FILTERING OF THE ENTIRE MODULE MODEL
add_filter( 'sek_add_css_rules_for_module_type___czr_img_slider_module', '\Nimble\sek_add_css_rules_for_czr_img_slider_module', 10, 2 );
// filter documented in Sek_Dyn_CSS_Builder::sek_css_rules_sniffer_walker
// Note : $complete_modul_model has been normalized
// @return populated $rules
function sek_add_css_rules_for_czr_img_slider_module( $rules, $complete_modul_model ) {
    if ( empty( $complete_modul_model['value'] ) || !is_array( $complete_modul_model['value'] ) )
      return $rules;

    $value = $complete_modul_model['value'];
    $slider_options = $value['slider_options'];

    $selector = '[data-sek-id="'.$complete_modul_model['id'].'"] .sek-module-inner .swiper .swiper-wrapper';


    // CUSTOM HEIGHT BY DEVICE
    if ( !empty( $slider_options[ 'height-type' ] ) ) {
        if ( 'custom' === $slider_options[ 'height-type' ] ) {
            $custom_user_height = array_key_exists( 'custom-height', $slider_options ) ? $slider_options[ 'custom-height' ] : array();

            if ( !is_array( $custom_user_height ) ) {
                sek_error_log( __FUNCTION__ . ' => error => the height option should be an array( {device} => {number}{unit} )', $custom_user_height);
            }
            $custom_user_height = is_array( $custom_user_height ) ? $custom_user_height : array();

            // DEFAULTS :
            // array(
            //     'desktop' => '400px',
            //     'tablet' => '',
            //     'mobile' => '200px'
            // );
            $all_defaults = sek_get_default_module_model( 'czr_img_slider_module');
            $slider_defaults = $all_defaults['slider_options'];
            $defaults = $slider_defaults['custom-height'];

            $custom_user_height = wp_parse_args( $custom_user_height, $defaults );

            if ( $defaults != $custom_user_height ) {
                $height_value = $custom_user_height;
                foreach ( $custom_user_height as $device => $num_unit ) {
                    $numeric = sek_extract_numeric_value( $num_unit );
                    if ( !empty( $numeric ) ) {
                        $unit = sek_extract_unit( $num_unit );
                        $unit = '%' === $unit ? 'vh' : $unit;
                        $height_value[$device] = $numeric . $unit;
                    }
                }

                $rules = sek_set_mq_css_rules(array(
                    'value' => $height_value,
                    'css_property' => 'height',
                    'selector' => $selector,
                    'level_id' => $complete_modul_model['id']
                ), $rules );
            }
        }// if custom height
        else {
            $rules[] = array(
                'selector' => $selector,
                'css_rules' => 'height:auto;',
                'mq' =>null
            );
        }
    }// Custom height rules

    return $rules;
}


?><?php

/* ------------------------------------------------------------------------- *
 *  LOAD AND REGISTER ACCORDION MODULE
/* ------------------------------------------------------------------------- */
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_czr_accordion_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_accordion_module',
        'is_father' => true,
        'children' => array(
            'accord_collec' => 'czr_accordion_collection_child',
            'accord_opts' => 'czr_accordion_opts_child'
        ),
        'name' => __('Accordion', 'text_doma'),
        'starting_value' => array(
            'accord_collec' => array(
                array('text_content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed non risus. Suspendisse lectus tortor, dignissim sit amet, adipiscing nec, ultricies sed, dolor.'),
                array('text_content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed non risus. Suspendisse lectus tortor, dignissim sit amet, adipiscing nec, ultricies sed, dolor.'),
                array('text_content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed non risus. Suspendisse lectus tortor, dignissim sit amet, adipiscing nec, ultricies sed, dolor.')
            )
        ),
        'sanitize_callback' => '\Nimble\sanitize_cb__czr_accordion_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'css_selectors' => array( '[data-sek-accordion-id]' ),//array( '.sek-icon i' ),
        'render_tmpl_path' => "accordion_tmpl.php",
        // 'front_assets' => array(
        //       'czr-font-awesome' => array(
        //           'type' => 'css',
        //           //'handle' => 'czr-font-awesome',
        //           'src' => NIMBLE_BASE_URL . '/assets/front/fonts/css/fontawesome-all.min.css'
        //           //'deps' => array()
        //       )
        // )
    );
}

/* ------------------------------------------------------------------------- *
 *  SANITIZATION
/* ------------------------------------------------------------------------- */
// convert into a json to prevent emoji breaking global json data structure
// fix for https://github.com/presscustomizr/nimble-builder/issues/544
function sanitize_cb__czr_accordion_module( $value ) {
    if ( !is_array( $value ) )
        return $value;
    if ( !empty($value['accord_collec']) && is_array( $value['accord_collec'] ) ) {
        foreach( $value['accord_collec'] as $key => $data ) {
            if ( array_key_exists( 'text_content', $data ) && is_string( $data['text_content'] ) ) {
                $value['accord_collec'][$key]['text_content'] = sek_maybe_encode_richtext( $data['text_content'] );
            }
            if ( array_key_exists( 'title_text', $data ) && is_string( $data['title_text'] ) ) {
                $value['accord_collec'][$key]['title_text'] = sek_maybe_encode_richtext( $data['title_text'] );
            }
        }
    }
    return $value;
}

/* ------------------------------------------------------------------------- *
 *  MAIN SETTINGS
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_accordion_collection_child() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_accordion_collection_child',
        'is_crud' => true,
        'name' => sprintf('<i class="material-icons" style="font-size: 1.2em;">toc</i> %1$s', __( 'Item collection', 'text_doma' ) ),
        'starting_value' => array(
            'text_content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed non risus. Suspendisse lectus tortor, dignissim sit amet, adipiscing nec, ultricies sed, dolor.'
        ),
        //'sanitize_callback' => '\Nimble\sanitize_callback__czr_simple_form_module',
        //'css_selectors' => array( '.sek-social-icon' ),//array( '.sek-icon i' ),
        'tmpl' => array(
            'pre-item' => array(
                // 'page-id' => array(
                //     'input_type'  => 'content_picker',
                //     'title'       => __('Pick a page', 'text_doma')
                // ),
                'img' => array(
                    'input_type'  => 'upload',
                    'title'       => __('Pick an image', 'text_doma'),
                    'default'     => ''
                ),
            ),
            'item-inputs' => array(
                'tabs' => array(
                    array(
                        'title' => __( 'Title', 'text_doma' ),
                        'inputs' => array(
                            'title_text' => array(
                                'input_type'        => 'nimble_tinymce_editor',
                                'editor_params'     => array(
                                    'media_button' => false,
                                    'includedBtns' => 'basic_btns',
                                    'height' => 50
                                ),
                                'title'              => __( 'Heading text', 'text_doma' ),
                                'default'            => '',
                                'width-100'         => true,
                                'refresh_markup'    => '.sek-inner-accord-title',
                                'notice_before'      => __( 'You may use some html tags like a, br, span with attributes like style, id, class ...', 'text_doma'),
                            ),
                            'title_attr'  => array(
                                'input_type'  => 'text',
                                'default'     => '',
                                'title'       => __('Title on mouse over', 'text_domain_to_be_replaced'),
                                'notice_after' => __('This is the text displayed on mouse over.' )
                            ),
                        )
                    ),
                    array(
                        'title' => __( 'Content', 'text_doma' ),
                        'inputs' => array(
                            'text_content' => array(
                                'input_type'        => 'nimble_tinymce_editor',
                                'editor_params'     => array(
                                    'media_button' => true,
                                    'includedBtns' => 'basic_btns_with_lists',
                                ),
                                'title'             => __( 'Text content', 'text_doma' ),
                                'default'           => '',
                                'width-100'         => true,
                                'refresh_markup'    => '.sek-accord-content',
                                'notice_before' => __('You may use some html tags in the "text" tab of the editor.', 'text_domain_to_be_replaced')
                            ),
                            'h_alignment_css' => array(
                                'input_type'  => 'horizTextAlignmentWithDeviceSwitcher',
                                'title'       => __('Horizontal alignment', 'text_doma'),
                                'default'     => array( 'desktop' => 'center'),
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'h_alignment',
                                'title_width' => 'width-100',
                                'width-100'   => true,
                                'css_selectors' => array( '.sek-accord-content' )
                            )
                        )
                    ),
                )//'tabs'
            )//'item-inputs'
        ),
        'render_tmpl_path' => '',
    );
}


/* ------------------------------------------------------------------------- *
 *  ACCORDION OPTIONS
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_accordion_opts_child() {
    $title_content_selector = array( '.sek-accord-item .sek-accord-title *' );
    $main_content_selector = array( '.sek-accord-item .sek-accord-content', '.sek-accord-item .sek-accord-content *' );
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_accordion_opts_child',
        'name' => sprintf('<i class="material-icons" style="font-size: 1.2em;">tune</i> %1$s', __( 'Accordion options : font style, borders, background, ...', 'text_doma' ) ),
        //'sanitize_callback' => '\Nimble\sanitize_callback__czr_simple_form_module',
        // 'starting_value' => array(
        //     'button_text' => __('Click me','text_doma'),
        //     'color_css'  => '#ffffff',
        //     'bg_color_css' => '#020202',
        //     'bg_color_hover' => '#151515', //lighten 15%,
        //     'use_custom_bg_color_on_hover' => 0,
        //     'border_radius_css' => '2',
        //     'h_alignment_css' => 'center',
        //     'use_box_shadow' => 1,
        //     'push_effect' => 1
        // ),
        //'css_selectors' => array( '.sek-social-icons-wrapper' ),//array( '.sek-icon i' ),
        'tmpl' => array(
            'item-inputs' => array(
                'tabs' => array(
                    array(
                        'title' => __( 'General', 'text_doma' ),
                        'inputs' => array(
                            'first_expanded' => array(
                                'input_type'  => 'nimblecheck',
                                'title'       => __('Display first item expanded', 'text_doma'),
                                'default'     => true,
                                'title_width' => 'width-80',
                                'input_width' => 'width-20'
                            ),
                            'one_expanded' => array(
                                'input_type'  => 'nimblecheck',
                                'title'       => __('Display one item expanded at a time', 'text_doma'),
                                'default'     => true,
                                'title_width' => 'width-80',
                                'input_width' => 'width-20'
                            ),
                            'border_width_css' => array(
                                'input_type'  => 'range_with_unit_picker',
                                'title'       => __( 'Border weight', 'text_doma' ),
                                'min' => 0,
                                'max' => 80,
                                'default' => '1px',
                                'width-100'   => true,
                                //'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'border_width',
                                'css_selectors' => '.sek-accord-wrapper .sek-accord-item',
                                'html_before' => '<hr/><h3>' . __('BORDER') .'</h3>'
                            ),
                            'border_color_css' => array(
                                'input_type'  => 'wp_color_alpha',
                                'title'       => __( 'Border color', 'text_doma' ),
                                'width-100'   => true,
                                'default'     => '#e3e3e3',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'border_color',
                                'css_selectors' => '.sek-accord-wrapper .sek-accord-item'
                            ),
                        )//inputs
                    ),
                    array(
                        'title' => __( 'Title style', 'text_doma' ),
                        'inputs' => array(
                            'title_bg_css' => array(
                                'input_type'  => 'wp_color_alpha',
                                'title'       => __('Backround color', 'text_doma'),
                                'width-100'   => true,
                                'title_width' => 'width-100',
                                'default'    => '#ffffff',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'background_color',
                                'css_selectors' => '.sek-accord-wrapper .sek-accord-item .sek-accord-title',
                                'html_before' => '<h3>' . __('COLOR AND BACKGROUND') .'</h3>'
                            ),
                            'color_css'           => array(
                                'input_type'  => 'wp_color_alpha',
                                'title'       => __( 'Text color', 'text_doma' ),
                                'default'     => '#565656',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'width-100'   => true,
                                'css_identifier' => 'color',
                                'css_selectors' => $title_content_selector
                            ),//"#000000",

                            'color_active_css'           => array(
                                'input_type'  => 'wp_color_alpha',
                                'title_width' => 'width-100',
                                'title'       => __( 'Text color when active', 'text_doma' ),
                                'default'     => '#1e261f',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'width-100'   => true,
                                'css_identifier' => 'color',
                                'css_selectors' => array( '.sek-accord-item .sek-accord-title:hover *', '[data-sek-expanded="true"] .sek-accord-title *')
                            ),//"#000000",

                            'font_family_css' => array(
                                'input_type'  => 'font_picker',
                                'title'       => __( 'Font family', 'text_doma' ),
                                'default'     => '',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'refresh_fonts' => true,
                                'css_identifier' => 'font_family',
                                'css_selectors' => $title_content_selector,
                                'html_before' => '<hr/><h3>' . __('FONT OPTIONS') .'</h3>',
                                'html_after' => sprintf('<span class="czr-notice"><i class="far fa-lightbulb"></i> %1s => <a href="%2s" target="_blank" rel="noopener noreferrer">%2$s</a></span><hr/>',
                                    __('Find inspiration'),
                                    'https://fonts.google.com/?sort=popularity'
                                )
                            ),
                            'font_size_css'       => array(
                                'input_type'  => 'range_with_unit_picker_device_switcher',
                                'default'     => array( 'desktop' => '16px' ),
                                'title_width' => 'width-100',
                                'title'       => __( 'Font size', 'text_doma' ),
                                'min' => 0,
                                'max' => 100,
                                'width-100' => true,
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'font_size',
                                'css_selectors' => $title_content_selector
                            ),//16,//"14px",
                            'line_height_css'     => array(
                                'input_type'  => 'range_with_unit_picker',
                                'title'       => __( 'Line height', 'text_doma' ),
                                'default'     => '1.5em',
                                'min' => 0,
                                'max' => 10,
                                'step' => 0.1,
                                'width-100' => true,
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'line_height',
                                'css_selectors' => $title_content_selector
                            ),//24,//"20px",
                            'title_border_w_css' => array(
                                'input_type'  => 'range_with_unit_picker',
                                'title'       => __( 'Border bottom weight', 'text_doma' ),
                                'min' => 0,
                                'max' => 80,
                                'default' => '1px',
                                'title_width' => 'width-100',
                                'width-100'   => true,
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'border_width',
                                'css_selectors' => '.sek-accord-wrapper .sek-accord-item .sek-accord-title',
                                'html_before' => '<hr/><h3>' . __('BORDER BOTTOM') .'</h3>'
                            ),
                            'title_border_c_css' => array(
                                'input_type'  => 'wp_color_alpha',
                                'title'       => __( 'Border bottom color', 'text_doma' ),
                                'width-100'   => true,
                                'default'     => '#e3e3e3',
                                //'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'border_color',
                                'css_selectors' => '.sek-accord-wrapper .sek-accord-item .sek-accord-title'
                            ),
                            'spacing_css'     => array(
                                'input_type'  => 'spacingWithDeviceSwitcher',
                                'title'       => __( 'Spacing', 'text_doma' ),
                                'default'     => array('desktop' => array('padding-top' => '15', 'padding-right' => '20', 'padding-left' => '20', 'padding-bottom' => '15', 'unit' => 'px')),//consistent with SCSS
                                'title_width' => 'width-100',
                                'width-100'   => true,
                                'refresh_markup'     => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'spacing_with_device_switcher',
                                'css_selectors'      => '.sek-accord-item .sek-accord-title',
                                'html_before' => '<hr/><h3>' . __('SPACING') .'</h3>'
                            )
                        )
                    ),
                    array(
                        'title' => __( 'Content style', 'text_doma' ),
                        'inputs' => array(
                            'ct_bg_css' => array(
                                'input_type'  => 'wp_color_alpha',
                                'title'       => __('Backround color', 'text_doma'),
                                'width-100'   => true,
                                'title_width' => 'width-100',
                                'default'    => '#f2f2f2',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'background_color',
                                'css_selectors' => array('.sek-accord-item .sek-accord-content'),
                                'html_before' => '<h3>' . __('COLOR AND BACKGROUND') .'</h3>'
                            ),
                            'ct_color_css'           => array(
                                'input_type'  => 'wp_color_alpha',
                                'title'       => __( 'Text color', 'text_doma' ),
                                'default'     => '#1e261f',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'width-100'   => true,
                                'css_identifier' => 'color',
                                'css_selectors' => $main_content_selector
                            ),//"#000000",

                            'ct_font_family_css' => array(
                                'input_type'  => 'font_picker',
                                'title'       => __( 'Font family', 'text_doma' ),
                                'default'     => '',
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'refresh_fonts' => true,
                                'css_identifier' => 'font_family',
                                'css_selectors' => $main_content_selector,
                                'html_before' => '<hr/><h3>' . __('FONT OPTIONS') .'</h3>',
                                'html_after' => sprintf('<span class="czr-notice"><i class="far fa-lightbulb"></i> %1s => <a href="%2s" target="_blank" rel="noopener noreferrer">%2$s</a></span><hr/>',
                                    __('Find inspiration'),
                                    'https://fonts.google.com/?sort=popularity'
                                )
                            ),
                            'ct_font_size_css'       => array(
                                'input_type'  => 'range_with_unit_picker_device_switcher',
                                'default'     => array( 'desktop' => '16px' ),
                                'title_width' => 'width-100',
                                'title'       => __( 'Font size', 'text_doma' ),
                                'min' => 0,
                                'max' => 100,
                                'width-100'         => true,
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'font_size',
                                'css_selectors' => $main_content_selector
                            ),//16,//"14px",
                            'ct_line_height_css'     => array(
                                'input_type'  => 'range_with_unit_picker',
                                'title'       => __( 'Line height', 'text_doma' ),
                                'default'     => '1.5em',
                                'min' => 0,
                                'max' => 10,
                                'step' => 0.1,
                                'width-100'         => true,
                                'refresh_markup' => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'line_height',
                                'css_selectors' => $main_content_selector
                            ),//24,//"20px",
                            'ct_spacing_css'     => array(
                                'input_type'  => 'spacingWithDeviceSwitcher',
                                'title'       => __( 'Spacing', 'text_doma' ),
                                'default'     => array('desktop' => array('padding-top' => '15', 'padding-right' => '20', 'padding-left' => '20', 'padding-bottom' => '15', 'unit' => 'px')),//consistent with SCSS
                                'title_width' => 'width-100',
                                'width-100'   => true,
                                'refresh_markup'     => false,
                                'refresh_stylesheet' => true,
                                'css_identifier' => 'spacing_with_device_switcher',
                                'css_selectors'      => '.sek-accord-item .sek-accord-content',
                                'html_before' => '<hr/><h3>' . __('SPACING') .'</h3>'
                            )
                        )//inputs
                    )
                )//tabs
            )
        ),
        'render_tmpl_path' => '',
    );
}




/* ------------------------------------------------------------------------- *
 *  SCHEDULE CSS RULES FILTERING
/* ------------------------------------------------------------------------- */
// PER ITEM CSS DESIGN => FILTERING OF EACH ITEM MODEL, TARGETING THE ID ( [data-sek-item-id="893af157d5e3"] )
//add_filter( 'sek_add_css_rules_for_single_item_in_module_type___czr_accordion_collection_child', '\Nimble\sek_add_css_rules_for_items_in_czr_accordion_collection_child', 10, 2 );

// filter documented in Sek_Dyn_CSS_Builder::sek_css_rules_sniffer_walker
// Note : $complete_modul_model has been normalized
// @return populated $rules
// @param $params
// Array
// (
//     [input_list] => Array
//         (
//             [icon] => fab fa-acquisitions-incorporated
//             [link] => https://twitter.com/home
//             [title_attr] => Follow me on twitter
//             [link_target] =>
//             [color_css] => #dd9933
//             [use_custom_color_on_hover] =>
//             [social_color_hover] => #dd3333
//             [id] => 62316ab99b4d
//         )
//     [parent_module_id] =>
//     [module_type] => czr_accordion_collection_child
//     [module_css_selector] => Array
//         (
//             [0] => .sek-social-icon
//         )

// )
function sek_add_css_rules_for_items_in_czr_accordion_collection_child( $rules, $params ) {
    // $item_input_list = wp_parse_args( $item_input_list, $default_value_model );
    $item_model = isset( $params['input_list'] ) ? $params['input_list'] : array();

    // VERTICAL ALIGNMENT
    // if ( !empty( $item_model[ 'v_alignment' ] ) ) {
    //     if ( !is_array( $item_model[ 'v_alignment' ] ) ) {
    //         sek_error_log( __FUNCTION__ . ' => error => the v_alignment option should be an array( {device} => {alignment} )');
    //     }
    //     $v_alignment_value = is_array( $item_model[ 'v_alignment' ] ) ? $item_model[ 'v_alignment' ] : array();
    //     $v_alignment_value = wp_parse_args( $v_alignment_value, array(
    //         'desktop' => 'center',
    //         'tablet' => '',
    //         'mobile' => ''
    //     ));
    //     $mapped_values = array();
    //     foreach ( $v_alignment_value as $device => $align_val ) {
    //         switch ( $align_val ) {
    //             case 'top' :
    //                 $mapped_values[$device] = "flex-start";
    //             break;
    //             case 'center' :
    //                 $mapped_values[$device] = "center";
    //             break;
    //             case 'bottom' :
    //                 $mapped_values[$device] = "flex-end";
    //             break;
    //         }
    //     }
    //     $rules = sek_set_mq_css_rules( array(
    //         'value' => $mapped_values,
    //         'css_property' => 'align-items',
    //         'selector' => sprintf( '[data-sek-id="%1$s"]  [data-sek-item-id="%2$s"] .sek-slider-text-wrapper', $params['parent_module_id'], $item_model['id'] )
    //     ), $rules );
    // }//Vertical alignment


    return $rules;
}




// GLOBAL CSS DESIGN => FILTERING OF THE ENTIRE MODULE MODEL
add_filter( 'sek_add_css_rules_for_module_type___czr_accordion_module', '\Nimble\sek_add_css_rules_for_czr_accordion_module', 10, 2 );

// filter documented in Sek_Dyn_CSS_Builder::sek_css_rules_sniffer_walker
// Note : $complete_modul_model has been normalized
// @return populated $rules
function sek_add_css_rules_for_czr_accordion_module( $rules, $complete_modul_model ) {
    if ( empty( $complete_modul_model['value'] ) || !is_array( $complete_modul_model['value'] ) )
      return $rules;

    $value = $complete_modul_model['value'];
    $defaults = sek_get_default_module_model( 'czr_accordion_module');
    $accord_defaults = $defaults['accord_opts'];

    $accord_opts = $value['accord_opts'];

    //sek_error_log('sek_get_default_module_model() ?', sek_get_default_module_model( 'czr_accordion_module') );

    // TEXT COLOR ( for the plus / minus icon )
    if ( !empty( $accord_opts[ 'color_css' ] ) && $accord_defaults[ 'color_css' ] != $accord_opts[ 'color_css' ] ) {
        $rules[] = array(
            'selector' => sprintf( '[data-sek-id="%1$s"] .sek-module-inner .sek-accord-wrapper .sek-accord-item .expander span', $complete_modul_model['id'] ),
            'css_rules' => 'background:'. $accord_opts[ 'color_css' ] .';',
            'mq' =>null
        );
    }
    // ACTIVE / HOVER TEXT COLOR ( for the plus / minus icon )
    if ( !empty( $accord_opts[ 'color_active_css' ] ) && $accord_defaults[ 'color_active_css' ] != $accord_opts[ 'color_active_css' ] ) {
        $rules[] = array(
            'selector' => sprintf( '[data-sek-id="%1$s"] .sek-module-inner .sek-accord-wrapper [data-sek-expanded="true"] .sek-accord-title .expander span, [data-sek-id="%1$s"] .sek-module-inner .sek-accord-wrapper .sek-accord-item .sek-accord-title:hover .expander span', $complete_modul_model['id'] ),
            'css_rules' => sprintf('background:%s;', $accord_opts[ 'color_active_css' ] ),
            'mq' =>null
        );
    }

    return $rules;
}


?><?php
/* ------------------------------------------------------------------------- *
 *  LOAD AND REGISTER SHORTCODE MODULE
/* ------------------------------------------------------------------------- */
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_czr_shortcode_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_shortcode_module',
        'name' => __('Shortcode', 'text_doma'),
        'css_selectors' => array( '.sek-module-inner > *' ),
        'sanitize_callback' => '\Nimble\sek_sanitize_czr_shortcode_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'tmpl' => array(
            'item-inputs' => array(
                'text_content' => array(
                    'input_type'        => 'nimble_tinymce_editor',
                    'editor_params'     => array(
                        'media_button' => true,
                        'includedBtns' => 'basic_btns_with_lists',
                    ),
                    'title'             => __( 'Write the shortcode(s) in the text editor', 'text_doma' ),
                    'default'           => '',
                    'width-100'         => true,
                    'title_width' => 'width-100',
                    'refresh_markup'    => '.sek-shortcode-content',
                    'notice_before' => __('A shortcode is a WordPress-specific code that lets you display predefined items. For example a trivial shortcode for a gallery looks like this [gallery].') . '<br/><br/>',
                    'notice_after' => __('You may use some html tags in the "text" tab of the editor.', 'text_domain_to_be_replaced')
                ),
                'refresh_button' => array(
                    'input_type'  => 'refresh_preview_button',
                    'title'       => __( '' , 'text_doma' ),
                    'refresh_markup' => false,
                    'refresh_stylesheet' => false,
                ),
                'lazyload' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Enable image lazy-loading', 'text_doma'),
                    'default'     => false,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20'
                ),
                // flex-box should be enabled by user and not active by default.
                // It's been implemented primarily to ease centering ( see https://github.com/presscustomizr/nimble-builder/issues/565 )
                // When enabled, it can create layout issues like : https://github.com/presscustomizr/nimble-builder/issues/576
                'use_flex' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Use a flex-box wrapper', 'text_doma'),
                    'default'     => false,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    'notice_after' => __('Flex-box is a CSS standard used to specify the layout of HTML pages. Using flex-box can make it easier to center the content of shortcodes.', 'text_doma')
                ),
                'h_alignment_css'        => array(
                    'input_type'  => 'horizAlignmentWithDeviceSwitcher',
                    'title'              => __( 'Horizontal alignment', 'text_doma' ),
                    'default'     => array( 'desktop' => 'center' ),
                    'refresh_markup'     => false,
                    'refresh_stylesheet' => true,
                    'css_identifier'     => 'h_flex_alignment',
                    'css_selectors'      => '.sek-module-inner > .sek-shortcode-content',
                    'title_width' => 'width-100',
                    'width-100'   => true,
                    'html_before' => '<hr/><h3>' . __('ALIGNMENT') .'</h3>'
                )
            )
        ),
        'render_tmpl_path' => "shortcode_module_tmpl.php",
    );
}

/* ------------------------------------------------------------------------- *
 *  SANITIZATION
/* ------------------------------------------------------------------------- */
// convert into a json to prevent emoji breaking global json data structure
// fix for https://github.com/presscustomizr/nimble-builder/issues/544
function sek_sanitize_czr_shortcode_module( $content ) {
    if ( is_array($content) && !empty($content['text_content']) ) {
        $content['text_content'] = sek_maybe_encode_richtext($content['text_content']);
    }
    return $content;
}
?><?php

/* ------------------------------------------------------------------------- *
 *  LOAD AND REGISTER GALLERY MODULE
/* ------------------------------------------------------------------------- */
//Fired in add_action( 'after_setup_theme', 'sek_register_modules', 50 );
function sek_get_module_params_for_czr_gallery_module() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_gallery_module',
        'is_father' => true,
        'children' => array(
            'gallery_collec' => 'czr_gallery_collection_child',
            'gallery_opts' => 'czr_gallery_opts_child'
        ),
        'name' => __('Gallery', 'text_doma'),
        'starting_value' => array(
            'gallery_collec' => array(
                array( 'img' =>  NIMBLE_BASE_URL . '/assets/img/default-img.png' ),
                array( 'img' =>  NIMBLE_BASE_URL . '/assets/img/default-img.png' ),
                array( 'img' =>  NIMBLE_BASE_URL . '/assets/img/default-img.png' ),
                array( 'img' =>  NIMBLE_BASE_URL . '/assets/img/default-img.png' ),
                array( 'img' =>  NIMBLE_BASE_URL . '/assets/img/default-img.png' ),
                array( 'img' =>  NIMBLE_BASE_URL . '/assets/img/default-img.png' )
            )
        ),
        'sanitize_callback' => '\Nimble\sanitize_cb__czr_gallery_module',
        // 'validate_callback' => 'function_prefix_to_be_replaced_validate_callback__czr_social_module',
        'css_selectors' => array( '.sek-gal-wrapper' ),//array( '.sek-icon i' ),
        'render_tmpl_path' => "gallery_tmpl.php",
        // 'front_assets' => array(
        //       'czr-font-awesome' => array(
        //           'type' => 'css',
        //           //'handle' => 'czr-font-awesome',
        //           'src' => NIMBLE_BASE_URL . '/assets/front/fonts/css/fontawesome-all.min.css'
        //           //'deps' => array()
        //       )
        // )
    );
}

/* ------------------------------------------------------------------------- *
 *  SANITIZATION
/* ------------------------------------------------------------------------- */
// convert into a json to prevent emoji breaking global json data structure
// fix for https://github.com/presscustomizr/nimble-builder/issues/544
function sanitize_cb__czr_gallery_module( $value ) {
    if ( !is_array( $value ) )
        return $value;
    if ( !empty($value['gallery_collec']) && is_array( $value['gallery_collec'] ) ) {
        foreach( $value['gallery_collec'] as $key => $data ) {
            if ( array_key_exists( 'custom_caption', $data ) && is_string( $data['custom_caption'] ) ) {
                $value['gallery_collec'][$key]['custom_caption'] = sek_maybe_encode_richtext( $data['custom_caption'] );
            }
            // if ( array_key_exists( 'title_text', $data ) && is_string( $data['title_text'] ) ) {
            //     $value['gallery_collec'][$key]['title_text'] = sek_maybe_encode_richtext( $data['title_text'] );
            // }
        }
    }
    return $value;
}

/* ------------------------------------------------------------------------- *
 *  MAIN SETTINGS
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_gallery_collection_child() {
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_gallery_collection_child',
        'is_crud' => true,
        'name' => sprintf('<i class="material-icons" style="font-size: 1.2em;">toc</i> %1$s', __( 'Image collection', 'text_doma' ) ),
        // 'starting_value' => array(
        //     'custom_caption' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed non risus. Suspendisse lectus tortor, dignissim sit amet, adipiscing nec, ultricies sed, dolor.'
        // ),
        //'sanitize_callback' => '\Nimble\sanitize_callback__czr_simple_form_module',
        //'css_selectors' => array( '.sek-social-icon' ),//array( '.sek-icon i' ),
        'tmpl' => array(
            'pre-item' => array(
                // 'page-id' => array(
                //     'input_type'  => 'content_picker',
                //     'title'       => __('Pick a page', 'text_doma')
                // ),
                'img' => array(
                    'input_type'  => 'upload',
                    'title'       => __('Pick an image', 'text_doma'),
                    'default'     => ''
                ),
            ),
            'item-inputs' => array(
                'img' => array(
                    'input_type'  => 'upload',
                    'title'       => __('Pick an image', 'text_doma'),
                    'default'     => ''
                ),
                'custom_caption' => array(
                    'input_type'         => 'text',
                    'title' => __('Image title displayed as tooltip and in the lightbox popup', 'text_domain_to' ),
                    'default'            => '',
                    'title_width' => 'width-100',
                    'width-100'         => true,
                    'notice_after' => __('When left empty, Nimble Builder will use by order of priority the image caption, description, and image title. Those properties can be edited for each image in the media library.')
                ),
            )//'item-inputs'
        ),
        'render_tmpl_path' => '',
    );
}


/* ------------------------------------------------------------------------- *
 *  GALLERY OPTIONS
/* ------------------------------------------------------------------------- */
function sek_get_module_params_for_czr_gallery_opts_child() {
    $title_content_selector = array( '.sek-accord-item .sek-accord-title *' );
    $main_content_selector = array( '.sek-accord-item .sek-accord-content', '.sek-accord-item .sek-accord-content *' );
    $pro_text = '';
    if ( !sek_is_pro() ) {
        $pro_text = sprintf( __( '%1$s + cool additional options', 'text-doma'),
            sprintf( '<a href="%1$s" target="_blank" rel="noopener noreferrer" style="text-decoration:underline">%2$s</a>',
                'https://nimblebuilder.com/gallery-examples/#masonry',
                __('masonry galleries', 'text-doma')
            )
        );
        $pro_text = sek_get_pro_notice_for_czr_input( $pro_text );
    }
    return array(
        'dynamic_registration' => true,
        'module_type' => 'czr_gallery_opts_child',
        'name' => sprintf('<i class="material-icons" style="font-size: 1.2em;">tune</i> %1$s', __( 'Gallery options', 'text_doma' ) ),
        //'sanitize_callback' => '\Nimble\sanitize_callback__czr_simple_form_module',
        // 'starting_value' => array(
        //     'button_text' => __('Click me','text_doma'),
        //     'color_css'  => '#ffffff',
        //     'bg_color_css' => '#020202',
        //     'bg_color_hover' => '#151515', //lighten 15%,
        //     'use_custom_bg_color_on_hover' => 0,
        //     'border_radius_css' => '2',
        //     'h_alignment_css' => 'center',
        //     'use_box_shadow' => 1,
        //     'push_effect' => 1
        // ),
        //'css_selectors' => array( '.sek-social-icons-wrapper' ),//array( '.sek-icon i' ),
        'tmpl' => array(
            'item-inputs' => array(
                'img_size' => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __('Select the image size', 'text_doma'),
                    'default'     => 'large',
                    'choices'     => sek_get_select_options_for_input_id( 'img-size' ),
                    'notice_before' => __('Select a size for this image among those generated by WordPress.', 'text_doma' ),
                    'html_after' => '<hr/>'
                ),
                'columns'  => array(
                    'input_type'  => 'range_simple_device_switcher',
                    'title'       => __( 'Number of columns', 'text_doma' ),
                    'default'     => array( 'desktop' => '3', 'tablet' => '2', 'mobile' => '1' ),
                    'min'         => 1,
                    'max'         => 24,
                    'step'        => 1,
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'refresh_stylesheet' => true, //<= some CSS rules are layout dependant
                    'html_after' => $pro_text
                ),//null,

                'custom-rows-columns' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Define custom row and column dimensions', 'text_doma'),
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                    //'refresh_markup' => true,
                    'refresh_stylesheet' => true,
                    'default'     => 0,
                    'html_before' => '<hr/>'
                    //'notice_after' => __('When enabled and possible, Nimble will use the post thumbnail.', 'text_doma'),
                ),

                'column_width'  => array(
                    'input_type'  => 'range_with_unit_picker_device_switcher',
                    'title'       => __( 'Custom column width', 'text_doma' ),
                    'min' => 0,
                    'max' => 1000,
                    'default'     => array( 'desktop' => '200px', 'tablet' => '150px', 'mobile' => '100px' ),
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true
                ),//null,

                'raw_height'  => array(
                    'input_type'  => 'range_with_unit_picker_device_switcher',
                    'title'       => __( 'Custom raw height', 'text_doma' ),
                    'min' => 0,
                    'max' => 1000,
                    'default'     => array( 'desktop' => '200px', 'tablet' => '150px', 'mobile' => '100px' ),
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true
                ),//null,

                'column_gap'  => array(
                    'input_type'  => 'range_with_unit_picker_device_switcher',
                    'title'       => __( 'Space between columns', 'text_doma' ),
                    'min' => 0,
                    'max' => 100,
                    'default'     => array( 'desktop' => '5px' ),
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true,
                    'html_before' => '<hr/>'
                ),//null,

                'row_gap'  => array(
                    'input_type'  => 'range_with_unit_picker_device_switcher',
                    'title'       => __( 'Space between rows', 'text_doma' ),
                    'min' => 0,
                    'max' => 100,
                    'default'     => array( 'desktop' => '5px' ),
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'refresh_markup' => false,
                    'refresh_stylesheet' => true
                ),//null,

                'link-to' => array(
                    'input_type'  => 'simpleselect',
                    'title'       => __('Schedule an action on click or tap', 'text_doma'),
                    'default'     => 'img-lightbox',
                    'choices'     => array(
                        'no-link' => __('No click action', 'text_doma' ),
                        'img-lightbox' =>__('Lightbox : enlarge the image, and dim out the rest of the content', 'text_doma' ),
                        'img-file' => __('Link to image file', 'text_doma' ),
                        'img-page' =>__('Link to image page', 'text_doma' )
                    ),
                    'title_width' => 'width-100',
                    'width-100'   => true,
                    'notice_after' => __('Note that some click actions are disabled during customization.', 'text_doma' ),
                    'html_before' => '<hr/>'
                ),

                'link-target' => array(
                    'input_type'  => 'nimblecheck',
                    'title'       => __('Open link in a new browser tab', 'text_doma'),
                    'default'     => false,
                    'title_width' => 'width-80',
                    'input_width' => 'width-20',
                )
            )
        ),
        'render_tmpl_path' => '',
    );
}




/* ------------------------------------------------------------------------- *
 *  SCHEDULE CSS RULES FILTERING
/* ------------------------------------------------------------------------- */
// GLOBAL CSS DESIGN => FILTERING OF THE ENTIRE MODULE MODEL
add_filter( 'sek_add_css_rules_for_module_type___czr_gallery_module', '\Nimble\sek_add_css_rules_for_czr_gallery_module', 10, 2 );

// filter documented in Sek_Dyn_CSS_Builder::sek_css_rules_sniffer_walker
// Note : $complete_modul_model has been normalized
// @return populated $rules
function sek_add_css_rules_for_czr_gallery_module( $rules, $complete_modul_model ) {
    if ( empty( $complete_modul_model['value'] ) || !is_array( $complete_modul_model['value'] ) )
      return $rules;

    $value = $complete_modul_model['value'];
    $main_settings = $value['gallery_opts'];

    //sek_error_log('sek_get_default_module_model() ?', sek_get_default_module_model( 'czr_gallery_module') );

    // TABLET AND MOBILE BREAKPOINT SETUP
    $mobile_breakpoint = Sek_Dyn_CSS_Builder::$breakpoints['sm'];// 576
    $tablet_breakpoint = Sek_Dyn_CSS_Builder::$breakpoints['md'];// 768

    $custom_tablet_breakpoint = $tablet_breakpoint;

    // Is there a global custom breakpoint set ?
    $global_custom_breakpoint = intval( sek_get_global_custom_breakpoint() );
    $has_global_custom_breakpoint = $global_custom_breakpoint >= 1;
    // Does the parent section have a custom breakpoint set ?
    $section_custom_breakpoint = intval( sek_get_closest_section_custom_breakpoint( array( 'searched_level_id' => $complete_modul_model['id'] ) ) );
    $has_section_custom_breakpoint = $section_custom_breakpoint >= 1;

    // Use section breakpoint in priority, then global one
    if ( $has_section_custom_breakpoint ) {
        $custom_tablet_breakpoint = $section_custom_breakpoint;
    } else if ( $has_global_custom_breakpoint ) {
        $custom_tablet_breakpoint = $global_custom_breakpoint;
    }

    $tablet_breakpoint = $custom_tablet_breakpoint;
    // If user define breakpoint ( => always for tablet ) is < to $mobile_breakpoint, make sure $mobile_breakpoint is reset to tablet_breakpoint
    $mobile_breakpoint = $mobile_breakpoint >= $tablet_breakpoint ? $tablet_breakpoint : $mobile_breakpoint;

    $tab_bp_val = $tablet_breakpoint - 1;// -1 to avoid "blind" spots @see https://github.com/presscustomizr/nimble-builder/issues/551
    $mob_bp_val = $mobile_breakpoint - 1;// -1 to avoid "blind" spots @see https://github.com/presscustomizr/nimble-builder/issues/551


    // GRID LAYOUT
    // NUMBER OF COLUMNS BY DEVICE IN CASE OF A CUSTOM BREAKPOINT, GLOBAL OR FOR THE SECTION
    // Get the default breakpoint values

    // BASE CSS RULES
    // .sek-gal-items.sek-all-col-1 {
    //   -ms-grid-columns: minmax(0,1fr);
    //   grid-template-columns: repeat(1, minmax(0,1fr));
    // }
    // .sek-gal-items.sek-all-col-2 {
    //   -ms-grid-columns: minmax(0,1fr) 20px minmax(0,1fr);
    //   grid-template-columns: repeat(2, minmax(0,1fr));
    //   grid-column-gap: 20px;
    //   grid-row-gap: 20px;
    // }
    $col_nb_gap_map = [
        'col-1' => null,
        'col-2' => '10px',
        'col-3' => '10px',
        'col-4' => '10px',
        'col-5' => '10px',
        'col-6' => '10px',
        'col-7' => '10px',
        'col-8' => '10px',
        'col-9' => '10px',
        'col-10' => '5px',
        'col-11' => '5px',
        'col-12' => '5px',
        'col-13' => '5px',
        'col-14' => '5px',
        'col-15' => '5px',
        'col-16' => '5px',
        'col-17' => '5px',
        'col-18' => '5px',
        'col-19' => '5px',
        'col-20' => '5px',
        'col-21' => '5px',
        'col-22' => '5px',
        'col-23' => '5px',
        'col-24' => '5px'
    ];

    if ( !isset(Nimble_Manager()->generic_gallery_grid_css_rules_written) ) {
        foreach ($col_nb_gap_map as $col_nb_index => $col_gap) {
            $col_nb = intval( str_replace('col-', '', $col_nb_index ) );
            $ms_grid_columns = [];
            // Up to 24 columns
            for ($j=1; $j <= $col_nb; $j++) {
                if ( $j > 1 ) {
                    $ms_grid_columns[] = $col_gap;
                }
                $ms_grid_columns[] = 'minmax(0,1fr)';
            }
            $ms_grid_columns = implode(' ', $ms_grid_columns);

            $grid_template_columns = "repeat({$col_nb}, minmax(0,1fr))";

            $col_css_rules = [
                '-ms-grid-columns:' . $ms_grid_columns,
                'grid-template-columns:' . $grid_template_columns
            ];
            if ( $col_nb > 1 ) {
                $col_css_rules[] = 'grid-column-gap:'.$col_gap;
                $col_css_rules[] = 'grid-row-gap:'.$col_gap;
            }
            $rules[] = array(
                'selector' => '.sek-gal-wrapper .sek-gal-items.sek-all-col-'.$col_nb,
                'css_rules' => implode(';', $col_css_rules),
                'mq' =>null
            );
        }
        Nimble_Manager()->generic_gallery_grid_css_rules_written = true;
    }

    // MEDIA QUERIES
    $main_settings['columns'] = is_array($main_settings['columns']) ? $main_settings['columns'] : [];
    $cols_by_device = wp_parse_args(
        $main_settings['columns'],
        [ 'desktop' => '3', 'tablet' => '2', 'mobile' => '1' ]// as per registration params
    );
    if ( sek_is_pro() && array_key_exists('min_column_width', $main_settings ) ) {
        $min_column_width_by_device = wp_parse_args(
            $main_settings['min_column_width'],
            [ 'desktop' => '250', 'tablet' => '250', 'mobile' => '250' ]
        );
    }

    // Normalize column and row dimensions
    // will be used later on
    $col_width_by_device = $main_settings['column_width'];
    $col_width_by_device = is_array( $col_width_by_device ) ? $col_width_by_device : [];
    $col_width_by_device = wp_parse_args(
        $main_settings['column_width'],
        [ 'desktop' => '200px', 'tablet' => '200px', 'mobile' => '200px' ]
    );

    // replace % by vh when needed
    $col_width_by_device_with_unit = $col_width_by_device;
    foreach ($col_width_by_device as $device => $num_unit ) {
        $numeric = sek_extract_numeric_value( $num_unit );
        $numeric = $numeric < 0 ? '0' : $numeric;
        $col_width_by_device_with_unit[$device] = '';
        // Leave the device value empty if === to default
        // Otherwise it will print a duplicated dynamic css rules, already hardcoded in the static stylesheet
        // fixes https://github.com/presscustomizr/nimble-builder/issues/419
        //if ( !empty( $num_unit ) && $numeric.'px' !== $defaults[$device].'' ) {
        if ( !empty( $num_unit ) ) {
            $unit = sek_extract_unit( $num_unit );
            $col_width_by_device_with_unit[$device] = $numeric . $unit;
        }
    }


    $raw_height_by_device = wp_parse_args(
        $main_settings['raw_height'],
        [ 'desktop' => '200px', 'tablet' => '200px', 'mobile' => '200px' ]
    );
     // replace % by vh when needed
    $raw_height_by_device_with_unit = $raw_height_by_device;
    foreach ($raw_height_by_device as $device => $num_unit ) {
         $numeric = sek_extract_numeric_value( $num_unit );
         $numeric = $numeric < 0 ? '0' : $numeric;
         $raw_height_by_device_with_unit[$device] = '';
         // Leave the device value empty if === to default
         // Otherwise it will print a duplicated dynamic css rules, already hardcoded in the static stylesheet
         // fixes https://github.com/presscustomizr/nimble-builder/issues/419
         //if ( !empty( $num_unit ) && $numeric.'px' !== $defaults[$device].'' ) {
         if ( !empty( $num_unit ) ) {
             $unit = sek_extract_unit( $num_unit );
             $raw_height_by_device_with_unit[$device] = $numeric . $unit;
         }
    }




    $col_css_rules = '';
    foreach ( $cols_by_device as $device => $col_nb ) {
        $col_nb = intval($col_nb);
        // First define the media queries using custom user breakpoints
        switch( $device ) {
            case 'desktop' :
                $media_qu = "(min-width:{$tablet_breakpoint}px)";
            break;
            case 'tablet' :
                if ( $mobile_breakpoint >= ( $tab_bp_val ) ) {
                    $media_qu = "(max-width:{$tab_bp_val}px)";
                } else {
                    $media_qu = "(min-width:{$mob_bp_val}px) and (max-width:{$tab_bp_val}px)";
                }
            break;
            case 'mobile' :
                $media_qu = "(max-width:{$mob_bp_val}px)";
            break;
        }


        // Then define the selector + css rules by device
        // SELECTOR
        $selector = sprintf('[data-sek-id="%1$s"] .sek-gal-wrapper .sek-gal-items.sek-%2$s-col-%3$s',
            $complete_modul_model['id'],
            $device,
            $col_nb
        );

        $has_custom_row_and_column_dimensions = sek_booleanize_checkbox_val( $main_settings['custom-rows-columns'] );

        // Custom row and column dimension is not compatible with masonry
        if ( array_key_exists('masonry_on', $main_settings ) ) {
            $has_custom_row_and_column_dimensions = $has_custom_row_and_column_dimensions && !sek_booleanize_checkbox_val( $main_settings['masonry_on'] );
        }
        $has_auto_fill_column = array_key_exists('auto_fill', $main_settings ) && sek_booleanize_checkbox_val( $main_settings['auto_fill'] );//<= pro
        // CSS RULES
        //     .sek-gal-items.sek-desktop-col-1 {
        //       -ms-grid-columns: minmax(0,1fr);
        //       grid-template-columns: repeat(1, minmax(0,1fr));
        //     }
        //     .sek-gal-items.sek-desktop-col-2 {
        //       -ms-grid-columns: minmax(0,1fr) 20px minmax(0,1fr);
        //       grid-template-columns: repeat(2, minmax(0,1fr));
        //       grid-column-gap: 20px;
        //       grid-row-gap: 20px;
        //     }
        // July 2021 : introduction of the auto-fill rule in pro
        if ( sek_is_pro() && $has_auto_fill_column ) {
            $min_col_width = 250;
            if ( array_key_exists($device, $min_column_width_by_device ) ) {
                $min_col_width = intval( $min_column_width_by_device[$device] );
            }
            $grid_template_columns = "repeat(auto-fill, minmax({$min_col_width}px,1fr));";
            // in this case, no need to add '-ms-grid-columns' rule
            $col_css_rules = [
                'grid-template-columns:' . $grid_template_columns
            ];
        } else {
            $ms_grid_columns = [];
            // Up to 24 columns
            for ($i=1; $i <= $col_nb; $i++) {
                if ( $i > 1 ) {
                    $col_gap = array_key_exists('col-'.$col_nb, $col_nb_gap_map ) ? $col_nb_gap_map['col-'.$col_nb] : '5px';
                    $ms_grid_columns[] = $col_gap;
                }
                $ms_grid_columns[] = 'minmax(0,1fr)';
            }

            $ms_grid_columns = implode(' ', $ms_grid_columns);

            $grid_template_columns = "repeat({$col_nb}, minmax(0,1fr))";
            $col_css_rules = [
                '-ms-grid-columns:' . $ms_grid_columns,
                'grid-template-columns:' . $grid_template_columns
            ];
        }
        if ( $col_nb > 1 ) {
            $col_gap = array_key_exists('col-'.$col_nb, $col_nb_gap_map ) ? $col_nb_gap_map['col-'.$col_nb] : '5px';
            $col_css_rules[] = 'grid-column-gap:'.$col_gap;
            $col_css_rules[] = 'grid-row-gap:'.$col_gap;
        }



        // Column width and row height
        if ( $has_custom_row_and_column_dimensions ) {
            if ( !$has_auto_fill_column ) {
                $norm_col_nb = $col_nb > 0 ? $col_nb : 1;

                if ( array_key_exists($device, $col_width_by_device_with_unit ) ) {
                    $col_width = $col_width_by_device_with_unit[$device];
                }
                if ( !empty($col_width) ) {
                    $grid_template_columns = "repeat({$norm_col_nb}, {$col_width});";
                    $col_css_rules[] = 'grid-template-columns:' . $grid_template_columns;
                }
            }

            if ( array_key_exists($device, $raw_height_by_device_with_unit ) ) {
                $raw_height = $raw_height_by_device_with_unit[$device];
            }
            if ( !empty($raw_height) ) {
                $col_css_rules[] = 'grid-auto-rows:' . $raw_height;
                $col_css_rules[] = '-ms-grid-rows:' . $raw_height;
                $col_css_rules[] = 'grid-template-rows:' . $raw_height;
            }
        }

        $col_css_rules_ready = [];
        if ( 'desktop' != $device ) {
            foreach ($col_css_rules as $col_rule) {
                $col_css_rules_ready[] = $col_rule .= '';//!important';
            }
        } else {
            $col_css_rules_ready = $col_css_rules;
        }
        $col_css_rules_ready = implode(';', $col_css_rules_ready);


        $rules[] = array(
            'selector' => $selector,
            'css_rules' => $col_css_rules_ready,
            'mq' => $media_qu
        );
    }// end foreach
    // END OF GRID LAYOUT




    // COLUMN AND ROW GAP
    // Horizontal Gap
    $gap = $main_settings['column_gap'];
    $gap = is_array( $gap ) ? $gap : array();
    $defaults = array(
        'desktop' => '10px',// <= this value matches the static CSS rule and the input default for the module
        'tablet' => '',
        'mobile' => ''
    );
    $gap = wp_parse_args( $gap, $defaults );
    // replace % by vh when needed
    $gap_ready_value = $gap;
    foreach ($gap as $device => $num_unit ) {
        $numeric = sek_extract_numeric_value( $num_unit );
        $numeric = $numeric < 0 ? '0' : $numeric;
        $gap_ready_value[$device] = '';
        // Leave the device value empty if === to default
        // Otherwise it will print a duplicated dynamic css rules, already hardcoded in the static stylesheet
        // fixes https://github.com/presscustomizr/nimble-builder/issues/419
        //if ( !empty( $num_unit ) && $numeric.'px' !== $defaults[$device].'' ) {
        if ( !empty( $num_unit ) ) {
            $unit = sek_extract_unit( $num_unit );
            $gap_ready_value[$device] = $numeric . $unit;
        }
    }

    // for grid layout => gap between columns
    // for list layout => gap between image and content
    $rules = sek_set_mq_css_rules(array(
        'value' => $gap_ready_value,
        'css_property' => 'grid-column-gap',
        'selector' => implode( ',', [
            '.nb-loc [data-sek-id="'.$complete_modul_model['id'].'"] .sek-module-inner .sek-gal-wrapper .sek-gal-items'
        ] ),
        'is_important' => false,
        'level_id' => $complete_modul_model['id']
    ), $rules );

    // Vertical Gap => common to list and grid layout
    $v_gap = $main_settings['row_gap'];
    $v_gap = is_array( $v_gap ) ? $v_gap : array();
    $defaults = array(
        'desktop' => '10px',// <= this value matches the static CSS rule and the input default for the module
        'tablet' => '',
        'mobile' => ''
    );
    $v_gap = wp_parse_args( $v_gap, $defaults );
    // replace % by vh when needed
    $v_gap_ready_value = $v_gap;
    foreach ($v_gap as $device => $num_unit ) {
        $numeric = sek_extract_numeric_value( $num_unit );
        $numeric = $numeric < 0 ? 0 : $numeric;
        $v_gap_ready_value[$device] = '';
        // Leave the device value empty if === to default
        // Otherwise it will print a duplicated dynamic css rules, already hardcoded in the static stylesheet
        // fixes https://github.com/presscustomizr/nimble-builder/issues/419
        //if ( !empty( $num_unit ) && $numeric.'px' !== $defaults[$device].'' ) {
        if ( !empty( $num_unit ) ) {
            $unit = sek_extract_unit( $num_unit );
            $v_gap_ready_value[$device] = $numeric . $unit;
        }
    }

    $rules = sek_set_mq_css_rules(array(
        'value' => $v_gap_ready_value,
        'css_property' => 'grid-row-gap',
        'selector' => '.nb-loc [data-sek-id="'.$complete_modul_model['id'].'"] .sek-module-inner .sek-gal-wrapper .sek-gal-items',
        'is_important' => false,
        'level_id' => $complete_modul_model['id']
    ), $rules );
    // END OF COLUMN AND ROW GAP



    return $rules;
}












// PER ITEM CSS DESIGN => FILTERING OF EACH ITEM MODEL, TARGETING THE ID ( [data-sek-item-id="893af157d5e3"] )
//add_filter( 'sek_add_css_rules_for_single_item_in_module_type___czr_gallery_collection_child', '\Nimble\sek_add_css_rules_for_items_in_czr_gallery_collection_child', 10, 2 );

// filter documented in Sek_Dyn_CSS_Builder::sek_css_rules_sniffer_walker
// Note : $complete_modul_model has been normalized
// @return populated $rules
// @param $params
// Array
// (
//     [input_list] => Array
//         (
//             [icon] => fab fa-acquisitions-incorporated
//             [link] => https://twitter.com/home
//             [title_attr] => Follow me on twitter
//             [link_target] =>
//             [color_css] => #dd9933
//             [use_custom_color_on_hover] =>
//             [social_color_hover] => #dd3333
//             [id] => 62316ab99b4d
//         )
//     [parent_module_id] =>
//     [module_type] => czr_gallery_collection_child
//     [module_css_selector] => Array
//         (
//             [0] => .sek-social-icon
//         )

// )
function sek_add_css_rules_for_items_in_czr_gallery_collection_child( $rules, $params ) {
    // $item_input_list = wp_parse_args( $item_input_list, $default_value_model );
    $item_model = isset( $params['input_list'] ) ? $params['input_list'] : array();

    // VERTICAL ALIGNMENT
    // if ( !empty( $item_model[ 'v_alignment' ] ) ) {
    //     if ( !is_array( $item_model[ 'v_alignment' ] ) ) {
    //         sek_error_log( __FUNCTION__ . ' => error => the v_alignment option should be an array( {device} => {alignment} )');
    //     }
    //     $v_alignment_value = is_array( $item_model[ 'v_alignment' ] ) ? $item_model[ 'v_alignment' ] : array();
    //     $v_alignment_value = wp_parse_args( $v_alignment_value, array(
    //         'desktop' => 'center',
    //         'tablet' => '',
    //         'mobile' => ''
    //     ));
    //     $mapped_values = array();
    //     foreach ( $v_alignment_value as $device => $align_val ) {
    //         switch ( $align_val ) {
    //             case 'top' :
    //                 $mapped_values[$device] = "flex-start";
    //             break;
    //             case 'center' :
    //                 $mapped_values[$device] = "center";
    //             break;
    //             case 'bottom' :
    //                 $mapped_values[$device] = "flex-end";
    //             break;
    //         }
    //     }
    //     $rules = sek_set_mq_css_rules( array(
    //         'value' => $mapped_values,
    //         'css_property' => 'align-items',
    //         'selector' => sprintf( '[data-sek-id="%1$s"]  [data-sek-item-id="%2$s"] .sek-slider-text-wrapper', $params['parent_module_id'], $item_model['id'] )
    //     ), $rules );
    // }//Vertical alignment


    return $rules;
}


/* ------------------------------------------------------------------------- *
 *  REMOVE LAZY LOAD OF 3RD PARTY PLUGINS
/* ------------------------------------------------------------------------- */

add_filter('wp_get_attachment_image_attributes','Nimble\nimble_image_no_lazy');

function nimble_image_no_lazy( $attr ){
    $attr['class'] .= " no-lazy";
    return $attr;
}

/* ------------------------------------------------------------------------- *
 *  EXCLUDE LAZY LOAD FROM WP ROCKET PLUGIN
/* ------------------------------------------------------------------------- */

add_action( 'init', 'Nimble\nimble_include_no_lazy_class' );

function nimble_include_no_lazy_class(){
    $settings = get_option('wp_rocket_settings');
    if( is_array( $settings ) && !in_array('no-lazy', $settings['exclude_lazyload'] ) ){
        $settings['exclude_lazyload'][] = 'no-lazy';
        update_option( 'wp_rocket_settings', $settings );
    }
}

?>