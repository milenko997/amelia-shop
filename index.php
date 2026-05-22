<?php get_header(); ?>

<main id="main" class="site-main">

	<!-- Hero -->
	<section class="hero">
		<div class="hero-content">
			<span class="hero-badge"><?php esc_html_e( 'New Collection 2025', 'amelia-shop' ); ?></span>
			<h1><?php esc_html_e( 'Feel Beautiful,', 'amelia-shop' ); ?><br><em><?php esc_html_e( 'Every Single Day', 'amelia-shop' ); ?></em></h1>
			<p><?php esc_html_e( 'Discover our curated collection of lingerie, underwear, socks and pajamas designed for comfort and elegance.', 'amelia-shop' ); ?></p>
			<div class="hero-actions">
				<?php if ( class_exists( 'WooCommerce' ) ) : ?>
				<a href="<?php echo esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ); ?>" class="btn">
					<?php esc_html_e( 'Shop Now', 'amelia-shop' ); ?>
				</a>
				<?php endif; ?>
				<a href="#categories" class="btn btn-outline">
					<?php esc_html_e( 'Browse Categories', 'amelia-shop' ); ?>
				</a>
			</div>
		</div>
	</section>

	<!-- Categories -->
	<section id="categories" class="section section-alt">
		<div class="container">
			<div class="section-header">
				<span class="eyebrow"><?php esc_html_e( 'Collections', 'amelia-shop' ); ?></span>
				<h2><?php esc_html_e( 'Shop by Category', 'amelia-shop' ); ?></h2>
				<p><?php esc_html_e( 'From everyday essentials to special occasion pieces — find your perfect fit.', 'amelia-shop' ); ?></p>
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
						'lingerie'  => '🩱',
						'underwear' => '🩲',
						'bras'      => '👙',
						'socks'     => '🧦',
						'pajamas'   => '🌙',
						'default'   => '✨',
					];

					if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) {
						foreach ( $categories as $cat ) {
							$icon = $cat_icons[ sanitize_title( $cat->name ) ] ?? $cat_icons['default'];
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
									<span><?php echo esc_html( $cat->count ); ?> <?php esc_html_e( 'products', 'amelia-shop' ); ?></span>
								</div>
							</a>
							<?php
						}
					} else {
						$defaults = [
							[ 'Lingerie',  '🩱', '#' ],
							[ 'Underwear', '🩲', '#' ],
							[ 'Bras',      '👙', '#' ],
							[ 'Socks',     '🧦', '#' ],
							[ 'Pajamas',   '🌙', '#' ],
							[ 'Sale',      '✨', '#' ],
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

	<!-- Featured Products -->
	<?php if ( class_exists( 'WooCommerce' ) ) : ?>
	<section class="section">
		<div class="container">
			<div class="section-header">
				<span class="eyebrow"><?php esc_html_e( 'Bestsellers', 'amelia-shop' ); ?></span>
				<h2><?php esc_html_e( 'Most Loved Pieces', 'amelia-shop' ); ?></h2>
				<p><?php esc_html_e( 'Our customers cannot stop raving about these.', 'amelia-shop' ); ?></p>
			</div>
			<?php
			echo do_shortcode( '[products limit="8" columns="4" orderby="popularity"]' );
			?>
			<div style="text-align:center;margin-top:2.5rem;">
				<a href="<?php echo esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ); ?>" class="btn btn-outline">
					<?php esc_html_e( 'View All Products', 'amelia-shop' ); ?>
				</a>
			</div>
		</div>
	</section>

	<!-- New Arrivals -->
	<section class="section section-alt">
		<div class="container">
			<div class="section-header">
				<span class="eyebrow"><?php esc_html_e( 'Just In', 'amelia-shop' ); ?></span>
				<h2><?php esc_html_e( 'New Arrivals', 'amelia-shop' ); ?></h2>
			</div>
			<?php echo do_shortcode( '[products limit="4" columns="4" orderby="date" order="DESC"]' ); ?>
		</div>
	</section>
	<?php endif; ?>

	<!-- Blog Posts (if any) -->
	<?php
	$blog_posts = new WP_Query( [
		'post_type'      => 'post',
		'posts_per_page' => 3,
		'post_status'    => 'publish',
	] );

	if ( $blog_posts->have_posts() ) : ?>
	<section class="section">
		<div class="container">
			<div class="section-header">
				<span class="eyebrow"><?php esc_html_e( 'Journal', 'amelia-shop' ); ?></span>
				<h2><?php esc_html_e( 'Style & Care Tips', 'amelia-shop' ); ?></h2>
			</div>
			<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:2rem;">
				<?php while ( $blog_posts->have_posts() ) : $blog_posts->the_post(); ?>
				<article style="background:var(--color-white);border-radius:var(--radius-lg);overflow:hidden;box-shadow:var(--shadow-sm);">
					<?php if ( has_post_thumbnail() ) : ?>
					<a href="<?php the_permalink(); ?>">
						<?php the_post_thumbnail( 'medium_large', [ 'style' => 'width:100%;height:220px;object-fit:cover;' ] ); ?>
					</a>
					<?php endif; ?>
					<div style="padding:1.5rem;">
						<h3 style="font-size:1.1rem;margin-bottom:.5rem;"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
						<p style="font-size:.875rem;color:var(--color-text-light);"><?php the_excerpt(); ?></p>
						<a href="<?php the_permalink(); ?>" style="font-size:.8rem;font-weight:700;color:var(--color-primary-dark);text-transform:uppercase;letter-spacing:.06em;"><?php esc_html_e( 'Read More →', 'amelia-shop' ); ?></a>
					</div>
				</article>
				<?php endwhile; wp_reset_postdata(); ?>
			</div>
		</div>
	</section>
	<?php endif; ?>

</main>

<?php get_footer(); ?>
