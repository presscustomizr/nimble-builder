<?php
namespace Nimble;
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
// this.defaultItemModel = {
//     img : '',
//     'img-size' : 'large',
//     'alignment' : '',
//     'link-to' : '',
//     'link-pick-url' : '',
//     'link-custom-url' : '',
//     'link-target' : '',
//     'lightbox' : true
// };
/*
The model looks like this
 Array
(
    [id] => __nimble__f8fa3ce4b67bcae6629f7b3d
    [level] => module
    [module_type] => czr_featured_pages_module
    [value] => Array
        (
            [0] => Array
                (
                    [id] => czr_featured_pages_module_0
                    [title] =>
                    [page-id] => Array
                        (
                            [id] => 8
                            [type_label] => Page
                            [title] => Chi siamo
                            [object_type] => page
                            [url] => http://customizr-tests.wordpress.test/chi-siamo/
                        )

                    [img-type] => featured
                    [img-id] =>
                    [img-size] => twentyseventeen-thumbnail-avatar
                    [content-type] => page-excerpt
                    [content-custom-text] => Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed non risus. Suspendisse lectus tortor, dignissim sit amet, adipiscing nec, ultricies sed, dolor.
                    [btn-display] => 1
                    [btn-custom-text] => Read More
                )

            [1] => Array
                (
                    [id] => czr_featured_pages_module_1
                    [title] =>
                    [page-id] => Array
                        (
                            [id] => _custom_
                            [type_label] =>
                            [title] => <span style="font-weight:bold">Set a custom url</span>
                            [object_type] =>
                            [url] =>
                        )

                    [img-type] => custom
                    [img-id] => 1783
                    [img-size] => large
                    [content-type] => page-excerpt
                    [content-custom-text] => Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed non risus. Suspendisse lectus tortor, dignissim sit amet, adipiscing nec, ultricies sed, dolor.
                    [btn-display] => 1
                    [btn-custom-text] => Read More
                )

        )

)

TO ASK: We're missing the title input
*/

$model          = Nimble_Manager()->model;
$module_type    = $model['module_type'];

$value          = array_key_exists( 'value', $model ) ? $model['value'] : array();
$value = is_array($value) ? $value : array();

//sek_error_log('FP TMPL', $model  );

/*
* Columns definition
*/
//should be an option in the future
$fp_per_row     = '3';

$fp_col_map     = array(
    //fp_per_row => col suffix
    '1' => '100',
    '2' => '50',
    '3' => '33',
    '4' => '25'
);

$fp_col_suffix  = '33';
$fp_col_suffix  = ( $fp_per_row > 7) ? 1 : $fp_col_suffix;
$fp_col_suffix  = isset( $fp_col_map[$fp_per_row] ) ? $fp_col_map[$fp_per_row] : $fp_col_suffix;



//HELPERS

/*
* $value should be an array
* and at least one [page-id][id] must be numeric or _custom_, and in this case [padge-id][url] must be set?
*/

if ( ! function_exists( 'Nimble\sek_fp_is_fp_set' ) ) :
    function sek_fp_is_fp_set( array $fp ) {
        return ( ! empty( $fp[ 'page-id' ]['id'] ) &&
            ( sek_fp_can_be_wp_page( $fp ) || sek_fp_is_custom( $fp ) ) );
    }
endif;


if ( ! function_exists( 'Nimble\sek_fp_is_custom' ) ) :
    function sek_fp_is_custom( array $fp ) {
        return ( '_custom_' == $fp[ 'page-id' ]['id'] && esc_url( $fp[ 'page-id' ]['url'] ) );
    }
endif;

if ( ! function_exists( 'Nimble\sek_fp_can_be_wp_page' ) ) :
    function sek_fp_can_be_wp_page( array $fp ) {
        return is_numeric( $fp[ 'page-id' ]['id'] );
    }
endif;


//START BLOCK RENDERING

/*
* We're always in a column which, by default, has right and left padding that we reset with the sek-row below
* Also: with the flexbox the "row" concept if defined by the width of the inner elements, this means that
* if we have 4 featured pages, since we display 3 fp per row, the fourth will always wrap on a new line,
* and sit at the very beginning whatever are the previous column heights:
* in a non flexbox layout, a first column taller then the second one caused the 4th (the one dropping on a new
* line) to be pushed below the second column.
*
* It's important to consider that this simplified markup would make the fp height equalization a
* little more complicated in js.
*/
?>
    <div class="sek-row marketing"><?php //js-center-images-disabled ?>

