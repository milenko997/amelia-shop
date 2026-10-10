<?php
/**
 * "Još X RSD do besplatne dostave" progress bar in the mini cart and on the cart page.
 *
 * The threshold comes from the WooCommerce "Free shipping" method (minimum order amount)
 * in the shipping zone that matches the cart. Without such a method it falls back to
 * Customizer → Promo traka → "Prag za besplatnu dostavu".
 */
defined( 'ABSPATH' ) || exit;

add_action( 'customize_register', function ( WP_Customize_Manager $wp_customize ) {
	$wp_customize->add_setting( 'amelia_free_shipping_threshold', [
		'default'           => 5000,
		'sanitize_callback' => 'absint',
	] );
	$wp_customize->add_control( 'amelia_free_shipping_threshold', [
		'section'     => 'amelia_promo',
		'type'        => 'number',
		'label'       => 'Prag za besplatnu dostavu (RSD)',
		'description' => 'Koristi se za traku u korpi samo dok u WooCommerce dostavi nije podešena metoda „Besplatna dostava“ sa minimalnim iznosom. 0 sakriva traku.',
	] );
} );

/**
 * @return array{amount: float, ignore_discounts: bool}|null
 */
function amelia_free_shipping_threshold() {
	if ( ! WC()->cart ) {
		return null;
	}

	$packages = WC()->cart->get_shipping_packages();
	$package  = reset( $packages );
	if ( $package ) {
		$zone = WC_Shipping_Zones::get_zone_matching_package( $package );
		foreach ( $zone->get_shipping_methods( true ) as $method ) {
			if (
				'free_shipping' === $method->id
				&& in_array( $method->requires, [ 'min_amount', 'either' ], true )
				&& (float) $method->min_amount > 0
			) {
				return [
					'amount'           => (float) $method->min_amount,
					'ignore_discounts' => 'yes' === $method->ignore_discounts,
				];
			}
		}
	}

	$fallback = (float) get_theme_mod( 'amelia_free_shipping_threshold', 5000 );
	return $fallback > 0 ? [ 'amount' => $fallback, 'ignore_discounts' => false ] : null;
}

function amelia_free_shipping_bar() {
	$threshold = amelia_free_shipping_threshold();
	if ( ! $threshold || WC()->cart->is_empty() ) {
		return;
	}

	// Same total WooCommerce's free shipping method compares against.
	$total = (float) WC()->cart->get_displayed_subtotal();
	if ( ! $threshold['ignore_discounts'] ) {
		$total -= (float) WC()->cart->get_discount_total();
		if ( WC()->cart->display_prices_including_tax() ) {
			$total -= (float) WC()->cart->get_discount_tax();
		}
	}

	$missing = max( 0, $threshold['amount'] - $total );
	$percent = min( 100, round( $total / $threshold['amount'] * 100 ) );
	?>
	<div class="free-shipping-bar<?php echo $missing <= 0 ? ' is-complete' : ''; ?>">
		<p class="free-shipping-bar__text">
			<?php if ( $missing > 0 ) : ?>
				Još <strong><?php echo wp_kses_post( wc_price( $missing ) ); ?></strong> do besplatne dostave
			<?php else : ?>
				Ostvarili ste <strong>besplatnu dostavu</strong>!
			<?php endif; ?>
		</p>
		<div class="free-shipping-bar__track" role="progressbar" aria-label="Napredak do besplatne dostave" aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?php echo esc_attr( $percent ); ?>">
			<span class="free-shipping-bar__fill" style="width: <?php echo esc_attr( $percent ); ?>%"></span>
		</div>
	</div>
	<?php
}

// Mini cart: inside .widget_shopping_cart_content, so cart fragments refresh it.
add_action( 'woocommerce_before_mini_cart', 'amelia_free_shipping_bar' );
// Cart page: inside .cart-page__main, which the cart page script re-renders after updates.
add_action( 'woocommerce_before_cart_table', 'amelia_free_shipping_bar' );
