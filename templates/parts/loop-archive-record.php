
<?php // @todo if compat template, then display the description ?>

<div class="audiotheme-records audiotheme-grid">

	<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

		<?php get_audiotheme_template_part( 'parts/content-record', 'archive' ); ?>

	<?php endwhile; endif; ?>

</div>

<?php audiotheme_archive_nav(); ?>
