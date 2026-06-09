<?php
defined( 'ABSPATH' ) || exit;

$title = $attributes['title'] ?? 'Često postavljana pitanja';
$items = $attributes['items'] ?? [];
$items = array_filter( $items, fn( $item ) => ! empty( $item['question'] ) );

if ( empty( $items ) ) {
	return;
}
?>
<section <?php echo get_block_wrapper_attributes( [ 'class' => 'faq-block' ] ); ?>>
	<div class="container">
		<?php if ( $title ) : ?>
			<h2 class="faq-block__title"><?php echo wp_kses_post( $title ); ?></h2>
		<?php endif; ?>
		<div class="faq-block__list">
			<?php foreach ( array_values( $items ) as $i => $item ) : ?>
				<div class="faq-block__item" id="faq-item-<?php echo $i; ?>">
					<button
						class="faq-block__question"
						aria-expanded="false"
						aria-controls="faq-answer-<?php echo $i; ?>"
					>
						<span><?php echo esc_html( $item['question'] ); ?></span>
						<svg class="faq-block__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
							<polyline points="6 9 12 15 18 9"/>
						</svg>
					</button>
					<div class="faq-block__answer" id="faq-answer-<?php echo $i; ?>">
						<div class="faq-block__answer-inner">
							<?php echo wp_kses_post( nl2br( $item['answer'] ) ); ?>
						</div>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
