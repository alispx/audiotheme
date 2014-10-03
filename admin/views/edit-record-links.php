<table class="audiotheme-repeater" id="record-links">
	<thead>
		<tr>
			<th colspan="3"><?php _e( 'Links', 'audiotheme_i18n' ); ?></th>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<td colspan="2">
				<a class="button audiotheme-repeater-add-item"><?php _e( 'Add URL', 'audiotheme' ) ?></a>
				<?php
				printf( '<span class="audiotheme-repeater-sort-warning" style="display: none;">%1$s <br /><em>%2$s</em></span>',
					esc_html__( 'The order has been changed.', 'audiotheme' ),
					esc_html__( 'Save your changes.', 'audiotheme' )
				);
				?>
			</td>
			<td>&nbsp;</td>
		</tr>
	</tfoot>
	<tbody class="audiotheme-repeater-items">
		<?php
		foreach( $record_links as $i => $link ) :
			$link = wp_parse_args( $link, array( 'name' => '', 'url' => '' ) );
			?>
			<tr class="audiotheme-repeater-item">
				<td><input type="text" name="record_links[<?php echo $i; ?>][name]" value="<?php echo esc_attr( $link['name'] ); ?>" placeholder="<?php esc_attr_e( 'Text', 'audiotheme' ); ?>" class="record-link-name audiotheme-clear-on-add" style="width: 8em"></td>
				<td><input type="text" name="record_links[<?php echo $i; ?>][url]" value="<?php echo esc_url( $link['url'] ); ?>" placeholder="<?php esc_attr_e( 'URL', 'audiotheme' ); ?>" class="widefat audiotheme-clear-on-add"></td>
				<td class="column-action"><a class="audiotheme-repeater-remove-item"><img src="<?php echo esc_url( AUDIOTHEME_URI . 'modules/admin/images/delete.png' ); ?>" width="16" height="16" alt="<?php esc_attr_e( 'Delete Item', 'audiotheme' ) ?>" title="<?php _e( 'Delete Item', 'audiotheme' ) ?>" class="icon-delete" /></a></td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>
