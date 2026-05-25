<?php get_header(); ?>

<main id="main" class="site-main">
	<div class="container" style="padding:3rem 1.5rem;">
		<h1 style="margin-bottom:2rem;">
			Rezultati pretrage za: <em><?php echo esc_html( get_search_query() ); ?></em>
		</h1>
		<?php if ( have_posts() ) : ?>
			<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:2rem;">
				<?php while ( have_posts() ) : the_post(); ?>
				<article <?php post_class( 'search-result' ); ?> style="background:var(--color-white);border-radius:var(--radius-lg);padding:1.5rem;box-shadow:var(--shadow-sm);">
					<h2 style="font-size:1.1rem;margin-bottom:.5rem;"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
					<p style="font-size:.875rem;color:var(--color-text-light);"><?php the_excerpt(); ?></p>
				</article>
				<?php endwhile; ?>
			</div>
			<?php the_posts_pagination(); ?>
		<?php else : ?>
			<p>Nema rezultata. Pokušajte drugačiji termin ili pregledajte našu prodavnicu.</p>
			<?php get_search_form(); ?>
		<?php endif; ?>
	</div>
</main>

<?php get_footer(); ?>
