<?php
// function register_simple_form_module() {
//     if ( ! isset( $GLOBALS['czr_base_fmk_namespace'] ) ) {
//         error_log( __FUNCTION__ . ' => global czr_base_fmk not set' );
//         return;
//     }

//     $czrnamespace = $GLOBALS['czr_base_fmk_namespace'];
// }

// fired @action 'after_setup_theme'
function sek_load_module_simple_form() {
    new \Nimble\Sek_Simple_Form();
}

if ( ! class_exists( '\Nimble\Sek_Simple_Form' ) ) :
class Sek_Simple_Form extends SEK_Front_Render_Css {

    private $form;
    private $fields;
    private $mailer;

    private $form_composition;

    function _setup_simple_forms() {
        //Hooks
        add_action( 'parse_request', array( $this, 'simple_form_parse_request' ), 20 );
        // Note : form input need to be prefixed to avoid a collision with reserved WordPress input
        // @see : https://stackoverflow.com/questions/15685020/wordpress-form-submission-and-the-404-error-page#16636051
        $this->form_composition = array(
            'nimble_simple_cf'              => array(
                'type'            => 'hidden',
                'value'           => 'nimble_simple_cf'
            ),
            'nimble_name' => array(
                'label'            => __( 'Name', 'text_domain_to_be_replaced' ),
                'required'         => 'required',
                'type'             => 'text',
                'wrapper_tag'      => 'p'
            ),
            'nimble_email' => array(
                'label'            => __( 'Email', 'text_domain_to_be_replaced' ),
                'required'         => 'required',
                'type'             => 'email',
                'wrapper_tag'      => 'p'
            ),
            'nimble_subject' => array(
                'label'            => __( 'Subject', 'text_domain_to_be_replaced' ),
                'type'             => 'url',
                'wrapper_tag'      => 'p'
            ),
            'nimble_message' => array(
                'label'            => __( 'Message', 'text_domain_to_be_replaced' ),
                'required'         => 'required',
                'additional_attrs' => array( 'rows' => "10", 'cols' => "50" ),
                'type'             => 'textarea',
                'wrapper_tag'      => 'p'
            ),
            'nimble_submit' => array(
                'type'             => 'submit',
                'value'            => __( 'Contact', 'text_domain_to_be_replaced' ),
                'wrapper_tag'      => 'p'
            )
        );
    }//_setup_simple_forms


    //@hook: parse_request
    function simple_form_parse_request() {
        if ( isset( $_POST['nimble_simple_cf'] ) ) {

            $form_composition = array();
            foreach ( $this->form_composition as $name => $field ) {
                $form_composition[ $name ]                = $field;
                if ( isset( $_POST[ $name ] ) ) {
                    $form_composition[ $name ][ 'value' ] = $_POST[ $name ];
                }
            }
            //generate fields
            $this->fields = $this->simple_form_generate_fields( $form_composition );
            //generate form
            $this->form   = $this->simple_form_generate_form( $this->fields );

            //mailer
            $this->mailer = new Sek_Mailer( $this-> form );

            $this->mailer->maybe_send();
        }
    }



    //Rendering
    //@return string
    function get_simple_form_html( $module_options ) {
        $html         = '';

        //set the fields to render
        $form_composition = $this->_set_form_composition( $this->form_composition, $module_options );
        //generate fields
        $fields       = isset( $this->fields ) ? $this->fields : $this->simple_form_generate_fields( $form_composition );
        //generate form
        $form         = isset( $this->form ) ? $this->form : $this->simple_form_generate_form( $fields );

        ob_start();
        ?>
        <div id="respond">
          <?php
            $echo_form = true;
            if ( ! is_null( $this->mailer ) ) {
                if ( 'sent' == $status_code = $this->mailer->get_status() ) {
                    $echo_form = false;
                }
                echo $this->mailer->get_message( $status_code );
            }

            if ( $echo_form ) {
                echo $form;
            }
          ?>
        </div>
        <?php
        return ob_get_clean();
    }

