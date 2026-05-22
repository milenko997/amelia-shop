<?php
defined( 'ABSPATH' ) || exit;
get_header( 'shop' );
?>

<div class="container" style="padding:2rem 1.5rem 5rem;">
	<?php woocommerce_breadcrumb(); ?>

	<?php while ( have_posts() ) : the_post(); ?>
		<?php wc_get_template_part( 'content', 'single-product' ); ?>
	<?php endwhile; ?>
</div>

<?php get_footer( 'shop' ); ?>
