<?php
defined( 'ABSPATH' ) || exit;

$title         = $attributes['title']          ?? 'Naša priča';
$body          = $attributes['content']        ?? '';
$media_url     = $attributes['mediaUrl']       ?? '';
$media_alt     = $attributes['mediaAlt']       ?? '';
$reversed      = ! empty( $attributes['reversed'] );
$title_size    = $attributes['titleFontSize']   ?? '2.5rem';
$content_size  = $attributes['contentFontSize'] ?? '1rem';
$title_align   = $attributes['titleAlign']     ?? 'left';
$content_align = $attributes['contentAlign']   ?? 'left';

$wrapper_class = 'about-us-section';
if ( $reversed ) {
	$wrapper_class .= ' is-reversed';
}

$wrapper_attrs = get_block_wrapper_attributes( [ 'class' => $wrapper_class ] );
?>
<section <?php echo $wrapper_attrs; ?>>
	<div class="about-us-text-col">
		<div class="about-us-text">
			<h2 class="about-us-title" style="font-size:<?php echo esc_attr( $title_size ); ?>;text-align:<?php echo esc_attr( $title_align ); ?>">
				<?php echo wp_kses_post( $title ); ?>
			</h2>
			<div class="about-us-content" style="font-size:<?php echo esc_attr( $content_size ); ?>;text-align:<?php echo esc_attr( $content_align ); ?>">
				<?php echo wp_kses_post( $body ); ?>
			</div>
		</div>
	</div>

	<div class="about-us-image-col">
		<?php if ( $media_url ) : ?>
			<img src="<?php echo esc_url( $media_url ); ?>" alt="<?php echo esc_attr( $media_alt ); ?>" />
		<?php else : ?>
			<div class="about-us-image-placeholder"></div>
		<?php endif; ?>
	</div>
</section>
