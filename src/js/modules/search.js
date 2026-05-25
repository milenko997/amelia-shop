export function initSearch() {
	const toggle   = document.getElementById( 'search-toggle' );
	const dropdown = document.getElementById( 'search-dropdown' );
	if ( ! toggle || ! dropdown ) return;

	toggle.addEventListener( 'click', () => {
		const isHidden = dropdown.hasAttribute( 'hidden' );
		if ( isHidden ) {
			dropdown.removeAttribute( 'hidden' );
			dropdown.querySelector( 'input[type="search"]' )?.focus();
		} else {
			dropdown.setAttribute( 'hidden', '' );
		}
	} );

	document.addEventListener( 'keydown', ( e ) => {
		if ( e.key === 'Escape' ) dropdown.setAttribute( 'hidden', '' );
	} );

	document.addEventListener( 'click', ( e ) => {
		if ( ! toggle.contains( e.target ) && ! dropdown.contains( e.target ) ) {
			dropdown.setAttribute( 'hidden', '' );
		}
	} );
}
