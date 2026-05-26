<?php get_header(); ?>

<main id="main" class="site-main">
	<?php while ( have_posts() ) : the_post(); ?>
		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			<div class="page-header container" style="max-width:900px;padding:3rem 1.5rem 1.5rem;">
				<h1 class="page-title"><?php the_title(); ?></h1>
			</div>
			<div class="page-content">
				<?php the_content(); ?>
			</div>
		</article>
	<?php endwhile; ?>
</main>

<?php get_footer(); ?>
