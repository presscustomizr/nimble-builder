<?php
// DEPRECATED WAS USED TO DISPLAY UI BUTTON IN THE PANEL
// if ( ! defined( 'SEK_BUTTON_SECTION_TMPL_SUFFIX' ) ) { define( 'SEK_BUTTON_SECTION_TMPL_SUFFIX', 'sek-add-new-sektion-button' ); }
// if ( ! defined( 'SEK_BUTTON_COLUMN_TMPL_SUFFIX' ) ) { define( 'SEK_BUTTON_COLUMN_TMPL_SUFFIX', 'sek-add-new-column-button' ); }
// if ( ! defined( 'SEK_BUTTON_MODULE_TMPL_SUFFIX' ) ) { define( 'SEK_BUTTON_MODULE_TMPL_SUFFIX', 'sek-add-new-module-button' ); }

// TINY MCE EDITOR
require_once(  dirname( __FILE__ ) . '/customizer/seks_tiny_mce_editor_actions.php' );

// CONTENT PICKER AJAX
add_action( 'customize_register', '\Nimble\sek_setup_content_picker' );
function sek_setup_content_picker() {
    require_once(  dirname( __FILE__ ) . '/customizer/seks_content_picker-ajax_actions.php' );
    new SEK_customize_ajax_content_picker_actions();
}

