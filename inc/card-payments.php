<?php
/**
 * Card payment requirements of Serbian banks / card processors:
 * - card brand and 3-D Secure logos with links in the footer (Customizer → Plaćanje karticama;
 *   the official logo files and link targets come from the bank)
 * - [amelia_placanje_karticama]: card data protection, currency conversion and card refund statements
 */
defined( 'ABSPATH' ) || exit;

/**
 * Logo slots shown in the footer, in this order.
 */
function amelia_card_logo_slots() {
	return [
		'visa'         => 'Visa',
		'mastercard'   => 'Mastercard',
		'maestro'      => 'Maestro',
		'dinacard'     => 'DinaCard',
		'amex'         => 'American Express',
		'visa_secure'  => 'Visa Secure',
		'mc_idcheck'   => 'Mastercard Identity Check',
		'bank'         => 'Banka / procesor plaćanja',
	];
}

add_action( 'customize_register', function ( WP_Customize_Manager $wp_customize ) {
	$wp_customize->add_section( 'amelia_card_payments', [
		'title'       => 'Plaćanje karticama',
		'description' => 'Logotipe i linkove koje zahteva banka. Prikazuju se u footer-u samo ako su otpremljeni. Tekst izjava za banku dodajte na stranicu šifrom [amelia_placanje_karticama].',
		'priority'    => 156,
	] );

	$wp_customize->add_setting( 'amelia_card_processor', [ 'sanitize_callback' => 'sanitize_text_field' ] );
	$wp_customize->add_control( 'amelia_card_processor', [
		'section'     => 'amelia_card_payments',
		'type'        => 'text',
		'label'       => 'Naziv banke / procesora plaćanja',
		'description' => 'Npr. „Banca Intesa a.d. Beograd“. Koristi se u izjavama za banku.',
	] );

	foreach ( amelia_card_logo_slots() as $key => $label ) {
		$wp_customize->add_setting( "amelia_card_logo_{$key}", [ 'sanitize_callback' => 'absint' ] );
		$wp_customize->add_control( new WP_Customize_Media_Control( $wp_customize, "amelia_card_logo_{$key}", [
			'section'   => 'amelia_card_payments',
			'mime_type' => 'image',
			'label'     => "Logo: {$label}",
		] ) );

		$wp_customize->add_setting( "amelia_card_link_{$key}", [ 'sanitize_callback' => 'esc_url_raw' ] );
		$wp_customize->add_control( "amelia_card_link_{$key}", [
			'section' => 'amelia_card_payments',
			'type'    => 'url',
			'label'   => "Link za {$label} (opciono)",
		] );
	}
} );

/**
 * Footer row with the uploaded logos (nothing until at least one logo is uploaded).
 */
function amelia_card_logos() {
	$items = [];
	foreach ( amelia_card_logo_slots() as $key => $label ) {
		$image_id = (int) get_theme_mod( "amelia_card_logo_{$key}" );
		if ( ! $image_id ) {
			continue;
		}
		$img  = wp_get_attachment_image( $image_id, 'medium', false, [ 'alt' => $label, 'loading' => 'lazy', 'class' => 'card-logos__img' ] );
		if ( ! $img ) {
			continue; // attachment deleted from the media library
		}
		$link = get_theme_mod( "amelia_card_link_{$key}" );
		$items[] = $link
			? '<a href="' . esc_url( $link ) . '" target="_blank" rel="noopener noreferrer">' . $img . '</a>'
			: $img;
	}
	if ( ! $items ) {
		return '';
	}
	return '<div class="card-logos" aria-label="Prihvatamo platne kartice">' . implode( '', $items ) . '</div>';
}

/* ============================================================
   [amelia_placanje_karticama] — statements required by the bank
   ============================================================ */
add_shortcode( 'amelia_placanje_karticama', function () {
	$company   = function_exists( 'amelia_company' ) ? amelia_company() : [];
	$seller    = $company['name'] ?? get_bloginfo( 'name' );
	$processor = trim( (string) get_theme_mod( 'amelia_card_processor' ) );
	$processor = '' !== $processor ? $processor : '[naziv banke]';

	ob_start();
	?>
	<div class="card-statements">
		<h2>Izjava o zaštiti podataka o platnim karticama</h2>
		<p>Prilikom unošenja podataka o platnoj kartici, poverljive informacije se prenose putem javne mreže u zaštićenoj (kriptovanoj) formi, upotrebom SSL protokola i PKI sistema, kao trenutno najsavremenije kriptografske tehnologije.</p>
		<p>Sigurnost podataka prilikom kupovine garantuje procesor platnih kartica, <?php echo esc_html( $processor ); ?>, pa se kompletan proces naplate obavlja na stranicama banke. Podaci o vašoj platnoj kartici ni u jednom trenutku nisu dostupni našem sistemu.</p>

		<h2>Izjava o konverziji</h2>
		<p>Sva plaćanja biće izvršena u lokalnoj valuti Republike Srbije, dinaru (RSD). Za informativni prikaz cena u drugim valutama koristi se srednji kurs Narodne banke Srbije. Iznos za koji će biti zadužena vaša platna kartica biće izražen u vašoj lokalnoj valuti kroz konverziju po kursu koji koriste kartičarske organizacije, a koji nama u trenutku transakcije ne može biti poznat. Kao rezultat ove konverzije postoji mogućnost neznatne razlike od originalne cene navedene na našem sajtu.</p>

		<h2>Povraćaj sredstava</h2>
		<p>U slučaju vraćanja robe i povraćaja sredstava kupcu koji je prethodno platio nekom od platnih kartica, delimično ili u celosti, a bez obzira na razlog vraćanja, <?php echo esc_html( $seller ); ?> je u obavezi da povraćaj vrši isključivo preko iste platne kartice kojom je plaćanje izvršeno. To znači da će banka, na zahtev prodavca, obaviti povraćaj sredstava na račun korisnika kartice.</p>
	</div>
	<?php
	return ob_get_clean();
} );
