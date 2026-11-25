/* Gallery picker for the "Ảnh" field in Artvibes meta boxes. */
jQuery(function ($) {
	$(document).on('click', '.av-gal-pick', function () {
		var input = $('#' + $(this).data('target')), preview = $(this).siblings('.av-gal');
		var frame = wp.media({ title: 'Chọn ảnh', multiple: 'add', library: { type: 'image' } });
		frame.on('open', function () {
			var sel = frame.state().get('selection');
			input.val().split(',').filter(Boolean).forEach(function (id) { sel.add(wp.media.attachment(id)); });
		});
		frame.on('select', function () {
			var items = frame.state().get('selection').toJSON();
			input.val(items.map(function (a) { return a.id; }).join(','));
			preview.html(items.map(function (a) {
				var src = (a.sizes && a.sizes.thumbnail ? a.sizes.thumbnail : a).url;
				return '<img src="' + src + '" alt="">';
			}).join(''));
		});
		frame.open();
	});
});
