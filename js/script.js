jQuery( function( $ )
{
	var type = $( '#rwi-type' ).val();

	// Autocomplete for object name
	$( '#rwi-name' ).autocomplete( {
		delay : 200,
		source: function( request, response )
		{
			var data = {
				action     : 'rwi_autocomplete',
				_ajax_nonce: RWI.nonce_autocomplete,
				term       : request.term,
				type       : type
			};

			// Add post ID if inspecting object type is post meta
			if ( 'post_meta' == type )
				data.post_id = $( '#post_ID' ).val();

			$.post( ajaxurl, data, function( r )
			{
				response( r );
			}, 'json' );
		}
	} );

	// View object value
	$( '#rwi-view' ).click( function()
	{
		var data = {
			action  : 'rwi_view',
			_wpnonce: RWI.nonce_view,
			name    : $( '#rwi-name' ).val(),
			type    : type
		};

		// Add post ID if inspecting object type is post meta
		if ( 'post_meta' === type )
			data.post_id = $( '#post_ID' ).val();

		request( data );

		return false;
	} );

	// Delete object
	$( '#rwi-delete' ).click( function()
	{
		var data = {
			action  : 'rwi_delete',
			_wpnonce: RWI.nonce_delete,
			name    : $( '#rwi-name' ).val(),
			type    : type
		};

		// Add post ID if inspecting object type is post meta
		if ( 'post_meta' === type )
			data.post_id = $( '#post_ID' ).val();

		request( data );

		return false;
	} );

	/**
	 * Send POST request via Ajax
	 *
	 * @param data Request data
	 *
	 * @return void
	 */
	function request( data )
	{
		$( '.loading' ).show();
		$.post( ajaxurl, data, function( r )
		{
			$( '.loading' ).hide();
			showResult( r );
		}, 'xml' );
	}

	/**
	 * Show Ajax result
	 *
	 * @param r
	 */
	function showResult( r )
	{
		var r = wpAjax.parseAjaxResponse( r, 'ajax-response' ),
			$result = $( '#rwi-result' );

		$result.hide().html( '' ).removeClass( 'error' ).removeClass( 'updated' );
		if ( r.errors )
		{
			$result.addClass( 'error' );
			r = r.responses[0].errors[0].message;
		}
		else
		{
			$result.addClass( 'updated' );
			r = r.responses[0].data
		}
		$( '<p>' ).append( r ).appendTo( $result );
		$result.fadeIn();
	}
} );
