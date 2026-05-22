<?php
defined( 'ABSPATH' ) || exit;

if ( ! $notices ) return;
?>

<div class="woocommerce-error" role="alert">
	<ul>
		<?php foreach ( $notices as $notice ) : ?>
		<li><?php echo wp_kses_post( $notice['notice'] ); ?></li>
		<?php endforeach; ?>
	</ul>
</div>
