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
    return array_merge( $params, array(
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
?>