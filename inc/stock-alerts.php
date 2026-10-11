<?php
/**
 * "Obavesti me kad stigne": visitors leave an email on an out-of-stock product or variation
 * and get one email when it is back in stock. The address is deleted right after sending.
 *
 * - Form on the single product page (simple products, sold-out variable products and,
 *   via src/js/modules/stock-alert.js, the selected out-of-stock variation)
 * - Subscriptions in the {prefix}amelia_stock_alerts table
 * - Emails sent in the background through WooCommerce's Action Scheduler
 * - "Čekaju obaveštenje" box on the product edit screen
 *
 * Spam protection reuses the contact form's signed timestamp and honeypot (inc/contact-form.php).
 */
defined( 'ABSPATH' ) || exit;

const AMELIA_STOCK_ALERTS_DB_VERSION = '1';

function amelia_stock_alerts_table() {
	global $wpdb;
	return $wpdb->prefix . 'amelia_stock_alerts';
}

/* ============================================================
   Database table (created once, on the first request after deploy)
   ============================================================ */
add_action( 'init', function () {
	if ( get_option( 'amelia_stock_alerts_db' ) === AMELIA_STOCK_ALERTS_DB_VERSION ) {
		return;
	}

	global $wpdb;
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';

	$table = amelia_stock_alerts_table();
	dbDelta( "CREATE TABLE {$table} (
		id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		product_id bigint(20) unsigned NOT NULL,
		email varchar(100) NOT NULL,
		created_at datetime NOT NULL,
		PRIMARY KEY  (id),
		UNIQUE KEY product_email (product_id,email),
		KEY product_id (product_id)
	) {$wpdb->get_charset_collate()};" );

	update_option( 'amelia_stock_alerts_db', AMELIA_STOCK_ALERTS_DB_VERSION );
} );

/* ============================================================
   Frontend form
   ============================================================ */
