<?php
/**
 * Variation swatches: buttons for sizes and color circles instead of <select> menus.
 *
 * The original <select> stays in the DOM (visually hidden) so WooCommerce's
 * variation script keeps working; src/js/modules/swatches.js syncs the two.
 */
defined( 'ABSPATH' ) || exit;

/**
 * Color names (lowercase, without diacritics) => CSS color.
 * Matching is by substring (spaces ignored) in this order, so "tamno plava" matches "tamnoplava".
 */
function amelia_swatch_colors() {
	return apply_filters( 'amelia_swatch_colors', [
		'svetloplava' => '#93c5fd',
		'tamnoplava'  => '#1e3a8a',
		'teget'       => '#1e2a4a',
		'plava'       => '#2563eb',
		'crna'        => '#1a1a1a',
		'bela'        => '#ffffff',
		'siva'        => '#9ca3af',
		'crvena'      => '#c0392b',
		'bordo'       => '#7f1d1d',
		'zelena'      => '#16a34a',
		'maslinasta'  => '#708238',
		'mint'        => '#a7f3d0',
		'tirkizna'    => '#14b8a6',
		'zuta'        => '#facc15',
		'narandzasta' => '#f97316',
		'koralna'     => '#ff7f50',
		'roze'        => '#f9a8d4',
		'ruzicasta'   => '#f9a8d4',
		'pink'        => '#ec4899',
		'puder'       => '#f1d3d3',
		'ljubicasta'  => '#8b5cf6',
		'lila'        => '#c4b5fd',
		'braon'       => '#78350f',
		'smeda'       => '#78350f', // remove_accents() turns "đ" into "d"
		'bez'         => '#e8d5b7',
		'krem'        => '#f5ecd7',
		'nude'        => '#e3bc9a',
		'sampanj'     => '#f7e7ce',
		'zlatna'      => '#d4af37',
		'srebrna'     => '#c0c0c0',
	] );
}

function amelia_is_color_attribute( $attribute ) {
	$name = sanitize_title( wc_attribute_label( $attribute ) . ' ' . $attribute );
	return (bool) preg_match( '/boj|color|colour/', $name );
}

/**
 * CSS color for an option label, or '' when the name is not a known color.
 */
function amelia_swatch_color( $label ) {
	$name = strtolower( remove_accents( $label ) );
	foreach ( amelia_swatch_colors() as $key => $color ) {
		if ( str_contains( str_replace( ' ', '', $name ), $key ) ) {
			return $color;
		}
	}
	return '';
}

add_filter( 'woocommerce_dropdown_variation_attribute_options_html', function ( $html, $args ) {
	$options   = $args['options'] ?? [];
	$attribute = $args['attribute'] ?? '';
	$product   = $args['product'] ?? null;

	if ( empty( $options ) || ! $product instanceof WC_Product ) {
		return $html;
	}

	// Taxonomy attributes store slugs; show the term names.
	$labels = [];
	if ( taxonomy_exists( $attribute ) ) {
		foreach ( wc_get_product_terms( $product->get_id(), $attribute, [ 'fields' => 'all' ] ) as $term ) {
			if ( in_array( $term->slug, $options, true ) ) {
				$labels[ $term->slug ] = $term->name;
			}
		}
	} else {
		foreach ( $options as $option ) {
			$labels[ $option ] = $option;
		}
	}

	$is_color = amelia_is_color_attribute( $attribute );
	$label    = wc_attribute_label( $attribute, $product );

	ob_start();
	?>
	<div class="variation-swatches<?php echo $is_color ? ' variation-swatches--color' : ''; ?>" role="radiogroup" aria-label="<?php echo esc_attr( $label ); ?>">
		<?php foreach ( $labels as $value => $text ) : ?>
			<?php $color = $is_color ? amelia_swatch_color( $text ) : ''; ?>
			<button
				type="button"
				class="swatch<?php echo $color ? ' swatch--color' : ''; ?>"
				role="radio"
				aria-checked="false"
				data-value="<?php echo esc_attr( $value ); ?>"
				title="<?php echo esc_attr( $text ); ?>"
			>
				<?php if ( $color ) : ?>
					<span class="swatch__color" style="--swatch-color: <?php echo esc_attr( $color ); ?>" aria-hidden="true"></span>
					<span class="screen-reader-text"><?php echo esc_html( $text ); ?></span>
				<?php else : ?>
					<?php echo esc_html( $text ); ?>
				<?php endif; ?>
			</button>
		<?php endforeach; ?>
	</div>
	<?php
	$swatches = ob_get_clean();

	return '<div class="swatches-field">' . str_replace( '<select ', '<select data-swatches tabindex="-1" aria-hidden="true" ', $html ) . $swatches . '</div>';
}, 10, 2 );
