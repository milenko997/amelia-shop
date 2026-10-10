<?php
/**
 * Legal requirements for online sales in Serbia (Zakon o zaštiti potrošača,
 * Zakon o elektronskoj trgovini, ZZPL):
 * - seller details (name, address, PIB, MB, contact) editable in Customizer and shown in the footer
 * - price note (VAT included / seller not in the VAT system)
 * - right of withdrawal and complaints info at checkout and in customer emails
 * - footer payment methods taken from the enabled WooCommerce gateways
 */
defined( 'ABSPATH' ) || exit;

/* ============================================================
   Customizer — Podaci o firmi
   ============================================================ */
function amelia_company_fields() {
	return [
		'name'    => 'Poslovno ime (naziv firme ili preduzetnika)',
		'address' => 'Adresa sedišta (ulica i broj, poštanski broj, mesto)',
		'pib'     => 'PIB',
		'mb'      => 'Matični broj',
		'phone'   => 'Telefon',
		'email'   => 'Email za kupce i reklamacije',
	];
}

add_action( 'customize_register', function ( WP_Customize_Manager $wp_customize ) {
	$wp_customize->add_section( 'amelia_company', [
		'title'       => 'Podaci o firmi',
		'description' => 'Zakon obavezuje da ovi podaci budu lako dostupni kupcima. Prikazuju se u footer-u, na pravnim stranicama (šifra [amelia_podaci_o_firmi]) i u podacima za Google.',
		'priority'    => 155,
	] );

	foreach ( amelia_company_fields() as $key => $label ) {
		$wp_customize->add_setting( "amelia_company_{$key}", [
			'sanitize_callback' => 'email' === $key ? 'sanitize_email' : 'sanitize_text_field',
		] );
		$wp_customize->add_control( "amelia_company_{$key}", [
			'section' => 'amelia_company',
			'type'    => 'email' === $key ? 'email' : 'text',
			'label'   => $label,
		] );
	}

	$wp_customize->add_setting( 'amelia_company_vat', [
		'default'           => true,
		'sanitize_callback' => 'wp_validate_boolean',
	] );
	$wp_customize->add_control( 'amelia_company_vat', [
		'section'     => 'amelia_company',
		'type'        => 'checkbox',
		'label'       => 'Firma je u sistemu PDV-a',
		'description' => 'Uključeno: „Cene su sa uračunatim PDV-om“. Isključeno: „Prodavac nije u sistemu PDV-a“.',
	] );
} );

/**
 * Filled-in company details, keyed like amelia_company_fields().
 */
function amelia_company() {
	$data = [];
	foreach ( array_keys( amelia_company_fields() ) as $key ) {
		$value = trim( (string) get_theme_mod( "amelia_company_{$key}" ) );
		if ( '' !== $value ) {
			$data[ $key ] = $value;
		}
	}
	return $data;
}

function amelia_price_note() {
	return get_theme_mod( 'amelia_company_vat', true )
		? 'Sve cene su izražene u dinarima, sa uračunatim PDV-om.'
		: 'Sve cene su izražene u dinarima. Prodavac nije u sistemu PDV-a.';
}

/* ============================================================
   [amelia_podaci_o_firmi] — seller details block for legal pages
   ============================================================ */
add_shortcode( 'amelia_podaci_o_firmi', function () {
	$company = amelia_company();
	if ( ! $company ) {
		return current_user_can( 'edit_theme_options' )
			? '<p><em>Unesite podatke o firmi u Izgled → Prilagodi → Podaci o firmi.</em></p>'
			: '';
	}

	$labels = [
		'name'    => 'Prodavac',
		'address' => 'Adresa',
		'pib'     => 'PIB',
		'mb'      => 'Matični broj',
		'phone'   => 'Telefon',
		'email'   => 'Email',
	];

	$html = '<dl class="company-details">';
	foreach ( $company as $key => $value ) {
		if ( 'email' === $key ) {
			$value_html = '<a href="mailto:' . esc_attr( antispambot( $value ) ) . '">' . esc_html( antispambot( $value ) ) . '</a>';
		} elseif ( 'phone' === $key ) {
			$value_html = '<a href="tel:' . esc_attr( preg_replace( '/[^\d+]/', '', $value ) ) . '">' . esc_html( $value ) . '</a>';
		} else {
			$value_html = esc_html( $value );
		}
		$html .= '<dt>' . esc_html( $labels[ $key ] ) . '</dt><dd>' . $value_html . '</dd>';
	}
	return $html . '</dl>';
} );

/* ============================================================
   Footer — seller line and payment methods
   ============================================================ */
