<?php
// The base fmk is loaded @after_setup_theme:10
add_action( 'after_setup_theme', '\Nimble\sek_register_modules', 50 );
function sek_register_modules() {
    $modules = [
        // UI CONTENT PICKER
        'sek_content_type_switcher_module',
        'sek_module_picker_module',

        'sek_intro_sec_picker_module',
        'sek_features_sec_picker_module',
        'sek_contact_sec_picker_module',
        'sek_column_layouts_sec_picker_module',
        'sek_my_sections_sec_picker_module',

        // UI LEVEL MODULES
        'sek_level_bg_module',
        'sek_level_border_module',
        //'sek_level_section_layout_module',<// deactivated for now. Replaced by sek_level_width_section
        'sek_level_height_module',
        'sek_level_spacing_module',
        'sek_level_width_module',
        'sek_level_width_section',
        'sek_level_anchor_module',
        'sek_level_visibility_module',
        'sek_level_breakpoint_module',

        // local skope options modules
        'sek_local_template',
        'sek_local_widths',
        'sek_local_custom_css',
        'sek_local_reset',
        'sek_local_performances',
        'sek_local_header_footer',

        // global options modules
        'sek_global_breakpoint',
        'sek_global_widths',
        //'sek_global_reset',
        'sek_global_performances',
        'sek_global_header_footer',

        // FRONT MODULES
        'czr_simple_html_module',

        'czr_tiny_mce_editor_module',
        'czr_tinymce_child',

        'czr_image_module',
        'czr_image_main_settings_child',
        'czr_image_borders_corners_child',

        //'czr_featured_pages_module',
        'czr_heading_module',
        'czr_heading_child',
        'czr_heading_spacing_child',

        'czr_spacer_module',
        'czr_divider_module',

        'czr_icon_module',
        'czr_icon_settings_child',
        'czr_icon_spacing_border_child',

        'czr_map_module',

        'czr_quote_module',
        'czr_quote_quote_child',
        'czr_quote_cite_child',
        'czr_quote_design_child',

        'czr_button_module',
        'czr_btn_content_child',
        'czr_btn_design_child',

        // simple form father + children
        'czr_simple_form_module',
        'czr_simple_form_fields_child',
        'czr_simple_form_button_child',
        'czr_simple_form_design_child',
        'czr_simple_form_fonts_child',
        'czr_simple_form_submission_child',

        // GENERIC FRONT CHILD MODULES
        'czr_font_child'
    ];

    // Header and footer have been introduced in v1.4.0 but not enabled by default
    // The module menu is on hold until "header and footer" feature is released.
    if ( NIMBLE_HEADER_FOOTER_ENABLED ) {
        $modules = array_merge( $modules, [
            'czr_menu_module',
            'czr_menu_content_child',
            //'czr_menu_design_child',
        ]);
    }

    foreach( $modules as $module_name ) {
        // Was previously written "\Nimble\sek_get_module_params_for_{$module_name}";
        // But this syntax can lead to function_exists() return false even if the function exists
        // Probably due to a php version issue. Bug detected with php version 5.6.38
        // bug report detailed here https://github.com/presscustomizr/nimble-builder/issues/234
        $fn = "Nimble\sek_get_module_params_for_{$module_name}";
        if ( function_exists( $fn ) ) {
            $params = $fn();
            if ( is_array( $params ) ) {
                CZR_Fmk_Base()->czr_pre_register_dynamic_module( $params );
            } else {
                error_log( __FUNCTION__ . ' Module registration params should be an array');
            }
        } else {
            error_log( __FUNCTION__ . ' missing params callback fn for module ' . $module_name );
        }
    }
}//sek_register_modules()


