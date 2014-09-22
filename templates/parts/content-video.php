<article id="post-<?php the_ID(); ?>" <?php post_class( 'audiotheme-video-single' ); ?> role="article">

	<?php the_audiotheme_video(); ?>

	<header class="audiotheme-video-header entry-header">
		<?php the_title( '<h1 class="audiotheme-video-title entry-title">', '</h1>' ); ?>
	</header>
	
	<?php if ( $tag_list = get_the_tag_list( '', ' ' ) ) : ?>
		
		<p class="audiotheme-term-list">
			<span class="audiotheme-term-list-label"><?php _e( 'Tags', 'audiotheme' ); ?></span>
			<span class="audiotheme-term-list-items"><?php echo $tag_list; ?></span>
		</p>
		
	<?php endif; ?>

	<div class="audiotheme-content entry-content">
		<?php the_content( '' ); ?>
	</div>

</article>
