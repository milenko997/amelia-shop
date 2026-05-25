<?php
/**
 * Template Name: Početna stranica
 * Template Post Type: page
 *
 * Select this template in the page editor (Page Attributes → Template)
 * to display the full homepage design on any page.
 */

get_header();
?>

<main id="main" class="site-main">

	<!-- Hero banner -->
	<section class="hero">
		<div class="hero-content">
			<span class="hero-badge">Nova kolekcija 2025</span>
			<h1>Oseti se lepo,<br><em>svakog dana</em></h1>
			<p>Otkrijte našu kolekciju donjeg veša, čarapa i pidžama dizajniranih za udobnost i eleganciju.</p>
			<div class="hero-actions">
				<?php if ( class_exists( 'WooCommerce' ) ) : ?>
				<a href="<?php echo esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ); ?>" class="btn">
					Kupuj odmah
				</a>
				<?php endif; ?>
				<a href="#kategorije" class="btn btn-outline">
					Pregledaj kategorije
				</a>
			</div>
		</div>
	</section>

	<!-- Categories -->
	<section id="kategorije" class="section section-alt">
		<div class="container">
			<div class="section-header">
				<span class="eyebrow">Kolekcije</span>
				<h2>Kupuj po kategoriji</h2>
				<p>Od svakodnevnih esencijala do posebnih priložnosti — pronađite savršenu kombinaciju.</p>
			</div>
			<div class="categories-grid">
				<?php
				if ( class_exists( 'WooCommerce' ) ) {
					$categories = get_terms( [
						'taxonomy'   => 'product_cat',
						'hide_empty' => false,
						'exclude'    => [ get_option( 'default_product_cat' ) ],
						'number'     => 6,
					] );

					$cat_icons = [
						'donji-ves'    => '🩱',
						'lingerie'     => '🩱',
						'donje-rublje' => '🩱',
						'gace'         => '🩲',
						'underwear'    => '🩲',
						'brushalteri'  => '👙',
						'bras'         => '👙',
						'carape'       => '🧦',
						'socks'        => '🧦',
						'pidzame'      => '🌙',
						'pajamas'      => '🌙',
						'default'      => '✨',
					];

					if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) {
						foreach ( $categories as $cat ) {
							$icon      = $cat_icons[ sanitize_title( $cat->name ) ] ?? $cat_icons['default'];
							$thumb_id  = get_term_meta( $cat->term_id, 'thumbnail_id', true );
							$thumb_url = $thumb_id ? wp_get_attachment_image_url( $thumb_id, 'amelia-category' ) : '';
							$url       = get_term_link( $cat );
							?>
							<a href="<?php echo esc_url( $url ); ?>" class="category-card">
								<?php if ( $thumb_url ) : ?>
									<img src="<?php echo esc_url( $thumb_url ); ?>" alt="<?php echo esc_attr( $cat->name ); ?>">
								<?php endif; ?>
								<div class="category-card-overlay">
									<span class="cat-icon"><?php echo $icon; ?></span>
									<h3><?php echo esc_html( $cat->name ); ?></h3>
									<span><?php echo esc_html( $cat->count ); ?> proizvoda</span>
								</div>
							</a>
							<?php
						}
					} else {
						$defaults = [
							[ 'Donji veš',   '🩱', '#' ],
							[ 'Gaće',        '🩲', '#' ],
							[ 'Brushalteri', '👙', '#' ],
							[ 'Čarape',      '🧦', '#' ],
							[ 'Pidžame',     '🌙', '#' ],
							[ 'Rasprodaja',  '✨', '#' ],
						];
						foreach ( $defaults as $d ) {
							echo '<a href="' . esc_url( $d[2] ) . '" class="category-card">';
							echo '<div class="category-card-overlay"><span class="cat-icon">' . $d[1] . '</span><h3>' . esc_html( $d[0] ) . '</h3></div>';
							echo '</a>';
						}
					}
				}
				?>
			</div>
		</div>
	</section>

	<!-- Bestselling products -->
	<?php if ( class_exists( 'WooCommerce' ) ) : ?>
	<section class="section">
		<div class="container">
			<div class="section-header">
				<span class="eyebrow">Najprodavanije</span>
				<h2>Omiljeni komadi</h2>
				<p>Naše mušterije ne prestaju da hvale ove proizvode.</p>
			</div>
			<?php echo do_shortcode( '[products limit="8" columns="4" orderby="popularity"]' ); ?>
			<div style="text-align:center;margin-top:2.5rem;">
				<a href="<?php echo esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ); ?>" class="btn btn-outline">
					Pogledaj sve proizvode
				</a>
			</div>
		</div>
	</section>

	<!-- New arrivals -->
	<section class="section section-alt">
		<div class="container">
			<div class="section-header">
				<span class="eyebrow">Novo u ponudi</span>
				<h2>Novi dolasci</h2>
			</div>
			<?php echo do_shortcode( '[products limit="4" columns="4" orderby="date" order="DESC"]' ); ?>
		</div>
	</section>
	<?php endif; ?>

</main>

<?php get_footer(); ?>
