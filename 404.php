<?php get_header(); ?>

<main id="main" class="site-main">
	<div class="container" style="text-align:center;padding:6rem 1.5rem;">
		<div style="font-size:6rem;margin-bottom:1rem;">🩱</div>
		<h1 style="font-size:3rem;margin-bottom:1rem;">404</h1>
		<h2 style="margin-bottom:1rem;">Stranica nije pronađena</h2>
		<p style="color:var(--color-text-light);margin-bottom:2rem;">Stranica koju tražite izgleda da je nestala — baš kao izgubljena čarapa!</p>
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="btn">Nazad na početnu</a>
	</div>
</main>

<?php get_footer(); ?>
