<?php
// The base fmk is loaded @after_setup_theme:10
add_action( 'after_setup_theme', '\Nimble\sek_schedule_module_registration', 50 );

// On front we register only the necessary modules
// When customizing, we register all of them
// On admin, we register none
function sek_schedule_module_registration() {
    // we load all modules when :
    // 1) customizing
    // 2) doing ajax <=> customizing
    // 3) isset( $_POST['nimble_simple_cf'] ) <= when a contact form is submitted.
    // Note about 3) => We should in fact load the necessary modules that we can determined with the posted skope_id. To be improved.
    // 3 fixes https://github.com/presscustomizr/nimble-builder/issues/433
    if ( isset( $_POST['nimble_simple_cf'] ) ) {
        sek_register_modules_when_customizing_or_ajaxing();
    } else if ( skp_is_customizing() || ( defined('DOING_AJAX') && DOING_AJAX ) ) {
        sek_register_modules_when_customizing_or_ajaxing();
        // prebuilt sections are registered from a JSON since https://github.com/presscustomizr/nimble-builder/issues/431
        sek_register_prebuilt_section_modules();
        // June 2020 : for https://github.com/presscustomizr/nimble-builder/issues/520
        sek_register_user_sections_module();
    } else {
        // Condition !is_admin() added in april 2020
        // fixes https://github.com/presscustomizr/nimble-builder/issues/658
        if ( !is_admin() ) {
            add_action( 'wp', '\Nimble\sek_register_active_modules_on_front', PHP_INT_MAX );
        }
    }
}

// @return void();
// @hook 'after_setup_theme'
function sek_register_modules_when_customizing_or_ajaxing() {
    $modules = array_merge(
        SEK_Front_Construct::$ui_picker_modules,
        // June 2020 filter added for https://github.com/presscustomizr/nimble-builder-pro/issues/6
        apply_filters( 'nb_level_module_collection', SEK_Front_Construct::$ui_level_modules ),
        SEK_Front_Construct::$ui_local_global_options_modules,
        SEK_Front_Construct::sek_get_front_module_collection()
    );

    // widgets module, menu module have been beta tested during 5 months and released in June 2019, in version 1.8.0
    if ( sek_are_beta_features_enabled() ) {
        $modules = array_merge( $modules, SEK_Front_Construct::$ui_front_beta_modules );
    }
    sek_do_register_module_collection( $modules );
}

// @return void();
// @hook 'wp'@PHP_INT_MAX
function sek_register_active_modules_on_front() {
    sek_register_modules_when_not_customizing_and_not_ajaxing();
}


// @param $skope_id added in april 2020 for https://github.com/presscustomizr/nimble-builder/issues/657
function sek_register_modules_when_not_customizing_and_not_ajaxing( $skope_id = '' ) {
    $contextually_actives_raw = sek_get_collection_of_contextually_active_modules( $skope_id );
    $contextually_actives_raw = array_keys( $contextually_actives_raw );

    $contextually_actives_candidates = array();
    $front_modules = array_merge( SEK_Front_Construct::sek_get_front_module_collection(), SEK_Front_Construct::$ui_front_beta_modules );

    // we need to get all children when the module is a father.
    // This will be flatenized afterwards
    foreach ( $contextually_actives_raw as $module_name ) {

        // Parent module with children
        if ( array_key_exists( $module_name, $front_modules ) ) {
            // get the list of childrent, includes the parent too.
            // @see ::sek_get_front_module_collection()
            $contextually_actives_candidates[] = $front_modules[ $module_name ];
        }
        // Simple module with no children
        if ( in_array( $module_name, $front_modules ) ) {
            $contextually_actives_candidates[] = $module_name;
        }
    }

    $modules = array_merge(
        $contextually_actives_candidates,
        apply_filters( 'nb_level_module_collection', SEK_Front_Construct::$ui_level_modules ),
        SEK_Front_Construct::$ui_local_global_options_modules
    );
    sek_do_register_module_collection( $modules );
}


// @return void();
function sek_do_register_module_collection( $modules ) {
    $module_candidates = array();
    // flatten the array
    // because can be formed this way after filter when including child
    // [0] => Array
    //     (
    //         [0] => czr_post_grid_module
    //         [1] => czr_post_grid_main_child
    //         [2] => czr_post_grid_thumb_child
    //         [3] => czr_post_grid_metas_child
    //         [4] => czr_post_grid_fonts_child
    //     )

    // [1] => sek_level_bg_module
    // [2] => sek_level_border_module
    foreach ($modules as $key => $value) {
      if ( is_array( $value ) ) {
          $module_candidates = array_merge( $module_candidates, $value );
      } else {
          $module_candidates[] = $value;
      }
    }

    // remove duplicated modules, typically 'czr_font_child'
    $module_candidates = array_unique( $module_candidates );
    foreach ( $module_candidates as $module_name ) {
        // Was previously written "\Nimble\sek_get_module_params_for_{$module_name}";
        // But this syntax can lead to function_exists() return false even if the function exists
        // Probably due to a php version issue. Bug detected with php version 5.6.38
        // bug report detailed here https://github.com/presscustomizr/nimble-builder/issues/234
        $fn = "Nimble\sek_get_module_params_for_{$module_name}";
        if ( function_exists( $fn ) ) {
            $params = apply_filters( "nimble_module_params_for_{$module_name}", $fn() );
            if ( is_array( $params ) ) {
                CZR_Fmk_Base()->czr_pre_register_dynamic_module( $params );
            } else {
                error_log( __FUNCTION__ . ' Module registration params should be an array');
            }
        } else {
            error_log( __FUNCTION__ . ' missing params callback fn for module ' . $module_name );
        }
    }
}






