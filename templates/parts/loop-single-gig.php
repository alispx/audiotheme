
<div class="no-fouc" data-audiotheme-media-classes="400,600">

	<?php while ( have_posts() ) : the_post(); ?>

		<?php get_audiotheme_template_part( 'parts/content-gig' ); ?>

	<?php endwhile; ?>

</div>
