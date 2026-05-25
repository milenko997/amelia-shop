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
	add_theme_support( 'wc-product-gallery-zoom' );
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
	$blocks = [ 'hero', 'category-grid', 'features-strip', 'newsletter' ];

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

add_action( 'woocommerce_before_main_content', fn() => print( '<main id="main" class="site-main"><div class="container">' ), 10 );
add_action( 'woocommerce_after_main_content',  fn() => print( '</div></main>' ),                                             10 );

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
