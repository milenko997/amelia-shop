	<!-- Site footer -->
	<footer id="colophon" class="site-footer">
		<div class="footer-grid">

			<!-- Brand -->
			<div class="footer-brand">
				<span class="logo-text"><?php bloginfo( 'name' ); ?></span>
				<p>Udoban i kvalitetan donji veš, čarape i pidžame za svaki dan, izrađeni s pažnjom.</p>
				<?php $social = amelia_social_urls(); ?>
				<?php if ( $social ) : ?>
				<div class="social-links">
					<?php foreach ( $social as $network => $url ) : ?>
					<a href="<?php echo esc_url( $url ); ?>" class="social-link" aria-label="<?php echo esc_attr( amelia_social_networks()[ $network ] ); ?>" target="_blank" rel="noopener noreferrer me">
						<?php echo amelia_social_icon( $network ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</a>
					<?php endforeach; ?>
				</div>
				<?php endif; ?>
			</div>

			<!-- Footer col: Shop -->
			<div class="footer-col">
				<h2 class="footer-col__title">Prodavnica</h2>
				<ul>
					<?php if ( class_exists( 'WooCommerce' ) ) : ?>
					<li><a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>">Svi proizvodi</a></li>
					<?php foreach ( amelia_footer_categories() as $cat ) : ?>
					<li><a href="<?php echo esc_url( get_term_link( $cat ) ); ?>"><?php echo esc_html( $cat->name ); ?></a></li>
					<?php endforeach; ?>
					<?php endif; ?>
				</ul>
			</div>

			<?php
			$footer_cols = [
				'Pomoć'     => [
					[ 'Informacije o dostavi', [ 'dostava', 'informacije-o-dostavi', 'isporuka' ] ],
					[ 'Reklamacije i povraćaj', [ 'reklamacije', 'povracaj-i-zamena', 'povracaj', 'zamena' ] ],
					[ 'Pravo na odustanak',    [ 'pravo-na-odustanak' ] ],
					[ 'Česta pitanja',         [ 'faq', 'cesta-pitanja' ] ],
					[ 'Kontakt',               [ 'kontakt' ] ],
				],
				'Kompanija' => [
					[ 'O nama',               [ 'o-nama' ] ],
					[ 'Politika privatnosti', [ 'politika-privatnosti' ], get_privacy_policy_url() ],
					[ 'Uslovi korišćenja',    [ 'uslovi-koriscenja', 'uslovi-kupovine', 'uslovi' ], function_exists( 'wc_terms_and_conditions_page_id' ) && wc_terms_and_conditions_page_id() ? get_permalink( wc_terms_and_conditions_page_id() ) : '' ],
				],
			];
			foreach ( $footer_cols as $col_title => $links ) :
				$items = [];
				foreach ( $links as $link ) {
					$url = ! empty( $link[2] ) ? $link[2] : amelia_page_url_by_slugs( $link[1] );
					if ( $url ) {
						$items[] = [ $link[0], $url ];
					}
				}
				if ( ! $items ) {
					continue;
				}
				?>
			<div class="footer-col">
				<h2 class="footer-col__title"><?php echo esc_html( $col_title ); ?></h2>
				<ul>
					<?php foreach ( $items as [ $label, $url ] ) : ?>
					<li><a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $label ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			</div>
			<?php endforeach; ?>
		</div>

		<!-- Footer bottom -->
		<div class="footer-bottom">
			<span>&copy; <?php echo esc_html( wp_date( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?>. Sva prava zadržana.</span>
			<?php // Only payment methods that are actually enabled in WooCommerce. ?>
			<?php $payment_methods = amelia_payment_method_titles(); ?>
			<?php if ( $payment_methods ) : ?>
			<div class="payment-icons" aria-label="Načini plaćanja">
				<?php foreach ( $payment_methods as $method ) : ?>
				<span class="payment-icon"><?php echo esc_html( $method ); ?></span>
				<?php endforeach; ?>
			</div>
			<?php endif; ?>
			<?php $privacy_url = get_privacy_policy_url() ?: amelia_page_url_by_slugs( [ 'politika-privatnosti' ] ); ?>
			<?php if ( $privacy_url ) : ?>
			<a href="<?php echo esc_url( $privacy_url ); ?>">Privatnost</a>
			<?php endif; ?>
		</div>

		<?php $company_line = amelia_footer_company_line(); ?>
		<?php if ( $company_line ) : ?>
		<p class="footer-company"><?php echo $company_line; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in amelia_footer_company_line() ?></p>
		<?php endif; ?>
	</footer>

</div><!-- #page -->

<?php if ( amelia_show_mini_cart() ) : ?>
<div class="mini-cart-overlay" id="mini-cart-overlay" hidden></div>
<aside class="mini-cart" id="mini-cart" role="dialog" aria-modal="true" aria-labelledby="mini-cart-title" inert<?php echo amelia_cart_just_added() ? ' data-open-on-load' : ''; ?>>
	<div class="mini-cart__header">
		<h2 class="mini-cart__title" id="mini-cart-title">Korpa</h2>
		<button type="button" class="mini-cart__close" id="mini-cart-close" aria-label="Zatvori korpu">
			<svg viewBox="0 0 24 24" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
		</button>
	</div>
	<?php // WooCommerce cart fragments keep this container in sync after every cart change. ?>
	<div class="widget_shopping_cart_content"><?php woocommerce_mini_cart(); ?></div>
</aside>
<?php endif; ?>

<?php wp_footer(); ?>
</body>
</html>
