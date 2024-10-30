import debouce from 'debounce';
import '../images/woocommerce-placeholder.png';
import '../images/clear.svg';
import '../images/loading.svg';
import '../images/loading-dark.svg';

( function( $ ) {
	let currentPageProducts = 1;
	let currentPageCategories = 1;
	let searchText = '';
	let ajaxInProgress = false;
	let maxPostsProducts = 0;
	let maxPostsCategories = 0;
	const searchField = $( '#mlwoo__search-input' );
	const clearBtn = $( '.mlwoo__clear-btn' );
	const searchResults = $( '#mlwoo__search-results' );
	const productGrid = $( '.mlwoo__search-results .mlwoo__grid' );
	const categoryGrid = $( '.mlwoo__grid--category' );
	const loadMoreSpinnerProducts = $( '.mlwoo__load-more-spinner--products' );
	const loadMoreSpinnerCategories = $( '.mlwoo__load-more-spinner--categories' );
	const body = $( 'body' );
	const debouncedSearchProduct = debouce( searchProduct, 300 );

	searchField.on( 'input', function() {
		searchText = $( this ).val();

		if ( searchText.length > 0 ) {
			clearBtn.show();
		} else {
			currentPageProducts = 1;
			maxPostsProducts = 0;
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
		currentPageProducts = 1;
		maxPostsProducts = 0;
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

		if ( maxPostsProducts > 0 && currentPageProducts * 10 >= maxPostsProducts ) {
			return;
		}
		if ( $( '#mlwoo__search-results' ).scrollTop() + $( '#mlwoo__search-results' ).height() > $( '#mlwoo__search-results .mlwoo__grid--products' ).height() - 88 ) {
			++currentPageProducts;
			searchProduct( true );
		}
	} );

	$( window ).scroll( function() {
		if ( ajaxInProgress ) {
			return;
		}

		if ( maxPostsCategories > 0 && currentPageCategories * 10 >= maxPostsCategories ) {
			return;
		}

		// console.log( $( window ).scrollTop() + $( window ).height(), $( '.mlwoo__grid--category' ).height() )
		if ( $( window ).scrollTop() + $( window ).height() > $( '.mlwoo__grid--category' ).height() - 88 ) {
			++currentPageCategories;
			searchCategory();
		}
	} )

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
				page: currentPageProducts,
			},
			beforeSend: function() {
				ajaxInProgress = true;

				if ( ! append ) {
					maxPostsProducts = 0;
					productGrid.html( '' );
				}

				loadMoreSpinnerProducts.addClass( 'mlwoo__load-more-spinner--show' );
			}
		} ).done( function( response ) {
			if ( ! response.success ) {
				return;
			}

			ajaxInProgress = false;
			maxPostsProducts = response.data.maxPosts;

			productGrid.append( response.data.htmlString );
			searchResults.addClass( 'mlwoo__search-results--slide-in' );
			body.css( 'overflow', 'hidden' );
			loadMoreSpinnerProducts.removeClass( 'mlwoo__load-more-spinner--show' );
		} );
	}

	function searchCategory() {
		$.ajax( {
			url: mlwoo.ajaxUrl,
			type: 'POST',
			data: {
				action: 'mlwoo_get_categories',
				page: currentPageCategories,
			},
			beforeSend: function() {
				ajaxInProgress = true;
				loadMoreSpinnerCategories.addClass( 'mlwoo__load-more-spinner--show' );
			}
		} ).done( function( response) {
			if ( ! response.success ) {
				return;
			}

			maxPostsCategories = response.data.maxPosts;
			ajaxInProgress = false;

			categoryGrid.append( response.data.htmlString );
			loadMoreSpinnerCategories.removeClass( 'mlwoo__load-more-spinner--show' );
		} );
	}
} )( jQuery )
