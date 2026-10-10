<?php
defined( 'ABSPATH' ) || exit;

define( 'AMELIA_VERSION', '1.0.0' );
define( 'AMELIA_DIR',     get_template_directory() );
define( 'AMELIA_URI',     get_template_directory_uri() );
define( 'AMELIA_BUILD',   AMELIA_DIR . '/build' );
define( 'AMELIA_BUILD_URI', AMELIA_URI . '/build' );

require AMELIA_DIR . '/inc/i18n.php';
require AMELIA_DIR . '/inc/seo.php';

/* ============================================================
   Theme Setup
   ============================================================ */
function amelia_setup() {
	load_theme_textdomain( 'amelia-shop', AMELIA_DIR . '/languages' );

	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', [ 'search-form', 'gallery', 'caption', 'style', 'script' ] );
	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'editor-styles' );

	// Page excerpt is used as the meta description.
	add_post_type_support( 'page', 'excerpt' );
	add_editor_style( 'https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400;1,600&family=Lato:wght@300;400;600;700&display=swap' );
	add_editor_style( 'build/main.css' );

	// WooCommerce
	add_theme_support( 'woocommerce', [
		'thumbnail_image_width'     => 600,
		'single_image_width'        => 900,
		'product_grid'              => [
			'default_rows'    => 4,
			'min_rows'        => 1,
			'default_columns' => 3,
			'min_columns'     => 2,
			'max_columns'     => 4,
		],
	] );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );

	register_nav_menus( [
		'primary' => 'Glavni meni',
	] );

	add_image_size( 'amelia-product',  600, 800, true );
	add_image_size( 'amelia-category', 400, 533, true );
}
add_action( 'after_setup_theme', 'amelia_setup' );

/* ============================================================
   Fallback menu — shown when no menu is assigned to 'primary'.
   Uses the first available custom menu if one exists.
   ============================================================ */
function amelia_fallback_menu( $args ) {
	$menus = wp_get_nav_menus();

	if ( ! empty( $menus ) ) {
		$new_args = array_merge( (array) $args, [
			'menu'        => $menus[0]->term_id,
			'fallback_cb' => false,
		] );
		wp_nav_menu( $new_args );
		return;
	}

	// No menu exists — show minimal navigation
	echo '<ul>';
	echo '<li><a href="' . esc_url( home_url( '/' ) ) . '">Početna</a></li>';
	if ( class_exists( 'WooCommerce' ) ) {
		echo '<li><a href="' . esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ) . '">Prodavnica</a></li>';
	}
	echo '</ul>';
}

/* ============================================================
   Footer helpers
   ============================================================ */

/**
 * Permalink of the first published page matching one of the slugs, or ''.
 * Footer links are only printed for pages that exist, so there are no dead "#" links.
 */
function amelia_page_url_by_slugs( array $slugs ) {
	foreach ( $slugs as $slug ) {
		$page = get_page_by_path( $slug );
		if ( $page && 'publish' === $page->post_status ) {
			return get_permalink( $page );
		}
	}
	return '';
}

function amelia_footer_categories() {
	$terms = get_terms( [
		'taxonomy'   => 'product_cat',
		'parent'     => 0,
		'hide_empty' => true,
		'exclude'    => [ (int) get_option( 'default_product_cat' ) ],
		'orderby'    => 'name',
		'number'     => 6,
	] );
	return is_wp_error( $terms ) ? [] : $terms;
}

function amelia_social_icon( $network ) {
	$paths = [
		'instagram' => '<rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1112.63 8 4 4 0 0116 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/>',
		'facebook'  => '<path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"/>',
		'tiktok'    => '<path d="M9 12a4 4 0 104 4V2a5 5 0 005 5"/>',
	];
	return isset( $paths[ $network ] ) ? '<svg viewBox="0 0 24 24" aria-hidden="true">' . $paths[ $network ] . '</svg>' : '';
}

/* ============================================================
   Content Width
   ============================================================ */
function amelia_content_width() {
	$GLOBALS['content_width'] = 1280;
}
add_action( 'after_setup_theme', 'amelia_content_width', 0 );

/* ============================================================
   Enqueue — compiled assets from build/
   ============================================================ */
