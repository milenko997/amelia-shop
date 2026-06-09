<?php
if ( ! class_exists( 'WooCommerce' ) ) {
	return;
}

$ajax_url = admin_url( 'admin-ajax.php' );
$nonce    = wp_create_nonce( 'amelia_favorites' );
?>
<section <?php echo get_block_wrapper_attributes( [ 'class' => 'favorites-block' ] ); ?>>
	<div class="container">
		<div class="favorites-block__grid"
			data-ajax-url="<?php echo esc_attr( $ajax_url ); ?>"
			data-nonce="<?php echo esc_attr( $nonce ); ?>"
		></div>
		<div class="favorites-block__empty" hidden>
			<div class="favorites-block__empty-icon">
				<svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
					<path d="M24 41S8 31 8 18a8 8 0 0116-2 8 8 0 0116 2c0 13-16 23-16 23z"/>
				</svg>
			</div>
			<h2 class="favorites-block__empty-title">Nema sačuvanih proizvoda</h2>
			<p class="favorites-block__empty-text">Kliknite na srce pored proizvoda da biste ga sačuvali ovde.</p>
			<a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="btn btn-primary">Pregledajte proizvode</a>
		</div>
	</div>
</section>
