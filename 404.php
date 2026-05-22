<?php get_header(); ?>

<main id="main" class="site-main">
	<div class="container" style="text-align:center;padding:6rem 1.5rem;">
		<div style="font-size:6rem;margin-bottom:1rem;">🩱</div>
		<h1 style="font-size:3rem;margin-bottom:1rem;">404</h1>
		<h2 style="margin-bottom:1rem;"><?php esc_html_e( 'Page Not Found', 'amelia-shop' ); ?></h2>
		<p style="color:var(--color-text-light);margin-bottom:2rem;"><?php esc_html_e( 'The page you are looking for seems to have gone missing — just like a missing sock!', 'amelia-shop' ); ?></p>
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="btn"><?php esc_html_e( 'Back to Home', 'amelia-shop' ); ?></a>
	</div>
</main>

<?php get_footer(); ?>
