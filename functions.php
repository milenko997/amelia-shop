<?php
defined( 'ABSPATH' ) || exit;

define( 'AMELIA_VERSION', '1.0.0' );
define( 'AMELIA_DIR',     get_template_directory() );
define( 'AMELIA_URI',     get_template_directory_uri() );
define( 'AMELIA_BUILD',   AMELIA_DIR . '/build' );
define( 'AMELIA_BUILD_URI', AMELIA_URI . '/build' );

/* ============================================================
   Theme Setup
   ============================================================ */
function amelia_setup() {
	load_theme_textdomain( 'amelia-shop', AMELIA_DIR . '/languages' );

	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', [ 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ] );
	add_theme_support( 'customize-selective-refresh-widgets' );
	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'editor-styles' );
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
		'primary'  => 'Glavni meni',
		'footer_1' => 'Podnožje – kolona 1',
		'footer_2' => 'Podnožje – kolona 2',
		'footer_3' => 'Podnožje – kolona 3',
	] );

	add_image_size( 'amelia-product',    600, 800,  true );
	add_image_size( 'amelia-product-lg', 900, 1200, true );
	add_image_size( 'amelia-category',   400, 533,  true );
}
add_action( 'after_setup_theme', 'amelia_setup' );

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
		'nonce'   => wp_create_nonce( 'amelia_nonce' ),
	] );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
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
	$blocks = [ 'hero', 'category-grid', 'features-strip', 'newsletter', 'products', 'about-us', 'contact' ];

	foreach ( $blocks as $block ) {
		$path = AMELIA_BUILD . '/blocks/' . $block;
		if ( file_exists( $path . '/block.json' ) ) {
			register_block_type( $path );
		}
	}
}
add_action( 'init', 'amelia_register_blocks' );

/* ============================================================
   Widget Areas
   ============================================================ */
function amelia_sidebars() {
	register_sidebar( [
		'name'          => 'Bočna traka prodavnice',
		'id'            => 'shop-sidebar',
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget'  => '</div>',
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>',
	] );

	register_sidebar( [
		'name'          => 'Podnožje – kolona 1',
		'id'            => 'footer-1',
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget'  => '</div>',
		'before_title'  => '<h4>',
		'after_title'   => '</h4>',
	] );
}
add_action( 'widgets_init', 'amelia_sidebars' );

/* ============================================================
   WooCommerce — remove default wrappers
   ============================================================ */
remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper',     10 );
remove_action( 'woocommerce_after_main_content',  'woocommerce_output_content_wrapper_end', 10 );
remove_action( 'woocommerce_sidebar',             'woocommerce_get_sidebar',                10 );

function amelia_wc_before_content() { echo '<main id="main" class="site-main"><div class="container">'; }
function amelia_wc_after_content()  { echo '</div></main>'; }
add_action( 'woocommerce_before_main_content', 'amelia_wc_before_content', 10 );
add_action( 'woocommerce_after_main_content',  'amelia_wc_after_content',  10 );

/* ============================================================
   WooCommerce — product card image wrap + badges
   ============================================================ */
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
	if ( strtotime( $product->get_date_created() ) > strtotime( '-30 days' ) ) {
		echo '<span class="badge badge-new">Novo</span>';
	}
	echo '</div>';

	echo '<button class="product-wishlist" aria-label="Dodaj na listu želja">';
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

/* ============================================================
   Misc
   ============================================================ */
add_filter( 'excerpt_length', fn() => 20 );

function amelia_body_classes( $classes ) {
	if ( is_woocommerce() || is_cart() || is_checkout() || is_account_page() ) {
		$classes[] = 'woo-page';
	}
	return $classes;
}
add_filter( 'body_class', 'amelia_body_classes' );

/* ============================================================
   Shop — helper: product price range
   ============================================================ */
