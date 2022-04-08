<?php
if ( !class_exists( '\Nimble\Sek_Simple_Form' ) ) :
class Sek_Simple_Form extends SEK_Front_Render_Css {

    private $form;
    private $fields;
    private $mailer;

    private $form_composition;

    function _setup_simple_forms() {
        //Hooks
        add_action( 'parse_request', array( $this, 'simple_form_parse_request' ), 20 );
        add_action( 'wp_enqueue_scripts', array( $this, 'maybe_enqueue_recaptcha_scripts' ), 0 );
        add_filter( 'body_class', array( $this, 'set_the_recaptcha_badge_visibility_class') );

        // Note : form input need to be prefixed to avoid a collision with reserved WordPress input
        // @see : https://stackoverflow.com/questions/15685020/wordpress-form-submission-and-the-404-error-page#16636051
        $this->form_composition = array(
            'nimble_simple_cf'              => array(
                'type'            => 'hidden',
                'value'           => 'nimble_simple_cf'
            ),
            'nimble_recaptcha_resp'   => array(
                'type'            => 'hidden',
                'value'           => ''
            ),
            'nimble_skope_id'     => array(
                'type'            => 'hidden',
                'value'           => ''
            ),
            'nimble_level_id'     => array(
                'type'            => 'hidden',
                'value'           => ''
            ),
            'nimble_name' => array(
                'label'            => __( 'Name', 'text_doma' ),
                'required'         => true,
                'type'             => 'text',
                'wrapper_tag'      => 'div'
            ),
            'nimble_email' => array(
                'label'            => __( 'Email', 'text_doma' ),
                'required'         => true,
                'type'             => 'email',
                'wrapper_tag'      => 'div'
            ),
            'nimble_subject' => array(
                'label'            => __( 'Subject', 'text_doma' ),
                'type'             => 'text',
                'wrapper_tag'      => 'div'
            ),
            'nimble_message' => array(
                'label'            => __( 'Message', 'text_doma' ),
                'required'         => true,
                'additional_attrs' => array( 'rows' => "10", 'cols' => "50" ),
                'type'             => 'textarea',
                'wrapper_tag'      => 'div'
            ),
            'nimble_privacy' => array(
                'label'            => __( 'I have read and agree to the privacy policy.', 'text_doma' ),
                'type'             => 'checkbox',
                'required'         => true,
                'value'            => false,
                //'additional_attrs' => array( 'class' => 'sek-btn' ),
                'wrapper_tag'      => 'div',
                'wrapper_class'    => array( 'sek-form-field', 'sek-privacy-wrapper' )
            ),
            'nimble_submit' => array(
                'type'             => 'submit',
                'value'            => __( 'Submit', 'text_doma' ),
                'additional_attrs' => array( 'class' => 'sek-btn' ),
                'wrapper_tag'      => 'div',
                'wrapper_class'    => array( 'sek-form-field', 'sek-form-btn-wrapper' )
            )
        );
    }//_setup_simple_forms


    //@hook: parse_request
    function simple_form_parse_request() {
        if ( !isset( $_POST['nimble_simple_cf'] ) )
          return;

        // get the module options
        // we are before 'wp', so let's use the posted skope_id and level_id to get our $module_user_values
        $module_model = array();
        if ( isset( $_POST['nimble_skope_id'] ) && '_skope_not_set_' !== sanitize_text_field($_POST['nimble_skope_id']) ) {
            $local_sektions = sek_get_skoped_seks( sanitize_text_field($_POST['nimble_skope_id']) );
            if ( is_array( $local_sektions ) && !empty( $local_sektions ) ) {
            $sektion_collection = array_key_exists('collection', $local_sektions) ? $local_sektions['collection'] : array();
            }
            if ( is_array($sektion_collection) && !empty( $sektion_collection ) && isset( $_POST['nimble_level_id'] ) ) {
                $module_model = sek_get_level_model( sanitize_text_field($_POST['nimble_level_id']), $sektion_collection );
                $module_model = sek_normalize_module_value_with_defaults( $module_model );
            }
        } else {
            sek_error_log( __FUNCTION__ . ' => skope_id problem');
            return;
        }

        if ( empty( $module_model ) ) {
            sek_error_log( __FUNCTION__ . ' => invalid module model');
            return;
        }

        //update the form with the posted values
        foreach ( $this->form_composition as $name => $field ) {
            $form_composition[ $name ]                = $field;
            if ( isset( $_POST[ $name ] ) ) {
                $form_composition[ $name ][ 'value' ] = sanitize_text_field($_POST[ $name ]);
            }
        }
        //set the form composition according to the user's options
        $form_composition = $this->_set_form_composition( $form_composition, $module_model );
        //generate fields
        $this->fields = $this->simple_form_generate_fields( $form_composition );
        //generate form
        $this->form = $this->simple_form_generate_form( $this->fields, $module_model );

        //mailer
        $this->mailer = new Sek_Mailer( $this->form );
        $this->mailer->maybe_send( $form_composition, $module_model );
    }

