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
    function sek_render_post() {
        ?>
            <article id="sek-pg-<?php the_ID(); ?>">
              <figure class="sek-pg-thumbnail">
                <a href="<?php the_permalink(); ?>">
                  <?php the_post_thumbnail(); ?>
                </a>
              </figure><!--/.pg-thumbnail-->
              <div class="sek-pg-content">
                <p class="sek-pg-category"><?php the_category(' / '); ?></p>
                <h2 class="sek-pg-title">
                  <a href="<?php the_permalink(); ?>" rel="bookmark" title="<?php the_title_attribute( array( 'before' => __( 'Permalink to ', 'text_doma' ) ) ); ?>"><?php the_title(); ?></a>
                </h2><!--/.pg-title-->

                <aside class="sek-pg-metas">
                  <span><?php the_author_posts_link(); ?></span><span class="published updated"><?php echo get_the_date( get_option('date_format') ); ?></span><span><?php comments_number( '0', '1', '%' ); ?> <?php _e('comments', 'text_doma'); ?></span>
                </aside><!--/.pg-meta-->

                <div class="sek-excerpt">
                  <?php the_excerpt(); ?>
                </div><!--/.sek-pg-content-->
              </div><!--/.pg-inner-->
            </article><!--/#sek-pg-->
        <?php
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
      //'cat'                    => $main_settings['categories']
  )
);
if ( $post_collection->have_posts() ) {

$layout = 'list';//to be replace by user choice
$layout_class = 'list' === $layout ? 'sek-list-layout' : 'sek-column-layout';
  ?>
  <div class="sek-post-grid-wrapper <?php echo $layout_class; ?>">
    <?php
      while ( $post_collection->have_posts() ) {
          $post_collection->the_post();
          sek_render_post();
      }//while
    ?>
  </div>
  <?php
}