function amelia_footer_company_line() {
	$company = amelia_company();
	$parts   = [];
	foreach ( [ 'name', 'address' ] as $key ) {
		if ( isset( $company[ $key ] ) ) {
			$parts[] = esc_html( $company[ $key ] );
		}
	}
	if ( isset( $company['pib'] ) ) {
		$parts[] = 'PIB: ' . esc_html( $company['pib'] );
	}
	if ( isset( $company['mb'] ) ) {
		$parts[] = 'MB: ' . esc_html( $company['mb'] );
	}
	if ( isset( $company['email'] ) ) {
		$email   = antispambot( $company['email'] );
		$parts[] = '<a href="mailto:' . esc_attr( $email ) . '">' . esc_html( $email ) . '</a>';
	}
	if ( isset( $company['phone'] ) ) {
		$parts[] = '<a href="tel:' . esc_attr( preg_replace( '/[^\d+]/', '', $company['phone'] ) ) . '">' . esc_html( $company['phone'] ) . '</a>';
	}
	return implode( ' · ', $parts );
}

/**
 * Titles of the payment methods enabled in WooCommerce (only real options are shown).
 */
function amelia_payment_method_titles() {
	if ( ! function_exists( 'WC' ) ) {
		return [];
	}
	$titles = [];
	foreach ( WC()->payment_gateways()->payment_gateways() as $gateway ) {
		if ( 'yes' === $gateway->enabled ) {
			$titles[] = wp_strip_all_tags( $gateway->get_title() );
		}
	}
	return $titles;
}

/* ============================================================
   Price note — product page, cart and checkout totals
   ============================================================ */
add_action( 'woocommerce_single_product_summary', function () {
	echo '<p class="price-note">' . esc_html( amelia_price_note() ) . '</p>';
}, 11 );

$amelia_totals_note = function () {
	echo '<tr class="price-note-row"><td colspan="2"><span class="price-note">' . esc_html( amelia_price_note() ) . '</span></td></tr>';
};
add_action( 'woocommerce_cart_totals_after_order_total', $amelia_totals_note );
add_action( 'woocommerce_review_order_after_order_total', $amelia_totals_note );

/* ============================================================
   Right of withdrawal and complaints — checkout and emails
   ============================================================ */
function amelia_legal_page_url( $slug ) {
	return function_exists( 'amelia_page_url_by_slugs' ) ? amelia_page_url_by_slugs( [ $slug ] ) : '';
}

add_action( 'woocommerce_review_order_before_submit', function () {
	$withdrawal = amelia_legal_page_url( 'pravo-na-odustanak' );
	$complaints = amelia_legal_page_url( 'reklamacije' );
	?>
	<p class="checkout-legal-note">
		Imate pravo da odustanete od kupovine u roku od 14 dana od prijema robe, bez navođenja razloga<?php
		if ( $withdrawal ) {
			echo ' (<a href="' . esc_url( $withdrawal ) . '" target="_blank" rel="noopener">pravo na odustanak i obrazac</a>)';
		}
		?>.
		<?php if ( $complaints ) : ?>
			Uslovi reklamacije su opisani na stranici <a href="<?php echo esc_url( $complaints ); ?>" target="_blank" rel="noopener">Reklamacije i povraćaj</a>.
		<?php endif; ?>
	</p>
	<?php
} );

add_action( 'woocommerce_email_after_order_table', function ( $order, $sent_to_admin, $plain_text, $email ) {
	if ( $sent_to_admin || ! $email || ! in_array( $email->id, [ 'customer_processing_order', 'customer_on_hold_order', 'customer_completed_order' ], true ) ) {
		return;
	}

	$withdrawal = amelia_legal_page_url( 'pravo-na-odustanak' );
	$complaints = amelia_legal_page_url( 'reklamacije' );
	$company    = amelia_company();

	$text  = 'Imate pravo da odustanete od ugovora u roku od 14 dana od dana prijema robe, bez navođenja razloga, slanjem izjave ili popunjenog obrasca za odustanak';
	$text .= isset( $company['email'] ) ? ' na ' . $company['email'] : '';
	$text .= '. Proizvodi koji su iz higijenskih razloga zapakovani i čije je pakovanje otvoreno (npr. donji veš) ne mogu se vratiti.';

	if ( $plain_text ) {
		echo "\n" . esc_html( $text ) . "\n";
		if ( $withdrawal ) {
			echo 'Pravo na odustanak i obrazac: ' . esc_url( $withdrawal ) . "\n";
		}
		if ( $complaints ) {
			echo 'Reklamacije: ' . esc_url( $complaints ) . "\n";
		}
		return;
	}

	echo '<p style="font-size:13px;line-height:1.5;margin:24px 0 0;">' . esc_html( $text );
	if ( $withdrawal ) {
		echo ' <a href="' . esc_url( $withdrawal ) . '">Pravo na odustanak i obrazac</a>.';
	}
	if ( $complaints ) {
		echo ' <a href="' . esc_url( $complaints ) . '">Reklamacije i povraćaj</a>.';
	}
	echo '</p>';
}, 20, 4 );
