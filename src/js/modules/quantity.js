export function initQuantity() {
	document.addEventListener( 'click', function ( e ) {
		const btn = e.target.closest( '.qty-btn' );
		if ( ! btn ) return;

		const wrap = btn.closest( '.qty-wrap' );
		if ( ! wrap ) return;

		const input = wrap.querySelector( 'input.qty' );
		if ( ! input ) return;

		const step = parseFloat( input.step ) || 1;
		const min  = parseFloat( input.min ) || 0;
		const max  = input.max ? parseFloat( input.max ) : Infinity;
		let   val  = parseFloat( input.value ) || min;

		if ( btn.classList.contains( 'qty-minus' ) ) {
			val = Math.max( min, val - step );
		} else {
			val = isFinite( max ) ? Math.min( max, val + step ) : val + step;
		}

		input.value = val;
		input.dispatchEvent( new Event( 'change', { bubbles: true } ) );
	} );
}
