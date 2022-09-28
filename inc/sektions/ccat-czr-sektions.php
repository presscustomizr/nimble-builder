<?php
namespace Nimble;
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

// Google Fonts => save the list of most used fonts in the site
// so that they are appended in first position when building gfont collection for input in customizer control js
// implemented for https://github.com/presscustomizr/nimble-builder/issues/418
add_action('customize_save_after', '\Nimble\sek_update_most_used_gfonts');
function sek_update_most_used_gfonts( $manager ) {
    if ( !sek_current_user_can_access_nb_ui() )
      return;
    $all_gfonts = sek_get_all_gfonts();
    if ( is_array($all_gfonts) && !empty($all_gfonts) ) {
        update_option( NIMBLE_OPT_NAME_FOR_MOST_USED_FONTS, $all_gfonts, 'no' );
    }
}


add_action('customize_save_after', '\Nimble\sek_maybe_write_global_stylesheet');
function sek_maybe_write_global_stylesheet( $manager ) {
    if ( !sek_current_user_can_access_nb_ui() )
      return;
    // Try to write the CSS
    new Sek_Dyn_CSS_Handler( array(
        'id'             => NIMBLE_GLOBAL_SKOPE_ID,
        'skope_id'       => NIMBLE_GLOBAL_SKOPE_ID,
        'mode'           => Sek_Dyn_CSS_Handler::MODE_FILE,
        'customizer_save' => true,//<= indicating that we are in a customizer_save scenario will tell the dyn css class to only write the css file + save the google fonts, not schedule the enqueuing
        'force_rewrite'  => true, //<- write even if the file exists
        'is_global_stylesheet' => true
    ) );
}

// @return array of all gfonts used in the site
// the duplicates are not removed, because we order the fonts by number of occurences in javascript.
// @see js control::font_picker in api.czrInputMap
// implemented for https://github.com/presscustomizr/nimble-builder/issues/418
function sek_get_all_gfonts() {
    // First check if we have font defined globally. Implemented since https://github.com/presscustomizr/nimble-builder/issues/292
    $global_options = get_option( NIMBLE_OPT_NAME_FOR_GLOBAL_OPTIONS );
    $ffamilies = array();
    if ( is_array( $global_options ) && !empty( $global_options['fonts'] ) && is_array( $global_options['fonts'] ) ) {
        $ffamilies = array_merge( $ffamilies, $global_options['fonts'] );
    }

    // Do a query on all NIMBLE_CPT and walk all skope ids, included the global skope ( for global sections )
    $sek_post_query_vars = array(
        'post_type'              => NIMBLE_CPT,
        'post_status'            => get_post_stati(),
        //'name'                   => sanitize_title(),
        'posts_per_page'         => -1,
        'no_found_rows'          => true,
        'cache_results'          => true,
        'update_post_meta_cache' => false,
        'update_post_term_cache' => false,
        'lazy_load_term_meta'    => false,
    );
    $query = new \WP_Query( $sek_post_query_vars );
    if ( !is_array( $query->posts ) || empty( $query->posts ) )
      return;

    foreach ($query->posts as $post_object ) {
        if ( $post_object ) {
            $seks_data = maybe_unserialize( $post_object->post_content );
        }
        $seks_data = is_array( $seks_data ) ? $seks_data : array();
        if ( empty( $seks_data ) )
          continue;
        if ( is_array( $seks_data ) && !empty( $seks_data['fonts'] ) && is_array( $seks_data['fonts'] ) ) {
            $ffamilies = array_merge( $ffamilies, $seks_data['fonts'] );
        }
    }//foreach

    // duplicates are kept for ordering
    //$ffamilies = array_unique( $ffamilies );
    return $ffamilies;
}



// ENQUEUE CUSTOMIZER JAVASCRIPT + PRINT LOCALIZED DATA
add_action ( 'customize_controls_enqueue_scripts', '\Nimble\sek_enqueue_controls_js_css', 20 );
function sek_enqueue_controls_js_css() {
    if ( !sek_current_user_can_access_nb_ui() )
      return;
    wp_enqueue_style(
        'sek-control',
        sprintf(
            '%1$s/assets/czr/sek/css/%2$s' ,
            NIMBLE_BASE_URL,
            sek_is_dev_mode() ? 'sek-control.css' : 'sek-control.min.css'
        ),
        array(),
        NIMBLE_ASSETS_VERSION,
        'all'
    );

    // June 2020 : commented for https://github.com/presscustomizr/nimble-builder/issues/708
    // now injected on api "ready"
    // wp_enqueue_script(
    //     'czr-sektions',
    //     //dev / debug mode mode?
    //     sprintf(
    //         '%1$s/assets/czr/sek/js/%2$s' ,
    //         NIMBLE_BASE_URL,
    //         sek_is_dev_mode() ? 'ccat-sek-control.js' : 'ccat-sek-control.min.js'
    //     ),
    //     array( 'czr-skope-base' , 'jquery', 'underscore' ),
    //     NIMBLE_ASSETS_VERSION,
    //     $in_footer = true
    // );


    wp_localize_script(
        'customize-controls',//czr-sektions',
        'sektionsLocalizedData',
        apply_filters( 'nimble-sek-localized-customizer-control-params',
            array(
                'nimbleVersion' => NIMBLE_VERSION,
                'isDevMode' => sek_is_dev_mode(),
                'isDebugMode' => sek_is_debug_mode(),
                'isPro' => sek_is_pro(),
                'isUpsellEnabled' => sek_is_upsell_enabled(),
                'baseUrl' => NIMBLE_BASE_URL,
                //ajaxURL is not mandatory because is normally available in the customizer window.ajaxurl
                'ajaxUrl' => admin_url( 'admin-ajax.php' ),
                'customizerURL'   => admin_url( 'customize.php' ),
                'sektionsPanelId' => '__sektions__',
                'addNewSektionId' => 'sek_add_new_sektion',
                'addNewColumnId' => 'sek_add_new_column',
                'addNewModuleId' => 'sek_add_new_module',

                'optPrefixForSektionSetting' => NIMBLE_OPT_PREFIX_FOR_SEKTION_COLLECTION,//'nimble___'
                'optNameForGlobalOptions' => NIMBLE_OPT_NAME_FOR_GLOBAL_OPTIONS,//'nimble___'
                'prefixForSettingsNotSaved' => NIMBLE_PREFIX_FOR_SETTING_NOT_SAVED,//"__nimble__"

                'globalOptionDBValues' => get_option( NIMBLE_OPT_NAME_FOR_GLOBAL_OPTIONS ),// 'nimble_global_opts'

                'defaultLocationModel' => Nimble_Manager()->default_location_model,
                'defaultLocalSektionSettingValue' => sek_get_default_location_model(),
                'defaultGlobalSektionSettingValue' => sek_get_default_location_model( NIMBLE_GLOBAL_SKOPE_ID ),

                'settingIdForGlobalSections' => sek_get_seks_setting_id( NIMBLE_GLOBAL_SKOPE_ID ),
                'globalSkopeId' => NIMBLE_GLOBAL_SKOPE_ID,

                'registeredModules' => CZR_Fmk_Base()->registered_modules,

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

                'areBetaFeaturesEnabled' => sek_are_beta_features_enabled(),

                'registeredWidgetZones' => array_merge( array( '_none_' => __('Select a widget area', 'text_doma') ), sek_get_registered_widget_areas() ),

                'globalOptionsMap' => SEK_Front_Construct::$global_options_map,
                'localOptionsMap' => SEK_Front_Construct::$local_options_map,

                'registeredLocations' => sek_get_locations(),
                // added for the module tree #359
                'moduleCollection' => sek_get_module_collection(),
                'moduleIconPath' => NIMBLE_MODULE_ICON_PATH,
                'czrAssetsPath' => NIMBLE_BASE_URL . '/assets/czr/',

                'hasActiveCachePlugin' => sek_has_active_cache_plugin(),

                // Tiny MCE
                'idOfDetachedTinyMceTextArea' => NIMBLE_DETACHED_TINYMCE_TEXTAREA_ID,
                'tinyMceNimbleEditorStylesheetUrl' => sprintf( '%1$s/assets/czr/sek/css/sek-tinymce-content.css', NIMBLE_BASE_URL ),
                // defaultToolbarBtns is used for the detached tinymce editor
                'defaultToolbarBtns' => "formatselect,fontsizeselect,forecolor,bold,italic,underline,strikethrough,bullist,numlist,blockquote,alignleft,aligncenter,alignright,link,unlink,spellchecker,hr,pastetext,removeformat,charmap,outdent,indent,undo,redo",
                // basic btns are used for the heading, the quote content and quote cite
                'basic_btns' => array('forecolor','bold','italic','underline','strikethrough','link','unlink'),
                'basic_btns_nolink' => array('forecolor','bold','italic','underline','strikethrough'),
                // with list introduced for the accordion module https://github.com/presscustomizr/nimble-builder/issues/482
                'basic_btns_with_lists' => array('forecolor','bold','italic','underline','strikethrough','link','unlink', 'bullist', 'numlist'),

                // May 21st, v1.7.5 => back to the local data
                // after problem was reported when fetching data remotely : https://github.com/presscustomizr/nimble-builder/issues/445
                //'presetSectionsModules' => array_keys( sek_get_sections_registration_params_api_data() )
                'presetSectionsModules' => array_keys( sek_get_sections_registration_params() ),

                // array(
                //     '[gfont]Trochut:700',
                //     '[gfont]Sansita:900',
                //     '[gfont]Josefin+Sans:100',
                //     '[gfont]Poppins:regular',
                //     '[cfont]Comic Sans MS,Comic Sans MS,cursive',
                //     '[gfont]Covered+By+Your+Grace:regular'
                // ),
                'alreadyUsedFonts' => get_option( NIMBLE_OPT_NAME_FOR_MOST_USED_FONTS ),
                'isTemplateGalleryEnabled' => true, //<= APRIL 2020 : for https://github.com/presscustomizr/nimble-builder/issues/651
                'isTemplateSaveEnabled' => true, //<= APRIL 2020 : for https://github.com/presscustomizr/nimble-builder/issues/655
                
                'useAPItemplates' => true,// March 2021 deployed in v3.0.0
                // Dec 2020
                // When developing locally, allow a local template api request
                'templateAPIUrl' => NIMBLE_DATA_API_URL_V2
            )
        )
    );//wp_localize_script()

    nimble_enqueue_code_editor();
}//sek_enqueue_controls_js_css()





/**
 * Enqueue all code editor assets
 */
function nimble_enqueue_code_editor() {
    wp_enqueue_script( 'code-editor' );
    wp_enqueue_style( 'code-editor' );

    wp_enqueue_script( 'csslint' );
    wp_enqueue_script( 'htmlhint' );
    wp_enqueue_script( 'csslint' );
    wp_enqueue_script( 'jshint' );
    wp_enqueue_script( 'htmlhint-kses' );
    wp_enqueue_script( 'jshint' );
    wp_enqueue_script( 'jsonlint' );
}



/**
 * Enqueue assets needed by the code editor for the given settings.
 *
 * @param array $args {
 *     Args.
 *
 *     @type string   $type       The MIME type of the file to be edited.
 *     @type array    $codemirror Additional CodeMirror setting overrides.
 *     @type array    $csslint    CSSLint rule overrides.
 *     @type array    $jshint     JSHint rule overrides.
 *     @type array    $htmlhint   JSHint rule overrides.
 *     @returns array Settings for the enqueued code editor.
 * }
 */
function nimble_get_code_editor_settings( $args ) {
    $settings = array(
        'codemirror' => array(
            'indentUnit' => 2,
            'tabSize' => 2,
            'indentWithTabs' => true,
            'inputStyle' => 'contenteditable',
            'lineNumbers' => true,
            'lineWrapping' => true,
            'styleActiveLine' => true,
            'continueComments' => true,
            'extraKeys' => array(
                'Ctrl-Space' => 'autocomplete',
                'Ctrl-/' => 'toggleComment',
                'Cmd-/' => 'toggleComment',
                'Alt-F' => 'findPersistent',
                'Ctrl-F'     => 'findPersistent',
                'Cmd-F'      => 'findPersistent',
            ),
            'direction' => 'ltr', // Code is shown in LTR even in RTL languages.
            'gutters' => array(),
        ),
        'csslint' => array(
            'errors' => true, // Parsing errors.
            'box-model' => true,
            'display-property-grouping' => true,
            'duplicate-properties' => true,
            'known-properties' => true,
            'outline-none' => true,
        ),
        'jshint' => array(
            // The following are copied from <https://github.com/WordPress/wordpress-develop/blob/4.8.1/.jshintrc>.
            'boss' => true,
            'curly' => true,
            'eqeqeq' => true,
            'eqnull' => true,
            'es3' => true,
            'expr' => true,
            'immed' => true,
            'noarg' => true,
            'nonbsp' => true,
            'onevar' => true,
            'quotmark' => 'single',
            'trailing' => true,
            'undef' => true,
            'unused' => true,

            'browser' => true,

            'globals' => array(
                '_' => false,
                'Backbone' => false,
                'jQuery' => false,
                'JSON' => false,
                'wp' => false,
            ),
        ),
        'htmlhint' => array(
            'tagname-lowercase' => true,
            'attr-lowercase' => true,
            'attr-value-double-quotes' => false,
            'doctype-first' => false,
            'tag-pair' => true,
            'spec-char-escape' => true,
            'id-unique' => true,
            'src-not-empty' => true,
            'attr-no-duplication' => true,
            'alt-require' => true,
            'space-tab-mixed-disabled' => 'tab',
            'attr-unsafe-chars' => true,
        ),
    );

    $type = '';

    if ( isset( $args['type'] ) ) {
        $type = $args['type'];

        // Remap MIME types to ones that CodeMirror modes will recognize.
        if ( 'application/x-patch' === $type || 'text/x-patch' === $type ) {
            $type = 'text/x-diff';
        }
    } //we do not treat the "file" case


    if ( 'text/css' === $type ) {
        $settings['codemirror'] = array_merge( $settings['codemirror'], array(
            'mode' => 'css',
            'lint' => true,
            'autoCloseBrackets' => true,
            'matchBrackets' => true,
        ) );
    } elseif ( 'text/x-scss' === $type || 'text/x-less' === $type || 'text/x-sass' === $type ) {
        $settings['codemirror'] = array_merge( $settings['codemirror'], array(
            'mode' => $type,
            'lint' => false,
            'autoCloseBrackets' => true,
            'matchBrackets' => true,
        ) );
    } elseif ( 'text/x-diff' === $type ) {
        $settings['codemirror'] = array_merge( $settings['codemirror'], array(
            'mode' => 'diff',
        ) );
    } elseif ( 'text/html' === $type ) {
        $settings['codemirror'] = array_merge( $settings['codemirror'], array(
            'mode' => 'htmlmixed',
            'lint' => true,
            'autoCloseBrackets' => true,
            'autoCloseTags' => true,
            'matchTags' => array(
                'bothTags' => true,
            ),
        ) );

        if ( !current_user_can( 'unfiltered_html' ) ) {
            $settings['htmlhint']['kses'] = wp_kses_allowed_html( 'post' );
        }
    } elseif ( 'text/x-gfm' === $type ) {
        $settings['codemirror'] = array_merge( $settings['codemirror'], array(
            'mode' => 'gfm',
            'highlightFormatting' => true,
        ) );
    } elseif ( 'application/javascript' === $type || 'text/javascript' === $type ) {
        $settings['codemirror'] = array_merge( $settings['codemirror'], array(
            'mode' => 'javascript',
            'lint' => true,
            'autoCloseBrackets' => true,
            'matchBrackets' => true,
        ) );
    } elseif ( false !== strpos( $type, 'json' ) ) {
        $settings['codemirror'] = array_merge( $settings['codemirror'], array(
            'mode' => array(
                'name' => 'javascript',
            ),
            'lint' => true,
            'autoCloseBrackets' => true,
            'matchBrackets' => true,
        ) );
        if ( 'application/ld+json' === $type ) {
            $settings['codemirror']['mode']['jsonld'] = true;
        } else {
            $settings['codemirror']['mode']['json'] = true;
        }
    } elseif ( false !== strpos( $type, 'jsx' ) ) {
        $settings['codemirror'] = array_merge( $settings['codemirror'], array(
            'mode' => 'jsx',
            'autoCloseBrackets' => true,
            'matchBrackets' => true,
        ) );
    } elseif ( 'text/x-markdown' === $type ) {
        $settings['codemirror'] = array_merge( $settings['codemirror'], array(
            'mode' => 'markdown',
            'highlightFormatting' => true,
        ) );
    } elseif ( 'text/nginx' === $type ) {
        $settings['codemirror'] = array_merge( $settings['codemirror'], array(
            'mode' => 'nginx',
        ) );
    } elseif ( 'application/x-httpd-php' === $type ) {
        $settings['codemirror'] = array_merge( $settings['codemirror'], array(
            'mode' => 'php',
            'autoCloseBrackets' => true,
            'autoCloseTags' => true,
            'matchBrackets' => true,
            'matchTags' => array(
                'bothTags' => true,
            ),
        ) );
    } elseif ( 'text/x-sql' === $type || 'text/x-mysql' === $type ) {
        $settings['codemirror'] = array_merge( $settings['codemirror'], array(
            'mode' => 'sql',
            'autoCloseBrackets' => true,
            'matchBrackets' => true,
        ) );
    } elseif ( false !== strpos( $type, 'xml' ) ) {
        $settings['codemirror'] = array_merge( $settings['codemirror'], array(
            'mode' => 'xml',
            'autoCloseBrackets' => true,
            'autoCloseTags' => true,
            'matchTags' => array(
                'bothTags' => true,
            ),
        ) );
    } elseif ( 'text/x-yaml' === $type ) {
        $settings['codemirror'] = array_merge( $settings['codemirror'], array(
            'mode' => 'yaml',
        ) );
    } else {
        $settings['codemirror']['mode'] = $type;
    }

    if ( !empty( $settings['codemirror']['lint'] ) ) {
        $settings['codemirror']['gutters'][] = 'CodeMirror-lint-markers';
    }

    // Let settings supplied via args override any defaults.
    foreach ( wp_array_slice_assoc( $args, array( 'codemirror', 'csslint', 'jshint', 'htmlhint' ) ) as $key => $value ) {
        $settings[ $key ] = array_merge(
            $settings[ $key ],
            $value
        );
    }

    $settings = apply_filters( 'nimble_code_editor_settings', $settings, $args );

    if ( empty( $settings ) || empty( $settings['codemirror'] ) ) {
        return false;
    }

    if ( isset( $settings['codemirror']['mode'] ) ) {
        $mode = $settings['codemirror']['mode'];
        if ( is_string( $mode ) ) {
            $mode = array(
                'name' => $mode,
            );
        }
    }

    return $settings;
}




/* ------------------------------------------------------------------------- *
 *  LOCALIZED PARAMS I18N
/* ------------------------------------------------------------------------- */
add_filter( 'nimble-sek-localized-customizer-control-params', '\Nimble\nimble_add_i18n_localized_control_params' );
function nimble_add_i18n_localized_control_params( $params ) {
    $data = array_merge( $params, array(
        'i18n' => array(
            'Sections' => __( 'Sections', 'text_doma'),

            'Nimble Builder' => __('Nimble Builder', 'text_doma'),

            "You've reached the maximum number of allowed nested sections." => __("You've reached the maximum number of allowed nested sections.", 'text_doma'),
            "You've reached the maximum number of columns allowed in this section." => __( "You've reached the maximum number of columns allowed in this section.", 'text_doma'),
            "A section must have at least one column." => __( "A section must have at least one column.", 'text_doma'),

            'If this problem locks Nimble Builder, you can try resetting the sections of this page.' => __('If this problem locks Nimble Builder, you can try resetting the sections of this page.', 'text_doma'),
            'Reset' => __('Reset', 'text_doma'),
            'Reset complete' => __('Reset complete', 'text_doma'),
            'Reset failed' => __('Reset failed', 'text_doma'),

            // Header button title text
            'Drag and drop content' => __('Drag and drop content', 'text_doma'),

            // Generated UI
            'Content Picker' => __('Content Picker', 'text_doma'),
            'Pick a pre-designed section' => __('Pick a pre-designed section', 'text_doma'),

            'Header location only accepts modules and pre-built header sections' => __('Header location only accepts modules and pre-built header sections', 'text_doma'),
            'Footer location only accepts modules and pre-built footer sections' => __('Footer location only accepts modules and pre-built footer sections', 'text_doma'),
            'You can\'t drop a header section in the footer location' => __('You can\'t drop a header section in the footer location', 'text_doma'),
            'You can\'t drop a footer section in the header location' => __('You can\'t drop a footer section in the header location', 'text_doma'),

            'Module' => __('Module', 'text_doma'),
            'Content for' => __('Content for', 'text_doma'),
            'Customize the options for module :' => __('Customize the options for module :', 'text_doma'),

            'Layout settings for the' => __('Layout settings for the', 'text_doma'),
            'Background settings for the' => __('Background settings for the', 'text_doma'),
            'Text settings for the' => __('Text settings for the', 'text_doma'),
            'Borders settings for the' => __('Borders settings for the', 'text_doma'),
            'Padding and margin settings for the' => __('Padding and margin settings for the', 'text_doma'),
            'Height, vertical alignment, z-index for the' => __('Height, vertical alignment, z-index for the', 'text_doma'),
            'Width settings for the' => __('Width settings for the', 'text_doma'),
            'Width and horizontal alignment for the' => __('Width and horizontal alignment for the', 'text_doma'),
            'Custom anchor ( CSS ID ) and CSS classes for the' => __('Custom anchor ( CSS ID ) and CSS classes for the', 'text_doma'),
            'Device visibility settings for the' => __('Device visibility settings for the', 'text_doma'),
            'Responsive settings : breakpoint, column direction' => __('Responsive settings : breakpoint, column direction', 'text_doma'),

            'Animation settings for the' => __('Animation settings for the', 'text_doma'),
            'Settings for the' => __('Settings for the', 'text_doma'),//section / column / module

            'The section cannot be moved higher.' => __('The section cannot be moved higher.', 'text_doma'),
            'The section cannot be moved lower.' => __('The section cannot be moved lower.', 'text_doma'),

            // UI global and local options
            'Current page options' => __( 'Current page options', 'text_doma'),
            'Page template' => __( 'Page template', 'text_doma'),
            'This page uses Nimble Builder template.' => __( 'This page uses Nimble Builder template.', 'text_doma'),
            'Page header and footer' => __( 'Page header and footer', 'text_doma'),
            'Inner and outer widths' => __( 'Inner and outer widths', 'text_doma'),
            'Custom CSS' => __( 'Custom CSS', 'text_doma'),
            'Remove all sections and options of this page' => __( 'Remove all sections and options of this page', 'text_doma'),
            'Remove the sections displayed in global locations' => __( 'Remove the sections displayed in global locations', 'text_doma'),
            'Page speed optimizations' => __( 'Page speed optimizations', 'text_doma'),

            'Global text options for Nimble sections' => __('Global text options for Nimble sections', 'text_doma'),
            'Site wide header and footer' => __( 'Site wide header and footer', 'text_doma'),
            'Site wide breakpoint for Nimble sections' => __( 'Site wide breakpoint for Nimble sections', 'text_doma'),
            'Site wide inner and outer sections widths' => __( 'Site wide inner and outer sections widths', 'text_doma'),

            'Site wide page speed optimizations' => __( 'Site wide page speed optimizations', 'text_doma'),
            'Beta features' => __( 'Beta features', 'text_doma'),
            'Protect your contact forms with Google reCAPTCHA' => __( 'Protect your contact forms with Google reCAPTCHA', 'text_doma'),

            // DEPRECATED
            'Options for the sections of the current page' => __( 'Options for the sections of the current page', 'text_doma'),
            'General options applied for the sections site wide' => __( 'General options applied for the sections site wide', 'text_doma'),

            'Site wide options' => __( 'Site wide options', 'text_doma'),
            'Site templates' => __('Site templates', 'text_doma'),

            // Levels
            'location' => __('location', 'text_doma'),
            'section' => __('section', 'text_doma'),
            'nested section' => __('nested section', 'text_doma'),
            'column' => __('column', 'text_doma'),
            'module' => __('module', 'text_doma'),

            // DRAG n DROP
            'This browser does not support drag and drop. You might need to update your browser or use another one.' => __('This browser does not support drag and drop. You might need to update your browser or use another one.', 'text_doma'),
            'You first need to click on a target ( with a + icon ) in the preview.' => __('You first need to click on a target ( with a + icon ) in the preview.', 'text_doma'),
            'Insert here' => __('Insert here', 'text_doma'),
            'Insert in a new section' => __('Insert in a new section', 'text_doma'),
            'Insert a new section here' => __('Insert a new section here', 'text_doma'),

            // DOUBLE CLICK INSERTION


            // MODULES
            'Select a font family' => __('Select a font family', 'text_doma'),
            'Web safe fonts' => __('Web safe fonts', 'text_doma'),
            'Google fonts' => __('Google fonts', 'text_doma'),
            'Already used fonts' => __( 'Already used fonts', 'text_doma'),

            'Set a custom url' => __('Set a custom url', 'text_doma'),

            'Something went wrong, please refresh this page.' => __('Something went wrong, please refresh this page.', 'text_doma'),

            'Select an icon' => __( 'Select an icon', 'text_doma' ),

            // Code Editor
            'codeEditorSingular' => __( 'There is %d error in your %s code which might break your site. Please fix it before saving.', 'text_doma' ),
            'codeEditorPlural' => __( 'There are %d errors in your %s code which might break your site. Please fix them before saving.', 'text_doma' ),

            // Various
            'Settings on desktops' => __('Settings on desktops', 'text_doma'),
            'Settings on tablets' => __('Settings on tablets', 'text_doma'),
            'Settings on mobiles' => __('Settings on mobiles', 'text_doma'),

            // Level Tree
            'No sections to navigate' => __('No sections to navigate', 'text_dom'),
            'Remove this element' => __('Remove this element', 'text_dom'),

            // Cache plugin warning
            // @see https://github.com/presscustomizr/nimble-builder/issues/395
            'You seem to be using a cache plugin.' => __('You seem to be using a cache plugin.', 'text_dom'),
            'It is recommended to disable your cache plugin when customizing your website.' => __('It is recommended to disable your cache plugin when customizing your website.', 'text_dom'),

            // Revision history
            // @see https://github.com/presscustomizr/nimble-builder/issues/392
            'Revision history of local sections' => __('Revision history of local sections', 'text_doma'),
            'Revision history of global sections' => __('Revision history of global sections', 'text_doma'),
            'The revision could not be restored.' => __('The revision could not be restored.', 'text_doma'),
            'The revision has been successfully restored.' => __('The revision has been successfully restored.', 'text_doma'),
            'Select' => __('Select', 'text_doma'),
            'No revision history available for the moment.' => __('No revision history available for the moment.', 'text_doma'),
            'This is the current version.' => __('This is the current version.', 'text_doma'),
            '(currently published version)' => __('(currently published version)','text_doma'),

            // Import / export
            'You need to publish before exporting.' => __( 'Nimble Builder : you need to publish before exporting.', 'text_doma'),
            'Export / Import' => __('Export / Import', 'text_doma'),
            'Export / Import global sections' => __('Export / Import global sections', 'text_doma'),
            'Export failed' => __('Export failed', 'text_doma'),
            'Nothing to export.' => __('Nimble Builder : you have nothing to export. Start adding sections to this page!', 'text_doma'),
            'Import failed' => __('Import failed', 'text_doma'),
            'Import exceeds server response time, try to uncheck "import images" option.' => __('Import exceeds server response time, try to uncheck "import images" option.', 'text_doma'),
            'The current page has no available locations to import Nimble Builder sections.' => __('The current page has no available locations to import Nimble Builder sections.', 'text_doma'),
            'Missing file' => __('Missing file', 'text_doma'),
            'File successfully imported' => __('File successfully imported', 'text_doma'),
            'Template successfully imported' => __('Template successfully imported', 'text_doma'),
            'Import failed, invalid file content' => __('Import failed, invalid file content', 'text_doma'),
            'Import failed, file problem' => __('Import failed, file problem', 'text_doma'),
            'Some image(s) could not be imported' => __('Some image(s) could not be imported', 'text_doma'),
            // 'Module' => __('Module', 'text_doma'),

            // Column width
            'This is a single-column section with a width of 100%. You can act on the internal width of the parent section, or adjust padding and margin.' => __('This is a single-column section with a width of 100%. You can act on the internal width of the parent section, or adjust padding and margin.', 'text_doma'),

            // Accordion module
            'Accordion title' => __('Accordion title', 'text_dom'),

            // Advanced list module
            'List item' => __('List item', 'text_dom'),

            // Template gallery and save
            'Last modified' => __('Last modified', 'text_dom'),
            'Use this template' => __('Use this template', 'text_dom'),
            'Edit this template' => __('Edit this template', 'text_dom'),
            'Remove this template' => __('Remove this template', 'text_dom'),
            'A title is required' => __('A title is required', 'text_dom'),
            'Template saved' => __('Template saved', 'text_dom'),
            'Template removed' => __('Template removed', 'text_dom'),
            'Error when processing templates' => __('Error when processing templates', 'text_dom'),
            'Last modified' => __('Last modified', 'text_dom'),
            'You did not save any templates yet.' => __('You did not save any templates yet.', 'text_dom'),
            'Live demo' => __('Live demo', 'text_dom'),
            '🍥 More templates coming...' => __('🍥 More templates coming...', 'text_doma'),

            // Section Save
            'You did not save any section yet.' => __('You did not save any section yet.', 'text_dom'),
            //'Remove this element' => __('Remove this element', 'text_dom'),
            //'Remove this element' => __('Remove this element', 'text_dom'),
            //'Remove this element' => __('Remove this element', 'text_dom'),

            'No template set.' => __('No template set.', 'text_dom'),
            'Template not found : reset or pick another one.' => __('Template not found : reset or pick another one.', 'text_dom'),
            'Active template : ' => __('Active template : ', 'text_dom'),
            'This page is not customized with NB' => __('This page is not customized with NB', 'text_dom'),
            'This page inherits a NB site template' => __('This page inherits a NB site template', 'text_dom'),
            'This page is customized with NB' => __('This page is customized with NB', 'text_dom'),
            'Refreshed to home page : site templates must be set when previewing home' => __('Refreshed to home page : site templates must be set when previewing home','text_dom'),

            'Remove all sections and options of this page' => __('Remove all sections and options of this page', 'text_dom'),
            'Go pro link when click on pro tmpl or section' =>  sprintf( '<a href="%2$s" target="_blank" rel="noreferrer noopener">%1$s</a>', __('🌟 This is a Nimble Builder Pro element'), NIMBLE_PRO_URL )
        )//array()
    )//array()
    );//array_merge
    if( get_option( NIMBLE_OPT_NAME_FOR_DISABLING_GOOGLE_FONTS ) == 'on' ){
        unset( $data['i18n']['Google fonts'] );   
    }
    return $data;
}//'nimble_add_i18n_localized_control_params'



