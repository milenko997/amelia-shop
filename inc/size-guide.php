<?php
/**
 * Size guide: a table per product category (Proizvodi → Kategorije → edit),
 * shown in a dialog on the single product page.
 *
 * The table is entered as plain text, one row per line, cells separated by "|":
 *   Veličina | Obim struka (cm) | Obim kukova (cm)
 *   S        | 62-66            | 88-92
 * The first row is the header.
 */
defined( 'ABSPATH' ) || exit;

const AMELIA_SIZE_TABLE_META = 'amelia_size_table';
const AMELIA_SIZE_NOTE_META  = 'amelia_size_note';

/* ============================================================
   Admin — fields on the product category screens
   ============================================================ */
function amelia_size_guide_example() {
	return "Veličina | Obim struka (cm) | Obim kukova (cm)\nS | 62-66 | 88-92\nM | 67-71 | 93-97\nL | 72-76 | 98-102\nXL | 77-82 | 103-108";
}

function amelia_size_guide_fields_html( $table = '', $note = '' ) {
	wp_nonce_field( 'amelia_size_guide', 'amelia_size_guide_nonce' );
	?>
	<label for="amelia-size-table"><strong>Tabela veličina</strong></label>
	<textarea id="amelia-size-table" name="amelia_size_table" rows="8" class="large-text code" placeholder="<?php echo esc_attr( amelia_size_guide_example() ); ?>"><?php echo esc_textarea( $table ); ?></textarea>
	<p class="description">
		Jedan red tabele po liniji, kolone razdvojene znakom <code>|</code>. Prvi red je zaglavlje.
		Tabela se prikazuje na svim proizvodima iz ove kategorije i njenih potkategorija.
		Primer je prikazan u polju dok je prazno.
	</p>
	<p>
		<label for="amelia-size-note"><strong>Kako se meri (opciono)</strong></label>
		<textarea id="amelia-size-note" name="amelia_size_note" rows="3" class="large-text" placeholder="Obim struka merite na najužem delu struka, a obim kukova na najširem delu kukova."><?php echo esc_textarea( $note ); ?></textarea>
	</p>
	<?php
}

add_action( 'product_cat_add_form_fields', function () {
	echo '<div class="form-field">';
	amelia_size_guide_fields_html();
	echo '</div>';
} );

add_action( 'product_cat_edit_form_fields', function ( $term ) {
	?>
	<tr class="form-field">
		<th scope="row">Tabela veličina</th>
		<td>
			<?php
			amelia_size_guide_fields_html(
				get_term_meta( $term->term_id, AMELIA_SIZE_TABLE_META, true ),
				get_term_meta( $term->term_id, AMELIA_SIZE_NOTE_META, true )
			);
			?>
		</td>
	</tr>
	<?php
} );

function amelia_size_guide_save( $term_id ) {
	if (
		! isset( $_POST['amelia_size_guide_nonce'] )
		|| ! wp_verify_nonce( sanitize_key( $_POST['amelia_size_guide_nonce'] ), 'amelia_size_guide' )
		|| ! current_user_can( 'manage_product_terms' )
	) {
		return;
	}

	foreach ( [ 'amelia_size_table' => AMELIA_SIZE_TABLE_META, 'amelia_size_note' => AMELIA_SIZE_NOTE_META ] as $field => $meta_key ) {
		$value = isset( $_POST[ $field ] ) ? sanitize_textarea_field( wp_unslash( $_POST[ $field ] ) ) : '';
		if ( '' === trim( $value ) ) {
			delete_term_meta( $term_id, $meta_key );
		} else {
			update_term_meta( $term_id, $meta_key, $value );
		}
	}
}
add_action( 'created_product_cat', 'amelia_size_guide_save' );
add_action( 'edited_product_cat', 'amelia_size_guide_save' );

/* ============================================================
   Frontend
   ============================================================ */

/**
 * Size guide for a product: the first category (or parent category) that has a table.
 *
 * @return array{rows: string[][], note: string}|null
 */
function amelia_get_size_guide( WC_Product $product ) {
	$product_id = $product->get_parent_id() ?: $product->get_id();

	foreach ( wc_get_product_term_ids( $product_id, 'product_cat' ) as $term_id ) {
		foreach ( array_merge( [ $term_id ], get_ancestors( $term_id, 'product_cat', 'taxonomy' ) ) as $id ) {
			$table = (string) get_term_meta( $id, AMELIA_SIZE_TABLE_META, true );
			$rows  = amelia_parse_size_table( $table );
			if ( count( $rows ) > 1 ) {
				return [
					'rows' => $rows,
					'note' => (string) get_term_meta( $id, AMELIA_SIZE_NOTE_META, true ),
				];
			}
		}
	}
	return null;
}

function amelia_parse_size_table( $text ) {
	$rows = [];
	foreach ( preg_split( '/\R/', trim( $text ) ) as $line ) {
		if ( '' === trim( $line ) ) {
			continue;
		}
		$rows[] = array_map( 'trim', explode( '|', $line ) );
	}
	return $rows;
}

add_action( 'woocommerce_before_add_to_cart_form', function () {
	global $product;
	if ( ! $product instanceof WC_Product ) {
		return;
	}

	$guide = amelia_get_size_guide( $product );
	if ( ! $guide ) {
		return;
	}

	$header = array_shift( $guide['rows'] );
	?>
	<button type="button" class="size-guide-toggle" data-size-guide-open aria-haspopup="dialog">
		<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 7h18v10H3z"/><path d="M7 7v4M11 7v3M15 7v4M19 7v3"/></svg>
		Tabela veličina
	</button>

	<dialog class="size-guide" id="size-guide" aria-labelledby="size-guide-title">
		<div class="size-guide__header">
			<h2 class="size-guide__title" id="size-guide-title">Tabela veličina</h2>
			<button type="button" class="size-guide__close" data-size-guide-close aria-label="Zatvori tabelu veličina">
				<svg viewBox="0 0 24 24" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
			</button>
		</div>
		<div class="size-guide__table-wrap">
			<table class="size-guide__table">
				<thead>
					<tr>
						<?php foreach ( $header as $cell ) : ?>
						<th scope="col"><?php echo esc_html( $cell ); ?></th>
						<?php endforeach; ?>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $guide['rows'] as $row ) : ?>
					<tr>
						<?php foreach ( $row as $i => $cell ) : ?>
							<?php if ( 0 === $i ) : ?>
							<th scope="row"><?php echo esc_html( $cell ); ?></th>
							<?php else : ?>
							<td><?php echo esc_html( $cell ); ?></td>
							<?php endif; ?>
						<?php endforeach; ?>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<?php if ( $guide['note'] ) : ?>
		<p class="size-guide__note"><?php echo nl2br( esc_html( $guide['note'] ) ); ?></p>
		<?php endif; ?>
	</dialog>
	<?php
}, 5 );
