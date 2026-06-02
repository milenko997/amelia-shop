<?php
defined( 'ABSPATH' ) || exit;
?>

<div class="cart-empty">
	<div class="cart-empty__icon">
		<svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
			<path d="M6 8h3.5l4.5 20h18l4-12H14"/>
			<circle cx="19" cy="33" r="2.5"/>
			<circle cx="33" cy="33" r="2.5"/>
		</svg>
	</div>
	<h2 class="cart-empty__title"><?php esc_html_e( 'Vaša korpa je prazna', 'amelia-shop' ); ?></h2>
	<p class="cart-empty__text"><?php esc_html_e( 'Izgleda da još niste dodali nijedan proizvod u korpu.', 'amelia-shop' ); ?></p>
	<a href="<?php echo esc_url( apply_filters( 'woocommerce_return_to_shop_redirect', wc_get_page_permalink( 'shop' ) ) ); ?>" class="btn btn-primary cart-empty__btn">
		<?php esc_html_e( 'Nastavite kupovinu', 'amelia-shop' ); ?>
	</a>
</div>
