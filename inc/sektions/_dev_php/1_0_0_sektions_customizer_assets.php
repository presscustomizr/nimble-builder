<?php
// TINY MCE EDITOR
require_once(  dirname( __FILE__ ) . '/customizer/seks_tiny_mce_editor_actions.php' );

// ENQUEUE CUSTOMIZER JAVASCRIPT + PRINT LOCALIZED DATA
add_action ( 'customize_controls_enqueue_scripts', '\Nimble\sek_enqueue_controls_js_css', 20 );
function sek_enqueue_controls_js_css() {
    wp_enqueue_style(
        'sek-control',
        sprintf(
            '%1$s/assets/czr/sek/css/%2$s' ,
            NIMBLE_BASE_URL,
            defined('NIMBLE_DEV') && true === NIMBLE_DEV ? 'sek-control.css' : 'sek-control.min.css'
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
            defined('NIMBLE_DEV') && true === NIMBLE_DEV ? 'ccat-sek-control.js' : 'ccat-sek-control.min.js'
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
            defined('NIMBLE_DEV') && true === NIMBLE_DEV ? 'czr-color-picker.js' : 'czr-color-picker.min.js'
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
                'isDevMode' => ( defined('WP_DEBUG') && true === WP_DEBUG ) || ( defined('NIMBLE_DEV') && true === NIMBLE_DEV ),
                'baseUrl' => NIMBLE_BASE_URL,
                'sektionsPanelId' => '__sektions__',
                'addNewSektionId' => 'sek_add_new_sektion',
                'addNewColumnId' => 'sek_add_new_column',
                'addNewModuleId' => 'sek_add_new_module',

                'optPrefixForSektionSetting' => NIMBLE_OPT_PREFIX_FOR_SEKTION_COLLECTION,//'nimble___'
                'optNameForGlobalOptions' => NIMBLE_OPT_NAME_FOR_GLOBAL_OPTIONS,//'nimble___'
                'optPrefixForSektionsNotSaved' => NIMBLE_OPT_PREFIX_FOR_LEVEL_UI,//"__nimble__"

                'globalOptionDBValues' => get_option( NIMBLE_OPT_NAME_FOR_GLOBAL_OPTIONS ),// '__nimble_options__'

                'defaultSektionSettingValue' => sek_get_default_sektions_value(),

                'presetSections' => sek_get_preset_sektions(),

                'registeredModules' => CZR_Fmk_Base() -> registered_modules,

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
                ])
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
            'Sections' => __( 'Sections', 'text_domain_to_be_replaced'),

            'Nimble Builder' => __('Nimble Builder', 'text_domain_to_be_replaced'),

            "You've reached the maximum number of allowed nested sections." => __("You've reached the maximum number of allowed nested sections.", 'text_domain_to_be_replaced'),
            "You've reached the maximum number of columns allowed in this section." => __( "You've reached the maximum number of columns allowed in this section.", 'text_domain_to_be_replaced'),
            "A section must have at least one column." => __( "A section must have at least one column.", 'text_domain_to_be_replaced'),

            'If this problem locks the Nimble builder, you might try to reset the sections for this page.' => __('If this problem locks the Nimble builder, you might try to reset the sections for this page.', 'text_domain_to_be_replaced'),
            'Reset' => __('Reset', 'text_domain_to_be_replaced'),
            'Reset complete' => __('Reset complete', 'text_domain_to_be_replaced'),

            // Header button title text
            'Drag and drop content' => __('Drag and drop content', 'text_domain_to_be_replaced'),

            // Generated UI
            'Content Picker' => __('Content Picker', 'text_domain_to_be_replaced'),
            'Pick a module' => __('Pick a module', 'text_domain_to_be_replaced'),
            'Pick a pre-designed section' => __('Pick a pre-designed section', 'text_domain_to_be_replaced'),
            'Select a content type' => __('Select a content type', 'text_domain_to_be_replaced'),

            'Sections for an introduction' => __('Sections for an introduction', 'text_domain_to_be_replaced'),
            'Sections for services and features' => __('Sections for services and features', 'text_domain_to_be_replaced'),

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

            // UI global and local options
            'Current page options' => __( 'Current page options', 'text_domain_to_be_replaced'),
            'Page template' => __( 'Page template', 'text_domain_to_be_replaced'),
            'Inner and outer widths' => __( 'Inner and outer widths', 'text_domain_to_be_replaced'),
            'Custom CSS' => __( 'Custom CSS', 'text_domain_to_be_replaced'),
            'Remove the sections in this page' => __( 'Remove the sections in this page', 'text_domain_to_be_replaced'),

            'Site wide breakpoint for Nimble sections' => __( 'Site wide breakpoint for Nimble sections', 'text_domain_to_be_replaced'),
            'Site wide inner and outer sections widths' => __( 'Site wide inner and outer sections widths', 'text_domain_to_be_replaced'),

            // DEPRECATED
            'Options for the sections of the current page' => __( 'Options for the sections of the current page', 'text_domain_to_be_replaced'),
            'General options applied for the sections site wide' => __( 'General options applied for the sections site wide', 'text_domain_to_be_replaced'),
            //

            'General options' => __( 'General options', 'text_domain_to_be_replaced'),


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

            'Select an icon'     => __( 'Select an icon', 'text_domain_to_be_replaced' ),

            // Code Editor
            'codeEditorSingular'   => __( 'There is %d error in your %s code which might break your site. Please fix it before saving.', 'text_domain_to_be_replaced' ),
            'codeEditorPlural'     => __( 'There are %d errors in your %s code which might break your site. Please fix them before saving.', 'text_domain_to_be_replaced' ),

            // Various
            'Settings on desktops' => __('Settings on desktops', 'text_domain_to_be_replaced'),
            'Settings on tablets' => __('Settings on tablets', 'text_domain_to_be_replaced'),
            'Settings on mobiles' => __('Settings on mobiles', 'text_domain_to_be_replaced')


            // 'Module' => __('Module', 'text_domain_to_be_replaced'),
            // 'Module' => __('Module', 'text_domain_to_be_replaced'),
            // 'Module' => __('Module', 'text_domain_to_be_replaced'),
            // 'Module' => __('Module', 'text_domain_to_be_replaced'),
            // 'Module' => __('Module', 'text_domain_to_be_replaced'),

        )//array()
    )//array()
    );//array_merge
}//'nimble_add_i18n_localized_control_params'







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
            'setting_id' => sek_get_seks_setting_id( $skope_id )//nimble___loop_start[skp__post_page_home]
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
    if ( false == $transient_data || empty( $transient_data ) || ( defined( 'NIMBLE_DEV') && true === NIMBLE_DEV ) ) {
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


// @see https://codex.wordpress.org/Function_Reference/get_intermediate_image_sizes
function sek_get_img_sizes() {
    global $_wp_additional_image_sizes;

    $sizes = array();
    $to_return = array(
        'original' => __('Original image dimensions', 'text_domain_to_be_replaced')
    );

    foreach ( get_intermediate_image_sizes() as $_size ) {

        $first_to_upper_size = ucfirst(strtolower($_size));
        $first_to_upper_size = preg_replace_callback( '/[.!?].*?\w/', '\Nimble\sek_img_sizes_preg_replace_callback', $first_to_upper_size );

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

function sek_img_sizes_preg_replace_callback( $matches ) {
    return strtoupper( $matches[0] );
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
      </div>
    </script>
    <?php
}
?>