function amelia_scripts() {
	// Google Fonts
	wp_enqueue_style(
		'amelia-fonts',
		'https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400;1,600&family=Lato:wght@300;400;600;700&display=swap',
		[],
		null
	);

	// Compiled theme CSS (from src/scss/main.scss via webpack)
	wp_enqueue_style(
		'amelia-theme',
		AMELIA_BUILD_URI . '/main.css',
		[ 'amelia-fonts' ],
		AMELIA_VERSION
	);

	// Compiled theme JS (from src/js/main.js via webpack)
	$asset_file = AMELIA_BUILD . '/main.asset.php';
	$asset      = file_exists( $asset_file ) ? require $asset_file : [ 'dependencies' => [], 'version' => AMELIA_VERSION ];

	wp_enqueue_script(
		'amelia-main',
		AMELIA_BUILD_URI . '/main.js',
		$asset['dependencies'],
		$asset['version'],
		true
	);

	wp_localize_script( 'amelia-main', 'ameliaData', [
		'ajaxUrl' => admin_url( 'admin-ajax.php' ),
	] );
}
add_action( 'wp_enqueue_scripts', 'amelia_scripts' );

/* ============================================================
   Block category
   ============================================================ */
function amelia_block_categories( $categories ) {
	return array_merge(
		[ [ 'slug' => 'amelia', 'title' => 'Amelia Shop', 'icon' => null ] ],
		$categories
	);
}
add_filter( 'block_categories_all', 'amelia_block_categories' );

/* ============================================================
   Register Gutenberg blocks
   ============================================================ */
function amelia_register_blocks() {
	$blocks = [ 'hero', 'category-grid', 'features-strip', 'newsletter', 'products', 'about-us', 'contact', 'favorites', 'faq' ];

	foreach ( $blocks as $block ) {
		$path = AMELIA_BUILD . '/blocks/' . $block;
		if ( file_exists( $path . '/block.json' ) ) {
			register_block_type( $path );
		}
	}
}
add_action( 'init', 'amelia_register_blocks' );

/* ============================================================
   WooCommerce — suppress "choose product options" notice on single product
   ============================================================ */
add_action( 'wp', function () {
	if ( ! is_product() || ! WC()->session ) {
		return;
	}
	$notices = WC()->session->get( 'wc_notices', [] );
	if ( empty( $notices['error'] ) ) {
		return;
	}
	$notices['error'] = array_values( array_filter( $notices['error'], function ( $notice ) {
		$text = is_array( $notice ) ? ( $notice['notice'] ?? '' ) : $notice;
		return strpos( $text, 'choose product options' ) === false;
	} ) );
	if ( empty( $notices['error'] ) ) {
		unset( $notices['error'] );
	}
	WC()->session->set( 'wc_notices', $notices );
} );

/* ============================================================
   WooCommerce — redirect My Account to shop
   ============================================================ */
add_action( 'template_redirect', function () {
	$page_id = get_option( 'woocommerce_myaccount_page_id' );
	if ( $page_id && is_page( $page_id ) ) {
		wp_safe_redirect( wc_get_page_permalink( 'shop' ) );
		exit;
	}
} );

/* ============================================================
   WooCommerce — remove default wrappers
   ============================================================ */
remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper',     10 );
remove_action( 'woocommerce_after_main_content',  'woocommerce_output_content_wrapper_end', 10 );
remove_action( 'woocommerce_sidebar',             'woocommerce_get_sidebar',                10 );
remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form',      10 );

function amelia_wc_before_content() {
	if ( is_cart() || is_checkout() || is_account_page() ) {
		echo '<main id="main" class="site-main">';
	} else {
		echo '<main id="main" class="site-main"><div class="container">';
	}
}
function amelia_wc_after_content() {
	if ( is_cart() || is_checkout() || is_account_page() ) {
		echo '</main>';
	} else {
		echo '</div></main>';
	}
}
add_action( 'woocommerce_before_main_content', 'amelia_wc_before_content', 10 );
add_action( 'woocommerce_after_main_content',  'amelia_wc_after_content',  10 );

/* ============================================================
   WooCommerce — product card image wrap + badges
   ============================================================ */
