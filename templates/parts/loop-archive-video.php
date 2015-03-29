<?php
/**
 *
 */

$columns = get_audiotheme_archive_meta( 'columns', true, 4 );
?>

<?php if ( is_audiotheme_theme_compat_active() ) : ?>

	<?php the_audiotheme_archive_description( '<div class="audiotheme-archive-intro archive-intro">', '</div>' ); ?>

<?php endif; ?>

<ul class="audiotheme-videos audiotheme-grid audiotheme-grid--columns-<?php echo absint( $columns ); ?> no-fouc" data-audiotheme-media-classes="400,600">

	<?php while ( have_posts() ) : the_post(); ?>

		<li id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

			<p class="audiotheme-featured-image">
				<a href="<?php the_permalink(); ?>"><?php the_post_thumbnail( 'video-thumbnail' ); ?></a>
			</p>

			<?php the_title( '<h2 class="audiotheme-video-title entry-title"><a href="' . esc_url( get_permalink() ) . '">', '</a></h2>' ); ?>

		</li>

	<?php endwhile; ?>

</ul>

<?php audiotheme_archive_nav(); ?>