    //set the fields to render
    private function _set_form_composition( $form_composition, $module_options = array() ) {
        $user_form_composition = array();
        if ( ! is_array( $module_options ) ) {
              sek_error_log( __CLASS__ . '::' . __FUNCTION__ . ' => ERROR : invalid module options array');
              return $user_form_composition;
        }
        foreach ($form_composition as $field_id => $field_data ) {
            switch ( $field_id ) {
              case 'nimble_name':
                  if ( ! empty( $module_options['show_name_field'] ) && sek_is_checked( $module_options['show_name_field'] ) ) {
                      $user_form_composition[$field_id] = $field_data;
                  }
              break;
              case 'nimble_subject':
                  if ( ! empty( $module_options['show_subject_field'] ) && sek_is_checked( $module_options['show_subject_field'] ) ) {
                      $user_form_composition[$field_id] = $field_data;
                  }
              break;
              case 'nimble_message':
                  if ( ! empty( $module_options['show_message_field'] ) && sek_is_checked( $module_options['show_message_field'] ) ) {
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
    function simple_form_generate_form( $fields ) {
        $form   = new Sek_Form();
        $form->add_fields( $fields );

        return $form;
    }
}//Sek_Simple_Form
endif;











/*
*
* Form class definition
*
*/
if ( ! class_exists( '\Nimble\Sek_Form' ) ) :
class Sek_Form {
    private $fields;
    private $attributes;

    public function __construct( $args = array() ) {
        $this->fields        = array();
        $this->attributes    = wp_parse_args( $args, array(
            'action' => get_the_permalink(),
            'method' => 'post'
            //TODO: add html callback
        ) );
    }

    public function add_field( Sek_Field $field ) {
        $this->fields[ sanitize_key( $field->get_input()->get_data('name') ) ] = $field;
    }

    public function add_fields( array $fields ) {
        foreach ($fields as $field) {
            $this->add_field( $field );
        }
    }

    public function get_fields() {
        return $this->fields;
    }

    public function get_field( $field_name ) {
        return $this->fields[ sanitize_key( $field_name ) ] ? $this->fields[ sanitize_key( $field_name ) ] : null;
    }

    //make sure fields are well formed
    public function is_well_formed() {
        $is_well_formed = true;

        foreach ( $this->fields as $form_field ) {
            $input        = $form_field->get_input();

            $value        = $input->get_value();
            $filter       = $input->get_data( 'filter' );
            $can_be_empty = 'required' != $input->get_data( 'required' );

            if ( $can_be_empty && ! $value ) {
                continue;
            }
            if ( $filter && ! filter_var( $value, $filter ) ) {
                $is_well_formed = false;
                break;
            }
        }

        return $is_well_formed;
    }

    public function get_attributes_html() {
        return implode( ' ', array_map(
            function ($k, $v) {
                return sanitize_key( $k ) .'="'. esc_attr( $v ) .'"';
            },
            array_keys( $this->attributes ), $this->attributes
        ) );
    }

    public function __toString() {
        return $this->html();
    }

    public function html() {
        $fields = '';

        foreach ($this->fields as $name => $field) {
            $fields .= $field;
        }

        return sprintf('<form %1$s>%2$s</form>',
            $this->get_attributes_html(),
            $fields
        );
    }
}//Sek_Form
endif;











/*
* Field class definition
*
* label and/or wrapper + input field
*/
if ( ! class_exists( '\Nimble\Sek_Field' ) ) :
class Sek_Field {
    private $input;
    private $data;

    public function __construct( Sek_Input_Interface $input, $args = array() ) {
        $this->input = $input;

        $this->data  = wp_parse_args( $args, [
            'wrapper_tag'         => '',
            'wrapper_class'       => '',
            'label'               => '',
            //TODO: allow callbacks
            'before_label'        => '',
            'after_label'         => '',
            'before_input'        => '',
            'after_input'         => '',
        ]);
    }

    public function get_input() {
        return $this->input;
    }

    public function __toString() {
        return $this->html();
    }

    public function html() {
        $label = $this->data[ 'label' ];

        //label stuff
        if ( $label ) {
            if ( 'required' == $this->input->get_data( 'required' ) ) {

                $label .= ' ' . esc_html__( '(required)', 'text_domain_to_be_replaced' );
            }
            $label = sprintf( '%1$s<label for="%2$s">%3$s</label>%4$s',
                $this->data[ 'before_label' ],
                esc_attr( $this->input->get_data( 'id' ) ),
                esc_html($label),
                $this->data[ 'after_label' ]
            );
        }

        //the input
        $html = sprintf( '%s%s%s%s',
            $label,
            $this->data[ 'before_input' ],
            $this->input,
            $this->data[ 'after_input' ]
        );

        //any wrapper?
        if ( $this->data[ 'wrapper_tag' ] ) {
            $wrapper_tag   = tag_escape( $this->data[ 'wrapper_tag' ] );
            $wrapper_class = $this->data[ 'wrapper_class' ] ? ' class="'. implode( ' ', array_map('sanitize_html_class', $this->data[ 'wrapper_class' ] ) ) .'"' : '';

            $html = sprintf( '<%1$s%2$s>%3$s</%1$s>',
                $wrapper_tag,
                $wrapper_class,
                $html
            );
        }

        return $html;
    }
}
endif;





















/*
*
* Input objects definition
*
*/
interface Sek_Input_Interface {
    public function sanitize( $value );
    public function escape( $value );
    public function get_value();
    public function set_value( $value );
    public function get_data( $data_key );
    public function html();
}

abstract class Sek_Input_Abstract implements Sek_Input_Interface {
    private $data;
    protected $attributes = array( 'id', 'name', 'required' );

    public function __construct( $args ) {
        //no name no party
        //TODO: raise exception
        if ( ! isset( $args['name'] ) ) {
            error_log( __FUNCTION__ . ' => contact form input name not set' );
            return;
        }

        $defaults = array(
            'name'               => '',
            'id'                 => '',
            'id_suffix'          => null,
            'additional_attrs'   => array(),
            'sanitize_cb'        => array( $this, 'sanitize' ),
            'escape_cb'          => array( $this, 'escape' ),
            'required'           => false,
            'filter'             => '',
            'value'              => ''
        );

        $data = wp_parse_args( $args, $defaults );


        $data[ 'id_suffix' ]        = is_null( $data[ 'id_suffix' ] ) ? rand() : $data[ 'id_suffix' ];
        $data[ 'id' ]               = empty( $data[ 'id' ] ) ? $data[ 'name' ] : $data[ 'id' ];
        $data[ 'id' ]               = $data[ 'id' ] . $data[ 'id_suffix' ];
        $data[ 'additional_attrs' ] = is_array( $data[ 'additional_attrs' ] ) ? $data[ 'additional_attrs' ] : array();

        $this->data = $data;

        if ( $data[ 'value' ] ) {
            $this->set_value( $data[ 'value' ]  );
        }

    }

    public function sanitize( $value ) {
        return $value;
    }

    public function escape( $value ) {
        return esc_attr( $value );
    }


    public function get_value() {
        $data = (array)$this->data;
        return $this->data['escape_cb']( $data['value'] );
    }


    public function set_value( $value ) {
        $this->data['value'] = $this->data['sanitize_cb']( $value );
    }

    public function get_data( $data_key ){
        return isset( $this->data[ $data_key ] ) ? $this->data[ $data_key ] : null;
    }

    public function get_attributes_html() {
        $attributes = array_merge(
            array_intersect_key(
                array_filter( $this->data ),
                array_flip( $this->attributes )
            ),
            $this->data[ 'additional_attrs' ]
        );

        $attributes = array_map(
            function ($k, $v) {
                $v     =  'value' == $k ? $this->get_value() : esc_attr( $v );
                return sanitize_key( $k ) .'="'. $v .'"';
            },
            array_keys($attributes), $attributes
        );

        return implode( ' ', $attributes );
    }


    public function __toString() {
        return $this->html();
    }

}//end abstract class









if ( ! class_exists( '\Nimble\Sek_Input_Basic' ) ) :
class Sek_Input_Basic extends Sek_Input_Abstract {

    public function __construct( $args ) {
        $this->attributes   = array_merge( $this->attributes, array( 'value', 'type' ) );
        parent::__construct( $args );
    }

    public function html() {
        return sprintf( '<input %s/>',
            $this->get_attributes_html()
        );
    }
}
endif;

if ( ! class_exists( '\Nimble\Sek_Input_Hidden' ) ) :
class Sek_Input_Hidden extends Sek_Input_Basic {
    public function __construct( $args ) {
        $args[ 'type' ]     = 'hidden';
        parent::__construct( $args );
    }
}
endif;

if ( ! class_exists( '\Nimble\Sek_Input_Text' ) ) :
class Sek_Input_Text extends Sek_Input_Basic {
    public function __construct( $args ) {
        $args               = is_array( $args ) ? $args : array();
        $args[ 'type' ]     = 'text';
        $args[ 'filter' ]   = FILTER_SANITIZE_STRING;

        parent::__construct( $args );
    }

    public function sanitize( $value ) {
        return sanitize_text_field( $value );
    }

    public function escape( $value ) {
        return esc_html( $value );
    }
}
endif;


if ( ! class_exists( '\Nimble\Sek_Input_Email' ) ) :
class Sek_Input_Email extends Sek_Input_Basic {
    public function __construct($args) {
        $args             = is_array( $args ) ? $args : array();
        $args[ 'type' ]   = 'text';
        $args[ 'filter' ] = FILTER_SANITIZE_EMAIL;

        parent::__construct( $args );
    }

    public function sanitize( $value ) {
        if ( ! is_email( $value ) ) {
            return '';
        }
        return sanitize_email($value);
    }

    public function escape( $value ) {
        return esc_html( $value );
    }
}
endif;


if ( ! class_exists( '\Nimble\Sek_Input_URL' ) ) :
class Sek_Input_URL extends Sek_Input_Basic {
    public function __construct($args) {
        $args             = is_array( $args ) ? $args : array();
        $args[ 'type' ]   = 'url';
        $args[ 'filter' ] = FILTER_SANITIZE_URL;

        parent::__construct( $args );
    }


    public function sanitize($value) {
        return esc_url_raw( $value );
    }

    public function escape( $value ){
        return esc_url( $value );
    }
}
endif;


if ( ! class_exists( '\Nimble\Sek_Input_Submit' ) ) :
class Sek_Input_Submit extends Sek_Input_Basic {
    public function __construct($args) {
        $args             = is_array( $args ) ? $args : array();

        $args[ 'type' ]   = 'submit';
        $args             = wp_parse_args($args, [
            'value' => esc_html__( 'Contact', 'text_domain_to_be_replaced' ),
        ]);

        parent::__construct( $args );
    }

    public function escape( $value ) {
        return esc_html( $value );
    }
}
endif;



if ( ! class_exists( '\Nimble\Sek_Input_Textarea' ) ) :
class Sek_Input_Textarea extends Sek_Input_Abstract {

    public function __construct($args) {
        $args             = is_array( $args ) ? $args : array();
        $args[ 'type' ]   = 'textarea';

        parent::__construct( $args );
    }

    public function sanitize( $value ) {
        return wp_kses_post($value);
    }

    public function escape( $value ) {
        return $this->sanitize( $value );
    }


    public function html() {
        return sprintf( '<textarea %1$s/>%2$s</textarea>',
            $this->get_attributes_html(),
            $this->get_value()
        );
    }
}
endif;






















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

    public function __construct( Sek_Form $form ) {
        $this-> form = $form;

        $this->messages = array(
            //status          => message
            'not_sent'        => __( 'Message was not sent. Try Again.', 'text_domain_to_be_replaced'),
            'sent'            => __( 'Thanks! Your message has been sent.', 'text_domain_to_be_replaced'),
            'aborted'         => __( 'Please supply correct information.', 'text_domain_to_be_replaced'), //<-todo too much generic
        );

        $this->status = 'init';
    }



    public function maybe_send() {

        if ( ! $this->form->is_well_formed() ) {
            $this->status = 'aborted';
            return;
        }

        //<-allow html? -> TODO: turn into option
        $allow_html     = true;

        $sender_email   = $this->form->get_field('nimble_email')->get_input()->get_value();
        $sender_name    = sprintf( '%1$s', $this->form->get_field('nimble_name')->get_input()->get_value() );

        $recipient      = get_option( 'admin_email' ); //<- maybe this can be an option as well

        $subject        = sprintf( __( 'Someone sent a message from %1$s', 'text_domain_to_be_replaced' ), get_bloginfo( 'name' ) );

        // $sender_website = sprintf( __( 'Website: %1$s %2$s', 'text_domain_to_be_replaced' ),
        //     $this->form->get_field('website')->get_input()->get_value(),
        //     $allow_html ? '<br><br><br>': "\r\n\r\n\r\n"
        // );

        $before_message = '';//$sender_website;
        $after_message  = '';

        $body           = sprintf( '%1$s%2$s%3$s%4$s%5$s',
                            $before_message,
                            sprintf( __( 'Message:%1$s%2$s', 'text_domain_to_be_replaced' ),
                                 $allow_html ? '<br><br>': "\r\n\r\n",
                                 $this->form->get_field('nimble_message')->get_input()->get_value()
                            ),
                            $after_message,
                            $allow_html ? '<br><br>--<br>': "\r\n\r\n--\r\n",
                            sprintf( __( 'This e-mail was sent from a contact form on %1$s (<a href="%2$s" target="_blank">%2$s</a>)', 'text_domain_to_be_replaced' ),
                                get_bloginfo( 'name' ),
                                get_site_url( 'url' )
                            )
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


    public function get_message( $status ) {
        return isset( $this->messages[ $status ] ) ? $this->messages[ $status ] : '';
    }



    /*
    * inspired from wpcf7
    */
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











/*
* inspired by wpcf7
*/
function simple_form_mail_template() {
    $template = array(
        'subject' =>
            sprintf( __( '%1$s: new contact request', 'text_domain_to_be_replaced' ),
                get_bloginfo( 'name' )
            ),
        'sender' => sprintf( '[your-name] <%s>', simple_form_from_email() ),
        'body' =>
            /* translators: %s: [your-name] <[your-email]> */
            sprintf( __( 'From: %s', 'text_domain_to_be_replaced' ),
                '[your-name] <[your-email]>' ) . "\n"
            /* translators: %s: [your-subject] */
            . sprintf( __( 'Subject: %s', 'text_domain_to_be_replaced' ),
                '[your-subject]' ) . "\n\n"
            . __( 'Message Body:', 'text_domain_to_be_replaced' )
                . "\n" . '[your-message]' . "\n\n"
            . '-- ' . "\n"
            /* translators: 1: blog name, 2: blog URL */
            . sprintf(
                __( 'This e-mail was sent from a contact form on %1$s (%2$s)', 'text_domain_to_be_replaced' ),
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