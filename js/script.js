jQuery(document).ready(function($) {

	var type = $('#rwi-type').val();

	// Autocomplete for object name
	$('#rwi-name').autocomplete({
		delay: 200,
		source: function(request, response) {
			var	data = {
				action  : 'rwi_autocomplete',
				_wpnonce: RWI.nonce_autocomplete,
				term    : request.term,
				type    : type
			};

			// Add post ID if inspecting object type is post meta
			if ( 'post_meta' === type )
				data.post_id = $('#post_ID').val();

			$.post(ajaxurl, data, function(r) {
				response(r);
			}, 'json');
		}
	});

	// View object value
	$('#rwi-view').click(function() {
		var data = {
			action  : 'rwi_view',
			_wpnonce: RWI.nonce_view,
			name    : $('#rwi-name').val(),
			type    : type
		};

		// Add post ID if inspecting object type is post meta
		if ( 'post_meta' === type )
			data.post_id = $('#post_ID').val();

		request(data);

		return false;
	});

	// Delete object
	$('#rwi-delete').click(function() {
		var data = {
			action  : 'rwi_delete',
			_wpnonce: RWI.nonce_delete,
			name    : $('#rwi-name').val(),
			type    : type
		};

		// Add post ID if inspecting object type is post meta
		if ( 'post_meta' === type )
			data.post_id = $('#post_ID').val();

		request(data);

		return false;
	});

	/**
	 * Send POST request via Ajax
	 *
	 * @param data Request data
	 *
	 * @return void
	 */
	function request(data) {
		$('.loading').show();
		$.post(ajaxurl, data, function(response) {
			$('.loading').hide();
			show_result(response);
		}, 'xml');
	}

	/**
	 * Show Ajax result
	 *
	 * @param response Ajax response in JSON format
	 */
	function show_result(r) {
		var res = wpAjax.parseAjaxResponse(r, 'ajax-response'),
			$result = $('#rwi-result'),
			html;

		$result.hide().html('').removeClass('error').removeClass('updated');
		if ( r.errors ) {
			$result.addClass('error');
			html = r.responses[0].errors[0].message;
		} else {
			$result.addClass('updated');
			html = r.responses[0].data
		}
		$('<p>').append(html).appendTo($result);
		$result.fadeIn();
	}
});