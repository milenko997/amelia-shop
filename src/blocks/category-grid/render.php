<?php
/**
 * Server-side render for amelia/category-grid block.
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block content (empty for dynamic blocks).
 * @var WP_Block $block      Block instance.
 */

if ( ! class_exists( 'WooCommerce' ) ) {
	return;
}

$count      = absint( $attributes['count']   ?? 6 );
$columns    = absint( $attributes['columns'] ?? 3 );
$orderby    = sanitize_key( $attributes['orderby'] ?? 'name' );
$hide_empty = (bool) ( $attributes['hideEmpty'] ?? false );

$categories = get_terms( [
	'taxonomy'   => 'product_cat',
	'hide_empty' => $hide_empty,
	'exclude'    => [ get_option( 'default_product_cat' ) ],
	'orderby'    => $orderby,
	'number'     => $count,
] );

if ( empty( $categories ) || is_wp_error( $categories ) ) {
	return;
}

$cat_icons = [
	'donji-ves'    => '🩱',
	'donje-rublje' => '🩱',
	'gace'         => '🩲',
	'brushalteri'  => '👙',
	'carape'       => '🧦',
	'pidzame'      => '🌙',
];

echo '<section ' . get_block_wrapper_attributes( [ 'class' => 'category-grid-section' ] ) . '>';
echo '<div class="categories-grid container" style="--columns:' . $columns . '">';

foreach ( $categories as $cat ) {
	$icon      = $cat_icons[ sanitize_title( $cat->name ) ] ?? '✨';
	$thumb_id  = get_term_meta( $cat->term_id, 'thumbnail_id', true );
	$thumb_url = $thumb_id ? wp_get_attachment_image_url( $thumb_id, 'amelia-category' ) : '';
	$url       = get_term_link( $cat );

	echo '<a href="' . esc_url( $url ) . '" class="category-card">';
	if ( $thumb_url ) {
		echo '<img src="' . esc_url( $thumb_url ) . '" alt="' . esc_attr( $cat->name ) . '" loading="lazy">';
	}
	echo '<div class="category-card-overlay">';
	echo '<span class="cat-icon">' . $icon . '</span>';
	echo '<h3>' . esc_html( $cat->name ) . '</h3>';
	echo '<span>' . esc_html( $cat->count ) . ' proizvoda</span>';
	echo '</div>';
	echo '</a>';
}

echo '</div>';
echo '</section>';
