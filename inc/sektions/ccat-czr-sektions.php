<?php
namespace Nimble;
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
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

                'registeredModules' => CZR_Fmk_Base() -> registered_modules,
                'preDropElementClass' => 'sortable-placeholder',
                'dropSelectors' => implode(',', [
                    '.sek-drop-zone', //This is the selector for all eligible drop zones printed statically or dynamically on dragstart
                    'body',// body will not be eligible for drop, but setting the body as drop zone allows us to fire dragenter / dragover actions, like toggling the "approaching" or "close" css class to real drop zone
                    '.sek-content-preset_section-drop-zone'//between sections
                ]),

                'isSavedSectionEnabled' => defined( 'NIMBLE_SAVED_SECTIONS_ENABLED' ) ? NIMBLE_SAVED_SECTIONS_ENABLED : true,
                'isNimbleHeaderFooterEnabled' => sek_is_header_footer_enabled(),

                'registeredWidgetZones' => array_merge( array( '_none_' => __('Select a widget area', 'text_doma') ), sek_get_registered_widget_areas() ),

                'globalOptionsMap' => SEK_Front_Construct::$global_options_map,
                'localOptionsMap' => SEK_Front_Construct::$local_options_map,

                'registeredLocations' => sek_get_locations(),
                'moduleCollection' => sek_get_module_collection(),
                'moduleIconPath' => NIMBLE_MODULE_ICON_PATH,

                'hasActiveCachePlugin' => sek_has_active_cache_plugin(),
                'idOfDetachedTinyMceTextArea' => NIMBLE_DETACHED_TINYMCE_TEXTAREA_ID,
                'tinyMceNimbleEditorStylesheetUrl' => sprintf( '%1$s/assets/czr/sek/css/sek-tinymce-content.css', NIMBLE_BASE_URL ),
                'defaultToolbarBtns' => "formatselect,fontsizeselect,forecolor,bold,italic,underline,strikethrough,bullist,numlist,blockquote,alignleft,aligncenter,alignright,link,unlink,spellchecker,hr,pastetext,removeformat,charmap,outdent,indent,undo,redo",
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
            'Drag and drop content' => __('Drag and drop content', 'text_doma'),
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
            'Current page options' => __( 'Current page options', 'text_doma'),
            'Page template' => __( 'Page template', 'text_doma'),
            'This page uses a custom template.' => __( 'This page uses a custom template.', 'text_doma'),
            'Page header and footer' => __( 'Page header and footer', 'text_doma'),
            'Inner and outer widths' => __( 'Inner and outer widths', 'text_doma'),
            'Custom CSS' => __( 'Custom CSS', 'text_doma'),
            'Reset the sections in this page' => __( 'Reset the sections in this page', 'text_doma'),
            'Page speed optimizations' => __( 'Page speed optimizations', 'text_doma'),

            'Site wide header and footer' => __( 'Site wide header and footer', 'text_doma'),
            'Site wide breakpoint for Nimble sections' => __( 'Site wide breakpoint for Nimble sections', 'text_doma'),
            'Site wide inner and outer sections widths' => __( 'Site wide inner and outer sections widths', 'text_doma'),

            'Site wide page speed optimizations' => __( 'Site wide page speed optimizations', 'text_doma'),
            'Beta features' => __( 'Beta features', 'text_doma'),
            'Protect your contact forms with Google reCAPTCHA' => __( 'Protect your contact forms with Google reCAPTCHA', 'text_doma'),
            'Options for the sections of the current page' => __( 'Options for the sections of the current page', 'text_doma'),
            'General options applied for the sections site wide' => __( 'General options applied for the sections site wide', 'text_doma'),

            'Site wide options' => __( 'Site wide options', 'text_doma'),
            'location' => __('location', 'text_doma'),
            'section' => __('section', 'text_doma'),
            'nested section' => __('nested section', 'text_doma'),
            'column' => __('column', 'text_doma'),
            'module' => __('module', 'text_doma'),
            'This browser does not support drag and drop. You might need to update your browser or use another one.' => __('This browser does not support drag and drop. You might need to update your browser or use another one.', 'text_doma'),
            'You first need to click on a target ( with a + icon ) in the preview.' => __('You first need to click on a target ( with a + icon ) in the preview.', 'text_doma'),
            'Insert here' => __('Insert here', 'text_doma'),
            'Insert in a new section' => __('Insert in a new section', 'text_doma'),
            'Insert a new section here' => __('Insert a new section here', 'text_doma'),
            'Select a font family' => __('Select a font family', 'text_doma'),
            'Web Safe Fonts' => __('Web Safe Fonts', 'text_doma'),
            'Google Fonts' => __('Google Fonts', 'text_doma'),

            'Set a custom url' => __('Set a custom url', 'text_doma'),

            'Something went wrong, please refresh this page.' => __('Something went wrong, please refresh this page.', 'text_doma'),

            'Select an icon'     => __( 'Select an icon', 'text_doma' ),
            'codeEditorSingular'   => __( 'There is %d error in your %s code which might break your site. Please fix it before saving.', 'text_doma' ),
            'codeEditorPlural'     => __( 'There are %d errors in your %s code which might break your site. Please fix them before saving.', 'text_doma' ),
            'Settings on desktops' => __('Settings on desktops', 'text_doma'),
            'Settings on tablets' => __('Settings on tablets', 'text_doma'),
            'Settings on mobiles' => __('Settings on mobiles', 'text_doma'),
            'No sections to navigate' => __('No sections to navigate', 'text_dom'),
            'Remove this element' => __('Remove this element', 'text_dom'),
            'You seem to be using a cache plugin.' => __('You seem to be using a cache plugin.', 'text_dom'),
            'It is recommended to disable your cache plugin when customizing your website.' => __('It is recommended to disable your cache plugin when customizing your website.', 'text_dom'),
            'Revision history of local sections' => __('Revision history of local sections', 'text_doma'),
            'Revision history of global sections' => __('Revision history of global sections', 'text_doma'),
            'The revision could not be restored.' => __('The revision could not be restored.', 'text_doma'),
            'The revision has been successfully restored.' => __('The revision has been successfully restored.', 'text_doma'),
            'Select' => __('Select', 'text_doma'),
            'No revision history available for the moment.' => __('No revision history available for the moment.', 'text_doma'),
            'This is the current version.' => __('This is the current version.', 'text_doma'),
            '(currently published version)' => __('(currently published version)','text_doma')

        )//array()
    )//array()
    );//array_merge
}//'nimble_add_i18n_localized_control_params'
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
        $new_skopes[] = $skp_data;
    }

    return $new_skopes;
}
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



