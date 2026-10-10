export function initSizeGuide() {
	const dialog = document.getElementById( 'size-guide' );
	if ( ! dialog || typeof dialog.showModal !== 'function' ) return;

	document.querySelectorAll( '[data-size-guide-open]' ).forEach( ( btn ) => {
		btn.addEventListener( 'click', () => dialog.showModal() );
	} );

	dialog.querySelector( '[data-size-guide-close]' )?.addEventListener( 'click', () => dialog.close() );

	// Click on the backdrop (outside the dialog box) closes it.
	dialog.addEventListener( 'click', ( e ) => {
		if ( e.target === dialog ) dialog.close();
	} );
}
