
<div class="no-fouc js-media-classes">

	<?php while ( have_posts() ) : the_post(); ?>

		<?php get_audiotheme_template_part( 'parts/content-track' ); ?>

	<?php endwhile; ?>

</div>
