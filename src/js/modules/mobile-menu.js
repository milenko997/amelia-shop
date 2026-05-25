export function initMobileMenu() {
	const toggle = document.getElementById( 'mobile-menu-toggle' );
	if ( ! toggle ) return;

	toggle.addEventListener( 'click', () => {
		const expanded = toggle.getAttribute( 'aria-expanded' ) === 'true';
		toggle.setAttribute( 'aria-expanded', String( ! expanded ) );
		document.body.classList.toggle( 'mobile-nav-open' );
	} );

	document.addEventListener( 'keydown', ( e ) => {
		if ( e.key === 'Escape' && document.body.classList.contains( 'mobile-nav-open' ) ) {
			document.body.classList.remove( 'mobile-nav-open' );
			toggle.setAttribute( 'aria-expanded', 'false' );
		}
	} );
}