    // Fired @hook wp_enqueue_scripts
    // @return void()
    function maybe_enqueue_recaptcha_scripts() {
        // enabled if
        // - not customizing
        // - global 'recaptcha' options has the following values
        //    - enabled === true
        //    - public_key entered
        //    - private_key entered
        // - the current page does not include a form in a local or global location
        if ( skp_is_customizing() || !sek_is_recaptcha_globally_enabled() || !sek_front_sections_include_a_form() )
          return;

        // @todo, we don't handle the case when reCaptcha is globally enabled but disabled for a particular form.

        $global_recaptcha_opts = sek_get_global_option_value('recaptcha');
        $global_recaptcha_opts = is_array( $global_recaptcha_opts ) ? $global_recaptcha_opts : array();

        $url = add_query_arg(
            array( 'render' => esc_attr( $global_recaptcha_opts['public_key'] ) ),
            'https://www.google.com/recaptcha/api.js'
        );

        wp_enqueue_script( 'google-recaptcha', $url, array(), '3.0', true );
        add_action('wp_head', array( $this, 'print_recaptcha_inline_js'), PHP_INT_MAX );
    }

    // @hook wp_footer
    // printed only when sek_is_recaptcha_globally_enabled()
    // AND
    // sek_front_sections_include_a_form()
    function print_recaptcha_inline_js() {
        ob_start();
        ?>
              if ( sekFrontLocalized.recaptcha_public_key ) {
                !( function( grecaptcha, sitekey ) {
                    var recaptcha = {
                        execute: function() {
                            var _action = ( window.sekFrontLocalized && sekFrontLocalized.skope_id ) ? sekFrontLocalized.skope_id.replace( 'skp__' , 'nimble_form__' ) : 'nimble_builder_form';
                            grecaptcha.execute(
                                sitekey,
                                // see https://developers.google.com/recaptcha/docs/v3#actions
                                { action: _action }
                            ).then( function( token ) {
                                var forms = document.getElementsByTagName( 'form' );
                                for ( var i = 0; i < forms.length; i++ ) {
                                    var fields = forms[ i ].getElementsByTagName( 'input' );
                                    for ( var j = 0; j < fields.length; j++ ) {
                                        var field = fields[ j ];
                                        if ( 'nimble_recaptcha_resp' === field.getAttribute( 'name' ) ) {
                                            field.setAttribute( 'value', token );
                                            break;
                                        }
                                    }
                                }
                            } );
                        }
                    };
                    grecaptcha.ready( recaptcha.execute );
                })( grecaptcha, sekFrontLocalized.recaptcha_public_key );
              } else {
                if ( window.console && window.console.log ) {
                    console.log('Nimble Builder form error => missing reCAPTCHA key');
                }
              }
        <?php
        $script = ob_get_clean();
        wp_register_script( 'nb_recaptcha_js', '');
        wp_enqueue_script( 'nb_recaptcha_js' );
        wp_add_inline_script( 'nb_recaptcha_js', $script );
    }

    // @hook body_class
    public function set_the_recaptcha_badge_visibility_class( $classes ) {
        // Shall we print the badge ?
        // @todo : we don't handle the case when recaptcha badge is globally displayed but the current page has disabled recaptcha
        $classes[] = !sek_is_recaptcha_badge_globally_displayed() ? 'sek-hide-rc-badge' : 'sek-show-rc-badge';
        return $classes;
    }


