<script type="text/html" id="tmpl-audiotheme-playlist-record">
	<div class="audiotheme-playlist-record-header">
		<img src="{{ data.thumbnail }}">
		<h4 class="audiotheme-playlist-record-title"><em>{{ data.title }}</em> {{ data.artist }}</h4>
	</div>

	<ol class="audiotheme-playlist-record-tracks">
		<# _.each( data.tracks, function( track ) { #>
			<li class="audiotheme-playlist-record-track" data-id="{{ track.id }}">
				<span class="audiotheme-playlist-record-track-cell">
					{{{ track.title }}}
				</span>
			</li>
		<# }); #>
	</ol>
</script>
