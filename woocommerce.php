<?php
get_header();

$is_shop_archive = is_shop() || is_product_category() || is_product_tag() || is_product_taxonomy();

if ( $is_shop_archive ) :

	$term = get_queried_object();
	if ( is_product_category() && $term && isset( $term->name ) ) {
		$shop_title = $term->name;
		$shop_desc  = $term->description ?? '';
	} else {
		$shop_page  = get_post( wc_get_page_id( 'shop' ) );
		$shop_title = $shop_page ? $shop_page->post_title : __( 'Prodavnica', 'amelia-shop' );
		$shop_desc  = $shop_page ? $shop_page->post_excerpt : '';
	}

	$price_range   = amelia_get_price_range( is_product_category() ? (int) get_queried_object_id() : 0 );
	$product_cats  = amelia_get_product_categories();

	remove_action( 'woocommerce_before_main_content', 'amelia_wc_before_content', 10 );
	remove_action( 'woocommerce_after_main_content',  'amelia_wc_after_content',  10 );
	remove_action( 'woocommerce_archive_description', 'woocommerce_taxonomy_archive_description', 10 );
	remove_action( 'woocommerce_archive_description', 'woocommerce_product_archive_description',  10 );
	remove_action( 'woocommerce_before_shop_loop',    'woocommerce_result_count',      20 );
	remove_action( 'woocommerce_before_shop_loop',    'woocommerce_catalog_ordering',  30 );

	global $wp_query;
	$found_posts   = $wp_query->found_posts ?? 0;
	$has_more_init = ( $wp_query->max_num_pages ?? 1 ) > 1;
	?>

	<div class="shop-hero">
		<div class="container">
			<?php woocommerce_breadcrumb(); ?>
			<h1 class="shop-hero__title"><?php echo esc_html( $shop_title ); ?></h1>
			<?php if ( $shop_desc ) : ?>
				<p class="shop-hero__desc"><?php echo wp_kses_post( $shop_desc ); ?></p>
			<?php endif; ?>
		</div>
	</div>

	<main id="main" class="site-main">
		<div class="container">
			<div class="shop-layout">
				<aside class="shop-sidebar" id="shop-sidebar">
					<form id="shop-filter-form" class="shop-filter" novalidate>

						<?php if ( ! empty( $product_cats ) && ! is_product_category() ) : ?>
						<div class="filter-group">
							<h3 class="filter-title"><?php esc_html_e( 'Kategorije', 'amelia-shop' ); ?></h3>
							<ul class="filter-cats">
								<?php foreach ( $product_cats as $cat ) : ?>
								<li>
									<label class="filter-check">
										<input type="checkbox" name="categories[]" value="<?php echo esc_attr( $cat->term_id ); ?>">
										<span class="filter-check__label"><?php echo esc_html( $cat->name ); ?></span>
										<span class="filter-check__count"><?php echo absint( $cat->count ); ?></span>
									</label>
								</li>
								<?php endforeach; ?>
							</ul>
						</div>
						<?php endif; ?>

						<div class="filter-group">
							<h3 class="filter-title"><?php esc_html_e( 'Cena', 'amelia-shop' ); ?></h3>
							<div class="filter-price">
								<div class="filter-price__track">
									<div class="filter-price__fill" id="price-fill"></div>
								</div>
								<div class="filter-price__inputs">
									<input type="range" id="price-min" class="price-range-thumb"
										min="<?php echo esc_attr( $price_range['min'] ); ?>"
										max="<?php echo esc_attr( $price_range['max'] ); ?>"
										value="<?php echo esc_attr( $price_range['min'] ); ?>"
										step="<?php echo esc_attr( $price_range['step'] ); ?>">
									<input type="range" id="price-max" class="price-range-thumb"
										min="<?php echo esc_attr( $price_range['min'] ); ?>"
										max="<?php echo esc_attr( $price_range['max'] ); ?>"
										value="<?php echo esc_attr( $price_range['max'] ); ?>"
										step="<?php echo esc_attr( $price_range['step'] ); ?>">
								</div>
								<div class="filter-price__labels">
									<span id="price-min-label"><?php echo esc_html( $price_range['min'] ); ?></span>
									<span>–</span>
									<span id="price-max-label"><?php echo esc_html( $price_range['max'] ); ?></span>
								</div>
							</div>
						</div>

						<button type="button" id="filter-reset" class="btn btn-outline filter-reset">
							<?php esc_html_e( 'Resetuj filtere', 'amelia-shop' ); ?>
						</button>

					</form>
				</aside>

				<div class="shop-content">
					<div class="shop-toolbar">
						<span id="shop-result-count" class="shop-result-count">
							<?php printf(
								esc_html( _n( '%d proizvod', '%d proizvoda', $found_posts, 'amelia-shop' ) ),
								(int) $found_posts
							); ?>
						</span>
						<select id="shop-sort" class="shop-sort-select">
							<option value="date:DESC"><?php esc_html_e( 'Najnovije',              'amelia-shop' ); ?></option>
							<option value="price:ASC"><?php esc_html_e( 'Cena: niska → visoka',  'amelia-shop' ); ?></option>
							<option value="price:DESC"><?php esc_html_e( 'Cena: visoka → niska', 'amelia-shop' ); ?></option>
							<option value="title:ASC"><?php esc_html_e( 'Naziv: A → Z',          'amelia-shop' ); ?></option>
						</select>
					</div>

					<div id="shop-products" class="shop-products-wrap"
						data-total="<?php echo esc_attr( $found_posts ); ?>"
						data-has-more="<?php echo $has_more_init ? '1' : '0'; ?>">
						<?php if ( woocommerce_product_loop() ) : ?>
							<?php woocommerce_product_loop_start(); ?>
							<?php while ( have_posts() ) : the_post(); ?>
								<?php wc_get_template_part( 'content', 'product' ); ?>
							<?php endwhile; ?>
							<?php woocommerce_product_loop_end(); ?>
						<?php else : ?>
							<div class="shop-empty">
								<div class="shop-empty__icon">
									<svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
										<circle cx="24" cy="9" r="3"/>
										<line x1="24" y1="12" x2="24" y2="18"/>
										<path d="M24 18 L6 30 H42 Z"/>
									</svg>
								</div>
								<h3 class="shop-empty__title"><?php esc_html_e( 'Nema pronađenih proizvoda', 'amelia-shop' ); ?></h3>
								<p class="shop-empty__text"><?php esc_html_e( 'Trenutno nema dostupnih proizvoda.', 'amelia-shop' ); ?></p>
							</div>
						<?php endif; ?>
					</div>
					<div id="shop-load-sentinel" class="shop-load-sentinel"></div>

				</div>

			</div>
		</div>
	</main>
<?php else : ?>
	<?php woocommerce_content(); ?>
<?php endif;

get_footer();
