<?php
/**
 * SEO: meta description, canonical, robots, Open Graph / Twitter cards and JSON-LD.
 *
 * All head output is skipped when a dedicated SEO plugin is active,
 * so the theme never duplicates tags that the plugin already prints.
 */
defined( 'ABSPATH' ) || exit;

function amelia_seo_plugin_active() {
	return defined( 'WPSEO_VERSION' )     // Yoast SEO
		|| class_exists( 'RankMath' )     // Rank Math
		|| defined( 'SEOPRESS_VERSION' )  // SEOPress
		|| defined( 'AIOSEO_VERSION' );   // All in One SEO
}

/* ============================================================
   Em dash cleanup: replaces "—" in visible text with "–".
   Covers content stored in the database (pages, products,
   categories) and dashes produced by wptexturize from "--".
   ============================================================ */
function amelia_replace_em_dash( $text ) {
	if ( ! is_string( $text ) || '' === $text ) {
		return $text;
	}
	return str_replace( [ '—', '&#8212;', '&mdash;' ], '–', $text );
}

foreach ( [ 'the_content', 'the_title', 'the_excerpt', 'term_description', 'single_term_title', 'single_cat_title', 'single_tag_title', 'woocommerce_short_description', 'widget_text', 'widget_block_content' ] as $amelia_hook ) {
	add_filter( $amelia_hook, 'amelia_replace_em_dash', 20 );
}
add_filter( 'document_title_parts', fn( $parts ) => array_map( 'amelia_replace_em_dash', $parts ), 20 );

// "Naslov | Amelia Shop" instead of the default en dash separator.
add_filter( 'document_title_separator', fn() => '|' );

/* ============================================================
   Customizer — social profiles, default share image, home description
   ============================================================ */
add_action( 'customize_register', function ( WP_Customize_Manager $wp_customize ) {
	$wp_customize->add_section( 'amelia_seo', [
		'title'    => 'SEO i društvene mreže',
		'priority' => 160,
	] );

	$wp_customize->add_setting( 'amelia_home_description', [ 'sanitize_callback' => 'sanitize_textarea_field' ] );
	$wp_customize->add_control( 'amelia_home_description', [
		'section'     => 'amelia_seo',
		'type'        => 'textarea',
		'label'       => 'Meta opis početne stranice',
		'description' => 'Tekst koji Google prikazuje ispod naslova (do 155 karaktera).',
	] );

	$wp_customize->add_setting( 'amelia_og_image', [ 'sanitize_callback' => 'absint' ] );
	$wp_customize->add_control( new WP_Customize_Media_Control( $wp_customize, 'amelia_og_image', [
		'section'     => 'amelia_seo',
		'mime_type'   => 'image',
		'label'       => 'Podrazumevana slika za deljenje',
		'description' => 'Prikazuje se kad se link podeli na društvenim mrežama, a stranica nema svoju sliku. Preporuka: 1200 × 630 px.',
	] ) );

	foreach ( amelia_social_networks() as $key => $label ) {
		$wp_customize->add_setting( "amelia_{$key}_url", [ 'sanitize_callback' => 'esc_url_raw' ] );
		$wp_customize->add_control( "amelia_{$key}_url", [
			'section' => 'amelia_seo',
			'type'    => 'url',
			'label'   => $label . ' profil (URL)',
		] );
	}
} );

function amelia_social_networks() {
	return [
		'instagram' => 'Instagram',
		'facebook'  => 'Facebook',
		'tiktok'    => 'TikTok',
	];
}

/**
 * Configured social profile URLs, keyed by network.
 */
function amelia_social_urls() {
	$urls = [];
	foreach ( array_keys( amelia_social_networks() ) as $key ) {
		$url = get_theme_mod( "amelia_{$key}_url" );
		if ( $url ) {
			$urls[ $key ] = $url;
		}
	}
	return $urls;
}

/* ============================================================
   Helpers — title, description, canonical, image
   ============================================================ */