// ENQUEUE CUSTOMIZER JAVASCRIPT + PRINT LOCALIZED DATA
add_action ( 'customize_controls_enqueue_scripts', '\Nimble\sek_enqueue_controls_js_css', 20 );
function sek_enqueue_controls_js_css() {
    wp_enqueue_style(
        'sek-control',
        sprintf(
            '%1$s/assets/czr/sek/css/%2$s' ,
            NIMBLE_BASE_URL,
            defined('CZR_DEV') && true === CZR_DEV ? 'sek-control.css' : 'sek-control.min.css'
        ),
        array(),
        NIMBLE_ASSETS_VERSION,
        'all'
    );

    $czrnamespace = $GLOBALS['czr_base_fmk_namespace'];
    //czr_fn\czr_register_dynamic_module
    $CZR_Fmk_Base_fn = $czrnamespace . 'CZR_Fmk_Base';
    if ( ! function_exists( $CZR_Fmk_Base_fn) ) {
        error_log( __FUNCTION__ . ' => Namespace problem => ' . $CZR_Fmk_Base_fn );
        return;
    }
    // registered modules
    $registered_modules = $CZR_Fmk_Base_fn() -> registered_modules;

    wp_enqueue_script(
        'czr-sektions',
        //dev / debug mode mode?
        sprintf(
            '%1$s/assets/czr/sek/js/%2$s' ,
            NIMBLE_BASE_URL,
            defined('CZR_DEV') && true === CZR_DEV ? 'ccat-sek-control.js' : 'ccat-sek-control.min.js'
        ),
        array( 'czr-skope-base' , 'jquery', 'underscore' ),
        NIMBLE_ASSETS_VERSION,
        $in_footer = true
    );

    wp_enqueue_script(
        'czr-color-picker',
        //dev / debug mode mode?
        sprintf(
            '%1$s/assets/czr/sek/js/libs/%2$s' ,
            NIMBLE_BASE_URL,
            defined('CZR_DEV') && true === CZR_DEV ? 'czr-color-picker.js' : 'czr-color-picker.min.js'
        ),
        array( 'jquery' ),
        NIMBLE_ASSETS_VERSION,
        $in_footer = true
    );

    wp_localize_script(
        'czr-sektions',
        'sektionsLocalizedData',
        array(
            'isDevMode' => ( defined('WP_DEBUG') && true === WP_DEBUG ) || ( defined('CZR_DEV') && true === CZR_DEV ),
            'baseUrl' => NIMBLE_BASE_URL,
            'sektionsPanelId' => '__sektions__',
            'addNewSektionId' => 'sek_add_new_sektion',
            'addNewColumnId' => 'sek_add_new_column',
            'addNewModuleId' => 'sek_add_new_module',

            'optPrefixForSektionSetting' => SEK_OPT_PREFIX_FOR_SEKTION_COLLECTION,//'sek___'
            'optPrefixForSektionsNotSaved' => SEK_OPT_PREFIX_FOR_SEKTIONS_NOT_SAVED,//"__sek__"

            'defaultSektionSettingValue' => sek_get_default_sektions_value(),

            'presetSections' => sek_get_preset_sektions(),

            'registeredModules' => $registered_modules,

            // Dnd
            'preDropElementClass' => 'sortable-placeholder',
            'dropSelectors' => implode(',', [
                // 'module' type
                //'.sek-module-drop-zone-for-first-module',//the drop zone when there's no module or nested sektion in the column
                //'[data-sek-level="location"]',
                //'.sek-not-empty-col',// the drop zone when there is at least one module
                //'.sek-column > .sek-column-inner sek-section',// the drop zone when there is at least one nested section
                //'.sek-content-module-drop-zone',//between sections
                '.sek-drop-zone', //This is the selector for all eligible drop zones printed statically or dynamically on dragstart
                'body',// body will not be eligible for drop, but setting the body as drop zone allows us to fire dragenter / dragover actions, like toggling the "approaching" or "close" css class to real drop zone

                // 'preset_section' type
                '.sek-content-preset_section-drop-zone'//between sections
            ]),


            'selectOptions' => array(
                  // IMAGE MODULE
                  'link-to' => array(
                      'no-link' => __('No link', 'text_domain_to_be_replaced' ),
                      'url' => __('Site content or custom url', 'text_domain_to_be_replaced' ),
                      'img-file' => __('Image file', 'text_domain_to_be_replaced' ),
                      'img-page' =>__('Image page', 'text_domain_to_be_replaced' )
                  ),
                  'img-size' => sek_get_img_sizes(),


                  // FEATURED PAGE MODULE
                  'img-type' => array(
                      'none' => __('No image', 'text_domain_to_be_replaced' ),
                      'featured' => __('Use the page featured image', 'text_domain_to_be_replaced' ),
                      'custom' => __('Use a custom image', 'text_domain_to_be_replaced' ),
                  ),
                  'content-type' => array(
                      'none' => __('No text', 'text_domain_to_be_replaced' ),
                      'page-excerpt' => __('Use the page excerpt', 'text_domain_to_be_replaced' ),
                      'custom' => __('Use a custom text', 'text_domain_to_be_replaced' ),
                  ),

                  // GENERIC CSS MODIFIERS INPUT TYPES
                  'font_weight_css' => array(
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
                  ),

                  'font_style_css' => array(
                      'inherit'   => __( 'inherit', 'text_domain_to_be_replaced' ),
                      'italic'  => __( 'italic', 'text_domain_to_be_replaced' ),
                      'normal'  => __( 'normal', 'text_domain_to_be_replaced' ),
                      'oblique' => __( 'oblique', 'text_domain_to_be_replaced' )
                  ),

                  'text_decoration_css' =>  array(
                      'none'      => __( 'none', 'text_domain_to_be_replaced' ),
                      'inherit'   => __( 'inherit', 'text_domain_to_be_replaced' ),
                      'line-through' => __( 'line-through', 'text_domain_to_be_replaced' ),
                      'overline'    => __( 'overline', 'text_domain_to_be_replaced' ),
                      'underline'   => __( 'underline', 'text_domain_to_be_replaced' )
                  ),

                  'text_transform_css' => array(
                      'none'      => __( 'none', 'text_domain_to_be_replaced' ),
                      'inherit'   => __( 'inherit', 'text_domain_to_be_replaced' ),
                      'capitalize'  => __( 'capitalize', 'text_domain_to_be_replaced' ),
                      'uppercase'   => __( 'uppercase', 'text_domain_to_be_replaced' ),
                      'lowercase'   => __( 'lowercase', 'text_domain_to_be_replaced' )
                  ),






                  // SPACING MODULE
                  'spacingUnits' => array(
                      'px' => __('Pixels', 'text_domain_to_be_replaced' ),
                      'em' => __('Em', 'text_domain_to_be_replaced'),
                      'percent' => __('Percents', 'text_domain_to_be_replaced' )
                  ),

                  // LAYOUT BACKGROUND BORDER
                  'boxed-wide' => array(
                      'boxed' => __('Boxed', 'text_domain_to_be_replaced'),
                      'fullwidth' => __('Full Width', 'text_domain_to_be_replaced')
                  ),
                  'height-type' => array(
                      'default' => __('default', 'text_domain_to_be_replaced'),
                      'fit-to-screen' => __('Fit to screen', 'text_domain_to_be_replaced'),
                      'custom' => __('Custom', 'text_domain_to_be_replaced' )
                  ),
                  'bg-scale' => array(
                      'default' => __('default', 'text_domain_to_be_replaced'),
                      'auto' => __('auto', 'text_domain_to_be_replaced'),
                      'cover' => __('scale to fill', 'text_domain_to_be_replaced'),
                      'contain' => __('fit', 'text_domain_to_be_replaced'),
                  ),
                  'bg-position' => array(
                      'default' => __('default', 'text_domain_to_be_replaced'),
                  ),
                  'border-type' => array(
                      'none' => __('none', 'text_domain_to_be_replaced'),
                      'solid' => __('solid', 'text_domain_to_be_replaced'),
                      'double' => __('double', 'text_domain_to_be_replaced'),
                      'dotted' => __('dotted', 'text_domain_to_be_replaced'),
                      'dashed' => __('dashed', 'text_domain_to_be_replaced')
                  )
            ),


            'i18n' => array(
                'Sections' => __( 'Sections', 'text_domain_to_be_replaced'),

                'Nimble Builder' => __('Nimble Builder', 'text_domain_to_be_replaced'),

                'Customizing' => __('Customizing', 'text_domain_to_be_replaced'),

                "You've reached the maximum number of allowed nested sections." => __("You've reached the maximum number of allowed nested sections.", 'text_domain_to_be_replaced'),
                "You've reached the maximum number of columns allowed in this section." => __( "You've reached the maximum number of columns allowed in this section.", 'text_domain_to_be_replaced'),
                "A section must have at least one column." => __( "A section must have at least one column.", 'text_domain_to_be_replaced'),

                'If this problem locks the Nimble builder, you might try to reset the sections for this page.' => __('If this problem locks the Nimble builder, you might try to reset the sections for this page.', 'text_domain_to_be_replaced'),
                'Reset' => __('Reset', 'text_domain_to_be_replaced'),
                'Reset complete' => __('Reset complete', 'text_domain_to_be_replaced'),

                // Generated UI
                'Module Picker' => __('Module Picker', 'text_domain_to_be_replaced'),
                'Drag and drop a module in one of the possible locations of the previewed page.' => __( 'Drag and drop a module in one of the possible locations of the previewed page.', 'text_domain_to_be_replaced' ),

                'Section Picker' => __('Section Picker', 'text_domain_to_be_replaced'),

                'Module' => __('Module', 'text_domain_to_be_replaced'),
                'Content for' => __('Content for', 'text_domain_to_be_replaced'),
                'Customize the options for module :' => __('Customize the options for module :', 'text_domain_to_be_replaced'),

                'Layout settings for the' => __('Layout settings for the', 'text_domain_to_be_replaced'),
                'Background and border settings for the' => __('Background and border settings for the', 'text_domain_to_be_replaced'),
                'Padding and margin settings for the' => __('Padding and margin settings for the', 'text_domain_to_be_replaced'),
                'Height settings for the' => __('Height settings for the', 'text_domain_to_be_replaced'),

                'Settings for the' => __('Settings for the', 'text_domain_to_be_replaced'),//section / column / module

                // Levels
                'location' => __('location', 'text_domain_to_be_replaced'),
                'section' => __('section', 'text_domain_to_be_replaced'),
                'column' => __('column', 'text_domain_to_be_replaced'),
                'module' => __('module', 'text_domain_to_be_replaced'),

                'This browser does not support drag and drop. You might need to update your browser or use another one.' => __('This browser does not support drag and drop. You might need to update your browser or use another one.', 'text_domain_to_be_replaced'),

                // DRAG n DROP
                'Insert here' => __('Insert here', 'text_domain_to_be_replaced'),
                'Insert in a new section' => __('Insert in a new section', 'text_domain_to_be_replaced'),
                'Insert a new section here' => __('Insert a new section here', 'text_domain_to_be_replaced'),

                // MODULES
                'Select a font family' => __('Select a font family', 'text_domain_to_be_replaced'),
                'Web Safe Fonts' => __('Web Safe Fonts', 'text_domain_to_be_replaced'),
                'Google Fonts' => __('Google Fonts', 'text_domain_to_be_replaced'),

                'Set a custom url' => __('Set a custom url', 'text_domain_to_be_replaced'),

                'Something went wrong, please refresh this page.' => __('Something went wrong, please refresh this page.', 'text_domain_to_be_replaced'),

                // 'Module' => __('Module', 'text_domain_to_be_replaced'),
                // 'Module' => __('Module', 'text_domain_to_be_replaced'),
                // 'Module' => __('Module', 'text_domain_to_be_replaced'),
                // 'Module' => __('Module', 'text_domain_to_be_replaced'),
                // 'Module' => __('Module', 'text_domain_to_be_replaced'),


            )
        )
    );
}


