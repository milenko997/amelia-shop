// Theme SCSS entry — MiniCssExtractPlugin extracts this to build/main.css
import '../scss/main.scss';

import { initMobileMenu }  from './modules/mobile-menu';
import { initSearch }       from './modules/search';
import { initCart }         from './modules/cart';
import { initAnimations }   from './modules/animations';

document.addEventListener( 'DOMContentLoaded', () => {
	initMobileMenu();
	initSearch();
	initCart();
	initAnimations();
} );