add_action( 'woocommerce_single_product_summary', function () {
	global $product;
	if ( ! $product instanceof WC_Product ) {
		return;
	}

	$is_variable = $product->is_type( 'variable' );

	// Simple (or fully sold-out variable) product: subscribe to the product itself, form visible.
	// Variable product with stock: hidden form, shown by JS when the chosen variation is sold out.
	if ( $product->is_in_stock() && ! $is_variable ) {
		return;
	}
	$for_variation = $is_variable && $product->is_in_stock();

	$status = isset( $_GET['obavestenje'] ) ? sanitize_key( $_GET['obavestenje'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$texts  = amelia_stock_alert_messages();
	$uid    = wp_unique_id( 'sa-' );
	?>
	<div class="stock-alert" id="obavesti-me" data-stock-alert<?php echo $for_variation && ! $status ? ' hidden' : ''; ?>>
		<p class="stock-alert__title">Nema na stanju? Javićemo vam čim stigne.</p>
		<form class="stock-alert__form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" novalidate>
			<input type="hidden" name="action" value="amelia_stock_alert">
			<input type="hidden" name="product_id" value="<?php echo esc_attr( $for_variation ? '' : $product->get_id() ); ?>">
			<input type="hidden" name="redirect" value="<?php echo esc_url( get_permalink( $product->get_id() ) ); ?>">
			<?php amelia_contact_form_token_fields(); ?>
			<div class="contact-form__hp" aria-hidden="true">
				<label for="<?php echo esc_attr( $uid ); ?>-website">Ne popunjavajte ovo polje</label>
				<input type="text" id="<?php echo esc_attr( $uid ); ?>-website" name="website" tabindex="-1" autocomplete="off">
			</div>
			<label class="screen-reader-text" for="<?php echo esc_attr( $uid ); ?>-email">Email adresa</label>
			<input type="email" id="<?php echo esc_attr( $uid ); ?>-email" name="email" required maxlength="100" autocomplete="email" placeholder="Vaša email adresa">
			<button type="submit" class="btn stock-alert__submit">Obavesti me</button>
		</form>
		<div class="stock-alert__status" role="status" aria-live="polite">
			<?php if ( isset( $texts[ $status ] ) ) : ?>
				<p class="stock-alert__message stock-alert__message--<?php echo 'prijavljeno' === $status ? 'success' : 'error'; ?>"><?php echo esc_html( $texts[ $status ] ); ?></p>
			<?php endif; ?>
		</div>
		<p class="stock-alert__note">
			Email koristimo samo za ovo obaveštenje i brišemo ga čim ga pošaljemo<?php
			if ( get_privacy_policy_url() ) {
				echo ' (<a href="' . esc_url( get_privacy_policy_url() ) . '">Politika privatnosti</a>)';
			}
			?>.
		</p>
	</div>
	<?php
}, 31 );

function amelia_stock_alert_messages() {
	return [
		'prijavljeno' => 'Hvala! Poslaćemo vam email čim proizvod bude ponovo na stanju.',
		'email'       => 'Unesite ispravnu email adresu.',
		'proizvod'    => 'Izaberite veličinu i boju kojih nema na stanju, pa pokušajte ponovo.',
		'istekla'     => 'Forma je istekla. Osvežite stranicu i pokušajte ponovo.',
		'limit'       => 'Previše prijava u kratkom roku. Pokušajte ponovo za nekoliko minuta.',
	];
}

/* ============================================================
   Subscription handler (fetch and plain form post)
   ============================================================ */
function amelia_stock_alert_respond( $result ) {
	// phpcs:disable WordPress.Security.NonceVerification.Missing -- signed timestamp + honeypot, see file header.
	$message = amelia_stock_alert_messages()[ $result ];
	if ( ! empty( $_POST['ajax'] ) ) {
		'prijavljeno' === $result
			? wp_send_json_success( [ 'message' => $message ] )
			: wp_send_json_error( [ 'message' => $message ], 422 );
	}
	$posted = isset( $_POST['redirect'] ) ? esc_url_raw( wp_unslash( $_POST['redirect'] ) ) : '';
	$back   = wp_validate_redirect( $posted, wp_get_referer() ?: home_url( '/' ) );
	// phpcs:enable
	wp_safe_redirect( add_query_arg( 'obavestenje', $result, remove_query_arg( 'obavestenje', $back ) ) . '#obavesti-me' );
	exit;
}

function amelia_handle_stock_alert() {
	// phpcs:disable WordPress.Security.NonceVerification.Missing
	$ts = isset( $_POST['ts'] ) ? (int) $_POST['ts'] : 0;
	if ( ! empty( $_POST['website'] ) || ( time() - $ts ) < AMELIA_CONTACT_MIN_SECONDS ) {
		amelia_stock_alert_respond( 'prijavljeno' ); // bot: pretend it worked
	}
	$sig = isset( $_POST['sig'] ) ? sanitize_text_field( wp_unslash( $_POST['sig'] ) ) : '';
	if ( ! hash_equals( wp_hash( 'amelia_contact|' . $ts ), $sig ) || ( time() - $ts ) > AMELIA_CONTACT_MAX_AGE ) {
		amelia_stock_alert_respond( 'istekla' );
	}

	$email      = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	$product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
	// phpcs:enable

	if ( ! is_email( $email ) ) {
		amelia_stock_alert_respond( 'email' );
	}

	// Only real, published, currently sold-out products or variations.
	$product = $product_id ? wc_get_product( $product_id ) : null;
	$parent  = $product && $product->get_parent_id() ? wc_get_product( $product->get_parent_id() ) : $product;
	if ( ! $product || ! $parent || 'publish' !== $parent->get_status() || $product->is_in_stock() ) {
		amelia_stock_alert_respond( 'proizvod' );
	}

	$ip_key = 'amelia_sa_' . md5( amelia_contact_form_ip() );
	$count  = (int) get_transient( $ip_key );
	if ( $count >= 10 ) {
		amelia_stock_alert_respond( 'limit' );
	}
	set_transient( $ip_key, $count + 1, 10 * MINUTE_IN_SECONDS );

	global $wpdb;
	// INSERT IGNORE: subscribing twice to the same product is fine and stores one row.
	$wpdb->query( $wpdb->prepare( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		'INSERT IGNORE INTO ' . amelia_stock_alerts_table() . ' (product_id, email, created_at) VALUES (%d, %s, %s)',
		$product_id,
		strtolower( $email ),
		current_time( 'mysql', true )
	) );

	amelia_stock_alert_respond( 'prijavljeno' );
}
add_action( 'admin_post_nopriv_amelia_stock_alert', 'amelia_handle_stock_alert' );
add_action( 'admin_post_amelia_stock_alert', 'amelia_handle_stock_alert' );

/* ============================================================
   Back in stock → send emails in the background
   ============================================================ */
function amelia_stock_alert_on_stock_change( $product_id, $stock_status ) {
	// "onbackorder" counts as available too (is_in_stock() is true for it).
	if ( 'outofstock' === $stock_status || ! function_exists( 'as_enqueue_async_action' ) ) {
		return;
	}

	global $wpdb;
	$waiting = (int) $wpdb->get_var( $wpdb->prepare( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		'SELECT COUNT(*) FROM ' . amelia_stock_alerts_table() . ' WHERE product_id = %d',
		$product_id
	) );
	// One pending job per product: repeated stock changes must not send the same emails twice.
	if ( $waiting && ! as_has_scheduled_action( 'amelia_send_stock_alerts', [ (int) $product_id ], 'amelia' ) ) {
		as_enqueue_async_action( 'amelia_send_stock_alerts', [ (int) $product_id ], 'amelia' );
	}
}
add_action( 'woocommerce_product_set_stock_status', 'amelia_stock_alert_on_stock_change', 10, 2 );
add_action( 'woocommerce_variation_set_stock_status', 'amelia_stock_alert_on_stock_change', 10, 2 );

add_action( 'amelia_send_stock_alerts', function ( $product_id ) {
	$product = wc_get_product( $product_id );
	if ( ! $product || ! $product->is_in_stock() ) {
		return; // sold out again before the job ran; keep the subscriptions
	}

	global $wpdb;
	$table = amelia_stock_alerts_table();
	$rows  = $wpdb->get_results( $wpdb->prepare( "SELECT id, email FROM {$table} WHERE product_id = %d LIMIT 200", $product_id ) ); // phpcs:ignore WordPress.DB

	$name    = wp_strip_all_tags( $product->get_name() ); // variations include size/color, e.g. "Gaćice - M, Crna"
	$url     = $product->get_permalink();
	$subject = sprintf( 'Ponovo na stanju: %s', $name );
	$body    = amelia_stock_alert_email_html( $product, $name, $url );

	$sent = 0;
	foreach ( $rows as $row ) {
		if ( wp_mail( $row->email, $subject, $body, [ 'Content-Type: text/html; charset=UTF-8' ] ) ) {
			$wpdb->delete( $table, [ 'id' => $row->id ], [ '%d' ] ); // phpcs:ignore WordPress.DB
			++$sent;
		}
	}

	// More than one batch waiting: continue in a new background job. Stop when nothing could be
	// sent (mail broken), otherwise the same failing rows would be re-queued forever.
	if ( 200 === count( $rows ) && $sent > 0 ) {
		as_enqueue_async_action( 'amelia_send_stock_alerts', [ (int) $product_id ], 'amelia' );
	}
} );

function amelia_stock_alert_email_html( WC_Product $product, $name, $url ) {
	$parent   = $product->get_parent_id() ? wc_get_product( $product->get_parent_id() ) : null;
	$image_id = $product->get_image_id() ?: ( $parent ? $parent->get_image_id() : 0 );
	$image    = $image_id ? wp_get_attachment_image_url( $image_id, 'amelia-product' ) : '';
	$site     = get_bloginfo( 'name' );

	ob_start();
	?>
	<div style="background:#faf8f7;padding:32px 16px;font-family:Arial,Helvetica,sans-serif;color:#2c2c2c;">
		<div style="max-width:520px;margin:0 auto;background:#ffffff;border-radius:12px;padding:32px;text-align:center;">
			<p style="margin:0 0 8px;font-size:13px;letter-spacing:1px;text-transform:uppercase;color:#a8787a;"><?php echo esc_html( $site ); ?></p>
			<h1 style="margin:0 0 16px;font-size:22px;">Dobra vest, ponovo je na stanju!</h1>
			<?php if ( $image ) : ?>
				<img src="<?php echo esc_url( $image ); ?>" alt="" width="200" style="display:block;margin:0 auto 16px;border-radius:8px;max-width:100%;">
			<?php endif; ?>
			<p style="margin:0 0 24px;font-size:16px;"><strong><?php echo esc_html( $name ); ?></strong> je ponovo dostupan. Količine su ograničene, pa požurite.</p>
			<a href="<?php echo esc_url( $url ); ?>" style="display:inline-block;background:#c8a4a5;color:#ffffff;text-decoration:none;font-weight:bold;padding:12px 28px;border-radius:999px;">Pogledaj proizvod</a>
			<p style="margin:24px 0 0;font-size:12px;color:#7a7a7a;">Ovo je jednokratno obaveštenje koje ste zatražili na našem sajtu. Vašu email adresu smo nakon slanja obrisali.</p>
		</div>
	</div>
	<?php
	return ob_get_clean();
}

/* ============================================================
   Admin — who is waiting on this product
   ============================================================ */
add_action( 'add_meta_boxes_product', function ( $post ) {
	add_meta_box( 'amelia-stock-alerts', 'Čekaju obaveštenje', function () use ( $post ) {
		global $wpdb;
		$product = wc_get_product( $post->ID );
		$ids     = $product ? array_merge( [ $post->ID ], $product->get_children() ) : [ $post->ID ];
		$in      = implode( ',', array_map( 'absint', $ids ) );
		$rows    = $wpdb->get_results( 'SELECT product_id, COUNT(*) AS waiting FROM ' . amelia_stock_alerts_table() . " WHERE product_id IN ({$in}) GROUP BY product_id" ); // phpcs:ignore WordPress.DB

		if ( ! $rows ) {
			echo '<p>Niko trenutno ne čeka obaveštenje za ovaj proizvod.</p>';
			return;
		}
		echo '<ul>';
		foreach ( $rows as $row ) {
			$item = wc_get_product( $row->product_id );
			printf( '<li>%s: <strong>%d</strong></li>', esc_html( $item ? wp_strip_all_tags( $item->get_name() ) : '#' . $row->product_id ), (int) $row->waiting );
		}
		echo '</ul><p class="description">Mejlovi se šalju automatski kada proizvod ili varijacija ponovo dođe na stanje.</p>';
	}, 'product', 'side' );
} );
