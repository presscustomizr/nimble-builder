<?php
////////////////////////////////////////////////////////////////
// CZR_Fmk_Base
if ( !class_exists( 'CZR_Fmk_Base_Tmpl_Builder' ) ) :
    class CZR_Fmk_Base_Tmpl_Builder extends CZR_Fmk_Base_Ajax_Filter {
        /*********************************************************
        ** TMPL BUILDER
        *********************************************************/
        // This is the standard method to be used in a module to generate the item input template
        // for pre-item, mod-opts and item-inputs
        // fired in self::ac_get_ajax_module_tmpl
        function ac_generate_czr_tmpl_from_map( $tmpl_map ) {
            $html = '';
            $default_input_entries = array(
                'input_type'  => 'text',
                'title'        => '',
                'default'  => '',

                'html_before' => '',
                'notice_before_title' => '',
                'notice_before' => '',
                'notice_after' => '',
                'placeholder' => '',
                'html_after' => '',

                // typically used for the number and range inputs
                'step' => '',
                'min' => '',
                'max' => '',
                'orientation' => '',//vertical / horizontal
                'unit' => '',//% or px for example

                'transport' => '',//<= can be set as a data property of the input wrapper, and used when instanciating the input

                'input_template' => '',//<= a static html template can be provided to render the input, in this case it will be used in priority
                'tmpl_callback' => '',//<= a callback function to be used to print the entire input template, including the wrapper

                'width-100' => false,//<= to force a width of 100%
                'title_width' => '',//width-80
                'input_width' => '',//width-20

                'code_type'   => '',//<= used for to specify the language type of the codemirror editor (if not specified full a html editor will be instantiated)

                'refresh_markup' => null,
                'refresh_stylesheet' => null,
                'refresh_fonts' => null,
                'refresh_preview' => null,

                'sanitize_cb' => '',
                'validate_cb' => '',

                'css_selectors' => array(), //<= used to specify css selectors on which we will apply the dynamically generated css for a given input id @see \Nimble\sek_add_css_rules_for_generic_css_input_types'
                'css_identifier' => '',//<= the identifier allowing us to map a css generation rule. @see \Nimble\sek_add_css_rules_for_css_sniffed_input_id
                'important_input_list' => array(),//<= the list of input_id that an important input can flag !important @see \Nimble\sek_add_css_rules_for_css_sniffed_input_id

                'choices' => array(), // <= used to declare the option list of a select input

                'has_device_switcher' => false, // <= indicates if the input value shall be saved by device or not

                'scope' => 'local',// <= used when resetting the sections
                // introduced for https://github.com/presscustomizr/nimble-builder/issues/403
                'editor_params' => array(),

                // introduced for https://github.com/presscustomizr/nimble-builder/issues/431
                'section_collection' => array()
            );
            foreach( $tmpl_map as $input_id => $input_data ) {
                if ( !is_string( $input_id ) || empty( $input_id ) ) {
                    wp_send_json_error( __FUNCTION__ . ' => wrong input id' );
                    break;
                }
                if ( !is_array( $input_data ) ) {
                    wp_send_json_error( __FUNCTION__ . ' => wrong var type for the input_data of input id : ' . esc_attr($input_id) );
                    break;
                }
                // check that we have no unknown entries in the provided input_data
                $maybe_diff = array_diff_key( $input_data, $default_input_entries );
                if ( !empty( $maybe_diff ) ) {
                    error_log('<' . __FUNCTION__ . '>');
                    error_log( '=> at least one unknown param in the registered input params for input id : ' . esc_attr($input_id) );
                    error_log( print_r( $maybe_diff, true ) );
                    error_log('</' . __FUNCTION__ . '>');
                    break;
                }

                // we're clear, let's go
                $input_data = wp_parse_args( $input_data, $default_input_entries );

                // Do we have a specific template provided ?
                if ( !empty( $input_data[ 'tmpl_callback' ] ) && function_exists( $input_data[ 'tmpl_callback' ] ) ) {
                    $html .= call_user_func_array( $input_data[ 'tmpl_callback' ], array( $input_data ) );
                } else {
                    $html .= $this->ac_get_default_input_tmpl( $input_id, $input_data );
                }

            }
            return $html;////will be sent by wp_send_json_success() in ::ac_set_ajax_czr_tmpl()
        }



        // Fired in ac_generate_czr_tmpl_from_map
        function ac_get_default_input_tmpl( $input_id, $input_data ) {
            if ( !array_key_exists( 'input_type', $input_data ) || empty( $input_data[ 'input_type' ] ) ) {
                 wp_send_json_error( 'ac_get_input_tmpl => missing input type for input id : ' . esc_attr($input_id) );
            }
            $input_type = $input_data[ 'input_type' ];

            // some inputs have a width of 100% even if not specified in the input_data
            $is_width_100 = true === $input_data[ 'width-100' ];
            if ( in_array( $input_type, array( 'color', 'radio', 'textarea' ) ) ) {
                $is_width_100 = true;
            }

            $css_attr = $this->czr_css_attr;

            ob_start();
            // <INPUT WRAPPER>
            printf( '<div class="%1$s %2$s %3$s" data-input-type="%4$s" %5$s>',
                esc_attr($css_attr['sub_set_wrapper']),
                $is_width_100 ? 'width-100' : '',
                'hidden' === $input_type ? 'hidden' : '',
                esc_attr($input_type),
                esc_attr(!empty( $input_data['transport'] ) ? 'data-transport="'. $input_data['transport'] .'"' : '')
            );
            ?>
            <?php if ( !empty( $input_data['html_before'] ) ) : ?>
                <div class="czr-html-before"><?php echo wp_kses_post($input_data['html_before']); ?></div>
            <?php endif; ?>

            <?php if ( !empty( $input_data['notice_before_title'] ) ) : ?>
                <span class="czr-notice"><?php echo wp_kses_post($input_data['notice_before_title']); ?></span><br/>
            <?php endif; ?>

            <?php
            // no need to print a title for an hidden input
            if ( $input_type !== 'hidden' ) {
                printf( '<div class="customize-control-title %1$s">%2$s</div>',
                  esc_attr(!empty( $input_data['title_width'] ) ? $input_data['title_width'] : ''),
                  wp_kses_post($input_data['title'])
                );
            }
            ?>
            <?php if ( !empty( $input_data['notice_before'] ) ) : ?>
                <span class="czr-notice"><?php echo wp_kses_post($input_data['notice_before']); ?></span>
            <?php endif; ?>

            <?php printf( '<div class="czr-input %1$s">', esc_attr(!empty( $input_data['input_width'] ) ? $input_data['input_width'] : '' ) ); ?>

            <?php
            if ( !empty( $input_data['input_template'] ) && is_string( $input_data['input_template'] ) ) {
                echo wp_kses_post($input_data['input_template']);
            } else {
                // THIS IS WHERE THE ACTUAL INPUT CONTENT IS SET
                $this->ac_set_input_tmpl_content( $input_type, $input_id, $input_data );
            }
            ?>
              </div><?php // class="czr-input" ?>
              <?php if ( !empty( $input_data['notice_after'] ) ) : ?>
                  <span class="czr-notice"><?php echo wp_kses_post($input_data['notice_after']); ?></span>
              <?php endif; ?>

              <?php if ( !empty( $input_data['html_after'] ) ) : ?>
                <div class="czr-html-after"><?php echo wp_kses_post($input_data['html_after']); ?></div>
              <?php endif; ?>

            </div> <?php //class="$css_attr['sub_set_wrapper']" ?>
            <?php
            // </INPUT WRAPPER>

            $tmpl_html = apply_filters( "czr_set_input_tmpl___{$input_type}", ob_get_clean(), $input_id, $input_data );
            //error_log( print_r($tmpl_html, true ) );
            if ( empty( $tmpl_html ) ) {
                wp_send_json_error( 'ac_get_input_tmpl => no html returned for input ' . esc_attr($input_id) );
            }
            return $tmpl_html;
        }//ac_get_input_tmpl()







        // fired in ::ac_get_default_input_tmpl();
        private function ac_set_input_tmpl_content( $input_type, $input_id, $input_data ) {
            $css_attr = $this->czr_css_attr;
            $input_tmpl_content = null;
            // First fires a hook to allow the input content to be remotely set
            // For example the module_picker, the spacing, h_text_alignment... are printed this way
            ob_start();
              do_action( 'czr_set_input_tmpl_content', $input_type, $input_id, $input_data );
            $input_tmpl_content = ob_get_clean();

            if ( !empty( $input_tmpl_content ) ) {
                echo wp_kses_post($input_tmpl_content);
            } else {
                // Then, if we have no content yet, let's go thought the default input cases
                switch ( $input_type ) {
                    /* ------------------------------------------------------------------------- *
                     *  HIDDEN
                    /* ------------------------------------------------------------------------- */
                    case 'hidden':
                      ?>
                        <input data-czrtype="<?php echo esc_attr($input_id); ?>" type="hidden" value=""></input>
                      <?php
                    break;

                    /* ------------------------------------------------------------------------- *
                     *  SELECT
                    /* ------------------------------------------------------------------------- */
                    case 'czr_layouts'://<= specific to the hueman theme
                    case 'select'://<= used in the customizr and hueman theme
                    case 'simpleselect'://<=used in Nimble Builder
                      ?>
                        <select data-czrtype="<?php echo esc_attr($input_id); ?>"></select>
                      <?php
                    break;
                    // multiselect with select2() js library
                    case 'multiselect':
                    case 'category_picker':
                      ?>
                        <select multiple="multiple" data-czrtype="<?php echo esc_attr($input_id); ?>"></select>
                      <?php
                    break;

                    /* ------------------------------------------------------------------------- *
                     *  TEXT
                    /* ------------------------------------------------------------------------- */
                    case 'text' :
                      ?>
                        <input data-czrtype="<?php echo esc_attr($input_id); ?>" type="text" value="" placeholder="<?php echo esc_attr($input_data['placeholder']); ?>"></input>
                      <?php
                    break;

                    /* ------------------------------------------------------------------------- *
                     *  NUMBER
                    /* ------------------------------------------------------------------------- */
                    case 'number' :
                      ?>
                        <?php
                        printf( '<input data-czrtype="%4$s" type="number" %1$s %2$s %3$s value="{{ data[\'%4$s\'] }}" />',
                          esc_attr(!empty( $input_data['step'] ) ? 'step="'. $input_data['step'] .'"' : ''),
                          esc_attr(!empty( $input_data['min'] ) ? 'min="'. $input_data['min'] .'"' : ''),
                          esc_attr(!empty( $input_data['max'] ) ? 'max="'. $input_data['max'] .'"' : ''),
                          esc_attr($input_id)
                        );
                        ?>
                      <?php
                    break;

                    /* ------------------------------------------------------------------------- *
                     *  COLOR
                    /* ------------------------------------------------------------------------- */
                    case 'wp_color_alpha' :
                      ?>
                        <input data-czrtype="<?php echo esc_attr($input_id); ?>" class="width-100"  data-alpha="true" type="text" value="{{ data['<?php echo esc_attr($input_id); ?>'] }}"></input>
                      <?php
                    break;
                    case 'color' :
                      ?>
                        <input data-czrtype="<?php echo esc_attr($input_id); ?>" type="text" value="{{ data['<?php echo esc_attr($input_id); ?>'] }}"></input>
                      <?php
                    break;

                    /* ------------------------------------------------------------------------- *
                     *  CHECK
                    /* ------------------------------------------------------------------------- */
                    case 'checkbox' :
                    case 'check' :
                      ?>
                        <#
                          var _checked = ( false != data['<?php echo esc_attr($input_id); ?>'] ) ? "checked=checked" : '';
                        #>
                        <input data-czrtype="<?php echo esc_attr($input_id); ?>" type="checkbox" {{ _checked }}></input>
                      <?php
                    break;

                    // DEPRECATED since april 2nd 2019
                    case 'gutencheck' :
                        ?>
                          <#
                            var _checked = ( false != data['<?php echo esc_attr($input_id); ?>'] ) ? "checked=checked" : '';
                          #>
                          <span class="czr-toggle-check"><input class="czr-toggle-check__input" data-czrtype="<?php echo esc_attr($input_id); ?>" type="checkbox" {{ _checked }}><span class="czr-toggle-check__track"></span><span class="czr-toggle-check__thumb"></span></span>
                        <?php
                    break;

                    case 'nimblecheck' :
                        ?>
                          <#
                            var _checked = ( false != data['<?php echo esc_attr($input_id); ?>'] ) ? "checked=checked" : '';
                          #>
                          <?php
                          // when input and label are tied by an id - for relationship
                          // clicking on any of them changes the input
                          // => We need a unique ID here so that input and label are tied by a unique link
                          // @see https://www.w3.org/TR/html401/interact/forms.html#h-17.9.1
                          // @see https://developer.mozilla.org/en-US/docs/Web/HTML/Element/Input/checkbox
                          $unique_id = sprintf('%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535));
                          ?>
                          <div class="nimblecheck-wrap">
                            <input id="nimblecheck-<?php echo esc_attr($unique_id); ?>" data-czrtype="<?php echo esc_attr($input_id); ?>" type="checkbox" {{ _checked }} class="nimblecheck-input">
                            <label for="nimblecheck-<?php echo esc_attr($unique_id); ?>" class="nimblecheck-label">Switch</label>
                          </div>
                        <?php
                    break;

                    /* ------------------------------------------------------------------------- *
                     *  TEXTAREA
                    /* ------------------------------------------------------------------------- */
                    case 'textarea' :
                      // Added an id attribute for https://github.com/presscustomizr/nimble-builder/issues/403
                      // needed to instantiate wp.editor.initialize(...)
                      ?>
                        <textarea id="textarea-{{ data.id }}" data-czrtype="<?php echo esc_attr($input_id); ?>" class="width-100" name="textarea" rows="10" cols="">{{ data.value }}</textarea>
                      <?php
                    break;

                    /* ------------------------------------------------------------------------- *
                     *  IMG UPLOAD AND UPLOAD URL
                    /* ------------------------------------------------------------------------- */
                    case 'upload' :
                    case 'upload_url' :
                      ?>
                        <input data-czrtype="<?php echo esc_attr($input_id); ?>" type="hidden"/>
                        <div class="<?php echo esc_attr($css_attr['img_upload_container']); ?>"></div>
                      <?php
                    break;

                    /* ------------------------------------------------------------------------- *
                     *  RANGE
                    /* ------------------------------------------------------------------------- */
                    case 'range_slider' :
                    case 'range' :
                      ?>
                        <?php //<# //console.log( 'IN php::ac_get_default_input_tmpl() => data range_slide => ', data ); #> ?>
                        <?php
                        printf( '<input data-czrtype="%5$s" type="range" %1$s %2$s %3$s %4$s value="{{ data[\'%5$s\'] }}" />',
                          esc_attr(!empty( $input_data['orientation'] ) ? 'data-orientation="'. $input_data['orientation'] .'"' : ''),
                          esc_attr(!empty( $input_data['unit'] ) ? 'data-unit="'. $input_data['unit'] .'"' : ''),
                          esc_attr(!empty( $input_data['min'] ) ? 'min="'. $input_data['min'] .'"' : ''),
                          esc_attr(!empty( $input_data['max'] ) ? 'max="'. $input_data['max'] .'"' : ''),
                          esc_attr($input_id)
                        );
                        ?>
                      <?php
                    break;

                    /* ------------------------------------------------------------------------- *
                     *  CONTENT PICKER
                    /* ------------------------------------------------------------------------- */
                    case 'content_picker' :
                      ?>
                        <?php
                        printf( '<span data-czrtype="%1$s"></span>', esc_attr($input_id) );
                        ?>
                      <?php
                    break;

                    /* ------------------------------------------------------------------------- *
                     *  PROBLEM : if we reach this case, it means that
                     *  - the input template has not been populated by the first do_action('czr_set_input_tmpl_content')
                     *  - no default input template is defined for the requested input type
                    /* ------------------------------------------------------------------------- */
                    default :
                        // this input type has no template, this is a problem
                        wp_send_json_error( 'ERROR => ' . __CLASS__ . '::' . __FUNCTION__ . ' this input type has no template : ' . $input_type );
                    break;
                }//switch ( $input_type ) {
            }//if ( empty( $input_tmpl_content ) )()
        }//function()

    }//class
endif;

?>