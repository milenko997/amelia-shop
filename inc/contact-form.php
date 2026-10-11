<?php
/**
 * Contact form (amelia/contact-form block): handles submissions and emails them to the shop.
 *
 * Works with and without JavaScript: view.js posts via fetch and gets JSON back,
 * a plain form post is redirected back to the page with ?kontakt=poslato or ?kontakt=<reason>.
 *
 * Spam protection without captcha: a hidden honeypot field, a signed render timestamp
 * (bots that submit instantly or replay old forms are rejected) and a per-IP rate limit.
 * No nonce, and view.js fetches a fresh timestamp before submitting, so the form keeps
 * working on cached pages.
 */
defined( 'ABSPATH' ) || exit;

const AMELIA_CONTACT_MIN_SECONDS = 3;
const AMELIA_CONTACT_MAX_AGE     = DAY_IN_SECONDS;
const AMELIA_CONTACT_RATE_LIMIT  = 5; // messages per IP per 10 minutes

/**
 * Form fields, shared by the template (render.php) and the validation below.
 */
function amelia_contact_form_fields() {
	return [
		'name'    => [ 'label' => 'Ime i prezime', 'type' => 'text', 'required' => true, 'autocomplete' => 'name', 'max' => 100 ],
		'email'   => [ 'label' => 'Email', 'type' => 'email', 'required' => true, 'autocomplete' => 'email', 'max' => 100 ],
		'phone'   => [ 'label' => 'Telefon', 'type' => 'tel', 'required' => false, 'autocomplete' => 'tel', 'max' => 40 ],
		'order'   => [ 'label' => 'Broj porudžbine', 'type' => 'text', 'required' => false, 'autocomplete' => 'off', 'max' => 40 ],
		'message' => [ 'label' => 'Poruka', 'type' => 'textarea', 'required' => true, 'autocomplete' => 'off', 'max' => 5000, 'min' => 10 ],
	];
}

/**
 * Result messages, keyed by the ?kontakt= value used for the no-JS redirect.
 */
function amelia_contact_form_messages() {
	return [
		'poslato' => 'Hvala! Vaša poruka je poslata, odgovorićemo vam u najkraćem roku.',
		'polja'   => 'Poruka nije poslata. Proverite da li su sva obavezna polja ispravno popunjena (ime, ispravan email i poruka od bar 10 karaktera).',
		'istekla' => 'Forma je istekla. Osvežite stranicu i pošaljite poruku ponovo.',
		'limit'   => 'Poslali ste previše poruka u kratkom roku. Pokušajte ponovo za nekoliko minuta.',
		'slanje'  => 'Poruka trenutno ne može da se pošalje. Pokušajte ponovo kasnije ili nas kontaktirajte telefonom.',
	];
}

function amelia_contact_form_token() {
	$ts = time();
	return [ 'ts' => $ts, 'sig' => wp_hash( 'amelia_contact|' . $ts ) ];
}

/**
 * Hidden fields that prove the form was rendered by us a moment ago.
 */
function amelia_contact_form_token_fields() {
	$token = amelia_contact_form_token();
	printf(
		'<input type="hidden" name="ts" value="%d"><input type="hidden" name="sig" value="%s">',
		(int) $token['ts'],
		esc_attr( $token['sig'] )
	);
}

// Fresh token for pages served from a cache (fetched by view.js before submitting).
function amelia_contact_form_send_token() {
	nocache_headers();
	wp_send_json_success( amelia_contact_form_token() );
}
add_action( 'admin_post_nopriv_amelia_contact_token', 'amelia_contact_form_send_token' );
add_action( 'admin_post_amelia_contact_token', 'amelia_contact_form_send_token' );

function amelia_contact_form_recipient() {
	$company = function_exists( 'amelia_company' ) ? amelia_company() : [];
	return $company['email'] ?? get_option( 'admin_email' );
}

/**
 * Visitor IP. WooCommerce's helper also reads the usual proxy headers, so visitors behind
 * a CDN or proxy don't all share one rate-limit bucket.
 */
function amelia_contact_form_ip() {
	if ( class_exists( 'WC_Geolocation' ) ) {
		return WC_Geolocation::get_ip_address();
	}
	return isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
}

/**
 * Sends the JSON (fetch) or redirect (plain post) response and exits.
 *
 * @param string $result Key from amelia_contact_form_messages().
 */
