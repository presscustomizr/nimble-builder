<?php
////////////////////////////////////////////////////////////////
// CZR_Fmk_Base
if ( !class_exists( 'CZR_Fmk_Base_Load_Resources' ) ) :
    class CZR_Fmk_Base_Load_Resources extends CZR_Fmk_Base_Construct {

        // fired in the constructor
        function czr_enqueue_fmk_resources() {
            // Enqueue the fmk control js
            add_action ( 'customize_controls_enqueue_scripts' , array( $this, 'ac_load_additional_controls_js' ) );
            add_action ( 'customize_controls_enqueue_scripts' , array( $this, 'ac_load_additional_controls_css' ) );

            // Enqueue the base preview js
            //hook : customize_preview_init
            add_action ( 'customize_preview_init' , array( $this, 'ac_customize_load_preview_js' ) );

            // adds specific js templates for the czr_module control
            add_action( 'customize_controls_print_footer_scripts', array( $this, 'ac_print_module_control_templates' ) , 1 );

            add_action( 'customize_controls_print_footer_scripts', array( $this, 'ac_print_img_uploader_template' ) , 1 );
        }


        // hook : 'customize_controls_enqueue_scripts'
        function ac_load_additional_controls_js() {
            // Enqueue scripts/styles for the color picker.
            // Probably already enqueued by the theme controls, but let's make sure they are.
            wp_enqueue_script( 'wp-color-picker' );
            wp_enqueue_style( 'wp-color-picker' );

            // July 2020 : compatibility with WP5.5 => wpColorPickerL10n are not loaded by WP core anymore, but we need them for the custom czr-alpha-colorpicker.js
            // see https://github.com/presscustomizr/nimble-builder/issues/729
            global $wp_version;
            if ( version_compare( $wp_version, '5.4.2' , '>=' ) ) {
                wp_localize_script(
                  'wp-color-picker',
                  'nb_wpColorPickerL10n',
                  array(
                    'clear'            => __( 'Clear' ),
                    'clearAriaLabel'   => __( 'Clear color' ),
                    'defaultString'    => __( 'Default' ),
                    'defaultAriaLabel' => __( 'Select default color' ),
                    'pick'             => __( 'Select Color' ),
                    'defaultLabel'     => __( 'Color value' )
                  )
                );
            }

            //'czr-customizer-fmk' will be enqueued as a dependency of 'font-customizer-control' only in plugin mode
            wp_enqueue_script(
                'czr-customizer-fmk',
                //dev / debug mode mode?
                sprintf(
                    '%1$s/assets/js/%2$s',
                    NIMBLE_FMK_BASE_URL,
                    defined('CZR_DEV') && true === CZR_DEV ? '_0_ccat_czr-base-fmk.js' : '_0_ccat_czr-base-fmk.min.js'
                ),
                array('customize-controls' , 'jquery', 'underscore'),
                ( defined('WP_DEBUG') && true === WP_DEBUG ) ? time() : NIMBLE_FMK_BASE_VERSION,
                $in_footer = true
            );

            // When used with Customizr or Hueman, free and pro, we also need to load the theme js part
            if ( false !== strpos( czr_get_parent_theme_slug(), 'customizr' ) || false !== strpos( czr_get_parent_theme_slug(), 'hueman' ) ) {
                wp_enqueue_script(
                    'czr-theme-customizer-fmk',
                    //dev / debug mode mode?
                    sprintf(
                        '%1$s/assets/js/%2$s',
                        NIMBLE_FMK_BASE_URL,
                        defined('CZR_DEV') && true === CZR_DEV ? '_1_ccat_czr-theme-fmk.js' : '_1_ccat_czr-theme-fmk.min.js'
                    ),
                    array( 'czr-customizer-fmk' ),
                    ( defined('WP_DEBUG') && true === WP_DEBUG ) ? time() : NIMBLE_FMK_BASE_VERSION,
                    $in_footer = true
                );
            }

            $theme_data   = wp_get_theme();
            $theme        = $theme_data->Name . ' ' . $theme_data->Version;
            $parent_theme = $theme_data->Template;
            if ( !empty( $parent_theme ) ) {
              $parent_theme_data = wp_get_theme( $parent_theme );
              $parent_theme      = $parent_theme_data->Name;
            }
            $parent_theme =strtolower($parent_theme);

            //additional localized param when standalone plugin mode
            wp_localize_script(
                'czr-customizer-fmk',
                'serverControlParams',
                apply_filters( 'czr_js_customizer_control_params' ,
                  array(
                      'css_attr' => $this->czr_css_attr,
                      'isDevMode' => ( defined('WP_DEBUG') && true === WP_DEBUG ) || ( defined('CZR_DEV') && true === CZR_DEV ),
                      'docURL'          => esc_url('docs.presscustomizr.com/'),
                      'i18n' => array(
                            'edit' => __('Edit', 'text_doma'),
                            'close' => __('Close', 'text_doma'),
                            'notset' => __('Not set', 'text_doma'),
                            'successMessage' => __('Done !', 'text_doma'),

                            'readDocumentation' => __('Learn more about this in the documentation', 'text_doma'),
                            'Settings' => __('Settings', 'text_doma'),
                            'Options for' => __('Options for', 'text_doma'),

                            // img upload translation
                            'select_image'        => __( 'Select Image', 'text_doma' ),
                            'change_image'        => __( 'Change Image', 'text_doma' ),
                            'remove_image'        => __( 'Remove', 'text_doma' ),
                            'default_image'       => __( 'Default', 'text_doma'  ),
                            'placeholder_image'   => __( 'No image selected', 'text_doma' ),
                            'frame_title_image'   => __( 'Select Image', 'text_doma' ),
                            'frame_button_image'  => __( 'Choose Image', 'text_doma' ),

                            'Customizing' => __('Customizing', 'text_doma'),
                      ),
                      'paramsForDynamicRegistration' => apply_filters( 'czr_fmk_dynamic_setting_js_params', array() ),
                      'activeTheme' => $parent_theme
                  )
                )
            );
        }



        // Enqueue the fmk css when standalone plugin
        // hook : 'customize_controls_enqueue_scripts'
        function ac_load_additional_controls_css() {
            wp_enqueue_style(
                'czr-fmk-controls-style',
                sprintf('%1$s/assets/css/czr-ccat-control-base%2$s.css', NIMBLE_FMK_BASE_URL, ( defined('WP_DEBUG') && true === WP_DEBUG ) ? '' : '.min'),
                array( 'customize-controls' ),
                ( defined('WP_DEBUG') && true === WP_DEBUG ) ? time() : NIMBLE_FMK_BASE_VERSION,
                $media = 'all'
            );

            //select2 stylesheet
            //overriden by some specific style in czr-control-base.css
            wp_enqueue_style(
                'czr-select2-css',
                 sprintf('%1$s/assets/css/lib/czrSelect2.min.css', NIMBLE_FMK_BASE_URL ),
                array( 'customize-controls' ),
                ( defined('WP_DEBUG') && true === WP_DEBUG ) ? time() : NIMBLE_FMK_BASE_VERSION,
                $media = 'all'
            );

            wp_enqueue_style(
                'czr-font-awesome',
                sprintf('%1$s/assets/fonts/css/fontawesome-all.min.css', NIMBLE_FMK_BASE_URL ),
                array(),
                ( defined('WP_DEBUG') && true === WP_DEBUG ) ? time() : NIMBLE_FMK_BASE_VERSION,
                $media = 'all'
            );
        }


        //hook : customize_preview_init
        function ac_customize_load_preview_js() {
            global $wp_version;

            wp_enqueue_script(
                'czr-customizer-preview' ,
                  sprintf(
                      '%1$s/assets/js/%2$s',
                      NIMBLE_FMK_BASE_URL,
                      defined('CZR_DEV') && true === CZR_DEV ? 'czr-preview-base.js' : 'czr-preview-base.min.js'
                  ),
                  array( 'customize-preview', 'underscore'),
                  ( defined('WP_DEBUG') && true === WP_DEBUG ) ? time() : NIMBLE_FMK_BASE_VERSION,
                  true
            );

            //localizes
            wp_localize_script(
                  'czr-customizer-preview',
                  'serverPreviewParams',
                  apply_filters('czr_base_fmk_customizer_preview_params' ,
                      array(
                          'themeFolder'     => get_template_directory_uri(),
                          //patch for old wp versions which don't trigger preview-ready signal => since WP 4.1
                          'preview_ready_event_exists'   => version_compare( $wp_version, '4.1' , '>=' ),
                          'blogname' => get_bloginfo('name'),
                          'isRTL'    => is_rtl()
                      )
                  )
            );
        }

        // DO WE STILL NEED TO PRINT THIS TMPL ?
        /////////////////////////////////////////////////////
        /// WHEN EMBEDDED IN A CONTROL //////////////////////
        /////////////////////////////////////////////////////
        //add specific js templates for the czr_module control
        //this is usually called in the manager for "registered" controls that need to be rendered with js
        //for this control, we'll do it another way because we need several js templates
        //=> that's why this control has not been "registered" and js templates are printed with the following action
        function ac_print_module_control_templates() {
            //Render the control wrapper for the CRUD types modules
            ?>
              <?php //Render the control wrapper for the CRUD types modules ?>
              <script type="text/html" id="tmpl-customize-control-czr_module-content">
                <label for="{{ data.settings['default'] }}-button">

                  <# if ( data.label ) { #>
                    <span class="customize-control-title">{{ data.label }}</span>
                  <# } #>
                  <# if ( data.description ) { #>
                    <span class="description customize-control-description">{{{ data.description }}}</span>
                  <# } #>
                </label>
              </script>
            <?php
        }


        // this template is used in setupImageUploaderSaveAsId and setupImageUploaderSaveAsUrl
        // @see js CZRInputMths
        function ac_print_img_uploader_template() {
          ?>
            <script type="text/html" id="tmpl-czr-img-uploader">
              <?php // case when a regular attachement object is provided, fetched from an id with wp.media.attachment( id ) ?>
                <# if ( ( data.attachment && data.attachment.id ) ) { #>
                  <div class="attachment-media-view attachment-media-view-{{ data.attachment.type }} {{ data.attachment.orientation }}">
                    <div class="thumbnail thumbnail-{{ data.attachment.type }}">
                      <# if ( 'image' === data.attachment.type && data.attachment.sizes && data.attachment.sizes.medium ) { #>
                        <img class="attachment-thumb" src="{{ data.attachment.sizes.medium.url }}" draggable="false" alt="" />
                      <# } else if ( 'image' === data.attachment.type && data.attachment.sizes && data.attachment.sizes.full ) { #>
                        <img class="attachment-thumb" src="{{ data.attachment.sizes.full.url }}" draggable="false" alt="" />
                      <# } #>
                    </div>
                    <div class="actions">
                      <# if ( data.canUpload ) { #>
                      <button type="button" class="button remove-button">{{ data.button_labels.remove }}</button>
                      <button type="button" class="button upload-button control-focus" id="{{ data.settings['default'] }}-button">{{ data.button_labels.change }}</button>
                      <div style="clear:both"></div>
                      <# } #>
                    </div>
                  </div>
                <?php // case when an url is provided ?>
                <# } else if ( !_.isEmpty( data.fromUrl ) ) { #>
                  <div class="attachment-media-view">
                    <div class="thumbnail thumbnail-thumb">
                        <img class="attachment-thumb" src="{{ data.fromUrl }}" draggable="false" alt="" />
                    </div>
                    <div class="actions">
                      <# if ( data.canUpload ) { #>
                      <button type="button" class="button remove-button">{{ data.button_labels.remove }}</button>
                      <button type="button" class="button upload-button control-focus" id="{{ data.settings['default'] }}-button">{{ data.button_labels.change }}</button>
                      <div style="clear:both"></div>
                      <# } #>
                    </div>
                  </div>
                <?php // case when neither attachement or url are provided => placeholder ?>
                <# } else { #>
                  <div class="attachment-media-view">
                    <div class="placeholder">
                      {{ data.button_labels.placeholder }}
                    </div>
                    <div class="actions">
                      <# if ( data.canUpload ) { #>
                      <button type="button" class="button upload-button" id="{{ data.settings['default'] }}-button">{{ data.button_labels.select }}</button>
                      <# } #>
                      <div style="clear:both"></div>
                    </div>
                  </div>
                <# } #>
            </script>
          <?php
        }
    }//class
endif;

?>