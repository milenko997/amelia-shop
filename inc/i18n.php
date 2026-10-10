<?php
/**
 * Serbian Latin translations for the frontend.
 *
 * WordPress and WooCommerce only ship Serbian in Cyrillic (sr_RS). bin/build-latin-translations.php
 * transliterates those files into languages/, and they are loaded here for visitors,
 * so every core and WooCommerce string (notices, checkout fields, emails) is in Latin.
 * Admin screens keep the language chosen in the user's profile.
 */
defined( 'ABSPATH' ) || exit;

function amelia_load_latin_translations() {
	foreach ( [ 'default', 'woocommerce' ] as $domain ) {
		$file = AMELIA_DIR . "/languages/{$domain}-sr_Latn.mo"; // WordPress loads the .l10n.php next to it.
		if ( ! is_readable( AMELIA_DIR . "/languages/{$domain}-sr_Latn.l10n.php" ) ) {
			continue;
		}
		// Drop any Cyrillic file loaded for the current locale so the Latin one is used.
		unload_textdomain( $domain, true );
		load_textdomain( $domain, $file );
	}
}

add_action( 'after_setup_theme', function () {
	if ( ! is_admin() || wp_doing_ajax() ) {
		amelia_load_latin_translations();
	}
}, 0 );

// Customer emails sent from the admin (e.g. "order completed") must be in Latin too.
add_filter( 'woocommerce_email_setup_locale', function ( $setup ) {
	if ( is_admin() && ! wp_doing_ajax() ) {
		amelia_load_latin_translations();
	}
	return $setup;
} );

// Emails switch the locale, which reloads translations; load Latin again afterwards.
add_action( 'change_locale', 'amelia_load_latin_translations', 20 );

// <html lang="sr-Latn-RS"> for visitors and search engines.
add_filter( 'language_attributes', function ( $output ) {
	if ( is_admin() ) {
		return $output;
	}
	return preg_replace( '/lang="[^"]*"/', 'lang="sr-Latn-RS"', $output );
} );

/* ============================================================
   Store wording — preferred over the official translation
   ============================================================ */
add_filter( 'gettext', function ( $translated, $original, $domain ) {
	if ( $domain !== 'woocommerce' ) return $translated;

	static $map = [
		// Buttons
		'Select options'                          => 'Izaberi opcije',
		'Add to cart'                             => 'Dodaj u korpu',
		'Read more'                               => 'Saznaj više',
		'View cart'                               => 'Pogledaj korpu',
		'Proceed to checkout'                     => 'Nastavi na plaćanje',
		'Place order'                             => 'Poruči',
		'Update cart'                             => 'Ažuriraj korpu',
		'Apply coupon'                            => 'Primeni kupon',
		'Continue shopping'                       => 'Nastavi kupovinu',
		// Cart & checkout
		'Cart'                                    => 'Korpa',
		'Checkout'                                => 'Plaćanje',
		'Your cart'                               => 'Vaša korpa',
		'Cart totals'                             => 'Ukupno',
		'Subtotal'                                => 'Međuzbir',
		'Total'                                   => 'Ukupno',
		'Shipping'                                => 'Dostava',
		'Tax'                                     => 'PDV',
		'Coupon:'                                 => 'Kupon:',
		'Coupon code'                             => 'Kod kupona',
		'Your cart is currently empty.'           => 'Vaša korpa je prazna.',
		'Product'                                 => 'Proizvod',
		'Price'                                   => 'Cena',
		'Quantity'                                => 'Količina',
		// Checkout fields
		'Billing details'                         => 'Podaci za naplatu',
		'Order notes'                             => 'Napomena uz narudžbinu',
		'First name'                              => 'Ime',
		'Last name'                               => 'Prezime',
		'Country / Region'                        => 'Država',
		'Street address'                          => 'Adresa',
		'Apartment, suite, unit, etc. (optional)' => 'Stan, sprat, br. (opciono)',
		'Town / City'                             => 'Grad',
		'State / County'                          => 'Okrug',
		'Postcode / ZIP'                          => 'Poštanski broj',
		'Phone'                                   => 'Telefon',
		'Email address'                           => 'Email adresa',
		// Order review
		'Your order'                              => 'Vaša narudžbina',
		'Order summary'                           => 'Pregled narudžbine',
		'Payment'                                 => 'Plaćanje',
		// Variations
		'Choose an option'                        => 'Izaberi opciju',
		// Product tabs
		'Description'                             => 'Opis',
		'Additional information'                  => 'Dodatne informacije',
		'Reviews'                                 => 'Recenzije',
		// Product
		'Sale!'                                   => 'Akcija!',
		'Out of stock'                            => 'Nema na stanju',
		'In stock'                                => 'Na stanju',
		'SKU:'                                    => 'Šifra:',
		'Category:'                               => 'Kategorija:',
		'Tag:'                                    => 'Oznaka:',
		'Related products'                        => 'Srodni proizvodi',
		'You may also like&hellip;'               => 'Možda vam se sviđa…',
	];

	return $map[ $original ] ?? $translated;
}, 10, 3 );

add_filter( 'ngettext', function ( $translated, $single, $plural, $number, $domain ) {
	if ( $domain !== 'woocommerce' ) return $translated;

	static $map = [
		'Category:' => [ 'Kategorija:', 'Kategorije:' ],
		'Tag:'      => [ 'Oznaka:', 'Oznake:' ],
	];

	return isset( $map[ $single ] ) ? $map[ $single ][ 1 === (int) $number ? 0 : 1 ] : $translated;
}, 10, 5 );

add_filter( 'gettext_with_context', function ( $translated, $original, $context, $domain ) {
	// The official translation has a stray leading space (" „%s“").
	if ( 'woocommerce' === $domain && 'Item name in quotes' === $context && '&ldquo;%s&rdquo;' === $original ) {
		return '„%s“';
	}
	return $translated;
}, 10, 4 );

// WooCommerce hardcodes the dinar symbol in Cyrillic ("рсд"); show it in Latin everywhere
// (prices, cart, checkout, emails, shop price filter).
add_filter( 'woocommerce_currency_symbol', function ( $symbol, $currency ) {
	return 'RSD' === $currency ? 'RSD' : $symbol;
}, 10, 2 );