// SINGLE MODULE PARAMS STUCTURE
// 'dynamic_registration' => true,
// 'module_type' => 'sek_column_layouts_sec_picker_module',
// 'name' => __('Empty sections with columns layout', 'nimble-builder'),
// 'tmpl' => array(
//     'item-inputs' => array(
//         'sections' => array(
//             'input_type'  => 'section_picker',
//             'title'       => __('Drag-and-drop or double-click a section to insert it into a drop zone of the preview page.', 'nimble-builder'),
//             'width-100'   => true,
//             'title_width' => 'width-100',
//             'section_collection' => array(
//                 array(
//                     'content-id' => 'two_columns',
//                     'title' => __('two columns layout', 'nimble-builder' ),
//                     'thumb' => 'two_columns.jpg'
//                 ),
//                 array(
//                     'content-id' => 'three_columns',
//                     'title' => __('three columns layout', 'nimble-builder' ),
//                     'thumb' => 'three_columns.jpg'
//                 ),
//                 array(
//                     'content-id' => 'four_columns',
//                     'title' => __('four columns layout', 'nimble-builder' ),
//                     'thumb' => 'four_columns.jpg'
//                 ),
//             )
//         )
//     )
// )
// @return void();
// @hook 'after_setup_theme'
function sek_register_prebuilt_section_modules() {
    $registration_params = sek_get_sections_registration_params();
    $default_module_params = array(
        'dynamic_registration' => true,
        'module_type' => '',
        'name' => '',
        'tmpl' => array(
            'item-inputs' => array(
                'sections' => array(
                    'input_type'  => 'section_picker',
                    'title'       => __('Drag-and-drop or double-click a section to insert it into a drop zone of the preview page.', 'nimble-builder'),
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'section_collection' => array()
                )
            )
        )
    );

    foreach ( $registration_params as $module_type => $module_params ) {
        $module_params = wp_parse_args( $module_params, array(
            'name' => '',
            'section_collection' => array()
        ));

        // normalize the module params
        $normalized_params = $default_module_params;
        $normalized_params['module_type'] = $module_type;
        $normalized_params['name'] = $module_params['name'];
        $normalized_params['tmpl']['item-inputs']['sections']['section_collection'] = $module_params['section_collection'];
        CZR_Fmk_Base()->czr_pre_register_dynamic_module( $normalized_params );
    }

}

// @return void();
// @hook 'after_setup_theme'
// June 2020 for https://github.com/presscustomizr/nimble-builder/issues/520
function sek_register_user_sections_module() {
    $normalized_params = array(
        'dynamic_registration' => true,
        'module_type' => 'sek_my_sections_sec_picker_module',
        'name' => __('My sections', 'nimble-builder'),
        'tmpl' => array(
            'item-inputs' => array(
                'sections' => array(
                    'input_type'  => 'section_picker',
                    'title'       => __('Drag-and-drop or double-click a section to insert it into a drop zone of the preview page.', 'nimble-builder'),
                    'width-100'   => true,
                    'title_width' => 'width-100',
                    'section_collection' => array()
                )
            )
        )
    );

    CZR_Fmk_Base()->czr_pre_register_dynamic_module( $normalized_params );
}




