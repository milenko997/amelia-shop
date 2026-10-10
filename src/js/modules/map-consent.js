/**
 * Contact block: replace the consent placeholder with the Google Maps iframe on click.
 */
export function initMapConsent() {
	document.querySelectorAll( '.contact-map-consent[data-map-src]' ).forEach( ( box ) => {
		box.querySelector( '[data-map-load]' )?.addEventListener( 'click', () => {
			const iframe = document.createElement( 'iframe' );
			iframe.src            = box.dataset.mapSrc;
			iframe.title          = 'Google Maps';
			iframe.loading        = 'lazy';
			iframe.referrerPolicy = 'no-referrer-when-downgrade';
			iframe.allowFullscreen = true;
			iframe.style.cssText  = 'border:0;display:block;width:100%;height:100%;';
			box.replaceWith( iframe );
		} );
	} );
}