function amelia_seo_clean_text( $text, $length = 155 ) {
	$text = excerpt_remove_blocks( (string) $text );
	$text = wp_strip_all_tags( strip_shortcodes( $text ) );
	$text = html_entity_decode( $text, ENT_QUOTES, get_bloginfo( 'charset' ) );
	$text = trim( preg_replace( '/\s+/u', ' ', $text ) );
	$text = amelia_replace_em_dash( $text );

	if ( mb_strlen( $text ) > $length ) {
		$text = mb_substr( $text, 0, $length - 1 );
		$text = preg_replace( '/\s+\S*$/u', '', $text ) . '…';
	}
	return $text;
}

function amelia_seo_title() {
	if ( is_front_page() ) {
		return get_bloginfo( 'name' );
	}
	if ( function_exists( 'is_shop' ) && is_shop() ) {
		return get_the_title( wc_get_page_id( 'shop' ) );
	}
	if ( is_singular() ) {
		return get_the_title( get_queried_object_id() );
	}
	if ( is_category() || is_tag() || is_tax() ) {
		return single_term_title( '', false );
	}
	if ( is_home() && get_option( 'page_for_posts' ) ) {
		return get_the_title( get_option( 'page_for_posts' ) );
	}
	return wp_get_document_title();
}

function amelia_seo_description() {
	$site_name = get_bloginfo( 'name' );
	$tagline   = get_bloginfo( 'description' );
	$text      = '';

	if ( is_front_page() ) {
		$text = get_theme_mod( 'amelia_home_description' );
		if ( ! $text && is_page() && has_excerpt( get_queried_object_id() ) ) {
			$text = get_the_excerpt( get_queried_object_id() );
		}
	} elseif ( function_exists( 'is_shop' ) && is_shop() ) {
		$shop_id = wc_get_page_id( 'shop' );
		$text    = has_excerpt( $shop_id ) ? get_the_excerpt( $shop_id ) : '';
		if ( ! $text ) {
			$text = sprintf( 'Kupite online u prodavnici %s. Donji veš, čarape i pidžame za svaki dan, uz jednostavnu porudžbinu i povraćaj u roku od 30 dana.', $site_name );
		}
	} elseif ( is_singular( 'product' ) ) {
		$product = wc_get_product( get_queried_object_id() );
		if ( $product ) {
			$text = $product->get_short_description() ?: $product->get_description();
		}
	} elseif ( is_singular() ) {
		$post = get_queried_object();
		$text = has_excerpt( $post ) ? $post->post_excerpt : $post->post_content;
	} elseif ( is_category() || is_tag() || is_tax() ) {
		$term = get_queried_object();
		$text = $term->description;
		if ( ! $text && function_exists( 'is_product_taxonomy' ) && is_product_taxonomy() ) {
			$text = sprintf( 'Pogledajte ponudu: %1$s. Kupite online u prodavnici %2$s i izaberite model koji vam odgovara.', $term->name, $site_name );
		}
	} elseif ( is_home() && get_option( 'page_for_posts' ) ) {
		$text = get_the_excerpt( get_option( 'page_for_posts' ) );
	}

	if ( ! $text ) {
		$text = $tagline;
	}

	return amelia_seo_clean_text( $text );
}

/**
 * Canonical URL for the current request. Singular views use WordPress core,
 * archives are built from the term/shop base so sort and filter query args never leak in.
 */
function amelia_seo_canonical() {
	if ( is_404() || is_search() ) {
		return '';
	}
	if ( is_front_page() ) {
		return home_url( '/' );
	}
	if ( is_singular() ) {
		return wp_get_canonical_url( get_queried_object_id() ) ?: '';
	}

	$base = '';
	if ( function_exists( 'is_shop' ) && is_shop() ) {
		$base = wc_get_page_permalink( 'shop' );
	} elseif ( is_category() || is_tag() || is_tax() ) {
		$link = get_term_link( get_queried_object() );
		$base = is_wp_error( $link ) ? '' : $link;
	} elseif ( is_home() && get_option( 'page_for_posts' ) ) {
		$base = get_permalink( get_option( 'page_for_posts' ) );
	} elseif ( is_post_type_archive() ) {
		$base = get_post_type_archive_link( get_query_var( 'post_type' ) );
	}

	if ( ! $base ) {
		return '';
	}

	$paged = (int) get_query_var( 'paged' );
	if ( $paged > 1 ) {
		global $wp_rewrite;
		$base = $wp_rewrite->using_permalinks()
			? user_trailingslashit( trailingslashit( $base ) . $wp_rewrite->pagination_base . '/' . $paged, 'paged' )
			: add_query_arg( 'paged', $paged, $base );
	}
	return $base;
}