?><?php
/* ------------------------------------------------------------------------- *
 *  SETUP DYNAMIC SERVER REGISTRATION FOR SETTING
/* ------------------------------------------------------------------------- */
if ( ! class_exists( 'SEK_CZR_Dyn_Register' ) ) :
    class SEK_CZR_Dyn_Register {
        static $instance;
        public $sanitize_callbacks = array();// <= will be populated to cache the callbacks when invoking sek_get_module_sanitize_callbacks().

        public static function get_instance( $params ) {
            if ( ! isset( self::$instance ) && ! ( self::$instance instanceof SEK_CZR_Dyn_Register ) )
              self::$instance = new SEK_CZR_Dyn_Register( $params );
            return self::$instance;
        }

        function __construct( $params = array() ) {
            add_action( 'customize_register', array( $this, 'load_nimble_setting_class' ) );

            add_filter( 'customize_dynamic_setting_args', array( $this, 'set_dyn_setting_args' ), 10, 2 );
            add_filter( 'customize_dynamic_setting_class', array( $this, 'set_dyn_setting_class') , 10, 3 );
        }//__construct
        function load_nimble_setting_class() {
            require_once(  NIMBLE_BASE_PATH . '/inc/sektions/seks_setting_class.php' );
        }
        function set_dyn_setting_args( $setting_args, $setting_id ) {
            if ( 0 === strpos( $setting_id, NIMBLE_OPT_PREFIX_FOR_SEKTION_COLLECTION ) || 0 === strpos( $setting_id, NIMBLE_OPT_NAME_FOR_GLOBAL_OPTIONS ) ) {
                return array(
                    'transport' => 'refresh',
                    'type' => 'option',
                    'default' => array(),
                );
            } else if ( 0 === strpos( $setting_id, NIMBLE_OPT_PREFIX_FOR_LEVEL_UI ) ) {
                return array(
                    'transport' => 'refresh',
                    'type' => '_nimble_ui_',//won't be saved as is,
                    'default' => array(),
                    'sanitize_callback' => array( $this, 'sanitize_callback' ),
                    'validate_callback' => array( $this, 'validate_callback' )
                );
            }
            return $setting_args;
        }
        function set_dyn_setting_class( $class, $setting_id, $args ) {
            if ( 0 !== strpos( $setting_id, NIMBLE_OPT_PREFIX_FOR_SEKTION_COLLECTION ) )
              return $class;
            return '\Nimble\Nimble_Customizer_Setting';
        }
        function sanitize_callback( $setting_data, $setting_instance ) {
            if ( isset( $_POST['location_skope_id'] ) ) {
                $sektionSettingValue = sek_get_skoped_seks( $_POST['location_skope_id'] );
                if ( is_array( $sektionSettingValue ) ) {
                    $sektion_collection = array_key_exists('collection', $sektionSettingValue) ? $sektionSettingValue['collection'] : array();
                    if ( is_array( $sektion_collection ) ) {
                        $model = sek_get_level_model( $setting_instance->id, $sektion_collection );
                        if ( is_array( $model ) && ! empty( $model['module_type'] ) ) {
                            $sanitize_callback = sek_get_registered_module_type_property( $model['module_type'], 'sanitize_callback' );
                            if ( ! empty( $sanitize_callback ) && is_string( $sanitize_callback ) && function_exists( $sanitize_callback ) ) {
                                $setting_data = $sanitize_callback( $setting_data );
                            }
                        }
                    }
                }
            }
            return $setting_data;
        }
        function validate_callback( $validity, $setting_data, $setting_instance ) {
            $validated = true;
            if ( isset( $_POST['location_skope_id'] ) ) {
                $sektionSettingValue = sek_get_skoped_seks( $_POST['location_skope_id'] );
                if ( is_array( $sektionSettingValue ) ) {
                    $sektion_collection = array_key_exists('collection', $sektionSettingValue) ? $sektionSettingValue['collection'] : array();
                    if ( is_array( $sektion_collection ) ) {
                        $model = sek_get_level_model( $setting_instance->id, $sektion_collection );
                        if ( is_array( $model ) && ! empty( $model['module_type'] ) ) {
                            $validate_callback = sek_get_registered_module_type_property( $model['module_type'], 'validate_callback' );
                            if ( ! empty( $validate_callback ) && is_string( $validate_callback ) && function_exists( $validate_callback ) ) {
                                $validated = $validate_callback( $setting_data );
                            }
                        }
                    }
                }
            }
            if ( true !== $validated ) {
                if ( is_wp_error( $validated ) ) {
                    $validation_msg = $validation_msg->get_error_message();
                    $validity->add(
                        'nimble_validation_error_in_' . $setting_instance->id ,
                        $validation_msg
                    );
                }

            }
            return $validity;
        }


 }//class
endif;

?><?php
add_action( 'customize_save_validation_before', '\Nimble\sek_remove_callback_wp_targeted_link_rel' );
function sek_remove_callback_wp_targeted_link_rel( $wp_customize ) {
    if ( false !== has_filter( 'content_save_pre', 'wp_targeted_link_rel' ) ) {
        remove_filter( 'content_save_pre', 'wp_targeted_link_rel' );
    }
};

?><?php
function sek_setup_nimble_editor( $content, $editor_id, $settings = array() ) {
  _NIMBLE_Editors::nimble_editor( $content, $editor_id, $settings );
}




