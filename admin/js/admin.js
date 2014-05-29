/*global tb_remove:false, wp:false */

window.audiotheme = window.audiotheme || {};

jQuery(function($) {
	$('.wrap').on('focus', '.audiotheme-input-append input', function() {
		$(this).parent().addClass('is-focused');
	}).on('blur', '.audiotheme-input-append input', function() {
		$(this).parent().removeClass('is-focused');
	});
});

/**
 * Media Popup Helper
 *
 * Monitors clicks on any element with a class of "thickbox" and checks
 * for data attributes to modify the behavior the media popup.
 *
 * To change the "Insert Into Post" button text, add the following attribute
 * to the calling element: data-insert-button-text="New Text"
 *
 * To change the field that the media popup inserts the url to the selected
 * media item, add the following attribute with the id of the target element
 * as its value: data-insert-field="Target ID"
 */
jQuery(function($) {
	var audiothemeInsertField = null,
		audiothemeInsertButtonText = null,
		audiothemeInsertButtonInterval = null;

	$('body').on('click', '.thickbox', function() {
		var $this = $(this),
			insertField = $this.data('insert-field'),
			insertButtonText = $this.data('insert-button-text');

		clearInterval(audiothemeInsertButtonInterval);

		if ( 'undefined' !== typeof insertField ) {
			audiothemeInsertField = insertField;
		}

		if ( 'undefined' !== typeof insertButtonText ) {
			audiothemeInsertButtonText = insertButtonText;

			audiothemeInsertButtonInterval = setInterval( function() {
				var buttons = $('#TB_iframeContent').contents().find('.button[name^="send"], #insertonlybutton');

				buttons.val( audiothemeInsertButtonText );

				if (audiothemeInsertField.length) {
					buttons.off('click').on('click.audiotheme', function(e) {
						var $this = $(this),
							mediaItem = $this.closest('table'),
							url;

						e.preventDefault();

						if ( mediaItem.find('#src').length ) {
							url = mediaItem.find('#src').val();
						} else if ( mediaItem.find('.urlfile').length ) {
							url = $(this).closest('table').find('.urlfile').data('link-url');
						}

						jQuery('#' + audiothemeInsertField).val(url);
						tb_remove();

						audiothemeInsertField = null;
						audiothemeInsertButtonText = null;
						clearInterval(audiothemeInsertButtonInterval);
					});
				}
			}, 500 );
		}
	});
});

/**
 * Repeater
 */
(function($) {
	// .audiotheme-clear-on-add will clear the value of a form element in a newly added row.
	// .audiotheme-hide-on-add will hide the element in a newly added row.
	// .audiotheme-remove-on-add will remove an element from a newly added row.
	// .audiotheme-show-on-add will show a hidden elment in a newly added row.

	var methods = {
		init : function( options ) {
			var settings = {
				items: null
			};

			if (options) {
				$.extend(settings, options);
			}

			return this.each(function() {
				var repeater = $(this),
					itemsParent = repeater.find('.audiotheme-repeater-items'),
					itemTemplate, template;

				if ( repeater.data('item-template-id') ) {
					template = wp.template( repeater.data('item-template-id' ) );

					if ( settings.items ) {
						repeater.audiothemeRepeater('clearList');

						$.each( settings.items, function( i, item ) {
							itemsParent.append( template( item ).replace( /__i__/g, i ) );
						});
					}

					itemTemplate = template( {} );
					itemTemplate = $( itemTemplate.replace( /__i__/g, '0' ) );
				} else {
					itemTemplate = repeater.find('.audiotheme-repeater-item:eq(0)').clone();
				}

				repeater.data('itemIndex', repeater.find('.audiotheme-repeater-item').length || 0);
				repeater.data('itemTemplate', itemTemplate);

				repeater.audiothemeRepeater('updateIndex');

				itemsParent.sortable({
					axis: 'y',
					forceHelperSize: true,
					forcePlaceholderSize: true,
					helper: function(e, ui) {
						var $helper = ui.clone();
						$helper.children().each(function(index) {
							$(this).width(ui.children().eq(index).width());
						});

						return $helper;
					},
					update: function() {
						repeater.audiothemeRepeater('updateIndex');
					},
					change: function() {
						repeater.find('.audiotheme-repeater-sort-warning').fadeIn('slow');
					}
				});

				repeater.find('.audiotheme-repeater-add-item').on('click', function(e) {
					e.preventDefault();
					$(this).closest('.audiotheme-repeater').audiothemeRepeater('addItem');
				});

				repeater.on('click', '.audiotheme-repeater-remove-item', function(e) {
					var repeater = $(this).closest('.audiotheme-repeater');
					e.preventDefault();
					$(this).closest('.audiotheme-repeater-item').remove();
					repeater.audiothemeRepeater('updateIndex');
				});

				repeater.on('blur', 'input,select,textarea', function() {
					$(this).closest('.audiotheme-repeater').find('.audiotheme-repeater-item').removeClass('audiotheme-repeater-active-item');
				}).on('focus', 'input,select,textarea', function() {
					$(this).closest('.audiotheme-repeater-item').addClass('audiotheme-repeater-active-item').siblings().removeClass('audiotheme-repeater-active-item');
				});
			});
		},

		addItem : function() {
			var repeater = $(this),
				itemIndex = repeater.data('itemIndex'),
				itemTemplate = repeater.data('itemTemplate');

			repeater.audiothemeRepeater('clearList');

			repeater.find('.audiotheme-repeater-items').append(itemTemplate.clone())
				.children(':last-child').find('input,select,textarea').each(function() {
					var $this = $(this);
					$this.attr('name', $this.attr('name').replace('[0]', '[' + itemIndex + ']') );
				}).end()
				.find('.audiotheme-clear-on-add').val('').end()
				.find('.audiotheme-remove-on-add').remove().end()
				.find('.audiotheme-show-on-add').show().end()
				.find('.audiotheme-hide-on-add').hide().end();

			repeater.data('itemIndex', itemIndex+1).audiothemeRepeater('updateIndex');

			repeater.trigger('addItem.audiotheme', [ repeater.find('.audiotheme-repeater-items').children().last() ]);
		},

		clearList : function() {
			var itemsParent = $(this).find('.audiotheme-repeater-items');

			if ( itemsParent.hasClass('is-empty') ) {
				itemsParent.removeClass('is-empty').html('');
			}
		},

		updateIndex : function() {
			$('.audiotheme-repeater-index', this).each(function(i) {
				$(this).text(i + 1 + '.');
			});
		}
	};

	$.fn.audiothemeRepeater = function(method) {
		if ( methods[method] ) {
			return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
		} else if ( typeof method === 'object' || ! method) {
			return methods.init.apply(this, arguments);
		} else {
			$.error('Method ' +  method + ' does not exist on jQuery.audiothemeRepeater');
		}
	};
})(jQuery);