<?php
namespace Nimble;
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
$model = Nimble_Manager() -> model;
$value = array_key_exists( 'value', $model ) ? $model['value'] : array();
$main_settings = $value['grid_main'];
$metas_settings = $value['grid_metas'];
$thumb_settings = $value['grid_thumb'];

if ( ! function_exists( 'Nimble\sek_render_post') ) {
  function sek_render_post( $main_settings, $metas_settings, $thumb_settings ) {
    // thumb, title, excerpt visibility
    foreach ( ['thumb'] as $element ) {
        ${'show_' . $element} = sek_booleanize_checkbox_val( $thumb_settings["show_{$element}"] );
    }
    foreach ( ['title', 'excerpt'] as $element ) {
        ${'show_' . $element} = sek_booleanize_checkbox_val( $main_settings["show_{$element}"] );
    }
    // meta visibility
    foreach ( ['cats', 'comments', 'author', 'date'] as $meta) {
        ${'show_' . $meta} = sek_booleanize_checkbox_val( $metas_settings["show_{$meta}"] );
    }
    $has_post_thumbnail = has_post_thumbnail();
    $use_post_thumb_placeholder = true === sek_booleanize_checkbox_val( $thumb_settings['use_post_thumb_placeholder'] );
    $has_post_thumb_class = ( $show_thumb && ( $has_post_thumbnail || $use_post_thumb_placeholder ) ) ? 'sek-has-thumb' : '';
    ?>
      <article id="sek-pg-<?php the_ID(); ?>" class="<?php echo $has_post_thumb_class; ?>">
        <?php if ( $show_thumb && ( $has_post_thumbnail || $use_post_thumb_placeholder ) ) : ?>
          <figure class="sek-pg-thumbnail">
            <?php // when title is not displayed, print it as an attribute of the image ?>
            <?php if ( $show_title ) : ?>
              <a href="<?php the_permalink(); ?>">
            <?php else : ?>
              <a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
            <?php endif; ?>
              <?php
                  if( $has_post_thumbnail ) {
                      $img_html = wp_get_attachment_image( get_post_thumbnail_id(), empty( $thumb_settings['img_size'] ) ? 'medium' : $thumb_settings['img_size']);
                      echo apply_filters( 'nimble_parse_for_smart_load', $img_html );
                  } else if ( $use_post_thumb_placeholder ) {
                      printf( '<img alt="default img" data-sek-smartload="false" src="%1$s"/>', NIMBLE_BASE_URL . '/assets/img/default-img.png'  );
                  }
              ?>
            </a>
          </figure>
        <?php endif; ?>
        <?php if ( $show_cats || $show_title || $show_author || $show_date || $show_comments || $show_excerpt ) : ?>
          <div class="sek-pg-content">
            <?php if ( $show_cats ) : ?>
              <div class="sek-pg-category"><?php the_category(' / '); ?></div>
            <?php endif; ?>
            <?php if ( $show_title ) : ?>
              <h2 class="sek-pg-title">
                <a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a>
              </h2><!--/.pg-title-->
            <?php endif; ?>
            <?php if ( $show_author || $show_date || $show_comments ) : ?>
              <aside class="sek-pg-metas">
                <?php if ( $show_author ) : ?>
                  <span><?php the_author_posts_link(); ?></span>
                <?php endif; ?>
                <?php if ( $show_date ) : ?>
                  <span class="published updated"><?php echo get_the_date( get_option('date_format') ); ?></span>
                <?php endif; ?>
                <?php if ( $show_comments ) : ?>
                  <span><?php comments_number( __('0 comments', 'text_doma'), __('1 comment', 'text_doma'), __('% comments', 'text_doma') ); ?></span>
                <?php endif; ?>
              </aside><!--/.pg-meta-->
            <?php endif; ?>
            <?php if ( $show_excerpt ) : ?>
              <div class="sek-excerpt">
                <?php the_excerpt(); ?>
              </div>
            <?php endif; ?>
          </div><?php //.sek-pg-content ?>
        <?php endif; ?>
      </article><!--/#sek-pg-->
    <?php
  }
}

// filters @hook 'excerpt_length'
if ( ! function_exists( 'Nimble\sek_pg_set_excerpt_length') ) {
  function sek_pg_set_excerpt_length( $original_length ) {
    $model = Nimble_Manager() -> model;
    $value = array_key_exists( 'value', $model ) ? $model['value'] : array();
    $main_settings = $value['grid_main'];
    $_custom = (int)$main_settings['excerpt_length'];
    $_custom = $_custom < 1 ? 1 : $_custom;

    return !is_numeric($_custom) ? $original_length : $_custom;
  }
}

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
  $columns_by_device = $main_settings['columns'];
  $columns_by_device = is_array( $columns_by_device ) ? $columns_by_device : array();
  $columns_by_device = wp_parse_args( $columns_by_device, array(
      'desktop' => 2,
      'tablet' => '',
      'mobile' => ''
  ));
  $normalized_columns_by_device = array();
  // normalizes
  foreach ( $columns_by_device as $device => $column_nb ) {
      $column_nb = (int)$column_nb;
      if ( !empty( $column_nb ) ) {
        $column_nb = $column_nb > 4 ? 4 : $column_nb;
        $column_nb = $column_nb < 1 ? 1 : $column_nb;
      }
      $normalized_columns_by_device[$device] = $column_nb;
  }


  $layout_class = 'list' === $main_settings['layout'] ? 'sek-list-layout' : 'sek-grid-layout';

  $shadow_class = true === sek_booleanize_checkbox_val( $main_settings['apply_shadow'] ) ? 'sek-shadow' : '';

  $has_thumb_custom_height = true === sek_booleanize_checkbox_val( $thumb_settings['img_has_custom_height'] ) ? 'sek-thumb-custom-height' : '';

  $tablet_breakpoint_class = true === sek_booleanize_checkbox_val( $main_settings['has_tablet_breakpoint'] ) ? 'sek-has-tablet-breakpoint' : '';
  $mobile_breakpoint_class = true === sek_booleanize_checkbox_val( $main_settings['has_mobile_breakpoint'] ) ? 'sek-has-mobile-breakpoint' : '';

  $grid_wrapper_classes = implode(' ', [ $tablet_breakpoint_class, $mobile_breakpoint_class] );

  $grid_items_classes = [ $layout_class, $shadow_class, $has_thumb_custom_height ];

  if ( 'grid' === $main_settings['layout'] ) {
    foreach ( $normalized_columns_by_device as $device => $column_nb ) {
      if ( empty( $column_nb ) )
        continue;
      $grid_items_classes[] = "sek-{$device}-col-{$column_nb}";
      if ( 'desktop' === $device ) {
        $grid_items_classes[] = "sek-all-col-{$column_nb}";
      }
    }
  }

  $grid_items_classes = implode(' ', $grid_items_classes );

  // FILTER EXCERPT LENGTH
  add_filter( 'excerpt_length', '\Nimble\sek_pg_set_excerpt_length', 999 );
  ?>
  <div class="sek-post-grid-wrapper <?php echo $grid_wrapper_classes; ?>">
    <div class="sek-grid-items <?php echo $grid_items_classes; ?>">
      <?php
        while ( $post_collection->have_posts() ) {
            $post_collection->the_post();
            sek_render_post( $main_settings, $metas_settings, $thumb_settings );
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

  // REMOVE FILTER EXCERPT LENGTH
  remove_filter( 'excerpt_length', '\Nimble\sek_pg_set_excerpt_length', 999 );
}