// ADD SEKTION VALUES TO EXPORTED DATA IN THE CUSTOMIZER PREVIEW
add_filter( 'skp_json_export_ready_skopes', '\Nimble\add_sektion_values_to_skope_export' );
function add_sektion_values_to_skope_export( $skopes ) {
    if ( !is_array( $skopes ) ) {
        sek_error_log( __FUNCTION__ . ' error => skp_json_export_ready_skopes filter => the filtered skopes must be an array.' );
    }
    $new_skopes = array();
    foreach ( $skopes as $skp_data ) {
        if ( !is_array( $skp_data ) || empty( $skp_data['skope'] ) ) {
            sek_error_log( __FUNCTION__ . ' error => missing skope informations' );
            continue;
        }

        if ( 'group' == $skp_data['skope'] ) {
            $new_skopes[] = $skp_data;
            continue;
        }


        if ( !is_array( $skp_data ) ) {
            error_log( 'skp_json_export_ready_skopes filter => the skope data must be an array.' );
            continue;
        }
        $skope_id = 'global' === $skp_data['skope'] ? NIMBLE_GLOBAL_SKOPE_ID : skp_get_skope_id( $skp_data['skope'] );
        $seks_data = sek_get_skoped_seks( $skope_id );

        // Feb 2021 added to fix regression https://github.com/presscustomizr/nimble-builder/issues/791
        $seks_data = sek_sniff_and_decode_richtext( $seks_data );

        $skp_data[ 'sektions' ] = array(
            'db_values' => $seks_data,
            'setting_id' => sek_get_seks_setting_id( $skope_id ),//nimble___loop_start[skp__post_page_home], nimble___custom_location_id[skp__global]
        );
        if ( 'local' == $skp_data['skope'] ) {
          $skp_data['has_local_nimble_customizations'] = sek_local_skope_has_been_customized($skope_id);//<= used when printing skope status on init. see control::printSektionsSkopeStatus()
        }
        // foreach( [
        //     'loop_start',
        //     'loop_end',
        //     'before_content',
        //     'after_content',
        //     'global'
        //     ] as $location ) {
        //     $skp_data[ 'sektions' ][ $location ] = array(
        //         'db_values' => sek_get_skoped_seks( $skope_id, $location ),
        //         'setting_id' => sek_get_seks_setting_id( $skope_id, $location )//nimble___loop_start[skp__post_page_home]
        //     );
        // }
        $new_skopes[] = $skp_data;
    }

    // sek_error_log( '//////////////////// => new_skopes', $new_skopes);

    return $new_skopes;
}

// June 2020 : added for https://github.com/presscustomizr/nimble-builder/issues/708
// print a script in the head of the customizer
// inject control js script on api "ready" event
add_action( 'customize_controls_print_scripts', '\Nimble\sek_print_nimble_czr_control_js', 100 );
//add_action( 'customize_controls_print_scripts', '\Nimble\sek_print_nimble_czr_control_js', 100 );
function sek_print_nimble_czr_control_js() {
    if ( !sek_current_user_can_access_nb_ui() )
      return;
    $script_url = sprintf(
        '%1$s/assets/czr/sek/js/%2$s?ver=%3$s' ,
        NIMBLE_BASE_URL,
        sek_is_dev_mode() ? 'ccat-sek-control.js' : 'ccat-sek-control.min.js',
        NIMBLE_ASSETS_VERSION
    );
    ob_start();
    ?>
      (function() {
        var _loadScript = function() {
          wp.customize.bind( 'ready', function() {
              wp.customize.apiIsReady = true; //<= used in CZRSeksPrototype::initialize()
              var _script = document.createElement("script"),
                  customizePreviewTag = document.getElementById('customize-preview');
              _script.setAttribute('src', '<?php echo esc_url($script_url); ?>'  );
              _script.setAttribute('id', 'nb-control-js' );
              //_script.setAttribute('defer', 'defer');

              // Insert after #customize-preview
              customizePreviewTag.parentNode.insertBefore(_script, customizePreviewTag.nextSibling);
          });
        },
        // recursively try to load jquery every 100ms during 5s ( max 50 times )
        _loadWhenWpCustomizeLoaded = function( attempts ) {
            attempts = attempts || 0;
            if ( wp && wp.customize ) {
                _loadScript();
            } else if ( attempts < 50 ) {
              setTimeout( function() {
                  attempts++;
                  _loadWhenWpCustomizeLoaded( attempts );
              }, 100 );
            } else {
              if ( window.console ) {
                  console.log('Nimble Builder => Error missing wp or wp.customize in global scope' );
              }
            }
        };
        _loadWhenWpCustomizeLoaded();
      })();
    <?php
    $script = ob_get_clean();
    wp_register_script( 'nb_load_czr_control_js', '');
    wp_enqueue_script( 'nb_load_czr_control_js' );
    wp_add_inline_script( 'nb_load_czr_control_js', $script );
};

add_action( 'customize_controls_print_footer_scripts', '\Nimble\sek_print_nimble_customizer_tmpl' );
function sek_print_nimble_customizer_tmpl() {
    ?>
    <script type="text/html" id="tmpl-nimble-top-bar">
      <div id="nimble-top-bar" class="czr-preview-notification">
          <div class="sek-add-content">
            <button type="button" class="material-icons" title="<?php _e('Add content', 'text_domain'); ?>" data-nimble-state="enabled">
              add_circle_outline<span class="screen-reader-text"><?php _e('Add content', 'text_domain'); ?></span>
            </button>
          </div>
          <div class="sek-level-tree">
            <button type="button" class="fas fa-stream" title="<?php _e('Section navigation', 'text_domain'); ?>" data-nimble-state="enabled">
              <span class="screen-reader-text"><?php _e('Section navigation', 'text_domain'); ?></span>
            </button>
          </div>
          <div class="sek-do-undo">
            <?php if ( is_rtl() ) : ?>
                <button type="button" class="icon do" title="<?php _e('Undo', 'text_domain'); ?>" data-nimble-history="undo" data-nimble-state="disabled">
                  <span class="screen-reader-text"><?php _e('Undo', 'text_domain'); ?></span>
                </button>
                <button type="button" class="icon undo" title="<?php _e('Redo', 'text_domain'); ?>" data-nimble-history="redo" data-nimble-state="disabled">
                  <span class="screen-reader-text"><?php _e('Redo', 'text_domain'); ?></span>
                </button>
            <?php else : ?>
                <button type="button" class="icon undo" title="<?php _e('Undo', 'text_domain'); ?>" data-nimble-history="undo" data-nimble-state="disabled">
                  <span class="screen-reader-text"><?php _e('Undo', 'text_domain'); ?></span>
                </button>
                <button type="button" class="icon do" title="<?php _e('Redo', 'text_domain'); ?>" data-nimble-history="redo" data-nimble-state="disabled">
                  <span class="screen-reader-text"><?php _e('Redo', 'text_domain'); ?></span>
                </button>
            <?php endif; ?>
          </div>
          <div class="sek-settings">
            <button type="button" class="fas fa-sliders-h" title="<?php _e('Local and global settings', 'text_domain'); ?>" data-nimble-state="enabled">
              <span class="screen-reader-text"><?php _e('Local and global settings', 'text_domain'); ?></span>
            </button>
          </div>
          <div class="sek-tmpl-saving">
            <button type="button" class="far fa-save" title="<?php _e('Save as template', 'text_domain'); ?>" data-nimble-state="enabled">
              <span class="screen-reader-text"><?php _e('Save as template', 'text_domain'); ?></span>
            </button>
          </div>
          <div class="sek-notifications">
            <?php if ( sek_is_debug_mode() ) : ?>
                <span class="debug-mode-notif"><span class="fas fa-info-circle">&nbsp;<?php _e('Debug mode active ( WP admin > Settings > Nimble Builder options )', 'text_domain'); ?></span></span>
            <?php endif; ?>
          </div>
          <div class="sek-nimble-doc" data-doc-href="https://docs.presscustomizr.com/collection/334-nimble-builder/?utm_source=usersite&utm_medium=link&utm_campaign=nimble-customizer-topbar">
            <div class="sek-nimble-icon"><img src="<?php echo esc_url(NIMBLE_BASE_URL.'/assets/img/nimble/nimble_icon.svg?ver='.NIMBLE_VERSION); ?>" alt="<?php _e('Nimble Builder','text_domain_to_replace'); ?>" title="<?php _e('Knowledge base', 'text_domain'); ?>"/></div>
            <span class="sek-pointer" title="<?php _e('Knowledge base', 'text_domain'); ?>"><?php _e('Knowledge base', 'text_domain'); ?></span>
            <button class="far fa-question-circle" type="button" title="<?php _e('Knowledge base', 'text_domain'); ?>" data-nimble-state="enabled">
              <span class="screen-reader-text"><?php _e('Knowledge base', 'text_domain'); ?></span>
            </button>
          </div>
      </div>
    </script>



<?php // SECTION SAVING
    // June 2020, for https://github.com/presscustomizr/nimble-builder/issues/520
    ?>
    <script type="text/html" id="tmpl-nimble-top-section-save-ui">
      <div id="nimble-top-section-save-ui" class="czr-preview-notification" data-sek-section-dialog-mode="hidden">
        <div class="nb-section-save-inner">
          <div class="sek-save-section-mode-switcher">
            <div class="sek-ui-button-group" role="group">
              <button aria-pressed="false" data-section-mode-switcher="save" class="sek-ui-button" type="button" title="<?php _e('Save as new section', 'text_domain'); ?>">
                  <i class="far fa-save"></i>&nbsp;<?php _e('Save as new section', 'text_domain'); ?>
              </button>
              <button aria-pressed="false" data-section-mode-switcher="update" class="sek-ui-button" type="button" title="<?php _e('Update a section', 'text_domain'); ?>">
                  <i class="far fa-edit"></i>&nbsp;<?php _e('Update a section', 'text_domain'); ?>
              </button>
              <button aria-pressed="false" data-section-mode-switcher="edit" class="sek-ui-button" type="button" title="<?php _e('Edit a section', 'text_domain'); ?>">
                  <i class="far fa-edit"></i>&nbsp;<?php _e('Edit a section', 'text_domain'); ?>
              </button>
              <button aria-pressed="false" data-section-mode-switcher="remove" class="sek-ui-button" type="button" title="<?php _e('Remove section(s)', 'text_domain'); ?>">
                  <i class="fas fa-trash"></i>&nbsp;<?php _e('Remove section(s)', 'text_domain'); ?>
              </button>
            </div>
          </div>
          <?php // the select input is printed with a default 'none' option, other options will be populated dynamically with ajax fetching results ?>
          <select class="sek-saved-section-picker"><option selected="selected" value="none"><?php _e('Select a section', 'text_doma'); ?></option></select>
          <div class="sek-section-title">
              <label for="sek-saved-section-title" class="customize-control-title"><?php _e('Section title', 'text_doma'); ?></label>
              <input id="sek-saved-section-title" type="text" value="">
          </div>
          <div class="sek-section-description">
              <label for="sek-saved-section-description" class="customize-control-title"><?php _e('Section description', 'text_doma'); ?></label>
              <textarea id="sek-saved-section-description" type="text" value=""></textarea>
          </div>
          <div class="sek-save-section-action">
            <div class="sek-ui-button-group" role="group">
              <button class="sek-ui-button sek-do-save-section" type="button" title="<?php _e('Save section', 'text_domain'); ?>">
                <i class="far fa-save"></i>&nbsp;<?php _e('Save section', 'text_domain'); ?><span class="spinner"></span>
              </button>
              <button class="sek-ui-button sek-do-update-section" type="button" title="<?php _e('Update section', 'text_domain'); ?>">
                <i class="far fa-save"></i>&nbsp;<?php _e('Update section', 'text_domain'); ?><span class="spinner"></span>
              </button>
              <button class="sek-ui-button sek-open-remove-confirmation" type="button" title="<?php _e('Remove section', 'text_domain'); ?>">
                <i class="fas fa-trash"></i>&nbsp;<?php _e('Remove section', 'text_domain'); ?><span class="spinner"></span>
              </button>
              <button class="sek-ui-button sek-close-dialog" type="button" title="<?php _e('Close', 'text_domain'); ?>">
                  <i class="far fa-times-circle"></i>&nbsp;<?php _e('Close', 'text_domain'); ?>
              </button>
            </div>
          </div>
          <div class="sek-section-remove-dialog">
            <p><?php _e('Removing a section cannot be undone. Are you sure you want to continue?', 'text_doma'); ?>
            <div class="sek-ui-button-group" role="group">
              <button class="sek-ui-button sek-do-remove-section" type="button" title="<?php _e('Remove section', 'text_domain'); ?>">
                <?php _e('Remove section', 'text_domain'); ?><span class="spinner"></span>
              </button>
              <button class="sek-ui-button sek-cancel-remove-section" type="button" title="<?php _e('Cancel', 'text_domain'); ?>">
                <?php _e('Cancel', 'text_domain'); ?>
              </button>
            </div>
          </div>
        </div><?php //nb-section-save-inner ?>
      </div>
    </script>



    <?php // TEMPLATE SAVING
    // April 2020, for https://github.com/presscustomizr/nimble-builder/issues/655
    ?>
    <script type="text/html" id="tmpl-nimble-top-tmpl-save-ui">
      <div id="nimble-top-tmpl-save-ui" class="czr-preview-notification" data-sek-tmpl-dialog-mode="hidden">
        <div class="nb-tmpl-save-inner">
          <div class="sek-save-tmpl-mode-switcher">
            <div class="sek-ui-button-group" role="group">
              <button aria-pressed="false" data-tmpl-mode-switcher="save" class="sek-ui-button" type="button" title="<?php _e('Save as new template', 'text_domain'); ?>">
                  <i class="far fa-save"></i>&nbsp;<?php _e('Save as new template', 'text_domain'); ?>
              </button>
              <button aria-pressed="false" data-tmpl-mode-switcher="update" class="sek-ui-button" type="button" title="<?php _e('Update a template', 'text_domain'); ?>">
                  <i class="far fa-edit"></i>&nbsp;<?php _e('Update a template', 'text_domain'); ?>
              </button>
              <button aria-pressed="false" data-tmpl-mode-switcher="edit" class="sek-ui-button" type="button" title="<?php _e('Edit a template', 'text_domain'); ?>">
                  <i class="far fa-edit"></i>&nbsp;<?php _e('Edit a template', 'text_domain'); ?>
              </button>
              <button aria-pressed="false" data-tmpl-mode-switcher="remove" class="sek-ui-button" type="button" title="<?php _e('Remove template(s)', 'text_domain'); ?>">
                  <i class="fas fa-trash"></i>&nbsp;<?php _e('Remove template(s)', 'text_domain'); ?>
              </button>
            </div>
          </div>
          <?php // the select input is printed with a default 'none' option, other options will be populated dynamically with ajax fetching results ?>
          <select class="sek-saved-tmpl-picker"><option selected="selected" value="none"><?php _e('Select a template', 'text_doma'); ?></option></select>
          <div class="sek-tmpl-title">
              <label for="sek-saved-tmpl-title" class="customize-control-title"><?php _e('Template title', 'text_doma'); ?></label>
              <input id="sek-saved-tmpl-title" type="text" value="">
          </div>
          <div class="sek-tmpl-description">
              <label for="sek-saved-tmpl-description" class="customize-control-title"><?php _e('Template description', 'text_doma'); ?></label>
              <textarea id="sek-saved-tmpl-description" type="text" value=""></textarea>
          </div>
          <div class="sek-save-tmpl-action">
            <div class="sek-ui-button-group" role="group">
              <button class="sek-ui-button sek-do-save-tmpl" type="button" title="<?php _e('Save template', 'text_domain'); ?>">
                <i class="far fa-save"></i>&nbsp;<?php _e('Save template', 'text_domain'); ?><span class="spinner"></span>
              </button>
              <button class="sek-ui-button sek-do-update-tmpl" type="button" title="<?php _e('Update template', 'text_domain'); ?>">
                <i class="far fa-save"></i>&nbsp;<?php _e('Update template', 'text_domain'); ?><span class="spinner"></span>
              </button>
              <button class="sek-ui-button sek-open-remove-confirmation" type="button" title="<?php _e('Remove template', 'text_domain'); ?>">
                <i class="fas fa-trash"></i>&nbsp;<?php _e('Remove template', 'text_domain'); ?><span class="spinner"></span>
              </button>
              <button class="sek-ui-button sek-close-dialog" type="button" title="<?php _e('Close', 'text_domain'); ?>">
                  <i class="far fa-times-circle"></i>&nbsp;<?php _e('Close', 'text_domain'); ?>
              </button>
            </div>
          </div>
          <div class="sek-tmpl-remove-dialog">
            <p><?php _e('Removing a template cannot be undone. Are you sure you want to continue?', 'text_doma'); ?>
            <div class="sek-ui-button-group" role="group">
              <button class="sek-ui-button sek-do-remove-tmpl" type="button" title="<?php _e('Remove template', 'text_domain'); ?>">
                <?php _e('Remove template', 'text_domain'); ?><span class="spinner"></span>
              </button>
              <button class="sek-ui-button sek-cancel-remove-tmpl" type="button" title="<?php _e('Cancel', 'text_domain'); ?>">
                <?php _e('Cancel', 'text_domain'); ?>
              </button>
            </div>
          </div>
        </div><?php //nb-tmpl-save-inner ?>
      </div>
    </script>


    <?php // TEMPLATE GALLERY ?>
    <script type="text/html" id="tmpl-nimble-top-tmpl-gallery">
      <div id="nimble-tmpl-gallery" class="czr-preview-notification" data-sek-tmpl-dialog-mode="hidden">
        <div class="czr-css-loader czr-mr-loader" style="display:block"><div></div><div></div><div></div></div>
        <div id="sek-gal-top-bar">
          <div id="sek-tmpl-source-switcher">
            <div aria-label="" class="sek-ui-button-group" role="group">
                <button type="button" aria-pressed="true" class="sek-ui-button is-selected" title="<?php _e('Nimble Builder templates', 'text_domain'); ?>" data-sek-tmpl-source="api_tmpl"><span><?php _e('Nimble Builder templates', 'text_domain'); ?></span></button>
                <button type="button" aria-pressed="false" class="sek-ui-button" title="<?php _e('My templates', 'text_domain'); ?>" data-sek-tmpl-source="user_tmpl"><span><?php _e('My templates', 'text_domain'); ?>&nbsp;<span class="sek-new-label"><?php _e('New!', 'text_doma'); ?></span></span></button>
            </div>
          </div>
          <div class="sek-tmpl-filter-wrapper">
            <input type="text" class="sek-filter-tmpl" placeholder="<?php _e('Filter templates', 'text_domain'); ?>">
          </div>
          <div class="sek-close-button">
            <button class="sek-ui-button sek-close-dialog" type="button" title="<?php _e('Close', 'text_domain'); ?>">
                <i class="far fa-times-circle"></i>&nbsp;<?php _e('Close', 'text_domain'); ?>
            </button>
          </div>
        </div>
        <div class="sek-tmpl-gallery-inner"></div>
        <div class="sek-tmpl-gal-inject-dialog">
            <p><strong><?php _e('This page already has Nimble Builder sections. What do you want to do ?') ?></strong></p>
            <div class="sek-ui-button-group" role="group">
              <button class="sek-ui-button" type="button" title="<?php _e('Replace existing sections', 'text_domain'); ?>" data-sek-tmpl-inject-mode="replace"><?php _e('Replace existing sections', 'text_domain'); ?></button>
              <button class="sek-ui-button" type="button" title="<?php _e('Insert before existing sections', 'text_domain'); ?>" data-sek-tmpl-inject-mode="before"><?php _e('Insert before existing sections', 'text_domain'); ?></button>
              <button class="sek-ui-button" type="button" title="<?php _e('Insert after existing sections', 'text_domain'); ?>" data-sek-tmpl-inject-mode="after"><?php _e('Insert after existing sections', 'text_domain'); ?></button>
              <button class="sek-ui-button" type="button" title="<?php _e('Cancel', 'text_domain'); ?>" data-sek-tmpl-inject-mode="cancel"><?php _e('Cancel', 'text_domain'); ?></button>
            </div>
        </div>
      </div>
    </script>


    <?php // LEVEL TREE  ?>
    <script type="text/html" id="tmpl-nimble-level-tree">
      <div id="nimble-level-tree">
          <div class="sek-tree-wrap"></div>
          <button class="button sek-close-level-tree far fa-times-circle" type="button" title="<?php _e('Close', 'text_domain'); ?>">
            <?php _e('Close', 'text_domain'); ?><span class="screen-reader-text"><?php _e('Close', 'text_domain'); ?></span>
          </button>
      </div>
    </script>
    <?php
}

// The idea here is to print the markup in customize_controls_print_footer_scripts hook and print the js in customize_controls_print_scripts
// printing inline scripts @customize_controls_print_scripts is mandatory to be able to use wp_add_inline_script(). see https://github.com/presscustomizr/nimble-builder/issues/887
add_action( 'customize_controls_print_scripts', function() {
  ?>
  <?php // Detached WP Editor => added when coding https://github.com/presscustomizr/nimble-builder/issues/403 ?>
  <?php
    // the textarea id for the detached editor is 'czr-customize-content_editor'
    // this function generates the <textarea> markup
    sek_setup_nimble_editor_js( '', NIMBLE_DETACHED_TINYMCE_TEXTAREA_ID , array(
        '_content_editor_dfw' => false,
        'drag_drop_upload' => true,
        'tabfocus_elements' => 'content-html,save-post',
        'editor_height' => 235,
        'default_editor' => 'tinymce',
        'tinymce' => array(
            'resize' => false,
            'wp_autoresize_on' => false,
            'add_unload_trigger' => false,
            'wpautop' => true
        ),
    ) );
});

// The idea here is to print the markup in customize_controls_print_footer_scripts hook and print the js in customize_controls_print_scripts
// printing inline scripts @customize_controls_print_scripts is mandatory to be able to use wp_add_inline_script(). see https://github.com/presscustomizr/nimble-builder/issues/887
add_action( 'customize_controls_print_footer_scripts', function() {
  ?>
  <?php // Detached WP Editor => added when coding https://github.com/presscustomizr/nimble-builder/issues/403 ?>
  <?php
    // the textarea id for the detached editor is 'czr-customize-content_editor'
    // this function generates the <textarea> markup
    sek_setup_nimble_editor_html( '', NIMBLE_DETACHED_TINYMCE_TEXTAREA_ID , array(
        '_content_editor_dfw' => false,
        'drag_drop_upload' => true,
        'tabfocus_elements' => 'content-html,save-post',
        'editor_height' => 235,
        'default_editor' => 'tinymce',
        'tinymce' => array(
            'resize' => false,
            'wp_autoresize_on' => false,
            'add_unload_trigger' => false,
            'wpautop' => true
        ),
    ) );
}, PHP_INT_MAX);

// Introduced for https://github.com/presscustomizr/nimble-builder/issues/395
function sek_has_active_cache_plugin() {
    if ( defined( 'WP_CACHE' ) && WP_CACHE ) {
        return true;
    }

    $cache_plugins = array(
        'WP Fastest Cache' => 'wp-fastest-cache/wpFastestCache.php',
        'W3 Total Cache' => 'w3-total-cache/w3-total-cache.php',
        'LiteSpeed Cache' => 'litespeed-cache/litespeed-cache.php',
        'WP Super Cache' => 'wp-super-cache/wp-cache.php',
        'Cache Enabler' => 'cache-enabler/cache-enabler.php',
        'Autoptimize' => 'autoptimize/autoptimize.php',
        'CachePress' => 'sg-cachepress/sg-cachepress.php',
        'Comet Cache' => 'comet-cache/comet-cache.php'
    );
    $active = null;
    foreach ( $cache_plugins as $plug_name => $plug_file ) {
        if( !is_null($active) )
          break;
        if ( sek_is_plugin_active( $plug_file ) )
          $active = $plug_name;
    }
    return $active;
}

/**
* HELPER
* Check whether the plugin is active by checking the active_plugins list.
* copy of is_plugin_active declared in wp-admin/includes/plugin.php
*
*
* @param string $plugin Base plugin path from plugins directory.
* @return bool True, if in the active plugins list. False, not in the list.
*/
function sek_is_plugin_active( $plugin ) {
  return in_array( $plugin, (array) get_option( 'active_plugins', array() ) ) || sek_is_plugin_active_for_network( $plugin );
}


/**
* HELPER
* Check whether the plugin is active for the entire network.
* copy of is_plugin_active_for_network declared in wp-admin/includes/plugin.php
*
* @param string $plugin Base plugin path from plugins directory.
* @return bool True, if active for the network, otherwise false.
*/
function sek_is_plugin_active_for_network( $plugin ) {
  if ( !is_multisite() )
    return false;

  $plugins = get_site_option( 'active_sitewide_plugins');
  if ( isset($plugins[$plugin]) )
    return true;

  return false;
}