// ADD SEKTION VALUES TO EXPORTED DATA IN THE CUSTOMIZER PREVIEW
add_filter( 'skp_json_export_ready_skopes', '\Nimble\add_sektion_values_to_skope_export' );
function add_sektion_values_to_skope_export( $skopes ) {
    if ( ! is_array( $skopes ) ) {
        error_log( 'skp_json_export_ready_skopes filter => the filtered skopes must be an array.' );
    }
    $new_skopes = array();
    foreach ( $skopes as $skp_data ) {
        if ( 'global' == $skp_data['skope'] || 'group' == $skp_data['skope'] ) {
            $new_skopes[] = $skp_data;
            continue;
        }
        if ( ! is_array( $skp_data ) ) {
            error_log( 'skp_json_export_ready_skopes filter => the skope data must be an array.' );
            continue;
        }
        $skope_id = skp_get_skope_id( $skp_data['skope'] );
        $skp_data[ 'sektions' ] = array(
            'db_values' => sek_get_skoped_seks( $skope_id ),
            'setting_id' => sek_get_seks_setting_id( $skope_id )//sek___loop_start[skp__post_page_home]
        );
        // foreach( [
        //     'loop_start',
        //     'loop_end',
        //     'before_content',
        //     'after_content',
        //     'global'
        //     ] as $location ) {
        //     $skp_data[ 'sektions' ][ $location ] = array(
        //         'db_values' => sek_get_skoped_seks( $skope_id, $location ),
        //         'setting_id' => sek_get_seks_setting_id( $skope_id, $location )//sek___loop_start[skp__post_page_home]
        //     );
        // }
        $new_skopes[] = $skp_data;
    }

    // error_log( '<////////////////////$new_skopes>' );
    // error_log( print_r($new_skopes, true ) );
    // error_log( '</////////////////////$new_skopes>' );

    return $new_skopes;
}



