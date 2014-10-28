
<?php if ( is_audiotheme_theme_compat_active() ) : ?>

	<?php the_audiotheme_archive_description( '<div class="audiotheme-archive-intro archive-intro">', '</div>' ); ?>

<?php endif; ?>

<ul class="audiotheme-videos audiotheme-grid no-fouc js-media-classes">

	<?php while ( have_posts() ) : the_post(); ?>

		<li id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

			<p class="audiotheme-featured-image">
				<a href="<?php echo get_permalink( $post->ID ); ?>"><?php echo get_the_post_thumbnail( $post->ID, 'video-thumbnail' ); ?></a>
			</p>

			<?php the_title( '<h2 class="audiotheme-video-title entry-title"><a href="' . get_permalink() . '">', '</a></h2>' ); ?>

		</li>

	<?php endwhile; ?>

</ul>

<?php audiotheme_archive_nav(); ?>
