
<?php while ( have_posts() ) : the_post(); ?>

	<?php get_audiotheme_template_part( 'video/content', 'single' ); ?>

<?php endwhile; ?>
