<?php
/*
*
* Mailer class definition
*
*/
if ( !class_exists( '\Nimble\Sek_Mailer' ) ) :
class Sek_Mailer {
    private $form;
    private $status;
    private $messages;
    private $invalid_field = false;
    public $recaptcha_errors = '_no_error_';//will store array( 'endpoint' => $endpoint, 'request' => $request, 'response' => '' );

    public function __construct( Sek_Form $form ) {
        $this->form = $form;

        $this->messages = array(
            //status          => message
            //'not_sent'        => __( 'Message was not sent. Try Again.', 'text_doma'),
            //'sent'            => __( 'Thanks!Your message has been sent.', 'text_doma'),
            'aborted'         => __( 'Please supply correct information.', 'text_doma') //<-todo too much generic
        );
        $this->status = 'init';

        // Validate reCAPTCHA if submitted
        // When sek_is_recaptcha_globally_enabled(), the hidden input 'nimble_recaptcha_resp' is rendered with a value set to a token remotely fetched with a js script
        // @see print_recaptcha_inline_js
        // on submission, we get the posted token value, and validate it with a remote http request to the google api
        if ( isset( $_POST['nimble_recaptcha_resp'] ) ) {
            if ( !$this->validate_recaptcha( sanitize_text_field($_POST['nimble_recaptcha_resp']) ) ) {
                $this->status = 'recaptcha_fail';
                if ( sek_is_dev_mode() ) {
                    sek_error_log('reCAPTCHA failure', $this->recaptcha_errors );
                }
            }
        }
    }


    //@return bool
    private function validate_recaptcha( $recaptcha_token ) {
        $is_valid = false;
        $endpoint = 'https://www.google.com/recaptcha/api/siteverify';
        $global_recaptcha_opts = sek_get_global_option_value('recaptcha');
        $global_recaptcha_opts = is_array( $global_recaptcha_opts ) ? $global_recaptcha_opts : array();
        // the user did not enter the key yet.
        // let's validate
        if ( empty($global_recaptcha_opts['private_key']) )
          return true;

        //$public = $global_recaptcha_opts['public_key'];
        $request = array(
            'body' => array(
                'secret' => $global_recaptcha_opts['private_key'],
                'response' => $recaptcha_token
            ),
        );

        // cache the recaptcha_errors
        $response = wp_remote_post( esc_url_raw( $endpoint ), $request );
        if ( is_array( $response ) ) {
            $maybe_recaptcha_errors = wp_remote_retrieve_body( $response );
            $maybe_recaptcha_errors = json_decode( $maybe_recaptcha_errors );
            $maybe_recaptcha_errors = is_object($maybe_recaptcha_errors) ? (array)$maybe_recaptcha_errors : $maybe_recaptcha_errors;
            if ( is_array( $maybe_recaptcha_errors ) && isset( $maybe_recaptcha_errors['error-codes'] ) && is_array( $maybe_recaptcha_errors['error-codes'] ) ) {
                $this->recaptcha_errors = implode(', ', $maybe_recaptcha_errors['error-codes'] );
            }

        }

        //sek_error_log('reCAPTCHA response ?', $response );
        // There
        if ( 200 != wp_remote_retrieve_response_code( $response ) ) {
            $this->recaptcha_errors = sprintf( __('There was a problem when performing the reCAPTCHA http request.') );
            return $is_valid;
        }

        // At this point, we can check the score if there not already an error messages, like a re-submission problem for example
        if ( '_no_error_' === $this->recaptcha_errors ) {
            $response_body = wp_remote_retrieve_body( $response );
            $response_body = json_decode( $response_body, true );

            // see https://developers.google.com/recaptcha/docs/v3#score
            $score = isset( $response_body['score'] ) ? $response_body['score'] : 0;

            // get the user defined threshold
            // must be normalized to be 0 >= threshold >= 1
            $user_score_threshold = array_key_exists('score', $global_recaptcha_opts) ? $global_recaptcha_opts['score'] : 0.5;
            $user_score_threshold = !is_numeric( $user_score_threshold ) ? 0.5 : $user_score_threshold;
            $user_score_threshold = $user_score_threshold > 1 ? 1 : $user_score_threshold;
            $user_score_threshold = $user_score_threshold < 0 ? 0 : $user_score_threshold;
            $user_score_threshold = apply_filters( 'nimble_recaptcha_score_treshold', $user_score_threshold );

            $is_valid = $is_human = $user_score_threshold < $score;
            if ( !$is_valid ) {
                $this->recaptcha_errors = sprintf( __('Google reCAPTCHA returned a score of %s, which is lower than your threshold of %s.', 'text_dom' ), $score, $user_score_threshold );
            }
        }

        return $is_valid;
    }


