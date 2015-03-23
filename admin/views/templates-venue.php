<script type="text/html" id="tmpl-audiotheme-venues-search-field">
	<input type="search" placeholder="<?php _e( 'Search Venues', 'audiotheme' ); ?>">
</script>

<script type="text/html" id="tmpl-audiotheme-venue-panel-title">
	<h2>{{ data.name }}</h2>
	<button class="button">Edit</button>
</script>

<script type="text/html" id="tmpl-audiotheme-venue-details">
	<table>
		<# if ( ! data.isAddressEmpty() ) { #>
			<tr>
				<th><?php _e( 'Address:', 'audiotheme' ) ?></th>
				<td>
					<# if ( data.address ) { #>
						{{ data.address }}<br>
					<# } #>
					{{ data.city }}, {{ data.state }} {{ data.postal_code }}<br>
					{{ data.country }}
				</td>
			</tr>
		<# } #>

		<# if ( data.phone ) { #>
			<tr>
				<th><?php _e( 'Phone:', 'audiotheme' ); ?></th>
				<td>{{ data.phone }}</td>
			</tr>
		<# } #>

		<# if ( data.website ) { #>
		<tr>
			<th><?php _e( 'Website:', 'audiotheme' ); ?></th>
			<td>{{ data.website }}</td>
		</tr>
		<# } #>
	</table>
</script>

<script type="text/html" id="tmpl-audiotheme-venue-edit-form">
	<table>
		<tr>
			<th><label for="venue-name"><?php _e( 'Name', 'audiotheme' ) ?></label></th>
			<td>
				<input type="text" name="audiotheme_venue[name]" id="venue-name" class="regular-text" value="{{ data.name }}" data-setting="name">
			</td>
		</tr>
		<tr>
			<th><label for="venue-address"><?php _e( 'Address', 'audiotheme' ) ?></label></th>
			<td>
				<textarea name="audiotheme_venue[address]" id="venue-address" class="regular-text" cols="30" rows="2" data-setting="address">{{ data.address }}</textarea>
			</td>
		</tr>
		<tr>
			<th><label for="venue-city"><?php _e( 'City', 'audiotheme' ) ?></label></th>
			<td>
				<input type="text" name="audiotheme_venue[city]" id="venue-city" class="regular-text" value="{{ data.city }}" data-setting="city">
			</td>
		</tr>
		<tr>
			<th><label for="venue-state"><?php _e( 'State', 'audiotheme' ) ?></label></th>
			<td>
				 <input type="text" name="audiotheme_venue[state]" id="venue-state" class="regular-text" value="{{ data.state }}" data-setting="state">
			</td>
		</tr>
		<tr>
			<th><label for="venue-postal-code"><?php _e( 'Postal Code', 'audiotheme' ) ?></label></th>
			<td>
				<input type="text" name="audiotheme_venue[postal_code]" id="venue-postal-code" class="regular-text" value="{{ data.postal_code }}" data-setting="postal_code">
			</td>
		</tr>
		<tr>
			<th><label for="venue-country"><?php _e( 'Country', 'audiotheme' ) ?></label></th>
			<td>
				<input type="text" name="audiotheme_venue[country]" id="venue-country" class="regular-text" value="{{ data.country }}" data-setting="country">
			</td>
		</tr>
		<tr>
			<th><label for="venue-timezone-string"><?php _e( 'Time zone', 'audiotheme' ) ?></label></th>
			<td>
				<select id="venue-timezone-string" name="audiotheme_venue[timezone_string]" data-setting="timezone_string">
					<?php echo wp_timezone_choice( get_option( 'timezone_string' ) ); ?>
				</select>
			</td>
		</tr>
		<tr>
			<th><label for="venue-phone"><?php _e( 'Phone', 'audiotheme' ) ?></label></th>
			<td>
				<input type="text" name="audiotheme_venue[phone]" id="venue-phone" class="regular-text" value="{{ data.phone }}" data-setting="phone">
			</td>
		</tr>
		<tr>
			<th><label for="venue-website"><?php _e( 'Website', 'audiotheme' ) ?></label></th>
			<td>
				<input type="text" name="audiotheme_venue[website]" id="venue-website" class="regular-text" value="{{ data.website }}" data-setting="website">
			</td>
		</tr>
	</table>
</script>
