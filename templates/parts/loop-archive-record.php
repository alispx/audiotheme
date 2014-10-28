<?php
/**
 *
 */

$columns = get_audiotheme_archive_meta( 'columns', true, 4 );
?>

<?php if ( is_audiotheme_theme_compat_active() ) : ?>

	<?php the_audiotheme_archive_description( '<div class="audiotheme-archive-intro archive-intro">', '</div>' ); ?>

<?php endif; ?>

<div class="audiotheme-records audiotheme-grid audiotheme-grid--columns-<?php echo absint( $columns ); ?> no-fouc js-media-classes">

	<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

		<?php get_audiotheme_template_part( 'parts/content-record', 'archive' ); ?>

	<?php endwhile; endif; ?>

</div>

<?php audiotheme_archive_nav(); ?>
