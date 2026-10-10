/**
 * Variation swatches (inc/swatches.php): buttons mirror the hidden <select>
 * that WooCommerce's variation script reads and updates.
 */
export function initSwatches() {
	const $ = window.jQuery;
	if ( ! $ ) return;

	const selectOf = ( $field ) => $field.find( 'select[data-swatches]' );

	// Does any in-stock variation match this value together with the other current choices?
	function isInStock( $form, attrName, value ) {
		const variations = $form.data( 'product_variations' );
		if ( ! Array.isArray( variations ) ) return true; // loaded via AJAX: unknown

		const chosen = {};
		$form.find( 'select[data-swatches]' ).each( function () {
			if ( this.name !== attrName && this.value ) chosen[ this.name ] = this.value;
		} );

		return variations.some( ( v ) => {
			if ( ! v.is_in_stock ) return false;
			const attrs = v.attributes || {};
			if ( attrs[ attrName ] && attrs[ attrName ] !== value ) return false;
			return Object.keys( chosen ).every( ( name ) => ! attrs[ name ] || attrs[ name ] === chosen[ name ] );
		} );
	}

	function sync( $form ) {
		$form.find( '.swatches-field' ).each( function () {
			const $field  = $( this );
			const $select = selectOf( $field );
			const current = $select.val();
			const name    = $select.attr( 'name' );

			$field.find( '.swatch' ).each( function () {
				const value     = this.dataset.value;
				const $option   = $select.find( 'option' ).filter( ( i, o ) => o.value === value );
				const available = $option.length > 0 && ! $option.prop( 'disabled' );
				const selected  = current === value;

				this.disabled = ! available;
				this.classList.toggle( 'is-selected', selected );
				this.classList.toggle( 'is-out-of-stock', available && ! isInStock( $form, name, value ) );
				this.setAttribute( 'aria-checked', String( selected ) );
			} );

			// Show the chosen value next to the attribute name (e.g. "Boja: Crna").
			const $label = $field.closest( 'tr' ).find( 'th.label label' );
			let $chosen  = $label.find( '.swatch-chosen' );
			if ( ! $chosen.length ) $chosen = $( '<span class="swatch-chosen"></span>' ).appendTo( $label );
			const text = current ? $field.find( '.swatch' ).filter( ( i, b ) => b.dataset.value === current ).attr( 'title' ) : '';
			$chosen.text( text ? `: ${ text }` : '' );
		} );
	}

	$( document ).on( 'click', '.swatch', function () {
		if ( this.disabled ) return;
		const $select = selectOf( $( this ).closest( '.swatches-field' ) );
		// Clicking the selected swatch again clears the choice.
		$select.val( $select.val() === this.dataset.value ? '' : this.dataset.value ).trigger( 'change' );
	} );

	// Arrow keys move between options inside a group (radio group pattern).
	$( document ).on( 'keydown', '.swatch', function ( e ) {
		if ( ! [ 'ArrowRight', 'ArrowDown', 'ArrowLeft', 'ArrowUp' ].includes( e.key ) ) return;
		e.preventDefault();
		const buttons = $( this ).closest( '.variation-swatches' ).find( '.swatch:not(:disabled)' ).get();
		const step    = e.key === 'ArrowRight' || e.key === 'ArrowDown' ? 1 : -1;
		const next    = buttons[ ( buttons.indexOf( this ) + step + buttons.length ) % buttons.length ];
		next?.focus();
	} );

	$( document.body ).on(
		'woocommerce_update_variation_values reset_data found_variation check_variations',
		'.variations_form',
		function () {
			sync( $( this ) );
		}
	);
	$( document.body ).on( 'change', '.variations_form select[data-swatches]', function () {
		sync( $( this ).closest( '.variations_form' ) );
	} );

	$( '.variations_form' ).each( function () {
		sync( $( this ) );
	} );
}