/**
 * Responsive product card image (srcset + width/height to avoid layout shift).
 */
function amelia_product_card_image( $product ) {
	$attrs = [ 'alt' => $product->get_name(), 'loading' => 'lazy' ];
	return $product->get_image_id()
		? wp_get_attachment_image( $product->get_image_id(), 'amelia-product', false, $attrs )
		: wc_placeholder_img( 'amelia-product', $attrs );
}

function amelia_is_new_product( $product ) {
	$created = $product->get_date_created();
	return $created && $created->getTimestamp() > strtotime( '-30 days' );
}

function amelia_product_image_wrap_open() {
	echo '<div class="product-image-wrap">';
}

function amelia_product_image_wrap_close() {
	$product = wc_get_product( get_the_ID() );
	if ( ! $product ) {
		echo '</div>';
		return;
	}

	echo '<div class="product-badges">';
	if ( $product->is_on_sale() ) {
		echo '<span class="badge badge-sale">Rasprodaja</span>';
	}
	if ( amelia_is_new_product( $product ) ) {
		echo '<span class="badge badge-new">Novo</span>';
	}
	echo '</div>';

	echo '<button class="product-wishlist" data-product-id="' . esc_attr( get_the_ID() ) . '" aria-label="Dodaj na listu želja">';
	echo '<svg viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/></svg>';
	echo '</button>';
	echo '</div>';
}

add_action( 'woocommerce_before_shop_loop_item_title', 'amelia_product_image_wrap_open',  5 );
add_action( 'woocommerce_before_shop_loop_item_title', 'amelia_product_image_wrap_close', 15 );
add_action( 'woocommerce_before_shop_loop_item_title', fn() => print( '<div class="product-info">' ), 20 );
add_action( 'woocommerce_after_shop_loop_item',        fn() => print( '</div>' ),                    20 );

/* ============================================================
   Cart count — AJAX
   ============================================================ */
function amelia_cart_count() {
	return WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
}

function amelia_ajax_cart_count() {
	wp_send_json_success( [ 'count' => amelia_cart_count() ] );
}
add_action( 'wp_ajax_amelia_cart_count',        'amelia_ajax_cart_count' );
add_action( 'wp_ajax_nopriv_amelia_cart_count', 'amelia_ajax_cart_count' );

function amelia_cart_count_html() {
	$count = amelia_cart_count();
	return '<span class="cart-count" id="cart-count"' . ( $count > 0 ? '' : ' style="display:none"' ) . '>' . esc_html( $count ) . '</span>';
}

// Refresh the header badge via WooCommerce fragments after AJAX add-to-cart.
add_filter( 'woocommerce_add_to_cart_fragments', function ( $fragments ) {
	$fragments['#cart-count'] = amelia_cart_count_html();
	return $fragments;
} );

