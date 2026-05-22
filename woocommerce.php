<?php
get_header();

$is_shop     = is_shop();
$is_archive  = is_product_category() || is_product_tag() || is_product_taxonomy();
$show_sidebar = is_active_sidebar( 'shop-sidebar' ) && ( $is_shop || $is_archive );
?>

<?php if ( $show_sidebar ) : ?>
<div class="shop-layout">
	<aside class="shop-sidebar">
		<?php dynamic_sidebar( 'shop-sidebar' ); ?>
	</aside>
	<div class="shop-content">
		<?php woocommerce_content(); ?>
	</div>
</div>
<?php else : ?>
<div class="container" style="margin-top:2rem;margin-bottom:4rem;">
	<?php woocommerce_content(); ?>
</div>
<?php endif; ?>

<?php get_footer(); ?>
