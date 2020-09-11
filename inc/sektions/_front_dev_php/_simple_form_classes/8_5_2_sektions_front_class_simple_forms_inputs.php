<?php
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
        if ( !isset( $args['name'] ) ) {
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
        $value = $this->data['escape_cb']( $data['value'] );
        if ( skp_is_customizing() ) {
            $field_name = $this->get_data('name');
            switch( $field_name ) {
                case 'nimble_name' :
                    $value = '';
                break;
                case 'nimble_email' :
                    $value = '';
                break;
                case 'nimble_subject' :
                    $value = '';
                break;
                // case 'nimble_message' :
                //     $value = __('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed non risus.', 'text-domain');
                // break;
            }
        }
        return $value;
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
        if ( skp_is_customizing() ) {
            $attributes['value'] = array_key_exists('value', $attributes ) ? $attributes['value'] : '';
        }
        $attributes = array_map(
            function ($k, $v) {
                switch ( $k ) {
                  case 'value':
                      $v = $this->get_value();
                  break;
                  default:
                      $v = esc_attr( $v );
                  break;
                }
                // 'required' attribute doesn't need a value : <input name="nimble_email" id="nimble_email1163989492" type="text" required/>
                return 'required' === $k ? 'required' : sanitize_key( $k ) .'="'. $v .'"';
            },
            array_keys($attributes), $attributes
        );

        return implode( ' ', $attributes );
    }


    public function __toString() {
        return $this->html();
    }

}//end abstract class









if ( !class_exists( '\Nimble\Sek_Input_Basic' ) ) :
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

if ( !class_exists( '\Nimble\Sek_Input_Hidden' ) ) :
class Sek_Input_Hidden extends Sek_Input_Basic {
    public function __construct( $args ) {
        $args[ 'type' ]     = 'hidden';
        parent::__construct( $args );
    }
}
endif;

if ( !class_exists( '\Nimble\Sek_Input_Checkbox' ) ) :
class Sek_Input_Checkbox extends Sek_Input_Basic {
    public function __construct( $args ) {
        $args[ 'type' ]     = 'checkbox';
        parent::__construct( $args );
    }
}
endif;


if ( !class_exists( '\Nimble\Sek_Input_Text' ) ) :
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


if ( !class_exists( '\Nimble\Sek_Input_Email' ) ) :
class Sek_Input_Email extends Sek_Input_Basic {
    public function __construct($args) {
        $args             = is_array( $args ) ? $args : array();
        $args[ 'type' ]   = 'text';
        $args[ 'filter' ] = FILTER_SANITIZE_EMAIL;

        parent::__construct( $args );
    }

    public function sanitize( $value ) {
        if ( !is_email( $value ) ) {
            return '';
        }
        return sanitize_email($value);
    }

    public function escape( $value ) {
        return esc_html( $value );
    }
}
endif;


if ( !class_exists( '\Nimble\Sek_Input_URL' ) ) :
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


if ( !class_exists( '\Nimble\Sek_Input_Submit' ) ) :
class Sek_Input_Submit extends Sek_Input_Basic {
    public function __construct($args) {
        $args             = is_array( $args ) ? $args : array();
        $args[ 'type' ]   = 'submit';
        $args             = wp_parse_args($args, [
            'value' => esc_html__( 'Contact', 'text_doma' ),
        ]);

        parent::__construct( $args );
    }

    public function escape( $value ) {
        return esc_html( $value );
    }
}
endif;



if ( !class_exists( '\Nimble\Sek_Input_Textarea' ) ) :
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
        return sprintf( '<textarea %1$s>%2$s</textarea>',
            $this->get_attributes_html(),
            $this->get_value()
        );
    }
}
endif;
?>