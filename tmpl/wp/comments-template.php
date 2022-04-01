<?php if ( post_password_required() ) { return; } ?>

<section id="sek-comments" class="sek-themeform">
	<?php if ( have_comments() ) : global $wp_query; ?>

		<h3><?php comments_number( __( 'No Responses', 'text_doma' ), __( '1 Response', 'text_doma' ), __( '% Responses', 'text_doma' ) ); ?></h3>

		<div id="sek-commentlist-container" class="comment-tab">

			<ol class="sek-commentlist">
				<?php wp_list_comments( sprintf( "avatar_size=%s", apply_filters('hu_avatar_size', 48 ) ) ); ?>
			</ol><!--/.commentlist-->

			<?php if ( get_comment_pages_count() > 1 && get_option('page_comments') ) : ?>
			<nav class="sek-comments-nav">
				<div class="sek-nav-previous"><?php previous_comments_link(); ?></div>
				<div class="sek-nav-next"><?php next_comments_link(); ?></div>
			</nav><!--/.comments-nav-->
			<?php endif; ?>

		</div>
	<?php else: // if there are no comments yet ?>

		<?php if (comments_open()) : ?>
			<!-- comments open, no comments -->
		<?php else : ?>
			<!-- comments closed, no comments -->
		<?php endif; ?>

	<?php endif; ?>

	<?php if ( comments_open() ) { comment_form(); } ?>

</section><!--/#comments-->