function sek_get_preset_sektions() {
    return array(
        'alternate_text_right' => '{"id":"","level":"section","collection":[{"id":"","level":"column","collection":[{"id":"","level":"module","module_type":"czr_image_module"}],"width":""},{"id":"","level":"column","collection":[{"id":"","level":"module","module_type":"czr_tiny_mce_editor_module","value":{"content":"Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed non risus. Suspendisse lectus tortor, dignissim sit amet, adipiscing nec, ultricies sed, dolor."}}]}]}',

        'alternate_text_left' => '{"id":"","level":"section","collection":[{"id":"","level":"column","collection":[{"id":"","level":"module","module_type":"czr_tiny_mce_editor_module","value":{"content":"Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed non risus. Suspendisse lectus tortor, dignissim sit amet, adipiscing nec, ultricies sed, dolor."}}],"width":""},{"id":"","level":"column","collection":[{"id":"","level":"module","module_type":"czr_image_module"}],"width":""}]}',
    );
}


// @see https://codex.wordpress.org/Function_Reference/get_intermediate_image_sizes
function sek_get_img_sizes() {
    global $_wp_additional_image_sizes;

    $sizes = array();
    $to_return = array(
        'original' => __('Original image dimensions', 'text_domain_to_be_replaced')
    );

    foreach ( get_intermediate_image_sizes() as $_size ) {

        $first_to_upper_size = ucfirst(strtolower($_size));
        $first_to_upper_size = preg_replace_callback('/[.!?].*?\w/', create_function('$matches', 'return strtoupper($matches[0]);'), $first_to_upper_size);

        if ( in_array( $_size, array('thumbnail', 'medium', 'medium_large', 'large') ) ) {
            $sizes[ $_size ]['width']  = get_option( "{$_size}_size_w" );
            $sizes[ $_size ]['height'] = get_option( "{$_size}_size_h" );
            $sizes[ $_size ]['title'] =  $first_to_upper_size;
            //$sizes[ $_size ]['crop']   = (bool) get_option( "{$_size}_crop" );
        } elseif ( isset( $_wp_additional_image_sizes[ $_size ] ) ) {
            $sizes[ $_size ] = array(
                'width'  => $_wp_additional_image_sizes[ $_size ]['width'],
                'height' => $_wp_additional_image_sizes[ $_size ]['height'],
                'title' =>  $first_to_upper_size
                //'crop'   => $_wp_additional_image_sizes[ $_size ]['crop'],
            );
        }
    }
    foreach ( $sizes as $_size => $data ) {
        $to_return[ $_size ] = $data['title'] . ' - ' . $data['width'] . ' x ' . $data['height'];
    }

    return $to_return;
}
?>