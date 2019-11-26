jQuery( function( $ )
{
	var type = $( '#rwi-type' ).val();

	// Autocomplete for object name
	$( '#rwi-name' ).autocomplete( {
		delay : 200,
		source: function( request, response ) {
			var data = {
				action     : 'rwi_autocomplete',
				_ajax_nonce: RWI.nonce_autocomplete,
				term       : request.term,
				type       : type
			};

			// Add post ID if inspecting object type is post meta
			if ( 'post_meta' == type ) {
				data.post_id = $( '#post_ID' ).val();
			}

			$.post( ajaxurl, data, function( r ) {
				response( r );
			}, 'json' );
		}
	} );

	// View object value
	$( '#rwi-view' ).click( function( e ) {
		e.preventDefault();

		var data = {
			action  : 'rwi_view',
			_wpnonce: RWI.nonce_view,
			name    : $( '#rwi-name' ).val(),
			type    : type
		};

		// Add post ID if inspecting object type is post meta
		if ( 'post_meta' === type ) {
			data.post_id = $( '#post_ID' ).val();
		}

		request( data );
	} );

	// Delete object
	$( '#rwi-delete' ).click( function() {
		var data = {
			action  : 'rwi_delete',
			_wpnonce: RWI.nonce_delete,
			name    : $( '#rwi-name' ).val(),
			type    : type
		};

		// Add post ID if inspecting object type is post meta
		if ( 'post_meta' === type ) {
			data.post_id = $( '#post_ID' ).val();
		}

		request( data );

		return false;
	} );

	/**
	 * Send POST request via Ajax
	 * @param data Request data
	 */
	function request( data ) {
		$( '.loading' ).show();
		$.post( ajaxurl, data, function( r ) {
			$( '.loading' ).hide();
			showResult( r );
		}, 'json' );
	}

	/**
	 * Show Ajax result
	 *
	 * @param r
	 */
	function showResult( r ) {
		var $result = $( '#rwi-result' );

		$result.hide().html( '' ).removeClass( 'error' ).removeClass( 'updated' );
		if ( ! r.success ) {
			$result.addClass( 'error' );
			r = r.data;
		} else {
			$result.addClass( 'updated' );
			r = r.data
		}
		$( '<p>' ).append( r ).appendTo( $result );
		$result.fadeIn();
	}
} );