    // Depending on the user options, some fields might exists in the $form object
    // We need to check their existence ( @see https://github.com/presscustomizr/nimble-builder/issues/399 )
    public function maybe_send( $form_composition, $module_model ) {
        // the captcha validation has been made on Sek_Mailer instantiation
        if ( 'recaptcha_fail' === $this->status ) {
            return;
        }

        //sek_error_log('$form_composition ??', $form_composition );
        //sek_error_log('$module_model ??', $module_model );
        //sek_error_log('$this->form ??', $form_composition , $this->form );

        $invalid_field = $this->form->has_invalid_field();
        if ( false !== $invalid_field ) {
            $this->status = 'aborted';
            $this->invalid_field = $invalid_field;
            return;
        }

        $module_user_values = array_key_exists( 'value', $module_model ) ? $module_model['value'] : array();
        //sek_error_log( '$module_model', $module_model );
        $submission_options = empty( $module_user_values['form_submission'] ) ? array() : $module_user_values['form_submission'];

        //<-allow html?->TODO: turn into option
        $allow_html     = true;

        $sender_email   = $this->form->get_field('nimble_email')->get_input()->get_value();

        // Define a default sender name + make sure the field exists
        // fixes https://github.com/presscustomizr/nimble-builder/issues/513
        $sender_name    = __('Someone', 'text_doma');
        $sender_name_is_set = false;
        if ( is_array( $form_composition ) && array_key_exists( 'nimble_name', $form_composition ) ) {
            $sender_name_candidate  = sprintf( '%1$s', $this->form->get_field('nimble_name')->get_input()->get_value() );
            if ( !empty( $sender_name_candidate ) ) {
                $sender_name = $sender_name_candidate;
                $sender_name_is_set = true;
            }
        }

        $sender_body_message = null === $this->form->get_field('nimble_message') ? '' : $this->form->get_field('nimble_message')->get_input()->get_value();

        if ( array_key_exists( 'recipients', $submission_options ) ) {
            $recipient      = $submission_options['recipients'];
        } else {
            $recipient      = get_option( 'admin_email' );
        }

        if ( array_key_exists( 'nimble_subject' , $form_composition ) ) {
            $subject = $this->form->get_field('nimble_subject')->get_input()->get_value();
        } else if ( $sender_name_is_set ) {
            $subject = sprintf( __( '%1$s sent a message from %2$s', 'text_doma' ), $sender_name, get_bloginfo( 'name' ) );
        } else {
            $subject = sprintf( __( 'Someone sent a message from %1$s', 'text_doma' ), get_bloginfo( 'name' ) );
        }



        // $sender_website = sprintf( __( 'Website: %1$s %2$s', 'text_doma' ),
        //     $this->form->get_field('website')->get_input()->get_value(),
        //     $allow_html ? '<br><br><br>': "\r\n\r\n\r\n"
        // );

        // the sender's email is written in the email's header reply-to field.
        // But it is also written inside the message body following this issue, https://github.com/presscustomizr/nimble-builder/issues/218
        $before_message = sprintf( '%1$s: %2$s &lt;%3$s&gt;', __('From', 'text_doma'), $sender_name, $sender_email );//$sender_website;
        $before_message .= sprintf( '<br>%1$s: %2$s', __('Subject', 'text_doma'), $subject );
        $after_message  = '';

        if ( array_key_exists( 'email_footer', $submission_options ) ) {
            // Feb 2021 : now saved as a json to fix emojis issues
            // see fix for https://github.com/presscustomizr/nimble-builder/issues/544
            // to ensure retrocompatibility with data previously not saved as json, we need to perform a json validity check
            $email_footer = sek_maybe_decode_richtext( $submission_options['email_footer'] );
            $email_footer = sek_strip_script_tags( $email_footer );
        } else {
            $email_footer = sprintf( __( 'This e-mail was sent from a contact form on %1$s (<a href="%2$s" target="_blank">%2$s</a>)', 'text_doma' ),
                get_bloginfo( 'name' ),
                get_site_url( 'url' )
            );
        }

        if ( !empty( $sender_body_message ) ) {
            $sender_body_message = sprintf( '<br><br>%1$s: <br>%2$s',
                __('Message body', 'text_doma'),
                //$allow_html ? '<br><br>': "\r\n\r\n",
                $sender_body_message
            );
        }

        $body = sprintf( '%1$s%2$s%3$s%4$s%5$s',
            $before_message,
            $sender_body_message,
            $after_message,
            $allow_html ? '<br><br>--<br>': "\r\n\r\n--\r\n",
            $email_footer
        );

        $headers        = array();
        $headers[]      = $allow_html ? 'Content-Type: text/html' : '';
        $headers[]      = 'charset=UTF-8'; //TODO: maybe turn into option

        $headers[]      = sprintf( 'From: %1$s <%2$s>', $sender_name, $this->get_from_email() );
        $headers[]      = sprintf( 'Reply-To: %1$s <%2$s>', $sender_name, $sender_email );

        $this->status   = wp_mail( $recipient, $subject, $body, $headers ) ? 'sent' : 'not_sent';
    }



