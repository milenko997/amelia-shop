<?php
defined( 'ABSPATH' ) || exit;
get_header( 'shop' );

$term = get_queried_object();
?>

<div class="container" style="padding:2rem 1.5rem 4rem;">

	<?php woocommerce_breadcrumb(); ?>

	<?php if ( $term && isset( $term->name ) ) : ?>
	<div style="margin-bottom:2rem;">
		<h1><?php echo esc_html( $term->name ); ?></h1>
		<?php if ( $term->description ) : ?>
		<p style="color:var(--color-text-light);max-width:600px;"><?php echo esc_html( $term->description ); ?></p>
		<?php endif; ?>
	</div>
	<?php else : ?>
	<div style="margin-bottom:2rem;">
		<h1><?php esc_html_e( 'Our Shop', 'amelia-shop' ); ?></h1>
		<p style="color:var(--color-text-light);"><?php esc_html_e( 'Discover our full collection of lingerie, underwear, socks and pajamas.', 'amelia-shop' ); ?></p>
	</div>
	<?php endif; ?>

	<?php do_action( 'woocommerce_before_shop_loop' ); ?>

	<?php if ( woocommerce_product_loop() ) : ?>
		<?php woocommerce_product_loop_start(); ?>
		<?php while ( have_posts() ) : the_post(); ?>
			<?php wc_get_template_part( 'content', 'product' ); ?>
		<?php endwhile; ?>
		<?php woocommerce_product_loop_end(); ?>
		<?php do_action( 'woocommerce_after_shop_loop' ); ?>
	<?php else : ?>
		<?php do_action( 'woocommerce_no_products_found' ); ?>
	<?php endif; ?>

</div>

<?php get_footer( 'shop' ); ?>
