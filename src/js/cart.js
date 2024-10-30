( function( $ ) {
	const applyCouponBtn = $( 'button[name="apply_coupon"]' );
	const applyCouponBtnText = $( '.mlwoo--cart__apply-coupon-text' );
	const applyCouponBtnSpinner = $( '.mlwoo--cart__apply-coupon-spinner' );

	let textCache;
	let htmlCache;

	applyCouponBtn.on( 'click', function() {
		applyCouponBtnText.hide();
		applyCouponBtnSpinner.show();

		textCache = this.childNodes[2].textContent;
		htmlCache = $( this ).children();
	} );

	$( document ).on( 'click', '.mlwoo--cart__quantity-input-control', function() {
		const current = $( this );
		const action = current.data( 'quantity-action' );
		const closestParent = current.closest( '.mlwoo--cart__item-quantity' );
		const inputField = closestParent.find( '.mlwoo--cart__quantity-input' );
		const quantity = Number( inputField.val() );

		$( 'button[name="update_cart"]' ).prop( 'disabled', false );

		switch ( action ) {
			case 'increase':
				inputField.val( quantity + 1 );
				break;

			case 'decrease':
				if ( quantity > 1 ) {
					inputField.val( quantity - 1 );
				}
				break;

			default:
				break;
		}
	} );

	$( document ).on( 'applied_coupon', function() {
		applyCouponBtn.text( textCache ).append( htmlCache );
	} );

	$( document.body ).on( 'updated_cart_totals', function( event ) {
		const updatedCartTotal = getCartTotal();

		if ( 'undefined' !== typeof nativeFunctions ) {
			nativeFunctions.syncCart( updatedCartTotal );
		}
	} );

	function getCartTotal() {
		let totalItems = 0;
		const qtyInputFields = $( '.mlwoo--cart__quantity-input' );

		qtyInputFields.each( function( index, item ) {
			totalItems += Number( $( item ).val() );
		} )

		return totalItems;
	}
} )( jQuery )
