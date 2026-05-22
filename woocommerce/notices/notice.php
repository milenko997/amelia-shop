<?php
defined( 'ABSPATH' ) || exit;

if ( ! $notices ) return;
?>

<?php foreach ( $notices as $notice ) : ?>
<div class="woocommerce-info" role="status">
	<span>&#8505;</span>
	<?php echo wp_kses_post( $notice['notice'] ); ?>
</div>
<?php endforeach; ?>
