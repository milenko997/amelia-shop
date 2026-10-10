/* global ameliaShop */
( function () {
	'use strict';

	const form         = document.getElementById( 'shop-filter-form' );
	if ( ! form ) return;

	const productsWrap = document.getElementById( 'shop-products' );
	const sentinel     = document.getElementById( 'shop-load-sentinel' );
	const countEl      = document.getElementById( 'shop-result-count' );
	const sortEl       = document.getElementById( 'shop-sort' );
	const resetBtn     = document.getElementById( 'filter-reset' );
	const minInput     = document.getElementById( 'price-min' );
	const maxInput     = document.getElementById( 'price-max' );
	const minLabel     = document.getElementById( 'price-min-label' );
	const maxLabel     = document.getElementById( 'price-max-label' );
	const fill         = document.getElementById( 'price-fill' );

	let debounce  = null;
	let isLoading = false;
	let controller = null;
	let hasMore   = productsWrap.dataset.hasMore === '1';
	let currPage  = parseInt( productsWrap.dataset.page, 10 ) || 1;

	function fmtPrice( val ) {
		const n   = Math.round( val ).toLocaleString( 'sr-RS' );
		const sym = ameliaShop.currency;
		return ameliaShop.currPos === 'left' || ameliaShop.currPos === 'left_space'
			? sym + ' ' + n
			: n + ' ' + sym;
	}

	function updateTrack() {
		const lo   = parseFloat( minInput.min );
		const step = parseFloat( minInput.step ) || 1;
		const rawHi    = parseFloat( minInput.max );
		const stepsUp  = Math.ceil( ( rawHi - lo ) / step );
		const hi       = lo + stepsUp * step;
		if ( hi !== rawHi ) {
			minInput.setAttribute( 'max', hi );
			maxInput.setAttribute( 'max', hi );
			maxInput.value = String( hi );
		}

		const min = parseFloat( minInput.value );
		const max = parseFloat( maxInput.value );

		const pMin = ( ( min - lo ) / ( hi - lo ) ) * 100;
		const pMax = ( ( max - lo ) / ( hi - lo ) ) * 100;

		fill.style.left  = pMin + '%';
		fill.style.width = ( pMax - pMin ) + '%';

		minLabel.textContent = fmtPrice( min );
		maxLabel.textContent = fmtPrice( max );
	}

	minInput.addEventListener( 'input', function () {
		const step = parseFloat( this.step ) || 1;
		if ( parseFloat( this.value ) >= parseFloat( maxInput.value ) ) {
			this.value = parseFloat( maxInput.value ) - step;
		}
		updateTrack();
		schedule();
	} );

	maxInput.addEventListener( 'input', function () {
		const step = parseFloat( this.step ) || 1;
		if ( parseFloat( this.value ) <= parseFloat( minInput.value ) ) {
			this.value = parseFloat( minInput.value ) + step;
		}
		updateTrack();
		schedule();
	} );

	form.querySelectorAll( 'input[type="checkbox"]' ).forEach( function ( cb ) {
		cb.addEventListener( 'change', function () { runFilter( 1, false ); } );
	} );

	sortEl && sortEl.addEventListener( 'change', function () { runFilter( 1, false ); } );

	resetBtn && resetBtn.addEventListener( 'click', function () {
		minInput.value = minInput.min;
		maxInput.value = maxInput.max;
		updateTrack();
		form.querySelectorAll( 'input[type="checkbox"]' ).forEach( function ( cb ) {
			cb.checked = false;
		} );
		if ( sortEl ) sortEl.value = sortEl.options[ 0 ].value;
		runFilter( 1, false );
	} );

	productsWrap.addEventListener( 'click', function ( e ) {
		if ( e.target.closest( '[data-reset-filters]' ) ) {
			resetBtn && resetBtn.click();
		}
	} );

	if ( sentinel && 'IntersectionObserver' in window ) {
		// Infinite scroll replaces the page links that are there for crawlers and no-JS visitors.
		const pagination = document.getElementById( 'shop-pagination' );
		if ( pagination ) pagination.hidden = true;

		const observer = new IntersectionObserver( function ( entries ) {
			if ( entries[ 0 ].isIntersecting && ! isLoading && hasMore ) {
				runFilter( currPage + 1, true );
			}
		}, { rootMargin: '400px' } );
		observer.observe( sentinel );
	}

	function schedule() {
		clearTimeout( debounce );
		debounce = setTimeout( function () { runFilter( 1, false ); }, 450 );
	}

	function runFilter( page, append ) {
		clearTimeout( debounce );

		// Infinite scroll waits for the in-flight request; a filter change
		// cancels it so the user's latest selection always wins.
		if ( isLoading ) {
			if ( append ) return;
			controller.abort();
		}

		isLoading  = true;
		controller = new AbortController();
		const currentController = controller;

		if ( ! append ) {
			productsWrap.classList.add( 'is-loading' );
		} else if ( sentinel ) {
			sentinel.classList.add( 'is-loading' );
		}

		const body = new FormData();
		body.append( 'action',    'amelia_filter_products' );
		body.append( 'nonce',     ameliaShop.nonce );
		body.append( 'min_price', minInput.value );
		body.append( 'max_price', maxInput.value );
		body.append( 'orderby',   sortEl ? sortEl.value : 'date:DESC' );
		body.append( 'page',      page );
		if ( ameliaShop.termId ) {
			body.append( 'locked_taxonomy', ameliaShop.taxonomy );
			body.append( 'locked_term',     ameliaShop.termId );
		}

		form.querySelectorAll( 'input[name="categories[]"]:checked' ).forEach( function ( cb ) {
			body.append( 'categories[]', cb.value );
		} );

		fetch( ameliaShop.ajaxUrl, { method: 'POST', body, signal: currentController.signal } )
			.then( function ( r ) { return r.json(); } )
			.then( function ( data ) {
				if ( ! data.success ) return;

				const d  = data.data;
				hasMore  = d.hasMore;
				currPage = d.page;

				if ( append ) {
					// PHP returns a full <ul>…</ul>; extract only the <li> items.
					const tmp = document.createElement( 'div' );
					tmp.innerHTML = d.html;
					const newItems = tmp.querySelectorAll( 'li.product' );
					const ul = productsWrap.querySelector( 'ul.products' );
					if ( ul && newItems.length ) {
						newItems.forEach( function ( item ) { ul.appendChild( item ); } );
					}
				} else {
					if ( ! d.html ) {
						productsWrap.innerHTML =
							'<div class="shop-empty">' +
								'<div class="shop-empty__icon">' +
									'<svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' +
										'<circle cx="24" cy="9" r="3"/>' +
										'<line x1="24" y1="12" x2="24" y2="18"/>' +
										'<path d="M24 18 L6 30 H42 Z"/>' +
									'</svg>' +
								'</div>' +
								'<h3 class="shop-empty__title">Nema pronađenih proizvoda</h3>' +
								'<p class="shop-empty__text">Pokušajte da prilagodite filtere ili resetujte pretragu.</p>' +
								'<button type="button" class="btn btn-outline shop-empty__reset" data-reset-filters>Resetuj filtere</button>' +
							'</div>';
					} else {
						// PHP returns the full <ul>…</ul> via woocommerce_product_loop_start/end.
						productsWrap.innerHTML = d.html;
					}
				}

				if ( countEl ) {
					const n = d.count;
					countEl.textContent = n === 1 ? n + ' proizvod' : n + ' proizvoda';
				}
			} )
			.catch( function ( err ) {
				if ( err.name !== 'AbortError' ) console.error( 'Shop filter error:', err );
			} )
			.finally( function () {
				// A newer request has taken over — leave its loading state alone.
				if ( controller !== currentController ) return;
				isLoading = false;
				productsWrap.classList.remove( 'is-loading' );
				if ( sentinel ) sentinel.classList.remove( 'is-loading' );
			} );
	}

	updateTrack();
} )();
