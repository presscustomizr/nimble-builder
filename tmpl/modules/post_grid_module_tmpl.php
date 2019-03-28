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
            <article id="post-<?php the_ID(); ?>" <?php post_class('group'); ?>>
              <div class="post-inner post-hover">
                <div class="post-thumbnail">
                  <a href="<?php the_permalink(); ?>">
                    <?php the_post_thumbnail(); ?>
                  </a>
                  <a class="post-comments" href="<?php comments_link(); ?>"><i class="far fa-comments"></i><?php comments_number( '0', '1', '%' ); ?></a>
                </div><!--/.post-thumbnail-->

                <div class="post-meta group">
                  <p class="post-category"><?php the_category(' / '); ?></p>
                  <?php sek_render_author_date(); ?>
                </div><!--/.post-meta-->

                <h2 class="post-title entry-title">
                  <a href="<?php the_permalink(); ?>" rel="bookmark" title="<?php the_title_attribute( array( 'before' => __( 'Permalink to ', 'text_doma' ) ) ); ?>"><?php the_title(); ?></a>
                </h2><!--/.post-title-->

                <div class="entry excerpt entry-summary">
                  <?php the_excerpt(); ?>
                </div><!--/.entry-->

              </div><!--/.post-inner-->
            </article><!--/.post-->
        <?php
    }
}


if ( ! function_exists( 'Nimble\sek_render_author_date') ) {
    function sek_render_author_date() {
      ?>
      <p class="post-date">
        <time class="published updated" datetime="<?php the_time('Y-m-d H:i:s'); ?>"><?php the_time( get_option('date_format') ); ?></time>
      </p>

      <p class="post-byline" style="display:none">&nbsp;<?php _e('by','text_doma'); ?>
        <span class="vcard author">
          <span class="fn"><?php the_author_posts_link(); ?></span>
        </span> &middot; Published <span class="published"><?php echo get_the_date( get_option('date_format') ); ?></span>
        <?php if( get_the_modified_date() != get_the_date() ) : ?> &middot; Last modified <span class="updated"><?php the_modified_date( get_option('date_format') ); ?></span><?php endif; ?>
      </p>
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
  ?>
  <div class="sek-post-grid-wrapper">
    <?php
      while ( $post_collection->have_posts() ) {
          $post_collection->the_post();
          sek_render_post();
      }//while
    ?>
  </div>
  <?php
}