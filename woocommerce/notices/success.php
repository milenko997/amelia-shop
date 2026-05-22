<?php
defined( 'ABSPATH' ) || exit;

if ( ! $notices ) return;
?>

<?php foreach ( $notices as $notice ) : ?>
<div class="woocommerce-message" role="alert">
	<span>&#10003;</span>
	<?php echo wp_kses_post( $notice['notice'] ); ?>
</div>
<?php endforeach; ?>
