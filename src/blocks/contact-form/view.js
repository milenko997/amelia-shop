/**
 * Contact form: submit via fetch, show field errors and the result without reloading.
 * Without JavaScript the form posts normally and the page shows ?kontakt=<result>.
 */
const GENERIC_ERROR = 'Poruka nije poslata. Pokušajte ponovo ili nas kontaktirajte telefonom.';

// Pages can be served from a cache for days; get a fresh signed timestamp first.
function refreshToken( form ) {
	return fetch( form.dataset.tokenUrl, { credentials: 'same-origin', cache: 'no-store' } )
		.then( ( r ) => r.json() )
		.then( ( res ) => {
			if ( res.success ) {
				form.elements.ts.value  = res.data.ts;
				form.elements.sig.value = res.data.sig;
			}
		} )
		.catch( () => {} ); // keep the token from the page
}

document.querySelectorAll( 'form[data-amelia-contact]' ).forEach( ( form ) => {
	const status = form.querySelector( '.contact-form__status' );
	const submit = form.querySelector( '.contact-form__submit' );
	if ( ! status || ! submit ) return;

	// Only pages served from a cache carry an old timestamp; a fresh page keeps its own,
	// so a quick autofill-and-send is never mistaken for a bot (3 s minimum on the server).
	const pageAge = Date.now() / 1000 - Number( form.elements.ts.value );
	if ( pageAge > 12 * 3600 ) {
		form.addEventListener( 'focusin', () => refreshToken( form ), { once: true } );
	}

	const showStatus = ( type, text ) => {
		status.hidden = false;
		status.innerHTML = '';
		const p = document.createElement( 'p' );
		p.className = `contact-form__notice contact-form__notice--${ type }`;
		p.textContent = text;
		status.appendChild( p );
	};

	const clearErrors = () => {
		form.querySelectorAll( '.contact-form__error' ).forEach( ( el ) => ( el.textContent = '' ) );
		form.querySelectorAll( '[aria-invalid]' ).forEach( ( el ) => el.removeAttribute( 'aria-invalid' ) );
	};

	const showErrors = ( errors ) => {
		let first = null;
		Object.entries( errors ).forEach( ( [ name, text ] ) => {
			const field = form.elements[ name ];
			if ( ! field ) return;
			field.setAttribute( 'aria-invalid', 'true' );
			const error = document.getElementById( field.getAttribute( 'aria-describedby' ) );
			if ( error ) error.textContent = text;
			first = first || field;
		} );
		first?.focus();
	};

	form.addEventListener( 'submit', ( e ) => {
		e.preventDefault();
		clearErrors();

		const data = new FormData( form );
		data.append( 'ajax', '1' );

		submit.disabled = true;
		submit.classList.add( 'is-loading' );

		fetch( form.action, { method: 'POST', body: data, credentials: 'same-origin' } )
			.then( ( r ) => {
				// A PHP error page or a security plugin's HTML is a server problem, not a connection one.
				if ( ! ( r.headers.get( 'content-type' ) || '' ).includes( 'application/json' ) ) {
					throw new Error( 'server' );
				}
				return r.json();
			} )
			.then( ( res ) => {
				const payload = res.data || {};
				if ( res.success ) {
					form.reset();
					showStatus( 'success', payload.message );
				} else {
					showErrors( payload.errors || {} );
					showStatus( 'error', payload.message || GENERIC_ERROR );
				}
			} )
			.catch( ( err ) => {
				showStatus(
					'error',
					err.message === 'server' ? GENERIC_ERROR : 'Poruka nije poslata zbog problema sa vezom. Proverite internet i pokušajte ponovo.'
				);
			} )
			.finally( () => {
				submit.disabled = false;
				submit.classList.remove( 'is-loading' );
			} );
	} );
} );
