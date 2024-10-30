( function( $ ) {
	let page = 1;
	const loadMoreBtn = $( '.mlwoo--orders__load-more-orders' );
	const addToCartBtnText = $( '.mlwoo--orders__load-more-text' );
	const addToCartSpinner = $( '.mlwoo--orders__add-to-cart-spinner' );

	loadMoreBtn.on( 'click', function() {
		$.ajax( {
			url: mlwoo.ajaxUrl,
			type: 'POST',
			data: {
				action: 'load_more_orders',
				page: ++page,
			},
			beforeSend: function() {
				addToCartBtnText.hide();
				addToCartSpinner.show();
			},
		} ).done( function( response ) {
			if ( ! response.success ) {
				return;
			}

			if ( 0 === response.data.data.length ) {
				loadMoreBtn.hide();
				return;
			}

			addToCartBtnText.show();
			addToCartSpinner.hide();

			const orderList = document.querySelector( '.mlwoo--orders__order-list' );
			orderList.insertAdjacentHTML( 'beforeEnd', response.data.data );
		} );
	} );
} )( jQuery )
