const FOCUSABLE = 'a[href], button:not([disabled]), input:not([disabled]), select, textarea, [tabindex]:not([tabindex="-1"])';

export function initMiniCart() {
	const drawer  = document.getElementById( 'mini-cart' );
	const toggle  = document.getElementById( 'mini-cart-toggle' );
	const overlay = document.getElementById( 'mini-cart-overlay' );
	const page    = document.getElementById( 'page' );
	if ( ! drawer || ! toggle || ! overlay ) return;

	const closeBtn = drawer.querySelector( '.mini-cart__close' );
	const isOpen   = () => document.body.classList.contains( 'mini-cart-open' );

	// The icon keeps its cart URL for no-JS visitors, but behaves as a button once the drawer works.
	toggle.setAttribute( 'role', 'button' );

	const open = () => {
		if ( isOpen() ) return;
		drawer.inert   = false;
		overlay.hidden = false;
		if ( page ) page.inert = true;
		toggle.setAttribute( 'aria-expanded', 'true' );
		document.body.classList.add( 'mini-cart-open' );
		closeBtn?.focus();
	};

	const close = () => {
		if ( ! isOpen() ) return;
		drawer.inert   = true;
		overlay.hidden = true;
		if ( page ) page.inert = false;
		toggle.setAttribute( 'aria-expanded', 'false' );
		document.body.classList.remove( 'mini-cart-open' );
		toggle.focus();
	};

	toggle.addEventListener( 'click', ( e ) => {
		e.preventDefault();
		open();
	} );

	// role="button" on a link: Space should activate it like a real button.
	toggle.addEventListener( 'keydown', ( e ) => {
		if ( e.key === ' ' ) {
			e.preventDefault();
			open();
		}
	} );

	overlay.addEventListener( 'click', close );
	closeBtn?.addEventListener( 'click', close );

	document.addEventListener( 'keydown', ( e ) => {
		if ( ! isOpen() ) return;

		if ( e.key === 'Escape' ) {
			close();
			return;
		}

		// Keep Tab focus inside the drawer.
		if ( e.key === 'Tab' ) {
			const items = Array.from( drawer.querySelectorAll( FOCUSABLE ) );
			if ( ! items.length ) return;
			const first = items[ 0 ];
			const last  = items[ items.length - 1 ];
			if ( e.shiftKey && document.activeElement === first ) {
				e.preventDefault();
				last.focus();
			} else if ( ! e.shiftKey && document.activeElement === last ) {
				e.preventDefault();
				first.focus();
			}
		}
	} );

	if ( window.jQuery ) {
		const $body = window.jQuery( document.body );
		// Show the updated cart right after an AJAX add to cart (fired via jQuery by WooCommerce).
		$body.on( 'added_to_cart', open );
		// Removing an item re-renders the drawer content and destroys the focused link.
		$body.on( 'removed_from_cart', () => {
			if ( isOpen() && ! drawer.contains( document.activeElement ) ) closeBtn?.focus();
		} );
	}

	// Page rendered right after a regular add to cart (single product form).
	if ( drawer.hasAttribute( 'data-open-on-load' ) ) open();
}
