<?php
/**
 * Server-side render for amelia/products block.
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block content.
 * @var WP_Block $block      Block instance.
 */

if ( ! class_exists( 'WooCommerce' ) ) {
	return;
}

$selected = array_map( 'absint', $attributes['selectedProducts'] ?? [] );
$orderby  = sanitize_key( $attributes['orderby'] ?? 'date' );
$order    = in_array( $attributes['order'] ?? 'DESC', [ 'ASC', 'DESC' ], true )
	? $attributes['order']
	: 'DESC';

$query_args = [
	'post_type'      => 'product',
	'post_status'    => 'publish',
	'posts_per_page' => -1,
];

if ( ! empty( $selected ) ) {
	$query_args['post__in'] = $selected;
	$query_args['orderby']  = 'post__in';
} else {
	$query_args['posts_per_page'] = 12;
	$meta_query_map = [
		'price'      => [ 'key' => '_price', 'type' => 'NUMERIC' ],
		'popularity' => [ 'key' => 'total_sales', 'type' => 'NUMERIC' ],
		'rating'     => [ 'key' => '_wc_average_rating', 'type' => 'DECIMAL' ],
	];
	if ( isset( $meta_query_map[ $orderby ] ) ) {
		$query_args['meta_key'] = $meta_query_map[ $orderby ]['key'];
		$query_args['orderby']  = 'meta_value_num';
	} else {
		$query_args['orderby'] = $orderby;
	}
	$query_args['order'] = $order;
}

$loop = new WP_Query( $query_args );

if ( ! $loop->have_posts() ) {
	return;
}

$products   = $loop->posts;
$count      = count( $products );
$use_slider = $count > 5;
$grid_cols  = min( $count, 5 );

echo '<section ' . get_block_wrapper_attributes( [ 'class' => 'products-block-section' ] ) . '>';
echo '<div class="container">';

if ( $use_slider ) {
	echo '<div class="products-swiper-wrap">';
	echo '<div class="swiper products-swiper">';
	echo '<div class="swiper-wrapper">';
} else {
	echo '<ul class="products-grid woocommerce-loop" style="--grid-cols:' . $grid_cols . '">';
}

foreach ( $products as $post ) {
	$product = wc_get_product( $post->ID );
	if ( ! $product ) {
		continue;
	}

	$img_id  = $product->get_image_id();
	$img_url = $img_id
		? wp_get_attachment_image_url( $img_id, 'amelia-product' )
		: wc_placeholder_img_src( 'amelia-product' );
	$url     = get_permalink( $product->get_id() );
	$is_new  = strtotime( $product->get_date_created() ) > strtotime( '-30 days' );

	$tag  = $use_slider ? 'div' : 'li';
	$card = "<{$tag} class=\"product\">";
	$card .= '<div class="product-image-wrap">';
	$card .= '<a href="' . esc_url( $url ) . '">';
	$card .= '<img src="' . esc_url( $img_url ) . '" alt="' . esc_attr( $product->get_name() ) . '" loading="lazy">';
	$card .= '</a>';
	$card .= '<div class="product-badges">';
	if ( $product->is_on_sale() ) $card .= '<span class="badge badge-sale">Rasprodaja</span>';
	if ( $is_new )                 $card .= '<span class="badge badge-new">Novo</span>';
	$card .= '</div>';
	$card .= '</div>';
	$card .= '<div class="product-info">';
	$card .= '<h3 class="woocommerce-loop-product__title"><a href="' . esc_url( $url ) . '">' . esc_html( $product->get_name() ) . '</a></h3>';
	$card .= '<span class="price">' . wp_kses_post( $product->get_price_html() ) . '</span>';
	$card .= '<a href="' . esc_url( $product->add_to_cart_url() ) . '" class="button add_to_cart_button ajax_add_to_cart product_type_' . esc_attr( $product->get_type() ) . '" data-product_id="' . esc_attr( $product->get_id() ) . '" data-product_sku="' . esc_attr( $product->get_sku() ) . '" rel="nofollow">' . esc_html( $product->add_to_cart_text() ) . '</a>';
	$card .= '</div>';
	$card .= "</{$tag}>";

	if ( $use_slider ) {
		echo '<div class="swiper-slide">' . $card . '</div>';
	} else {
		echo $card;
	}
}

if ( $use_slider ) {
	echo '</div>'; // .swiper-wrapper
	echo '</div>'; // .products-swiper
	echo '<button class="swiper-button-prev" aria-label="Prethodni">';
	echo '<svg viewBox="0 0 24 24" fill="currentColor" stroke="none"><path d="M15 6L8 12L15 18Z"/></svg>';
	echo '</button>';
	echo '<button class="swiper-button-next" aria-label="Sledeći">';
	echo '<svg viewBox="0 0 24 24" fill="currentColor" stroke="none"><path d="M9 6L16 12L9 18Z"/></svg>';
	echo '</button>';
	echo '<div class="swiper-pagination"></div>';
	echo '</div>'; // .products-swiper-wrap
} else {
	echo '</ul>';
}

echo '</div>';
echo '</section>';
