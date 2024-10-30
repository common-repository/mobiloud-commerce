import debouce from 'debounce';
import '../images/woocommerce-placeholder.png';
import '../images/clear.svg';
import '../images/loading.svg';
import '../images/loading-dark.svg';

( function( $ ) {
	let currentPage = 1;
	let searchText = '';
	let ajaxInProgress = false;
	let maxPosts = 0;
	const searchField = $( '#mlwoo__search-input' );
	const clearBtn = $( '.mlwoo__clear-btn' );
	const searchResults = $( '#mlwoo__search-results' );
	const productGrid = $( '.mlwoo__search-results .mlwoo__grid' );
	const loadMoreSpinner = $( '.mlwoo__load-more-spinner' );
	const body = $( 'body' );
	const debouncedSearchProduct = debouce( searchProduct, 300 );

	searchField.on( 'input', function() {
		searchText = $( this ).val();

		if ( searchText.length > 0 ) {
			clearBtn.show();
		} else {
			currentPage = 1;
			maxPosts = 0;
			clearBtn.hide();
			body.css( 'overflow', 'scroll' );
			searchResults.removeClass( 'mlwoo__search-results--slide-in' );
			setTimeout( () => {
				productGrid.html( '' );
			}, 300 );
		}
	} );

	searchField.on( 'input', function() {
		debouncedSearchProduct( false );
	} );

	clearBtn.on( 'click', function() {
		searchField.val( '' );
		currentPage = 1;
		maxPosts = 0;
		searchText = '';
		$( this ).hide();
		body.css( 'overflow', 'scroll' );
		searchResults.removeClass( 'mlwoo__search-results--slide-in' );
		setTimeout( () => {
			productGrid.html( '' );
		}, 300 );
	} );

	$( '#mlwoo__search-results' ).scroll( function() {
		if ( ajaxInProgress ) {
			return;
		}

		if ( currentPage * 10 >= maxPosts ) {
			return;
		}

		if ( $( '#mlwoo__search-results' ).scrollTop() + $( '#mlwoo__search-results' ).height() > $( '#mlwoo__search-results .mlwoo__grid--products' ).height() - 88 ) {
			++currentPage;
			searchProduct( true );
		}
	} );

	function searchProduct( append = false ) {
		if ( searchText.length <= 3 ) {
			return;
		}

		$.ajax( {
			url: mlwoo.ajaxUrl,
			type: 'POST',
			data: {
				action: 'mlwoo_get_products',
				search: searchText,
				page: currentPage,
			},
			beforeSend: function() {
				ajaxInProgress = true;

				if ( ! append ) {
					maxPosts = 0;
					productGrid.html( '' );
				}

				loadMoreSpinner.addClass( 'mlwoo__load-more-spinner--show' );
			}
		} ).done( function( response ) {
			if ( ! response.success ) {
				return;
			}

			ajaxInProgress = false;
			maxPosts = response.data.maxPosts;

			productGrid.append( response.data.htmlString );
			searchResults.addClass( 'mlwoo__search-results--slide-in' );
			body.css( 'overflow', 'hidden' );
			loadMoreSpinner.removeClass( 'mlwoo__load-more-spinner--show' );
		} );
	}
} )( jQuery )
