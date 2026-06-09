<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<div id="page" class="site">

	<!-- Promo strip -->
	<div class="promo-strip">
		<div class="promo-items">
			<span class="promo-item">&#10024; Besplatna dostava za narudžbine iznad 50€</span>
			<span class="promo-item">&#128197; Besplatni povraćaj 30 dana</span>
			<span class="promo-item">&#128274; Sigurna kupovina</span>
		</div>
	</div>

	<!-- Site header -->
	<header id="masthead" class="site-header">
		<div class="header-inner">

			<!-- Logo -->
			<div class="site-logo">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
					<?php if ( has_custom_logo() ) : ?>
						<?php the_custom_logo(); ?>
					<?php else : ?>
						<span class="logo-text"><?php bloginfo( 'name' ); ?></span>
					<?php endif; ?>
				</a>
			</div>

			<!-- Primary nav -->
			<nav class="primary-nav" id="primary-nav" aria-label="Glavna navigacija">
				<?php
				wp_nav_menu( [
					'theme_location' => 'primary',
					'menu_class'     => '',
					'container'      => false,
					'fallback_cb'    => 'amelia_fallback_menu',
				] );
				?>
			</nav>

			<!-- Header actions -->
			<div class="header-actions">

				<!-- Search -->
				<button class="header-icon-btn" id="search-toggle" aria-label="Pretraga">
					<svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
				</button>

				<!-- Cart -->
				<?php if ( class_exists( 'WooCommerce' ) ) : ?>
				<a href="<?php echo esc_url( wc_get_cart_url() ); ?>" class="header-icon-btn" aria-label="Korpa">
					<svg viewBox="0 0 24 24"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
					<?php $count = amelia_cart_count(); ?>
					<?php if ( $count > 0 ) : ?>
					<span class="cart-count" id="cart-count"><?php echo esc_html( $count ); ?></span>
					<?php endif; ?>
				</a>
				<?php endif; ?>

				<!-- Mobile toggle -->
				<button class="mobile-menu-toggle" id="mobile-menu-toggle" aria-label="Otvori meni" aria-expanded="false">
					<span></span><span></span><span></span>
				</button>
			</div>
		</div>

		<!-- Search dropdown -->
		<div id="search-dropdown" class="search-dropdown" hidden>
			<div class="container">
				<?php get_search_form(); ?>
			</div>
		</div>
	</header>

<?php
/**
 * Fallback menu — shown when no menu is assigned to the 'primary' location.
 * Auto-detects and uses the first available custom menu if one exists.
 */
function amelia_fallback_menu( $args ) {
	$menus = wp_get_nav_menus();

	if ( ! empty( $menus ) ) {
		$new_args = array_merge( (array) $args, [
			'menu'        => $menus[0]->term_id,
			'fallback_cb' => false,
		] );
		wp_nav_menu( $new_args );
		return;
	}

	// No menu exists — show minimal navigation
	echo '<ul>';
	echo '<li><a href="' . esc_url( home_url( '/' ) ) . '">Početna</a></li>';
	if ( class_exists( 'WooCommerce' ) ) {
		echo '<li><a href="' . esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ) . '">Prodavnica</a></li>';
	}
	echo '</ul>';
}
