export function initCartPage() {
	const cartPage = document.querySelector( '.cart-page' );
	if ( ! cartPage ) return;

	let timer = null;

	hideUpdateBtn();

	cartPage.addEventListener( 'change', function ( e ) {
		if ( ! e.target.matches( '.woocommerce-cart-form .qty' ) ) return;
		clearTimeout( timer );
		timer = setTimeout( doUpdate, 600 );
	} );

	cartPage.addEventListener( 'click', function ( e ) {
		const link = e.target.closest( '.product-remove a.remove' );
		if ( ! link ) return;
		e.preventDefault();
		doFetch( link.href );
	} );

	function hideUpdateBtn() {
		const btn = cartPage.querySelector( '[name="update_cart"]' );
		if ( btn ) btn.style.display = 'none';
	}

	function doUpdate() {
		const form = cartPage.querySelector( '.woocommerce-cart-form' );
		if ( ! form ) return;
		const data = new URLSearchParams( new FormData( form ) );
		data.set( 'update_cart', 'Update cart' );
		doFetch( form.action || window.location.href, data.toString() );
	}

	function doFetch( url, body ) {
		cartPage.classList.add( 'is-loading' );

		const opts = body
			? { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body }
			: {};

		fetch( url, opts )
			.then( function ( r ) { return r.text(); } )
			.then( function ( html ) {
				const doc = new DOMParser().parseFromString( html, 'text/html' );

				const newMain = doc.querySelector( '.cart-page__main' );
				const curMain = cartPage.querySelector( '.cart-page__main' );
				if ( newMain && curMain ) curMain.replaceWith( newMain );

				const newTotals = doc.querySelector( '.cart_totals' );
				const curTotals = cartPage.querySelector( '.cart_totals' );
				if ( newTotals && curTotals ) curTotals.replaceWith( newTotals );

				const newEmpty = doc.querySelector( '.cart-empty' );
				if ( newEmpty ) {
					cartPage.querySelector( '.cart-page__layout' ).replaceWith( newEmpty );
				}

				hideUpdateBtn();
				document.body.dispatchEvent( new Event( 'removed_from_cart' ) );
			} )
			.catch( function ( err ) { console.error( 'Cart update error:', err ); } )
			.finally( function () { cartPage.classList.remove( 'is-loading' ); } );
	}
}

export function initCart() {
	if ( typeof ameliaData === 'undefined' ) return;

	const updateCount = () => {
		fetch( ameliaData.ajaxUrl, {
			method : 'POST',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
			body   : 'action=amelia_cart_count&nonce=' + encodeURIComponent( ameliaData.nonce ),
		} )
			.then( ( r ) => r.json() )
			.then( ( data ) => {
				if ( ! data.success ) return;
				const badge = document.getElementById( 'cart-count' );
				const count = data.data.count;
				if ( badge ) {
					badge.textContent    = count;
					badge.style.display  = count > 0 ? 'flex' : 'none';
				}
			} )
			.catch( () => {} );
	};

	document.body.addEventListener( 'added_to_cart',     updateCount );
	document.body.addEventListener( 'removed_from_cart', updateCount );

	const STORAGE_KEY = 'amelia_favorites';

	function getFavoriteIds() {
		try { return JSON.parse( localStorage.getItem( STORAGE_KEY ) || '[]' ); }
		catch { return []; }
	}

	function saveFavoriteIds( ids ) {
		localStorage.setItem( STORAGE_KEY, JSON.stringify( ids ) );
	}

	function syncAllWishlistButtons() {
		const ids = getFavoriteIds();
		document.querySelectorAll( '.product-wishlist[data-product-id]' ).forEach( ( btn ) => {
			const active = ids.includes( parseInt( btn.dataset.productId, 10 ) );
			btn.classList.toggle( 'active', active );
			const path = btn.querySelector( 'svg path' );
			if ( path ) path.style.fill = active ? 'var(--color-primary)' : 'none';
		} );
	}

	syncAllWishlistButtons();

	document.addEventListener( 'click', ( e ) => {
		const btn = e.target.closest( '.product-wishlist[data-product-id]' );
		if ( ! btn ) return;

		e.preventDefault();
		e.stopPropagation();

		const id  = parseInt( btn.dataset.productId, 10 );
		let ids   = getFavoriteIds();

		if ( ids.includes( id ) ) {
			ids = ids.filter( ( i ) => i !== id );
		} else {
			ids.push( id );
		}

		saveFavoriteIds( ids );
		document.dispatchEvent( new CustomEvent( 'amelia:favorites-updated', { detail: { ids } } ) );

		const active = ids.includes( id );
		btn.classList.toggle( 'active', active );
		const path = btn.querySelector( 'svg path' );
		if ( path ) path.style.fill = active ? 'var(--color-primary)' : 'none';
	} );
}
