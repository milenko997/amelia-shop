	<!-- Site footer -->
	<footer id="colophon" class="site-footer">
		<div class="footer-grid">

			<!-- Brand -->
			<div class="footer-brand">
				<span class="logo-text"><?php bloginfo( 'name' ); ?></span>
				<p>Udobni, elegantni i kvalitetni svakodnevni artikli — donji veš, čarape i pidžame izrađeni s pažnjom.</p>
				<div class="social-links">
					<a href="#" class="social-link" aria-label="Instagram" target="_blank">
						<svg viewBox="0 0 24 24"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1112.63 8 4 4 0 0116 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>
					</a>
				</div>
			</div>

			<!-- Footer col: Shop -->
			<div class="footer-col">
				<h4>Prodavnica</h4>
				<ul>
					<?php if ( class_exists( 'WooCommerce' ) ) : ?>
					<li><a href="<?php echo esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ); ?>">Svi proizvodi</a></li>
					<?php endif; ?>
					<li><a href="#">Donji veš</a></li>
					<li><a href="#">Gaće</a></li>
					<li><a href="#">Čarape</a></li>
					<li><a href="#">Pidžame</a></li>
					<li><a href="#">Rasprodaja</a></li>
				</ul>
			</div>

			<!-- Footer col: Help -->
			<div class="footer-col">
				<h4>Pomoć</h4>
				<ul>
					<li><a href="#">Informacije o dostavi</a></li>
					<li><a href="#">Povraćaj i zamena</a></li>
					<li><a href="#">Česta pitanja</a></li>
					<li><a href="#">Kontakt</a></li>
				</ul>
			</div>

			<!-- Footer col: Company -->
			<div class="footer-col">
				<h4>Kompanija</h4>
				<ul>
					<li><a href="#">O nama</a></li>
					<li><a href="#">Politika privatnosti</a></li>
					<li><a href="#">Uslovi korišćenja</a></li>
				</ul>
			</div>
		</div>

		<!-- Footer bottom -->
		<div class="footer-bottom">
			<span>&copy; <?php echo esc_html( date( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?>. Sva prava zadržana.</span>
			<div class="payment-icons">
				<span class="payment-icon">VISA</span>
				<span class="payment-icon">MC</span>
				<span class="payment-icon">PayPal</span>
				<span class="payment-icon">Maestro</span>
			</div>
			<a href="<?php echo esc_url( home_url( '/politika-privatnosti' ) ); ?>">Privatnost</a>
		</div>
	</footer>

</div><!-- #page -->

<?php wp_footer(); ?>
</body>
</html>
