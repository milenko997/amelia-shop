	<!-- Site footer -->
	<footer id="colophon" class="site-footer">
		<div class="footer-grid">

			<!-- Brand -->
			<div class="footer-brand">
				<span class="logo-text"><?php bloginfo( 'name' ); ?></span>
				<p>Udobni, elegantni i kvalitetni svakodnevni artikli — donji veš, čarape i pidžame izrađeni s pažnjom.</p>
				<div class="social-links">
					<a href="#" class="social-link" aria-label="Instagram">
						<svg viewBox="0 0 24 24"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1112.63 8 4 4 0 0116 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>
					</a>
					<a href="#" class="social-link" aria-label="Facebook">
						<svg viewBox="0 0 24 24"><path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"/></svg>
					</a>
					<a href="#" class="social-link" aria-label="Pinterest">
						<svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12c0 4.24 2.65 7.86 6.39 9.29-.09-.78-.17-1.98.03-2.83.19-.77 1.27-5.38 1.27-5.38s-.32-.65-.32-1.61c0-1.51.88-2.64 1.97-2.64.93 0 1.38.7 1.38 1.53 0 .93-.6 2.33-.9 3.63-.26 1.08.53 1.96 1.58 1.96 1.9 0 3.17-2.44 3.17-5.33 0-2.2-1.49-3.74-3.62-3.74-2.47 0-3.92 1.85-3.92 3.76 0 .74.28 1.54.64 1.97a.26.26 0 01.06.24c-.06.27-.21.85-.24.97-.04.15-.13.18-.29.11-1.08-.5-1.75-2.1-1.75-3.37 0-2.74 1.99-5.26 5.74-5.26 3.01 0 5.35 2.15 5.35 5.02 0 2.99-1.89 5.4-4.5 5.4-.88 0-1.71-.46-1.99-1l-.54 2.03c-.2.75-.73 1.7-1.09 2.28.82.25 1.69.39 2.59.39 5.52 0 10-4.48 10-10S17.52 2 12 2z"/></svg>
					</a>
					<a href="#" class="social-link" aria-label="TikTok">
						<svg viewBox="0 0 24 24"><path d="M9 12a4 4 0 104 4V4a5 5 0 005 5"/></svg>
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
					<li><a href="#">Vodič za veličine</a></li>
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
					<li><a href="#">Održivost</a></li>
					<li><a href="#">Politika privatnosti</a></li>
					<li><a href="#">Uslovi korišćenja</a></li>
					<li><a href="#">Politika kolačića</a></li>
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
