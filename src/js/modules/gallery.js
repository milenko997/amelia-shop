export function initGallery() {
	if ( ! window.matchMedia( '(hover: hover) and (pointer: fine)' ).matches ) return;

	// Prevent lightbox opening on desktop.
	document.addEventListener( 'click', function ( e ) {
		if ( e.target.closest( '.woocommerce-product-gallery__image a' ) ) {
			e.preventDefault();
		}
	} );

	// Track cursor position so the CSS zoom scales around the pointer.
	document.addEventListener( 'mousemove', function ( e ) {
		const wrap = e.target.closest( '.woocommerce-product-gallery__image' );
		if ( ! wrap ) return;
		const img = wrap.querySelector( 'img' );
		if ( ! img ) return;
		const rect = wrap.getBoundingClientRect();
		const x = ( ( e.clientX - rect.left ) / rect.width  * 100 ).toFixed( 2 ) + '%';
		const y = ( ( e.clientY - rect.top  ) / rect.height * 100 ).toFixed( 2 ) + '%';
		img.style.transformOrigin = x + ' ' + y;
	} );
}
