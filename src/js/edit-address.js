( function( $ ) {
	const saveAddressForm = $( '.mlwoo--edit-address form' );

	saveAddressForm.on( 'submit', function( e ) {
		e.preventDefault();

		const pill = $( '.mlwoo__pill--success' );
		const formData = $( this ).serialize();
		const saveBtnText = $( '.mlwoo--save-address-text' );
		const saveBtnSpinner = $( '.mlwoo--save-address-spinner' );
		const formWrapper = $( '.woocommerce-MyAccount-content' )

		$.ajax( {
			url: mlwoo.ajaxUrl,
			type: 'POST',
			data: formData,
			beforeSend: function() {
				saveBtnText.hide();
				saveBtnSpinner.show();
			}
		} ).done( function( response ) {
			if ( ! response.success ) {
				formWrapper.find( '.woocommerce-error' ).remove();
				formWrapper.prepend( response.data );
				saveBtnText.show();
				saveBtnSpinner.hide();
				$( 'html, body' ).animate( { scrollTop: 0 }, 'slow' );
				return;
			}

			saveBtnText.show();
			saveBtnSpinner.hide();

			pillAnimate();
		} );

		function pillAnimate() {
			pill.addClass( 'mlwoo__pill--slide-in' );
	
			setTimeout( function() {
				pill.removeClass( 'mlwoo__pill--slide-in' );
			}, 1800 );
		}
	} );
} )( jQuery )
