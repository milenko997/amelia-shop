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

	// Wishlist toggle (visual only — no persistence)
	document.addEventListener( 'click', ( e ) => {
		const btn = e.target.closest( '.product-wishlist' );
		if ( ! btn ) return;
		btn.classList.toggle( 'active' );
		const path = btn.querySelector( 'svg path' );
		if ( path ) {
			path.style.fill = btn.classList.contains( 'active' )
				? 'var(--color-primary)'
				: 'none';
		}
	} );
}