function amelia_contact_form_respond( $result, array $errors = [] ) {
	// phpcs:disable WordPress.Security.NonceVerification.Missing -- see file header.
	$message = amelia_contact_form_messages()[ $result ];

	if ( ! empty( $_POST['ajax'] ) ) {
		$payload = [ 'message' => $message, 'errors' => $errors ];
		'poslato' === $result ? wp_send_json_success( $payload ) : wp_send_json_error( $payload, 422 );
	}

	// Back to the page with the form; the posted URL works even when the browser sends no Referer.
	$posted = isset( $_POST['redirect'] ) ? esc_url_raw( wp_unslash( $_POST['redirect'] ) ) : '';
	$back   = wp_validate_redirect( $posted, wp_get_referer() ?: home_url( '/' ) );
	// phpcs:enable

	wp_safe_redirect( add_query_arg( 'kontakt', $result, remove_query_arg( 'kontakt', $back ) ) . '#kontakt-forma' );
	exit;
}

function amelia_handle_contact_form() {
	// phpcs:disable WordPress.Security.NonceVerification.Missing -- see file header.

	// Honeypot filled in or form sent faster than a person could type: pretend it worked.
	$ts = isset( $_POST['ts'] ) ? (int) $_POST['ts'] : 0;
	if ( ! empty( $_POST['website'] ) || ( time() - $ts ) < AMELIA_CONTACT_MIN_SECONDS ) {
		amelia_contact_form_respond( 'poslato' );
	}

	$sig = isset( $_POST['sig'] ) ? sanitize_text_field( wp_unslash( $_POST['sig'] ) ) : '';
	if ( ! hash_equals( wp_hash( 'amelia_contact|' . $ts ), $sig ) || ( time() - $ts ) > AMELIA_CONTACT_MAX_AGE ) {
		amelia_contact_form_respond( 'istekla' );
	}

	$values = [];
	$errors = [];
	foreach ( amelia_contact_form_fields() as $key => $field ) {
		$raw   = isset( $_POST[ $key ] ) ? wp_unslash( $_POST[ $key ] ) : '';
		$value = 'textarea' === $field['type'] ? sanitize_textarea_field( $raw ) : sanitize_text_field( $raw );
		if ( 'email' === $field['type'] ) {
			$value = sanitize_email( $value );
		}
		$values[ $key ] = $value;

		$length = mb_strlen( $value );
		if ( $field['required'] && 0 === $length ) {
			$errors[ $key ] = 'Ovo polje je obavezno.';
		} elseif ( $length > $field['max'] ) {
			$errors[ $key ] = sprintf( 'Najviše %d karaktera.', $field['max'] );
		} elseif ( isset( $field['min'] ) && $length < $field['min'] ) {
			$errors[ $key ] = sprintf( 'Unesite bar %d karaktera.', $field['min'] );
		}
	}
	// phpcs:enable

	if ( ! isset( $errors['email'] ) && ! is_email( $values['email'] ) ) {
		$errors['email'] = 'Unesite ispravnu email adresu.';
	}
	if ( $errors ) {
		amelia_contact_form_respond( 'polja', $errors );
	}

	// Count the attempt before sending, so parallel requests can't all slip under the limit.
	$ip_key = 'amelia_cf_' . md5( amelia_contact_form_ip() );
	$sent   = (int) get_transient( $ip_key );
	if ( $sent >= AMELIA_CONTACT_RATE_LIMIT ) {
		amelia_contact_form_respond( 'limit' );
	}
	set_transient( $ip_key, $sent + 1, 10 * MINUTE_IN_SECONDS );

	$body  = 'Nova poruka sa kontakt forme na sajtu ' . home_url( '/' ) . "\n\n";
	$body .= "Ime: {$values['name']}\nEmail: {$values['email']}\n";
	$body .= $values['phone'] ? "Telefon: {$values['phone']}\n" : '';
	$body .= $values['order'] ? "Broj porudžbine: {$values['order']}\n" : '';
	$body .= "\nPoruka:\n{$values['message']}\n\n";
	$body .= "Odgovorom na ovaj mejl pišete direktno pošiljaocu.\n";

	$mailed = wp_mail(
		amelia_contact_form_recipient(),
		sprintf( 'Nova poruka sa sajta: %s', $values['name'] ),
		$body,
		[
			'Content-Type: text/plain; charset=UTF-8',
			// Address only: wp_mail splits headers on commas, so a name like "Petrović, Ana" would break it.
			'Reply-To: ' . $values['email'],
		]
	);

	amelia_contact_form_respond( $mailed ? 'poslato' : 'slanje' );
}
add_action( 'admin_post_nopriv_amelia_contact', 'amelia_handle_contact_form' );
add_action( 'admin_post_amelia_contact', 'amelia_handle_contact_form' );