function amelia_ajax_get_favorites() {
	check_ajax_referer( 'amelia_favorites', 'nonce' );

	$raw_ids = isset( $_POST['ids'] ) ? sanitize_text_field( wp_unslash( $_POST['ids'] ) ) : '';
	$ids     = array_filter( array_map( 'absint', explode( ',', $raw_ids ) ) );

	if ( empty( $ids ) ) {
		wp_send_json_error();
	}

	$loop = new WP_Query( [
		'post_type'      => 'product',
		'post_status'    => 'publish',
		'post__in'       => $ids,
		'orderby'        => 'post__in',
		'posts_per_page' => count( $ids ),
	] );

	if ( ! $loop->have_posts() ) {
		wp_send_json_error();
	}

	$html = '';
	foreach ( $loop->posts as $post ) {
		$product = wc_get_product( $post->ID );
		if ( ! $product ) continue;

		$url     = get_permalink( $product->get_id() );
		$is_new  = amelia_is_new_product( $product );

		$html .= '<li class="product">';
		$html .= '<div class="product-image-wrap">';
		$html .= '<a href="' . esc_url( $url ) . '">';
		$html .= amelia_product_card_image( $product );
		$html .= '</a>';
		$html .= '<div class="product-badges">';
		if ( $product->is_on_sale() ) $html .= '<span class="badge badge-sale">Rasprodaja</span>';
		if ( $is_new )                 $html .= '<span class="badge badge-new">Novo</span>';
		$html .= '</div>';
		$html .= '<button class="product-wishlist active" data-product-id="' . esc_attr( $product->get_id() ) . '" aria-label="Ukloni sa liste želja">';
		$html .= '<svg viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z" style="fill:var(--color-primary)"/></svg>';
		$html .= '</button>';
		$html .= '</div>';
		$html .= '<div class="product-info">';
		$html .= '<h3 class="woocommerce-loop-product__title"><a href="' . esc_url( $url ) . '">' . esc_html( $product->get_name() ) . '</a></h3>';
		$html .= '<span class="price">' . wp_kses_post( $product->get_price_html() ) . '</span>';
		$html .= '<a href="' . esc_url( $product->add_to_cart_url() ) . '" class="button add_to_cart_button ajax_add_to_cart product_type_' . esc_attr( $product->get_type() ) . '" data-product_id="' . esc_attr( $product->get_id() ) . '" data-product_sku="' . esc_attr( $product->get_sku() ) . '" rel="nofollow">' . esc_html( $product->add_to_cart_text() ) . '</a>';
		$html .= '</div>';
		$html .= '</li>';
	}

	$html = '<ul class="products-grid woocommerce-loop" style="--grid-cols:4">' . $html . '</ul>';

	wp_send_json_success( [ 'html' => $html ] );
}
add_action( 'wp_ajax_amelia_get_favorites',        'amelia_ajax_get_favorites' );
add_action( 'wp_ajax_nopriv_amelia_get_favorites', 'amelia_ajax_get_favorites' );

/* ============================================================
   Misc
   ============================================================ */
add_filter( 'excerpt_length', fn() => 20 );

function amelia_body_classes( $classes ) {
	if ( function_exists( 'is_woocommerce' ) && ( is_woocommerce() || is_cart() || is_checkout() || is_account_page() ) ) {
		$classes[] = 'woo-page';
	}
	return $classes;
}
add_filter( 'body_class', 'amelia_body_classes' );

/* ============================================================
   Shop — helper: product price range
   ============================================================ */
function amelia_get_price_range( $term_id = 0, $taxonomy = 'product_cat' ) {
	global $wpdb;

	if ( $term_id ) {
		$child_ids = get_term_children( (int) $term_id, $taxonomy );
		$all_ids   = array_merge( [ (int) $term_id ], is_array( $child_ids ) ? $child_ids : [] );
		$ph        = implode( ',', array_fill( 0, count( $all_ids ), '%d' ) );

		$row = $wpdb->get_row( $wpdb->prepare(
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			"SELECT MIN(CAST(pm.meta_value AS DECIMAL(10,2))) AS min_price,
			        MAX(CAST(pm.meta_value AS DECIMAL(10,2))) AS max_price
			 FROM {$wpdb->postmeta} pm
			 INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
			 INNER JOIN {$wpdb->term_relationships} tr ON tr.object_id = p.ID
			 INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
			 WHERE pm.meta_key = '_price'
			   AND pm.meta_value != ''
			   AND CAST(pm.meta_value AS DECIMAL(10,2)) > 0
			   AND p.post_status = 'publish'
			   AND tt.taxonomy = %s
			   AND tt.term_id IN ($ph)",
			$taxonomy,
			...$all_ids
		) );
	} else {
		$row = $wpdb->get_row(
			"SELECT MIN(CAST(meta_value AS DECIMAL(10,2))) AS min_price,
			        MAX(CAST(meta_value AS DECIMAL(10,2))) AS max_price
			 FROM {$wpdb->postmeta}
			 INNER JOIN {$wpdb->posts} ON post_id = ID
			 WHERE meta_key = '_price'
			   AND meta_value != ''
			   AND CAST(meta_value AS DECIMAL(10,2)) > 0
			   AND post_status = 'publish'"
		);
	}

	// floor min so no product is excluded at the low end;
	// ceil max so decimal prices (e.g. 2999.99) are always inside the slider range.
	$min  = $row ? (int) floor( (float) $row->min_price ) : 0;
	$max  = $row ? (int) ceil(  (float) $row->max_price ) : 10000;
	if ( $min === $max ) { $min = 0; }
	$range = $max - $min;
	$step  = $range > 1000 ? 100 : ( $range > 100 ? 10 : 1 );
	return compact( 'min', 'max', 'step' );
}

