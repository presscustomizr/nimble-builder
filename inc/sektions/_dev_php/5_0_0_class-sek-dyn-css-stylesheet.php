<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class Sek_Stylesheet {

    private $rules = array();


    public function sek_add_rule( $selector, $style_rules, array $mq = null ) {

        if ( ! is_string( $selector ) )
            return;

        if ( ! is_string( $style_rules ) )
            return;

        //TODO: allowed media query?
        $mq_hash = 'all';

        if ( $mq ) {
            $mq_hash = $this->sek_mq_to_hash( $mq );
        }

        if ( !isset( $this->rules[ $mq_hash ] ) ) {
            $this->sek_add_mq_hash( $mq_hash );
        }

        if ( !isset( $this->rules[ $mq_hash ][ $selector ] ) ) {
            $this->rules[ $mq_hash ][ $selector ] = array();
        }

        $this->rules[ $mq_hash ][ $selector ][] = $style_rules;
    }


    //totally Elementor inpired
    //add and sort media queries
    private function sek_add_mq_hash( $mq_hash ) {
        $this->rules[ $mq_hash ] = array();

        //TODO: test and probably improve ordering: need to think about convoluted use cases
        uksort(
            $this->rules, function( $a, $b ) {
                if ( 'all' === $a ) {
                    return -1;
                }

                if ( 'all' === $b ) {
                    return 1;
                }

                $a_query = $this->sek_hash_to_mq( $a );

                $b_query = $this->sek_hash_to_mq( $b );

                if ( isset( $a_query['min'] ) xor isset( $b_query['min'] ) ) {
                    return 1;
                }

                if ( isset( $a_query['min'] ) ) {
                    return $a_query['min'] - $b_query['min'];
                }

                return $b_query['max'] - $a_query['max'];
            }
        );
    }


    //totally Elementor inpired
    private function sek_mq_to_hash( array $mq ) {
        $hash = [];

        foreach ( $mq as $min_max => $value ) {
            $hash[] = $min_max . '_' . $value;
        }

        return implode( '-', $hash );
    }


    //totally Elementor inpired
    private function sek_hash_to_mq( $mq_hash ) {
        $mq = [];

        $mq_hash = array_filter( explode( '-', $mq_hash ) );

        foreach ( $mq_hash as $single_mq ) {
            $single_mq_parts = explode( '_', $single_mq );

            $mq[ $single_mq_parts[0] ] = $single_mq_parts[1];

        }

        return $mq;
    }


    private function sek_maybe_wrap_in_media_query( $css,  $mq_hash = 'all' ) {
        if ( 'all' === $mq_hash ) {
            return $css;
        }

        $mq           = $this->sek_hash_to_mq( $mq_hash );

        return '@media ' . implode( ' and ', array_map(
                function( $min_max, $value ) {
                    return "({$min_max}-width:{$value}px)";
                },
                array_keys( $mq ),
                array_values( $mq )
            )
        ) . '{' . $css . '}';
    }



    private function sek_parse_rules( $selector, $style_rules = array() ) {
        $style_rules = is_array( $style_rules ) ? implode( ';', $style_rules ) : $style_rules;
        return $selector . '{' . $style_rules . '}';
    }




    //stringify the stylesheet object
    public function __toString() {
        $css = '';
        foreach ( $this->rules as $mq_hash => $selectors ) {
            $_css = '';
            foreach ( $selectors as $selector => $style_rules ) {
                $_css .=  $this->sek_parse_rules( $selector, $style_rules );
            }
            $_css = $this->sek_maybe_wrap_in_media_query( $_css, $mq_hash );
            $css .= $_css;
        }

        return $css;
    }


}//end class

?>