<?php foreach ( $value as $fp ) : ?>
        <div class="sek-col-base sek-col-<?php echo $fp_col_suffix ?>">
            <div class="sek-fp-widget sek-link-mask-p round">
        <?php
            // normalizes
            //$fp = wp_parse_args( $fp, $default_value_model );

            $is_custom_url   = false;
            $is_wp_post_type = false;

            if ( ! empty( $fp[ 'page-id' ]['id'] ) ) {
                $is_custom_url    = sek_fp_is_custom( $fp ) ;
                $is_wp_post_type  = !$is_custom_url && sek_fp_can_be_wp_page( $fp ) && $page = get_post($fp[ 'page-id' ][ 'id' ]);

                $featured_page_id = $is_wp_post_type ? $fp[ 'page-id' ][ 'id' ] : '';
            }
            if ( empty( $is_custom_url ) && empty( $is_wp_post_type ) ):
                echo '<h2>Feature Page temporary placeholder</h2>';
                echo Nimble_Manager()->sek_get_input_placeholder_content( 'upload' );
            else :
                //DEFINITION

                //IMAGE
                switch ( $fp[ 'img-type' ] ) {
                    case 'custom':
                                if ( ! empty( $fp[ 'img-id' ] ) ) {
                                   $fp_image         =  wp_get_attachment_image( $fp[ 'img-id' ], $fp['img-size'] );
                                   break;
                                }
                    case 'featured'  :
                                if ( $is_wp_post_type ) {
                                    $fp_image        = get_the_post_thumbnail( $fp[ 'page-id' ][ 'id' ], $fp['img-size'] );
                                    break;
                                }
                    default      : $fp_image = null;
                }



                $fp_title         = $fp[ 'page-id' ][ 'title' ];
                $fp_title         = esc_attr( strip_tags( $fp_title ) );

                $fp_link          = esc_url( $fp[ 'page-id' ][ 'url' ] );
                $fp_link          = !$fp_link ? 'javascript:void(0)' : $fp_link;

                //TEXT
                switch ( $fp[ 'content-type' ] ) {
                    case 'custom': $fp_text = $fp[ 'content-custom-text' ];
                                   break;
                    case 'page-excerpt'  :
                                if ( $is_wp_post_type ) {
                                    $fp_text  = !post_password_required($featured_page_id) ? strip_tags(apply_filters( 'the_content' , $page->post_excerpt )) : '' ;
                                    $fp_text  = ( empty($fp_text) && !post_password_required($featured_page_id) ) ? strip_tags(apply_filters( 'the_content' , $page->post_content )) : $fp_text;
                                    break;
                                }
                    default      : $fp_text = '';
                }

                if ( $fp_text ) {
                    //trim
                    //limit text to 200 chars?
                    $default_fp_text_length         = apply_filters( 'sek_fp_text_length', '250' );
                    $fp_text                        = sek_text_truncate( $fp_text, $default_fp_text_length, $more = '...', $strip_tags = false ); //tags already stripped
                }

                //BUTTON
                $fp_button_text   = esc_attr( strip_tags( $fp[ 'btn-custom-text' ] ) );
                $fp_button        = $fp[ 'btn-display' ] && $fp_button_text;

            ?>
            <?php /* SINGLE FP RENDERING*/
                if ( $fp_image ) : /* FP IMAGE */?>
                <div class="sek-fp-thumb-wrapper sek__r-wFP">
                    <a class="sek-link-mask" href="<?php echo $fp_link ?>" title="<?php echo $fp_title  ?>"></a>
                    <?php echo $fp_image ?>
                </div>
            <?php
                endif; /* END FP IMAGE*/
                /* FP TITLE */
            ?>
                <h4 class="sek-fp-title"><?php echo $fp_title ?></h4>
            <?php
                if ( $fp_text ) :
            ?>
                  <p class="sek-fp-text"><?php echo $fp_text ?></p>
            <?php endif;/* END FP TEXT*/
                /* FP BUTTON TEXT */
                if ( $fp_button ) :
            ?>
                <span class="sek-fp-button-holder"><a class="sek-btn sek-fp-btn-link" href="<?php echo $fp_link ?>" title="<?php echo $fp_title ?>"><?php echo $fp_button_text ?></a></span>
            <?php
                endif;/* END FP BUTTON TEXT*/
            endif; /* end sek_fp_is_fp_set */
        ?>
            </div>
        </div><!-- end .sek-col-base -->
<?php endforeach ?>
</div><!--end .sek-row -->