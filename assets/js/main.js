( function () {
	'use strict';

	/* ── Mobile menu ────────────────────────────────────── */
	const toggle = document.getElementById( 'mobile-menu-toggle' );
	const nav    = document.getElementById( 'primary-nav' );

	if ( toggle && nav ) {
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

	/* ── Search dropdown ────────────────────────────────── */
	const searchToggle   = document.getElementById( 'search-toggle' );
	const searchDropdown = document.getElementById( 'search-dropdown' );

	if ( searchToggle && searchDropdown ) {
		searchToggle.addEventListener( 'click', () => {
			const hidden = searchDropdown.hasAttribute( 'hidden' );
			if ( hidden ) {
				searchDropdown.removeAttribute( 'hidden' );
				const input = searchDropdown.querySelector( 'input[type="search"]' );
				if ( input ) input.focus();
			} else {
				searchDropdown.setAttribute( 'hidden', '' );
			}
		} );

		document.addEventListener( 'keydown', ( e ) => {
			if ( e.key === 'Escape' ) searchDropdown.setAttribute( 'hidden', '' );
		} );

		document.addEventListener( 'click', ( e ) => {
			if ( ! searchToggle.contains( e.target ) && ! searchDropdown.contains( e.target ) ) {
				searchDropdown.setAttribute( 'hidden', '' );
			}
		} );
	}

	/* ── Cart count update via AJAX ─────────────────────── */
	function updateCartCount() {
		if ( typeof ameliaData === 'undefined' ) return;

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
					badge.textContent = count;
					badge.style.display = count > 0 ? 'flex' : 'none';
				}
			} )
			.catch( () => {} );
	}

	document.body.addEventListener( 'added_to_cart',     updateCartCount );
	document.body.addEventListener( 'removed_from_cart', updateCartCount );

	/* ── Sticky header shadow on scroll ─────────────────── */
	const header = document.getElementById( 'masthead' );
	if ( header ) {
		window.addEventListener( 'scroll', () => {
			header.classList.toggle( 'scrolled', window.scrollY > 50 );
		}, { passive: true } );
	}

	/* ── Wishlist button toggle (visual only) ───────────── */
	document.addEventListener( 'click', ( e ) => {
		const btn = e.target.closest( '.product-wishlist' );
		if ( ! btn ) return;
		btn.classList.toggle( 'active' );
		const svg = btn.querySelector( 'svg' );
		if ( svg ) {
			const path = svg.querySelector( 'path' );
			if ( path ) {
				path.style.fill = btn.classList.contains( 'active' )
					? 'var(--color-primary)'
					: 'none';
			}
		}
	} );

	/* ── Smooth reveal on scroll ─────────────────────────── */
	if ( 'IntersectionObserver' in window ) {
		const els = document.querySelectorAll( '.category-card, .section-header, .feature-item' );
		const io  = new IntersectionObserver(
			( entries ) => {
				entries.forEach( ( entry ) => {
					if ( entry.isIntersecting ) {
						entry.target.style.opacity  = '1';
						entry.target.style.transform = 'translateY(0)';
						io.unobserve( entry.target );
					}
				} );
			},
			{ threshold: 0.1 }
		);

		els.forEach( ( el ) => {
			el.style.opacity   = '0';
			el.style.transform = 'translateY(20px)';
			el.style.transition = 'opacity .5s ease, transform .5s ease';
			io.observe( el );
		} );
	}
} )();
