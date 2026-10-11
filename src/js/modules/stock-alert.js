/**
 * "Obavesti me kad stigne" (inc/stock-alerts.php).
 * - Variable products: show the form for the selected variation when it is sold out.
 * - Submit via fetch; without JavaScript the form posts normally.
 */
export function initStockAlert() {
	const box = document.querySelector( '[data-stock-alert]' );
	if ( ! box ) return;

	const form   = box.querySelector( 'form' );
	const status = box.querySelector( '.stock-alert__status' );
	const submit = box.querySelector( '.stock-alert__submit' );
	if ( ! form || ! status || ! submit ) return;

	const showStatus = ( type, text ) => {
		status.innerHTML = '';
		const p = document.createElement( 'p' );
		p.className = `stock-alert__message stock-alert__message--${ type }`;
		p.textContent = text;
		status.appendChild( p );
	};

	// Pages can be served from a cache for days; get a fresh signed timestamp (same endpoint as
	// the contact form, src/blocks/contact-form/view.js) so the server doesn't reject it as expired.
	const pageAge = Date.now() / 1000 - Number( form.elements.ts.value );
	if ( pageAge > 12 * 3600 ) {
		form.addEventListener( 'focusin', () => {
			fetch( form.dataset.tokenUrl, { credentials: 'same-origin', cache: 'no-store' } )
				.then( ( r ) => r.json() )
				.then( ( res ) => {
					if ( res.success ) {
						form.elements.ts.value  = res.data.ts;
						form.elements.sig.value = res.data.sig;
					}
				} )
				.catch( () => {} ); // keep the token from the page
		}, { once: true } );
	}

	// Variable product: follow WooCommerce's variation events (jQuery).
	const $ = window.jQuery;
	const $variations = $ ? $( '.variations_form' ) : null;
	if ( $variations && $variations.length && form.elements.product_id.value === '' ) {
		$variations.on( 'found_variation', ( e, variation ) => {
			const soldOut = ! variation.is_in_stock;
			box.hidden = ! soldOut;
			form.elements.product_id.value = soldOut ? variation.variation_id : '';
			status.innerHTML = '';
		} );
		$variations.on( 'reset_data hide_variation', () => {
			box.hidden = true;
			form.elements.product_id.value = '';
		} );
	}

	form.addEventListener( 'submit', ( e ) => {
		e.preventDefault();
		const data = new FormData( form );
		data.append( 'ajax', '1' );
		submit.disabled = true;

		fetch( form.action, { method: 'POST', body: data, credentials: 'same-origin' } )
			.then( ( r ) => {
				if ( ! ( r.headers.get( 'content-type' ) || '' ).includes( 'application/json' ) ) {
					throw new Error( 'server' );
				}
				return r.json();
			} )
			.then( ( res ) => {
				const message = ( res.data && res.data.message ) || 'Prijava nije uspela. Pokušajte ponovo.';
				if ( res.success ) {
					// reset() would clear the variation id set above; keep it.
					const productId = form.elements.product_id.value;
					form.reset();
					form.elements.product_id.value = productId;
					showStatus( 'success', message );
				} else {
					showStatus( 'error', message );
				}
			} )
			.catch( ( err ) => {
				showStatus(
					'error',
					err.message === 'server'
						? 'Prijava trenutno nije moguća. Pokušajte ponovo kasnije.'
						: 'Prijava nije uspela zbog problema sa vezom. Proverite internet i pokušajte ponovo.'
				);
			} )
			.finally( () => {
				submit.disabled = false;
			} );
	} );
}
