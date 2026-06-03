<?php
defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_thankyou', $order ? $order->get_id() : 0 );
?>

<div class="order-received">
	<div class="container">

	<?php if ( $order ) : ?>

		<?php if ( $order->has_status( 'failed' ) ) : ?>

			<div class="order-received__failed">
				<p><?php esc_html_e( 'Nažalost, vaša porudžbina nije mogla biti obrađena jer je banka/trgovac odbila transakciju. Pokušajte ponovo.', 'amelia-shop' ); ?></p>
				<a href="<?php echo esc_url( $order->get_checkout_payment_url() ); ?>" class="btn btn-primary"><?php esc_html_e( 'Pokušaj ponovo', 'amelia-shop' ); ?></a>
				<?php if ( is_user_logged_in() ) : ?>
					<a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" class="btn btn-outline"><?php esc_html_e( 'Moj nalog', 'amelia-shop' ); ?></a>
				<?php endif; ?>
			</div>

		<?php else : ?>

			<div class="order-received__banner">
				<div class="order-received__check">
					<svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
						<circle cx="24" cy="24" r="20"/>
						<polyline points="16 24 21 29 33 17"/>
					</svg>
				</div>
				<h1 class="order-received__title"><?php esc_html_e( 'Hvala na porudžbini!', 'amelia-shop' ); ?></h1>
				<p class="order-received__subtitle"><?php esc_html_e( 'Vaša porudžbina je primljena i trenutno se obrađuje. Potvrda je poslata na vašu e-mail adresu.', 'amelia-shop' ); ?></p>
				<a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="btn order-received__shop-btn">
					<?php esc_html_e( 'Nastavite kupovinu', 'amelia-shop' ); ?>
				</a>
			</div>

			<ul class="order-received__overview">
				<li>
					<span><?php esc_html_e( 'Broj porudžbine', 'amelia-shop' ); ?></span>
					<strong><?php echo $order->get_order_number(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></strong>
				</li>
				<li>
					<span><?php esc_html_e( 'Datum', 'amelia-shop' ); ?></span>
					<strong><?php echo wc_format_datetime( $order->get_date_created() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></strong>
				</li>
				<?php if ( is_user_logged_in() && $order->get_user_id() === get_current_user_id() && $order->get_billing_email() ) : ?>
				<li>
					<span><?php esc_html_e( 'E-mail', 'amelia-shop' ); ?></span>
					<strong><?php echo esc_html( $order->get_billing_email() ); ?></strong>
				</li>
				<?php endif; ?>
				<li>
					<span><?php esc_html_e( 'Ukupno', 'amelia-shop' ); ?></span>
					<strong><?php echo $order->get_formatted_order_total(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></strong>
				</li>
				<?php if ( $order->get_payment_method_title() ) : ?>
				<li>
					<span><?php esc_html_e( 'Način plaćanja', 'amelia-shop' ); ?></span>
					<strong><?php echo wp_kses_post( $order->get_payment_method_title() ); ?></strong>
				</li>
				<?php endif; ?>
			</ul>

			<div class="order-received__details">
				<?php do_action( 'woocommerce_thankyou_' . $order->get_payment_method(), $order->get_id() ); ?>
				<?php do_action( 'woocommerce_thankyou', $order->get_id() ); ?>
			</div>

		<?php endif; ?>

	<?php else : ?>

		<div class="order-received__banner">
			<div class="order-received__check">
				<svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
					<circle cx="24" cy="24" r="20"/>
					<polyline points="16 24 21 29 33 17"/>
				</svg>
			</div>
			<h1 class="order-received__title"><?php esc_html_e( 'Hvala na porudžbini!', 'amelia-shop' ); ?></h1>
			<p class="order-received__subtitle"><?php esc_html_e( 'Vaša porudžbina je primljena.', 'amelia-shop' ); ?></p>
			<a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="btn order-received__shop-btn">
				<?php esc_html_e( 'Nastavite kupovinu', 'amelia-shop' ); ?>
			</a>
		</div>

	<?php endif; ?>

	</div>
</div>
