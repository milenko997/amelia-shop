// Self-hosted fonts (no requests to Google, which would share visitors' IP addresses).
import '@fontsource/playfair-display/400.css';
import '@fontsource/playfair-display/600.css';
import '@fontsource/playfair-display/700.css';
import '@fontsource/playfair-display/400-italic.css';
import '@fontsource/playfair-display/600-italic.css';
import '@fontsource/lato/300.css';
import '@fontsource/lato/400.css';
import '@fontsource/lato/700.css';

// Theme SCSS entry — MiniCssExtractPlugin extracts this to build/main.css
import '../scss/main.scss';

import { initMobileMenu }  from './modules/mobile-menu';
import { initSearch }       from './modules/search';
import { initCart, initCartPage } from './modules/cart';
import { initAnimations }   from './modules/animations';
import { initQuantity }     from './modules/quantity';
import { initGallery }      from './modules/gallery';
import { initMiniCart }     from './modules/mini-cart';
import { initSwatches }     from './modules/swatches';
import { initSizeGuide }    from './modules/size-guide';
import { initMapConsent }   from './modules/map-consent';

document.addEventListener( 'DOMContentLoaded', () => {
	initMobileMenu();
	initSearch();
	initCart();
	initCartPage();
	initAnimations();
	initQuantity();
	initGallery();
	initMiniCart();
	initSwatches();
	initSizeGuide();
	initMapConsent();
} );
