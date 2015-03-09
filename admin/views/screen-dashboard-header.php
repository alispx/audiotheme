<div class="wrap">

<header class="audiotheme-dashboard-hero">
	<div class="audiotheme-dashboard-hero-branding">
		<h1 class="audiotheme-dashboard-hero-title"><a href="https://audiotheme.com/" target="_blank">AudioTheme</a></h1>
		<p>
			<?php printf( __( 'AudioTheme %s has all the tools you need to easily manage your gigs, discography, videos and more.', 'audiotheme' ), AUDIOTHEME_VERSION ); ?>
		</p>
	</div>

	<ul class="audiotheme-hero-tabs">
		<?php
		foreach ( self::get_tabs() as $tab ) {
			printf(
				'<li class="audiotheme-hero-tab %s"><a href="%s">%s</a></li>',
				$tab['is_active'] ? 'is-active' : '',
				esc_url( $tab['url'] ),
				esc_html( $tab['label'] )
			);
		}
		?>
	</ul>
</header>
