<?php
/*
*&
*
*/
if ( !class_exists( '\Nimble\Sek_Form' ) ) :
class Sek_Form {
    private $fields;
    private $attributes;

    // Sek_Form is instantiated from Sek_Simple_Form::simple_form_generate_form
    //$form   = new Sek_Form( [
    //     'action' => is_array( $module_model ) && !empty( $module_model['id']) ? $module_model['id'] :'#',
    //     'method' => 'post'
    // ] );
    public function __construct( $args = array() ) {
        $this->fields        = array();
        $this->attributes    = wp_parse_args( $args, array(
            'action' => '#',// the action attribute doesn't have to be specified when form data is sent to the same page
            // see https://developer.mozilla.org/en-US/docs/Learn/Forms/Sending_and_retrieving_form_data,
            // for the moment we use the parent module id, example : #__nimble__cd4d5b307a3b
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
        if ( is_array( $this->fields ) && array_key_exists(sanitize_key( $field_name ), $this->fields) ) {
            return $this->fields[ sanitize_key( $field_name ) ];
        }
        return null;
    }

    //make sure fields are well formed
    public function has_invalid_field() {
        $has_invalid_field = false;

        foreach ( $this->fields as $form_field ) {
            if ( false !== $has_invalid_field )
              continue;
            $input        = $form_field->get_input();
            $value        = $input->get_value();
            $filter       = $input->get_data( 'filter' );
            $can_be_empty = true !== $input->get_data( 'required' );

            if ( $can_be_empty && !$value ) {
                continue;
            }
            if ( $filter && !filter_var( $value, $filter ) ) {
                $has_invalid_field = $input->get_data('label');
                break;
            }
        }

        return $has_invalid_field;
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

        // The form output is late escaped on rendering in tmpl\modules\simple_form_module_tmpl.php
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
if ( !class_exists( '\Nimble\Sek_Field' ) ) :
class Sek_Field {
    private $input;
    private $data;

    public function __construct( Sek_Input_Interface $input, $args = array() ) {
        $this->input = $input;

        $this->data  = wp_parse_args( $args, [
            'wrapper_tag'         => '',
            'wrapper_class'       => array( 'sek-form-field' ),
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
            if ( true == $this->input->get_data( 'required' ) ) {
                $label .= ' *';
                //$label .= ' ' . esc_html__( '(required)', 'text_doma' );
            }
            $label = sprintf( '%1$s<label for="%2$s">%3$s</label>%4$s',
                $this->data[ 'before_label' ],
                esc_attr( $this->input->get_data( 'id' ) ),
                wp_specialchars_decode($label),
                $this->data[ 'after_label' ]
            );
        }

        //the input
        if ( !empty($this->data['type']) && 'checkbox' === $this->data['type'] ) {
            $html = sprintf( '%s%s%s%s',
                $this->data[ 'before_input' ],
                $this->input,
                $this->data[ 'after_input' ],
                $label
            );
        } else {
            $html = sprintf( '%s%s%s%s',
                $label,
                $this->data[ 'before_input' ],
                $this->input,
                $this->data[ 'after_input' ]
            );
        }

        //any wrapper?
        if ( $this->data[ 'wrapper_tag' ] ) {
            $wrapper_tag   = tag_escape( $this->data[ 'wrapper_tag' ] );
            $wrapper_class = $this->data[ 'wrapper_class' ] ? ' class="'. implode( ' ', array_map('sanitize_html_class', $this->data[ 'wrapper_class' ] ) ) .'"' : '';

            $html = sprintf( '<%1$s%2$s>%3$s</%1$s>',
                $wrapper_tag,
                esc_attr($wrapper_class),
                $html
            );
        }

        return $html;
    }
}
endif;

?>