// Default catalog sort: newest first (matches AJAX default).
add_filter( 'woocommerce_default_catalog_orderby', fn() => 'date' );

// Products per page.
add_filter( 'loop_shop_per_page', fn() => 21 );

/* ============================================================
   Shop — helper: top-level product categories
   ============================================================ */
function amelia_get_product_categories() {
	return get_terms( [
		'taxonomy'   => 'product_cat',
		'hide_empty' => true,
		'orderby'    => 'name',
		'order'      => 'ASC',
	] ) ?: [];
}

/* ============================================================
   Shop — helper: taxonomy term the current archive is locked to
   (product category, tag or attribute archive)
   ============================================================ */
function amelia_get_locked_term() {
	$term = is_product_taxonomy() ? get_queried_object() : null;
	return $term instanceof WP_Term
		? [ 'taxonomy' => $term->taxonomy, 'term_id' => (int) $term->term_id ]
		: [ 'taxonomy' => '', 'term_id' => 0 ];
}

/* ============================================================
   Shop — enqueue async filter script
   ============================================================ */
function amelia_shop_filter_scripts() {
	if ( ! ( is_shop() || is_product_taxonomy() ) ) return;

	$asset_file = AMELIA_BUILD . '/shop-filter.asset.php';
	$asset      = file_exists( $asset_file ) ? require $asset_file : [ 'dependencies' => [], 'version' => AMELIA_VERSION ];

	wp_enqueue_script(
		'amelia-shop-filter',
		AMELIA_BUILD_URI . '/shop-filter.js',
		$asset['dependencies'],
		$asset['version'],
		true
	);

	$locked = amelia_get_locked_term();
	wp_localize_script( 'amelia-shop-filter', 'ameliaShop', [
		'ajaxUrl'    => admin_url( 'admin-ajax.php' ),
		'nonce'      => wp_create_nonce( 'amelia_nonce' ),
		'currency'   => html_entity_decode( get_woocommerce_currency_symbol(), ENT_QUOTES ),
		'currPos'    => get_option( 'woocommerce_currency_pos', 'right' ),
		'taxonomy'   => $locked['taxonomy'],
		'termId'     => $locked['term_id'],
	] );
}
add_action( 'wp_enqueue_scripts', 'amelia_shop_filter_scripts' );

/* ============================================================
   Shop — AJAX product filter
   ============================================================ */