/**
 * Best share image for the current request: [ url, width, height, alt ] or null.
 */
function amelia_seo_image() {
	$image_id = 0;

	if ( is_singular() ) {
		$image_id = get_post_thumbnail_id( get_queried_object_id() );
	} elseif ( is_category() || is_tag() || is_tax() ) {
		$image_id = (int) get_term_meta( get_queried_object_id(), 'thumbnail_id', true );
	} elseif ( function_exists( 'is_shop' ) && is_shop() ) {
		$image_id = get_post_thumbnail_id( wc_get_page_id( 'shop' ) );
	}

	if ( ! $image_id ) {
		$image_id = (int) get_theme_mod( 'amelia_og_image' ) ?: (int) get_theme_mod( 'custom_logo' );
	}
	if ( ! $image_id ) {
		return null;
	}

	$src = wp_get_attachment_image_src( $image_id, 'large' );
	if ( ! $src ) {
		return null;
	}

	$alt = get_post_meta( $image_id, '_wp_attachment_image_alt', true ) ?: amelia_seo_title();
	return [ $src[0], (int) $src[1], (int) $src[2], $alt ];
}

/* ============================================================
   <head> output
   ============================================================ */
add_action( 'wp_head', function () {
	if ( amelia_seo_plugin_active() ) {
		return;
	}

	$title       = amelia_replace_em_dash( amelia_seo_title() );
	$description = amelia_seo_description();
	$canonical   = amelia_seo_canonical();
	$image       = amelia_seo_image();
	$is_product  = is_singular( 'product' );
	$is_article  = is_singular( 'post' );

	$meta = [];
	if ( $description ) {
		$meta[] = [ 'name', 'description', $description ];
	}

	$meta[] = [ 'property', 'og:locale', get_locale() ];
	$meta[] = [ 'property', 'og:site_name', get_bloginfo( 'name' ) ];
	$meta[] = [ 'property', 'og:type', $is_product ? 'product' : ( $is_article ? 'article' : 'website' ) ];
	$meta[] = [ 'property', 'og:title', $title ];
	if ( $description ) {
		$meta[] = [ 'property', 'og:description', $description ];
	}
	if ( $canonical ) {
		$meta[] = [ 'property', 'og:url', $canonical ];
	}
	if ( $image ) {
		$meta[] = [ 'property', 'og:image', $image[0] ];
		$meta[] = [ 'property', 'og:image:width', $image[1] ];
		$meta[] = [ 'property', 'og:image:height', $image[2] ];
		$meta[] = [ 'property', 'og:image:alt', $image[3] ];
	}

	if ( $is_product ) {
		$product = wc_get_product( get_queried_object_id() );
		if ( $product && '' !== $product->get_price() ) {
			$meta[] = [ 'property', 'product:price:amount', wc_format_decimal( $product->get_price(), wc_get_price_decimals() ) ];
			$meta[] = [ 'property', 'product:price:currency', get_woocommerce_currency() ];
			$meta[] = [ 'property', 'product:availability', $product->is_in_stock() ? 'in stock' : 'out of stock' ];
		}
	}

	if ( $is_article ) {
		$meta[] = [ 'property', 'article:published_time', get_the_date( 'c', get_queried_object_id() ) ];
		$meta[] = [ 'property', 'article:modified_time', get_the_modified_date( 'c', get_queried_object_id() ) ];
	}

	$meta[] = [ 'name', 'twitter:card', $image ? 'summary_large_image' : 'summary' ];
	$meta[] = [ 'name', 'twitter:title', $title ];
	if ( $description ) {
		$meta[] = [ 'name', 'twitter:description', $description ];
	}
	if ( $image ) {
		$meta[] = [ 'name', 'twitter:image', $image[0] ];
	}

	echo "\n";
	// Core prints rel=canonical for singular views; archives get theirs here.
	if ( $canonical && ! is_singular() ) {
		printf( '<link rel="canonical" href="%s">' . "\n", esc_url( $canonical ) );
	}
	foreach ( $meta as [ $attr, $key, $value ] ) {
		printf( '<meta %s="%s" content="%s">' . "\n", $attr, esc_attr( $key ), esc_attr( $value ) );
	}
}, 1 );