// HELPERS
// Used when registering a select input in a module
// @return an array of options that will be used to populate the select input in js
function sek_get_select_options_for_input_id( $input_id ) {
    $options = array();
    switch( $input_id ) {
        // IMAGE MODULE
        case 'img-link-to' :
            $options = array(
                'no-link' => __('No link', 'text_domain_to_be_replaced' ),
                'url' => __('Site content or custom url', 'text_domain_to_be_replaced' ),
                'img-file' => __('Image file', 'text_domain_to_be_replaced' ),
                'img-page' =>__('Image page', 'text_domain_to_be_replaced' )
            );
        break;
        case 'img_hover_effect' :
            $options = array(
                'none' => __('No effect', 'text_domain_to_be_replaced' ),
                'opacity' => __('Opacity', 'text_domain_to_be_replaced' ),
                'zoom-out' => __('Zoom out', 'text_domain_to_be_replaced' ),
                'zoom-in' => __('Zoom in', 'text_domain_to_be_replaced' ),
                'move-up' =>__('Move up', 'text_domain_to_be_replaced' ),
                'move-down' =>__('Move down', 'text_domain_to_be_replaced' ),
                'blur' =>__('Blur', 'text_domain_to_be_replaced' ),
                'grayscale' =>__('Grayscale', 'text_domain_to_be_replaced' ),
                'reverse-grayscale' =>__('Reverse grayscale', 'text_domain_to_be_replaced' )
            );
        break;
        case 'img-size' :
            $options = sek_get_img_sizes();
        break;

        // ALL MODULES
        case 'link-to' :
            $options = array(
                'no-link' => __('No link', 'text_domain_to_be_replaced' ),
                'url' => __('Site content or custom url', 'text_domain_to_be_replaced' ),
            );
        break;

        // FEATURED PAGE MODULE
        case 'img-type' :
            $options = array(
                'none' => __( 'No image', 'text_domain_to_be_replaced' ),
                'featured' => __( 'Use the page featured image', 'text_domain_to_be_replaced' ),
                'custom' => __( 'Use a custom image', 'text_domain_to_be_replaced' ),
            );
        break;
        case 'content-type' :
            $options = array(
                'none' => __( 'No text', 'text_domain_to_be_replaced' ),
                'page-excerpt' => __( 'Use the page excerpt', 'text_domain_to_be_replaced' ),
                'custom' => __( 'Use a custom text', 'text_domain_to_be_replaced' ),
            );
        break;

        // HEADING MODULE
        case 'heading_tag':
            $options = array(
                /* Not totally sure these should be localized as they strictly refer to html tags */
                'h1' => __('H1', 'text_domain_to_be_replaced' ),
                'h2' => __('H2', 'text_domain_to_be_replaced' ),
                'h3' => __('H3', 'text_domain_to_be_replaced' ),
                'h4' => __('H4', 'text_domain_to_be_replaced' ),
                'h5' => __('H5', 'text_domain_to_be_replaced' ),
                'h6' => __('H6', 'text_domain_to_be_replaced' ),
            );
        break;

        // CSS MODIFIERS INPUT ID
        case 'font_weight_css' :
            $options = array(
                'normal'  => __( 'normal', 'text_domain_to_be_replaced' ),
                'bold'    => __( 'bold', 'text_domain_to_be_replaced' ),
                'bolder'  => __( 'bolder', 'text_domain_to_be_replaced' ),
                'lighter'   => __( 'lighter', 'text_domain_to_be_replaced' ),
                100     => 100,
                200     => 200,
                300     => 300,
                400     => 400,
                500     => 500,
                600     => 600,
                700     => 700,
                800     => 800,
                900     => 900
            );
        break;
        case 'font_style_css' :
            $options = array(
                'inherit'   => __( 'inherit', 'text_domain_to_be_replaced' ),
                'italic'  => __( 'italic', 'text_domain_to_be_replaced' ),
                'normal'  => __( 'normal', 'text_domain_to_be_replaced' ),
                'oblique' => __( 'oblique', 'text_domain_to_be_replaced' )
            );
        break;
        case 'text_decoration_css'  :
            $options = array(
                'none'      => __( 'none', 'text_domain_to_be_replaced' ),
                'inherit'   => __( 'inherit', 'text_domain_to_be_replaced' ),
                'line-through' => __( 'line-through', 'text_domain_to_be_replaced' ),
                'overline'    => __( 'overline', 'text_domain_to_be_replaced' ),
                'underline'   => __( 'underline', 'text_domain_to_be_replaced' )
            );
        break;
        case 'text_transform_css' :
            $options = array(
                'none'      => __( 'none', 'text_domain_to_be_replaced' ),
                'inherit'   => __( 'inherit', 'text_domain_to_be_replaced' ),
                'capitalize'  => __( 'capitalize', 'text_domain_to_be_replaced' ),
                'uppercase'   => __( 'uppercase', 'text_domain_to_be_replaced' ),
                'lowercase'   => __( 'lowercase', 'text_domain_to_be_replaced' )
            );
        break;

        // SPACING MODULE
        case 'css_unit' :
            $options = array(
                'px' => __('Pixels', 'text_domain_to_be_replaced' ),
                'em' => __('Em', 'text_domain_to_be_replaced'),
                'percent' => __('Percents', 'text_domain_to_be_replaced' )
            );
        break;

        //QUOTE MODULE
        case 'quote_design' :
            $options = array(
                'none' => __( 'Text only', 'text_domain_to_be_replaced' ),
                'border-before' => __( 'Side Border', 'text_domain_to_be_replaced' ),
                'quote-icon-before' => __( 'Quote Icon', 'text_domain_to_be_replaced' ),
            );
        break;

        // LEVELS UI : LAYOUT BACKGROUND BORDER HEIGHT WIDTH
        case 'boxed-wide' :
            $options = array(
                'boxed' => __('Boxed', 'text_domain_to_be_replaced'),
                'fullwidth' => __('Full Width', 'text_domain_to_be_replaced')
            );
        break;
        case 'height-type' :
            $options = array(
                'auto' => __('Adapt to content', 'text_domain_to_be_replaced'),
                'custom' => __('Custom', 'text_domain_to_be_replaced' )
            );
        break;
        case 'width-type' :
            $options = array(
                'default' => __('default', 'text_domain_to_be_replaced'),
                'custom' => __('Custom', 'text_domain_to_be_replaced' )
            );
        break;
        case 'bg-scale' :
            $options = array(
                'default' => __('default', 'text_domain_to_be_replaced'),
                'auto' => __('auto', 'text_domain_to_be_replaced'),
                'cover' => __('scale to fill', 'text_domain_to_be_replaced'),
                'contain' => __('fit', 'text_domain_to_be_replaced'),
            );
        break;
        case 'bg-position' :
            $options = array(
                'default' => __('default', 'text_domain_to_be_replaced'),
            );
        break;
        case 'border-type' :
            $options = array(
                'none' => __('none', 'text_domain_to_be_replaced'),
                'solid' => __('solid', 'text_domain_to_be_replaced'),
                'double' => __('double', 'text_domain_to_be_replaced'),
                'dotted' => __('dotted', 'text_domain_to_be_replaced'),
                'dashed' => __('dashed', 'text_domain_to_be_replaced')
            );
        break;

        default :
            sek_error_log( __FUNCTION__ . ' => no case set for input id : '. $input_id );
        break;
    }
    return $options;
}

?>