<?php
/**
 * "Možda vam se dopada": up to 3 product suggestions at the bottom of the mini cart list.
 *
 * Uses the cross-sells set on the products in the cart (Proizvod → Povezani proizvodi →
 * Unakrsna prodaja), filled up with related products from the same categories.
 * Rendered inside the cart fragment, so it updates together with the mini cart.
 */
defined( 'ABSPATH' ) || exit;

const AMELIA_MINI_CART_RECS = 3;

/**
 * @return WC_Product[]
 */
function amelia_mini_cart_recommendations() {
	if ( ! WC()->cart || WC()->cart->is_empty() ) {
		return [];
	}

	$in_cart = [];
	$cross   = [];
	foreach ( WC()->cart->get_cart() as $item ) {
		$in_cart[] = (int) $item['product_id'];
		$product   = wc_get_product( $item['product_id'] );
		if ( $product ) {
			$cross = array_merge( $cross, $product->get_cross_sell_ids() );
		}
	}
	$in_cart = array_unique( $in_cart );

	$ids = array_diff( array_unique( array_map( 'intval', $cross ) ), $in_cart );
	if ( count( $ids ) < AMELIA_MINI_CART_RECS ) {
		// WooCommerce caches related products, so this stays cheap.
		$related = wc_get_related_products( $in_cart[0], AMELIA_MINI_CART_RECS * 3, $in_cart );
		$ids     = array_unique( array_merge( $ids, $related ) );
	}

	$products = [];
	foreach ( $ids as $id ) {
		$product = wc_get_product( $id );
		if ( $product && $product->is_visible() && $product->is_purchasable() && $product->is_in_stock() ) {
			$products[] = $product;
		}
		if ( count( $products ) === AMELIA_MINI_CART_RECS ) {
			break;
		}
	}
	return $products;
}

add_action( 'woocommerce_mini_cart_contents', function () {
	$products = amelia_mini_cart_recommendations();
	if ( ! $products ) {
		return;
	}
	?>
	<li class="mini-cart-recs">
		<p class="mini-cart-recs__title">Možda vam se dopada</p>
		<ul class="mini-cart-recs__list">
			<?php foreach ( $products as $product ) : ?>
			<li class="mini-cart-recs__item">
				<a class="mini-cart-recs__link" href="<?php echo esc_url( $product->get_permalink() ); ?>">
					<?php echo $product->get_image( 'woocommerce_thumbnail', [ 'loading' => 'lazy' ] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<span class="mini-cart-recs__name"><?php echo esc_html( $product->get_name() ); ?></span>
				</a>
				<span class="mini-cart-recs__price"><?php echo wp_kses_post( $product->get_price_html() ); ?></span>
				<?php if ( $product->is_type( 'simple' ) ) : ?>
					<?php // Same classes as shop buttons, so WooCommerce adds it via AJAX and refreshes the mini cart. ?>
					<a href="<?php echo esc_url( $product->add_to_cart_url() ); ?>"
						class="mini-cart-recs__add add_to_cart_button ajax_add_to_cart product_type_simple"
						data-product_id="<?php echo esc_attr( $product->get_id() ); ?>"
						data-product_sku="<?php echo esc_attr( $product->get_sku() ); ?>"
						data-quantity="1"
						rel="nofollow"
						aria-label="<?php echo esc_attr( sprintf( 'Dodaj „%s“ u korpu', $product->get_name() ) ); ?>">Dodaj</a>
				<?php else : ?>
					<a href="<?php echo esc_url( $product->get_permalink() ); ?>" class="mini-cart-recs__add">Izaberi</a>
				<?php endif; ?>
			</li>
			<?php endforeach; ?>
		</ul>
	</li>
	<?php
} );
