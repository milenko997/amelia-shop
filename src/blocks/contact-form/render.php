<?php
/**
 * Server-side render for amelia/contact-form. Fields, messages and submissions: inc/contact-form.php.
 *
 * @var array $attributes Block attributes.
 */
defined( 'ABSPATH' ) || exit;

$title       = $attributes['title'] ?? 'Pišite nam';
$description = $attributes['description'] ?? '';
$button_text = $attributes['buttonText'] ?? 'Pošalji poruku';
$uid         = wp_unique_id( 'cf-' );
$privacy_url = get_privacy_policy_url();
$messages    = amelia_contact_form_messages();
$fields      = amelia_contact_form_fields();

// Result of a submission without JavaScript (?kontakt=poslato|polja|istekla|limit|slanje).
$status = isset( $_GET['kontakt'] ) ? sanitize_key( $_GET['kontakt'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
if ( ! isset( $messages[ $status ] ) ) {
	$status = '';
}

// Two fields per row, the message on its own.
$rows = [ [ 'name', 'email' ], [ 'phone', 'order' ], [ 'message' ] ];
?>
<section <?php echo get_block_wrapper_attributes( [ 'class' => 'contact-form-block', 'id' => 'kontakt-forma' ] ); ?>>
	<div class="contact-form-block__inner">
		<?php if ( $title ) : ?>
			<h2 class="contact-form-block__title"><?php echo wp_kses_post( $title ); ?></h2>
		<?php endif; ?>
		<?php if ( $description ) : ?>
			<p class="contact-form-block__desc"><?php echo wp_kses_post( $description ); ?></p>
		<?php endif; ?>

		<form class="contact-form" data-amelia-contact method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" novalidate>
			<input type="hidden" name="action" value="amelia_contact">
			<input type="hidden" name="redirect" value="<?php echo esc_url( get_permalink() ?: home_url( '/' ) ); ?>">
			<?php amelia_contact_form_token_fields(); ?>

			<?php // Honeypot: hidden from people, bots fill it in. ?>
			<div class="contact-form__hp" aria-hidden="true">
				<label for="<?php echo esc_attr( $uid ); ?>-website">Ne popunjavajte ovo polje</label>
				<input type="text" id="<?php echo esc_attr( $uid ); ?>-website" name="website" tabindex="-1" autocomplete="off">
			</div>

			<div class="contact-form__status" role="status" aria-live="polite"<?php echo $status ? '' : ' hidden'; ?>>
				<?php if ( $status ) : ?>
					<p class="contact-form__notice contact-form__notice--<?php echo 'poslato' === $status ? 'success' : 'error'; ?>"><?php echo esc_html( $messages[ $status ] ); ?></p>
				<?php endif; ?>
			</div>

			<?php foreach ( $rows as $row ) : ?>
			<div class="contact-form__row<?php echo 1 === count( $row ) ? ' contact-form__row--full' : ''; ?>">
				<?php foreach ( $row as $key ) : ?>
					<?php
					$field = $fields[ $key ];
					$id    = "{$uid}-{$key}";
					$attrs = sprintf(
						'id="%1$s" name="%2$s" autocomplete="%3$s" maxlength="%4$d" aria-describedby="%1$s-error"%5$s%6$s',
						esc_attr( $id ),
						esc_attr( $key ),
						esc_attr( $field['autocomplete'] ),
						(int) $field['max'],
						$field['required'] ? ' required' : '',
						isset( $field['min'] ) ? sprintf( ' minlength="%d"', (int) $field['min'] ) : ''
					);
					?>
				<div class="contact-form__field">
					<label for="<?php echo esc_attr( $id ); ?>">
						<?php echo esc_html( $field['label'] ); ?>
						<?php if ( $field['required'] ) : ?>
							<span class="required" aria-hidden="true">*</span>
						<?php else : ?>
							<span class="contact-form__optional">(opciono)</span>
						<?php endif; ?>
					</label>
					<?php if ( 'textarea' === $field['type'] ) : ?>
						<textarea rows="6" <?php echo $attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above ?>></textarea>
					<?php else : ?>
						<input type="<?php echo esc_attr( $field['type'] ); ?>" <?php echo $attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above ?>>
					<?php endif; ?>
					<span class="contact-form__error" id="<?php echo esc_attr( $id ); ?>-error"></span>
				</div>
				<?php endforeach; ?>
			</div>
			<?php endforeach; ?>

			<div class="contact-form__footer">
				<button type="submit" class="btn contact-form__submit"><?php echo esc_html( $button_text ); ?></button>
				<p class="contact-form__privacy">
					Podatke iz forme koristimo samo da bismo vam odgovorili<?php
					if ( $privacy_url ) {
						echo ', u skladu sa <a href="' . esc_url( $privacy_url ) . '">Politikom privatnosti</a>';
					}
					?>.
				</p>
			</div>
		</form>
	</div>
</section>
