<?php
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
    [id] => __sek__f8fa3ce4b67bcae6629f7b3d
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
                    [img-size] => large
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
                            [id] => 2479
                            [type_label] => Page
                            [title] => test brix
                            [object_type] => page
                            [url] => http://customizr-tests.wordpress.test/2479-2/
                        )

                    [img-type] => featured
                    [img-id] =>
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

$model = SEK_Front() -> model;
$module_type = $model['module_type'];
$value = array_key_exists( 'value', $model ) ? $model['value'] : null;



/*
* $value should be an array
* and at least one [page-id][id] must be numeric or _custom_, and in this case [padge-id][url] must be set?
*/

function sek_fp_temporary_placeholder() {
    echo '<h2>Feature Page temporary placeholder</h2>';
    echo SEK_Front() -> sek_get_input_placeholder_content( 'upload' );
}

function sek_fp_is_fp_set( array $fp ) {
    return ( ! empty( $fp[ 'page-id' ]['id'] ) &&
        ( sek_fp_is_wp_page( $fp ) || sek_fp_is_custom( $fp ) ) );
}

function sek_fp_is_custom( array $fp ) {
    return ( '_custom_' == $fp[ 'page-id' ]['id'] && esc_url( $fp[ 'page-id' ]['url'] ) );
}

function sek_fp_is_wp_page( array $fp ) {
    return is_numeric( $fp[ 'page-id' ]['id'] );
}

// print the module content if not empty
if ( is_null( $value ) || ! is_array( $value ) ) :
    sek_fp_temporary_placeholder();
else :
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
        <div class="sek-row marketing js-center-images-disabled">

    <?php foreach ( $value as $fp ) : ?>
            <div class="sek-col-base sek-col-33">
                <div class="sek-fp-widget sek-link-mask-p round">
            <?php
                if ( ! sek_fp_is_fp_set( $fp ) ) :
                    sek_fp_temporary_placeholder();
                else :
                    //TEST
                    $featured_page_id = $fp[ 'page-id' ]['id'];
                    $fp_image         = get_the_post_thumbnail( $featured_page_id, $fp['img-size'] );
                    if ( $fp_image ) : /* FP IMAGE */?>
                    <div class="sek-fp-thumb-wrapper sek__r-wFP">
                        <a class="sek-link-mask" href="<?php esc_url( $fp_link ) ?>" title="<?php echo esc_attr( strip_tags( $fp_title ) ) ?>"></a>
                        <?php echo $fp_image ?>
                    </div>
                <?php
                    endif; /* END FP IMAGE*/
                    /* FP TITLE */
                ?>
                    <h4 class="sek-fp-title"><?php echo esc_attr( strip_tags( $fp_title ) ) ?></h4>
                <?php
                    if ( $fp_text ) :
                ?>
                      <p class="sek-fp-text"><?php echo $fp_text ?></p>
                <?php endif;/* END FP TEXT*/
                    /* FP BUTTON TEXT */
                    if ( $fp_button ) :
                ?>
                    <span class="sek-fp-button-holder"><a class="sek-btn sek-fp-btn-link" href="esc_url( $fp_link )" title="What we do" data-color="skin"><?php echo $fp_button_text ?></a></span>
                <?php
                    endif;/* END FP BUTTON TEXT*/
                endif; /* end sek_fp_is_fp_set */
            ?>
                </div>
            </div><!-- end .sek-col-base -->
    <?php endforeach ?>
    </div><!--end .sek-row -->
<?php endif;
