( function( $ ) {
	$( function() {
		let ml_niceEditor     = false;
		let currentTextarea   = false;
		let mlwooNonce        = $( '#mlwoo_nonce_editor' ).val();
		let cssEditorsWrapper = $( '.mlwoo-css-editors-wrapper' );
		let saveButton        = $( '.mlwoo-page-css-save' );

		currentTextarea = $( `textarea[name="mlwoo-commerce-css-textarea"]` );

		if ( ml_niceEditor ) {
			ml_niceEditor.toTextArea();
		}

		cssEditorsWrapper.find( '.ml-show' ).removeClass( 'ml-show' );
		currentTextarea.addClass( 'ml-show' );

		ml_niceEditor = wp.codeEditor.initialize( currentTextarea.get( 0 ), mlCodeMirror ).codemirror;

		saveButton.on( 'click', function( e ) {
			e.preventDefault();
			ml_niceEditor.save();

			if ( ! currentTextarea ) {
				return;
			}

			let ajaxData = {
				action: 'mlwoo_page_save_css',
				editor: 'mlwoo-commerce-css-textarea',
				value: currentTextarea.val(),
				ml_nonce: mlwooNonce,
			};

			jQuery.post(
				ajaxurl,
				ajaxData,
				function( response ) {
					if ( response.success ) {
						sweetAlert( 'Saved!', '', 'success' );
					} else {
						sweetAlert( 'Error!', '', 'error' );
					}
				}
			);
		} );
	} );
} )( jQuery )