    // Rendering
    // Invoked from the tmpl
    // @return string
    // @param module_options is the module level "value" property. @see tmpl/modules/simple_form_module_tmpl.php
    function get_simple_form_html( $module_model ) {
        // sek_error_log('$module_model ?', $module_model );
        // sek_error_log('$this->fields ?', $this->fields );
        // sek_error_log('$this->form ?', $this->form );
        // sek_error_log('$this->mailer ?', $this->mailer );
        // sek_error_log('$_POST ?', $_POST );
        $html         = '';
        //set the form composition according to the user's options
        $form_composition = $this->_set_form_composition( $this->form_composition, $module_model );
        //generate fields
        $fields       = isset( $this->fields ) ? $this->fields : $this->simple_form_generate_fields( $form_composition );
        //generate form
        $form         = isset( $this->form ) ? $this->form : $this->simple_form_generate_form( $fields, $module_model );

        $module_id = is_array( $module_model ) && array_key_exists('id', $module_model ) ? $module_model['id'] : '';
        ob_start();
        ?>
        <div id="sek-form-respond">
          <?php
            $echo_form = true;
            // When loading the page after a send attempt, focus on the module html element with a javascript animation
            // In this case, don't echo the form, but only the user defined message which should be displayed after submitting the form
            if ( !is_null( $this->mailer ) ) {
                // Make sure we target the right form if several forms are displayed in a page
                $current_form_has_been_submitted = isset( $_POST['nimble_level_id'] ) && sanitize_text_field($_POST['nimble_level_id']) === $module_id;

                if ( 'sent' == $this->mailer->get_status() && $current_form_has_been_submitted ) {
                    $echo_form = false;
                }
            }

            if ( !$echo_form ) {
                ob_start();
                ?>
                      nb_.listenTo( 'nb-jquery-loaded', function() {
                            jQuery( function($) {
                                var $elToFocusOn = $('div[data-sek-id="<?php echo esc_attr($module_id); ?>"]' );
                                if ( $elToFocusOn.length > 0 ) {
                                      var _do = function() {
                                          $('html, body').animate({
                                              scrollTop : $elToFocusOn.offset().top - ( $(window).height() / 2 ) + ( $elToFocusOn.outerHeight() / 2 )
                                          }, 'slow');
                                      };
                                      try { _do(); } catch(er) {}
                                }
                            });
                      });
                <?php
                $script = ob_get_clean();
                wp_register_script( 'nb_simple_form_js', '');
                wp_enqueue_script( 'nb_simple_form_js' );
                wp_add_inline_script( 'nb_simple_form_js', $script );

                $message = $this->mailer->get_message( $this->mailer->get_status(), $module_model );
                if ( !empty($message) ) {
                    $class = 'sek-mail-failure';
                    switch( $this->mailer->get_status() ) {
                          case 'sent' :
                              $class = 'sek-mail-success';
                          break;
                          case 'not_sent' :
                              $class = '';
                          break;
                          case 'aborted' :
                              $class = 'sek-mail-aborted';
                          break;
                    }
                    printf( '<div class="sek-form-message %1$s">%2$s</div>', esc_attr($class), wp_kses_post($message) );
                }
            } else {
                // If we're in the regular case ( not after submission ), echo the form
                echo $form;//The output is late escaped in tmpl/modules/simple_form_module_tmpl.php, as this function only returns the html content
            }
          ?>
        </div>
        <?php
        return ob_get_clean();
    }


