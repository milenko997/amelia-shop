<?php get_header(); ?>

<main id="main" class="site-main">
	<div class="container" style="max-width:900px;padding:3rem 1.5rem;">
		<?php while ( have_posts() ) : the_post(); ?>
			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
				<h1 class="page-title" style="margin-bottom:1.5rem;"><?php the_title(); ?></h1>
				<div class="page-content">
					<?php the_content(); ?>
				</div>
			</article>
		<?php endwhile; ?>
	</div>
</main>

<?php get_footer(); ?>
