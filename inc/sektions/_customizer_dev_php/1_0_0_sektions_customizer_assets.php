<?php
namespace Nimble;
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}



// ENQUEUE CUSTOMIZER JAVASCRIPT + PRINT LOCALIZED DATA
add_action ( 'customize_controls_enqueue_scripts', '\Nimble\sek_enqueue_controls_js_css', 20 );
function sek_enqueue_controls_js_css() {
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


    wp_enqueue_script(
        'czr-sektions',
        //dev / debug mode mode?
        sprintf(
            '%1$s/assets/czr/sek/js/%2$s' ,
            NIMBLE_BASE_URL,
            sek_is_dev_mode() ? 'ccat-sek-control.js' : 'ccat-sek-control.min.js'
        ),
        array( 'czr-skope-base' , 'jquery', 'underscore' ),
        NIMBLE_ASSETS_VERSION,
        $in_footer = true
    );


    wp_localize_script(
        'czr-sektions',
        'sektionsLocalizedData',
        apply_filters( 'nimble-sek-localized-customizer-control-params',
            array(
                'nimbleVersion' => NIMBLE_VERSION,
                'isDevMode' => sek_is_dev_mode(),
                'baseUrl' => NIMBLE_BASE_URL,
                'customizerURL'   => admin_url( 'customize.php' ),
                'sektionsPanelId' => '__sektions__',
                'addNewSektionId' => 'sek_add_new_sektion',
                'addNewColumnId' => 'sek_add_new_column',
                'addNewModuleId' => 'sek_add_new_module',

                'optPrefixForSektionSetting' => NIMBLE_OPT_PREFIX_FOR_SEKTION_COLLECTION,//'nimble___'
                'optNameForGlobalOptions' => NIMBLE_OPT_NAME_FOR_GLOBAL_OPTIONS,//'nimble___'
                'optPrefixForSektionsNotSaved' => NIMBLE_OPT_PREFIX_FOR_LEVEL_UI,//"__nimble__"

                'globalOptionDBValues' => get_option( NIMBLE_OPT_NAME_FOR_GLOBAL_OPTIONS ),// '__nimble_options__'

                'defaultLocalSektionSettingValue' => sek_get_default_location_model(),
                'defaultGlobalSektionSettingValue' => sek_get_default_location_model( NIMBLE_GLOBAL_SKOPE_ID ),

                'settingIdForGlobalSections' => sek_get_seks_setting_id( NIMBLE_GLOBAL_SKOPE_ID ),
                'globalSkopeId' => NIMBLE_GLOBAL_SKOPE_ID,

                'userSavedSektions' => get_option(NIMBLE_OPT_NAME_FOR_SAVED_SEKTIONS),

                //'presetSections' => sek_get_preset_sektions(), <= fetched on demand in ajax

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

                'isSavedSectionEnabled' => defined( 'NIMBLE_SAVED_SECTIONS_ENABLED' ) ? NIMBLE_SAVED_SECTIONS_ENABLED : true,
                'isNimbleHeaderFooterEnabled' => sek_is_header_footer_enabled(),

                'registeredWidgetZones' => array_merge( array( '_none_' => __('Select a widget area', 'text_doma') ), sek_get_registered_widget_areas() ),

                'globalOptionsMap' => SEK_Front_Construct::$global_options_map,
                'localOptionsMap' => SEK_Front_Construct::$local_options_map,

                'registeredLocations' => sek_get_locations(),
                // added for the module tree #359
                'moduleCollection' => sek_get_module_collection(),
                'moduleIconPath' => NIMBLE_MODULE_ICON_PATH,

                'hasActiveCachePlugin' => sek_has_active_cache_plugin(),

                // Tiny MCE
                'idOfDetachedTinyMceTextArea' => NIMBLE_DETACHED_TINYMCE_TEXTAREA_ID,
                'tinyMceNimbleEditorStylesheetUrl' => sprintf( '%1$s/assets/czr/sek/css/sek-tinymce-content.css', NIMBLE_BASE_URL ),
                // defaultToolbarBtns is used for the detached tinymce editor
                'defaultToolbarBtns' => "formatselect,fontsizeselect,forecolor,bold,italic,underline,strikethrough,bullist,numlist,blockquote,alignleft,aligncenter,alignright,link,unlink,spellchecker,hr,pastetext,removeformat,charmap,outdent,indent,undo,redo",
                // basic btns are used for the heading, the quote content and quote cite
                'basic_btns' => array('forecolor','bold','italic','underline','strikethrough','link','unlink'),
                'basic_btns_nolink' => array('forecolor','bold','italic','underline','strikethrough')
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

        if ( ! current_user_can( 'unfiltered_html' ) ) {
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

    if ( ! empty( $settings['codemirror']['lint'] ) ) {
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
            'Pick a module' => __('Pick a module', 'text_doma'),
            'Pick a pre-designed section' => __('Pick a pre-designed section', 'text_doma'),
            'Select a content type' => __('Select a content type', 'text_doma'),

            'The header location only accepts modules and pre-built header sections' => __('The header location only accepts modules and pre-built header sections', 'text_doma'),
            'The footer location only accepts modules and pre-built footer sections' => __('The footer location only accepts modules and pre-built footer sections', 'text_doma'),
            'You can\'t drop a header section in the footer location' => __('You can\'t drop a header section in the footer location', 'text_doma'),
            'You can\'t drop a footer section in the header location' => __('You can\'t drop a footer section in the header location', 'text_doma'),

            'Sections for an introduction' => __('Sections for an introduction', 'text_doma'),
            'Sections for services and features' => __('Sections for services and features', 'text_doma'),
            'Contact-us sections' => __('Contact-us sections', 'text_doma'),
            'Empty sections with columns layout' => __('Empty sections with columns layout', 'text_doma'),
            'Header sections' => __('Header sections', 'text_doma'),
            'Footer sections' => __('Footer sections', 'text_doma'),

            'Module' => __('Module', 'text_doma'),
            'Content for' => __('Content for', 'text_doma'),
            'Customize the options for module :' => __('Customize the options for module :', 'text_doma'),

            'Layout settings for the' => __('Layout settings for the', 'text_doma'),
            'Background settings for the' => __('Background settings for the', 'text_doma'),
            'Borders settings for the' => __('Borders settings for the', 'text_doma'),
            'Padding and margin settings for the' => __('Padding and margin settings for the', 'text_doma'),
            'Height and vertical alignment for the' => __('Height and vertical alignment for the', 'text_doma'),
            'Width settings for the' => __('Width settings for the', 'text_doma'),
            'Custom anchor ( CSS ID ) and CSS classes for the' => __('Custom anchor ( CSS ID ) and CSS classes for the', 'text_doma'),
            'Device visibility settings for the' => __('Device visibility settings for the', 'text_doma'),
            'Responsive settings : breakpoint, column direction' => __('Responsive settings : breakpoint, column direction', 'text_doma'),

            'Settings for the' => __('Settings for the', 'text_doma'),//section / column / module

            // UI global and local options
            'Current page options' => __( 'Current page options', 'text_doma'),
            'Page template' => __( 'Page template', 'text_doma'),
            'This page uses a custom template.' => __( 'This page uses a custom template.', 'text_doma'),
            'Page header and footer' => __( 'Page header and footer', 'text_doma'),
            'Inner and outer widths' => __( 'Inner and outer widths', 'text_doma'),
            'Custom CSS' => __( 'Custom CSS', 'text_doma'),
            'Reset the sections in this page' => __( 'Reset the sections in this page', 'text_doma'),
            'Reset the sections displayed in global locations' => __( 'Reset the sections displayed in global locations', 'text_doma'),
            'Page speed optimizations' => __( 'Page speed optimizations', 'text_doma'),

            'Site wide header and footer' => __( 'Site wide header and footer', 'text_doma'),
            'Site wide breakpoint for Nimble sections' => __( 'Site wide breakpoint for Nimble sections', 'text_doma'),
            'Site wide inner and outer sections widths' => __( 'Site wide inner and outer sections widths', 'text_doma'),

            'Site wide page speed optimizations' => __( 'Site wide page speed optimizations', 'text_doma'),
            'Beta features' => __( 'Beta features', 'text_doma'),
            'Protect your contact forms with Google reCAPTCHA' => __( 'Protect your contact forms with Google reCAPTCHA', 'text_doma'),

            // DEPRECATED
            'Options for the sections of the current page' => __( 'Options for the sections of the current page', 'text_doma'),
            'General options applied for the sections site wide' => __( 'General options applied for the sections site wide', 'text_doma'),
            //

            'Site wide options' => __( 'Site wide options', 'text_doma'),


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
            'Web Safe Fonts' => __('Web Safe Fonts', 'text_doma'),
            'Google Fonts' => __('Google Fonts', 'text_doma'),

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
            'Export failed' => __('Export failed', 'text_doma'),
            'Nothing to export.' => __('Nimble Builder : you have nothing to export. Start adding sections to this page!', 'text_doma'),
            'Import failed' => __('Import failed', 'text_doma'),
            'The current page has no available locations to import Nimble Builder sections.' => __('The current page has no available locations to import Nimble Builder sections.', 'text_doma'),
            'Missing file' => __('Missing file', 'text_doma'),
            'File successfully imported' => __('File successfully imported', 'text_doma'),
            'Import failed, invalid file content' => __('Import failed, invalid file content', 'text_doma'),
            'Import failed, file problem' => __('Import failed, file problem', 'text_doma'),
            'Some image(s) could not be imported' => __('Some image(s) could not be imported', 'text_doma')
            // 'Module' => __('Module', 'text_doma'),
            //

        )//array()
    )//array()
    );//array_merge
}//'nimble_add_i18n_localized_control_params'







// ADD SEKTION VALUES TO EXPORTED DATA IN THE CUSTOMIZER PREVIEW
add_filter( 'skp_json_export_ready_skopes', '\Nimble\add_sektion_values_to_skope_export' );
function add_sektion_values_to_skope_export( $skopes ) {
    if ( ! is_array( $skopes ) ) {
        sek_error_log( __FUNCTION__ . ' error => skp_json_export_ready_skopes filter => the filtered skopes must be an array.' );
    }
    $new_skopes = array();
    foreach ( $skopes as $skp_data ) {
        if ( ! is_array( $skp_data ) || empty( $skp_data['skope'] ) ) {
            sek_error_log( __FUNCTION__ . ' error => missing skope informations' );
            continue;
        }
        if ( 'group' == $skp_data['skope'] ) {
            $new_skopes[] = $skp_data;
            continue;
        }
        if ( ! is_array( $skp_data ) ) {
            error_log( 'skp_json_export_ready_skopes filter => the skope data must be an array.' );
            continue;
        }
        $skope_id = 'global' === $skp_data['skope'] ? NIMBLE_GLOBAL_SKOPE_ID : skp_get_skope_id( $skp_data['skope'] );
        $skp_data[ 'sektions' ] = array(
            'db_values' => sek_get_skoped_seks( $skope_id ),
            'setting_id' => sek_get_seks_setting_id( $skope_id )//nimble___loop_start[skp__post_page_home], nimble___custom_location_id[skp__global]
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
        //         'setting_id' => sek_get_seks_setting_id( $skope_id, $location )//nimble___loop_start[skp__post_page_home]
        //     );
        // }
        $new_skopes[] = $skp_data;
    }

    // sek_error_log( '//////////////////// => new_skopes', $new_skopes);

    return $new_skopes;
}


// @return array() of json decoded sections
// used when js localizing the preset_sections for the customizer
// the transient is refreshed
function sek_get_preset_sektions() {
    $transient_name = 'nimble_preset_sections_' . NIMBLE_VERSION;
    $transient_data = get_transient( $transient_name );
    if ( false == $transient_data || empty( $transient_data ) || sek_is_dev_mode() ) {
        $preset_raw = @file_get_contents( NIMBLE_BASE_PATH ."/assets/preset_sections.json" );
        if ( $preset_raw === false ) {
          $preset_raw = wp_remote_fopen( NIMBLE_BASE_PATH ."/assets/preset_sections.json" );
        }

        $presets_decoded = json_decode( $preset_raw, true );
        set_transient( $transient_name , $presets_decoded , 60*60*24*30 );
    }
    else {
        $presets_decoded = $transient_data;
    }
    return $presets_decoded;
}


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
            <button type="button" class="icon undo" title="<?php _e('Undo', 'text_domain'); ?>" data-nimble-history="undo" data-nimble-state="disabled">
              <span class="screen-reader-text"><?php _e('Undo', 'text_domain'); ?></span>
            </button>
            <button type="button" class="icon do" title="<?php _e('Redo', 'text_domain'); ?>" data-nimble-history="redo" data-nimble-state="disabled">
              <span class="screen-reader-text"><?php _e('Redo', 'text_domain'); ?></span>
            </button>
          </div>
          <div class="sek-settings">
            <button type="button" class="fas fa-sliders-h" title="<?php _e('Global settings', 'text_domain'); ?>" data-nimble-state="enabled">
              <span class="screen-reader-text"><?php _e('Global settings', 'text_domain'); ?></span>
            </button>
          </div>
          <div class="sek-notifications"></div>
          <div class="sek-nimble-doc" data-doc-href="https://docs.presscustomizr.com/collection/334-nimble-builder/?utm_source=usersite&utm_medium=link&utm_campaign=nimble-customizer-topbar">
            <div class="sek-nimble-icon"><img src="<?php echo NIMBLE_BASE_URL.'/assets/img/nimble/nimble_icon.svg?ver='.NIMBLE_VERSION; ?>" alt="<?php _e('Nimble Builder','text_domain_to_replace'); ?>" title="<?php _e('Nimble online documentation', 'text_domain'); ?>"/></div>
            <span class="sek-pointer" title="<?php _e('Nimble online documentation', 'text_domain'); ?>"><?php _e('Nimble online documentation', 'text_domain'); ?></span>
            <button class="far fa-question-circle" type="button" title="<?php _e('Nimble online documentation', 'text_domain'); ?>" data-nimble-state="enabled">
              <span class="screen-reader-text"><?php _e('Nimble online documentation', 'text_domain'); ?></span>
            </button>
          </div>
      </div>
    </script>

    <script type="text/html" id="tmpl-nimble-top-save-ui">
      <div id="nimble-top-save-ui" class="czr-preview-notification">
          <input id="sek-saved-section-id" type="hidden" value="">
          <div class="sek-section-title">
              <label for="sek-saved-section-title" class="customize-control-title"><?php _e('Section title', 'text_doma'); ?></label>
              <input id="sek-saved-section-title" type="text" value="">
          </div>
          <div class="sek-section-description">
              <label for="sek-saved-section-description" class="customize-control-title"><?php _e('Section description', 'text_doma'); ?></label>
              <textarea id="sek-saved-section-description" type="text" value=""></textarea>
          </div>
          <div class="sek-section-save">
              <button class="button sek-do-save-section far fa-save" type="button" title="<?php _e('Save', 'text_domain'); ?>">
                <?php _e('Save', 'text_domain'); ?><span class="screen-reader-text"><?php _e('Save', 'text_domain'); ?></span>
              </button>
          </div>
          <button class="button sek-cancel-save far fa-times-circle" type="button" title="<?php _e('Cancel', 'text_domain'); ?>">
              <?php _e('Cancel', 'text_domain'); ?><span class="screen-reader-text"><?php _e('Cancel', 'text_domain'); ?></span>
          </button>
      </div>
    </script>

    <script type="text/html" id="tmpl-nimble-level-tree">
      <div id="nimble-level-tree">
          <div class="sek-tree-wrap"></div>
          <button class="button sek-close-level-tree far fa-times-circle" type="button" title="<?php _e('Close', 'text_domain'); ?>">
            <?php _e('Close', 'text_domain'); ?><span class="screen-reader-text"><?php _e('Close', 'text_domain'); ?></span>
          </button>
      </div>
    </script>

    <?php // Detached WP Editor => added when coding https://github.com/presscustomizr/nimble-builder/issues/403 ?>
    <div id="czr-customize-content_editor-pane">
      <div data-czr-action="close-tinymce-editor" class="czr-close-editor"><i class="fas fa-arrow-circle-down" title="<?php _e( 'Hide Editor', 'text_doma' ); ?>"></i>&nbsp;<span><?php _e( 'Hide Editor', 'text_doma');?></span></div>
      <div id="czr-customize-content_editor-dragbar" title="<?php _e('Resize the editor', 'text_domain'); ?>">
        <span class="screen-reader-text"><?php _e( 'Resize the editor', 'nimble-builder' ); ?></span>
        <i class="czr-resize-handle fas fa-arrows-alt-v"></i>
      </div>
      <!-- <textarea style="height:250px;width:100%" id="czr-customize-content_editor"></textarea> -->
      <?php
        // the textarea id for the detached editor is 'czr-customize-content_editor'
        // this function generates the <textarea> markup
        sek_setup_nimble_editor( '', NIMBLE_DETACHED_TINYMCE_TEXTAREA_ID , array(
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
      ?>
    </div>
    <?php
}




// Introduced for https://github.com/presscustomizr/nimble-builder/issues/395
function sek_has_active_cache_plugin() {
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
  if ( ! is_multisite() )
    return false;

  $plugins = get_site_option( 'active_sitewide_plugins');
  if ( isset($plugins[$plugin]) )
    return true;

  return false;
}



?>