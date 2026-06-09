function syncWishlistButtons() {
	const ids = JSON.parse( localStorage.getItem( 'amelia_favorites' ) || '[]' );
	document.querySelectorAll( '.product-wishlist[data-product-id]' ).forEach( ( btn ) => {
		const id     = parseInt( btn.dataset.productId, 10 );
		const active = ids.includes( id );
		btn.classList.toggle( 'active', active );
		const path = btn.querySelector( 'svg path' );
		if ( path ) path.style.fill = active ? 'var(--color-primary)' : 'none';
	} );
}

function loadFavorites() {
	const grid  = document.querySelector( '.favorites-block__grid' );
	const empty = document.querySelector( '.favorites-block__empty' );
	if ( ! grid ) return;

	const ids = JSON.parse( localStorage.getItem( 'amelia_favorites' ) || '[]' );

	if ( ! ids.length ) {
		grid.innerHTML = '';
		if ( empty ) empty.hidden = false;
		return;
	}

	if ( empty ) empty.hidden = true;

	const ajaxUrl = grid.dataset.ajaxUrl;
	const nonce   = grid.dataset.nonce;

	grid.classList.add( 'is-loading' );

	const body = new URLSearchParams( {
		action: 'amelia_get_favorites',
		nonce:  nonce,
		ids:    ids.join( ',' ),
	} );

	fetch( ajaxUrl, {
		method:  'POST',
		headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
		body:    body.toString(),
	} )
		.then( ( r ) => r.json() )
		.then( ( data ) => {
			grid.classList.remove( 'is-loading' );
			if ( data.success && data.data.html ) {
				grid.innerHTML = data.data.html;
				syncWishlistButtons();
				if ( window.amelia && window.amelia.initAjaxCart ) {
					window.amelia.initAjaxCart();
				}
			} else {
				grid.innerHTML = '';
			}
		} )
		.catch( () => {
			grid.classList.remove( 'is-loading' );
		} );
}

document.addEventListener( 'DOMContentLoaded', () => {
	loadFavorites();
	syncWishlistButtons();

	document.addEventListener( 'amelia:favorites-updated', ( e ) => {
		const ids  = e.detail.ids;
		const grid = document.querySelector( '.favorites-block__grid' );
		const empty = document.querySelector( '.favorites-block__empty' );

		syncWishlistButtons();

		if ( ! grid ) return;

		if ( ! ids.length ) {
			grid.innerHTML = '';
			if ( empty ) empty.hidden = false;
			return;
		}

		if ( empty ) empty.hidden = true;

		// Remove cards for products no longer in favorites
		grid.querySelectorAll( '[data-product-id]' ).forEach( ( card ) => {
			if ( ! ids.includes( parseInt( card.dataset.productId, 10 ) ) ) {
				card.closest( '.product' )?.remove();
			}
		} );

		if ( ! grid.querySelectorAll( '.product' ).length ) {
			grid.innerHTML = '';
			if ( empty ) empty.hidden = false;
			return;
		}

		// Reload if a newly added product isn't rendered yet
		const renderedIds = Array.from( grid.querySelectorAll( '[data-product-id]' ) )
			.map( ( el ) => parseInt( el.dataset.productId, 10 ) );
		if ( ids.some( ( id ) => ! renderedIds.includes( id ) ) ) {
			loadFavorites();
		}
	} );
} );