function amelia_filter_products() {
	check_ajax_referer( 'amelia_nonce', 'nonce' );

	$per_page        = 21;
	$locked_tax      = isset( $_POST['locked_taxonomy'] ) ? sanitize_key( wp_unslash( $_POST['locked_taxonomy'] ) ) : '';
	$locked_term     = isset( $_POST['locked_term'] ) ? absint( $_POST['locked_term'] ) : 0;
	if ( ! $locked_term || ! taxonomy_exists( $locked_tax ) || ! is_object_in_taxonomy( 'product', $locked_tax ) ) {
		$locked_tax  = '';
		$locked_term = 0;
	}
	$price_range     = $locked_term ? amelia_get_price_range( $locked_term, $locked_tax ) : amelia_get_price_range();
	$min_price       = isset( $_POST['min_price'] ) ? (float) $_POST['min_price'] : $price_range['min'];
	$max_price       = isset( $_POST['max_price'] ) ? (float) $_POST['max_price'] : $price_range['max'];
	$at_min          = $min_price <= $price_range['min'];
	$at_max          = $max_price >= $price_range['max'];
	$categories      = isset( $_POST['categories'] ) ? array_map( 'intval', (array) $_POST['categories'] ) : [];
	$page        = isset( $_POST['page'] ) ? max( 1, (int) $_POST['page'] ) : 1;
	$orderby_raw = sanitize_text_field( wp_unslash( $_POST['orderby'] ?? 'date:DESC' ) );
	[ $orderby, $order ] = array_pad( explode( ':', $orderby_raw ), 2, 'DESC' );
	$order = in_array( strtoupper( $order ), [ 'ASC', 'DESC' ], true ) ? strtoupper( $order ) : 'DESC';

	// popularity/rating are not native WP_Query orderby values — sort by their meta keys.
	$meta_orderby_map = [
		'price'      => '_price',
		'popularity' => 'total_sales',
		'rating'     => '_wc_average_rating',
	];
	$meta_orderby = $meta_orderby_map[ $orderby ] ?? '';
	if ( $meta_orderby ) {
		$orderby = 'meta_value_num';
	} elseif ( ! in_array( $orderby, [ 'date', 'title' ], true ) ) {
		$orderby = 'date';
	}

	// Mirror WooCommerce's catalog visibility — exclude products hidden from the catalog.
	// Use term_taxonomy_id (most reliable) via wc_get_product_visibility_term_ids().
	$visibility_term_ids = wc_get_product_visibility_term_ids();
	$exclude_term_ids    = array_filter( [ $visibility_term_ids['exclude-from-catalog'] ?? 0 ] );

	// Also exclude out-of-stock products when the WooCommerce setting is enabled.
	if ( 'yes' === get_option( 'woocommerce_hide_out_of_stock_items' ) ) {
		$exclude_term_ids[] = $visibility_term_ids['outofstock'] ?? 0;
		$exclude_term_ids   = array_filter( $exclude_term_ids );
	}

	$args = [
		'post_type'      => 'product',
		'post_status'    => 'publish',
		'posts_per_page' => $per_page,
		'paged'          => $page,
		'orderby'        => $orderby,
		'order'          => $order,
		'tax_query'      => [ 'relation' => 'AND', [
			'taxonomy' => 'product_visibility',
			'field'    => 'term_taxonomy_id',
			'terms'    => $exclude_term_ids,
			'operator' => 'NOT IN',
		] ],
	];

	if ( $meta_orderby ) {
		$args['meta_key'] = $meta_orderby;
	}

	// Only apply a price meta_query when the slider has actually been moved.
	if ( ! $at_min || ! $at_max ) {
		$price_clause = $at_max
			? [ 'key' => '_price', 'value' => $min_price, 'compare' => '>=', 'type' => 'DECIMAL(10,2)' ]
			: [ 'key' => '_price', 'value' => [ $min_price, $max_price ], 'compare' => 'BETWEEN', 'type' => 'DECIMAL(10,2)' ];
		$args['meta_query'] = [ 'relation' => 'AND', $price_clause ];
	}

	if ( $locked_term ) {
		$args['tax_query'][] = [
			'taxonomy' => $locked_tax,
			'field'    => 'term_id',
			'terms'    => [ $locked_term ],
		];
	}

	if ( ! empty( $categories ) ) {
		$args['tax_query'][] = [
			'taxonomy' => 'product_cat',
			'field'    => 'term_id',
			'terms'    => $categories,
		];
	}

	$query = new WP_Query( $args );

	// Use WooCommerce's own loop open/close so that loop context (counter,
	// columns, is_visible checks) is identical to the initial PHP page render.
	ob_start();
	if ( $query->have_posts() ) {
		woocommerce_product_loop_start();
		while ( $query->have_posts() ) {
			$query->the_post();
			wc_get_template_part( 'content', 'product' );
		}
		woocommerce_product_loop_end();
	}
	wp_reset_postdata();

	$html  = ob_get_clean();
	$total = (int) $query->found_posts;

	wp_send_json_success( [
		'html'      => $html,
		'count'     => $total,
		'hasMore'   => $total > ( $page * $per_page ),
		'page'      => $page,
	] );
}
add_action( 'wp_ajax_amelia_filter_products',        'amelia_filter_products' );
add_action( 'wp_ajax_nopriv_amelia_filter_products', 'amelia_filter_products' );

add_action( 'wp', function () {
	if ( ! is_product() ) return;
	remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10 );
	remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_upsell_display',           15 );
	remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products',  20 );
} );

add_filter( 'woocommerce_product_tabs', function ( $tabs ) {
	unset( $tabs['reviews'] );
	return $tabs;
} );

add_filter( 'woocommerce_output_related_products_args', function ( $args ) {
	$args['posts_per_page'] = 5;
	$args['columns']        = 5;
	return $args;
} );
