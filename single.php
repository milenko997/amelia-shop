<?php get_header(); ?>

<main id="main" class="site-main">
	<div class="container" style="max-width:900px;padding:3rem 1.5rem;">
		<?php while ( have_posts() ) : the_post(); ?>
			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
				<?php if ( has_post_thumbnail() ) : ?>
				<div style="border-radius:var(--radius-lg);overflow:hidden;margin-bottom:2rem;max-height:500px;">
					<?php the_post_thumbnail( 'large', [ 'style' => 'width:100%;height:500px;object-fit:cover;' ] ); ?>
				</div>
				<?php endif; ?>
				<h1 style="margin-bottom:1rem;"><?php the_title(); ?></h1>
				<div style="font-size:.875rem;color:var(--color-text-light);margin-bottom:2rem;">
					<?php echo esc_html( get_the_date() ); ?> &middot; <?php the_author(); ?>
				</div>
				<div class="entry-content" style="line-height:1.9;font-size:1.05rem;">
					<?php the_content(); ?>
				</div>
			</article>
		<?php endwhile; ?>
	</div>
</main>

<?php get_footer(); ?>
