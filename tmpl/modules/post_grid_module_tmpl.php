<?php
namespace Nimble;
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
$model = Nimble_Manager() -> model;
$value = array_key_exists( 'value', $model ) ? $model['value'] : array();
$main_settings = $value['grid_main'];
$metas_settings = $value['grid_metas'];


if ( ! function_exists( 'Nimble\sek_render_post') ) {
  function sek_render_post( $main_settings, $metas_settings ) {
    // thumb, title, excerpt visibility
    foreach ( ['thumb', 'title', 'excerpt'] as $element ) {
        ${'show_' . $element} = sek_booleanize_checkbox_val( $main_settings["show_{$element}"] );
    }
    // meta visibility
    foreach ( ['cats', 'comments', 'author', 'date'] as $meta) {
        ${'show_' . $meta} = sek_booleanize_checkbox_val( $metas_settings["show_{$meta}"] );
    }
    ?>
      <article id="sek-pg-<?php the_ID(); ?>">
        <?php if ( $show_thumb ) : ?>
          <figure class="sek-pg-thumbnail">
            <a href="<?php the_permalink(); ?>">
              <?php the_post_thumbnail(); ?>
            </a>
          </figure>
        <?php endif; ?>
        <div class="sek-pg-content">
          <?php if ( $show_cats ) : ?>
            <p class="sek-pg-category"><?php the_category(' / '); ?></p>
          <?php endif; ?>
          <?php if ( $show_title ) : ?>
            <h2 class="sek-pg-title">
              <a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a>
            </h2><!--/.pg-title-->
          <?php endif; ?>
          <aside class="sek-pg-metas">
            <?php if ( $show_author ) : ?>
              <span><?php the_author_posts_link(); ?></span>
            <?php endif; ?>
            <?php if ( $show_date ) : ?>
              <span class="published updated"><?php echo get_the_date( get_option('date_format') ); ?></span>
            <?php endif; ?>
            <?php if ( $show_comments ) : ?>
              <span><?php comments_number( '0', '1', '%' ); ?> <?php _e('comments', 'text_doma'); ?></span>
            <?php endif; ?>
          </aside><!--/.pg-meta-->
          <?php if ( $show_excerpt ) : ?>
            <div class="sek-excerpt">
              <?php the_excerpt(); ?>
            </div>
          <?php endif; ?>
        </div><?php //.sek-pg-content ?>
      </article><!--/#sek-pg-->
    <?php
  }
}

// sek_error_log('CATEGORIES ?', $main_settings['categories'] );
$categories_in = '';
if ( is_array( $main_settings['categories'] ) ) {
    // https://codex.wordpress.org/Class_Reference/WP_Query#Category_Parameters
    $categories_in = implode(',', $main_settings['categories'] );
}
$order = 'DESC';
$orderby = 'title';

if ( !empty( $main_settings['order_by'] ) && is_string( $main_settings['order_by'] ) ) {
    $order_params = explode('_', $main_settings['order_by'] );
    // 'date_desc' will give orderby = date and order = DESC
    if ( is_array( $order_params ) && 2 === count($order_params) ) {
        $order = strtoupper( $order_params[1] );
        $orderby = $order_params[0];
    }
}

// Query featured entries
$post_collection = new \WP_Query(
  array(
    'no_found_rows'          => false,
    'update_post_meta_cache' => false,
    'update_post_term_cache' => false,
    'ignore_sticky_posts'    => 1,
    'posts_per_page'         => $main_settings['post_number'],
    'cat'                    => $categories_in,
    'order'                  => $order,
    'orderby'                => $orderby
  )
);

if ( $post_collection->have_posts() ) {
  $column_nb = (int)$main_settings['columns'];
  $column_nb = $column_nb > 4 ? 4 : $column_nb;
  $column_nb = $column_nb < 1 ? 1 : $column_nb;
  $layout_class = 'list' === $main_settings['layout'] ? 'sek-list-layout' : 'sek-grid-layout';
  $column_class = 'list' === $main_settings['layout'] ? '' : "sek-col-{$column_nb}";
  $shadow_class = true === sek_booleanize_checkbox_val( $main_settings['apply_shadow'] ) ? '.sek-shadow' : '';
  $breakpoint_class = true === sek_booleanize_checkbox_val( $main_settings['has_breakpoint'] ) ? '.sek-has-breakpoint' : '';
  $grid_items_classes = implode(' ', [ $layout_class, $column_class, $shadow_class, $breakpoint_class ] );
  ?>
  <div class="sek-post-grid-wrapper">
    <div class="sek-grid-items <?php echo $grid_items_classes; ?>">
      <?php
        while ( $post_collection->have_posts() ) {
            $post_collection->the_post();
            sek_render_post( $main_settings, $metas_settings );
        }//while
        // After looping through a separate query, this function restores the $post global to the current post in the main query.
        wp_reset_postdata();
        //  This will remove obscure bugs that occur when the previous WP_Query object is not destroyed properly before another is set up.
        // $GLOBALS['wp_query'] = $GLOBALS['wp_the_query'];
        wp_reset_query();
      ?>
    </div><?php //.sek-grid-item ?>
  </div><?php //.sek-post-grid-wrapper ?>
  <?php
}