    public function get_status() {
        return $this->status;
    }


    public function get_message( $status, $module_model ) {
        $module_user_values = array_key_exists( 'value', $module_model ) ? $module_model['value'] : array();
        //sek_error_log( '$module_model', $module_model );
        $submission_options = empty( $module_user_values['form_submission'] ) ? array() : $module_user_values['form_submission'];

        $submission_message = isset( $this->messages[ $status ] ) ? $this->messages[ $status ] : '';

        // the check with strlen( preg_replace('/\s+/' ... ) allow user to "hack" the custom submission message with a blank space
        // because if the field is empty it will fallback on the default value
        switch( $status ) {
            case 'not_sent' :
                if ( array_key_exists( 'failure_message', $submission_options ) && !empty( $submission_options['failure_message'] ) && 0 < strlen( preg_replace('/\s+/', '', $submission_options['failure_message'] ) ) ) {
                    $submission_message = $submission_options['failure_message'];
                }
            break;
            case 'sent' :
                if ( array_key_exists( 'success_message', $submission_options ) && !empty( $submission_options['success_message'] ) && 0 < strlen( preg_replace('/\s+/', '', $submission_options['success_message'] ) ) ) {
                    $submission_message = $submission_options['success_message'];
                }
            break;
            case 'aborted' :
                if ( array_key_exists( 'error_message', $submission_options ) && !empty( $submission_options['error_message'] ) && 0 < strlen( preg_replace('/\s+/', '', $submission_options['error_message'] ) ) ) {
                    $submission_message = $submission_options['error_message'];
                }
                if ( false !== $this->invalid_field ) {
                    $submission_message = sprintf( __( '%1$s : <strong>%2$s</strong>.', 'text-domain' ), $submission_message, $this->invalid_field );
                }
            break;
            case 'recaptcha_fail' :
                $global_recaptcha_opts = sek_get_global_option_value('recaptcha');
                $global_recaptcha_opts = is_array( $global_recaptcha_opts ) ? $global_recaptcha_opts : array();
                if ( true === sek_booleanize_checkbox_val($global_recaptcha_opts['show_failure_message']) ) {
                    $submission_message = !empty($global_recaptcha_opts['failure_message']) ? $global_recaptcha_opts['failure_message'] : '';
                }
            break;
        }

        if ( '_no_error_' !== $this->recaptcha_errors && current_user_can( 'customize' ) ) {
              $submission_message .= sprintf( '<br/>%s : <i>%s</i>', __('reCAPTCHA problem (only visible by a logged in administrator )', 'text_doma'), $this->recaptcha_errors );
        }
        return $submission_message;
    }




    // inspired from wpcf7
    private function get_from_email() {
        $admin_email = get_option( 'admin_email' );
        $sitename    = strtolower( sanitize_text_field($_SERVER['SERVER_NAME']) );

        if ( in_array( $sitename, array( 'localhost', '127.0.0.1' ) ) ) {
            return $admin_email;
        }

        if ( substr( $sitename, 0, 4 ) == 'www.' ) {
            $sitename = substr( $sitename, 4 );
        }

        if ( strpbrk( $admin_email, '@' ) == '@' . $sitename ) {
            return $admin_email;
        }

        return 'wordpress@' . $sitename;
    }
}//Sek_Mailer
endif;












// inspired by wpcf7
function sek_simple_form_mail_template() {
    $template = array(
        'subject' =>
            sprintf( __( '%1$s: new contact request', 'text_doma' ),
                get_bloginfo( 'name' )
            ),
        'sender' => sprintf( '[your-name] <%s>', simple_form_from_email() ),
        'body' =>
            /* translators: %s: [your-name] <[your-email]> */
            sprintf( __( 'From: %s', 'text_doma' ),
                '[your-name] <[your-email]>' ) . "\n"
            /* translators: %s: [your-subject] */
            . sprintf( __( 'Subject: %s', 'text_doma' ),
                '[your-subject]' ) . "\n\n"
            . __( 'Message Body:', 'text_doma' )
                . "\n" . '[your-message]' . "\n\n"
            . '-- ' . "\n"
            /* translators: 1: blog name, 2: blog URL */
            . sprintf(
                __( 'This e-mail was sent from a contact form on %1$s (%2$s)', 'text_doma' ),
                get_bloginfo( 'name' ),
                get_bloginfo( 'url' ) ),
        'recipient' => get_option( 'admin_email' ),
        'additional_headers' => 'Reply-To: [your-email]',
        'attachments' => '',
        'use_html' => 0,
        'exclude_blank' => 0,
    );

    return $template;
}//simple_form_mail_template


?>