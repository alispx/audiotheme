<?php if ( ! get_query_var( 'paged' ) && ! get_query_var( 'page' ) ) : ?>

	<header class="audiotheme-archive-header archive-header">
		<?php the_audiotheme_archive_title( '<h1 class="audiotheme-archive-title archive-title">', '</h1>' ); ?>
		<?php the_audiotheme_archive_description( '<div class="audiotheme-archive-intro archive-intro">', '</div>' ); ?>
	</header>

<?php endif; ?>
