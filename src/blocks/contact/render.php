<?php
defined( 'ABSPATH' ) || exit;

$title         = $attributes['title']          ?? 'Kontaktirajte nas';
$working_hours = $attributes['workingHours']   ?? '';
$contact_info  = $attributes['contactInfo']    ?? '';
$map_embed     = $attributes['mapEmbed']       ?? '';
$reversed      = ! empty( $attributes['reversed'] );
$title_size    = $attributes['titleFontSize']   ?? '2.5rem';
$content_size  = $attributes['contentFontSize'] ?? '1rem';
$title_align   = $attributes['titleAlign']     ?? 'left';
$content_align = $attributes['contentAlign']   ?? 'left';

// Extract src from a pasted <iframe> tag, or use the value as a raw URL.
$map_src = '';
if ( $map_embed ) {
	if ( preg_match( '/src=["\']([^"\']+)["\']/', $map_embed, $m ) ) {
		$map_src = $m[1];
	} else {
		$map_src = $map_embed;
	}
}

$wrapper_class = 'contact-section';
if ( ! $reversed ) {
	$wrapper_class .= ' is-reversed';
}

$wrapper_attrs = get_block_wrapper_attributes( [ 'class' => $wrapper_class ] );
?>
<section <?php echo $wrapper_attrs; ?>>

	<div class="contact-map-col">
		<?php if ( $map_src ) : ?>
			<iframe
				src="<?php echo esc_url( $map_src ); ?>"
				width="100%"
				height="100%"
				style="border:0;display:block;"
				allowfullscreen=""
				loading="lazy"
				referrerpolicy="no-referrer-when-downgrade"
				title="<?php esc_attr_e( 'Google Maps', 'amelia-shop' ); ?>"
			></iframe>
		<?php else : ?>
			<div class="contact-map-placeholder"></div>
		<?php endif; ?>
	</div>

	<div class="contact-text-col">
		<div class="contact-text">
			<h2 class="contact-title" style="font-size:<?php echo esc_attr( $title_size ); ?>;text-align:<?php echo esc_attr( $title_align ); ?>">
				<?php echo wp_kses_post( $title ); ?>
			</h2>

			<?php if ( $working_hours ) : ?>
				<div class="contact-detail-group">
					<span class="contact-group-label"><?php esc_html_e( 'Radno vreme', 'amelia-shop' ); ?></span>
					<div class="contact-hours" style="font-size:<?php echo esc_attr( $content_size ); ?>;text-align:<?php echo esc_attr( $content_align ); ?>">
						<?php echo wp_kses_post( $working_hours ); ?>
					</div>
				</div>
			<?php endif; ?>

			<?php if ( $contact_info ) : ?>
				<div class="contact-detail-group">
					<span class="contact-group-label"><?php esc_html_e( 'Kontakt', 'amelia-shop' ); ?></span>
					<div class="contact-info" style="font-size:<?php echo esc_attr( $content_size ); ?>;text-align:<?php echo esc_attr( $content_align ); ?>">
						<?php echo wp_kses_post( $contact_info ); ?>
					</div>
				</div>
			<?php endif; ?>
		</div>
	</div>

</section>
