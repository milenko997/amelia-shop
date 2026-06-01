<?php
defined( 'ABSPATH' ) || exit;

global $product;

if ( post_password_required() ) {
	echo get_the_password_form(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	return;
}

do_action( 'woocommerce_before_single_product' );
?>

<article id="product-<?php the_ID(); ?>" <?php wc_product_class( 'product-single', $product ); ?>>

	<div class="product-hero">
		<div class="container">
			<?php woocommerce_breadcrumb(); ?>

			<div class="product-hero__grid">
				<div class="product-hero__gallery">
					<?php do_action( 'woocommerce_before_single_product_summary' ); ?>
				</div>

				<div class="summary entry-summary">
					<?php do_action( 'woocommerce_single_product_summary' ); ?>
				</div>
			</div>
		</div>
	</div>

	<?php do_action( 'woocommerce_after_single_product_summary' ); ?>

	<div class="product-tabs-wrap">
		<div class="container">
			<?php woocommerce_output_product_data_tabs(); ?>
		</div>
	</div>

	<?php
	ob_start();
	woocommerce_upsell_display();
	$upsells_html = ob_get_clean();
	if ( $upsells_html ) :
	?>
	<div class="product-upsells-wrap">
		<div class="container">
			<?php echo $upsells_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</div>
	</div>
	<?php endif; ?>

	<?php
	ob_start();
	woocommerce_output_related_products();
	$related_html = ob_get_clean();
	if ( $related_html ) :
	?>
	<div class="product-related-wrap">
		<div class="container">
			<?php echo $related_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</div>
	</div>
	<?php endif; ?>

</article>

<?php do_action( 'woocommerce_after_single_product' ); ?>
