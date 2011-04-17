var TextUpload = {

	init: function() {
		jQuery('<em>' + Symphony.Language.get('Remove File') + '</em>').
		appendTo('span.file:not(:has(input))').
		click(function(event) {
			var div = jQuery(this).parents('div.frame'),
			name = div.attr('id');

			// Prevent clicktrough
			event.preventDefault();

			// Add new empty file input
			div.empty().append('<span class="file"><input name="' + name + '" type="file"></span>');
		});
	}
}

jQuery(document).ready(function(){
	TextUpload.init();
});
