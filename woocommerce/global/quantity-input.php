<?php
defined( 'ABSPATH' ) || exit;

$label = ! empty( $args['product_name'] )
	? sprintf( esc_html__( '%s quantity', 'woocommerce' ), wp_strip_all_tags( $args['product_name'] ) )
	: esc_html__( 'Quantity', 'woocommerce' );
?>
<div class="quantity qty-wrap">
	<?php do_action( 'woocommerce_before_quantity_input_field' ); ?>
	<label class="screen-reader-text" for="<?php echo esc_attr( $input_id ); ?>"><?php echo esc_attr( $label ); ?></label>
	<button type="button" class="qty-btn qty-minus" aria-label="<?php esc_attr_e( 'Decrease quantity', 'woocommerce' ); ?>">−</button>
	<input
		type="<?php echo esc_attr( $type ); ?>"
		<?php echo $readonly ? 'readonly="readonly"' : ''; ?>
		id="<?php echo esc_attr( $input_id ); ?>"
		class="<?php echo esc_attr( join( ' ', (array) $classes ) ); ?>"
		name="<?php echo esc_attr( $input_name ); ?>"
		value="<?php echo esc_attr( $input_value ); ?>"
		aria-label="<?php esc_attr_e( 'Product quantity', 'woocommerce' ); ?>"
		<?php if ( in_array( $type, array( 'text', 'search', 'tel', 'url', 'email', 'password' ), true ) ) : ?>
			size="4"
		<?php endif; ?>
		min="<?php echo esc_attr( $min_value ); ?>"
		<?php if ( 0 < $max_value ) : ?>
			max="<?php echo esc_attr( $max_value ); ?>"
		<?php endif; ?>
		<?php if ( ! $readonly ) : ?>
			step="<?php echo esc_attr( $step ); ?>"
			placeholder="<?php echo esc_attr( $placeholder ); ?>"
			inputmode="<?php echo esc_attr( $inputmode ); ?>"
			autocomplete="<?php echo esc_attr( isset( $autocomplete ) ? $autocomplete : 'on' ); ?>"
		<?php endif; ?>
	/>
	<button type="button" class="qty-btn qty-plus" aria-label="<?php esc_attr_e( 'Increase quantity', 'woocommerce' ); ?>">+</button>
	<?php do_action( 'woocommerce_after_quantity_input_field' ); ?>
</div>