/**
 * started from a copy of class-wp-editor.php as of March 2019
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
        'wpautop'             => ! has_blocks(),
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
   * Outputs the HTML for a single instance of the editor.
   *
   * @param string $content The initial content of the editor.
   * @param string $editor_id ID for the textarea and TinyMCE and Quicktags instances (can contain only ASCII letters and numbers).
   * @param array $settings See _NIMBLE_Editors::parse_settings() for description.
   */
  public static function nimble_editor( $content, $editor_id, $settings = array() ) {
    $set            = self::parse_settings( $editor_id, $settings );
    $editor_class   = ' class="' . trim( esc_attr( $set['editor_class'] ) . ' wp-editor-area' ) . '"';
    $tabindex       = $set['tabindex'] ? ' tabindex="' . (int) $set['tabindex'] . '"' : '';
    $default_editor = 'html';
    $buttons        = $autocomplete = '';
    $editor_id_attr = esc_attr( $editor_id );

    if ( $set['drag_drop_upload'] ) {
      self::$drag_drop_upload = true;
    }

    if ( ! empty( $set['editor_height'] ) ) {
      $height = ' style="height: ' . (int) $set['editor_height'] . 'px"';
    } else {
      $height = ' rows="' . (int) $set['textarea_rows'] . '"';
    }

    if ( ! current_user_can( 'upload_files' ) ) {
      $set['media_buttons'] = false;
    }

    if ( self::$this_tinymce ) {
      $autocomplete = ' autocomplete="off"';

      if ( self::$this_quicktags ) {
        $default_editor = $set['default_editor'] ? $set['default_editor'] : wp_default_editor();
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

    echo '<div id="wp-' . $editor_id_attr . '-wrap" class="' . $wrap_class . '">';

    if ( self::$editor_buttons_css ) {
      wp_print_styles( 'editor-buttons' );
      self::$editor_buttons_css = false;
    }

    if ( ! empty( $set['editor_css'] ) ) {
      echo $set['editor_css'] . "\n";
    }

    if ( ! empty( $buttons ) || $set['media_buttons'] ) {
      echo '<div id="wp-' . $editor_id_attr . '-editor-tools" class="wp-editor-tools hide-if-no-js">';

      if ( $set['media_buttons'] ) {
        self::$has_medialib = true;

        if ( ! function_exists( 'media_buttons' ) ) {
          include( ABSPATH . 'wp-admin/includes/media.php' );
        }

        echo '<div id="wp-' . $editor_id_attr . '-media-buttons" class="wp-media-buttons">';

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

      echo '<div class="wp-editor-tabs">' . $buttons . "</div>\n";
      echo "</div>\n";
    }

    $quicktags_toolbar = '';

    if ( self::$this_quicktags ) {
      if ( 'content' === $editor_id && ! empty( $GLOBALS['current_screen'] ) && $GLOBALS['current_screen']->base === 'post' ) {
        $toolbar_id = 'ed_toolbar';
      } else {
        $toolbar_id = 'qt_' . $editor_id_attr . '_toolbar';
      }

      $quicktags_toolbar = '<div id="' . $toolbar_id . '" class="quicktags-toolbar"></div>';
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
      '<div id="wp-' . $editor_id_attr . '-editor-container" class="wp-editor-container">' .
      $quicktags_toolbar .
      '<textarea' . $editor_class . $height . $tabindex . $autocomplete . ' cols="40" name="' . esc_attr( $set['textarea_name'] ) . '" ' .
      'id="' . $editor_id_attr . '">%s</textarea></div>'
    );
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
    if ( self::$this_tinymce ) {
      remove_filter( 'the_editor_content', 'format_for_editor' );
    }
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

    printf( $the_editor, $content );
    echo "\n</div>\n\n";

    self::editor_settings( $editor_id, $set );
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
      add_action( 'customize_controls_print_footer_scripts', array( __CLASS__, 'editor_js' ), 50 );
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

          if ( ! self::$has_medialib ) {
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
            unset( $plugins[ $key ] );
          }

          if ( ! empty( $mce_external_plugins ) ) {

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

            if ( ! empty( $mce_external_languages ) ) {
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
              if ( ! in_array( $name, $loaded_langs, true ) ) {
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

                if ( ! empty( $strings ) ) {
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

        if ( ! empty( $mce_external_plugins ) ) {
          $settings['external_plugins'] = wp_json_encode( $mce_external_plugins );
        }

        /** This filter is documented in wp-admin/includes/media.php */
        if ( apply_filters( 'disable_captions', '' ) ) {
          $settings['wpeditimage_disable_captions'] = true;
        }

        $mce_css       = $settings['content_css'];
        $editor_styles = get_editor_stylesheets();

        if ( ! empty( $editor_styles ) ) {
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

        if ( ! empty( $mce_css ) ) {
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
        $mce_buttons = array( 'formatselect', 'bold', 'italic', 'bullist', 'numlist', 'blockquote', 'alignleft', 'aligncenter', 'alignright', 'link', 'spellchecker' );

        if ( ! wp_is_mobile() ) {
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
          if ( $post_format && ! is_wp_error( $post_format ) ) {
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

      if ( ! empty( $set['tinymce']['body_class'] ) ) {
        $body_class .= ' ' . $set['tinymce']['body_class'];
        unset( $set['tinymce']['body_class'] );
      }

      $mceInit = array(
        'selector'          => "#$editor_id",
        'wpautop'           => (bool) $set['wpautop'],
        'indent'            => ! $set['wpautop'],
        'toolbar1'          => implode( ',', $mce_buttons ),
        'toolbar2'          => implode( ',', $mce_buttons_2 ),
        'toolbar3'          => implode( ',', $mce_buttons_3 ),
        'toolbar4'          => implode( ',', $mce_buttons_4 ),
        'tabfocus_elements' => $set['tabfocus_elements'],
        'body_class'        => $body_class,
      );
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

      if ( empty( $mceInit['toolbar3'] ) && ! empty( $mceInit['toolbar4'] ) ) {
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
      } elseif ( ! empty( $value ) && is_string( $value ) && (
        ( '{' == $value{0} && '}' == $value{strlen( $value ) - 1} ) ||
        ( '[' == $value{0} && ']' == $value{strlen( $value ) - 1} ) ||
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
    if ( did_action( 'wp_enqueue_editor' ) ) {
      return;
    }

    self::enqueue_scripts( true );
    wp_enqueue_style( 'editor-buttons' );

    add_action( 'customize_controls_print_footer_scripts', array( __CLASS__, 'force_uncompressed_tinymce' ), 1 );
    add_action( 'customize_controls_print_footer_scripts', array( __CLASS__, 'print_default_editor_scripts' ), 45 );

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

    ?>
    <script type="text/javascript">
    window.wp = window.wp || {};
    window.wp.editor = window.wp.editor || {};
    window.wp.editor.getDefaultSettings = function() {
      return {
        tinymce: <?php echo $settings; ?>,
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
        baseURL: "<?php echo $baseurl; ?>",
        suffix: "<?php echo $suffix; ?>",
        mceInit: {},
        qtInit: {},
        load_ext: function(url,lang){var sl=tinymce.ScriptLoader;sl.markDone(url+'/langs/'+lang+'.js');sl.markDone(url+'/langs/'+lang+'_dlg.js');}
      };
      <?php
    }
    ?>
    </script>
    <?php

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
      'preview_styles'               => 'font-family font-size font-weight font-style text-decoration text-transform',

      'end_container_on_empty_block' => true,
      'wpeditimage_html5_captions'   => true,
      'wp_lang_attr'                 => get_bloginfo( 'language' ),
      'wp_keep_scroll_position'      => false,
      'wp_shortcut_labels'           => wp_json_encode( $shortcut_labels ),
    );

    $suffix  = SCRIPT_DEBUG ? '' : '.min';
    $version = 'ver=' . get_bloginfo( 'version' );
    $settings['content_css'] = includes_url( "css/dashicons$suffix.css?$version" ) . ',' .
      includes_url( "js/tinymce/skins/wordpress/wp-content.css?$version" );

    return $settings;
  }

  private static function get_translation() {
    if ( empty( self::$translation ) ) {
      self::$translation = array(
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
        'Name'                                 => _x( 'Name', 'Name of link anchor (TinyMCE)' ),
        'Anchor'                               => _x( 'Anchor', 'Link anchor (TinyMCE)' ),
        'Anchors'                              => _x( 'Anchors', 'Link anchors (TinyMCE)' ),
        'Id should start with a letter, followed only by letters, numbers, dashes, dots, colons or underscores.' =>
          __( 'Id should start with a letter, followed only by letters, numbers, dashes, dots, colons or underscores.' ),
        'Id'                                   => _x( 'Id', 'Id for link anchor (TinyMCE)' ),
        'Document properties'                  => __( 'Document properties' ),
        'Robots'                               => __( 'Robots' ),
        'Title'                                => __( 'Title' ),
        'Keywords'                             => __( 'Keywords' ),
        'Encoding'                             => __( 'Encoding' ),
        'Description'                          => __( 'Description' ),
        'Author'                               => __( 'Author' ),
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
        'Insert'                               => _x( 'Insert', 'TinyMCE menu' ),
        'File'                                 => _x( 'File', 'TinyMCE menu' ),
        'Edit'                                 => _x( 'Edit', 'TinyMCE menu' ),
        'Tools'                                => _x( 'Tools', 'TinyMCE menu' ),
        'View'                                 => _x( 'View', 'TinyMCE menu' ),
        'Table'                                => _x( 'Table', 'TinyMCE menu' ),
        'Format'                               => _x( 'Format', 'TinyMCE menu' ),
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
    if ( ! $mce_locale ) {
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
      if ( $key === $value ) {
        unset( $mce_translation[ $key ] );
        continue;
      }

      if ( false !== strpos( $value, '&' ) ) {
        $mce_translation[ $key ] = html_entity_decode( $value, ENT_QUOTES, 'UTF-8' );
      }
    }
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
      if ( ! empty( $init['theme_url'] ) ) {
        $has_custom_theme = true;
        break;
      }
    }

    if ( ! $has_custom_theme ) {
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

    if ( ! isset( $concatenate_scripts ) ) {
      script_concat_settings();
    }

    wp_print_scripts( array( 'wp-tinymce' ) );

    echo "<script type='text/javascript'>\n" . self::wp_mce_translation() . "</script>\n";
  }

  /**
   * Print (output) the TinyMCE configuration and initialization scripts.
   *
   * @global string $tinymce_version
   */
  public static function editor_js() {
    global $tinymce_version;

    $tmce_on = ! empty( self::$mce_settings );
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

    if ( ! empty( self::$qt_settings ) ) {
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
    ?>

    <script type="text/javascript">
    nimbleTinyMCEPreInit = {
      baseURL: "<?php echo $baseurl; ?>",
      suffix: "<?php echo $suffix; ?>",
      <?php

      if ( self::$drag_drop_upload ) {
        echo 'dragDropUpload: true,';
      }

      ?>
      mceInit: <?php echo $mceInit; ?>,
      qtInit: <?php echo $qtInit; ?>,
      ref: <?php echo self::_parse_init( $ref ); ?>,
      load_ext: function(url,lang){var sl=tinymce.ScriptLoader;sl.markDone(url+'/langs/'+lang+'.js');sl.markDone(url+'/langs/'+lang+'_dlg.js');}
    };
    </script>
    <?php

    if ( $tmce_on ) {
      self::print_tinymce_scripts();

      if ( self::$ext_plugins ) {
        echo "<script type='text/javascript' src='{$baseurl}/langs/wp-langs-en.js?$version'></script>\n";
      }
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

    ?>
    <script type="text/javascript">
    <?php

    if ( self::$ext_plugins ) {
      echo self::$ext_plugins . "\n";
    }

    if ( ! is_admin() ) {
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

          if ( ( $wrap.hasClass( 'tmce-active' ) || ! nimbleTinyMCEPreInit.qtInit.hasOwnProperty( id ) ) && ! init.wp_skip_init ) {
            tinymce.init( init );
            if ( ! window.wpActiveEditor ) {
              window.wpActiveEditor = id;//<= where is this used ?
            }
          }
        }
      }

      if ( typeof quicktags !== 'undefined' ) {
        for ( id in nimbleTinyMCEPreInit.qtInit ) {
          quicktags( nimbleTinyMCEPreInit.qtInit[id] );

          if ( ! window.wpActiveEditor ) {
            window.wpActiveEditor = id;//<= where is this used ?
          }
        }
      }
    }());
    </script>
    <?php

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
    $get_posts = new WP_Query;
    $posts     = $get_posts->query( $query );
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

    return ! empty( $results ) ? $results : false;
  }

  /**
   * Dialog for internal linking.
   *
   * @since 3.1.0
   */
  public static function wp_link_dialog() {
    if ( self::$link_dialog_printed ) {
      return;
    }

    self::$link_dialog_printed = true;
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
add_action( 'czr_set_input_tmpl_content', '\Nimble\sek_set_input_tmpl_content', 10, 3 );
function sek_set_input_tmpl_content( $input_type, $input_id, $input_data ) {
    if ( ! array_key_exists( 'input_type', $input_data ) || empty( $input_data[ 'input_type' ] ) ) {
         wp_send_json_error( 'sek_set_input_tmpl_content => missing input type for input id : ' . $input_id );
    }
    switch( $input_type ) {
        case 'content_type_switcher' :
            sek_set_input_tmpl___content_type_switcher( $input_id, $input_data );
        break;
        case 'module_picker' :
            sek_set_input_tmpl___module_picker( $input_id, $input_data );
        break;
        case 'section_picker' :
            sek_set_input_tmpl___section_picker( $input_id, $input_data );
        break;

        case 'spacing' :
        case 'spacingWithDeviceSwitcher' :
            sek_set_input_tmpl___spacing( $input_id, $input_data );
        break;
        case 'bg_position' :
        case 'bgPositionWithDeviceSwitcher' :
            sek_set_input_tmpl___bg_position( $input_id, $input_data );
        break;
        case 'h_alignment' :
        case 'horizAlignmentWithDeviceSwitcher' :
            sek_set_input_tmpl___h_alignment( $input_id, $input_data );
        break;
        case 'h_text_alignment' :
        case 'horizTextAlignmentWithDeviceSwitcher' :
            sek_set_input_tmpl___h_text_alignment( $input_id, $input_data );
        break;
        case 'verticalAlignWithDeviceSwitcher' :
            sek_set_input_tmpl___v_alignment( $input_id, $input_data );
        break;
        case 'font_picker' :
            sek_set_input_tmpl___font_picker( $input_id, $input_data );
        break;
        case 'fa_icon_picker' :
            sek_set_input_tmpl___fa_icon_picker( $input_id, $input_data );
        break;
        case 'font_size' :
        case 'line_height' :
            sek_set_input_tmpl___font_size_line_height( $input_id, $input_data );
        break;
        case 'code_editor' :
            sek_set_input_tmpl___code_editor( $input_id, $input_data );
        break;
        case 'range_with_unit_picker' :
            sek_set_input_tmpl___range_with_unit_picker( $input_id, $input_data );
        break;
        case 'range_with_unit_picker_device_switcher' :
            sek_set_input_tmpl___range_with_unit_picker_device_switcher( $input_id, $input_data );
        break;
        case 'range_simple' :
            sek_set_input_tmpl___range_simple( $input_id, $input_data );
        break;
        case 'borders' :
            sek_set_input_tmpl___borders( $input_id, $input_data );
        break;
        case 'border_radius' :
            sek_set_input_tmpl___border_radius( $input_id, $input_data );
        break;
        case 'buttons_choice' :
            sek_set_input_tmpl___buttons_choice( $input_id, $input_data );
        break;
        case 'reset_button' :
            sek_set_input_tmpl___reset_button( $input_id, $input_data );
        break;
        case 'revision_history' :
            sek_set_input_tmpl___revision_history( $input_id, $input_data );
        break;
        case 'detached_tinymce_editor' :
            sek_set_input_tmpl___detached_tinymce_editor( $input_id, $input_data );
        break;
        case 'nimble_tinymce_editor' :
            sek_set_input_tmpl___nimble_tinymce_editor( $input_id, $input_data );
        break;
    }
}
?><?php

/* ------------------------------------------------------------------------- *
 *  CONTENT TYPE SWITCHER INPUT
/* ------------------------------------------------------------------------- */
function sek_set_input_tmpl___content_type_switcher( $input_id, $input_data ) {
    ?>
        <input data-czrtype="<?php echo $input_id; ?>" type="hidden"/>
        <div class="sek-content-type-wrapper">
            <div aria-label="<?php _e( 'Content type', 'text_domain'); ?>" class="sek-ui-button-group" role="group">
                <button type="button" aria-pressed="false" class="sek-ui-button" title="<?php _e('Pick a section', 'text_domain');?>" data-sek-content-type="section"><?php _e('Pick a section', 'text_domain');?></button>
                <button type="button" aria-pressed="false" class="sek-ui-button" title="<?php _e('Pick a module', 'text_domain');?>" data-sek-content-type="module"><?php _e('Pick a module', 'text_domain');?></button>
            </div>
        </div>
  <?php
}


/* ------------------------------------------------------------------------- *
 *  MODULE PICKER INPUT
/* ------------------------------------------------------------------------- */
function sek_set_input_tmpl___module_picker( $input_id, $input_data ) {
    ?>
        <input data-czrtype="<?php echo $input_id; ?>" type="hidden"/>
        <div class="sek-content-type-wrapper">
          <?php
            $content_collection = sek_get_module_collection();

            $i = 0;
            foreach( $content_collection as $_params ) {
                $_params = wp_parse_args( $_params, array(
                    'content-type' => 'module',
                    'content-id' => '',
                    'title' => '',
                    'icon' => '',
                    'font_icon' => '',
                    'active' => true
                ));

                $icon_img_html = '<i style="color:red">Missing Icon</i>';
                if ( !empty( $_params['icon'] ) ) {
                    $icon_img_src = NIMBLE_MODULE_ICON_PATH . $_params['icon'];
                    $icon_img_html = '<img draggable="false" title="'. $_params['title'] . '" alt="'. $_params['title'] . '" class="nimble-module-icons" src="' . $icon_img_src .'"/>';
                } else if ( !empty( $_params['font_icon'] ) ) {
                    $icon_img_html = $_params['font_icon'];
                }

                printf('<div draggable="%7$s" data-sek-content-type="%1$s" data-sek-content-id="%2$s" title="%5$s"><div class="sek-module-icon %6$s">%3$s</div><div class="sek-module-title"><div class="sek-centered-module-title">%4$s</div></div></div>',
                      $_params['content-type'],
                      $_params['content-id'],
                      $icon_img_html,
                      $_params['title'],
                      true === $_params['active'] ? __('Drag and drop or double-click to insert in your chosen target element.', 'text_doma' ) : __('Available soon ! This module is currently in beta, you can activate it in Site Wide Options > Beta features', 'text_doma'),
                      !empty( $_params['font_icon'] ) ? 'is-font-icon' : '',
                      true === $_params['active'] ? 'true' : 'false'
                );
            }
          ?>
        </div>
    <?php
}









/* ------------------------------------------------------------------------- *
 *  SECTION PICKER INPUT
/* ------------------------------------------------------------------------- */
function sek_set_input_tmpl___section_picker( $input_id, $input_data ) {
    ?>
        <input data-czrtype="<?php echo $input_id; ?>" type="hidden"/>
        <div class="sek-content-type-wrapper">
          <?php
            $content_collection = array();
            switch( $input_id ) {
                case 'intro_sections' :
                    $content_collection = array(
                        array(
                            'content-type' => 'preset_section',
                            'content-id' => 'intro_three',
                            'title' => __('1 columns, call to action, full-width background', 'text-domain' ),
                            'thumb' => 'intro_three.jpg'
                        ),
                        array(
                            'content-type' => 'preset_section',
                            'content-id' => 'intro_one',
                            'title' => __('1 column, full-width background', 'text-domain' ),
                            'thumb' => 'intro_one.jpg'
                        ),
                        array(
                            'content-type' => 'preset_section',
                            'content-id' => 'intro_two',
                            'title' => __('2 columns, call to action, full-width background', 'text-domain' ),
                            'thumb' => 'intro_two.jpg'
                        )
                    );
                break;
                case 'features_sections' :
                    $content_collection = array(
                        array(
                            'content-type' => 'preset_section',
                            'content-id' => 'features_one',
                            'title' => __('3 columns with icon and call to action', 'text-domain' ),
                            'thumb' => 'features_one.jpg',
                        ),
                        array(
                            'content-type' => 'preset_section',
                            'content-id' => 'features_two',
                            'title' => __('3 columns with icon', 'text-domain' ),
                            'thumb' => 'features_two.jpg',
                        )
                    );
                break;
                case 'contact_sections' :
                    $content_collection = array(
                        array(
                            'content-type' => 'preset_section',
                            'content-id' => 'contact_one',
                            'title' => __('A contact form and a Google map', 'text-domain' ),
                            'thumb' => 'contact_one.jpg',
                        ),
                        array(
                            'content-type' => 'preset_section',
                            'content-id' => 'contact_two',
                            'title' => __('A contact form with an image background', 'text-domain' ),
                            'thumb' => 'contact_two.jpg',
                        )
                    );
                break;
                case 'layout_sections' :
                    $content_collection = array(
                        array(
                            'content-type' => 'preset_section',
                            'content-id' => 'two_columns',
                            'title' => __('two columns layout', 'text-domain' ),
                            'thumb' => 'two_columns.jpg'
                        ),
                        array(
                            'content-type' => 'preset_section',
                            'content-id' => 'three_columns',
                            'title' => __('three columns layout', 'text-domain' ),
                            'thumb' => 'three_columns.jpg'
                        ),
                        array(
                            'content-type' => 'preset_section',
                            'content-id' => 'four_columns',
                            'title' => __('four columns layout', 'text-domain' ),
                            'thumb' => 'four_columns.jpg'
                        ),
                    );
                break;
                case 'header_sections' :
                    $content_collection = array(
                        array(
                            'content-type' => 'preset_section',
                            'content-id' => 'header_one',
                            'title' => __('simple header with a logo on the right, menu on the left', 'text-domain' ),
                            'thumb' => 'header_one.jpg',
                            'height' => '33px'
                        )
                    );
                break;
                case 'footer_sections' :
                    $content_collection = array(
                        array(
                            'content-type' => 'preset_section',
                            'content-id' => 'footer_one',
                            'title' => __('simple footer with 3 columns and large bottom zone', 'text-domain' ),
                            'thumb' => 'footer_one.jpg'
                        )
                    );
                break;
            }
            foreach( $content_collection as $_params) {
                $section_type = 'content';
                if ( false !== strpos($_params['content-id'], 'header_') ) {
                    $section_type = 'header';
                } else if ( false !== strpos($_params['content-id'], 'footer_') ) {
                    $section_type = 'footer';
                }

                printf('<div draggable="true" data-sek-content-type="%1$s" data-sek-content-id="%2$s" style="%3$s" title="%4$s" data-sek-section-type="%5$s"><div class="sek-overlay"></div></div>',
                    $_params['content-type'],
                    $_params['content-id'],
                    sprintf( 'background: url(%1$s) 50% 50% / cover no-repeat;%2$s',
                        NIMBLE_BASE_URL . '/assets/img/section_assets/thumbs/' . $_params['thumb'] . '?ver=' . NIMBLE_VERSION,
                        isset( $_params['height'] ) ? 'height:'.$_params['height'] : ''
                    ),
                    $_params['title'],
                    $section_type
                );
            }
          ?>
        </div>
  <?php
}

?><?php

/* ------------------------------------------------------------------------- *
 *  SPACING INPUT
/* ------------------------------------------------------------------------- */
function sek_set_input_tmpl___spacing( $input_id, $input_data ) {
    ?>
    <input data-czrtype="<?php echo $input_id; ?>" type="hidden"/>
    <div class="sek-spacing-wrapper">
        <div class="sek-pad-marg-inner">
          <div class="sek-pm-top-bottom-wrap sek-flex-justify-center">
            <div class="sek-flex-center-stretch" data-sek-spacing="margin-top" title="<?php _e('Margin top', 'text-domain'); ?>">
              <div class="sek-pm-input-parent">
                <input class="sek-pm-input" value="" type="number"  >
              </div>
            </div>
          </div>
          <div class="sek-pm-middle-wrap sek-flex-justify-center">
            <div class="sek-flex-center-stretch sek-pm-margin-left" data-sek-spacing="margin-left" title="<?php _e('Margin left', 'text-domain'); ?>">
              <div class="sek-pm-input-parent">
                <input class="sek-pm-input" value="" type="number"  >
              </div>
            </div>

            <div class="sek-pm-padding-wrapper">
              <div class="sek-flex-justify-center">
                <div class="sek-flex-center-stretch" data-sek-spacing="padding-top" title="<?php _e('Padding top', 'text-domain'); ?>">
                  <div class="sek-pm-input-parent">
                    <input class="sek-pm-input" value="" type="number"  >
                  </div>
                </div>
              </div>
                <div class="sek-flex-justify-center sek-flex-space-between">
                  <div class="sek-flex-center-stretch" data-sek-spacing="padding-left" title="<?php _e('Padding left', 'text-domain'); ?>">
                    <div class="sek-pm-input-parent">
                      <input class="sek-pm-input" value="" type="number"  >
                    </div>
                  </div>
                  <div class="sek-flex-center-stretch" data-sek-spacing="padding-right" title="<?php _e('Padding right', 'text-domain'); ?>">
                    <div class="sek-pm-input-parent">
                      <input class="sek-pm-input" value="" type="number"  >
                    </div>
                  </div>
                </div>
              <div class="sek-flex-justify-center">
                <div class="sek-flex-center-stretch" data-sek-spacing="padding-bottom" title="<?php _e('Padding bottom', 'text-domain'); ?>">
                  <div class="sek-pm-input-parent">
                    <input class="sek-pm-input" value="" type="number"  >
                  </div>
                </div>
              </div>
            </div>

            <div class="sek-flex-center-stretch sek-pm-margin-right" data-sek-spacing="margin-right" title="<?php _e('Margin right', 'text-domain'); ?>">
              <div class="sek-pm-input-parent">
                <input class="sek-pm-input" value="" type="number"  >
              </div>
            </div>
          </div>

          <div class="sek-pm-top-bottom-wrap sek-flex-justify-center">
            <div class="sek-flex-center-stretch" data-sek-spacing="margin-bottom" title="<?php _e('Margin bottom', 'text-domain'); ?>">
              <div class="sek-pm-input-parent">
                <input class="sek-pm-input" value="" type="number"  >
              </div>
            </div>
          </div>
        </div><?php //sek-pad-marg-inner ?>
        <div class="sek-unit-wrapper">
          <div aria-label="<?php _e( 'unit', 'text_domain'); ?>" class="sek-ui-button-group" role="group">
                <button type="button" aria-pressed="false" class="sek-ui-button" title="<?php _e('pixels', 'text_domain');?>" data-sek-unit="px"><?php _e('px', 'text_domain');?></button><button type="button" aria-pressed="false" class="sek-ui-button" title="<?php _e('em', 'text_domain');?>" data-sek-unit="em"><?php _e('em', 'text_domain');?></button><button type="button" aria-pressed="false" class="sek-ui-button" title="<?php _e('percents', 'text_domain');?>" data-sek-unit="%"><?php _e('%', 'text_domain');?></button></div>
        </div>
        <div class="reset-spacing-wrap"><span class="sek-do-reset"><?php _e('Reset all spacing', 'text_doma' ); ?></span></div>

    </div><?php // sek-spacing-wrapper ?>
    <?php
}

?><?php

/* ------------------------------------------------------------------------- *
 *  BACKGROUND POSITION INPUT
/* ------------------------------------------------------------------------- */
function sek_set_input_tmpl___bg_position( $input_id, $input_data ) {
    ?>
        <div class="sek-bg-pos-wrapper">
          <input data-czrtype="<?php echo $input_id; ?>" type="hidden"/>
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
    <?php
}

?><?php

/* ------------------------------------------------------------------------- *
 *  HORIZONTAL ALIGNMENT INPUT
/* ------------------------------------------------------------------------- */
/* ------------------------------------------------------------------------- *
 *  HORIZONTAL ALIGNMENT INPUT FOR TEXT => includes the 'justify' icon
/* ------------------------------------------------------------------------- */
function sek_set_input_tmpl___h_alignment( $input_id, $input_data ) {
    ?>
        <div class="sek-h-align-wrapper">
          <input data-czrtype="<?php echo $input_id; ?>" type="hidden"/>
          <div class="sek-align-icons">
            <div data-sek-align="left" title="<?php _e('Align left','text_domain_to_be_translated'); ?>"><i class="material-icons">format_align_left</i></div>
            <div data-sek-align="center" title="<?php _e('Align center','text_domain_to_be_translated'); ?>"><i class="material-icons">format_align_center</i></div>
            <div data-sek-align="right" title="<?php _e('Align right','text_domain_to_be_translated'); ?>"><i class="material-icons">format_align_right</i></div>
          </div>
        </div><?php // sek-h-align-wrapper ?>
    <?php
}

function sek_set_input_tmpl___h_text_alignment( $input_id, $input_data ) {
    ?>
        <div class="sek-h-align-wrapper">
          <input data-czrtype="<?php echo $input_id; ?>" type="hidden"/>
          <div class="sek-align-icons">
            <div data-sek-align="left" title="<?php _e('Align left','text_domain_to_be_translated'); ?>"><i class="material-icons">format_align_left</i></div>
            <div data-sek-align="center" title="<?php _e('Align center','text_domain_to_be_translated'); ?>"><i class="material-icons">format_align_center</i></div>
            <div data-sek-align="right" title="<?php _e('Align right','text_domain_to_be_translated'); ?>"><i class="material-icons">format_align_right</i></div>
            <div data-sek-align="justify" title="<?php _e('Justified','text_domain_to_be_translated'); ?>"><i class="material-icons">format_align_justify</i></div>
          </div>
        </div><?php // sek-h-align-wrapper ?>
    <?php
}
?><?php
/* ------------------------------------------------------------------------- *
 *  VERTICAL ALIGNMENT INPUT
/* ------------------------------------------------------------------------- */
function sek_set_input_tmpl___v_alignment( $input_id, $input_data ) {
    ?>
        <div class="sek-v-align-wrapper">
          <input data-czrtype="<?php echo $input_id; ?>" type="hidden"/>
          <div class="sek-align-icons">
            <div data-sek-align="top" title="<?php _e('Align top','text_domain_to_be_translated'); ?>"><i class="material-icons">vertical_align_top</i></div>
            <div data-sek-align="center" title="<?php _e('Align center','text_domain_to_be_translated'); ?>"><i class="material-icons">vertical_align_center</i></div>
            <div data-sek-align="bottom" title="<?php _e('Align bottom','text_domain_to_be_translated'); ?>"><i class="material-icons">vertical_align_bottom</i></div>
          </div>
        </div><?php // sek-h-align-wrapper ?>
    <?php
}

?><?php
/* ------------------------------------------------------------------------- *
 *  FONT AWESOME ICON PICKER INPUT
/* ------------------------------------------------------------------------- */
function sek_set_input_tmpl___fa_icon_picker( $input_id, $input_data ) {
    ?>
        <select data-czrtype="<?php echo $input_id; ?>"></select>
    <?php
}
add_filter( "ac_set_ajax_czr_tmpl___fa_icon_picker_input", '\Nimble\sek_get_fa_icon_list_tmpl', 10, 3 );
function sek_get_fa_icon_list_tmpl( $html, $requested_tmpl = '', $posted_params = array() ) {
    if ( empty( $requested_tmpl ) ) {
        wp_send_json_error( __FUNCTION__ . ' => the requested tmpl is empty' );
    }

    return wp_json_encode(
        sek_retrieve_decoded_font_awesome_icons()
    );//will be sent by wp_send_json_success() in ::ac_set_ajax_czr_tmpl()
}
function sek_retrieve_decoded_font_awesome_icons() {
    $faicons_json_path      = NIMBLE_BASE_PATH . '/assets/faicons.json';
    $faicons_transient_name = 'sek_font_awesome_november_2018';
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

?>
<?php
/* ------------------------------------------------------------------------- *
 *  FONT PICKER INPUT
/* ------------------------------------------------------------------------- */
function sek_set_input_tmpl___font_picker( $input_id, $input_data ) {
    ?>
        <select data-czrtype="<?php echo $input_id; ?>"></select>
    <?php
}
add_filter( "ac_set_ajax_czr_tmpl___font_picker_input", '\Nimble\sek_get_font_list_tmpl', 10, 3 );
function sek_get_font_list_tmpl( $html, $requested_tmpl = '', $posted_params = array() ) {
    if ( empty( $requested_tmpl ) ) {
        wp_send_json_error( __FUNCTION__ . ' => the requested tmpl is empty' );
    }

    return wp_json_encode( array(
        'cfonts' => sek_get_cfonts(),
        'gfonts' => sek_get_gfonts(),
    ) );//will be sent by wp_send_json_success() in ::ac_set_ajax_czr_tmpl()
}


function sek_get_cfonts() {
    $cfonts = array();
    $raw_cfonts = array(
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
      $cfonts[] = array(
          'name'    => $font ,
          'subsets'   => array()
      );
    }
    return apply_filters( 'sek_font_picker_cfonts', $cfonts );
}
function sek_retrieve_decoded_gfonts() {
    if ( false == get_transient( 'sek_gfonts_may_2018' ) ) {
        $gfont_raw      = @file_get_contents( NIMBLE_BASE_PATH ."/assets/webfonts.json" );

        if ( $gfont_raw === false ) {
          $gfont_raw = wp_remote_fopen( NIMBLE_BASE_PATH ."/assets/webfonts.json" );
        }

        $gfonts_decoded   = json_decode( $gfont_raw, true );
        set_transient( 'sek_gfonts_may_2018' , $gfonts_decoded , 60*60*24*3000 );
    }
    else {
      $gfonts_decoded = get_transient( 'sek_gfonts_may_2018' );
    }

    return $gfonts_decoded;
}
function sek_get_gfonts( $what = null ) {

  $gfonts_decoded = sek_retrieve_decoded_gfonts();
  $gfonts = array();

  foreach ( $gfonts_decoded['items'] as $font ) {
    foreach ( $font['variants'] as $variant ) {
      $name     = str_replace( ' ', '+', $font['family'] );
      $gfonts[]   = array(
          'name'    => $name . ':' .$variant
      );
    }
  }

  return ('subsets' == $what) ? apply_filters( 'sek_font_picker_gfonts_subsets ', $subsets ) : apply_filters( 'sek_font_picker_gfonts', $gfonts )  ;
}

?><?php

/* ------------------------------------------------------------------------- *
 *  FONT SIZE
/* ------------------------------------------------------------------------- */
/* ------------------------------------------------------------------------- *
 *  LINE HEIGHT INPUT TMPLS
/* ------------------------------------------------------------------------- */
function sek_set_input_tmpl___font_size_line_height( $input_id, $input_data ) {
    ?>
      <?php
          ?>
          <#
            var value = data['<?php echo $input_id; ?>'],
                unit = data['<?php echo $input_id; ?>'];
            value = _.isString( value ) ? value.replace(/px|em|%/g,'') : '';
            unit = _.isString( unit ) ? unit.replace(/[0-9]|\.|,/g, '') : 'px';
            unit = _.isEmpty( unit ) ? 'px' : unit;
          #>
        <div class="sek-font-size-line-height-wrapper">
          <input data-czrtype="<?php echo $input_id; ?>" type="hidden" data-sek-unit="{{ unit }}"/>

          <?php
              printf( '<input type="number" %1$s %2$s %3$s value="{{ value }}" />',
                  ! empty( $input_data['step'] ) ? 'step="'. $input_data['step'] .'"' : '',
                  ! empty( $input_data['min'] ) ? 'min="'. $input_data['min'] .'"' : '',
                  ! empty( $input_data['max'] ) ? 'max="'. $input_data['max'] .'"' : ''
                );
          ?>
          <div aria-label="<?php _e( 'unit', 'text_domain'); ?>" class="sek-ui-button-group sek-float-right" role="group">
              <button type="button" aria-pressed="false" class="sek-ui-button" title="<?php _e('pixels', 'text_domain');?>" data-sek-unit="px"><?php _e('px', 'text_domain');?></button><button type="button" aria-pressed="false" class="sek-ui-button" title="<?php _e('em', 'text_domain');?>" data-sek-unit="em"><?php _e('em', 'text_domain');?></button><button type="button" aria-pressed="false" class="sek-ui-button" title="<?php _e('percents', 'text_domain');?>" data-sek-unit="%"><?php _e('%', 'text_domain');?></button></div>
        </div><?php // sek-font-size-wrapper ?>
    <?php
}
?><?php
/* ------------------------------------------------------------------------- *
 *  CODE EDITOR INPUT TEMPLATE
/* ------------------------------------------------------------------------- */
function sek_set_input_tmpl___code_editor( $input_id, $input_data ) {
    /*
    * Needed to form the correct params to pass to the code mirror editor, based on the code type
    */
    $code_editor_params = nimble_get_code_editor_settings( array(
        'type' => ! empty( $input_data[ 'code_type' ] ) ? $input_data[ 'code_type' ] : 'text/html',
    ));
    ?>
        <textarea data-czrtype="<?php echo $input_id; ?>" class="width-100" name="textarea" rows="10" cols="" data-editor-params="<?php echo htmlspecialchars( json_encode( $code_editor_params ) ); ?>">{{ data.value }}</textarea>
    <?php
}
?>
<?php
/* ------------------------------------------------------------------------- *
 *  CODE EDITOR INPUT TEMPLATE
/* ------------------------------------------------------------------------- */
function sek_set_input_tmpl___range_simple( $input_id, $input_data ) {
    ?>
    <?php
    ?>
    <#
      var value = data['<?php echo $input_id; ?>'],
          unit = data['<?php echo $input_id; ?>'];
      value = _.isString( value ) ? value.replace(/px|em|%/g,'') : value;
      unit = _.isString( unit ) ? unit.replace(/[0-9]|\.|,/g, '') : 'px';
      unit = _.isEmpty( unit ) ? 'px' : unit;
    #>
    <div class="sek-range-with-unit-picker-wrapper sek-no-unit-picker">
        <?php //<# //console.log( 'IN php::sek_set_input_tmpl___range_simple() => data range_slide => ', data ); #> ?>
        <div class="sek-range-wrapper">
          <input data-czrtype="<?php echo $input_id; ?>" type="hidden" data-sek-unit="{{ unit }}"/>
          <?php
          printf( '<input class="sek-range-input" type="range" %1$s %2$s %3$s %4$s/>',
            ! empty( $input_data['step'] ) ? 'step="'. $input_data['step'] .'"' : '',
            ! empty( $input_data['unit'] ) ? 'data-unit="'. $input_data['unit'] .'"' : '',
            ! empty( $input_data['min'] ) ? 'min="'. $input_data['min'] .'"' : 'min="0"',
            ! empty( $input_data['max'] ) ? 'max="'. $input_data['max'] .'"' : ''
          );
          ?>
        </div>
        <div class="sek-number-wrapper">
            <input class="sek-pm-input" value="{{ value }}" type="number"  >
        </div>
    </div><?php // sek-spacing-wrapper ?>
  <?php
}
?>
<?php
/* ------------------------------------------------------------------------- *
 *  CODE EDITOR INPUT TEMPLATE
/* ------------------------------------------------------------------------- */
function sek_set_input_tmpl___range_with_unit_picker( $input_id, $input_data ) {
    ?>
    <?php
    ?>
    <#
      var value = data['<?php echo $input_id; ?>'],
          unit = data['<?php echo $input_id; ?>'];
      value = _.isString( value ) ? value.replace(/px|em|%/g,'') : value;
      unit = _.isString( unit ) ? unit.replace(/[0-9]|\.|,/g, '') : 'px';
      unit = _.isEmpty( unit ) ? 'px' : unit;
    #>
    <div class="sek-range-with-unit-picker-wrapper">
        <?php //<# //console.log( 'IN php::sek_set_input_tmpl___range_with_unit_picker() => data range_slide => ', data ); #> ?>
        <div class="sek-range-wrapper">
          <input data-czrtype="<?php echo $input_id; ?>" type="hidden" data-sek-unit="{{ unit }}"/>
          <?php
          printf( '<input class="sek-range-input" type="range" %1$s %2$s %3$s %4$s/>',
            ! empty( $input_data['step'] ) ? 'step="'. $input_data['step'] .'"' : '',
            ! empty( $input_data['unit'] ) ? 'data-unit="'. $input_data['unit'] .'"' : '',
            ! empty( $input_data['min'] ) ? 'min="'. $input_data['min'] .'"' : 'min="0"',
            ! empty( $input_data['max'] ) ? 'max="'. $input_data['max'] .'"' : ''
          );
          ?>
        </div>
        <div class="sek-number-wrapper">
            <input class="sek-pm-input" value="{{ value }}" type="number"  >
        </div>
        <div class="sek-unit-wrapper">
          <div aria-label="<?php _e( 'unit', 'text_domain'); ?>" class="sek-ui-button-group" role="group">
                <button type="button" aria-pressed="false" class="sek-ui-button" title="<?php _e('pixels', 'text_domain');?>" data-sek-unit="px"><?php _e('px', 'text_domain');?></button><button type="button" aria-pressed="false" class="sek-ui-button" title="<?php _e('em', 'text_domain');?>" data-sek-unit="em"><?php _e('em', 'text_domain');?></button><button type="button" aria-pressed="false" class="sek-ui-button" title="<?php _e('percents', 'text_domain');?>" data-sek-unit="%"><?php _e('%', 'text_domain');?></button></div>
        </div>
    </div><?php // sek-spacing-wrapper ?>
  <?php
}
?>
<?php
/* ------------------------------------------------------------------------- *
 *  CODE EDITOR INPUT TEMPLATE
/* ------------------------------------------------------------------------- */
function sek_set_input_tmpl___range_with_unit_picker_device_switcher( $input_id, $input_data ) {
    ?>
    <?php
    ?>
    <#
      var value = data['<?php echo $input_id; ?>'],
          unit = data['<?php echo $input_id; ?>'];
      value = _.isString( value ) ? value.replace(/px|em|%/g,'') : value;
      unit = _.isString( unit ) ? unit.replace(/[0-9]|\.|,/g, '') : 'px';
      unit = _.isEmpty( unit ) ? 'px' : unit;
    #>
    <div class="sek-range-with-unit-picker-wrapper">
        <?php //<# //console.log( 'IN php::sek_set_input_tmpl___range_with_unit_picker_device_switcher() => data range_slide => ', data ); #> ?>
        <div class="sek-range-wrapper">
          <input data-czrtype="<?php echo $input_id; ?>" type="hidden" data-sek-unit="{{ unit }}"/>
          <?php
          printf( '<input class="sek-range-input" type="range" %1$s %2$s %3$s %4$s/>',
            ! empty( $input_data['step'] ) ? 'step="'. $input_data['step'] .'"' : '',
            ! empty( $input_data['unit'] ) ? 'data-unit="'. $input_data['unit'] .'"' : '',
            ! empty( $input_data['min'] ) ? 'min="'. $input_data['min'] .'"' : 'min="0"',
            ! empty( $input_data['max'] ) ? 'max="'. $input_data['max'] .'"' : ''
          );
          ?>
        </div>
        <div class="sek-number-wrapper">
            <input class="sek-pm-input" value="{{ value }}" type="number"  >
        </div>
        <div class="sek-unit-wrapper">
          <div aria-label="<?php _e( 'unit', 'text_domain'); ?>" class="sek-ui-button-group" role="group">
                <button type="button" aria-pressed="false" class="sek-ui-button" title="<?php _e('pixels', 'text_domain');?>" data-sek-unit="px"><?php _e('px', 'text_domain');?></button><button type="button" aria-pressed="false" class="sek-ui-button" title="<?php _e('em', 'text_domain');?>" data-sek-unit="em"><?php _e('em', 'text_domain');?></button><button type="button" aria-pressed="false" class="sek-ui-button" title="<?php _e('percents', 'text_domain');?>" data-sek-unit="%"><?php _e('%', 'text_domain');?></button></div>
        </div>
    </div><?php // sek-spacing-wrapper ?>
  <?php
}
?>
<?php
/* ------------------------------------------------------------------------- *
 *  BORDERS INPUT TEMPLATE
/* ------------------------------------------------------------------------- */
function sek_set_input_tmpl___borders( $input_id, $input_data ) {
    ?>
    <?php
    ?>
    <div class="sek-borders">
        <?php //<# //console.log( 'IN php::sek_set_input_tmpl___borders() => data range_slide => ', data ); #> ?>
        <div class="sek-border-type-wrapper">
            <div aria-label="unit" class="sek-ui-button-group" role="group"><button type="button" aria-pressed="true" class="sek-ui-button is-selected" title="<?php _e('All', 'text-domain');?>" data-sek-border-type="_all_"><?php _e('All', 'text-domain');?></button><button type="button" aria-pressed="false" class="sek-ui-button" title="<?php _e('Left', 'text-domain');?>" data-sek-border-type="left"><?php _e('Left', 'text-domain');?></button><button type="button" aria-pressed="false" class="sek-ui-button" title="<?php _e('Top', 'text-domain');?>" data-sek-border-type="top"><?php _e('Top', 'text-domain');?></button><button type="button" aria-pressed="false" class="sek-ui-button" title="<?php _e('Right', 'text-domain');?>" data-sek-border-type="right"><?php _e('Right', 'text-domain');?></button><button type="button" aria-pressed="false" class="sek-ui-button" title="<?php _e('Bottom', 'text-domain');?>" data-sek-border-type="bottom"><?php _e('Bottom', 'text-domain');?></button></div>
        </div>
        <div class="sek-range-unit-wrapper">
            <div class="sek-range-wrapper">
              <input data-czrtype="<?php echo $input_id; ?>" type="hidden"/>
              <?php
              printf( '<input class="sek-range-input" type="range" %1$s %2$s %3$s %4$s/>',
                ! empty( $input_data['step'] ) ? 'step="'. $input_data['step'] .'"' : '',
                ! empty( $input_data['unit'] ) ? 'data-unit="'. $input_data['unit'] .'"' : '',
                ! empty( $input_data['min'] ) ? 'min="'. $input_data['min'] .'"' : 'min="0"',
                ! empty( $input_data['max'] ) ? 'max="'. $input_data['max'] .'"' : ''
              );
              ?>
            </div>
            <div class="sek-number-wrapper">
                <input class="sek-pm-input" value="{{ value }}" type="number"  >
            </div>
            <div class="sek-unit-wrapper">
              <div aria-label="<?php _e( 'unit', 'text_domain'); ?>" class="sek-ui-button-group" role="group">
                    <button type="button" aria-pressed="false" class="sek-ui-button" title="<?php _e('pixels', 'text_domain');?>" data-sek-unit="px"><?php _e('px', 'text_domain');?></button><button type="button" aria-pressed="false" class="sek-ui-button" title="<?php _e('em', 'text_domain');?>" data-sek-unit="em"><?php _e('em', 'text_domain');?></button></div>
            </div>
        </div>
        <div class="sek-color-wrapper">
            <div class="sek-color-picker"><input class="sek-alpha-color-input" data-alpha="true" type="text" value=""/></div>
            <div class="sek-reset-button"><button type="button" class="button sek-reset-button sek-float-right"><?php _e( 'Reset', 'text_domain'); ?></button></div>
        </div>
    </div><?php // sek-borders ?>
  <?php
}
?>
<?php
/* ------------------------------------------------------------------------- *
 *  BORDER RADIUS INPUT TEMPLATE
/* ------------------------------------------------------------------------- */
function sek_set_input_tmpl___border_radius( $input_id, $input_data ) {
    ?>
    <?php
    ?>
    <div class="sek-borders">
        <?php //<# //console.log( 'IN php::sek_set_input_tmpl___border_radius() => data range_slide => ', data ); #> ?>
        <div class="sek-border-type-wrapper">
            <div aria-label="unit" class="sek-ui-button-group sek-float-left" role="group"><button type="button" aria-pressed="true" class="sek-ui-button is-selected" title="<?php _e('All', 'text-domain');?>" data-sek-radius-type="_all_"><?php _e('All', 'text-domain');?></button><button type="button" aria-pressed="false" class="sek-ui-button" title="<?php _e('Top left', 'text-domain');?>" data-sek-radius-type="top_left"><i class="material-icons">border_style</i></button><button type="button" aria-pressed="false" class="sek-ui-button" title="<?php _e('Top right', 'text-domain');?>" data-sek-radius-type="top_right"><i class="material-icons">border_style</i></button><button type="button" aria-pressed="false" class="sek-ui-button" title="<?php _e('Bottom right', 'text-domain');?>" data-sek-radius-type="bottom_right"><i class="material-icons">border_style</i></button><button type="button" aria-pressed="false" class="sek-ui-button" title="<?php _e('Bottom left', 'text-domain');?>" data-sek-radius-type="bottom_left"><i class="material-icons">border_style</i></button></div>
            <div class="sek-reset-button"><button type="button" class="button sek-reset-button sek-float-right"><?php _e( 'Reset', 'text_domain'); ?></button></div>
        </div>
        <div class="sek-range-unit-wrapper">
            <div class="sek-range-wrapper">
              <input data-czrtype="<?php echo $input_id; ?>" type="hidden"/>
              <?php
              printf( '<input class="sek-range-input" type="range" %1$s %2$s %3$s %4$s/>',
                ! empty( $input_data['step'] ) ? 'step="'. $input_data['step'] .'"' : '',
                ! empty( $input_data['unit'] ) ? 'data-unit="'. $input_data['unit'] .'"' : '',
                ! empty( $input_data['min'] ) ? 'min="'. $input_data['min'] .'"' : 'min="0"',
                ! empty( $input_data['max'] ) ? 'max="'. $input_data['max'] .'"' : ''
              );
              ?>
            </div>
            <div class="sek-number-wrapper">
                <input class="sek-pm-input" value="{{ value }}" type="number"  >
            </div>
            <div class="sek-unit-wrapper">
              <div aria-label="<?php _e( 'unit', 'text_domain'); ?>" class="sek-ui-button-group" role="group">
                    <button type="button" aria-pressed="false" class="sek-ui-button" title="<?php _e('pixels', 'text_domain');?>" data-sek-unit="px"><?php _e('px', 'text_domain');?></button><button type="button" aria-pressed="false" class="sek-ui-button" title="<?php _e('em', 'text_domain');?>" data-sek-unit="em"><?php _e('em', 'text_domain');?></button><button type="button" aria-pressed="false" class="sek-ui-button" title="<?php _e('percents', 'text_domain');?>" data-sek-unit="%"><?php _e('%', 'text_domain');?></button></div>
            </div>
        </div>
    </div><?php // sek-borders ?>
  <?php
}
?>
<?php
/* ------------------------------------------------------------------------- *
 *  MULTIPLE BUTTON CHOICES INPUT TEMPLATE
/* ------------------------------------------------------------------------- */
function sek_set_input_tmpl___buttons_choice( $input_id, $input_data ) {
    ?>
      <?php //<# //console.log( 'IN php::sek_set_input_tmpl___buttons_choice() => data range_slide => ', data ); #> ?>
      <?php
        if ( ! is_array( $input_data ) || empty( $input_data['choices'] ) || ! is_array( $input_data['choices'] ) ) {
            sek_error_log( __FUNCTION__ . ' error => missing choices property' );
            return;
        }
      ?>
      <div class="sek-button-choice-wrapper">
        <input data-czrtype="<?php echo $input_id; ?>" type="hidden"/>
        <div aria-label="<?php _e( 'unit', 'text_domain'); ?>" class="sek-ui-button-group sek-float-right" role="group">
            <?php
              foreach( $input_data['choices'] as $choice => $label ) {
                  printf('<button type="button" aria-pressed="false" class="sek-ui-button" title="%1$s" data-sek-choice="%2$s">%1$s</button>',
                    $label,
                    $choice
                  );
              }
            ?>
        </div>
      </div>
  <?php
}
?>
<?php
/* ------------------------------------------------------------------------- *
 *  RESET BUTTON INPUT TEMPLATE
/* ------------------------------------------------------------------------- */
function sek_set_input_tmpl___reset_button( $input_id, $input_data ) {
    ?>
      <?php //<# //console.log( 'IN php::sek_set_input_tmpl___buttons_choice() => data range_slide => ', data ); #> ?>
      <?php
        if ( ! is_array( $input_data ) || empty( $input_data['scope'] ) ) {
            sek_error_log( __FUNCTION__ . ' error => missing scope property' );
            return;
        }
      ?>
      <div class="sek-button-choice-wrapper">
        <input data-czrtype="<?php echo $input_id; ?>" type="hidden"/>
        <button type="button" aria-pressed="false" class="sek-ui-button sek-float-right" title="<?php _e('Reset', 'text-domain'); ?>" data-sek-reset-scope="<?php echo $input_data['scope']; ?>"><?php _e('Reset', 'text-domain'); ?></button>
      </div>
  <?php
}
?>
<?php
/* ------------------------------------------------------------------------- *
 *  RESET BUTTON INPUT TEMPLATE
/* ------------------------------------------------------------------------- */
function sek_set_input_tmpl___revision_history( $input_id, $input_data ) {
    ?>
      <?php //<# //console.log( 'IN php::sek_set_input_tmpl___buttons_choice() => data range_slide => ', data ); #> ?>
      <?php
        if ( ! is_array( $input_data ) || empty( $input_data['scope'] ) ) {
            sek_error_log( __FUNCTION__ . ' error => missing scope property' );
            return;
        }
      ?>
      <input data-czrtype="<?php echo $input_id; ?>" type="hidden"/>
  <?php
}
?>
<?php
/* ------------------------------------------------------------------------- *
 *  DETACHED WP EDITOR INPUT TEMPLATE
/* ------------------------------------------------------------------------- */
function sek_set_input_tmpl___detached_tinymce_editor( $input_id, $input_data ) {
    ?>
      <?php ////console.log( 'IN php::sek_set_input_tmpl___detached_tinymce_edito() => input data => ', data ); #> ?>
      <button type="button" class="button text_editor-button" data-czr-control-id="{{ data.control_id }}" data-czr-input-id="<?php echo $input_id; ?>" data-czr-action="open-tinymce-editor"><?php _e('Edit', 'text_doma' ); ?></button>&nbsp;
      <button type="button" class="button text_editor-button" data-czr-control-id="{{ data.control_id }}" data-czr-input-id="<?php echo $input_id; ?>" data-czr-action="close-tinymce-editor"><?php _e('Hide editor', 'text_doma' ); ?></button>
      <input data-czrtype="<?php echo $input_id; ?>" type="hidden" value="{{ data.value }}"/>
  <?php
}

/* ------------------------------------------------------------------------- *
 *  WP EDITOR INPUT TEMPLATE
/* ------------------------------------------------------------------------- */
function sek_set_input_tmpl___nimble_tinymce_editor( $input_id, $input_data ) {
    ?>
    <?php //<# console.log( 'IN php::ac_get_default_input_tmpl() => data range_slide => ', data ); #> ?>
      <textarea id="textarea-{{ data.control_id }}" data-czrtype="<?php echo $input_id; ?>" class="width-100" name="textarea" rows="10" cols="">{{ data.value }}</textarea>
    <?php
}
?>
