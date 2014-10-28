<?php if ( empty( $records ) ) : ?>

	<h4><?php echo get_the_title( $record->ID ); ?></h4>

	<?php if ( $artist || $genre || $release ) : ?>

		<table>
			<?php if ( $artist ) : ?>
				<tr>
					<th><?php _e( 'Artist:', 'audiotheme' ); ?></th>
					<td><?php echo esc_html( $artist ); ?></td>
				</tr>
			<?php endif; ?>

			<?php if ( $release ) : ?>
				<tr>
					<th><?php _e( 'Release:', 'audiotheme' ); ?></th>
					<td><?php echo esc_html( $release ); ?></td>
				</tr>
			<?php endif; ?>

			<?php if ( $genre ) : ?>
				<tr>
					<th><?php _e( 'Genre:', 'audiotheme' ); ?></th>
					<td><?php echo esc_html( $genre ); ?></td>
				</tr>
			<?php endif; ?>
		</table>

	<?php endif; ?>

	<?php if ( $tracks = get_audiotheme_record_tracks( $record->ID ) ) : ?>

		<h5><?php _e( 'Tracks', 'audiotheme' ); ?></h5>
		<ol class="audiotheme-tracklist">
			<?php
			foreach ( $tracks as $track ) {
				echo '<li>';
					if ( $track->ID == $post->ID ) {
						echo esc_html( get_the_title( $track->ID ) );
					} else {
						printf(
							'<a href="%s">%s</a>',
							esc_url( get_edit_post_link( $track->ID ) ),
							esc_html( get_the_title( $track->ID ) )
						);
					}
				echo '</li>';
			}
			?>
		</ol>

	<?php endif; ?>

	<p>
		<a href="<?php echo get_edit_post_link( $record->ID ); ?>" class="button alignright"><?php echo $record_post_type_object->labels->edit_item; ?></a>
	</p>

<?php else : ?>

	<p class="audiotheme-field">
		<label for="post-parent"><?php _e( 'Record:', 'audiotheme' ); ?></label>
		<select name="post_parent" id="post-parent" class="widefat">
			<option value=""></option>
			<?php
			foreach ( $records as $record ) {
				printf( '<option value="%s">%s</option>',
					$record->ID,
					esc_html( $record->post_title )
				);
			}
			?>
		</select>
		<span class="description"><?php _e( 'Assign this track to a record.', 'audiotheme' ); ?></span>
	</p>

<?php endif; ?>

<div class="clear"></div>
