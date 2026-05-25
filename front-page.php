<?php
/**
 * Front page template.
 *
 * Used when Settings → Reading → "A static page" is configured.
 * The homepage content is built entirely with Gutenberg blocks
 * placed in the WordPress editor (hero, category-grid, products, newsletter…).
 */

get_header();
?>

<main id="main" class="site-main">
	<?php while ( have_posts() ) : the_post(); ?>
		<?php the_content(); ?>
	<?php endwhile; ?>
</main>

<?php get_footer(); ?>
