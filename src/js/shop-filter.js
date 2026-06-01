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
	let hasMore   = productsWrap.dataset.hasMore === '1';
	let currPage  = 1;

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
		if ( isLoading ) return;

		isLoading = true;

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

		form.querySelectorAll( 'input[name="categories[]"]:checked' ).forEach( function ( cb ) {
			body.append( 'categories[]', cb.value );
		} );

		fetch( ameliaShop.ajaxUrl, { method: 'POST', body } )
			.then( function ( r ) { return r.json(); } )
			.then( function ( data ) {
				if ( ! data.success ) return;

				const d  = data.data;
				hasMore  = d.hasMore;
				currPage = d.page;

				console.log( '[Amelia] AJAX response — found_posts:', d._debug.found_posts,
					'| IDs:', d._debug.query_ids,
					'| args:', d._debug.args );

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
			.catch( function ( err ) { console.error( 'Shop filter error:', err ); } )
			.finally( function () {
				isLoading = false;
				productsWrap.classList.remove( 'is-loading' );
				if ( sentinel ) sentinel.classList.remove( 'is-loading' );
			} );
	}

	const initItems = productsWrap.querySelectorAll( 'li.product' );
	console.log( '[Amelia] Initial DOM products:', initItems.length,
		'| data-total:', productsWrap.dataset.total,
		'| data-has-more:', productsWrap.dataset.hasMore );

	updateTrack();
} )();
