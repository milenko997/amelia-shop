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
					[ 'Povraćaj i zamena',     [ 'povracaj-i-zamena', 'povracaj', 'zamena', 'reklamacije' ] ],
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
			<div class="payment-icons">
				<span class="payment-icon">VISA</span>
				<span class="payment-icon">MC</span>
				<span class="payment-icon">PayPal</span>
				<span class="payment-icon">Maestro</span>
			</div>
			<?php $privacy_url = get_privacy_policy_url() ?: amelia_page_url_by_slugs( [ 'politika-privatnosti' ] ); ?>
			<?php if ( $privacy_url ) : ?>
			<a href="<?php echo esc_url( $privacy_url ); ?>">Privatnost</a>
			<?php endif; ?>
		</div>
	</footer>

</div><!-- #page -->

<?php wp_footer(); ?>
</body>
</html>
