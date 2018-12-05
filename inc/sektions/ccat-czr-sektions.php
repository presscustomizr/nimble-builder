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

    wp_enqueue_script(
        'czr-color-picker',
        sprintf(
            '%1$s/assets/czr/sek/js/libs/%2$s' ,
            NIMBLE_BASE_URL,
            sek_is_dev_mode() ? 'czr-color-picker.js' : 'czr-color-picker.min.js'
        ),
        array( 'jquery' ),
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

                'isSavedSectionEnabled' => defined( 'NIMBLE_SAVED_SECTIONS_ENABLED' ) ? NIMBLE_SAVED_SECTIONS_ENABLED : true
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
            'Sections' => __( 'Sections', 'text_domain_to_be_replaced'),

            'Nimble Builder' => __('Nimble Builder', 'text_domain_to_be_replaced'),

            "You've reached the maximum number of allowed nested sections." => __("You've reached the maximum number of allowed nested sections.", 'text_domain_to_be_replaced'),
            "You've reached the maximum number of columns allowed in this section." => __( "You've reached the maximum number of columns allowed in this section.", 'text_domain_to_be_replaced'),
            "A section must have at least one column." => __( "A section must have at least one column.", 'text_domain_to_be_replaced'),

            'If this problem locks the Nimble builder, you might try to reset the sections for this page.' => __('If this problem locks the Nimble builder, you might try to reset the sections for this page.', 'text_domain_to_be_replaced'),
            'Reset' => __('Reset', 'text_domain_to_be_replaced'),
            'Reset complete' => __('Reset complete', 'text_domain_to_be_replaced'),
            'Drag and drop content' => __('Drag and drop content', 'text_domain_to_be_replaced'),
            'Content Picker' => __('Content Picker', 'text_domain_to_be_replaced'),
            'Pick a module' => __('Pick a module', 'text_domain_to_be_replaced'),
            'Pick a pre-designed section' => __('Pick a pre-designed section', 'text_domain_to_be_replaced'),
            'Select a content type' => __('Select a content type', 'text_domain_to_be_replaced'),

            'Sections for an introduction' => __('Sections for an introduction', 'text_domain_to_be_replaced'),
            'Sections for services and features' => __('Sections for services and features', 'text_domain_to_be_replaced'),
            'Contact-us sections' => __('Contact-us sections', 'text_domain_to_be_replaced'),
            'Empty sections with columns layout' => __('Empty sections with columns layout', 'text_domain_to_be_replaced'),

            'Drag and drop a module in one of the possible locations of the previewed page.' => __( 'Drag and drop a module in one of the possible locations of the previewed page.', 'text_domain_to_be_replaced' ),

            'Module' => __('Module', 'text_domain_to_be_replaced'),
            'Content for' => __('Content for', 'text_domain_to_be_replaced'),
            'Customize the options for module :' => __('Customize the options for module :', 'text_domain_to_be_replaced'),

            'Layout settings for the' => __('Layout settings for the', 'text_domain_to_be_replaced'),
            'Background settings for the' => __('Background settings for the', 'text_domain_to_be_replaced'),
            'Borders settings for the' => __('Borders settings for the', 'text_domain_to_be_replaced'),
            'Padding and margin settings for the' => __('Padding and margin settings for the', 'text_domain_to_be_replaced'),
            'Height settings for the' => __('Height settings for the', 'text_domain_to_be_replaced'),
            'Width settings for the' => __('Width settings for the', 'text_domain_to_be_replaced'),
            'Set a custom anchor for the' => __('Set a custom anchor for the', 'text_domain_to_be_replaced'),
            'Device visibility settings for the' => __('Device visibility settings for the', 'text_domain_to_be_replaced'),
            'Responsive settings : breakpoint, column direction' => __('Responsive settings : breakpoint, column direction', 'text_domain_to_be_replaced'),

            'Settings for the' => __('Settings for the', 'text_domain_to_be_replaced'),//section / column / module
            'Current page options' => __( 'Current page options', 'text_domain_to_be_replaced'),
            'Page template' => __( 'Page template', 'text_domain_to_be_replaced'),
            'Inner and outer widths' => __( 'Inner and outer widths', 'text_domain_to_be_replaced'),
            'Custom CSS' => __( 'Custom CSS', 'text_domain_to_be_replaced'),
            'Remove the sections in this page' => __( 'Remove the sections in this page', 'text_domain_to_be_replaced'),
            'Page speed optimizations' => __( 'Page speed optimizations', 'text_domain_to_be_replaced'),

            'Site wide breakpoint for Nimble sections' => __( 'Site wide breakpoint for Nimble sections', 'text_domain_to_be_replaced'),
            'Site wide inner and outer sections widths' => __( 'Site wide inner and outer sections widths', 'text_domain_to_be_replaced'),

            'Site wide page speed optimizations' => __( 'Site wide page speed optimizations', 'text_domain_to_be_replaced'),
            'Options for the sections of the current page' => __( 'Options for the sections of the current page', 'text_domain_to_be_replaced'),
            'General options applied for the sections site wide' => __( 'General options applied for the sections site wide', 'text_domain_to_be_replaced'),

            'Site wide options' => __( 'Site wide options', 'text_domain_to_be_replaced'),
            'location' => __('location', 'text_domain_to_be_replaced'),
            'section' => __('section', 'text_domain_to_be_replaced'),
            'column' => __('column', 'text_domain_to_be_replaced'),
            'module' => __('module', 'text_domain_to_be_replaced'),

            'This browser does not support drag and drop. You might need to update your browser or use another one.' => __('This browser does not support drag and drop. You might need to update your browser or use another one.', 'text_domain_to_be_replaced'),
            'Insert here' => __('Insert here', 'text_domain_to_be_replaced'),
            'Insert in a new section' => __('Insert in a new section', 'text_domain_to_be_replaced'),
            'Insert a new section here' => __('Insert a new section here', 'text_domain_to_be_replaced'),
            'Select a font family' => __('Select a font family', 'text_domain_to_be_replaced'),
            'Web Safe Fonts' => __('Web Safe Fonts', 'text_domain_to_be_replaced'),
            'Google Fonts' => __('Google Fonts', 'text_domain_to_be_replaced'),

            'Set a custom url' => __('Set a custom url', 'text_domain_to_be_replaced'),

            'Something went wrong, please refresh this page.' => __('Something went wrong, please refresh this page.', 'text_domain_to_be_replaced'),

            'Select an icon'     => __( 'Select an icon', 'text_domain_to_be_replaced' ),
            'codeEditorSingular'   => __( 'There is %d error in your %s code which might break your site. Please fix it before saving.', 'text_domain_to_be_replaced' ),
            'codeEditorPlural'     => __( 'There are %d errors in your %s code which might break your site. Please fix them before saving.', 'text_domain_to_be_replaced' ),
            'Settings on desktops' => __('Settings on desktops', 'text_domain_to_be_replaced'),
            'Settings on tablets' => __('Settings on tablets', 'text_domain_to_be_replaced'),
            'Settings on mobiles' => __('Settings on mobiles', 'text_domain_to_be_replaced')

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
          <div class="sek-nimble-doc" data-doc-href="https://docs.presscustomizr.com/collection/334-nimble-builder">
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
              <label for="sek-saved-section-title" class="customize-control-title"><?php _e('Section title', 'text_domain_to_be_replaced'); ?></label>
              <input id="sek-saved-section-title" type="text" value="">
          </div>
          <div class="sek-section-description">
              <label for="sek-saved-section-description" class="customize-control-title"><?php _e('Section description', 'text_domain_to_be_replaced'); ?></label>
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
    <?php
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
/*
* This approach has been inspired by the excellent https://github.com/xwp/wp-customize-posts
*/

add_action( 'customize_register', '\Nimble\sek_register_tiny_mce_editor_tmpl_and_scripts');
function sek_register_tiny_mce_editor_tmpl_and_scripts() {
    add_action( 'customize_controls_print_footer_scripts', '\Nimble\sek_print_tiny_mce_editor_template', 0 );
    add_action( 'customize_controls_init', '\Nimble\sek_enqueue_tiny_mce_editor' );
}
function sek_print_tiny_mce_editor_template() {
    global $wp_customize;

    ?>
      <div id="czr-customize-content_editor-pane">
        <div data-czr-action="close-tinymce-editor" class="czr-close-editor"><i class="fas fa-arrow-circle-down" title="<?php _e( 'Hide Editor', 'text_domain_to_be_replaced' ); ?>"></i>&nbsp;<span><?php _e( 'Hide Editor', 'text_domain_to_be_replaced');?></span></div>
        <div id="czr-customize-content_editor-dragbar" title="<?php _e('Resize the editor', 'text_domain'); ?>">
          <span class="screen-reader-text"><?php _e( 'Resize the editor', 'nimble-builder' ); ?></span>
          <i class="czr-resize-handle fas fa-arrows-alt-v"></i>
        </div>

        <?php
          wp_editor( '', 'czr-customize-content_editor', array(
              '_content_editor_dfw' => false,
              'drag_drop_upload' => true,
              'tabfocus_elements' => 'content-html,save-post',
              'editor_height' => 200,
              'default_editor' => 'tinymce',
              'tinymce' => array(
                  'resize' => false,
                  'wp_autoresize_on' => false,
                  'add_unload_trigger' => false
              ),
          ) );
        ?>
      </div>
    <?php
}

/**
 * Enqueue a WP Editor instance we can use for rich text editing.
 */
function sek_enqueue_tiny_mce_editor() {
    if ( false === has_action( 'customize_controls_print_footer_scripts', array( '_WP_Editors', 'enqueue_scripts' ) ) ) {
        add_action( 'customize_controls_print_footer_scripts', array( '_WP_Editors', 'enqueue_scripts' ) );
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
            $content_collection = array(
                array(
                  'content-type' => 'module',
                  'content-id' => 'czr_tiny_mce_editor_module',
                  'title' => __( 'WordPress Editor', 'text_domain_to_be_replaced' ),
                  'icon' => 'Nimble_rich-text-editor_icon.svg'
                ),
                array(
                  'content-type' => 'module',
                  'content-id' => 'czr_image_module',
                  'title' => __( 'Image', 'text_domain_to_be_replaced' ),
                  'icon' => 'Nimble__image_icon.svg'
                ),
                array(
                  'content-type' => 'module',
                  'content-id' => 'czr_heading_module',
                  'title' => __( 'Heading', 'text_domain_to_be_replaced' ),
                  'icon' => 'Nimble__heading_icon.svg'
                ),

                array(
                  'content-type' => 'module',
                  'content-id' => 'czr_icon_module',
                  'title' => __( 'Icon', 'text_domain_to_be_replaced' ),
                  'icon' => 'Nimble__icon_icon.svg'
                ),
                array(
                  'content-type' => 'module',
                  'content-id' => 'czr_button_module',
                  'title' => __( 'Button', 'text_domain_to_be_replaced' ),
                  'icon' => 'Nimble_button_icon.svg'
                ),
                array(
                  'content-type' => 'module',
                  'content-id' => 'czr_map_module',
                  'title' => __( 'Map', 'text_domain_to_be_replaced' ),
                  'icon' => 'Nimble_map_icon.svg'
                ),

                array(
                  'content-type' => 'preset_section',
                  'content-id' => 'two_columns',
                  'title' => __( 'Two Columns', 'text_domain_to_be_replaced' ),
                  'icon' => 'Nimble_2-columns_icon.svg'
                ),
                array(
                  'content-type' => 'preset_section',
                  'content-id' => 'three_columns',
                  'title' => __( 'Three Columns', 'text_domain_to_be_replaced' ),
                  'icon' => 'Nimble_3-columns_icon.svg'
                ),
                array(
                  'content-type' => 'preset_section',
                  'content-id' => 'four_columns',
                  'title' => __( 'Four Columns', 'text_domain_to_be_replaced' ),
                  'icon' => 'Nimble_4-columns_icon.svg'
                ),

                array(
                  'content-type' => 'module',
                  'content-id' => 'czr_simple_html_module',
                  'title' => __( 'Html Content', 'text_domain_to_be_replaced' ),
                  'icon' => 'Nimble_html_icon.svg'
                ),
                array(
                  'content-type' => 'module',
                  'content-id' => 'czr_quote_module',
                  'title' => __( 'Quote', 'text_domain_to_be_replaced' ),
                  'icon' => 'Nimble_quote_icon.svg'
                ),
                array(
                  'content-type' => 'module',
                  'content-id' => 'czr_spacer_module',
                  'title' => __( 'Spacer', 'text_domain_to_be_replaced' ),
                  'icon' => 'Nimble__spacer_icon.svg'
                ),
                array(
                  'content-type' => 'module',
                  'content-id' => 'czr_divider_module',
                  'title' => __( 'Divider', 'text_domain_to_be_replaced' ),
                  'icon' => 'Nimble__divider_icon.svg'
                ),
                array(
                  'content-type' => 'module',
                  'content-id' => 'czr_simple_form_module',
                  'title' => __( 'Simple Contact Form', 'text_domain_to_be_replaced' ),
                  'icon' => 'Nimble_contact-form_icon.svg'
                ),


            );
            $i = 0;
            foreach( $content_collection as $_params) {
                $icon_img_src = '';
                if ( !empty( $_params['icon'] ) ) {
                    $icon_img_src = NIMBLE_BASE_URL . '/assets/czr/sek/icons/modules/' . $_params['icon'];
                }

                printf('<div draggable="true" data-sek-content-type="%1$s" data-sek-content-id="%2$s" title="%5$s"><div class="sek-module-icon">%3$s</div><div class="sek-module-title"><div class="sek-centered-module-title">%4$s</div></div></div>',
                      $_params['content-type'],
                      $_params['content-id'],
                      empty( $icon_img_src ) ? '<i style="color:red">Missing Icon</i>' : '<img draggable="false" title="'. $_params['title'] . '" alt="'. $_params['title'] . '" class="nimble-module-icons" src="' . $icon_img_src .'"/>',
                      $_params['title'],
                      __('Drag and drop the module in the previewed page.', 'text_domain_to_be_replaced' )
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
            }
            foreach( $content_collection as $_params) {
                printf('<div draggable="true" data-sek-section-type="content" data-sek-content-type="%1$s" data-sek-content-id="%2$s" style="%3$s" title="%4$s"><div class="sek-overlay"></div></div>',
                    $_params['content-type'],
                    $_params['content-id'],
                    sprintf( 'background: url(%1$s) 50% 50% / cover no-repeat;%2$s',
                        NIMBLE_BASE_URL . '/assets/img/section_assets/thumbs/' . $_params['thumb'],
                        isset( $_params['height'] ) ? 'height:'.$_params['height'] : ''
                    ),
                    $_params['title']
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
        <div class="reset-spacing-wrap"><span class="sek-do-reset"><?php _e('Reset all spacing', 'text_domain_to_be_replaced' ); ?></span></div>

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
    $faicons_transient_name = 'sek_font_awesome_october_2018';
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
        <# //console.log( 'IN php::sek_set_input_tmpl___range_simple() => data range_slide => ', data ); #>
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
        <# //console.log( 'IN php::sek_set_input_tmpl___range_with_unit_picker() => data range_slide => ', data ); #>
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
        <# //console.log( 'IN php::sek_set_input_tmpl___range_with_unit_picker_device_switcher() => data range_slide => ', data ); #>
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
        <# //console.log( 'IN php::sek_set_input_tmpl___borders() => data range_slide => ', data ); #>
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
        <# //console.log( 'IN php::sek_set_input_tmpl___border_radius() => data range_slide => ', data ); #>
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
      <# //console.log( 'IN php::sek_set_input_tmpl___buttons_choice() => data range_slide => ', data ); #>
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
      <# //console.log( 'IN php::sek_set_input_tmpl___buttons_choice() => data range_slide => ', data ); #>
      <?php
        if ( ! is_array( $input_data ) || empty( $input_data['scope'] ) ) {
            sek_error_log( __FUNCTION__ . ' error => missing scope property' );
            return;
        }
      ?>
      <div class="sek-button-choice-wrapper">
        <input data-czrtype="<?php echo $input_id; ?>" type="hidden"/>
        <button type="button" aria-pressed="false" class="sek-ui-button sek-float-right" title="%1$s" data-sek-reset-scope="<?php echo $input_data['scope']; ?>"><?php _e('Reset', 'text-domain'); ?></button>
      </div>
  <?php
}
?>
