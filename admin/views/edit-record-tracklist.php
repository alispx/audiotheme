<table id="record-tracklist" class="audiotheme-repeater audiotheme-edit-after-editor widefat" data-item-template-id="audiotheme-track">
	<thead>
		<tr>
			<th colspan="5"><?php _e( 'Tracks', 'audiotheme' ) ?></th>
			<th class="column-action">
				<?php if ( current_user_can( 'publish_posts' ) ) : ?>
					<a class="button audiotheme-repeater-add-item"><?php _e( 'Add Track', 'audiotheme' ) ?></a>
				<?php endif; ?>
			</th>
		</tr>
	</thead>

	<tfoot>
	    <tr>
	    	<td colspan="5">
	    		<?php
	    		printf( '<span class="audiotheme-repeater-sort-warning" style="display: none">%1$s <em>%2$s</em></span>',
	    			esc_html__( 'The order has been changed.', 'audiotheme' ),
	    			esc_html__( 'Save your changes.', 'audiotheme' )
	    		);
	    		?>
	    	</td>
			<td class="column-action">
				<?php if ( current_user_can( 'publish_posts' ) ) : ?>
					<a class="button audiotheme-repeater-add-item"><?php _e( 'Add Track', 'audiotheme' ) ?></a>
				<?php endif; ?>
			</td>
	    </tr>
	</tfoot>

	<tbody class="audiotheme-repeater-items is-empty">
		<tr>
			<td colspan="6"><?php echo get_post_type_object( 'audiotheme_track' )->labels->not_found; ?></td>
		</tr>
	</tbody>
</table>
