<?php
/*
*
* Mailer class definition
*
*/
if ( ! class_exists( '\Nimble\Sek_Mailer' ) ) :
class Sek_Mailer {
    private $form;
    private $status;
    private $messages;
    private $invalid_field = false;
    private $recaptcha_data;//will store array( 'endpoint' => $endpoint, 'request' => $request, 'response' => '' );

    public function __construct( Sek_Form $form ) {
        $this-> form = $form;

        $this->messages = array(
            //status          => message
            'not_sent'        => __( 'Message was not sent. Try Again.', 'text_doma'),
            'sent'            => __( 'Thanks! Your message has been sent.', 'text_doma'),
            'aborted'         => __( 'Please supply correct information.', 'text_doma'), //<-todo too much generic
            'recaptcha_fail'  => __( 'Google ReCaptcha validation failed', 'text_doma')
        );
        $this->status = 'init';

        // Validate reCAPTCHA if submitted
        // When sek_is_recaptcha_globally_enabled(), the hidden input 'nimble_recaptcha_resp' is rendered with a value set to a token remotely fetched with a js script
        // @see print_recaptcha_inline_js
        // on submission, we get the posted token value, and validate it with a remote http request to the google api
        if ( isset( $_POST['nimble_recaptcha_resp'] ) ) {
            if ( ! $this->validate_recaptcha( $_POST['nimble_recaptcha_resp'] ) ) {
                $this->status = 'recaptcha_fail';
                if ( sek_is_dev_mode() ) {
                    sek_error_log('reCAPTCHA failure', $this->recaptcha_data );
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

        // cache the recaptcha_data
        $this->recaptcha_data = array( 'endpoint' => $endpoint, 'request' => $request, 'response' => '' );
        $this->recaptcha_data['response'] = $response = wp_remote_post( esc_url_raw( $endpoint ), $request );
        if ( 200 != wp_remote_retrieve_response_code( $response ) ) {
            return $is_valid;
        }
        $response_body = wp_remote_retrieve_body( $response );
        $response_body = json_decode( $response_body, true );

        // see https://developers.google.com/recaptcha/docs/v3#score
        $score = isset( $response_body['score'] ) ? $response_body['score'] : 0;
        $threshold = apply_filters( 'nimble_recaptcha_human_treshold', 0.5 );
        $is_valid = $is_human = $threshold < $score;
        return $is_valid;
    }

    public function maybe_send( $form_composition, $module_model ) {
        // the captcha validation has been made on Sek_Mailer instantiation
        if ( 'recaptcha_fail' === $this->status ) {
            return;
        }

        //sek_error_log('$form_composition', $form_composition );
        //sek_error_log('$module_model', $module_model );
        $invalid_field = $this->form->has_invalid_field();
        if ( false !== $invalid_field ) {
            $this->status = 'aborted';
            $this->invalid_field = $invalid_field;
            return;
        }

        $module_user_values = array_key_exists( 'value', $module_model ) ? $module_model['value'] : array();
        //sek_error_log( '$module_model', $module_model );
        $submission_options = empty( $module_user_values['form_submission'] ) ? array() : $module_user_values['form_submission'];

        //<-allow html? -> TODO: turn into option
        $allow_html     = true;

        $sender_email   = $this->form->get_field('nimble_email')->get_input()->get_value();
        $sender_name    = sprintf( '%1$s', $this->form->get_field('nimble_name')->get_input()->get_value() );

        if ( array_key_exists( 'recipients', $submission_options ) ) {
            $recipient      = $submission_options['recipients'];
        } else {
            $recipient      = get_option( 'admin_email' );
        }

        if ( array_key_exists( 'nimble_subject' , $form_composition ) ) {
            $subject = $this->form->get_field('nimble_subject')->get_input()->get_value();
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
            $email_footer = $submission_options['email_footer'];
        } else {
            $email_footer = sprintf( __( 'This e-mail was sent from a contact form on %1$s (<a href="%2$s" target="_blank">%2$s</a>)', 'text_doma' ),
                get_bloginfo( 'name' ),
                get_site_url( 'url' )
            );
        }

        $body           = sprintf( '%1$s%2$s%3$s%4$s%5$s',
                            $before_message,
                            sprintf( '<br><br>%1$s: <br>%2$s',
                                __('Message body', 'text_doma'),
                                //$allow_html ? '<br><br>': "\r\n\r\n",
                                $this->form->get_field('nimble_message')->get_input()->get_value()
                            ),
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
        switch( $status ) {
            case 'not_sent' :
                if ( array_key_exists( 'failure_message', $submission_options ) && !empty( $submission_options['failure_message'] ) ) {
                    $submission_message = $submission_options['failure_message'];
                }
            break;
            case 'sent' :
                if ( array_key_exists( 'success_message', $submission_options ) && !empty( $submission_options['success_message'] ) ) {
                    $submission_message = $submission_options['success_message'];
                }
            break;
            case 'aborted' :
                if ( array_key_exists( 'error_message', $submission_options ) && !empty( $submission_options['error_message'] ) ) {
                    $submission_message = $submission_options['error_message'];
                }
                if ( false !== $this->invalid_field ) {
                    $submission_message = sprintf( __( '%1$s The following field is not well formed : <strong>%2$s</strong>.', 'text-domain' ), $submission_message, $this->invalid_field );
                }
            break;
        }
        return $submission_message;
    }




    // inspired from wpcf7
    private function get_from_email() {
        $admin_email = get_option( 'admin_email' );
        $sitename    = strtolower( $_SERVER['SERVER_NAME'] );

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