// Nov 2020 => compatibility with WPML
// When running ajax requests in the preview, WP verifies if the request is for the current site to prevent cross site injections ( XSS ) attacks
// This is done in customize-preview.js during the $.ajax preFiltering with the method api.isLinkPreviewable()
// see customize-preview.js, $.ajaxPrefilter( prefilterAjax );
// WPML filters the home_url() by adding the language to it, like mysite.com/fr/
// but the admin ajax url doesn't include the language and is mysite.com/wp-admin/admin-ajax.php, which won't pass the api.isLinkPreviewable() test
// The following filter makes sure that the base home url is always added to the list of allowed urls
//
// this filter is declared in class-wp-customize-manager.php, get_allowed_urls()
add_filter('customize_allowed_urls', function( $allowed_urls ) {
  $allowed_urls = is_array($allowed_urls) ? $allowed_urls : [];
  // @see https://developer.wordpress.org/reference/functions/get_home_url/
  if ( is_multisite() ) {
      switch_to_blog( $blog_id );
      $url_to_add = get_option( 'home' );
      restore_current_blog();
  } else {
      $url_to_add = get_option( 'home' );
  }
  $allowed_urls[] = $url_to_add;
  return $allowed_urls;
});
?><?php
add_action( 'customize_controls_print_footer_scripts', '\Nimble\sek_print_nimble_input_templates' );
function sek_print_nimble_input_templates() {


      // data structure :
      // {
      //     input_type : input_type,
      //     input_data : input_data,
      //     input_id : input_id,
      //     item_model : item_model,
      //     input_tmpl : wp.template( 'nimble-input___' + input_type )
      // }
      ?>
      <script type="text/html" id="tmpl-nimble-input-wrapper">
        <# var css_attr = serverControlParams.css_attr,
            input_data = data.input_data,
            input_type = input_data.input_type,
            is_width_100 = true === input_data['width-100'];


        // some inputs have a width of 100% even if not specified in the input_data
        if ( _.contains( ['color', 'radio', 'textarea'], input_type ) ) {
            is_width_100 = true;
        }
        var width_100_class = is_width_100 ? 'width-100' : '',
            hidden_class = 'hidden' === input_type ? 'hidden' : '',
            data_transport_attr = !_.isEmpty( input_data.transport ) ? 'data-transport="' + input_data.transport + '"' : '',
            input_width = !_.isEmpty( input_data.input_width ) ? input_data.input_width : '';
        #>

        <div class="{{css_attr.sub_set_wrapper}} {{width_100_class}} {{hidden_class}}" data-input-type="{{input_type}}" <# print(data_transport_attr); #>>
          <# if ( input_data.html_before ) { #>
            <div class="czr-html-before"><# print(input_data.html_before); #></div>
          <# } #>
          <# if ( input_data.notice_before_title ) { #>
            <span class="czr-notice"><# print(input_data.notice_before_title); #></span><br/>
          <# } #>
          <# if ( 'hidden' !== input_type ) { #>
            <# var title_width = !_.isEmpty( input_data.title_width ) ? input_data.title_width : ''; #>
            <div class="customize-control-title {{title_width}}"><# print( input_data.title ); #></div>
          <# } #>
          <# if ( input_data.notice_before ) { #>
            <span class="czr-notice"><# print(input_data.notice_before); #></span>
          <# } #>

          <?php // nested template, see https://stackoverflow.com/questions/8938841/underscore-js-nested-templates#13649447 ?>
          <?php // about print(), see https://underscorejs.org/#template ?>
          <div class="czr-input {{input_width}}"><# if ( _.isFunction( data.input_tmpl ) ) { print(data.input_tmpl(data)); } #></div>

          <# if ( input_data.notice_after ) { #>
            <span class="czr-notice"><# print(input_data.notice_after); #></span>
          <# } #>
          <# if ( input_data.html_after ) { #>
            <div class="czr-html-after"><# print(input_data.html_after); #></div>
          <# } #>
        </div><?php //css_attr.sub_set_wrapper ?>
      </script>



      <?php
      /* ------------------------------------------------------------------------- *
       *  PARTS FOR MULTI-ITEMS MODULES
       *  fixes https://github.com/presscustomizr/nimble-builder/issues/473
      /* ------------------------------------------------------------------------- */
      ?>
      <script type="text/html" id="tmpl-nimble-crud-module-part">
        <# var css_attr = serverControlParams.css_attr; #>
        <button class="{{css_attr.open_pre_add_btn}}"><?php _e('Add New', 'text_doma'); ?> <span class="fas fa-plus-square"></span></button>
        <div class="{{css_attr.pre_add_wrapper}}">
          <div class="{{css_attr.pre_add_success}}"><p></p></div>
          <div class="{{css_attr.pre_add_item_content}}">

            <span class="{{css_attr.cancel_pre_add_btn}} button"><?php _e('Cancel', 'text_doma'); ?></span> <span class="{{css_attr.add_new_btn}} button"><?php _e('Add it', 'text_doma'); ?></span>
          </div>
        </div>
      </script>

      <script type="text/html" id="tmpl-nimble-rud-item-part">
        <# var css_attr = serverControlParams.css_attr, is_sortable_class ='';
          if ( data.is_sortable ) {
              is_sortable_class = css_attr.item_sort_handle;
          }
        #>
        <div class="{{css_attr.item_header}} {{is_sortable_class}} czr-custom-model">
          <# if ( ( true === data.is_sortable ) ) { #>
            <div class="{{css_attr.item_title}} "><h4>{{ data.title }}</h4></div>
          <# } else { #>
            <div class="{{css_attr.item_title}}"><h4>{{ data.title }}</h4></div>
          <# } #>
          <div class="{{css_attr.item_btns}}">
            <a title="<?php _e('Edit', 'text_doma'); ?>" href="javascript:void(0);" class="fas fa-pencil-alt {{css_attr.edit_view_btn}}"></a>&nbsp;
            <# if ( ( true === data.items_are_clonable ) ) { #>
              <a title="<?php _e('Clone', 'text_doma'); ?>" href="javascript:void(0);" class="far fa-clone czr-clone-item"></a>&nbsp;
            <# } #>
            <a title="<?php _e('Remove', 'text_doma'); ?>" href="javascript:void(0);" class="fas fa-trash {{css_attr.display_alert_btn}}"></a>
          </div>
          <div class="{{css_attr.remove_alert_wrapper}}"></div>
        </div>
      </script>



      <?php
      /* ------------------------------------------------------------------------- *
       *  SUBTEMPLATES
      /* ------------------------------------------------------------------------- */
      ?>
      <script type="text/html" id="tmpl-nimble-subtemplate___range_number">
        <?php
          // we save the int value + unit
          // we want to keep only the numbers when printing the tmpl
          // dev note : value.replace(/\D+/g, '') : ''; not working because remove "." which we might use for em for example
        ?>
        <#
          var item_model = data.item_model,
              input_id = data.input_id,
              rawValue = _.has( item_model, input_id ) ? item_model[input_id] : null,
              value,
              unit;

          value = _.isString( rawValue ) ? rawValue.replace(/px|em|%/g,'') : rawValue;
          unit = _.isString( rawValue ) ? rawValue.replace(/[0-9]|\.|,/g, '') : 'px';
          unit = _.isEmpty( unit ) ? 'px' : unit;
          var _step = _.has( data.input_data, 'step' ) ? 'step="' + data.input_data.step + '"' : '',
              _saved_unit = _.has( item_model, 'unit' ) ? 'data-unit="' + data.input_data.unit + '"' : '',
              _min = _.has( data.input_data, 'min' ) ? 'min="' + data.input_data.min + '"': '',
              _max = _.has( data.input_data, 'max' ) ? 'max="' + data.input_data.max + '"': '';
        #>
        <div class="sek-range-wrapper">
          <input data-czrtype="{{input_id}}" type="hidden" data-sek-unit="{{unit}}"/>
          <input class="sek-range-input" type="range" <# print(_step); #> <# print(_saved_unit); #> <# print(_min); #> <# print(_max); #>/>
        </div>
        <div class="sek-number-wrapper">
            <input class="sek-pm-input" value="{{value}}" type="number" <# print(_step); #> <# print(_min); #> <# print(_max); #> >
        </div>
      </script>


      <script type="text/html" id="tmpl-nimble-subtemplate___unit_picker">
          <div class="sek-unit-wrapper">
            <div aria-label="<?php _e('unit', 'text_doma'); ?>" class="sek-ui-button-group" role="group"><button type="button" aria-pressed="false" class="sek-ui-button" title="<?php _e('pixels', 'text_doma'); ?>" data-sek-unit="px">px</button><button type="button" aria-pressed="false" class="sek-ui-button" title="em" data-sek-unit="em">em</button><button type="button" aria-pressed="false" class="sek-ui-button" title="<?php _e('percents', 'text_doma'); ?>" data-sek-unit="%">%</button></div>
          </div>
      </script>

      <script type="text/html" id="tmpl-nimble-subtemplate___number">
        <div class="sek-simple-number-wrapper">
            <input data-czrtype="{{data.input_id}}" class="sek-pm-input" value="{{value}}" type="number"  >
        </div>
      </script>











      <?php
      /* ------------------------------------------------------------------------- *
       * CODE EDITOR
      /* ------------------------------------------------------------------------- */
      ?>
      <?php
      // data structure :
      // {
      //     input_type : input_type,
      //     input_data : input_data,
      //     input_id : input_id,
      //     item_model : item_model,
      //     input_tmpl : wp.template( 'nimble-input___' + input_type )
      // }
      ?>

      <script type="text/html" id="tmpl-nimble-input___code_editor">
        <#
          var item_model = data.item_model,
              input_id = data.input_id,
              value = _.has( item_model, input_id ) ? item_model[input_id] : null,
              code_type = data.input_data.code_type;
        #>
        <textarea data-czrtype="{{input_id}}" data-editor-code-type="{{code_type}}" class="width-100" name="textarea" rows="10" cols=""></textarea>
      </script>



      <script type="text/html" id="tmpl-nimble-input___detached_tinymce_editor">
        <#
          var input_data = data.input_data,
              item_model = data.item_model,
              input_id = data.input_id,
              value = _.has( item_model, input_id ) ? item_model[input_id] : null,
              code_type = data.input_data.code_type;
        #>
        <button type="button" class="button text_editor-button" data-czr-control-id="{{ data.control_id }}" data-czr-input-id="{{input_id}}" data-czr-action="open-tinymce-editor"><?php _e('Edit', 'text_doma'); ?></button>&nbsp;
        <button type="button" class="button text_editor-button" data-czr-control-id="{{ data.control_id }}" data-czr-input-id="{{input_id}}" data-czr-action="close-tinymce-editor"><?php _e('Hide editor', 'text_doma'); ?></button>
        <input data-czrtype="{{input_id}}" type="hidden" value=""/>
      </script>

      <script type="text/html" id="tmpl-nimble-input___nimble_tinymce_editor">
        <?php
        // Added an id attribute for https://github.com/presscustomizr/nimble-builder/issues/403
        // needed to instantiate wp.editor.initialize(...)
        ?>
        <#
          var item_model = data.item_model,
              input_id = data.input_id,
              value = _.has( item_model, input_id ) ? item_model[input_id] : null;
        #>
        <textarea id="textarea-{{input_id}}" data-czrtype="{{input_id}}" class="width-100" name="textarea" rows="10" cols=""></textarea>
      </script>



      <script type="text/html" id="tmpl-nimble-input___h_alignment">
        <#
          var input_id = data.input_id;
        #>
        <div class="sek-h-align-wrapper">
          <input data-czrtype="{{input_id}}" type="hidden"/>
          <div class="sek-align-icons">
            <div data-sek-align="left" title="<?php _e('Align left', 'text_doma'); ?>"><i class="material-icons">format_align_left</i></div>
            <div data-sek-align="center" title="<?php _e('Align center', 'text_doma'); ?>"><i class="material-icons">format_align_center</i></div>
            <div data-sek-align="right" title="<?php _e('Align right', 'text_doma'); ?>"><i class="material-icons">format_align_right</i></div>
          </div>
        </div><?php // sek-h-align-wrapper ?>
      </script>


      <script type="text/html" id="tmpl-nimble-input___h_text_alignment">
        <#
          var input_id = data.input_id;
        #>
        <div class="sek-h-align-wrapper">
          <input data-czrtype="{{input_id}}" type="hidden"/>
          <div class="sek-align-icons">
            <div data-sek-align="left" title="<?php _e('Align left', 'text_doma'); ?>"><i class="material-icons">format_align_left</i></div>
            <div data-sek-align="center" title="<?php _e('Align center', 'text_doma'); ?>"><i class="material-icons">format_align_center</i></div>
            <div data-sek-align="right" title="<?php _e('Align right', 'text_doma'); ?>"><i class="material-icons">format_align_right</i></div>
            <div data-sek-align="justify" title="<?php _e('Justified', 'text_doma'); ?>"><i class="material-icons">format_align_justify</i></div>
          </div>
        </div><?php // sek-h-align-wrapper ?>
      </script>


      <script type="text/html" id="tmpl-nimble-input___nimblecheck">
        <#
          var input_id = data.input_id,
          item_model = data.item_model,
          value = _.has( item_model, input_id ) ? item_model[input_id] : false,
          _checked = ( false != value ) ? "checked=checked" : '',
          _uniqueId = wp.customize.czr_sektions.guid();
        #>
        <div class="nimblecheck-wrap">
          <input id="nimblecheck-{{_uniqueId}}" data-czrtype="{{input_id}}" type="checkbox" <# print(_checked); #> class="nimblecheck-input">
          <label for="nimblecheck-{{_uniqueId}}" class="nimblecheck-label">{{sektionsLocalizedData.i18n['Switch']}}</label>
        </div>
      </script>


      <?php
      /* ------------------------------------------------------------------------- *
       *  ALPHA COLOR
      /* ------------------------------------------------------------------------- */
      ?>
      <script type="text/html" id="tmpl-nimble-input___wp_color_alpha">
        <#
          var item_model = data.item_model,
              input_id = data.input_id,
              value = _.has( item_model, input_id ) ? item_model[input_id] : null;
        #>
        <input data-czrtype="{{data.input_id}}" class="width-100"  data-alpha="true" type="text" value="{{value}}"></input>
      </script>


      <?php
      /* ------------------------------------------------------------------------- *
       *  SIMPLE SELECT : USED FOR SELECT, FONT PICKER, ICON PICKER, ...
      /* ------------------------------------------------------------------------- */
      ?>
      <script type="text/html" id="tmpl-nimble-input___simpleselect">
        <select data-czrtype="{{data.input_id}}"></select>
      </script>

      <?php
      /* ------------------------------------------------------------------------- *
       *  SIMPLE SELECT WITH DEVICE SWITCHER
      /* ------------------------------------------------------------------------- */
      ?>
      <script type="text/html" id="tmpl-nimble-input___simpleselect_deviceswitcher">
        <input data-czrtype="{{data.input_id}}" type="hidden"/>
        <select></select>
      </script>


      <?php
      /* ------------------------------------------------------------------------- *
       *  NUMBER
      /* ------------------------------------------------------------------------- */
      ?>
      <script type="text/html" id="tmpl-nimble-input___number_simple">
        <#
          var number_tmpl = wp.customize.CZR_Helpers.getInputSubTemplate( 'number' );
          if ( _.isFunction( number_tmpl ) ) { print( number_tmpl( data ) ); }
        #>
      </script>

      <?php
      /* ------------------------------------------------------------------------- *
       *  RANGE
      /* ------------------------------------------------------------------------- */
      ?>
      <script type="text/html" id="tmpl-nimble-input___range_simple">
        <div class="sek-range-with-unit-picker-wrapper sek-no-unit-picker">
          <#
            var range_tmpl = wp.customize.CZR_Helpers.getInputSubTemplate( 'range_number' );
            if ( _.isFunction( range_tmpl ) ) { print( range_tmpl( data ) ); }
          #>
        </div>
      </script>


      <script type="text/html" id="tmpl-nimble-input___range_with_unit_picker">
        <div class="sek-range-with-unit-picker-wrapper">
            <#
              var range_tmpl = wp.customize.CZR_Helpers.getInputSubTemplate( 'range_number' );
              if ( _.isFunction( range_tmpl ) ) { print( range_tmpl( data ) ); }
              var unit_tmpl = wp.customize.CZR_Helpers.getInputSubTemplate( 'unit_picker' );
              if ( _.isFunction( unit_tmpl ) ) { print( unit_tmpl( data ) ); }
            #>
        </div>
      </script>




      <?php
      /* ------------------------------------------------------------------------- *
       *  SPACING
      /* ------------------------------------------------------------------------- */
      ?>
      <script type="text/html" id="tmpl-nimble-input___spacing">
        <input data-czrtype="{{data.input_id}}" type="hidden"/>
        <div class="sek-spacing-wrapper">
            <div class="sek-pad-marg-inner">
              <div class="sek-pm-top-bottom-wrap sek-flex-justify-center">
                <div class="sek-flex-center-stretch" data-sek-spacing="margin-top" title="<?php _e('Margin top', 'text_doma'); ?>">
                  <div class="sek-pm-input-parent">
                    <input class="sek-pm-input" value="" type="number"  >
                  </div>
                </div>
              </div>
              <div class="sek-pm-middle-wrap sek-flex-justify-center">
                <div class="sek-flex-center-stretch sek-pm-margin-left" data-sek-spacing="margin-left" title="<?php _e('Margin left', 'text_doma'); ?>">
                  <div class="sek-pm-input-parent">
                    <input class="sek-pm-input" value="" type="number"  >
                  </div>
                </div>

                <div class="sek-pm-padding-wrapper">
                  <div class="sek-flex-justify-center">
                    <div class="sek-flex-center-stretch" data-sek-spacing="padding-top" title="<?php _e('Padding top', 'text_doma'); ?>">
                      <div class="sek-pm-input-parent">
                        <input class="sek-pm-input" value="" type="number"  >
                      </div>
                    </div>
                  </div>
                    <div class="sek-flex-justify-center sek-flex-space-between">
                      <div class="sek-flex-center-stretch" data-sek-spacing="padding-left" title="<?php _e('Padding left', 'text_doma'); ?>">
                        <div class="sek-pm-input-parent">
                          <input class="sek-pm-input" value="" type="number"  >
                        </div>
                      </div>
                      <div class="sek-flex-center-stretch" data-sek-spacing="padding-right" title="<?php _e('Padding right', 'text_doma'); ?>">
                        <div class="sek-pm-input-parent">
                          <input class="sek-pm-input" value="" type="number"  >
                        </div>
                      </div>
                    </div>
                  <div class="sek-flex-justify-center">
                    <div class="sek-flex-center-stretch" data-sek-spacing="padding-bottom" title="<?php _e('Padding bottom', 'text_doma'); ?>">
                      <div class="sek-pm-input-parent">
                        <input class="sek-pm-input" value="" type="number"  >
                      </div>
                    </div>
                  </div>
                </div>

                <div class="sek-flex-center-stretch sek-pm-margin-right" data-sek-spacing="margin-right" title="<?php _e('Margin right', 'text_doma'); ?>">
                  <div class="sek-pm-input-parent">
                    <input class="sek-pm-input" value="" type="number"  >
                  </div>
                </div>
              </div>

              <div class="sek-pm-top-bottom-wrap sek-flex-justify-center">
                <div class="sek-flex-center-stretch" data-sek-spacing="margin-bottom" title="<?php _e('Margin bottom', 'text_doma'); ?>">
                  <div class="sek-pm-input-parent">
                    <input class="sek-pm-input" value="" type="number"  >
                  </div>
                </div>
              </div>
            </div><?php //sek-pad-marg-inner ?>

            <#
              var unit_tmpl = wp.customize.CZR_Helpers.getInputSubTemplate( 'unit_picker' );
              if ( _.isFunction( unit_tmpl ) ) { print( unit_tmpl( data ) ); }
            #>
            <div class="reset-spacing-wrap"><span class="sek-do-reset"><?php _e('Reset all spacing', 'text_doma' ); ?></span></div>

        </div><?php // sek-spacing-wrapper ?>
      </script>


      <?php
      /* ------------------------------------------------------------------------- *
       *  TEXT
      /* ------------------------------------------------------------------------- */
      ?>
      <script type="text/html" id="tmpl-nimble-input___text">
        <# var input_data = data.input_data; #>
        <input data-czrtype="{{data.input_id}}" type="text" value="" placeholder="<# print(input_data.placeholder); #>"></input>
      </script>



      <?php
      /* ------------------------------------------------------------------------- *
       *  CONTENT PICKER
      /* ------------------------------------------------------------------------- */
      ?>
      <script type="text/html" id="tmpl-nimble-input___content_picker">
        <span data-czrtype="{{data.input_id}}"></span>
      </script>


      <?php
      /* ------------------------------------------------------------------------- *
       *  UPLOAD
      /* ------------------------------------------------------------------------- */
      ?>
      <script type="text/html" id="tmpl-nimble-input___upload">
        <input data-czrtype="{{data.input_id}}" type="hidden"/>
        <div class="{{serverControlParams.css_attr.img_upload_container}}"></div>
      </script>


      <?php
      /* ------------------------------------------------------------------------- *
       *  BORDERS
      /* ------------------------------------------------------------------------- */
      ?>
      <script type="text/html" id="tmpl-nimble-input___borders">
        <div class="sek-borders">
          <div class="sek-border-type-wrapper">
            <div aria-label="unit" class="sek-ui-button-group" role="group"><button type="button" aria-pressed="true" class="sek-ui-button is-selected" title="<?php _e('All', 'text_doma'); ?>" data-sek-border-type="_all_"><?php _e('All', 'text_doma'); ?></button><button type="button" aria-pressed="false" class="sek-ui-button" title="<?php _e('Left', 'text_doma'); ?>" data-sek-border-type="left"><?php _e('Left', 'text_doma'); ?></button><button type="button" aria-pressed="false" class="sek-ui-button" title="<?php _e('Top', 'text_doma'); ?>" data-sek-border-type="top"><?php _e('Top', 'text_doma'); ?></button><button type="button" aria-pressed="false" class="sek-ui-button" title="<?php _e('Right', 'text_doma'); ?>" data-sek-border-type="right"><?php _e('Right', 'text_doma'); ?></button><button type="button" aria-pressed="false" class="sek-ui-button" title="<?php _e('Bottom', 'text_doma'); ?>" data-sek-border-type="bottom"><?php _e('Bottom', 'text_doma'); ?></button></div>
          </div>
          <div class="sek-range-unit-wrapper">
            <#
              var range_tmpl = wp.customize.CZR_Helpers.getInputSubTemplate( 'range_number' );
              if ( _.isFunction( range_tmpl ) ) { print( range_tmpl( data ) ); }
              var unit_tmpl = wp.customize.CZR_Helpers.getInputSubTemplate( 'unit_picker' );
              if ( _.isFunction( unit_tmpl ) ) { print( unit_tmpl( data ) ); }
            #>
          </div>
          <div class="sek-color-wrapper">
              <div class="sek-color-picker"><input class="sek-alpha-color-input" data-alpha="true" type="text" value=""/></div>
              <div class="sek-reset-button"><button type="button" class="button sek-reset-button sek-float-right"><?php _e('Reset', 'text_doma'); ?></button></div>
          </div>
        </div>
      </script>


      <?php
      /* ------------------------------------------------------------------------- *
       *  BORDER RADIUS
      /* ------------------------------------------------------------------------- */
      ?>
      <script type="text/html" id="tmpl-nimble-input___border_radius">
        <div class="sek-borders">
          <div class="sek-border-type-wrapper">
            <div aria-label="unit" class="sek-ui-button-group sek-float-left" role="group"><button type="button" aria-pressed="true" class="sek-ui-button is-selected" title="<?php _e('All', 'text_doma'); ?>" data-sek-radius-type="_all_"><?php _e('All', 'text_doma'); ?></button><button type="button" aria-pressed="false" class="sek-ui-button" title="<?php _e('Top left', 'text_doma'); ?>" data-sek-radius-type="top_left"><i class="material-icons">border_style</i></button><button type="button" aria-pressed="false" class="sek-ui-button" title="<?php _e('Top right', 'text_doma'); ?>" data-sek-radius-type="top_right"><i class="material-icons">border_style</i></button><button type="button" aria-pressed="false" class="sek-ui-button" title="<?php _e('Bottom right', 'text_doma'); ?>" data-sek-radius-type="bottom_right"><i class="material-icons">border_style</i></button><button type="button" aria-pressed="false" class="sek-ui-button" title="<?php _e('Bottom left', 'text_doma'); ?>" data-sek-radius-type="bottom_left"><i class="material-icons">border_style</i></button></div>
            <div class="sek-reset-button"><button type="button" class="button sek-reset-button sek-float-right"><?php _e('Reset', 'text_doma'); ?></button></div>
          </div>
          <div class="sek-range-unit-wrapper">
            <#
              var range_tmpl = wp.customize.CZR_Helpers.getInputSubTemplate( 'range_number' );
              if ( _.isFunction( range_tmpl ) ) { print( range_tmpl( data ) ); }
              var unit_tmpl = wp.customize.CZR_Helpers.getInputSubTemplate( 'unit_picker' );
              if ( _.isFunction( unit_tmpl ) ) { print( unit_tmpl( data ) ); }
            #>
          </div>
        </div>
      </script>


      <?php
      /* ------------------------------------------------------------------------- *
       *  MODULE OPTION SWITCHER
      /* ------------------------------------------------------------------------- */
      ?>
      <script type="text/html" id="tmpl-nimble-input___module_option_switcher">
        <input data-czrtype="{{data.input_id}}" type="hidden"/>
        <div class="sek-content-type-wrapper">
            <div aria-label="<?php _e('Option type', 'text_doma'); ?>" class="sek-ui-button-group" role="group">
                <button type="button" aria-pressed="false" class="sek-ui-button" title="<?php _e('Module Content', 'text_doma'); ?>" data-sek-option-type="content"><span class="sek-wrap-opt-switch-btn"><i class="material-icons">create</i><span><?php _e('Module Content', 'text_doma'); ?></span></span></button>
                <button type="button" aria-pressed="false" class="sek-ui-button" title="<?php _e('Module Settings', 'text_doma'); ?>" data-sek-option-type="settings"><span class="sek-wrap-opt-switch-btn"><i class="material-icons">tune</i><span><?php _e('Module Settings', 'text_doma'); ?></span></span></button>
            </div>
        </div>
      </script>


      <?php
      /* ------------------------------------------------------------------------- *
       *  CONTENT SWITCHER
      /* ------------------------------------------------------------------------- */
      ?>
      <script type="text/html" id="tmpl-nimble-input___content_type_switcher">
        <input data-czrtype="{{data.input_id}}" type="hidden"/>
        <div class="sek-content-type-wrapper">
            <div aria-label="<?php _e('Content type', 'text_doma'); ?>" class="sek-ui-button-group" role="group">
                <button type="button" aria-pressed="false" class="sek-ui-button" title="<?php _e('Pick a section', 'text_doma'); ?>" data-sek-content-type="section"><?php _e('Pick a section', 'text_doma'); ?></button>
                <button type="button" aria-pressed="false" class="sek-ui-button" title="<?php _e('Pick a module', 'text_doma'); ?>" data-sek-content-type="module"><?php _e('Pick a module', 'text_doma'); ?></button>
                <button type="button" aria-pressed="false" class="sek-ui-button" title="<?php _e('Pick a template', 'text_doma'); ?>" data-sek-content-type="template"><?php _e('Pick a template', 'text_doma'); ?>&nbsp;<span class="sek-new-label"><?php _e('New!', 'text_doma'); ?></span></button>
            </div>
        </div>
      </script>


      <?php
      /* ------------------------------------------------------------------------- *
       *  MODULE PICKER
      /* ------------------------------------------------------------------------- */
      ?>
      <script type="text/html" id="tmpl-nimble-input___module_picker">
        <input data-czrtype="{{data.input_id}}" type="hidden"/>
        <div class="sek-content-type-wrapper">
            <#
            var icon_img_html = '<i style="color:red">Missing Icon</i>', icon_img_src;

            _.each( sektionsLocalizedData.moduleCollection, function( rawModData ) {
                //normalizes the module params
                var modData = jQuery.extend( true, {}, rawModData ),
                defaultModParams = {
                  'content-type' : 'module',
                  'content-id' : '',
                  'title' : '',
                  'icon' : '',
                  'font_icon' : '',
                  'active' : true,
                  'is_pro' : false
                },
                modData = jQuery.extend( defaultModParams, modData );
                var _assets_version = "<?php echo esc_attr(NIMBLE_ASSETS_VERSION); ?>";
                if ( !_.isEmpty( modData['icon'] ) ) {
                    if ( 'http' === modData['icon'].substring(0, 4) ) {
                      icon_img_src = modData['icon'];
                    } else {
                      icon_img_src = sektionsLocalizedData.moduleIconPath + modData['icon'];
                    }
                    icon_img_src = icon_img_src + '?v=' + _assets_version;
                    icon_img_html = '<img draggable="false" title="' + modData['title'] + '" alt="' +  modData['title'] + '" class="nimble-module-icons" src="' + icon_img_src + '"/>';
                } else if ( !_.isEmpty( modData['font_icon'] ) ) {
                    icon_img_html = modData['font_icon'];
                }

                var title_attr = "<?php _e('Drag and drop or double-click to insert in your chosen target element.', 'text_doma'); ?>",
                    font_icon_class = !_.isEmpty( modData['font_icon'] ) ? 'is-font-icon' : '',
                    is_draggable = true !== modData['active'] ? 'false' : 'true',
                    is_pro_module = modData['is_pro'] ? 'yes' : 'no';
                if ( true !== modData['active'] ) {
                    if ( modData['is_pro'] ) {
                        title_attr = "<?php _e('Pro feature', 'text_doma'); ?>";
                    } else {
                        title_attr = "<?php _e('Available soon ! This module is currently in beta, you can activate it in Site Wide Options > Beta features', 'text_doma'); ?>";
                    }
                }
                // "data-sek-eligible-for-module-dropzones" was introduced for https://github.com/presscustomizr/nimble-builder/issues/540
                #>
                <div draggable="{{is_draggable}}" data-sek-eligible-for-module-dropzones="true" data-sek-content-type="{{modData['content-type']}}" data-sek-content-id="{{modData['content-id']}}" title="{{title_attr}}" data-sek-is-pro-module="{{is_pro_module}}"><div class="sek-module-icon {{font_icon_class}}"><# print(icon_img_html); #></div><div class="sek-module-title"><div class="sek-centered-module-title">{{modData['title']}}</div></div>
                  <#
                  if ( 'yes' === is_pro_module ) {
                    var pro_img_html = '<div class="sek-is-pro"><img src="' + sektionsLocalizedData.czrAssetsPath + 'sek/img/pro_orange.svg" alt="Pro feature"/></div>';
                    print(pro_img_html);
                  }
                  #>
                </div>
                <#
            });//_.each
            #>
        </div>
      </script>


      <?php
      /* ------------------------------------------------------------------------- *
       *  SECTION PICKER
      /* ------------------------------------------------------------------------- */
      ?>
      <script type="text/html" id="tmpl-nimble-input___section_picker">
        <input data-czrtype="{{data.input_id}}" type="hidden"/>
        <div class="sek-content-type-wrapper">
            <#
            // June 2020 : the section collection is passed only when rendering pre-built sections
            // @see sek_register_prebuilt_section_modules() and sek_get_sections_registration_params()
            // For user saved sections, the rendering is done in javascript, not here
            // @see @see _dev_control/modules/ui/_10_0_0_UI_module_and_section_pickers.js
            var section_collection = ( data.input_data && data.input_data.section_collection ) ? data.input_data.section_collection : [];
            // if ( _.isEmpty( section_collection ) ) {
            //     wp.customize.errare('Error in js template tmpl-nimble-input___section_picker => missing section collection');
            //     return;
            // }

            var img_version = sektionsLocalizedData.isDevMode ? Math.floor((1 + Math.random()) * 0x10000).toString(16).substring(1) : sektionsLocalizedData.nimbleVersion;
            // FOR PREBUILT SECTIONS ONLY, user sections are rendered in javascript @see _dev_control/modules/ui/_10_0_0_UI_module_and_section_pickers.js
            _.each( section_collection, function( rawSecParams ) {
                //normalizes the params
                var section_type = 'content',
                secParams = jQuery.extend( true, {}, rawSecParams ),
                defaultParams = {
                  'content-id' : '',
                  'thumb' : '',
                  'title' : '',
                  'section_type' : '',
                  'height': '',
                  'active' : true,
                  'is_pro' : false,
                  'demo_url' : false
                },
                secParams = jQuery.extend( defaultParams, secParams );

                if ( !_.isEmpty( secParams['section_type'] ) ) {
                    section_type = secParams['section_type'];
                }

                var thumbUrl = [ sektionsLocalizedData.baseUrl , '/assets/img/section_assets/thumbs/', secParams['thumb'] ,  '?ver=' , img_version ].join(''),
                    styleAttr = 'background: url(' + thumbUrl  + ') 50% 50% / cover no-repeat;',
                    is_draggable = true !== secParams['active'] ? 'false' : 'true',
                    is_pro_section = secParams['is_pro'] ? 'yes' : 'no';

                if ( !_.isEmpty(secParams['height']) ) {
                    styleAttr = styleAttr + 'height:' + secParams['height'] + ';';
                }

                #>
                <div draggable="{{is_draggable}}" data-sek-content-type="preset_section" data-sek-content-id="{{secParams['content-id']}}" style="<# print(styleAttr); #>" title="{{secParams['title']}}" data-sek-section-type="{{section_type}}" data-sek-is-pro-section="{{is_pro_section}}"><div class="sek-overlay"></div>
                  <#
                  if ( 'yes' === is_pro_section ) {
                    var pro_img_html = '<div class="sek-is-pro"><img src="' + sektionsLocalizedData.czrAssetsPath + 'sek/img/pro_orange.svg" alt="Pro feature"/></div>';
                    print(pro_img_html);
                  }
                  var demo_title = "<?php _e('View in live demo', 'text_doma'); ?>";
                  if ( secParams['demo_url'] && -1 === secParams['demo_url'].indexOf('http') ) { #>
                    <div class="sek-demo-link"><a href="https://nimblebuilder.com/nimble-builder-sections?utm_source=usersite&amp;utm_medium=link&amp;utm_campaign=section_demos{{secParams['demo_url']}}" target="_blank" rel="noopener noreferrer">{{demo_title}} <i class="fas fa-external-link-alt"></i></a></div>
                  <# } else if ( secParams['demo_url'] ) { #>
                    <div class="sek-demo-link"><a href="{{secParams['demo_url']}}" target="_blank" rel="noopener noreferrer">{{demo_title}} <i class="fas fa-external-link-alt"></i></a></div>
                  <# } #>
                </div>
                <#
            });//_.each
            #>
        </div>
      </script>



      <?php
      /* ------------------------------------------------------------------------- *
       *  BACKGROUND POSITION INPUT
      /* ------------------------------------------------------------------------- */
      ?>
      <script type="text/html" id="tmpl-nimble-input___bg_position">
        <div class="sek-bg-pos-wrapper">
          <input data-czrtype="{{data.input_id}}" type="hidden"/>
          <label class="sek-bg-pos">
            <input type="radio" name="sek-bg-pos" value="top_left">
            <span>
              <svg width="24" height="24">
                <path id="sek-pth" fill-rule="evenodd" d="M14.96 16v-1h-1v-1h-1v-1h-1v-1h-1v-1.001h-1V14h-1v-4-1h5v1h-3v.938h1v.999h1v1h1v1.001h1v1h1V16h-1z" class="sek-svg-bg-pos">
                </path>
              </svg>
            </span>
          </label>
          <label class="sek-bg-pos">
            <input type="radio" name="sek-bg-pos" value="top">
            <span>
              <svg width="24" height="24">
                <path id="sek-pth" fill-rule="evenodd" d="M14.969 12v-1h-1v-1h-1v7h-1v-7h-1v1h-1v1h-1v-1.062h1V9.937h1v-1h1V8h1v.937h1v1h1v1.001h1V12h-1z" class="sek-svg-bg-pos">
                </path>
              </svg>
            </span>
          </label>
          <label class="sek-bg-pos">
            <input type="radio" name="sek-bg-pos" value="top_right">
            <span>
              <svg width="24" height="24">
                <path id="sek-pth" fill-rule="evenodd" d="M9.969 16v-1h1v-1h1v-1h1v-1h1v-1.001h1V14h1v-4-1h-1-4v1h3v.938h-1v.999h-1v1h-1v1.001h-1v1h-1V16h1z" class="sek-svg-bg-pos">
                </path>
              </svg>
            </span>
          </label>
          <label class="sek-bg-pos">
            <input type="radio" name="sek-bg-pos" value="left">
            <span>
              <svg width="24" height="24">
                <path id="sek-pth" fill-rule="evenodd" d="M11.469 9.5h-1v1h-1v1h7v1h-7v1h1v1h1v1h-1.063v-1h-1v-1h-1v-1h-.937v-1h.937v-1h1v-1h1v-1h1.063v1z" class="sek-svg-bg-pos">
                </path>
              </svg>
            </span>
          </label>
          <label class="sek-bg-pos">
            <input type="radio" name="sek-bg-pos" value="center">
            <span>
              <svg width="24" height="24">
                <path id="sek-pth" fill-rule="evenodd" d="M12 9a3 3 0 1 1 0 6 3 3 0 0 1 0-6z" class="sek-svg-bg-pos">
                </path>
              </svg>
            </span>
          </label>
          <label class="sek-bg-pos">
            <input type="radio" name="sek-bg-pos" value="right">
            <span>
              <svg width="24" height="24">
                <path id="sek-pth" fill-rule="evenodd" d="M12.469 14.5h1v-1h1v-1h-7v-1h7v-1h-1v-1h-1v-1h1.062v1h1v1h1v1h.938v1h-.938v1h-1v1h-1v1h-1.062v-1z" class="sek-svg-bg-pos">
                </path>
              </svg>
            </span>
          </label>
          <label class="sek-bg-pos">
            <input type="radio" name="sek-bg-pos" value="bottom_left">
            <span>
              <svg width="24" height="24">
                <path id="sek-pth" fill-rule="evenodd" d="M14.969 9v1h-1v1h-1v1h-1v1h-1v1.001h-1V11h-1v5h5v-1h-3v-.938h1v-.999h1v-1h1v-1.001h1v-1h1V9h-1z" class="sek-svg-bg-pos">
                </path>
              </svg>
            </span>
          </label>
          <label class="sek-bg-pos">
            <input type="radio" name="sek-bg-pos" value="bottom">
            <span>
              <svg width="24" height="24">
                <path id="sek-pth" fill-rule="evenodd" d="M9.969 13v1h1v1h1V8h1v7h1v-1h1v-1h1v1.063h-1v.999h-1v1.001h-1V17h-1v-.937h-1v-1.001h-1v-.999h-1V13h1z" class="sek-svg-bg-pos">
                </path>
              </svg>
            </span>
          </label>
          <label class="sek-bg-pos">
            <input type="radio" name="sek-bg-pos" value="bottom_right">
            <span>
              <svg width="24" height="24">
                <path id="sek-pth" fill-rule="evenodd" d="M9.969 9v1h1v1h1v1h1v1h1v1.001h1V11h1v5h-1-4v-1h3v-.938h-1v-.999h-1v-1h-1v-1.001h-1v-1h-1V9h1z" class="sek-svg-bg-pos">
                </path>
              </svg>
            </span>
          </label>
        </div><?php // sek-bg-pos-wrapper ?>
      </script>

      <?php
      /* ------------------------------------------------------------------------- *
       *  BUTTON CHOICE
      /* ------------------------------------------------------------------------- */
      ?>
      <script type="text/html" id="tmpl-nimble-input___buttons_choice">
        <div class="sek-button-choice-wrapper">
          <input data-czrtype="{{data.input_id}}" type="hidden"/>
          <div aria-label="<?php _e('unit', 'text_doma'); ?>" class="sek-ui-button-group sek-float-right" role="group">
              <#
                var input_data = data.input_data;
                if ( _.isEmpty( input_data.choices ) || !_.isObject( input_data.choices ) ) {
                    wp.customize.errare( 'Error in buttons_choice js tmpl => missing or invalid input_data.choices');
                } else {
                    _.each( input_data.choices, function( label, choice ) {
                        #><button type="button" aria-pressed="false" class="sek-ui-button" title="{{label}}" data-sek-choice="{{choice}}">{{label}}</button><#
                    });
                }
              #>
          </div>
        </div>
      </script>


      <?php
      /* ------------------------------------------------------------------------- *
       *  MULTISELECT, CATEGORY PICKER
      /* ------------------------------------------------------------------------- */
      ?>
      <script type="text/html" id="tmpl-nimble-input___multiselect">
        <select multiple="multiple" data-czrtype="{{data.input_id}}"></select>
      </script>


      <?php
      /* ------------------------------------------------------------------------- *
       *  GRID LAYOUT
      /* ------------------------------------------------------------------------- */
      ?>
      <script type="text/html" id="tmpl-nimble-input___grid_layout">
        <div class="sek-grid-layout-wrapper">
          <input data-czrtype="{{data.input_id}}" type="hidden"/>
          <div class="sek-grid-icons">
            <div data-sek-grid-layout="list" title="<?php _e('List layout', 'text_doma'); ?>"><i class="material-icons">view_list</i></div>
            <div data-sek-grid-layout="grid" title="<?php _e('Grid layout', 'text_doma'); ?>"><i class="material-icons">view_module</i></div>
          </div>
        </div>
      </script>


      <?php
      /* ------------------------------------------------------------------------- *
       *  VERTICAL ALIGNMENT
      /* ------------------------------------------------------------------------- */
      ?>
      <script type="text/html" id="tmpl-nimble-input___v_alignment">
        <div class="sek-v-align-wrapper">
          <input data-czrtype="{{data.input_id}}" type="hidden"/>
          <div class="sek-align-icons">
            <div data-sek-align="top" title="<?php _e('Align top', 'text_doma'); ?>"><i class="material-icons">vertical_align_top</i></div>
            <div data-sek-align="center" title="<?php _e('Align center', 'text_doma'); ?>"><i class="material-icons">vertical_align_center</i></div>
            <div data-sek-align="bottom" title="<?php _e('Align bottom', 'text_doma'); ?>"><i class="material-icons">vertical_align_bottom</i></div>
          </div>
        </div>
      </script>


      <?php
      /* ------------------------------------------------------------------------- *
       *  REMOVE BUTTON
      /* ------------------------------------------------------------------------- */
      ?>
      <script type="text/html" id="tmpl-nimble-input___reset_button">
        <div class="sek-button-choice-wrapper">
          <input data-czrtype="{{data.input_id}}" type="hidden"/>
          <button type="button" aria-pressed="false" class="sek-ui-button sek-float-right" title="<?php _e('Remove now', 'text_doma'); ?>" data-sek-reset-scope="{{data.input_data.scope}}"><?php _e('Remove now', 'text_doma'); ?></button>
        </div>
      </script>

      <?php
      /* ------------------------------------------------------------------------- *
       *  REFRESH PREVIEW BUTTON
      /* ------------------------------------------------------------------------- */
      ?>
      <script type="text/html" id="tmpl-nimble-input___refresh_preview_button">
        <div class="sek-button-choice-wrapper">
          <input data-czrtype="{{data.input_id}}" type="hidden"/>
          <button type="button" aria-pressed="false" class="sek-refresh-button sek-float-right button button-primary" title="<?php _e('Refresh preview', 'text_doma'); ?>"><?php _e('Refresh preview', 'text_doma'); ?></button>
        </div>
      </script>

      <?php
      /* ------------------------------------------------------------------------- *
       *  REVISION HISTORY / HIDDEN
      /* ------------------------------------------------------------------------- */
      ?>
      <script type="text/html" id="tmpl-nimble-input___revision_history">
        <input data-czrtype="{{data.input_id}}" type="hidden"/>
      </script>


      <?php
      /* ------------------------------------------------------------------------- *
       *  IMPORT / EXPORT
      /* ------------------------------------------------------------------------- */
      ?>
      <script type="text/html" id="tmpl-nimble-input___import_export">
        <div class="sek-export-btn-wrap">
          <div class="customize-control-title width-100"><?php //_e('Export', 'text_doma'); ?></div>
          <button type="button" class="button" data-czr-control-id="{{ data.control_id }}" data-czr-input-id="{{data.input_id}}" data-czr-action="sek-export"><?php _e('Export', 'text_doma' ); ?></button>
        </div>
        <div class="sek-import-btn-wrap">
          <div class="customize-control-title width-100"><?php _e('IMPORT', 'text_doma'); ?></div>
          <span class="czr-notice"><?php _e('Select the file to import and click on Import button.', 'text_doma' ); ?></span>
          <span class="czr-notice"><?php _e('Be sure to import a file generated with Nimble Builder export system.', 'text_doma' ); ?></span>
          <?php // <DIALOG FOR LOCAL IMPORT> ?>
          <div class="czr-import-dialog czr-local-import notice notice-info">
              <div class="czr-import-message"><?php _e('Some of the imported sections need a location that is not active on this page. Sections in missing locations will not be rendered. You can continue importing or assign those sections to a contextually active location.', 'text_doma' ); ?></div>
              <button type="button" class="button" data-czr-control-id="{{ data.control_id }}" data-czr-input-id="{{data.input_id}}" data-czr-action="sek-import-as-is"><?php _e('Import without modification', 'text_doma' ); ?></button>
              <button type="button" class="button" data-czr-control-id="{{ data.control_id }}" data-czr-input-id="{{data.input_id}}" data-czr-action="sek-import-assign"><?php _e('Import in existing locations', 'text_doma' ); ?></button>
              <button type="button" class="button" data-czr-control-id="{{ data.control_id }}" data-czr-input-id="{{data.input_id}}" data-czr-action="sek-cancel-import"><?php _e('Cancel import', 'text_doma' ); ?></button>
          </div>
          <?php // </DIALOG FOR LOCAL IMPORT> ?>
          <?php // <DIALOG FOR GLOBAL IMPORT> ?>
          <div class="czr-import-dialog czr-global-import notice notice-info">
              <div class="czr-import-message"><?php _e('Some of the imported sections need a location that is not active on this page. For example, if you are importing a global header footer, you need to activate the Nimble site wide header and footer, in "Site wide header and footer" options.', 'text_doma' ); ?></div>
               <button type="button" class="button" data-czr-control-id="{{ data.control_id }}" data-czr-input-id="{{data.input_id}}" data-czr-action="sek-import-as-is"><?php _e('Import', 'text_doma' ); ?></button>
              <button type="button" class="button" data-czr-control-id="{{ data.control_id }}" data-czr-input-id="{{data.input_id}}" data-czr-action="sek-cancel-import"><?php _e('Cancel import', 'text_doma' ); ?></button>
          </div>
          <?php // </DIALOG FOR GLOBAL IMPORT> ?>
          <div class="sek-uploading"><?php _e( 'Uploading...', 'text_doma' ); ?></div>
          <input type="file" name="sek-import-file" class="sek-import-file" />
          <input type="hidden" name="sek-skope" value="{{data.input_data.scope}}" />
          <button type="button" class="button disabled" data-czr-control-id="{{ data.control_id }}" data-czr-input-id="{{data.input_id}}" data-czr-action="sek-pre-import"><?php _e('Import', 'text_doma' ); ?></button>

        </div>
        <input data-czrtype="{{data.input_id}}" type="hidden" value="{{data.value}}"/>
      </script>
      <?php

      /* ------------------------------------------------------------------------- *
       *  INACTIVE
       * Sept 2020 introduced an "inactive" input type in order to display pro info for Nimble
       * this input should be "hidden" type, and should not trigger an API change.
       * when working on https://github.com/presscustomizr/nimble-builder-pro/issues/67

      /* ------------------------------------------------------------------------- */
      ?>
      <script type="text/html" id="tmpl-nimble-input___inactive">
        <input data-czrtype="{{data.input_id}}" type="hidden"/>
      </script>
      <?php

      /* ------------------------------------------------------------------------- *
       *  SITE TMPL PICKER
      /* ------------------------------------------------------------------------- */
      ?>
      <script type="text/html" id="tmpl-nimble-input___site_tmpl_picker">
        <div class="sek-button-choice-wrapper">
          <input data-czrtype="{{data.input_id}}" type="hidden"/>
          <div class="sek-ui-button-group" role="group">
            <button type="button" aria-pressed="false" class="sek-ui-button sek-remove-site-tmpl" title="<?php _e('Reset to default', 'text_doma'); ?>"><?php _e('Reset to default', 'text_doma'); ?></button>
            <button type="button" aria-pressed="false" class="sek-ui-button sek-pick-site-tmpl" title="<?php _e('Pick a template', 'text_doma'); ?>" data-sek-group-scope="{{data.input_id}}"><?php _e('Pick a template', 'text_doma'); ?></button>
          </div>
        </div>
      </script>

      <?php
}//sek_print_nimble_input_templates() @hook 'customize_controls_print_footer_scripts'



?><?php
/* ------------------------------------------------------------------------- *
 *  SETUP DYNAMIC SERVER REGISTRATION FOR SETTING
/* ------------------------------------------------------------------------- */
// Fired @'after_setup_theme:20'
if ( !class_exists( 'SEK_CZR_Dyn_Register' ) ) :
    class SEK_CZR_Dyn_Register {
        static $instance;
        public $sanitize_callbacks = array();// <= will be populated to cache the callbacks when invoking sek_get_module_sanitize_callbacks().

        public static function get_instance( $params ) {
            if ( !isset( self::$instance ) && !( self::$instance instanceof SEK_CZR_Dyn_Register ) )
              self::$instance = new SEK_CZR_Dyn_Register( $params );
            return self::$instance;
        }

        function __construct( $params = array() ) {
            // Schedule the loading the skoped settings class
            add_action( 'customize_register', array( $this, 'load_nimble_setting_class' ) );

            add_filter( 'customize_dynamic_setting_args', array( $this, 'set_dyn_setting_args' ), 10, 2 );
            add_filter( 'customize_dynamic_setting_class', array( $this, 'set_dyn_setting_class') , 10, 3 );
        }//__construct

        //@action 'customize_register'
        function load_nimble_setting_class() {
            require_once(  NIMBLE_BASE_PATH . '/inc/sektions/seks_setting_class.php' );
        }

        //@filter 'customize_dynamic_setting_args'
        function set_dyn_setting_args( $setting_args, $setting_id ) {
            // shall start with "nimble___" or "nimble_global_opts"
            // those are the setting that will actually be saved in DB : 
            // - sektion collections ( local and global skope )
            // - global options
            // - site template options
            if ( 0 === strpos( $setting_id, NIMBLE_OPT_PREFIX_FOR_SEKTION_COLLECTION ) || 0 === strpos( $setting_id, NIMBLE_OPT_NAME_FOR_GLOBAL_OPTIONS ) ) {
                //sek_error_log( 'DYNAMICALLY REGISTERING SEK SETTING => ' . $setting_id,  $setting_args);
                return array(
                    'transport' => 'refresh',
                    'type' => 'option',
                    'default' => array(),
                    // Only the section collections are sanitized on save
                    'sanitize_callback' => 0 === strpos( $setting_id, NIMBLE_OPT_PREFIX_FOR_SEKTION_COLLECTION ) ? '\Nimble\sek_sektion_collection_sanitize_cb' : null
                    //'validate_callback'    => '\Nimble\sek_sektion_collection_validate_cb'
                );
            } else if ( 0 === strpos( $setting_id, NIMBLE_PREFIX_FOR_SETTING_NOT_SAVED ) ) {
                //sek_error_log( 'DYNAMICALLY REGISTERING SEK SETTING => ' . $setting_id,  $setting_args);
                return array(
                    'transport' => 'refresh',
                        'type' => '_nimble_ui_',//won't be saved as is,
                    'default' => array(),
                    //'sanitize_callback' => array( $this, 'sanitize_callback' ),
                    //'validate_callback'    => '\Nimble\sek_sektion_collection_validate_cb'
                );
            }
            return $setting_args;
            //return wp_parse_args( array( 'default' => array() ), $setting_args );
        }


        //@filter 'customize_dynamic_setting_class'
        // We use a custom setting class only for the section collections ( local and global ), not for global options and site template options
        function set_dyn_setting_class( $class, $setting_id, $args ) {
            //sek_error_log( 'REGISTERING CLASS DYNAMICALLY for setting =>' . $setting_id );
            // Setting class for NB global options and Site Template options
            if ( 0 === strpos( $setting_id, NIMBLE_OPT_NAME_FOR_GLOBAL_OPTIONS ) ) {
                return '\Nimble\Nimble_Options_Setting';
            }
            
            // Setting class for NB sektion collections => shall start with 'nimble___'
            if ( 0 === strpos( $setting_id, NIMBLE_OPT_PREFIX_FOR_SEKTION_COLLECTION ) ) {
                return '\Nimble\Nimble_Collection_Setting';
            }
            return $class;
        }

 }//class
endif;

?><?php
// The idea here is to print the markup in customize_controls_print_footer_scripts hook and print the js in customize_controls_print_scripts
// printing inline scripts @customize_controls_print_scripts is mandatory to be able to use wp_add_inline_script(). see https://github.com/presscustomizr/nimble-builder/issues/887
function sek_setup_nimble_editor_js( $content, $editor_id, $settings = array() ) {
  _NIMBLE_Editors::nimble_editor_js( $content, $editor_id, $settings );
}

function sek_setup_nimble_editor_html( $content, $editor_id, $settings = array() ) {
  _NIMBLE_Editors::render_nimble_editor( $content, $editor_id, $settings );
}



/**
 * started from a copy of wp-includes/class-wp-editor.php as of March 2019
 * _NIMBLE_Editors::nimble_editor() is fired with sek_setup_nimble_editor() in hook 'customize_controls_print_footer_scripts'
 * the job of this class is to print the js parameters for the detached tinyMce editor for Nimble
 * the editor is then destroyed and re-instantiated each time a WP text editor module is customized
 * @see api.czrInputMap.detached_tinymce_editor
 */

final class _NIMBLE_Editors {
  public static $mce_locale;

  private static $mce_settings = array();
  private static $qt_settings  = array();
  private static $plugins      = array();
  private static $qt_buttons   = array();
  private static $ext_plugins;
  private static $baseurl;
  private static $first_init;
  private static $this_tinymce       = false;
  private static $this_quicktags     = false;
  private static $has_tinymce        = false;
  private static $has_quicktags      = false;
  private static $has_medialib       = false;
  private static $editor_buttons_css = true;
  private static $drag_drop_upload   = false;
  private static $old_dfw_compat     = false;
  private static $translation;
  private static $tinymce_scripts_printed = false;
  private static $link_dialog_printed     = false;

  private static $editor_markup;// <= used to cache the editor markup and render it afterwards @customize_controls_print_footer_scripts

  private function __construct() {}

  /**
   * Parse default arguments for the editor instance.
   *
   * @param string $editor_id ID for the current editor instance.
   * @param array  $settings {
   *     Array of editor arguments.
   *
   *     @type bool       $wpautop           Whether to use wpautop(). Default true.
   *     @type bool       $media_buttons     Whether to show the Add Media/other media buttons.
   *     @type string     $default_editor    When both TinyMCE and Quicktags are used, set which
   *                                         editor is shown on page load. Default empty.
   *     @type bool       $drag_drop_upload  Whether to enable drag & drop on the editor uploading. Default false.
   *                                         Requires the media modal.
   *     @type string     $textarea_name     Give the textarea a unique name here. Square brackets
   *                                         can be used here. Default $editor_id.
   *     @type int        $textarea_rows     Number rows in the editor textarea. Default 20.
   *     @type string|int $tabindex          Tabindex value to use. Default empty.
   *     @type string     $tabfocus_elements The previous and next element ID to move the focus to
   *                                         when pressing the Tab key in TinyMCE. Default ':prev,:next'.
   *     @type string     $editor_css        Intended for extra styles for both Visual and Text editors.
   *                                         Should include `<style>` tags, and can use "scoped". Default empty.
   *     @type string     $editor_class      Extra classes to add to the editor textarea element. Default empty.
   *     @type bool       $teeny             Whether to output the minimal editor config. Examples include
   *                                         Press This and the Comment editor. Default false.
   *     @type bool       $dfw               Deprecated in 4.1. Since 4.3 used only to enqueue wp-fullscreen-stub.js
   *                                         for backward compatibility.
   *     @type bool|array $tinymce           Whether to load TinyMCE. Can be used to pass settings directly to
   *                                         TinyMCE using an array. Default true.
   *     @type bool|array $quicktags         Whether to load Quicktags. Can be used to pass settings directly to
   *                                         Quicktags using an array. Default true.
   * }
   * @return array Parsed arguments array.
   */
  public static function parse_settings( $editor_id, $settings ) {

    /**
     * Filters the wp_editor() settings.
     *
     * @since 4.0.0
     *
     * @see _NIMBLE_Editors::parse_settings()
     *
     * @param array  $settings  Array of editor arguments.
     * @param string $editor_id ID for the current editor instance.
     */
    $settings = apply_filters( 'nimble_editor_settings', $settings, $editor_id );

    $set = wp_parse_args(
      $settings,
      array(
        // Disable autop if the current post has blocks in it.
        'wpautop'             => !has_blocks(),
        'media_buttons'       => true,
        'default_editor'      => '',
        'drag_drop_upload'    => false,
        'textarea_name'       => $editor_id,
        'textarea_rows'       => 20,
        'tabindex'            => '',
        'tabfocus_elements'   => ':prev,:next',
        'editor_css'          => '',
        'editor_class'        => '',
        'teeny'               => false,
        'dfw'                 => false,
        '_content_editor_dfw' => false,
        'tinymce'             => true,
        'quicktags'           => true,
      )
    );

    self::$this_tinymce = ( $set['tinymce'] && user_can_richedit() );

    if ( self::$this_tinymce ) {
      if ( false !== strpos( $editor_id, '[' ) ) {
        self::$this_tinymce = false;
        _deprecated_argument( 'wp_editor()', '3.9.0', 'TinyMCE editor IDs cannot have brackets.' );
      }
    }

    self::$this_quicktags = (bool) $set['quicktags'];

    if ( self::$this_tinymce ) {
      self::$has_tinymce = true;
    }

    if ( self::$this_quicktags ) {
      self::$has_quicktags = true;
    }

    if ( $set['dfw'] ) {
      self::$old_dfw_compat = true;
    }

    if ( empty( $set['editor_height'] ) ) {
      return $set;
    }

    if ( 'content' === $editor_id && empty( $set['tinymce']['wp_autoresize_on'] ) ) {
      // A cookie (set when a user resizes the editor) overrides the height.
      $cookie = (int) get_user_setting( 'ed_size' );

      if ( $cookie ) {
        $set['editor_height'] = $cookie;
      }
    }

    if ( $set['editor_height'] < 50 ) {
      $set['editor_height'] = 50;
    } elseif ( $set['editor_height'] > 5000 ) {
      $set['editor_height'] = 5000;
    }

    return $set;
  }

  /**
   * Outputs the JS for a single instance of the editor.
   *
   * @param string $content The initial content of the editor.
   * @param string $editor_id ID for the textarea and TinyMCE and Quicktags instances (can contain only ASCII letters and numbers).
   * @param array $settings See _NIMBLE_Editors::parse_settings() for description.
   */
  public static function nimble_editor_js( $content, $editor_id, $settings = array() ) {
    $set            = self::parse_settings( $editor_id, $settings );

    if ( !current_user_can( 'upload_files' ) ) {
      $set['media_buttons'] = false;
    }
    self::editor_settings( $editor_id, $set );
  }


/**
   * Outputs the HTML for a single instance of the editor.
   * 
   *
   * @param string $content The initial content of the editor.
   * @param string $editor_id ID for the textarea and TinyMCE and Quicktags instances (can contain only ASCII letters and numbers).
   * @param array $settings See _NIMBLE_Editors::parse_settings() for description.
   */
  public static function render_nimble_editor( $content, $editor_id, $settings = array() ) {
    $set            = self::parse_settings( $editor_id, $settings );
    $default_editor = 'html';
    $buttons        = $autocomplete = '';
    $editor_id_attr = esc_attr( $editor_id );

    if ( $set['drag_drop_upload'] ) {
      self::$drag_drop_upload = true;
    }

    if ( !current_user_can( 'upload_files' ) ) {
      $set['media_buttons'] = false;
    }

    if ( self::$this_tinymce ) {
      $autocomplete = ' autocomplete="off"';

      if ( self::$this_quicktags ) {
        $default_editor = $set['default_editor'] ? $set['default_editor'] : wp_default_editor();
        // 'html' is used for the "Text" editor tab.
        if ( 'html' !== $default_editor ) {
          $default_editor = 'tinymce';
        }

        $buttons .= '<button type="button" id="' . $editor_id_attr . '-tmce" class="wp-switch-editor switch-tmce"' .
          ' data-wp-editor-id="' . $editor_id_attr . '">' . _x( 'Visual', 'Name for the Visual editor tab' ) . "</button>\n";
        $buttons .= '<button type="button" id="' . $editor_id_attr . '-html" class="wp-switch-editor switch-html"' .
          ' data-wp-editor-id="' . $editor_id_attr . '">' . _x( 'Text', 'Name for the Text editor tab (formerly HTML)' ) . "</button>\n";
      } else {
        $default_editor = 'tinymce';
      }
    }

    $switch_class = 'html' === $default_editor ? 'html-active' : 'tmce-active';
    $wrap_class   = 'wp-core-ui wp-editor-wrap ' . $switch_class;

    if ( $set['_content_editor_dfw'] ) {
      $wrap_class .= ' has-dfw';
    }

    // Detached WP Editor => added when coding https://github.com/presscustomizr/nimble-builder/issues/403
    echo '<div id="czr-customize-content_editor-pane">';
    printf('<div data-czr-action="close-tinymce-editor" class="czr-close-editor"><i class="fas fa-arrow-circle-down" title="%1$s"></i>&nbsp;<span>%2$s</span></div>', __( 'Hide Editor', 'text_doma' ), __( 'Hide Editor', 'text_doma'));
      printf('<div id="czr-customize-content_editor-dragbar" title="%1$s">', __('Resize the editor', 'text_domain'));
        printf('<span class="screen-reader-text">%1$s</span>', __( 'Resize the editor', 'nimble-builder' ));
        echo '<i class="czr-resize-handle fas fa-arrows-alt-v"></i>';
      echo '</div>';

      echo '<div id="wp-' . esc_attr($editor_id_attr) . '-wrap" class="' . esc_attr($wrap_class) . '">';
      if ( self::$editor_buttons_css ) {
        wp_print_styles( 'editor-buttons' );
        self::$editor_buttons_css = false;
      }

      if ( !empty( $set['editor_css'] ) ) {
        echo wp_kses_post($set['editor_css']) . "\n";
      }

      if ( !empty( $buttons ) || $set['media_buttons'] ) {
        echo '<div id="wp-' . esc_attr($editor_id_attr) . '-editor-tools" class="wp-editor-tools hide-if-no-js">';

        if ( $set['media_buttons'] ) {
          self::$has_medialib = true;

          if ( !function_exists( 'media_buttons' ) ) {
            include( ABSPATH . 'wp-admin/includes/media.php' );
          }

          echo '<div id="wp-' . esc_attr($editor_id_attr) . '-media-buttons" class="wp-media-buttons">';

          /**
           * Fires after the default media button(s) are displayed.
           *
           * @since 2.5.0
           *
           * @param string $editor_id Unique editor identifier, e.g. 'content'.
           */
          do_action( 'media_buttons', $editor_id );
          echo "</div>\n";
        }

        echo '<div class="wp-editor-tabs">' . wp_kses_post($buttons) . "</div>\n";
        echo "</div>\n";
      }

      $quicktags_toolbar = '';

      if ( self::$this_quicktags ) {
        if ( 'content' === $editor_id && !empty( $GLOBALS['current_screen'] ) && $GLOBALS['current_screen']->base === 'post' ) {
          $toolbar_id = 'ed_toolbar';
        } else {
          $toolbar_id = 'qt_' . esc_attr($editor_id_attr) . '_toolbar';
        }

        $quicktags_toolbar = '<div id="' . esc_attr($toolbar_id) . '" class="quicktags-toolbar"></div>';
      }

      /**
       * Filters the HTML markup output that displays the editor.
       *
       * @since 2.1.0
       *
       * @param string $output Editor's HTML markup.
       */
      $the_editor = apply_filters(
        'the_nimble_editor',
        '<div id="wp-' . esc_attr($editor_id_attr) . '-editor-container" class="wp-editor-container">' .
        $quicktags_toolbar .
        sprintf('<textarea' . ' class="%1$s" %2$s %3$s %4$s cols="40" name="%5$s" ' .
        'id="' . esc_attr($editor_id_attr) . '">',
          trim( esc_attr( $set['editor_class'] ) . ' wp-editor-area' ),
          !empty( $set['editor_height'] ) ? 'style="height: ' . esc_attr((int) $set['editor_height']) . 'px"' : 'rows="' . esc_attr((int) $set['textarea_rows']) . '"',
          $set['tabindex'] ? ' tabindex="' . esc_attr((int) $set['tabindex']) . '"' : '',
          self::$this_tinymce ? 'autocomplete="off"' : '',
          esc_attr( $set['textarea_name'] )
        ) . '%s</textarea></div>'
      );

      // if ( self::$this_tinymce ) {
      //   $autocomplete = ' autocomplete="off"';

      // $the_editor = apply_filters(
      //   'the_nimble_editor',
      //   '<div id="wp-' . esc_attr($editor_id_attr) . '-editor-container" class="wp-editor-container">' .
      //   $quicktags_toolbar .
      //   '<textarea' . $editor_class . $height . $tabindex . $autocomplete . ' cols="40" name="' . esc_attr( $set['textarea_name'] ) . '" ' .
      //   'id="' . esc_attr($editor_id_attr) . '">%s</textarea></div>'
      // );

      // if ( !empty( $set['editor_height'] ) ) {
      //   $height = ' style="height: ' . (int) $set['editor_height'] . 'px"';
      // } else {
      //   $height = ' rows="' . (int) $set['textarea_rows'] . '"';
      // }


      // Prepare the content for the Visual or Text editor, only when TinyMCE is used (back-compat).
      if ( self::$this_tinymce ) {
        add_filter( 'the_nimble_editor_content', 'format_for_editor', 10, 2 );
      }

      /**
       * Filters the default editor content.
       *
       * @since 2.1.0
       *
       * @param string $content        Default editor content.
       * @param string $default_editor The default editor for the current user.
       *                               Either 'html' or 'tinymce'.
       */
      $content = apply_filters( 'the_nimble_editor_content', $content, $default_editor );

      // Remove the filter as the next editor on the same page may not need it.
      if ( self::$this_tinymce ) {
        remove_filter( 'the_editor_content', 'format_for_editor' );
      }

      // Back-compat for the `htmledit_pre` and `richedit_pre` filters
      if ( 'html' === $default_editor && has_filter( 'htmledit_pre' ) ) {
        /** This filter is documented in wp-includes/deprecated.php */
        $content = apply_filters_deprecated( 'htmledit_pre', array( $content ), '4.3.0', 'format_for_editor' );
      } elseif ( 'tinymce' === $default_editor && has_filter( 'richedit_pre' ) ) {
        /** This filter is documented in wp-includes/deprecated.php */
        $content = apply_filters_deprecated( 'richedit_pre', array( $content ), '4.3.0', 'format_for_editor' );
      }

      if ( false !== stripos( $content, 'textarea' ) ) {
        $content = preg_replace( '%</textarea%i', '&lt;/textarea', $content );
      }

      echo wp_kses_post(sprintf( $the_editor, $content ));
    echo "\n</div></div>\n\n";
  }







  /**
   * @global string $tinymce_version
   *
   * @param string $editor_id
   * @param array  $set
   */
  public static function editor_settings( $editor_id, $set ) {
    global $tinymce_version;

    if ( empty( self::$first_init ) ) {
      add_action( 'customize_controls_print_scripts', array( __CLASS__, 'editor_js' ), 50 );
      add_action( 'customize_controls_print_footer_scripts', array( __CLASS__, 'force_uncompressed_tinymce' ), 1 );
      add_action( 'customize_controls_print_footer_scripts', array( __CLASS__, 'enqueue_scripts' ), 1 );
    }

    if ( self::$this_quicktags ) {

      $qtInit = array(
        'id'      => $editor_id,
        'buttons' => '',
      );

      if ( is_array( $set['quicktags'] ) ) {
        $qtInit = array_merge( $qtInit, $set['quicktags'] );
      }

      if ( empty( $qtInit['buttons'] ) ) {
        //$qtInit['buttons'] = 'strong,em,link,block,del,ins,img,ul,ol,li,code,more,close';
        //@nikeo modif
        $qtInit['buttons'] = 'strong,em,link,block,del,ins,img,ul,ol,li,code';
      }

      if ( $set['_content_editor_dfw'] ) {
        $qtInit['buttons'] .= ',dfw';
      }

      /**
       * Filters the Quicktags settings.
       *
       * @since 3.3.0
       *
       * @param array  $qtInit    Quicktags settings.
       * @param string $editor_id The unique editor ID, e.g. 'content'.
       */
      $qtInit = apply_filters( 'nimble_quicktags_settings', $qtInit, $editor_id );

      self::$qt_settings[ $editor_id ] = $qtInit;

      self::$qt_buttons = array_merge( self::$qt_buttons, explode( ',', $qtInit['buttons'] ) );
    }

    if ( self::$this_tinymce ) {

      if ( empty( self::$first_init ) ) {
        $baseurl     = self::get_baseurl();
        $mce_locale  = self::get_mce_locale();
        $ext_plugins = '';

        if ( $set['teeny'] ) {

          /**
           * Filters the list of teenyMCE plugins.
           *
           * @since 2.7.0
           *
           * @param array  $plugins   An array of teenyMCE plugins.
           * @param string $editor_id Unique editor identifier, e.g. 'content'.
           */
          $plugins = apply_filters( 'nimble_teeny_mce_plugins', array( 'colorpicker', 'lists', 'fullscreen', 'image', 'wordpress', 'wpeditimage', 'wplink' ), $editor_id );
        } else {

          /**
           * Filters the list of TinyMCE external plugins.
           *
           * The filter takes an associative array of external plugins for
           * TinyMCE in the form 'plugin_name' => 'url'.
           *
           * The url should be absolute, and should include the js filename
           * to be loaded. For example:
           * 'myplugin' => 'http://mysite.com/wp-content/plugins/myfolder/mce_plugin.js'.
           *
           * If the external plugin adds a button, it should be added with
           * one of the 'mce_buttons' filters.
           *
           * @since 2.5.0
           *
           * @param array $external_plugins An array of external TinyMCE plugins.
           */
          $mce_external_plugins = apply_filters( 'nimble_mce_external_plugins', array() );

          $plugins = array(
            'charmap',
            'colorpicker',
            'hr',
            'lists',
            'media',
            'paste',
            'tabfocus',
            'textcolor',
            'fullscreen',
            'wordpress',
            'wpautoresize',
            'wpeditimage',
            'wpemoji',
            'wpgallery',
            'wplink',
            'wpdialogs',
            'wptextpattern',
            'wpview',
          );

          if ( !self::$has_medialib ) {
            $plugins[] = 'image';
          }

          /**
           * Filters the list of default TinyMCE plugins.
           *
           * The filter specifies which of the default plugins included
           * in WordPress should be added to the TinyMCE instance.
           *
           * @since 3.3.0
           *
           * @param array $plugins An array of default TinyMCE plugins.
           */
          $plugins = array_unique( apply_filters( 'nimble_tiny_mce_plugins', $plugins ) );

          if ( ( $key = array_search( 'spellchecker', $plugins ) ) !== false ) {
            // Remove 'spellchecker' from the internal plugins if added with 'tiny_mce_plugins' filter to prevent errors.
            // It can be added with 'mce_external_plugins'.
            unset( $plugins[ $key ] );
          }

          if ( !empty( $mce_external_plugins ) ) {

            /**
             * Filters the translations loaded for external TinyMCE 3.x plugins.
             *
             * The filter takes an associative array ('plugin_name' => 'path')
             * where 'path' is the include path to the file.
             *
             * The language file should follow the same format as wp_mce_translation(),
             * and should define a variable ($strings) that holds all translated strings.
             *
             * @since 2.5.0
             *
             * @param array $translations Translations for external TinyMCE plugins.
             */
            $mce_external_languages = apply_filters( 'nimble_mce_external_languages', array() );

            $loaded_langs = array();
            $strings      = '';

            if ( !empty( $mce_external_languages ) ) {
              foreach ( $mce_external_languages as $name => $path ) {
                if ( @is_file( $path ) && @is_readable( $path ) ) {
                  include_once( $path );
                  $ext_plugins   .= $strings . "\n";
                  $loaded_langs[] = $name;
                }
              }
            }

            foreach ( $mce_external_plugins as $name => $url ) {
              if ( in_array( $name, $plugins, true ) ) {
                unset( $mce_external_plugins[ $name ] );
                continue;
              }

              $url                           = set_url_scheme( $url );
              $mce_external_plugins[ $name ] = $url;
              $plugurl                       = dirname( $url );
              $strings                       = '';

              // Try to load langs/[locale].js and langs/[locale]_dlg.js
              if ( !in_array( $name, $loaded_langs, true ) ) {
                $path = str_replace( content_url(), '', $plugurl );
                $path = WP_CONTENT_DIR . $path . '/langs/';

                if ( function_exists( 'realpath' ) ) {
                  $path = trailingslashit( realpath( $path ) );
                }

                if ( @is_file( $path . $mce_locale . '.js' ) ) {
                  $strings .= @file_get_contents( $path . $mce_locale . '.js' ) . "\n";
                }

                if ( @is_file( $path . $mce_locale . '_dlg.js' ) ) {
                  $strings .= @file_get_contents( $path . $mce_locale . '_dlg.js' ) . "\n";
                }

                if ( 'en' != $mce_locale && empty( $strings ) ) {
                  if ( @is_file( $path . 'en.js' ) ) {
                    $str1     = @file_get_contents( $path . 'en.js' );
                    $strings .= preg_replace( '/([\'"])en\./', '$1' . $mce_locale . '.', $str1, 1 ) . "\n";
                  }

                  if ( @is_file( $path . 'en_dlg.js' ) ) {
                    $str2     = @file_get_contents( $path . 'en_dlg.js' );
                    $strings .= preg_replace( '/([\'"])en\./', '$1' . $mce_locale . '.', $str2, 1 ) . "\n";
                  }
                }

                if ( !empty( $strings ) ) {
                  $ext_plugins .= "\n" . $strings . "\n";
                }
              }

              $ext_plugins .= 'nimbleTinyMCEPreInit.load_ext("' . $plugurl . '", "' . $mce_locale . '");' . "\n";
            }
          }
        }

        self::$plugins     = $plugins;
        self::$ext_plugins = $ext_plugins;

        $settings            = self::default_settings();
        $settings['plugins'] = implode( ',', $plugins );

        if ( !empty( $mce_external_plugins ) ) {
          $settings['external_plugins'] = wp_json_encode( $mce_external_plugins );
        }

        /** This filter is documented in wp-admin/includes/media.php */
        if ( apply_filters( 'disable_captions', '' ) ) {
          $settings['wpeditimage_disable_captions'] = true;
        }

        $mce_css       = $settings['content_css'];
        $editor_styles = get_editor_stylesheets();

        if ( !empty( $editor_styles ) ) {
          // Force urlencoding of commas.
          foreach ( $editor_styles as $key => $url ) {
            if ( strpos( $url, ',' ) !== false ) {
              $editor_styles[ $key ] = str_replace( ',', '%2C', $url );
            }
          }

          $mce_css .= ',' . implode( ',', $editor_styles );
        }

        /**
         * Filters the comma-delimited list of stylesheets to load in TinyMCE.
         *
         * @since 2.1.0
         *
         * @param string $stylesheets Comma-delimited list of stylesheets.
         */
        $mce_css = trim( apply_filters( 'nimble_mce_css', $mce_css ), ' ,' );

        if ( !empty( $mce_css ) ) {
          $settings['content_css'] = $mce_css;
        } else {
          unset( $settings['content_css'] );
        }

        self::$first_init = $settings;
      }

      if ( $set['teeny'] ) {

        /**
         * Filters the list of teenyMCE buttons (Text tab).
         *
         * @since 2.7.0
         *
         * @param array  $buttons   An array of teenyMCE buttons.
         * @param string $editor_id Unique editor identifier, e.g. 'content'.
         */
        $mce_buttons   = apply_filters( 'nimble_teeny_mce_buttons', array( 'bold', 'italic', 'underline', 'blockquote', 'strikethrough', 'bullist', 'numlist', 'alignleft', 'aligncenter', 'alignright', 'undo', 'redo', 'link', 'fullscreen' ), $editor_id );
        $mce_buttons_2 = $mce_buttons_3 = $mce_buttons_4 = array();
      } else {
        //@nikeo modif
        //$mce_buttons = array( 'formatselect', 'bold', 'italic', 'bullist', 'numlist', 'blockquote', 'alignleft', 'aligncenter', 'alignright', 'link', 'wp_more', 'spellchecker' );
        $mce_buttons = array( 'formatselect', 'bold', 'italic', 'bullist', 'numlist', 'blockquote', 'alignleft', 'aligncenter', 'alignright', 'link', 'spellchecker' );

        if ( !wp_is_mobile() ) {
          if ( $set['_content_editor_dfw'] ) {
            $mce_buttons[] = 'dfw';
          } else {
            $mce_buttons[] = 'fullscreen';
          }
        }

        $mce_buttons[] = 'wp_adv';

        /**
         * Filters the first-row list of TinyMCE buttons (Visual tab).
         *
         * @since 2.0.0
         *
         * @param array  $buttons   First-row list of buttons.
         * @param string $editor_id Unique editor identifier, e.g. 'content'.
         */
        $mce_buttons = apply_filters( 'nimble_mce_buttons', $mce_buttons, $editor_id );

        $mce_buttons_2 = array( 'strikethrough', 'hr', 'forecolor', 'pastetext', 'removeformat', 'charmap', 'outdent', 'indent', 'undo', 'redo' );

        // @nikeo modif
        // if ( !wp_is_mobile() ) {
        //   $mce_buttons_2[] = 'wp_help';
        // }

        /**
         * Filters the second-row list of TinyMCE buttons (Visual tab).
         *
         * @since 2.0.0
         *
         * @param array  $buttons   Second-row list of buttons.
         * @param string $editor_id Unique editor identifier, e.g. 'content'.
         */
        $mce_buttons_2 = apply_filters( 'nimble_mce_buttons_2', $mce_buttons_2, $editor_id );

        /**
         * Filters the third-row list of TinyMCE buttons (Visual tab).
         *
         * @since 2.0.0
         *
         * @param array  $buttons   Third-row list of buttons.
         * @param string $editor_id Unique editor identifier, e.g. 'content'.
         */
        $mce_buttons_3 = apply_filters( 'nimble_mce_buttons_3', array(), $editor_id );

        /**
         * Filters the fourth-row list of TinyMCE buttons (Visual tab).
         *
         * @since 2.5.0
         *
         * @param array  $buttons   Fourth-row list of buttons.
         * @param string $editor_id Unique editor identifier, e.g. 'content'.
         */
        $mce_buttons_4 = apply_filters( 'nimble_mce_buttons_4', array(), $editor_id );
      }

      $body_class = $editor_id;

      if ( $post = get_post() ) {
        $body_class .= ' post-type-' . sanitize_html_class( $post->post_type ) . ' post-status-' . sanitize_html_class( $post->post_status );

        if ( post_type_supports( $post->post_type, 'post-formats' ) ) {
          $post_format = get_post_format( $post );
          if ( $post_format && !is_wp_error( $post_format ) ) {
            $body_class .= ' post-format-' . sanitize_html_class( $post_format );
          } else {
            $body_class .= ' post-format-standard';
          }
        }

        $page_template = get_page_template_slug( $post );

        if ( $page_template !== false ) {
          $page_template = empty( $page_template ) ? 'default' : str_replace( '.', '-', basename( $page_template, '.php' ) );
          $body_class   .= ' page-template-' . sanitize_html_class( $page_template );
        }
      }

      $body_class .= ' locale-' . sanitize_html_class( strtolower( str_replace( '_', '-', get_user_locale() ) ) );

      if ( !empty( $set['tinymce']['body_class'] ) ) {
        $body_class .= ' ' . $set['tinymce']['body_class'];
        unset( $set['tinymce']['body_class'] );
      }

      $mceInit = array(
        'selector'          => "#$editor_id",
        'wpautop'           => (bool) $set['wpautop'],
        'indent'            => !$set['wpautop'],
        'toolbar1'          => implode( ',', $mce_buttons ),
        'toolbar2'          => implode( ',', $mce_buttons_2 ),
        'toolbar3'          => implode( ',', $mce_buttons_3 ),
        'toolbar4'          => implode( ',', $mce_buttons_4 ),
        'tabfocus_elements' => $set['tabfocus_elements'],
        'body_class'        => $body_class,
      );

      // Merge with the first part of the init array
      $mceInit = array_merge( self::$first_init, $mceInit );

      if ( is_array( $set['tinymce'] ) ) {
        $mceInit = array_merge( $mceInit, $set['tinymce'] );
      }

      /*
       * For people who really REALLY know what they're doing with TinyMCE
       * You can modify $mceInit to add, remove, change elements of the config
       * before tinyMCE.init. Setting "valid_elements", "invalid_elements"
       * and "extended_valid_elements" can be done through this filter. Best
       * is to use the default cleanup by not specifying valid_elements,
       * as TinyMCE checks against the full set of HTML 5.0 elements and attributes.
       */
      if ( $set['teeny'] ) {

        /**
         * Filters the teenyMCE config before init.
         *
         * @since 2.7.0
         *
         * @param array  $mceInit   An array with teenyMCE config.
         * @param string $editor_id Unique editor identifier, e.g. 'content'.
         */
        $mceInit = apply_filters( 'teeny_mce_before_init', $mceInit, $editor_id );
      } else {

        /**
         * Filters the TinyMCE config before init.
         *
         * @since 2.5.0
         *
         * @param array  $mceInit   An array with TinyMCE config.
         * @param string $editor_id Unique editor identifier, e.g. 'content'.
         */
        $mceInit = apply_filters( 'tiny_mce_before_init', $mceInit, $editor_id );
      }

      if ( empty( $mceInit['toolbar3'] ) && !empty( $mceInit['toolbar4'] ) ) {
        $mceInit['toolbar3'] = $mceInit['toolbar4'];
        $mceInit['toolbar4'] = '';
      }

      self::$mce_settings[ $editor_id ] = $mceInit;
    } // end if self::$this_tinymce
  }

  /**
   * @param array $init
   * @return string
   */
  private static function _parse_init( $init ) {
    $options = '';

    foreach ( $init as $key => $value ) {
      if ( is_bool( $value ) ) {
        $val      = $value ? 'true' : 'false';
        $options .= $key . ':' . $val . ',';
        continue;
      } elseif ( !empty( $value ) && is_string( $value ) && (
        ( '{' == $value[0] && '}' == $value[ strlen( $value ) - 1 ] ) ||
        ( '[' == $value[0] && ']' == $value[ strlen( $value ) - 1 ] ) ||
        preg_match( '/^\(?function ?\(/', $value ) ) ) {

        $options .= $key . ':' . $value . ',';
        continue;
      }
      $options .= $key . ':"' . $value . '",';
    }

    return '{' . trim( $options, ' ,' ) . '}';
  }

  /**
   *
   * @static
   *
   * @param bool $default_scripts Optional. Whether default scripts should be enqueued. Default false.
   */
  public static function enqueue_scripts( $default_scripts = false ) {
    if ( $default_scripts || self::$has_tinymce ) {
      wp_enqueue_script( 'editor' );
    }

    if ( $default_scripts || self::$has_quicktags ) {
      wp_enqueue_script( 'quicktags' );
      wp_enqueue_style( 'buttons' );
    }

    if ( $default_scripts || in_array( 'wplink', self::$plugins, true ) || in_array( 'link', self::$qt_buttons, true ) ) {
      wp_enqueue_script( 'wplink' );
      wp_enqueue_script( 'jquery-ui-autocomplete' );
    }

    if ( self::$old_dfw_compat ) {
      wp_enqueue_script( 'wp-fullscreen-stub' );
    }

    if ( self::$has_medialib ) {
      add_thickbox();
      wp_enqueue_script( 'media-upload' );
      wp_enqueue_script( 'wp-embed' );
    } elseif ( $default_scripts ) {
      wp_enqueue_script( 'media-upload' );
    }

    /**
     * Fires when scripts and styles are enqueued for the editor.
     *
     * @since 3.9.0
     *
     * @param array $to_load An array containing boolean values whether TinyMCE
     *                       and Quicktags are being loaded.
     */
    do_action(
      'wp_enqueue_editor',
      array(
        'tinymce'   => ( $default_scripts || self::$has_tinymce ),
        'quicktags' => ( $default_scripts || self::$has_quicktags ),
      )
    );
  }

  /**
   * Enqueue all editor scripts.
   * For use when the editor is going to be initialized after page load.
   *
   * @since 4.8.0
   */
  public static function enqueue_default_editor() {
    // We are past the point where scripts can be enqueued properly.
    if ( did_action( 'wp_enqueue_editor' ) ) {
      return;
    }

    self::enqueue_scripts( true );

    // Also add wp-includes/css/editor.css
    wp_enqueue_style( 'editor-buttons' );

    add_action( 'customize_controls_print_footer_scripts', array( __CLASS__, 'force_uncompressed_tinymce' ), 1 );
    add_action( 'customize_controls_print_scripts', array( __CLASS__, 'print_default_editor_scripts' ), 45 );

  }

  /**
   * Print (output) all editor scripts and default settings.
   * For use when the editor is going to be initialized after page load.
   *
   * @since 4.8.0
   */
  public static function print_default_editor_scripts() {
    $user_can_richedit = user_can_richedit();

    if ( $user_can_richedit ) {
      $settings = self::default_settings();

      $settings['toolbar1']    = 'bold,italic,bullist,numlist,link';
      $settings['wpautop']     = false;
      $settings['indent']      = true;
      $settings['elementpath'] = false;

      if ( is_rtl() ) {
        $settings['directionality'] = 'rtl';
      }

      // In production all plugins are loaded (they are in wp-editor.js.gz).
      // The 'wpview', 'wpdialogs', and 'media' TinyMCE plugins are not initialized by default.
      // Can be added from js by using the 'wp-before-tinymce-init' event.
      $settings['plugins'] = implode(
        ',',
        array(
          'charmap',
          'colorpicker',
          'hr',
          'lists',
          'paste',
          'tabfocus',
          'textcolor',
          'fullscreen',
          'wordpress',
          'wpautoresize',
          'wpeditimage',
          'wpemoji',
          'wpgallery',
          'wplink',
          'wptextpattern',
        )
      );

      $settings = self::_parse_init( $settings );
    } else {
      $settings = '{}';
    }
    ob_start();
    ?>
    window.wp = window.wp || {};
    window.wp.editor = window.wp.editor || {};
    window.wp.editor.getDefaultSettings = function() {
      return {
        tinymce: <?php echo wp_kses_post($settings); ?>,
        quicktags: {
          buttons: 'strong,em,link,ul,ol,li,code'
        }
      };
    };

    <?php

    if ( $user_can_richedit ) {
      $suffix  = SCRIPT_DEBUG ? '' : '.min';
      $baseurl = self::get_baseurl();
      ?>
      var nimbleTinyMCEPreInit = {
        baseURL: "<?php echo esc_url($baseurl); ?>",
        suffix: "<?php echo esc_attr($suffix); ?>",
        mceInit: {},
        qtInit: {},
        load_ext: function(url,lang){var sl=tinymce.ScriptLoader;sl.markDone(url+'/langs/'+lang+'.js');sl.markDone(url+'/langs/'+lang+'_dlg.js');}
      };
      <?php
    }
    ?>
    <?php
    $editor_script_three = ob_get_clean();
    wp_register_script( 'nb_print_editor_js_three', '');
    wp_enqueue_script( 'nb_print_editor_js_three' );
    wp_add_inline_script( 'nb_print_editor_js_three', $editor_script_three );

    if ( $user_can_richedit ) {
      self::print_tinymce_scripts();
    }

    /**
     * Fires when the editor scripts are loaded for later initialization,
     * after all scripts and settings are printed.
     *
     * @since 4.8.0
     */
    do_action( 'print_default_editor_scripts' );

    self::wp_link_dialog();
  }

  public static function get_mce_locale() {
    if ( empty( self::$mce_locale ) ) {
      $mce_locale       = get_user_locale();
      self::$mce_locale = empty( $mce_locale ) ? 'en' : strtolower( substr( $mce_locale, 0, 2 ) ); // ISO 639-1
    }

    return self::$mce_locale;
  }

  public static function get_baseurl() {
    if ( empty( self::$baseurl ) ) {
      self::$baseurl = includes_url( 'js/tinymce' );
    }

    return self::$baseurl;
  }

  /**
   * Returns the default TinyMCE settings.
   * Doesn't include plugins, buttons, editor selector.
   *
   * @global string $tinymce_version
   *
   * @return array
   */
  private static function default_settings() {
    global $tinymce_version;

    $shortcut_labels = array();

    foreach ( self::get_translation() as $name => $value ) {
      if ( is_array( $value ) ) {
        $shortcut_labels[ $name ] = $value[1];
      }
    }

    $settings = array(
      'theme'                        => 'modern',
      'skin'                         => 'lightgray',
      'language'                     => self::get_mce_locale(),
      'formats'                      => '{' .
        'alignleft: [' .
          '{selector: "p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li", styles: {textAlign:"left"}},' .
          '{selector: "img,table,dl.wp-caption", classes: "alignleft"}' .
        '],' .
        'aligncenter: [' .
          '{selector: "p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li", styles: {textAlign:"center"}},' .
          '{selector: "img,table,dl.wp-caption", classes: "aligncenter"}' .
        '],' .
        'alignright: [' .
          '{selector: "p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li", styles: {textAlign:"right"}},' .
          '{selector: "img,table,dl.wp-caption", classes: "alignright"}' .
        '],' .
        'strikethrough: {inline: "del"}' .
      '}',
      'relative_urls'                => false,
      'remove_script_host'           => false,
      'convert_urls'                 => false,
      'browser_spellcheck'           => true,
      'fix_list_elements'            => true,
      'entities'                     => '38,amp,60,lt,62,gt',
      'entity_encoding'              => 'raw',
      'keep_styles'                  => false,
      'cache_suffix'                 => 'wp-mce-' . $tinymce_version,
      'resize'                       => 'vertical',
      'menubar'                      => false,
      'branding'                     => false,

      // Limit the preview styles in the menu/toolbar
      'preview_styles'               => 'font-family font-size font-weight font-style text-decoration text-transform',

      'end_container_on_empty_block' => true,
      'wpeditimage_html5_captions'   => true,
      'wp_lang_attr'                 => get_bloginfo( 'language' ),
      'wp_keep_scroll_position'      => false,
      'wp_shortcut_labels'           => wp_json_encode( $shortcut_labels ),
    );

    $suffix  = SCRIPT_DEBUG ? '' : '.min';
    $version = 'ver=' . get_bloginfo( 'version' );

    // Default stylesheets
    $settings['content_css'] = includes_url( "css/dashicons$suffix.css?$version" ) . ',' .
      includes_url( "js/tinymce/skins/wordpress/wp-content.css?$version" );

    return $settings;
  }

  private static function get_translation() {
    if ( empty( self::$translation ) ) {
      self::$translation = array(
        // Default TinyMCE strings
        'New document'                         => __( 'New document' ),
        'Formats'                              => _x( 'Formats', 'TinyMCE' ),

        'Headings'                             => _x( 'Headings', 'TinyMCE' ),
        'Heading 1'                            => array( __( 'Heading 1' ), 'access1' ),
        'Heading 2'                            => array( __( 'Heading 2' ), 'access2' ),
        'Heading 3'                            => array( __( 'Heading 3' ), 'access3' ),
        'Heading 4'                            => array( __( 'Heading 4' ), 'access4' ),
        'Heading 5'                            => array( __( 'Heading 5' ), 'access5' ),
        'Heading 6'                            => array( __( 'Heading 6' ), 'access6' ),

        /* translators: block tags */
        'Blocks'                               => _x( 'Blocks', 'TinyMCE' ),
        'Paragraph'                            => array( __( 'Paragraph' ), 'access7' ),
        'Blockquote'                           => array( __( 'Blockquote' ), 'accessQ' ),
        'Div'                                  => _x( 'Div', 'HTML tag' ),
        'Pre'                                  => _x( 'Pre', 'HTML tag' ),
        'Preformatted'                         => _x( 'Preformatted', 'HTML tag' ),
        'Address'                              => _x( 'Address', 'HTML tag' ),

        'Inline'                               => _x( 'Inline', 'HTML elements' ),
        'Underline'                            => array( __( 'Underline' ), 'metaU' ),
        'Strikethrough'                        => array( __( 'Strikethrough' ), 'accessD' ),
        'Subscript'                            => __( 'Subscript' ),
        'Superscript'                          => __( 'Superscript' ),
        'Clear formatting'                     => __( 'Clear formatting' ),
        'Bold'                                 => array( __( 'Bold' ), 'metaB' ),
        'Italic'                               => array( __( 'Italic' ), 'metaI' ),
        'Code'                                 => array( __( 'Code' ), 'accessX' ),
        'Source code'                          => __( 'Source code' ),
        'Font Family'                          => __( 'Font Family' ),
        'Font Sizes'                           => __( 'Font Sizes' ),

        'Align center'                         => array( __( 'Align center' ), 'accessC' ),
        'Align right'                          => array( __( 'Align right' ), 'accessR' ),
        'Align left'                           => array( __( 'Align left' ), 'accessL' ),
        'Justify'                              => array( __( 'Justify' ), 'accessJ' ),
        'Increase indent'                      => __( 'Increase indent' ),
        'Decrease indent'                      => __( 'Decrease indent' ),

        'Cut'                                  => array( __( 'Cut' ), 'metaX' ),
        'Copy'                                 => array( __( 'Copy' ), 'metaC' ),
        'Paste'                                => array( __( 'Paste' ), 'metaV' ),
        'Select all'                           => array( __( 'Select all' ), 'metaA' ),
        'Undo'                                 => array( __( 'Undo' ), 'metaZ' ),
        'Redo'                                 => array( __( 'Redo' ), 'metaY' ),

        'Ok'                                   => __( 'OK' ),
        'Cancel'                               => __( 'Cancel' ),
        'Close'                                => __( 'Close' ),
        'Visual aids'                          => __( 'Visual aids' ),

        'Bullet list'                          => array( __( 'Bulleted list' ), 'accessU' ),
        'Numbered list'                        => array( __( 'Numbered list' ), 'accessO' ),
        'Square'                               => _x( 'Square', 'list style' ),
        'Default'                              => _x( 'Default', 'list style' ),
        'Circle'                               => _x( 'Circle', 'list style' ),
        'Disc'                                 => _x( 'Disc', 'list style' ),
        'Lower Greek'                          => _x( 'Lower Greek', 'list style' ),
        'Lower Alpha'                          => _x( 'Lower Alpha', 'list style' ),
        'Upper Alpha'                          => _x( 'Upper Alpha', 'list style' ),
        'Upper Roman'                          => _x( 'Upper Roman', 'list style' ),
        'Lower Roman'                          => _x( 'Lower Roman', 'list style' ),

        // Anchor plugin
        'Name'                                 => _x( 'Name', 'Name of link anchor (TinyMCE)' ),
        'Anchor'                               => _x( 'Anchor', 'Link anchor (TinyMCE)' ),
        'Anchors'                              => _x( 'Anchors', 'Link anchors (TinyMCE)' ),
        'Id should start with a letter, followed only by letters, numbers, dashes, dots, colons or underscores.' =>
          __( 'Id should start with a letter, followed only by letters, numbers, dashes, dots, colons or underscores.' ),
        'Id'                                   => _x( 'Id', 'Id for link anchor (TinyMCE)' ),

        // Fullpage plugin
        'Document properties'                  => __( 'Document properties' ),
        'Robots'                               => __( 'Robots' ),
        'Title'                                => __( 'Title' ),
        'Keywords'                             => __( 'Keywords' ),
        'Encoding'                             => __( 'Encoding' ),
        'Description'                          => __( 'Description' ),
        'Author'                               => __( 'Author' ),

        // Media, image plugins
        'Image'                                => __( 'Image' ),
        'Insert/edit image'                    => array( __( 'Insert/edit image' ), 'accessM' ),
        'General'                              => __( 'General' ),
        'Advanced'                             => __( 'Advanced' ),
        'Source'                               => __( 'Source' ),
        'Border'                               => __( 'Border' ),
        'Constrain proportions'                => __( 'Constrain proportions' ),
        'Vertical space'                       => __( 'Vertical space' ),
        'Image description'                    => __( 'Image description' ),
        'Style'                                => __( 'Style' ),
        'Dimensions'                           => __( 'Dimensions' ),
        'Insert image'                         => __( 'Insert image' ),
        'Date/time'                            => __( 'Date/time' ),
        'Insert date/time'                     => __( 'Insert date/time' ),
        'Table of Contents'                    => __( 'Table of Contents' ),
        'Insert/Edit code sample'              => __( 'Insert/edit code sample' ),
        'Language'                             => __( 'Language' ),
        'Media'                                => __( 'Media' ),
        'Insert/edit media'                    => __( 'Insert/edit media' ),
        'Poster'                               => __( 'Poster' ),
        'Alternative source'                   => __( 'Alternative source' ),
        'Paste your embed code below:'         => __( 'Paste your embed code below:' ),
        'Insert video'                         => __( 'Insert video' ),
        'Embed'                                => __( 'Embed' ),

        // Each of these have a corresponding plugin
        'Special character'                    => __( 'Special character' ),
        'Right to left'                        => _x( 'Right to left', 'editor button' ),
        'Left to right'                        => _x( 'Left to right', 'editor button' ),
        'Emoticons'                            => __( 'Emoticons' ),
        'Nonbreaking space'                    => __( 'Nonbreaking space' ),
        'Page break'                           => __( 'Page break' ),
        'Paste as text'                        => __( 'Paste as text' ),
        'Preview'                              => __( 'Preview' ),
        'Print'                                => __( 'Print' ),
        'Save'                                 => __( 'Save' ),
        'Fullscreen'                           => __( 'Fullscreen' ),
        'Horizontal line'                      => __( 'Horizontal line' ),
        'Horizontal space'                     => __( 'Horizontal space' ),
        'Restore last draft'                   => __( 'Restore last draft' ),
        'Insert/edit link'                     => array( __( 'Insert/edit link' ), 'metaK' ),
        'Remove link'                          => array( __( 'Remove link' ), 'accessS' ),

        // Link plugin
        'Link'                                 => __( 'Link' ),
        'Insert link'                          => __( 'Insert link' ),
        'Insert/edit link'                     => __( 'Insert/edit link' ),
        'Target'                               => __( 'Target' ),
        'New window'                           => __( 'New window' ),
        'Text to display'                      => __( 'Text to display' ),
        'Url'                                  => __( 'URL' ),
        'The URL you entered seems to be an email address. Do you want to add the required mailto: prefix?' =>
          __( 'The URL you entered seems to be an email address. Do you want to add the required mailto: prefix?' ),
        'The URL you entered seems to be an external link. Do you want to add the required http:// prefix?' =>
          __( 'The URL you entered seems to be an external link. Do you want to add the required http:// prefix?' ),

        'Color'                                => __( 'Color' ),
        'Custom color'                         => __( 'Custom color' ),
        'Custom...'                            => _x( 'Custom...', 'label for custom color' ), // no ellipsis
        'No color'                             => __( 'No color' ),
        'R'                                    => _x( 'R', 'Short for red in RGB' ),
        'G'                                    => _x( 'G', 'Short for green in RGB' ),
        'B'                                    => _x( 'B', 'Short for blue in RGB' ),

        // Spelling, search/replace plugins
        'Could not find the specified string.' => __( 'Could not find the specified string.' ),
        'Replace'                              => _x( 'Replace', 'find/replace' ),
        'Next'                                 => _x( 'Next', 'find/replace' ),
        /* translators: previous */
        'Prev'                                 => _x( 'Prev', 'find/replace' ),
        'Whole words'                          => _x( 'Whole words', 'find/replace' ),
        'Find and replace'                     => __( 'Find and replace' ),
        'Replace with'                         => _x( 'Replace with', 'find/replace' ),
        'Find'                                 => _x( 'Find', 'find/replace' ),
        'Replace all'                          => _x( 'Replace all', 'find/replace' ),
        'Match case'                           => __( 'Match case' ),
        'Spellcheck'                           => __( 'Check Spelling' ),
        'Finish'                               => _x( 'Finish', 'spellcheck' ),
        'Ignore all'                           => _x( 'Ignore all', 'spellcheck' ),
        'Ignore'                               => _x( 'Ignore', 'spellcheck' ),
        'Add to Dictionary'                    => __( 'Add to Dictionary' ),

        // TinyMCE tables
        'Insert table'                         => __( 'Insert table' ),
        'Delete table'                         => __( 'Delete table' ),
        'Table properties'                     => __( 'Table properties' ),
        'Row properties'                       => __( 'Table row properties' ),
        'Cell properties'                      => __( 'Table cell properties' ),
        'Border color'                         => __( 'Border color' ),

        'Row'                                  => __( 'Row' ),
        'Rows'                                 => __( 'Rows' ),
        'Column'                               => _x( 'Column', 'table column' ),
        'Cols'                                 => _x( 'Cols', 'table columns' ),
        'Cell'                                 => _x( 'Cell', 'table cell' ),
        'Header cell'                          => __( 'Header cell' ),
        'Header'                               => _x( 'Header', 'table header' ),
        'Body'                                 => _x( 'Body', 'table body' ),
        'Footer'                               => _x( 'Footer', 'table footer' ),

        'Insert row before'                    => __( 'Insert row before' ),
        'Insert row after'                     => __( 'Insert row after' ),
        'Insert column before'                 => __( 'Insert column before' ),
        'Insert column after'                  => __( 'Insert column after' ),
        'Paste row before'                     => __( 'Paste table row before' ),
        'Paste row after'                      => __( 'Paste table row after' ),
        'Delete row'                           => __( 'Delete row' ),
        'Delete column'                        => __( 'Delete column' ),
        'Cut row'                              => __( 'Cut table row' ),
        'Copy row'                             => __( 'Copy table row' ),
        'Merge cells'                          => __( 'Merge table cells' ),
        'Split cell'                           => __( 'Split table cell' ),

        'Height'                               => __( 'Height' ),
        'Width'                                => __( 'Width' ),
        'Caption'                              => __( 'Caption' ),
        'Alignment'                            => __( 'Alignment' ),
        'H Align'                              => _x( 'H Align', 'horizontal table cell alignment' ),
        'Left'                                 => __( 'Left' ),
        'Center'                               => __( 'Center' ),
        'Right'                                => __( 'Right' ),
        'None'                                 => _x( 'None', 'table cell alignment attribute' ),
        'V Align'                              => _x( 'V Align', 'vertical table cell alignment' ),
        'Top'                                  => __( 'Top' ),
        'Middle'                               => __( 'Middle' ),
        'Bottom'                               => __( 'Bottom' ),

        'Row group'                            => __( 'Row group' ),
        'Column group'                         => __( 'Column group' ),
        'Row type'                             => __( 'Row type' ),
        'Cell type'                            => __( 'Cell type' ),
        'Cell padding'                         => __( 'Cell padding' ),
        'Cell spacing'                         => __( 'Cell spacing' ),
        'Scope'                                => _x( 'Scope', 'table cell scope attribute' ),

        'Insert template'                      => _x( 'Insert template', 'TinyMCE' ),
        'Templates'                            => _x( 'Templates', 'TinyMCE' ),

        'Background color'                     => __( 'Background color' ),
        'Text color'                           => __( 'Text color' ),
        'Show blocks'                          => _x( 'Show blocks', 'editor button' ),
        'Show invisible characters'            => __( 'Show invisible characters' ),

        /* translators: word count */
        'Words: {0}'                           => sprintf( __( 'Words: %s' ), '{0}' ),
        'Paste is now in plain text mode. Contents will now be pasted as plain text until you toggle this option off.' =>
          __( 'Paste is now in plain text mode. Contents will now be pasted as plain text until you toggle this option off.' ) . "\n\n" .
          __( 'If you&#8217;re looking to paste rich content from Microsoft Word, try turning this option off. The editor will clean up text pasted from Word automatically.' ),
        'Rich Text Area. Press ALT-F9 for menu. Press ALT-F10 for toolbar. Press ALT-0 for help' =>
          __( 'Rich Text Area. Press Alt-Shift-H for help.' ),
        'Rich Text Area. Press Control-Option-H for help.' => __( 'Rich Text Area. Press Control-Option-H for help.' ),
        'You have unsaved changes are you sure you want to navigate away?' =>
          __( 'The changes you made will be lost if you navigate away from this page.' ),
        'Your browser doesn\'t support direct access to the clipboard. Please use the Ctrl+X/C/V keyboard shortcuts instead.' =>
          __( 'Your browser does not support direct access to the clipboard. Please use keyboard shortcuts or your browser&#8217;s edit menu instead.' ),

        // TinyMCE menus
        'Insert'                               => _x( 'Insert', 'TinyMCE menu' ),
        'File'                                 => _x( 'File', 'TinyMCE menu' ),
        'Edit'                                 => _x( 'Edit', 'TinyMCE menu' ),
        'Tools'                                => _x( 'Tools', 'TinyMCE menu' ),
        'View'                                 => _x( 'View', 'TinyMCE menu' ),
        'Table'                                => _x( 'Table', 'TinyMCE menu' ),
        'Format'                               => _x( 'Format', 'TinyMCE menu' ),

        // WordPress strings
        'Toolbar Toggle'                       => array( __( 'Toolbar Toggle' ), 'accessZ' ),
        'Insert Read More tag'                 => array( __( 'Insert Read More tag' ), 'accessT' ),
        'Insert Page Break tag'                => array( __( 'Insert Page Break tag' ), 'accessP' ),
        'Read more...'                         => __( 'Read more...' ), // Title on the placeholder inside the editor (no ellipsis)
        'Distraction-free writing mode'        => array( __( 'Distraction-free writing mode' ), 'accessW' ),
        'No alignment'                         => __( 'No alignment' ), // Tooltip for the 'alignnone' button in the image toolbar
        'Remove'                               => __( 'Remove' ), // Tooltip for the 'remove' button in the image toolbar
        'Edit|button'                          => __( 'Edit' ), // Tooltip for the 'edit' button in the image toolbar
        'Paste URL or type to search'          => __( 'Paste URL or type to search' ), // Placeholder for the inline link dialog
        'Apply'                                => __( 'Apply' ), // Tooltip for the 'apply' button in the inline link dialog
        'Link options'                         => __( 'Link options' ), // Tooltip for the 'link options' button in the inline link dialog
        'Visual'                               => _x( 'Visual', 'Name for the Visual editor tab' ), // Editor switch tab label
        'Text'                                 => _x( 'Text', 'Name for the Text editor tab (formerly HTML)' ), // Editor switch tab label
        'Add Media'                            => array( __( 'Add Media' ), 'accessM' ), // Tooltip for the 'Add Media' button in the Block Editor Classic block

        // Shortcuts help modal
        'Keyboard Shortcuts'                   => array( __( 'Keyboard Shortcuts' ), 'accessH' ),
        'Classic Block Keyboard Shortcuts'     => __( 'Classic Block Keyboard Shortcuts' ),
        'Default shortcuts,'                   => __( 'Default shortcuts,' ),
        'Additional shortcuts,'                => __( 'Additional shortcuts,' ),
        'Focus shortcuts:'                     => __( 'Focus shortcuts:' ),
        'Inline toolbar (when an image, link or preview is selected)' => __( 'Inline toolbar (when an image, link or preview is selected)' ),
        'Editor menu (when enabled)'           => __( 'Editor menu (when enabled)' ),
        'Editor toolbar'                       => __( 'Editor toolbar' ),
        'Elements path'                        => __( 'Elements path' ),
        'Ctrl + Alt + letter:'                 => __( 'Ctrl + Alt + letter:' ),
        'Shift + Alt + letter:'                => __( 'Shift + Alt + letter:' ),
        'Cmd + letter:'                        => __( 'Cmd + letter:' ),
        'Ctrl + letter:'                       => __( 'Ctrl + letter:' ),
        'Letter'                               => __( 'Letter' ),
        'Action'                               => __( 'Action' ),
        'Warning: the link has been inserted but may have errors. Please test it.' => __( 'Warning: the link has been inserted but may have errors. Please test it.' ),
        'To move focus to other buttons use Tab or the arrow keys. To return focus to the editor press Escape or use one of the buttons.' =>
          __( 'To move focus to other buttons use Tab or the arrow keys. To return focus to the editor press Escape or use one of the buttons.' ),
        'When starting a new paragraph with one of these formatting shortcuts followed by a space, the formatting will be applied automatically. Press Backspace or Escape to undo.' =>
          __( 'When starting a new paragraph with one of these formatting shortcuts followed by a space, the formatting will be applied automatically. Press Backspace or Escape to undo.' ),
        'The following formatting shortcuts are replaced when pressing Enter. Press Escape or the Undo button to undo.' =>
          __( 'The following formatting shortcuts are replaced when pressing Enter. Press Escape or the Undo button to undo.' ),
        'The next group of formatting shortcuts are applied as you type or when you insert them around plain text in the same paragraph. Press Escape or the Undo button to undo.' =>
          __( 'The next group of formatting shortcuts are applied as you type or when you insert them around plain text in the same paragraph. Press Escape or the Undo button to undo.' ),
      );
    }

    /*
    Imagetools plugin (not included):
      'Edit image' => __( 'Edit image' ),
      'Image options' => __( 'Image options' ),
      'Back' => __( 'Back' ),
      'Invert' => __( 'Invert' ),
      'Flip horizontally' => __( 'Flip horizontally' ),
      'Flip vertically' => __( 'Flip vertically' ),
      'Crop' => __( 'Crop' ),
      'Orientation' => __( 'Orientation' ),
      'Resize' => __( 'Resize' ),
      'Rotate clockwise' => __( 'Rotate clockwise' ),
      'Rotate counterclockwise' => __( 'Rotate counterclockwise' ),
      'Sharpen' => __( 'Sharpen' ),
      'Brightness' => __( 'Brightness' ),
      'Color levels' => __( 'Color levels' ),
      'Contrast' => __( 'Contrast' ),
      'Gamma' => __( 'Gamma' ),
      'Zoom in' => __( 'Zoom in' ),
      'Zoom out' => __( 'Zoom out' ),
    */

    return self::$translation;
  }

  /**
   * Translates the default TinyMCE strings and returns them as JSON encoded object ready to be loaded with tinymce.addI18n(),
   * or as JS snippet that should run after tinymce.js is loaded.
   *
   * @param string $mce_locale The locale used for the editor.
   * @param bool $json_only optional Whether to include the JavaScript calls to tinymce.addI18n() and tinymce.ScriptLoader.markDone().
   * @return string Translation object, JSON encoded.
   */
  public static function wp_mce_translation( $mce_locale = '', $json_only = false ) {
    if ( !$mce_locale ) {
      $mce_locale = self::get_mce_locale();
    }

    $mce_translation = self::get_translation();

    foreach ( $mce_translation as $name => $value ) {
      if ( is_array( $value ) ) {
        $mce_translation[ $name ] = $value[0];
      }
    }

    /**
     * Filters translated strings prepared for TinyMCE.
     *
     * @since 3.9.0
     *
     * @param array  $mce_translation Key/value pairs of strings.
     * @param string $mce_locale      Locale.
     */
    $mce_translation = apply_filters( 'wp_mce_translation', $mce_translation, $mce_locale );

    foreach ( $mce_translation as $key => $value ) {
      // Remove strings that are not translated.
      if ( $key === $value ) {
        unset( $mce_translation[ $key ] );
        continue;
      }

      if ( false !== strpos( $value, '&' ) ) {
        $mce_translation[ $key ] = html_entity_decode( $value, ENT_QUOTES, 'UTF-8' );
      }
    }

    // Set direction
    if ( is_rtl() ) {
      $mce_translation['_dir'] = 'rtl';
    }

    if ( $json_only ) {
      return wp_json_encode( $mce_translation );
    }

    $baseurl = self::get_baseurl();

    return "tinymce.addI18n( '$mce_locale', " . wp_json_encode( $mce_translation ) . ");\n" .
      "tinymce.ScriptLoader.markDone( '$baseurl/langs/$mce_locale.js' );\n";
  }

  /**
   * Force uncompressed TinyMCE when a custom theme has been defined.
   *
   * The compressed TinyMCE file cannot deal with custom themes, so this makes
   * sure that we use the uncompressed TinyMCE file if a theme is defined.
   * Even if we are on a production environment.
   */
  public static function force_uncompressed_tinymce() {
    $has_custom_theme = false;
    foreach ( self::$mce_settings as $init ) {
      if ( !empty( $init['theme_url'] ) ) {
        $has_custom_theme = true;
        break;
      }
    }

    if ( !$has_custom_theme ) {
      return;
    }

    $wp_scripts = wp_scripts();

    $wp_scripts->remove( 'wp-tinymce' );
    wp_register_tinymce_scripts( $wp_scripts, true );
  }

  /**
   * Print (output) the main TinyMCE scripts.
   *
   * @since 4.8.0
   *
   * @global string $tinymce_version
   * @global bool   $concatenate_scripts
   * @global bool   $compress_scripts
   */
  public static function print_tinymce_scripts() {
    global $concatenate_scripts;

    if ( self::$tinymce_scripts_printed ) {
      return;
    }

    self::$tinymce_scripts_printed = true;

    if ( !isset( $concatenate_scripts ) ) {
      script_concat_settings();
    }
    
    wp_print_scripts( array( 'wp-tinymce' ) );
    $script = self::wp_mce_translation();
    wp_register_script( 'nb_print_tinymce_translations', '');
    wp_enqueue_script( 'nb_print_tinymce_translations' );
    wp_add_inline_script( 'nb_print_tinymce_translations', $script );
  }

  /**
   * Print (output) the TinyMCE configuration and initialization scripts.
   *
   * @global string $tinymce_version
   */
  public static function editor_js() {
    global $tinymce_version;

    $tmce_on = !empty( self::$mce_settings );
    $mceInit = $qtInit = '';

    if ( $tmce_on ) {
      foreach ( self::$mce_settings as $editor_id => $init ) {
        $options  = self::_parse_init( $init );
        $mceInit .= "'$editor_id':{$options},";
      }
      $mceInit = '{' . trim( $mceInit, ',' ) . '}';
    } else {
      $mceInit = '{}';
    }

    if ( !empty( self::$qt_settings ) ) {
      foreach ( self::$qt_settings as $editor_id => $init ) {
        $options = self::_parse_init( $init );
        $qtInit .= "'$editor_id':{$options},";
      }
      $qtInit = '{' . trim( $qtInit, ',' ) . '}';
    } else {
      $qtInit = '{}';
    }

    $ref = array(
      'plugins'  => implode( ',', self::$plugins ),
      'theme'    => 'modern',
      'language' => self::$mce_locale,
    );

    $suffix  = SCRIPT_DEBUG ? '' : '.min';
    $baseurl = self::get_baseurl();
    $version = 'ver=' . $tinymce_version;

    /**
     * Fires immediately before the TinyMCE settings are printed.
     *
     * @since 3.2.0
     *
     * @param array $mce_settings TinyMCE settings array.
     */
    do_action( 'before_wp_tiny_mce', self::$mce_settings );
    ob_start();
    ?>
    nimbleTinyMCEPreInit = {
      baseURL: "<?php echo esc_url($baseurl); ?>",
      suffix: "<?php echo esc_attr($suffix); ?>",
      <?php

      if ( self::$drag_drop_upload ) {
        echo 'dragDropUpload: true,';
      }
      ?>
      mceInit: <?php echo wp_kses_post($mceInit); ?>,
      qtInit: <?php echo wp_kses_post($qtInit); ?>,
      ref: <?php echo wp_kses_post(self::_parse_init( $ref )); ?>,
      load_ext: function(url,lang){var sl=tinymce.ScriptLoader;sl.markDone(url+'/langs/'+lang+'.js');sl.markDone(url+'/langs/'+lang+'_dlg.js');}
    };
    <?php

    $editor_script_one = ob_get_clean();
    wp_register_script( 'nb_print_editor_js', '');
    wp_enqueue_script( 'nb_print_editor_js' );
    wp_add_inline_script( 'nb_print_editor_js', $editor_script_one );


    if ( $tmce_on ) {
      self::print_tinymce_scripts();
      
      // @nikeo addon => not needed
      // if ( self::$ext_plugins ) {
      //   // Load the old-format English strings to prevent unsightly labels in old style popups
      //   echo "<script type='text/javascript' src='{$baseurl}/langs/wp-langs-en.js?$version'></script>\n";
      // }
    }

    /**
     * Fires after tinymce.js is loaded, but before any TinyMCE editor
     * instances are created.
     *
     * @since 3.9.0
     *
     * @param array $mce_settings TinyMCE settings array.
     */
    do_action( 'wp_tiny_mce_init', self::$mce_settings );

    ob_start();
    ?>
    <?php

    if ( self::$ext_plugins ) {
      echo wp_kses_post(self::$ext_plugins) . "\n";
    }

    if ( !is_admin() ) {
      echo 'var ajaxurl = "' . admin_url( 'admin-ajax.php', 'relative' ) . '";';
    }

    ?>
    ( function() {
      var init, id, $wrap;

      if ( typeof tinymce !== 'undefined' ) {
        if ( tinymce.Env.ie && tinymce.Env.ie < 11 ) {
          tinymce.$( '.wp-editor-wrap ' ).removeClass( 'tmce-active' ).addClass( 'html-active' );
          return;
        }

        for ( id in nimbleTinyMCEPreInit.mceInit ) {
          init = nimbleTinyMCEPreInit.mceInit[id];
          $wrap = tinymce.$( '#wp-' + id + '-wrap' );

          if ( ( $wrap.hasClass( 'tmce-active' ) || !nimbleTinyMCEPreInit.qtInit.hasOwnProperty( id ) ) && !init.wp_skip_init ) {
            tinymce.init( init );
            if ( !window.wpActiveEditor ) {
              window.wpActiveEditor = id;//<= where is this used ?
            }
          }
        }
      }

      if ( typeof quicktags !== 'undefined' ) {
        for ( id in nimbleTinyMCEPreInit.qtInit ) {
          quicktags( nimbleTinyMCEPreInit.qtInit[id] );

          if ( !window.wpActiveEditor ) {
            window.wpActiveEditor = id;//<= where is this used ?
          }
        }
      }
    }());
    <?php
    $editor_script_two = ob_get_clean();
    wp_register_script( 'nb_print_editor_js_two', '');
    wp_enqueue_script( 'nb_print_editor_js_two' );
    wp_add_inline_script( 'nb_print_editor_js_two', $editor_script_two );

    if ( in_array( 'wplink', self::$plugins, true ) || in_array( 'link', self::$qt_buttons, true ) ) {
      self::wp_link_dialog();
    }

    /**
     * Fires after any core TinyMCE editor instances are created.
     *
     * @since 3.2.0
     *
     * @param array $mce_settings TinyMCE settings array.
     */
    do_action( 'after_wp_tiny_mce', self::$mce_settings );
  }

  /**
   * Outputs the HTML for distraction-free writing mode.
   *
   * @since 3.2.0
   * @deprecated 4.3.0
   */
  public static function wp_fullscreen_html() {
    _deprecated_function( __FUNCTION__, '4.3.0' );
  }

  /**
   * Performs post queries for internal linking.
   *
   * @since 3.1.0
   *
   * @param array $args Optional. Accepts 'pagenum' and 's' (search) arguments.
   * @return false|array Results.
   */
  public static function wp_link_query( $args = array() ) {
    $pts      = get_post_types( array( 'public' => true ), 'objects' );
    $pt_names = array_keys( $pts );

    $query = array(
      'post_type'              => $pt_names,
      'suppress_filters'       => true,
      'update_post_term_cache' => false,
      'update_post_meta_cache' => false,
      'post_status'            => 'publish',
      'posts_per_page'         => 20,
    );

    $args['pagenum'] = isset( $args['pagenum'] ) ? absint( $args['pagenum'] ) : 1;

    if ( isset( $args['s'] ) ) {
      $query['s'] = $args['s'];
    }

    $query['offset'] = $args['pagenum'] > 1 ? $query['posts_per_page'] * ( $args['pagenum'] - 1 ) : 0;

    /**
     * Filters the link query arguments.
     *
     * Allows modification of the link query arguments before querying.
     *
     * @see WP_Query for a full list of arguments
     *
     * @since 3.7.0
     *
     * @param array $query An array of WP_Query arguments.
     */
    $query = apply_filters( 'wp_link_query_args', $query );

    // Do main query.
    $get_posts = new WP_Query;
    $posts     = $get_posts->query( $query );

    // Build results.
    $results = array();
    foreach ( $posts as $post ) {
      if ( 'post' == $post->post_type ) {
        $info = mysql2date( __( 'Y/m/d' ), $post->post_date );
      } else {
        $info = $pts[ $post->post_type ]->labels->singular_name;
      }

      $results[] = array(
        'ID'        => $post->ID,
        'title'     => trim( esc_html( strip_tags( get_the_title( $post ) ) ) ),
        'permalink' => get_permalink( $post->ID ),
        'info'      => $info,
      );
    }

    /**
     * Filters the link query results.
     *
     * Allows modification of the returned link query results.
     *
     * @since 3.7.0
     *
     * @see 'wp_link_query_args' filter
     *
     * @param array $results {
     *     An associative array of query results.
     *
     *     @type array {
     *         @type int    $ID        Post ID.
     *         @type string $title     The trimmed, escaped post title.
     *         @type string $permalink Post permalink.
     *         @type string $info      A 'Y/m/d'-formatted date for 'post' post type,
     *                                 the 'singular_name' post type label otherwise.
     *     }
     * }
     * @param array $query  An array of WP_Query arguments.
     */
    $results = apply_filters( 'wp_link_query', $results, $query );

    return !empty( $results ) ? $results : false;
  }

  /**
   * Dialog for internal linking.
   *
   * @since 3.1.0
   */
  public static function wp_link_dialog() {
    // Run once
    if ( self::$link_dialog_printed ) {
      return;
    }

    self::$link_dialog_printed = true;

    // display: none is required here, see #WP27605
    ?>
    <div id="wp-link-backdrop" style="display: none"></div>
    <div id="wp-link-wrap" class="wp-core-ui" style="display: none" role="dialog" aria-labelledby="link-modal-title">
    <form id="wp-link" tabindex="-1">
    <?php wp_nonce_field( 'internal-linking', '_ajax_linking_nonce', false ); ?>
    <h1 id="link-modal-title"><?php _e( 'Insert/edit link' ); ?></h1>
    <button type="button" id="wp-link-close"><span class="screen-reader-text"><?php _e( 'Close' ); ?></span></button>
    <div id="link-selector">
      <div id="link-options">
        <p class="howto" id="wplink-enter-url"><?php _e( 'Enter the destination URL' ); ?></p>
        <div>
          <label><span><?php _e( 'URL' ); ?></span>
          <input id="wp-link-url" type="text" aria-describedby="wplink-enter-url" /></label>
        </div>
        <div class="wp-link-text-field">
          <label><span><?php _e( 'Link Text' ); ?></span>
          <input id="wp-link-text" type="text" /></label>
        </div>
        <div class="link-target">
          <label><span></span>
          <input type="checkbox" id="wp-link-target" /> <?php _e( 'Open link in a new tab' ); ?></label>
        </div>
      </div>
      <p class="howto" id="wplink-link-existing-content"><?php _e( 'Or link to existing content' ); ?></p>
      <div id="search-panel">
        <div class="link-search-wrapper">
          <label>
            <span class="search-label"><?php _e( 'Search' ); ?></span>
            <input type="search" id="wp-link-search" class="link-search-field" autocomplete="off" aria-describedby="wplink-link-existing-content" />
            <span class="spinner"></span>
          </label>
        </div>
        <div id="search-results" class="query-results" tabindex="0">
          <ul></ul>
          <div class="river-waiting">
            <span class="spinner"></span>
          </div>
        </div>
        <div id="most-recent-results" class="query-results" tabindex="0">
          <div class="query-notice" id="query-notice-message">
            <em class="query-notice-default"><?php _e( 'No search term specified. Showing recent items.' ); ?></em>
            <em class="query-notice-hint screen-reader-text"><?php _e( 'Search or use up and down arrow keys to select an item.' ); ?></em>
          </div>
          <ul></ul>
          <div class="river-waiting">
            <span class="spinner"></span>
          </div>
         </div>
       </div>
    </div>
    <div class="submitbox">
      <div id="wp-link-cancel">
        <button type="button" class="button"><?php _e( 'Cancel' ); ?></button>
      </div>
      <div id="wp-link-update">
        <input type="submit" value="<?php esc_attr_e( 'Add Link' ); ?>" class="button button-primary" id="wp-link-submit" name="wp-link-submit">
      </div>
    </div>
    </form>
    </div>
    <?php
  }
}

?><?php
////////////////////////////////////////////////////////////////
// GENERIC HELPER FIRED IN ALL AJAX CALLBACKS
// @param $params = array('check_nonce' => true )
function sek_do_ajax_pre_checks( $params = array() ) {
    $params = wp_parse_args( $params, array( 'check_nonce' => true ) );
    if ( $params['check_nonce'] ) {
        $action = 'save-customize_' . get_stylesheet();
        if ( !check_ajax_referer( $action, 'nonce', false ) ) {
             wp_send_json_error( array(
                'code' => 'invalid_nonce',
                'message' => __( __CLASS__ . '::' . __FUNCTION__ . ' => check_ajax_referer() failed.' ),
            ) );
        }
    }

    if ( !is_user_logged_in() ) {
        wp_send_json_error( __CLASS__ . '::' . __FUNCTION__ . ' => unauthenticated' );
    }
    if ( !current_user_can( 'customize' ) ) {
      wp_send_json_error( __CLASS__ . '::' . __FUNCTION__ . ' => user_cant_edit_theme_options');
    }
    if ( !current_user_can( 'customize' ) ) {
        status_header( 403 );
        wp_send_json_error( __CLASS__ . '::' . __FUNCTION__ . ' => customize_not_allowed' );
    } else if ( !isset( $_SERVER['REQUEST_METHOD'] ) || 'POST' !== $_SERVER['REQUEST_METHOD'] ) {
        status_header( 405 );
        wp_send_json_error( __CLASS__ . '::' . __FUNCTION__ . ' => bad_method' );
    }
}//sek_do_ajax_pre_checks()



// IMPORT IMG
add_action( 'wp_ajax_sek_import_attachment', '\Nimble\sek_ajax_import_attachment' );
// Fetches the list of revision for a given skope_id
add_action( 'wp_ajax_sek_get_revision_history', '\Nimble\sek_get_revision_history' );
// Fetches the revision for a given post id
add_action( 'wp_ajax_sek_get_single_revision', '\Nimble\sek_get_single_revision' );
// Fetches the category collection to generate the options for a select input
// @see api.czrInputMap.category_picker
add_action( 'wp_ajax_sek_get_post_categories', '\Nimble\sek_get_post_categories' );

// Fetches the code editor params to generate the options for a textarea input
// @see api.czrInputMap.code_editor
add_action( 'wp_ajax_sek_get_code_editor_params', '\Nimble\sek_get_code_editor_params' );

add_action( 'wp_ajax_sek_postpone_feedback', '\Nimble\sek_postpone_feedback_notification' );

// <AJAX TO FETCH INPUT COMPONENTS>
// this dynamic filter is declared on wp_ajax_ac_get_template in the czr_base_fmk
// It allows us to populate the server response with the relevant module html template
// $html = apply_filters( "ac_set_ajax_czr_tmpl___{$module_type}", '', $tmpl );
add_filter( "ac_set_ajax_czr_tmpl___fa_icon_picker_input", '\Nimble\sek_get_fa_icon_list_tmpl', 10, 3 );

// this dynamic filter is declared on wp_ajax_ac_get_template in the czr_base_fmk
// It allows us to populate the server response with the relevant module html template
// $html = apply_filters( "ac_set_ajax_czr_tmpl___{$module_type}", '', $tmpl );
add_filter( "ac_set_ajax_czr_tmpl___font_picker_input", '\Nimble\sek_get_font_list_tmpl', 10, 3 );
// </AJAX TO FETCH INPUT COMPONENTS>

/////////////////////////////////////////////////////////////////
// hook : wp_ajax_sek_import_attachment
function sek_ajax_import_attachment() {
    sek_do_ajax_pre_checks( array( 'check_nonce' => false ) );

    if ( !isset( $_POST['img_url'] ) || !is_string($_POST['img_url']) ) {
        wp_send_json_error( 'missing_or_invalid_img_url_when_importing_image');
    }

    $id = sek_sideload_img_and_return_attachment_id( sanitize_text_field($_POST['img_url']) );
    if ( is_wp_error( $id ) ) {
        wp_send_json_error( __CLASS__ . '::' . __FUNCTION__ . ' => problem when trying to wp_insert_attachment() for img : ' . sanitize_text_field($_POST['img_url']) . ' | SERVER ERROR => ' . json_encode( $id ) );
    } else {
        wp_send_json_success([
          'id' => $id,
          'url' => wp_get_attachment_url( $id )
        ]);
    }
}





////////////////////////////////////////////////////////////////
// REVISIONS
// Fired in __construct()
function sek_get_revision_history() {
    sek_do_ajax_pre_checks( array( 'check_nonce' => true ) );

    if ( !isset( $_POST['skope_id'] ) || empty( $_POST['skope_id'] ) ) {
        wp_send_json_error(  __CLASS__ . '::' . __FUNCTION__ . ' => missing skope_id' );
    }
    $rev_list = sek_get_revision_history_from_posts( sanitize_text_field($_POST['skope_id']) );
    wp_send_json_success( $rev_list );
}


function sek_get_single_revision() {
    sek_do_ajax_pre_checks( array( 'check_nonce' => true ) );

    if ( !isset( $_POST['revision_post_id'] ) || empty( $_POST['revision_post_id'] ) ) {
        wp_send_json_error(  __CLASS__ . '::' . __FUNCTION__ . ' => missing revision_post_id' );
    }
    $revision = sek_get_single_post_revision( sanitize_text_field($_POST['revision_post_id']) );
    wp_send_json_success( $revision );
}



////////////////////////////////////////////////////////////////
// POST CATEGORIES => to be used in the category picker select input
// Fired in __construct()
function sek_get_post_categories() {
    sek_do_ajax_pre_checks( array( 'check_nonce' => true ) );
    $raw_cats = get_categories();
    $raw_cats = is_array( $raw_cats ) ? $raw_cats : array();
    $cat_collection = array();
    foreach( $raw_cats as $cat ) {
        $cat_collection[] = array(
            'id' => $cat->term_id,
            'slug' => $cat->slug,
            'name' => sprintf( '%s (%s %s)', $cat->cat_name, $cat->count, __('posts', 'text_doma') )
        );
    }
    wp_send_json_success( $cat_collection );
}



////////////////////////////////////////////////////////////////
// CODE EDITOR PARAMS => to be used in the code editor input
// Fired in __construct()
function sek_get_code_editor_params() {
    sek_do_ajax_pre_checks( array( 'check_nonce' => true ) );
    $code_type = isset( $_POST['code_type'] ) ? sanitize_text_field($_POST['code_type']) : 'text/html';
    $editor_params = nimble_get_code_editor_settings( array(
        'type' => $code_type
    ));
    wp_send_json_success( $editor_params );
}

////////////////////////////////////////////////////////////////
// POSTPONE FEEDBACK NOTIFICATION IN CUSTOMIZER
// INSPIRED FROM CORE DISMISS POINTER MECHANISM
// @see wp-admin/includes/ajax-actions.php
// Nov 2020 => DEPRECATED https://github.com/presscustomizr/nimble-builder/issues/701
function sek_postpone_feedback_notification() {
    sek_do_ajax_pre_checks( array( 'check_nonce' => true ) );

    if ( !isset( $_POST['transient_duration_in_days'] ) ||!is_numeric( $_POST['transient_duration_in_days'] ) ) {
        $transient_duration = 7 * DAY_IN_SECONDS;
    } else {
        $transient_duration = sanitize_text_field($_POST['transient_duration_in_days']) * DAY_IN_SECONDS;
    }
    set_transient( NIMBLE_FEEDBACK_NOTICE_ID, 'maybe_later', $transient_duration );
    wp_die( 1 );
}


////////////////////////////////////////////////////////////////
// FETCH FONT AWESOME ICONS
// hook : ac_set_ajax_czr_tmpl___czr_tiny_mce_editor_module
// this dynamic filter is declared on wp_ajax_ac_get_template
// It allows us to populate the server response with the relevant module html template
// $html = apply_filters( "ac_set_ajax_czr_tmpl___{$module_type}", '', $tmpl );
//
// For czr_tiny_mce_editor_module, we request the font_list tmpl
function sek_get_fa_icon_list_tmpl( $html, $requested_tmpl = '', $posted_params = array() ) {
    if ( empty( $requested_tmpl ) ) {
        wp_send_json_error( __FUNCTION__ . ' => the requested tmpl is empty' );
    }
    return wp_json_encode(
        sek_retrieve_decoded_font_awesome_icons()
    );//will be sent by wp_send_json_success() in ::ac_set_ajax_czr_tmpl()
}


//retrieves faicons:
// 1) from faicons.json if needed (transient doesn't exists, or is new version => set in TC_wfc ) and decodes them
// otherwise
// 2) from the transient set if it exists
function sek_retrieve_decoded_font_awesome_icons() {
    // this file must be generated with: https://github.com/presscustomizr/nimble-builder/issues/57
    $faicons_json_path      = NIMBLE_BASE_PATH . '/assets/faicons.json';
    $faicons_transient_name = NIMBLE_FAWESOME_TRANSIENT_ID;
    if ( false == get_transient( $faicons_transient_name ) ) {
        if ( file_exists( $faicons_json_path ) ) {
            $faicons_raw      = @file_get_contents( $faicons_json_path );

            if ( false === $faicons_raw ) {
                $faicons_raw = wp_remote_fopen( $faicons_json_path );
            }

            $faicons_decoded   = json_decode( $faicons_raw, true );
            set_transient( $faicons_transient_name , $faicons_decoded , 60*60*24*3000 );
        } else {
            wp_send_json_error( __FUNCTION__ . ' => the file faicons.json is missing' );
        }
    }
    else {
        $faicons_decoded = get_transient( $faicons_transient_name );
    }

    return $faicons_decoded;
}




////////////////////////////////////////////////////////////////
// FETCH FONT LISTS
// hook : ac_set_ajax_czr_tmpl___czr_tiny_mce_editor_module
// For czr_tiny_mce_editor_module, we request the font_list tmpl
function sek_get_font_list_tmpl( $html, $requested_tmpl = '', $posted_params = array() ) {
    if ( empty( $requested_tmpl ) ) {
        wp_send_json_error( __FUNCTION__ . ' => the requested tmpl is empty' );
    }

    return wp_json_encode( array(
        'cfonts' => sek_get_cfonts(),
        'gfonts' => get_option( NIMBLE_OPT_NAME_FOR_DISABLING_GOOGLE_FONTS ) == 'on' ? [] : sek_get_gfonts(),
    ) );//will be sent by wp_send_json_success() in ::ac_set_ajax_czr_tmpl()
}


function sek_get_cfonts() {
    $cfonts = array();
    $raw_cfonts = array(
        '-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Helvetica Neue, Arial, sans-serif',
        'Arial Black,Arial Black,Gadget,sans-serif',
        'Century Gothic',
        'Comic Sans MS,Comic Sans MS,cursive',
        'Courier New,Courier New,Courier,monospace',
        'Georgia,Georgia,serif',
        'Helvetica Neue, Helvetica, Arial, sans-serif',
        'Impact,Charcoal,sans-serif',
        'Lucida Console,Monaco,monospace',
        'Lucida Sans Unicode,Lucida Grande,sans-serif',
        'Palatino Linotype,Book Antiqua,Palatino,serif',
        'Tahoma,Geneva,sans-serif',
        'Times New Roman,Times,serif',
        'Trebuchet MS,Helvetica,sans-serif',
        'Verdana,Geneva,sans-serif',
    );
    foreach ( $raw_cfonts as $font ) {
      //no subsets for cfonts => epty array()
      $cfonts[] = array(
          'name'    => $font ,
          'subsets'   => array()
      );
    }
    return apply_filters( 'sek_font_picker_cfonts', $cfonts );
}


//retrieves gfonts:
// 1) from webfonts.json if needed (transient doesn't exists, or is new version => set in TC_wfc ) and decodes them
// otherwise
// 2) from the transiet set if it exists
//
// => Until June 2017, the webfonts have been stored in 'tc_gfonts' transient
// => In June 2017, the Google Fonts have been updated with a new webfonts.json
// generated from : https://www.googleapis.com/webfonts/v1/webfonts?key=AIzaSyBID8gp8nBOpWyH5MrsF7doP4fczXGaHdA
//
// => The transient name is now : czr_gfonts_june_2017
function sek_retrieve_decoded_gfonts() {
    if ( false == get_transient( NIMBLE_GFONTS_TRANSIENT_ID ) ) {
        $gfont_raw      = @file_get_contents( NIMBLE_BASE_PATH ."/assets/webfonts.json" );

        if ( $gfont_raw === false ) {
          $gfont_raw = wp_remote_fopen( NIMBLE_BASE_PATH ."/assets/webfonts.json" );
        }

        $gfonts_decoded   = json_decode( $gfont_raw, true );
        set_transient( NIMBLE_GFONTS_TRANSIENT_ID , $gfonts_decoded , 60*60*24*3000 );
    }
    else {
      $gfonts_decoded = get_transient( NIMBLE_GFONTS_TRANSIENT_ID );
    }

    return $gfonts_decoded;
}

//@return the google fonts
function sek_get_gfonts( $what = null ) {
    //checks if transient exists or has expired

    $gfonts_decoded = sek_retrieve_decoded_gfonts();
    $gfonts = array();
    //$subsets = array();

    // $subsets['all-subsets'] = sprintf( '%1$s ( %2$s %3$s )',
    //   __( 'All languages' , 'text_doma' ),
    //   count($gfonts_decoded['items']) + count( get_cfonts() ),
    //   __('fonts' , 'text_doma' )
    // );

    foreach ( $gfonts_decoded['items'] as $font ) {
      foreach ( $font['variants'] as $variant ) {
        $name     = str_replace( ' ', '+', $font['family'] );
        $gfonts[]   = array(
            'name'    => $name . ':' .$variant
            //'subsets'   => $font['subsets']
        );
      }
      //generates subset list : subset => font number
      // foreach ( $font['subsets'] as $sub ) {
      //   $subsets[$sub] = isset($subsets[$sub]) ? $subsets[$sub]+1 : 1;
      // }
    }

    //finalizes the subset array
    // foreach ( $subsets as $subset => $font_number ) {
    //   if ( 'all-subsets' == $subset )
    //     continue;
    //   $subsets[$subset] = sprintf('%1$s ( %2$s %3$s )',
    //     $subset,
    //     $font_number,
    //     __('fonts' , 'text_doma' )
    //   );
    // }

    return ('subsets' == $what) ? apply_filters( 'sek_font_picker_gfonts_subsets ', $subsets ) : apply_filters( 'sek_font_picker_gfonts', $gfonts )  ;
}
?><?php
add_action( 'customize_register', '\Nimble\sek_catch_export_action', PHP_INT_MAX );
function sek_catch_export_action( $wp_customize ) {
    if ( current_user_can( 'customize' ) ) {
        if ( isset( $_REQUEST['sek_export_nonce'] ) ) {
            sek_maybe_export();
        }
    }
}

// fire from sek_catch_export_action() @hook 'customize_register'
function sek_maybe_export() {
    $nonce = 'save-customize_' . get_stylesheet();
    if ( !isset( $_REQUEST['sek_export_nonce'] ) ) {
        sek_error_log( __FUNCTION__ . ' => missing nonce.');
        return;
    }
    if ( !isset( $_REQUEST['skope_id']) || empty( $_REQUEST['skope_id'] ) ) {
        sek_error_log( __FUNCTION__ . ' => missing or empty skope_id.');
        return;
    }
    if ( !isset( $_REQUEST['active_locations'] ) || empty( $_REQUEST['active_locations'] ) ) {
        sek_error_log( __FUNCTION__ . ' => missing active locations param.');
        return;
    }
    if ( !wp_verify_nonce( $_REQUEST['sek_export_nonce'], $nonce ) ) {
        sek_error_log( __FUNCTION__ . ' => invalid none.');
        return;
    }
    if ( !is_user_logged_in() ) {
        sek_error_log( __FUNCTION__ . ' => user not logged in.');
        return;
    }
    if ( !current_user_can( 'customize' ) ) {
        sek_error_log( __FUNCTION__ . ' => missing customize capabilities.');
        return;
    }

    $seks_data = sek_get_skoped_seks( sanitize_text_field($_REQUEST['skope_id']) );

    //sek_error_log('EXPORT BEFORE FILTER ? ' . $_REQUEST['skope_id'] , $seks_data );
    // the filter 'nimble_pre_export' is used to :
    // replace image id by the absolute url
    // clean level ids and replace them with a placeholder string
    $seks_data = apply_filters( 'nimble_pre_export', $seks_data );

    // March 2021 : make sure text input are sanitized like in #544 #792
    //$seks_data = sek_sektion_collection_sanitize_cb( $seks_data );

    $theme_name = sanitize_title_with_dashes( get_stylesheet() );
    
    //sek_error_log('EXPORT AFTER FILTER ?', $seks_data );
    $export = array(
        'data' => $seks_data,
        'metas' => array(
            'skope_id' => sanitize_text_field($_REQUEST['skope_id']),
            'version' => NIMBLE_VERSION,
            // is sent as a string : "__after_header,__before_main_wrapper,loop_start,__before_footer"
            'active_locations' => is_string( $_REQUEST['active_locations'] ) ? explode( ',', sanitize_text_field($_REQUEST['active_locations']) ) : array(),
            'date' => date("Y-m-d"),
            'theme' => $theme_name
        )
    );

    //sek_error_log('$export ?', $export );

    $skope_id = str_replace('skp__', '',  sanitize_text_field($_REQUEST['skope_id']) );
    $filename = $theme_name . '_' . $skope_id . '.nimblebuilder';

    // Set the download headers.
    header( 'Content-disposition: attachment; filename=' . $filename );
    header( 'Content-Type: application/octet-stream; charset=' . get_option( 'blog_charset' ) );

    echo wp_json_encode( $export );

    // Start the download.
    die();
}

// Ajax action before processing the export
// control that all required fields are there
// This is to avoid a white screen when generating the download window afterwards
add_action( 'wp_ajax_sek_pre_export_checks', '\Nimble\sek_ajax_pre_export_checks' );
function sek_ajax_pre_export_checks() {
    //sek_error_log('PRE EXPORT CHECKS ?', $_POST );
    $action = 'save-customize_' . get_stylesheet();
    if ( !check_ajax_referer( $action, 'nonce', false ) ) {
        wp_send_json_error( 'check_ajax_referer_failed' );
    }
    if ( !is_user_logged_in() ) {
        wp_send_json_error( 'user_unauthenticated' );
    }
    if ( !current_user_can( 'customize' ) ) {
        status_header( 403 );
        wp_send_json_error( 'customize_not_allowed' );
    } else if ( !isset( $_SERVER['REQUEST_METHOD'] ) || 'POST' !== $_SERVER['REQUEST_METHOD'] ) {
        status_header( 405 );
        wp_send_json_error( 'bad_ajax_method' );
    }
    if ( !isset( $_POST['skope_id'] ) || empty( $_POST['skope_id'] ) ) {
        wp_send_json_error( 'missing_skope_id' );
    }
    if ( !isset( $_POST['active_locations'] ) || empty( $_POST['active_locations'] ) ) {
        wp_send_json_error( 'no_active_locations_to_export' );
    }
    wp_send_json_success();
}






// EXPORT FILTER
add_filter( 'nimble_pre_export', '\Nimble\sek_parse_img_and_clean_id' );
function sek_parse_img_and_clean_id( $seks_data ) {
    $new_seks_data = array();
    foreach ( $seks_data as $key => $value ) {
        if ( is_array($value) ) {
            $new_seks_data[$key] = sek_parse_img_and_clean_id( $value );
        } else {
            switch( $key ) {
                case 'bg-image' :
                case 'img' :
                    if ( is_int( $value ) && (int)$value > 0 ) {
                        $value = '__img_url__' . wp_get_attachment_url((int)$value);
                    }
                break;
                case 'id' :
                    if ( is_string( $value ) && false !== strpos( $value, '__nimble__' ) ) {
                        $value = '__rep__me__';
                    }
                break;
            }
            $new_seks_data[$key] = $value;
        }
    }
    return $new_seks_data;
}






// fetch the content from a user imported file
add_action( 'wp_ajax_sek_get_manually_imported_file_content', '\Nimble\sek_ajax_get_manually_imported_file_content' );
function sek_ajax_get_manually_imported_file_content() {
    // sek_error_log(__FUNCTION__ . ' AJAX $_POST ?', $_POST );
    // sek_error_log(__FUNCTION__ . ' AJAX $_FILES ?', $_FILES );
    // sek_error_log(__FUNCTION__ . ' AJAX $_REQUEST ?', $_REQUEST );

    $action = 'save-customize_' . get_stylesheet();
    if ( !check_ajax_referer( $action, 'nonce', false ) ) {
        wp_send_json_error( 'check_ajax_referer_failed' );
    }
    if ( !is_user_logged_in() ) {
        wp_send_json_error( 'user_unauthenticated' );
    }
    if ( !current_user_can( 'customize' ) ) {
        status_header( 403 );
        wp_send_json_error( 'customize_not_allowed' );
    } else if ( !isset( $_SERVER['REQUEST_METHOD'] ) || 'POST' !== $_SERVER['REQUEST_METHOD'] ) {
        status_header( 405 );
        wp_send_json_error( 'bad_ajax_method' );
    }
    if ( !isset( $_FILES['file_candidate'] ) || empty( $_FILES['file_candidate'] ) ) {
        wp_send_json_error( 'missing_file_candidate' );
    }
    if ( !isset( $_POST['skope'] ) || empty( $_POST['skope'] ) ) {
        wp_send_json_error( 'missing_skope' );
    }

    // load WP upload if not done yet
    if ( !function_exists( 'wp_handle_upload' ) ) {
        require_once( ABSPATH . 'wp-admin/includes/file.php' );
    }

    // @see https://codex.wordpress.org/Function_Reference/wp_handle_upload
    // Important => always run unlink( $file['file'] ) before sending the json success or error
    // otherwise WP will write the file in the /wp-content folder
    $file = wp_handle_upload(
        $_FILES['file_candidate'],
        array(
            'test_form' => false,
            'test_type' => false,
            'mimes' => array(
                'text' => 'text/plain',
                //'nimblebuilder' => 'text/plain',
                'json' => 'application/json',
                'nimblebuilder' => 'application/json'
            )
        )
    );

    // Make sure we have an uploaded file.
    if ( isset( $file['error'] ) ) {
        unlink( $file['file'] );
        wp_send_json_error( 'import_file_error' );
        return;
    }
    if ( !file_exists( $file['file'] ) ) {
        unlink( $file['file'] );
        wp_send_json_error( 'import_file_do_not_exist' );
        return;
    }

    // Get the upload data.
    $raw = file_get_contents( $file['file'] );
    //$raw_unserialized_data = @unserialize( $raw );
    $raw_unserialized_data = json_decode( $raw, true );

    // VALIDATE IMPORTED CONTENT
    // data structure :
    // $raw_unserialized_data = array(
    //     'data' => $seks_data,
    //     'metas' => array(
    //         'skope_id' => $_REQUEST['skope_id'],
    //         'version' => NIMBLE_VERSION,
    //         // is sent as a string : "__after_header,__before_main_wrapper,loop_start,__before_footer"
    //         'active_locations' => is_string( $_REQUEST['active_locations'] ) ? explode( ',', $_REQUEST['active_locations'] ) : array(),
    //         'date' => date("Y-m-d")
    //     )
    // );
    // check import structure
    if ( !is_array( $raw_unserialized_data ) || empty( $raw_unserialized_data['data']) || !is_array( $raw_unserialized_data['data'] ) || empty( $raw_unserialized_data['metas'] ) || !is_array( $raw_unserialized_data['metas'] ) ) {
        unlink( $file['file'] );
        wp_send_json_error(  'invalid_import_content' );
        return;
    }
    // check version
    // => current Nimble Version must be at least import version
    if ( !empty( $raw_unserialized_data['metas']['version'] ) && version_compare( NIMBLE_VERSION, $raw_unserialized_data['metas']['version'], '<' ) ) {
        unlink( $file['file'] );
        wp_send_json_error( 'nimble_builder_needs_update' );
        return;
    }

    $maybe_import_images = true;
    // in a pre-import-check context, we don't need to sniff and upload images
    if ( array_key_exists( 'pre_import_check', $_POST ) && true === sek_booleanize_checkbox_val( sanitize_text_field($_POST['pre_import_check']) ) ) {
        $maybe_import_images = false;
    }
    // april 2020 : introduced for https://github.com/presscustomizr/nimble-builder/issues/663
    if ( array_key_exists( 'import_img', $_POST ) && false === sek_booleanize_checkbox_val( sanitize_text_field($_POST['import_img']) ) ) {
        $maybe_import_images = false;
    }

    // Make sure NB decodes encoded rich text before sending to the customizer
    // see #544 and #791
    $raw_unserialized_data['data'] = sek_sniff_and_decode_richtext( $raw_unserialized_data['data'] );

    $imported_content = array(
        //'data' => apply_filters( 'nimble_pre_import', $raw_unserialized_data['data'], $do_import_images ),
        'data' => sek_maybe_import_imgs( $raw_unserialized_data['data'], $maybe_import_images ),
        'metas' => $raw_unserialized_data['metas'],
        // the image import errors won't block the import
        // they are used when notifying user in the customizer
        'img_errors' => !empty( Nimble_Manager()->img_import_errors ) ? implode(',', Nimble_Manager()->img_import_errors) : array()
    );

    // Remove the uploaded file
    // Important => always run unlink( $file['file'] ) before sending the json success or error
    // otherwise WP will write the file in the /wp-content folder
    unlink( $file['file'] );
    // Send
    wp_send_json_success( $imported_content );
}
?><?php
////////////////////////////////////////////////////////////////
// Fetches the user saved templates
add_action( 'wp_ajax_sek_get_all_saved_tmpl', '\Nimble\sek_ajax_get_all_saved_templates' );
// @hook wp_ajax_sek_get_user_saved_templates
function sek_ajax_get_all_saved_templates() {
    sek_do_ajax_pre_checks( array( 'check_nonce' => true ) );

    $decoded_templates = sek_get_all_saved_templates();

    if ( is_array($decoded_templates) ) {
        wp_send_json_success( $decoded_templates );
    } else {
        if ( !empty( $decoded_templates ) ) {
            sek_error_log(  __FUNCTION__ . ' error => invalid templates returned', $decoded_templates );
            wp_send_json_error(  __FUNCTION__ . ' error => invalid templates returned' );
        }
    }
}

////////////////////////////////////////////////////////////////
// Fetches the api templates
add_action( 'wp_ajax_sek_get_all_api_tmpl', '\Nimble\sek_ajax_get_all_api_templates' );
// @hook wp_ajax_sek_get_user_saved_templates
function sek_ajax_get_all_api_templates() {
    sek_do_ajax_pre_checks( array( 'check_nonce' => true ) );

    $decoded_templates = sek_get_all_api_templates();

    if ( is_array($decoded_templates) ) {
        wp_send_json_success( $decoded_templates );
    } else {
        if ( !empty( $decoded_templates ) ) {
            sek_error_log(  __FUNCTION__ . ' error => invalid templates returned', $decoded_templates );
            wp_send_json_error(  __FUNCTION__ . ' error => invalid templates returned' );
        }
    }
}


////////////////////////////////////////////////////////////////
// TEMPLATE GET CONTENT + METAS
// Fetches the json of a given user template
add_action( 'wp_ajax_sek_get_user_tmpl_json', '\Nimble\sek_ajax_sek_get_user_tmpl_json' );
// @hook wp_ajax_sek_get_user_saved_templates
function sek_ajax_sek_get_user_tmpl_json() {
    sek_do_ajax_pre_checks( array( 'check_nonce' => true ) );

    // We must have a tmpl_post_name
    if ( empty( $_POST['tmpl_post_name']) || !is_string( $_POST['tmpl_post_name'] ) ) {
        wp_send_json_error( __FUNCTION__ . '_missing_tmpl_post_name' );
    }
    // if ( !isset( $_POST['skope_id'] ) || empty( $_POST['skope_id'] ) ) {
    //     wp_send_json_error( __FUNCTION__ . '_missing_skope_id' );
    // }
    $tmpl_post = sek_get_saved_tmpl_post( sanitize_text_field($_POST['tmpl_post_name']) );
    if ( !is_wp_error( $tmpl_post ) && $tmpl_post && is_object( $tmpl_post ) ) {
        $tmpl_decoded = maybe_unserialize( $tmpl_post->post_content );

        // Structure of $content :
        // array(
        //     'data' => $_POST['tmpl_data'],//<= json stringified
        //     'tmpl_post_name' => ( !empty( $_POST['tmpl_post_name'] ) && is_string( $_POST['tmpl_post_name'] ) ) ? $_POST['tmpl_post_name'] : null,
        //     'metas' => array(
        //         'title' => $_POST['tmpl_title'],
        //         'description' => $_POST['tmpl_description'],
        //         'skope_id' => $_POST['skope_id'],
        //         'version' => NIMBLE_VERSION,
        //         // is sent as a string : "__after_header,__before_main_wrapper,loop_start,__before_footer"
        //         'tmpl_locations' => is_string( $_POST['tmpl_locations'] ) ? explode( ',', $_POST['tmpl_locations'] ) : array(),
        //         'date' => date("Y-m-d"),
        //         'theme' => sanitize_title_with_dashes( get_stylesheet() )
        //     )
        // );
        if ( is_array( $tmpl_decoded ) && !empty( $tmpl_decoded['data'] ) && is_array( $tmpl_decoded['data'] ) ) {
            //$tmpl_decoded['data'] = json_decode( wp_unslash( $tmpl_decoded['data'], true ) );
            $tmpl_decoded['data'] = sek_maybe_import_imgs( $tmpl_decoded['data'], $do_import_images = true );
            // the image import errors won't block the import
            // they are used when notifying user in the customizer
            $tmpl_decoded['img_errors'] = !empty( Nimble_Manager()->img_import_errors ) ? implode(',', Nimble_Manager()->img_import_errors) : array();
            // Make sure we decode encoded rich text before sending to the customizer
            // see #544 and #791
            $tmpl_decoded['data'] = sek_sniff_and_decode_richtext( $tmpl_decoded['data'] );

            // added March 2021 for site templates #478
            // If property '__inherits_group_skope_tmpl_when_exists__' has been saved by mistake in the template, make sure it's unset now
            if ( array_key_exists('__inherits_group_skope_tmpl_when_exists__', $tmpl_decoded['data'] ) ) {
                unset( $tmpl_decoded['data']['__inherits_group_skope_tmpl_when_exists__'] );
            }
            wp_send_json_success( $tmpl_decoded );
        } else {
            wp_send_json_error( __FUNCTION__ . '_invalid_tmpl_post_data' );
        }
    } else {
        wp_send_json_error( __FUNCTION__ . '_tmpl_post_not_found' );
    }
}



add_action( 'wp_ajax_sek_get_api_tmpl_json', '\Nimble\sek_ajax_sek_get_api_tmpl_json' );
// @hook wp_ajax_sek_get_user_saved_templates
function sek_ajax_sek_get_api_tmpl_json() {
    sek_do_ajax_pre_checks( array( 'check_nonce' => true ) );

    // We must have a tmpl_post_name
    if ( empty( $_POST['api_tmpl_name']) || !is_string( $_POST['api_tmpl_name'] ) ) {
        wp_send_json_error( __FUNCTION__ . '_missing_tmpl_post_name' );
    }
    $tmpl_name = sanitize_text_field($_POST['api_tmpl_name']);

    // Pro Template case
    $is_pro_tmpl = array_key_exists('api_tmpl_is_pro', $_POST ) && 'yes' === sanitize_text_field($_POST['api_tmpl_is_pro']);
    if ( $is_pro_tmpl ) {
        $pro_key_status = apply_filters( 'nimble_pro_key_status_OK', 'nok' );
        if ( 'pro_key_status_ok' !== $pro_key_status ) {
            wp_send_json_error( $pro_key_status );
            return;
        }
    }

    $raw_tmpl_data = sek_get_single_tmpl_api_data( $tmpl_name, $is_pro_tmpl );// <= returns an unserialized array, in which the template['data'] is NOT a JSON, unlike for user saved templates

    // If the api returned a pro license key problem, bail now and return the api string message
    if ( $is_pro_tmpl && is_string( $raw_tmpl_data ) && !empty( $raw_tmpl_data ) ) {
        wp_send_json_error( $raw_tmpl_data );
    } else if ( !is_array( $raw_tmpl_data) || empty( $raw_tmpl_data ) ) {
        sek_error_log( __FUNCTION__ . ' problem when getting template : ' . $tmpl_name );
        wp_send_json_error( __FUNCTION__ . '_invalid_template_'. $tmpl_name );
    }

    //sek_error_log( __FUNCTION__ . ' api template collection', $raw_tmpl_data );
    if ( !isset($raw_tmpl_data['data'] ) || empty( $raw_tmpl_data['data'] ) ) {
        sek_error_log( __FUNCTION__ . ' problem => missing or invalid data property for template : ' . $tmpl_name, $raw_tmpl_data );
        wp_send_json_error( __FUNCTION__ . '_missing_data_property_for_template_' . $tmpl_name );
    } else {
        // $tmpl_decoded = $raw_tmpl_data;
        $raw_tmpl_data['data'] = sek_maybe_import_imgs( $raw_tmpl_data['data'], $do_import_images = true );
        $raw_tmpl_data['img_errors'] = !empty( Nimble_Manager()->img_import_errors ) ? implode(',', Nimble_Manager()->img_import_errors) : array();
        // Make sure we decode encoded rich text before sending to the customizer
        // see #544 and #791
        $raw_tmpl_data['data'] = sek_sniff_and_decode_richtext( $raw_tmpl_data['data'] );
        
        // added March 2021 for site templates #478
        // If property '__inherits_group_skope_tmpl_when_exists__' has been saved by mistake in the template, make sure it's unset now
        if ( array_key_exists('__inherits_group_skope_tmpl_when_exists__', $raw_tmpl_data['data'] ) ) {
            unset( $raw_tmpl_data['data']['__inherits_group_skope_tmpl_when_exists__'] );
        }
        wp_send_json_success( $raw_tmpl_data );
    }
    //return [];
}



////////////////////////////////////////////////////////////////
// TEMPLATE SAVE
// introduced in april 2020 for https://github.com/presscustomizr/nimble-builder/issues/655
add_action( 'wp_ajax_sek_save_user_template', '\Nimble\sek_ajax_save_user_template' );
/////////////////////////////////////////////////////////////////
// hook : wp_ajax_sek_save_user_template
function sek_ajax_save_user_template() {
    sek_do_ajax_pre_checks( array( 'check_nonce' => true ) );
    $is_edit_metas_only_case = isset( $_POST['edit_metas_only'] ) && 'yes' === sanitize_text_field($_POST['edit_metas_only']);

    // TMPL DATA => the nimble content
    if ( !$is_edit_metas_only_case && empty( $_POST['tmpl_data']) ) {
        wp_send_json_error( __FUNCTION__ . '_missing_template_data' );
    }
    if ( !$is_edit_metas_only_case && !is_string( $_POST['tmpl_data'] ) ) {
        wp_send_json_error( __FUNCTION__ . '_template_data_must_be_a_json_stringified' );
    }

    // TMPL METAS
    // We must have a title
    if ( empty( $_POST['tmpl_title']) ) {
        wp_send_json_error( __FUNCTION__ . '_missing_template_title' );
    }
    if ( !is_string( $_POST['tmpl_description'] ) ) {
        wp_send_json_error( __FUNCTION__ . '_template_description_must_be_a_string' );
    }
    if ( !isset( $_POST['skope_id'] ) || empty( $_POST['skope_id'] ) ) {
        wp_send_json_error( __FUNCTION__ . '_missing_skope_id' );
    }
    if ( !isset( $_POST['tmpl_locations'] ) || empty( $_POST['tmpl_locations'] ) ) {
        wp_send_json_error( __FUNCTION__ . '_missing_tmpl_locations' );
    }

    if ( $is_edit_metas_only_case ) {
        $tmpl_data = [];
    } else {
        // clean level ids and replace them with a placeholder string
        $tmpl_data = json_decode( wp_unslash( $_POST['tmpl_data'] ), true );
        $tmpl_data = sek_template_save_clean_id( $tmpl_data );
        
        // added March 2021 for site templates #478
        // If property '__inherits_group_skope_tmpl_when_exists__' has been set to the template, make sure it's unset now
        if ( array_key_exists('__inherits_group_skope_tmpl_when_exists__', $tmpl_data ) ) {
            unset( $tmpl_data['__inherits_group_skope_tmpl_when_exists__'] );
        }
    }
    
    // make sure description and title are clean before DB
    $tmpl_title = sek_maybe_encode_richtext( sanitize_text_field($_POST['tmpl_title']) );
    $tmpl_description = sek_maybe_encode_richtext( sanitize_text_field($_POST['tmpl_description']) );
    
    // sanitize tmpl_locations
    $tmpl_locations = [];
    if ( is_array($_POST['tmpl_locations']) ) {
        foreach($_POST['tmpl_locations'] as $loc ) {
            $tmpl_locations[] = sanitize_text_field($loc);
        }
    }

    // sek_error_log('json decode ?', json_decode( wp_unslash( $_POST['sek_data'] ), true ) );
    $template_to_save = array(
        'data' => $tmpl_data,//<= array
        'tmpl_post_name' => ( !empty( $_POST['tmpl_post_name'] ) && is_string( $_POST['tmpl_post_name'] ) ) ? sanitize_text_field($_POST['tmpl_post_name']) : null,
        'metas' => array(
            'title' => $tmpl_title,
            'description' => $tmpl_description,
            'skope_id' => sanitize_text_field($_POST['skope_id']),
            'version' => NIMBLE_VERSION,
            // is sent as a string : "__after_header,__before_main_wrapper,loop_start,__before_footer"
            'tmpl_locations' => $tmpl_locations,
            'tmpl_header_location' => isset( $_POST['tmpl_header_location'] ) ? sanitize_text_field($_POST['tmpl_header_location']) : '',
            'tmpl_footer_location' => isset( $_POST['tmpl_footer_location'] ) ? sanitize_text_field($_POST['tmpl_footer_location']) : '',
            'date' => date("Y-m-d"),
            'theme' => sanitize_title_with_dashes( get_stylesheet() ),
            // for api templates
            'is_pro_tmpl' => false,
            'thumb_url' => ''
        )
    );

    $saved_template_post = sek_update_user_tmpl_post( $template_to_save, $is_edit_metas_only_case );
    if ( is_wp_error( $saved_template_post ) || is_null($saved_template_post) || empty($saved_template_post) ) {
        wp_send_json_error( __FUNCTION__ . ' => error when invoking sek_update_user_tmpl_post()' );
    } else {
        // sek_error_log( 'ALORS CE POST?', $saved_template_post );
        wp_send_json_success( [ 'tmpl_post_id' => $saved_template_post->ID ] );
    }
    //sek_error_log( __FUNCTION__ . '$_POST' ,  $_POST);
}


// SAVE FILTER
function sek_template_save_clean_id( $tmpl_data = array() ) {
    $new_tmpl_data = array();
    if ( !is_array( $tmpl_data ) ) {
        sek_error_log( __FUNCTION__ . ' error => tmpl_data should be an array');
        return array();
    }
    $level = null;
    if ( isset($tmpl_data['level'] ) ) {
        $level = $tmpl_data['level'];
    }
    foreach ( $tmpl_data as $key => $value ) {
        if ( is_array($value) ) {
            $new_tmpl_data[$key] = sek_template_save_clean_id( $value );
        } else {
            switch( $key ) {
                // we want to replace ids for all levels but locations
                // only section, columns and modules have an id which starts by __nimble__, for ex : __nimble__2024500518bf
                // locations id are like : loop_start
                case 'id' :
                    if ( 'location' !== $level && is_string( $value ) && false !== strpos( $value, '__nimble__' ) ) {
                        $value = '__rep__me__';
                    }
                break;
            }
            $new_tmpl_data[$key] = $value;
        }
    }
    return $new_tmpl_data;
}


////////////////////////////////////////////////////////////////
// TEMPLATE REMOVE
// introduced in may 2020 for https://github.com/presscustomizr/nimble-builder/issues/655
add_action( 'wp_ajax_sek_remove_user_template', '\Nimble\sek_ajax_remove_user_template' );
/////////////////////////////////////////////////////////////////
// hook : wp_ajax_sek_remove_user_template
function sek_ajax_remove_user_template() {
    //sek_error_log( __FUNCTION__ . ' ALORS YEAH IN REMOVAL ? ?', $_POST );

    sek_do_ajax_pre_checks( array( 'check_nonce' => true ) );

    // We must have a tmpl_post_name
    if ( empty( $_POST['tmpl_post_name']) || !is_string( $_POST['tmpl_post_name'] ) ) {
        wp_send_json_error( __FUNCTION__ . '_missing_tmpl_post_name' );
    }
    $tmpl_post_name = sanitize_text_field($_POST['tmpl_post_name']);
    // if ( !isset( $_POST['skope_id'] ) || empty( $_POST['skope_id'] ) ) {
    //     wp_send_json_error( __FUNCTION__ . '_missing_skope_id' );
    // }
    $tmpl_post_to_remove = sek_get_saved_tmpl_post( $tmpl_post_name );

    //sek_error_log( __FUNCTION__ . ' => so $tmpl_post_to_remove ' . $_POST['tmpl_post_name'], $tmpl_post_to_remove );

    if ( $tmpl_post_to_remove && is_object( $tmpl_post_to_remove ) ) {
        // the CPT is moved to Trash instead of permanently deleted when using wp_delete_post()
        $r = wp_trash_post( $tmpl_post_to_remove->ID );
        if ( is_wp_error( $r ) ) {
            wp_send_json_error( __FUNCTION__ . '_removal_error' );
        }

        // Added April 2021 for stie templates #478
        do_action('nb_on_remove_saved_tmpl_post', $tmpl_post_name );
    } else {
        wp_send_json_error( __FUNCTION__ . '_tmpl_post_not_found' );
    }

    if ( is_wp_error( $tmpl_post_to_remove ) || is_null($tmpl_post_to_remove) || empty($tmpl_post_to_remove) ) {
        wp_send_json_error( __FUNCTION__ . '_removal_error' );
    } else {
        // sek_error_log( 'ALORS CE POST?', $saved_template_post );
        wp_send_json_success( [ 'tmpl_post_removed' => $tmpl_post_name ] );
    }
    //sek_error_log( __FUNCTION__ . '$_POST' ,  $_POST);
}

?><?php
////////////////////////////////////////////////////////////////
// Fetches the user saved sections
add_action( 'wp_ajax_sek_get_all_saved_sections', '\Nimble\sek_ajax_get_all_saved_sections' );
// @hook wp_ajax_sek_get_user_saved_sections
function sek_ajax_get_all_saved_sections() {
    sek_do_ajax_pre_checks( array( 'check_nonce' => true ) );

    $decoded_sections = sek_get_all_saved_sections();

    if ( is_array($decoded_sections) ) {
        wp_send_json_success( $decoded_sections );
    } else {
        if ( !empty( $decoded_sections ) ) {
            sek_error_log(  __FUNCTION__ . ' error => invalid sections returned', $decoded_sections );
            wp_send_json_error(  __FUNCTION__ . ' error => invalid sections returned' );
        }
    }
}



// Fetches the preset_sections
add_action( 'wp_ajax_sek_get_single_api_section_data', '\Nimble\sek_ajax_get_single_api_section_data' );
////////////////////////////////////////////////////////////////
// PRESET SECTIONS
// Fired in __construct()
// hook : 'wp_ajax_sek_get_preset_sektions'
function sek_ajax_get_single_api_section_data() {
    sek_do_ajax_pre_checks( array( 'check_nonce' => true ) );

    // September 2020 => force update every 24 hours so users won't miss a new pre-build section
    // Note that the refresh should have take place on 'upgrader_process_complete'
    // always force refresh when developing
    sek_do_ajax_pre_checks( array( 'check_nonce' => true ) );

    // We must have a api_section_id
    if ( empty( $_POST['api_section_id']) || !is_string( $_POST['api_section_id'] ) ) {
        wp_send_json_error( __FUNCTION__ . '_missing_api_section_id' );
    }
    $api_section_id = sanitize_text_field($_POST['api_section_id']);

    $is_pro_section_id = sek_is_pro() && is_string($api_section_id) && 'pro_' === substr($api_section_id,0,4);
    $pro_key_status = apply_filters( 'nimble_pro_key_status_OK', 'nok' );
    if ( $is_pro_section_id && 'pro_key_status_ok' !== $pro_key_status ) {
        wp_send_json_error( $pro_key_status );
        return;
    }
    $raw_api_sec_data = sek_api_get_single_section_data( $api_section_id );// <= returns an unserialized array

    // When injecting a pro section, NB checks the validity of the key.
    // if the api response is not an array, there was a problem when checking the key
    // and in this case the response is a string like : 'Expired.'
    if ( $is_pro_section_id && is_string($raw_api_sec_data) && !empty($raw_api_sec_data) ) {
        wp_send_json_error( $raw_api_sec_data );
        return;
    }

    if( !is_array( $raw_api_sec_data) || empty( $raw_api_sec_data ) ) {
        sek_error_log( __FUNCTION__ . ' problem when getting section : ' . $api_section_id );
        wp_send_json_error( 'Error : empty or invalid section data : '. $api_section_id );
        return;
    }
    //sek_error_log( __FUNCTION__ . ' api section data', $raw_api_sec_data );
    if ( !isset($raw_api_sec_data['collection'] ) || empty( $raw_api_sec_data['collection'] ) ) {
        sek_error_log( __FUNCTION__ . ' problem => missing or invalid data property for section : ' . $api_section_id, $raw_api_sec_data );
        wp_send_json_error( 'Error : missing_data_property_for_section : ' . $api_section_id );
    } else {
        // $tmpl_decoded = $raw_api_sec_data;
        $raw_api_sec_data['collection'] = sek_maybe_import_imgs( $raw_api_sec_data['collection'], $do_import_images = true );
        //$raw_api_sec_data['img_errors'] = !empty( Nimble_Manager()->img_import_errors ) ? implode(',', Nimble_Manager()->img_import_errors) : array();
        // Make sure we decode encoded rich text before sending to the customizer
        // see #544 and #791
        $raw_api_sec_data['collection'] = sek_sniff_and_decode_richtext( $raw_api_sec_data['collection'] );

        wp_send_json_success( $raw_api_sec_data );
    }
}






////////////////////////////////////////////////////////////////
// SECTION GET CONTENT + METAS
// Fetches the json of a given user section
add_action( 'wp_ajax_sek_get_user_section_json', '\Nimble\sek_ajax_sek_get_user_section_json' );
// @hook wp_ajax_sek_get_user_saved_sections
function sek_ajax_sek_get_user_section_json() {
    sek_do_ajax_pre_checks( array( 'check_nonce' => true ) );

    // We must have a section_post_name
    if ( empty( $_POST['section_post_name']) || !is_string( $_POST['section_post_name'] ) ) {
        wp_send_json_error( __FUNCTION__ . '_missing_section_post_name' );
    }
    // if ( !isset( $_POST['skope_id'] ) || empty( $_POST['skope_id'] ) ) {
    //     wp_send_json_error( __FUNCTION__ . '_missing_skope_id' );
    // }
    $section_post = sek_get_saved_section_post( sanitize_text_field($_POST['section_post_name']) );
    if ( !is_wp_error( $section_post ) && $section_post && is_object( $section_post ) ) {
        $section_decoded = maybe_unserialize( $section_post->post_content );
        // Structure of $content :
        // array(
        //     'data' => $_POST['section_data'],//<= json stringified
        //     'section_post_name' => ( !empty( $_POST['section_post_name'] ) && is_string( $_POST['section_post_name'] ) ) ? $_POST['section_post_name'] : null,
        //     'metas' => array(
        //         'title' => $_POST['section_title'],
        //         'description' => $_POST['section_description'],
        //         'skope_id' => $_POST['skope_id'],
        //         'version' => NIMBLE_VERSION,
        //         // is sent as a string : "__after_header,__before_main_wrapper,loop_start,__before_footer"
        //         'active_locations' => is_string( $_POST['active_locations'] ) ? explode( ',', $_POST['active_locations'] ) : array(),
        //         'date' => date("Y-m-d"),
        //         'theme' => sanitize_title_with_dashes( get_stylesheet() )
        //     )
        // );
        if ( is_array( $section_decoded ) && !empty( $section_decoded['data'] ) && is_string( $section_decoded['data'] ) ) {
            $section_decoded['data'] = json_decode( wp_unslash( $section_decoded['data'], true ) );
        }
        // Make sure we decode encoded rich text before sending to the customizer
        // see #544 and #791
        $section_decoded['data'] = sek_sniff_and_decode_richtext( $section_decoded['data'] );
        wp_send_json_success( $section_decoded );
    } else {
        wp_send_json_error( __FUNCTION__ . '_section_post_not_found' );
    }
}






////////////////////////////////////////////////////////////////
// SECTION SAVE
// introduced in april 2020 for https://github.com/presscustomizr/nimble-builder/issues/655
// ENABLED WHEN CONSTANT NIMBLE_SECTION_SAVE_ENABLED === true
add_action( 'wp_ajax_sek_save_user_section', '\Nimble\sek_ajax_save_user_section' );
/////////////////////////////////////////////////////////////////
// hook : wp_ajax_sek_save_user_section
function sek_ajax_save_user_section() {
    sek_do_ajax_pre_checks( array( 'check_nonce' => true ) );
    $is_edit_metas_only_case = isset( $_POST['edit_metas_only'] ) && 'yes' === sanitize_text_field($_POST['edit_metas_only']);
    // TMPL DATA => the nimble content
    if ( !$is_edit_metas_only_case && empty( $_POST['section_data']) ) {
        wp_send_json_error( __FUNCTION__ . '_missing_section_data' );
    }
    if ( !$is_edit_metas_only_case && !is_string( $_POST['section_data'] ) ) {
        wp_send_json_error( __FUNCTION__ . '_section_data_must_be_a_json_stringified' );
    }

    // TMPL METAS
    // We must have a title
    if ( empty( $_POST['section_title']) ) {
        wp_send_json_error( __FUNCTION__ . '_missing_section_title' );
    }
    if ( !is_string( $_POST['section_description'] ) ) {
        wp_send_json_error( __FUNCTION__ . '_section_description_must_be_a_string' );
    }
    if ( !isset( $_POST['skope_id'] ) || empty( $_POST['skope_id'] ) ) {
        wp_send_json_error( __FUNCTION__ . '_missing_skope_id' );
    }
    // if ( !isset( $_POST['active_locations'] ) || empty( $_POST['active_locations'] ) ) {
    //     wp_send_json_error( __FUNCTION__ . '_missing_active_locations' );
    // }

    if ( $is_edit_metas_only_case ) {
        $seks_data = [];
    } else {
        // clean level ids and replace them with a placeholder string
        $seks_data = json_decode( wp_unslash( $_POST['section_data'] ), true );
        $seks_data = sek_section_save_clean_id( $seks_data );
    }

    // make sure description and title are clean before DB
    $sec_title = sek_maybe_encode_richtext( sanitize_text_field($_POST['section_title']) );
    $sec_description = sek_maybe_encode_richtext( sanitize_text_field($_POST['section_description']) );

    $section_to_save = array(
        'data' => $seks_data,//<= json stringified
        // the section post name is provided only when updating
        'section_post_name' => ( !empty( $_POST['section_post_name'] ) && is_string( $_POST['section_post_name'] ) ) ? sanitize_text_field($_POST['section_post_name']) : null,
        'metas' => array(
            'title' => $sec_title,
            'description' => $sec_description,
            'skope_id' => sanitize_text_field($_POST['skope_id']),
            'version' => NIMBLE_VERSION,
            // is sent as a string : "__after_header,__before_main_wrapper,loop_start,__before_footer"
            //'active_locations' => is_array( $_POST['active_locations'] ) ? $_POST['active_locations'] : array(),
            'date' => date("Y-m-d"),
            'theme' => sanitize_title_with_dashes( get_stylesheet() )
        )
    );

    $saved_section_post = sek_update_saved_section_post( $section_to_save, $is_edit_metas_only_case );
    if ( is_wp_error( $saved_section_post ) || is_null($saved_section_post) || empty($saved_section_post) ) {
        wp_send_json_error( __FUNCTION__ . ' => error when invoking sek_update_saved_section_post()' );
    } else {
        wp_send_json_success( [ 'section_post_id' => $saved_section_post->ID ] );
    }
}


// SAVE FILTER
function sek_section_save_clean_id( $seks_data = array() ) {
    $new_seks_data = array();
    if ( !is_array( $seks_data ) ) {
        sek_error_log( __FUNCTION__ . ' error => seks_data should be an array');
        return array();
    }

    foreach ( $seks_data as $key => $value ) {
        if ( is_array($value) ) {
            $new_seks_data[$key] = sek_section_save_clean_id( $value );
        } else {
            switch( $key ) {
                case 'id' :
                    if ( is_string( $value ) && false !== strpos( $value, '__nimble__' ) ) {
                        $value = '__rep__me__';
                    }
                break;
            }
            $new_seks_data[$key] = $value;
        }
    }
    return $new_seks_data;
}


////////////////////////////////////////////////////////////////
// SECTION REMOVE
// introduced in may 2020 for https://github.com/presscustomizr/nimble-builder/issues/655
// ENABLED WHEN CONSTANT NIMBLE_SECTION_SAVE_ENABLED === true
add_action( 'wp_ajax_sek_remove_user_section', '\Nimble\sek_ajax_remove_user_section' );
/////////////////////////////////////////////////////////////////
// hook : wp_ajax_sek_remove_user_section
function sek_ajax_remove_user_section() {
    sek_do_ajax_pre_checks( array( 'check_nonce' => true ) );

    // We must have a section_post_name
    if ( empty( $_POST['section_post_name']) || !is_string( $_POST['section_post_name'] ) ) {
        wp_send_json_error( __FUNCTION__ . '_missing_section_post_name' );
    }
    // if ( !isset( $_POST['skope_id'] ) || empty( $_POST['skope_id'] ) ) {
    //     wp_send_json_error( __FUNCTION__ . '_missing_skope_id' );
    // }
    $section_post_to_remove = sek_get_saved_section_post( sanitize_text_field($_POST['section_post_name']) );

    if ( $section_post_to_remove && is_object( $section_post_to_remove ) ) {
        // the CPT is moved to Trash instead of permanently deleted when using wp_delete_post()
        $r = wp_trash_post( $section_post_to_remove->ID );
        if ( is_wp_error( $r ) ) {
            wp_send_json_error( __FUNCTION__ . '_removal_error' );
        }
    } else {
        wp_send_json_error( __FUNCTION__ . '_section_post_not_found' );
    }

    if ( is_wp_error( $section_post_to_remove ) || is_null($section_post_to_remove) || empty($section_post_to_remove) ) {
        wp_send_json_error( __FUNCTION__ . '_removal_error' );
    } else {
        wp_send_json_success( [ 'section_post_removed' => sanitize_text_field($_POST['section_post_name']) ] );
    }
}
?><?php
// WP 5.0.0 compat. until the bug is fixed
// this hook fires before the customize changeset is inserter / updated in database
// Removing the wp_targeted_link_rel callback from the 'content_save_pre' filter prevents corrupting the changeset JSON
// more details in this ticket : https://core.trac.wordpress.org/ticket/45292
add_action( 'customize_save_validation_before', '\Nimble\sek_remove_callback_wp_targeted_link_rel' );
function sek_remove_callback_wp_targeted_link_rel( $wp_customize ) {
    if ( false !== has_filter( 'content_save_pre', 'wp_targeted_link_rel' ) ) {
        remove_filter( 'content_save_pre', 'wp_targeted_link_rel' );
    }
};

?>