// HELPERS
// Used when registering a select input in a module
// @return an array of options that will be used to populate the select input in js
function sek_get_select_options_for_input_id( $input_id ) {
    $options = array();
    switch( $input_id ) {
        case 'img_hover_effect' :
            $options = array(
                'none' => __('No effect', 'nimble-builder' ),
                'opacity' => __('Opacity', 'nimble-builder' ),
                'zoom-out' => __('Zoom out', 'nimble-builder' ),
                'zoom-in' => __('Zoom in', 'nimble-builder' ),
                'move-up' =>__('Move up', 'nimble-builder' ),
                'move-down' =>__('Move down', 'nimble-builder' ),
                'blur' =>__('Blur', 'nimble-builder' ),
                'grayscale' =>__('Grayscale', 'nimble-builder' ),
                'reverse-grayscale' =>__('Reverse grayscale', 'nimble-builder' )
            );
        break;
        case 'img-size' :
            $options = sek_get_img_sizes();
        break;

        // ALL MODULES
        case 'link-to' :
            $options = array(
                'no-link' => __('No link', 'nimble-builder' ),
                'url' => __('Site content or custom url', 'nimble-builder' ),
            );
        break;

        // FEATURED PAGE MODULE
        case 'img-type' :
            $options = array(
                'none' => __( 'No image', 'nimble-builder' ),
                'featured' => __( 'Use the page featured image', 'nimble-builder' ),
                'custom' => __( 'Use a custom image', 'nimble-builder' ),
            );
        break;
        case 'content-type' :
            $options = array(
                'none' => __( 'No text', 'nimble-builder' ),
                'page-excerpt' => __( 'Use the page excerpt', 'nimble-builder' ),
                'custom' => __( 'Use a custom text', 'nimble-builder' ),
            );
        break;

        // HEADING MODULE
        case 'heading_tag':
            $options = array(
                /* Not totally sure these should be localized as they strictly refer to html tags */
                'h1' => __('H1', 'nimble-builder' ),
                'h2' => __('H2', 'nimble-builder' ),
                'h3' => __('H3', 'nimble-builder' ),
                'h4' => __('H4', 'nimble-builder' ),
                'h5' => __('H5', 'nimble-builder' ),
                'h6' => __('H6', 'nimble-builder' ),
            );
        break;

        // CSS MODIFIERS INPUT ID
        case 'font_weight_css' :
            $options = array(
                'normal'  => __( 'normal', 'nimble-builder' ),
                'bold'    => __( 'bold', 'nimble-builder' ),
                'bolder'  => __( 'bolder', 'nimble-builder' ),
                'lighter'   => __( 'lighter', 'nimble-builder' ),
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
                'inherit'   => __( 'inherit', 'nimble-builder' ),
                'italic'  => __( 'italic', 'nimble-builder' ),
                'normal'  => __( 'normal', 'nimble-builder' ),
                'oblique' => __( 'oblique', 'nimble-builder' )
            );
        break;
        case 'text_decoration_css'  :
            $options = array(
                'none'      => __( 'none', 'nimble-builder' ),
                'inherit'   => __( 'inherit', 'nimble-builder' ),
                'line-through' => __( 'line-through', 'nimble-builder' ),
                'overline'    => __( 'overline', 'nimble-builder' ),
                'underline'   => __( 'underline', 'nimble-builder' )
            );
        break;
        case 'text_transform_css' :
            $options = array(
                'none'      => __( 'none', 'nimble-builder' ),
                'inherit'   => __( 'inherit', 'nimble-builder' ),
                'capitalize'  => __( 'capitalize', 'nimble-builder' ),
                'uppercase'   => __( 'uppercase', 'nimble-builder' ),
                'lowercase'   => __( 'lowercase', 'nimble-builder' )
            );
        break;

        // SPACING MODULE
        case 'css_unit' :
            $options = array(
                'px' => __('Pixels', 'nimble-builder' ),
                'em' => __('Em', 'nimble-builder'),
                'percent' => __('Percents', 'nimble-builder' )
            );
        break;

        //QUOTE MODULE
        case 'quote_design' :
            $options = array(
                'none' => __( 'Text only', 'nimble-builder' ),
                'border-before' => __( 'Side Border', 'nimble-builder' ),
                'quote-icon-before' => __( 'Quote Icon', 'nimble-builder' ),
            );
        break;

        // LEVELS UI : LAYOUT BACKGROUND BORDER HEIGHT WIDTH
        case 'boxed-wide' :
            $options = array(
                'boxed' => __('Boxed', 'nimble-builder'),
                'fullwidth' => __('Full Width', 'nimble-builder')
            );
        break;
        case 'height-type' :
            $options = array(
                'auto' => __('Adapt to content', 'nimble-builder'),
                'custom' => __('Custom', 'nimble-builder' )
            );
        break;
        case 'width-type' :
            $options = array(
                'default' => __('Default', 'nimble-builder'),
                'custom' => __('Custom', 'nimble-builder' )
            );
        break;
        case 'bg-scale' :
            $options = array(
                'default' => __('Default', 'nimble-builder'),
                'auto' => __('Automatic', 'nimble-builder'),
                'cover' => __('Scale to fill', 'nimble-builder'),
                'contain' => __('Fit', 'nimble-builder'),
            );
        break;
        case 'bg-position' :
            $options = array(
                'default' => __('default', 'nimble-builder'),
            );
        break;
        case 'border-type' :
            $options = array(
                'none' => __('none', 'nimble-builder'),
                'solid' => __('solid', 'nimble-builder'),
                'double' => __('double', 'nimble-builder'),
                'dotted' => __('dotted', 'nimble-builder'),
                'dashed' => __('dashed', 'nimble-builder')
            );
        break;

        default :
            sek_error_log( __FUNCTION__ . ' => no case set for input id : '. $input_id );
        break;
    }
    return $options;
}

?>