    //set the fields to render
    private function _set_form_composition( $form_composition, $module_model = array() ) {

        $user_form_composition = array();
        if ( !is_array( $module_model ) ) {
              sek_error_log( __CLASS__ . '::' . __FUNCTION__ . ' => ERROR : invalid module options array');
              return $user_form_composition;
        }
        $module_user_values = array_key_exists( 'value', $module_model ) ? $module_model['value'] : array();
        //sek_error_log( '$module_model', $module_model );
        $form_fields_options = empty( $module_user_values['form_fields'] ) ? array() : $module_user_values['form_fields'];
        $form_button_options = empty( $module_user_values['form_button'] ) ? array() : $module_user_values['form_button'];
        $form_submission_options = empty( $module_user_values['form_submission'] ) ? array() : $module_user_values['form_submission'];

        foreach ( $form_composition as $field_id => $field_data ) {
            //sek_error_log( '$field_data', $field_data );
            switch ( $field_id ) {
                case 'nimble_name':
                    if ( !empty( $form_fields_options['show_name_field'] ) && sek_is_checked( $form_fields_options['show_name_field'] ) ) {
                        $user_form_composition[$field_id] = $field_data;
                        $user_form_composition[$field_id]['required'] = sek_is_checked( $form_fields_options['name_field_required'] );
                        $user_form_composition[$field_id]['label'] = esc_attr( $form_fields_options['name_field_label'] );
                    }
                break;
                case 'nimble_subject':
                    if ( !empty( $form_fields_options['show_subject_field'] ) && sek_is_checked( $form_fields_options['show_subject_field'] ) ) {
                        $user_form_composition[$field_id] = $field_data;
                        $user_form_composition[$field_id]['required'] = sek_is_checked( $form_fields_options['subject_field_required'] );
                        $user_form_composition[$field_id]['label'] = esc_attr( $form_fields_options['subject_field_label'] );
                    }
                break;
                case 'nimble_message':
                    if ( !empty( $form_fields_options['show_message_field'] ) && sek_is_checked( $form_fields_options['show_message_field'] ) ) {
                        $user_form_composition[$field_id] = $field_data;
                        $user_form_composition[$field_id]['required'] = sek_is_checked( $form_fields_options['message_field_required'] );
                        $user_form_composition[$field_id]['label'] = esc_attr( $form_fields_options['message_field_label'] );
                    }
                break;
                case 'nimble_email':
                    $user_form_composition[$field_id] = $field_data;
                    $user_form_composition[$field_id]['label'] = esc_attr( $form_fields_options['email_field_label'] );
                break;
                case 'nimble_privacy':
                    if ( !empty( $form_fields_options['show_privacy_field'] ) && sek_is_checked( $form_fields_options['show_privacy_field'] ) ) {
                        $user_form_composition[$field_id] = $field_data;
                        $user_form_composition[$field_id]['required'] = sek_is_checked( $form_fields_options['privacy_field_required'] );
                        // prevent users running script in this field while customizing
                        $user_form_composition[$field_id]['label'] = sek_strip_script_tags_and_print_js_inline( $form_fields_options['privacy_field_label'], $module_model );
                        // Feb 2021 : now saved as a json to fix emojis issues
                        // see fix for https://github.com/presscustomizr/nimble-builder/issues/544
                        // to ensure retrocompatibility with data previously not saved as json, we need to perform a json validity check
                        $user_form_composition[$field_id]['label'] = sek_maybe_decode_richtext( $user_form_composition[$field_id]['label'] );
                    }
                break;

                //'additional_attrs' => array( 'class' => 'sek-btn' ),
                case 'nimble_submit':
                    $user_form_composition[$field_id] = $field_data;
                    $visual_effect_class = '';
                    //visual effect classes
                    if ( array_key_exists( 'use_box_shadow', $form_button_options ) && true === sek_booleanize_checkbox_val( $form_button_options['use_box_shadow'] ) ) {
                        $visual_effect_class = ' box-shadow';
                        if ( array_key_exists( 'push_effect', $form_button_options ) && true === sek_booleanize_checkbox_val( $form_button_options['push_effect'] ) ) {
                            $visual_effect_class .= ' push-effect';
                        }
                    }
                    $user_form_composition[$field_id]['additional_attrs']['class'] = 'sek-btn' . $visual_effect_class;

                    // Feb 2021 : now saved as a json to fix emojis issues
                    // see fix for https://github.com/presscustomizr/nimble-builder/issues/544
                    // to ensure retrocompatibility with data previously not saved as json, we need to perform a json validity check
                    $user_form_composition[$field_id]['value'] = sek_maybe_decode_richtext( $form_fields_options['button_text'] );
                break;
                case 'nimble_skope_id':
                    $user_form_composition[$field_id] = $field_data;
                    // When the form is submitted, we grab the skope_id from the posted value, because it is too early to build it.
                    // of course we don't need to set this input value when customizing.
                    $skope_id = '';
                    if ( !skp_is_customizing() ) {
                        $skope_id = isset( $_POST['nimble_skope_id'] ) ? sanitize_text_field($_POST['nimble_skope_id']) : sek_get_level_skope_id( $module_model['id'] );
                    }

                    // always use the posted skope_id
                    // => in a scenario in which we post the form several times, the skp_get_skope_id() won't be available after the first submit action
                    $user_form_composition[$field_id]['value'] = $skope_id;
                break;
                case 'nimble_level_id':
                    $user_form_composition[$field_id] = $field_data;
                    $user_form_composition[$field_id]['value'] = $module_model['id'];
                break;
                // print the recaptcha input field if
                // 1) reCAPTCHA enabled in the global options AND properly setup with non empty keys
                // 2) reCAPTCHA enabled for this particular form
                case 'nimble_recaptcha_resp' :
                    if ( !skp_is_customizing() && sek_is_recaptcha_globally_enabled() && 'disabled' !== $form_submission_options['recaptcha_enabled'] ) {
                        $user_form_composition[$field_id] = $field_data;
                    }
                break;
                default:
                    $user_form_composition[$field_id] = $field_data;
                break;
            }

        }
        return $user_form_composition;
    }


    //generate the fields
    function simple_form_generate_fields( $form_composition = array() ) {
        $form_composition = empty( $form_composition ) ? $this->form_composition : $form_composition;
        $fields_ = array();
        $id_suffix = rand();
        foreach ( $form_composition as $name => $field ) {
            $field = wp_parse_args( $field, array( 'type' => 'text' ) );

            if ( class_exists( $class = '\Nimble\Sek_Input_' . ucfirst( $field['type'] ) ) ) {
                $fields_ [] = new Sek_Field (
                    new $class( array_merge( array( 'id_suffix'=> $id_suffix ), $field, array( 'name' => $name ) ) ),
                    $field
                );
            }
        }

        return $fields_;
    }


    //generate the fields
    function simple_form_generate_form( $fields, $module_model ) {
        $form   = new Sek_Form( [
            'action' => is_array( $module_model ) && !empty( $module_model['id']) ? '#' . $module_model['id'] :'#',
            'method' => 'post'
        ] );
        $form->add_fields( $fields );

        return $form;
    }
}//Sek_Simple_Form
endif;

?>