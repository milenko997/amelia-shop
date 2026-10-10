<?php
get_header();
$is_wc_page = function_exists( 'is_cart' ) && ( is_cart() || is_checkout() || is_account_page() );
?>

<?php if ( $is_wc_page ) : ?>
	<?php while ( have_posts() ) : the_post(); ?>
		<?php the_content(); ?>
	<?php endwhile; ?>
<?php else : ?>
	<main id="main" class="site-main">
		<?php while ( have_posts() ) : the_post(); ?>
			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
				<?php // The visible page title was removed by design; keep one h1 for search engines. ?>
				<?php if ( ! preg_match( '/<h1[\s>]/i', get_the_content() ) ) : ?>
					<h1 class="screen-reader-text"><?php the_title(); ?></h1>
				<?php endif; ?>
				<div class="page-content">
					<?php the_content(); ?>
				</div>
			</article>
		<?php endwhile; ?>
	</main>
<?php endif; ?>

<?php get_footer(); ?>
