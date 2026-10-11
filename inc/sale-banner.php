<?php
/**
 * Sale banner with a countdown above the header (Customizer → Akcijska traka).
 * Shown only while the sale is running; hides itself when the countdown reaches zero,
 * also on pages served from a cache (src/js/modules/sale-banner.js).
 */
defined( 'ABSPATH' ) || exit;

add_action( 'customize_register', function ( WP_Customize_Manager $wp_customize ) {
	$wp_customize->add_section( 'amelia_sale_banner', [
		'title'       => 'Akcijska traka',
		'description' => 'Traka sa odbrojavanjem iznad header-a, za akcije poput Black Friday-a. Sama nestaje kada akcija istekne.',
		'priority'    => 151,
	] );

	$settings = [
		'amelia_sale_enabled'   => [ 'checkbox', 'Prikaži akcijsku traku', 'wp_validate_boolean', false, '' ],
		'amelia_sale_text'      => [ 'text', 'Tekst akcije', 'sanitize_text_field', 'Black Friday: -30% na sve', 'Npr. „Black Friday: -30% na sve“.' ],
		'amelia_sale_end'       => [ 'datetime-local', 'Akcija traje do', 'amelia_sanitize_datetime_local', '', 'Datum i vreme po srpskom vremenu.' ],
		'amelia_sale_link'      => [ 'url', 'Link (opciono)', 'esc_url_raw', '', 'Npr. adresa kategorije na akciji.' ],
		'amelia_sale_link_text' => [ 'text', 'Tekst linka', 'sanitize_text_field', 'Pogledaj ponudu', '' ],
	];
	foreach ( $settings as $id => [ $type, $label, $sanitize, $default, $description ] ) {
		$wp_customize->add_setting( $id, [ 'default' => $default, 'sanitize_callback' => $sanitize ] );
		$wp_customize->add_control( $id, [
			'section'     => 'amelia_sale_banner',
			'type'        => $type,
			'label'       => $label,
			'description' => $description,
		] );
	}
} );

function amelia_sanitize_datetime_local( $value ) {
	return preg_match( '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/', (string) $value ) ? $value : '';
}

/**
 * End of the running sale, or null when the banner is off or the sale is over.
 */
function amelia_sale_end() {
	$end = get_theme_mod( 'amelia_sale_end' );
	if ( ! get_theme_mod( 'amelia_sale_enabled' ) || ! $end ) {
		return null;
	}
	$date = DateTimeImmutable::createFromFormat( 'Y-m-d\TH:i', $end, wp_timezone() );
	return $date && $date->getTimestamp() > time() ? $date : null;
}

function amelia_sale_banner() {
	$end  = amelia_sale_end();
	$text = trim( (string) get_theme_mod( 'amelia_sale_text', 'Black Friday: -30% na sve' ) );
	if ( ! $end || '' === $text ) {
		return;
	}
	$link      = get_theme_mod( 'amelia_sale_link' );
	$link_text = get_theme_mod( 'amelia_sale_link_text', 'Pogledaj ponudu' );
	?>
	<div class="sale-banner" data-sale-end="<?php echo esc_attr( $end->format( DATE_ATOM ) ); ?>">
		<span class="sale-banner__text"><?php echo esc_html( $text ); ?></span>
		<span class="sale-banner__countdown">
			<?php // Replaced by a live countdown; without JavaScript the end date is shown. ?>
			do <?php echo esc_html( wp_date( 'j.n. \u G:i', $end->getTimestamp() ) ); ?>
		</span>
		<?php if ( $link ) : ?>
			<a class="sale-banner__link" href="<?php echo esc_url( $link ); ?>"><?php echo esc_html( $link_text ); ?></a>
		<?php endif; ?>
	</div>
	<?php
}
