<?php
defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_cart' );
?>

<section class="cart-page">
	<div class="container">

		<div class="cart-page__layout">

			<div class="cart-page__main">

				<form class="woocommerce-cart-form" action="<?php echo esc_url( wc_get_cart_url() ); ?>" method="post">
					<?php do_action( 'woocommerce_before_cart_table' ); ?>

					<table class="shop_table shop_table_responsive cart woocommerce-cart-form__contents" cellspacing="0">
						<thead>
							<tr>
								<th class="product-remove"></th>
								<th class="product-thumbnail"></th>
								<th class="product-name"><?php esc_html_e( 'Proizvod', 'amelia-shop' ); ?></th>
								<th class="product-price"><?php esc_html_e( 'Cena', 'amelia-shop' ); ?></th>
								<th class="product-quantity"><?php esc_html_e( 'Količina', 'amelia-shop' ); ?></th>
								<th class="product-subtotal"><?php esc_html_e( 'Ukupno', 'amelia-shop' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php do_action( 'woocommerce_before_cart_contents' ); ?>

							<?php foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) :
								$_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
								$product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );
								$product_name = apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key );

								if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_cart_item_visible', true, $cart_item, $cart_item_key ) ) :
									$product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
							?>
							<tr class="woocommerce-cart-form__cart-item <?php echo esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key ) ); ?>">

								<td class="product-remove">
									<?php echo apply_filters( 'woocommerce_cart_item_remove_link', sprintf(
										'<a role="button" href="%s" class="remove" aria-label="%s" data-product_id="%s" data-product_sku="%s">&times;</a>',
										esc_url( wc_get_cart_remove_url( $cart_item_key ) ),
										esc_attr( sprintf( __( 'Remove %s from cart', 'woocommerce' ), wp_strip_all_tags( $product_name ) ) ),
										esc_attr( $product_id ),
										esc_attr( $_product->get_sku() )
									), $cart_item_key ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								</td>

								<td class="product-thumbnail">
									<?php
									$thumbnail = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key );
									if ( ! $product_permalink ) {
										echo $thumbnail; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
									} else {
										printf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $thumbnail ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
									}
									?>
								</td>

								<td class="product-name" data-title="<?php esc_attr_e( 'Proizvod', 'amelia-shop' ); ?>">
									<?php
									if ( ! $product_permalink ) {
										echo wp_kses_post( $product_name );
									} else {
										echo wp_kses_post( apply_filters( 'woocommerce_cart_item_name', sprintf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $_product->get_name() ), $cart_item, $cart_item_key ) );
									}
									do_action( 'woocommerce_after_cart_item_name', $cart_item, $cart_item_key );
									echo wc_get_formatted_cart_item_data( $cart_item ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
									if ( $_product->backorders_require_notification() && $_product->is_on_backorder( $cart_item['quantity'] ) ) {
										echo wp_kses_post( apply_filters( 'woocommerce_cart_item_backorder_notification', '<p class="backorder_notification">' . esc_html__( 'Available on backorder', 'woocommerce' ) . '</p>', $product_id ) );
									}
									?>
								</td>

								<td class="product-price" data-title="<?php esc_attr_e( 'Cena', 'amelia-shop' ); ?>">
									<?php echo apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								</td>

								<td class="product-quantity" data-title="<?php esc_attr_e( 'Količina', 'amelia-shop' ); ?>">
									<?php
									if ( $_product->is_sold_individually() ) {
										$min_quantity = 1;
										$max_quantity = 1;
									} else {
										$min_quantity = 0;
										$max_quantity = $_product->get_max_purchase_quantity();
									}
									$product_quantity = woocommerce_quantity_input(
										[
											'input_name'   => "cart[{$cart_item_key}][qty]",
											'input_value'  => $cart_item['quantity'],
											'max_value'    => $max_quantity,
											'min_value'    => $min_quantity,
											'product_name' => $product_name,
										],
										$_product,
										false
									);
									echo apply_filters( 'woocommerce_cart_item_quantity', $product_quantity, $cart_item_key, $cart_item ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
									?>
								</td>

								<td class="product-subtotal" data-title="<?php esc_attr_e( 'Ukupno', 'amelia-shop' ); ?>">
									<?php echo apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								</td>

							</tr>
							<?php endif; endforeach; ?>

							<?php do_action( 'woocommerce_cart_contents' ); ?>
							<?php do_action( 'woocommerce_after_cart_contents' ); ?>
						</tbody>
					</table>

					<?php do_action( 'woocommerce_after_cart_table' ); ?>

					<div class="cart-actions">
						<?php if ( wc_coupons_enabled() ) : ?>
						<div class="cart-coupon">
							<label for="coupon_code" class="screen-reader-text"><?php esc_html_e( 'Coupon:', 'woocommerce' ); ?></label>
							<input type="text" name="coupon_code" class="input-text" id="coupon_code" value="" placeholder="<?php esc_attr_e( 'Kod kupona', 'amelia-shop' ); ?>">
							<button type="submit" class="btn btn-outline" name="apply_coupon" value="<?php esc_attr_e( 'Apply coupon', 'woocommerce' ); ?>"><?php esc_html_e( 'Primeni kupon', 'amelia-shop' ); ?></button>
							<?php do_action( 'woocommerce_cart_coupon' ); ?>
						</div>
						<?php endif; ?>

						<button type="submit" class="btn btn-outline" name="update_cart" value="<?php esc_attr_e( 'Update cart', 'woocommerce' ); ?>"><?php esc_html_e( 'Ažuriraj korpu', 'amelia-shop' ); ?></button>

						<?php do_action( 'woocommerce_cart_actions' ); ?>
						<?php wp_nonce_field( 'woocommerce-cart', 'woocommerce-cart-nonce' ); ?>
					</div>

				</form>

			</div>

			<aside class="cart-page__sidebar">
				<?php do_action( 'woocommerce_cart_collaterals' ); ?>
			</aside>

		</div>
	</div>
</section>

<?php do_action( 'woocommerce_after_cart' ); ?>