function amelia_get_price_range() {
	global $wpdb;
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
   Shop — enqueue async filter script
   ============================================================ */
function amelia_shop_filter_scripts() {
	if ( ! ( is_shop() || is_product_category() || is_product_tag() ) ) return;

	$asset_file = AMELIA_BUILD . '/shop-filter.asset.php';
	$asset      = file_exists( $asset_file ) ? require $asset_file : [ 'dependencies' => [], 'version' => AMELIA_VERSION ];

	wp_enqueue_script(
		'amelia-shop-filter',
		AMELIA_BUILD_URI . '/shop-filter.js',
		$asset['dependencies'],
		$asset['version'],
		true
	);

	$price_range = amelia_get_price_range();
	wp_localize_script( 'amelia-shop-filter', 'ameliaShop', [
		'ajaxUrl'    => admin_url( 'admin-ajax.php' ),
		'nonce'      => wp_create_nonce( 'amelia_nonce' ),
		'currency'   => html_entity_decode( get_woocommerce_currency_symbol(), ENT_QUOTES ),
		'currPos'    => get_option( 'woocommerce_currency_pos', 'right' ),
		'priceMin'   => $price_range['min'],
		'priceMax'   => $price_range['max'],
		'categoryId' => is_product_category() ? (int) get_queried_object_id() : 0,
	] );
}
add_action( 'wp_enqueue_scripts', 'amelia_shop_filter_scripts' );

/* ============================================================
   Shop — AJAX product filter
   ============================================================ */
function amelia_filter_products() {
	check_ajax_referer( 'amelia_nonce', 'nonce' );

	$per_page    = 21;
	$price_range = amelia_get_price_range();
	$min_price   = isset( $_POST['min_price'] ) ? (float) $_POST['min_price'] : $price_range['min'];
	$max_price   = isset( $_POST['max_price'] ) ? (float) $_POST['max_price'] : $price_range['max'];
	$at_min      = $min_price <= $price_range['min'];
	$at_max      = $max_price >= $price_range['max'];
	$categories       = isset( $_POST['categories'] ) ? array_map( 'intval', (array) $_POST['categories'] ) : [];
	$locked_category  = isset( $_POST['locked_category'] ) ? (int) $_POST['locked_category'] : 0;
	$page        = isset( $_POST['page'] ) ? max( 1, (int) $_POST['page'] ) : 1;
	$orderby_raw = sanitize_text_field( $_POST['orderby'] ?? 'date:DESC' );
	[ $orderby, $order ] = array_pad( explode( ':', $orderby_raw ), 2, 'DESC' );
	$order = in_array( strtoupper( $order ), [ 'ASC', 'DESC' ], true ) ? strtoupper( $order ) : 'DESC';

	$meta_orderby = '';
	if ( $orderby === 'price' ) {
		$meta_orderby = '_price';
		$orderby      = 'meta_value_num';
	} elseif ( ! in_array( $orderby, [ 'date', 'title', 'popularity', 'rating', 'rand' ], true ) ) {
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
			? [ 'key' => '_price', 'value' => $min_price, 'compare' => '>=', 'type' => 'NUMERIC' ]
			: [ 'key' => '_price', 'value' => [ $min_price, $max_price ], 'compare' => 'BETWEEN', 'type' => 'NUMERIC' ];
		$args['meta_query'] = [ 'relation' => 'AND', $price_clause ];
	}

	if ( $locked_category ) {
		$args['tax_query'][] = [
			'taxonomy' => 'product_cat',
			'field'    => 'term_id',
			'terms'    => [ $locked_category ],
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
		'html'    => $html,
		'count'   => $total,
		'hasMore' => $total > ( $page * $per_page ),
		'page'    => $page,
		'_debug'  => [
			'query_ids'   => wp_list_pluck( $query->posts, 'ID' ),
			'found_posts' => $total,
			'args'        => [
				'posts_per_page' => $args['posts_per_page'],
				'paged'          => $args['paged'],
				'orderby'        => $args['orderby'],
				'order'          => $args['order'],
			],
		],
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

add_filter( 'woocommerce_output_related_products_args', function ( $args ) {
	$args['posts_per_page'] = 5;
	$args['columns']        = 5;
	return $args;
} );