/* ============================================================
   Robots — keep thin and duplicate pages out of the index
   ============================================================ */
add_filter( 'wp_robots', function ( $robots ) {
	if ( amelia_seo_plugin_active() ) {
		return $robots;
	}

	// Search results, author and date archives add no unique content for a shop.
	$noindex = is_search() || is_author() || is_date();

	// Sorted or filtered shop listings duplicate the canonical listing.
	if ( function_exists( 'is_shop' ) && ( is_shop() || is_product_taxonomy() ) ) {
		foreach ( array_keys( $_GET ) as $param ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if ( in_array( $param, [ 'orderby', 'min_price', 'max_price', 'rating_filter' ], true ) || str_starts_with( $param, 'filter_' ) ) {
				$noindex = true;
				break;
			}
		}
	}

	if ( $noindex ) {
		$robots['noindex'] = true;
		$robots['follow']  = true;
		unset( $robots['max-image-preview'] );
	}
	return $robots;
} );

// Author archives are noindexed above, so keep them out of the XML sitemap too.
add_filter( 'wp_sitemaps_add_provider', function ( $provider, $name ) {
	return 'users' === $name && ! amelia_seo_plugin_active() ? false : $provider;
}, 10, 2 );

/* ============================================================
   JSON-LD — Organization (OnlineStore) and WebSite on the home page.
   WooCommerce already prints Product and BreadcrumbList data.
   ============================================================ */
add_action( 'wp_head', function () {
	if ( amelia_seo_plugin_active() || ! is_front_page() ) {
		return;
	}

	$home = home_url( '/' );
	$org  = [
		'@type' => 'OnlineStore',
		'@id'   => $home . '#organization',
		'name'  => get_bloginfo( 'name' ),
		'url'   => $home,
	];

	$description = amelia_seo_description();
	if ( $description ) {
		$org['description'] = $description;
	}

	$logo_id = (int) get_theme_mod( 'custom_logo' );
	$logo    = $logo_id ? wp_get_attachment_image_src( $logo_id, 'full' ) : null;
	if ( $logo ) {
		$org['logo'] = [
			'@type'  => 'ImageObject',
			'url'    => $logo[0],
			'width'  => (int) $logo[1],
			'height' => (int) $logo[2],
		];
	}

	$social = array_values( amelia_social_urls() );
	if ( $social ) {
		$org['sameAs'] = $social;
	}

	$graph = [
		'@context' => 'https://schema.org',
		'@graph'   => [
			$org,
			[
				'@type'      => 'WebSite',
				'@id'        => $home . '#website',
				'url'        => $home,
				'name'       => get_bloginfo( 'name' ),
				'inLanguage' => str_replace( '_', '-', get_locale() ),
				'publisher'  => [ '@id' => $home . '#organization' ],
			],
		],
	];

	echo '<script type="application/ld+json">' . wp_json_encode( $graph, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
}, 2 );

/* ============================================================
   Performance — open the Google Fonts connections early
   ============================================================ */
add_filter( 'wp_resource_hints', function ( $urls, $relation ) {
	if ( 'preconnect' === $relation && wp_style_is( 'amelia-fonts', 'enqueued' ) ) {
		$urls[] = 'https://fonts.googleapis.com';
		$urls[] = [ 'href' => 'https://fonts.gstatic.com', 'crossorigin' ];
	}
	return $urls;
}, 10, 2 );
