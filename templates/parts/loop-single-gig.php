
<div class="no-fouc js-media-classes">

	<?php while ( have_posts() ) : the_post(); ?>

		<?php get_audiotheme_template_part( 'parts/content-gig' ); ?>

	<?php endwhile; ?>

</div>
