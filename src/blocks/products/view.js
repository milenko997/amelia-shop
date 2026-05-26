import Swiper from 'swiper';
import { Navigation, Pagination, Autoplay } from 'swiper/modules';
import 'swiper/css';
import 'swiper/css/navigation';
import 'swiper/css/pagination';

document.addEventListener( 'DOMContentLoaded', () => {
	document.querySelectorAll( '.products-swiper' ).forEach( ( el ) => {
		const wrap = el.parentElement;
		new Swiper( el, {
			modules:      [ Navigation, Pagination, Autoplay ],
			slidesPerView: 1,
			spaceBetween:  24,
			loop:          true,
			autoplay: {
				delay:                3500,
				disableOnInteraction: false,
				pauseOnMouseEnter:    true,
			},
			navigation: {
				nextEl: wrap.querySelector( '.swiper-button-next' ),
				prevEl: wrap.querySelector( '.swiper-button-prev' ),
			},
			pagination: {
				el:        wrap.querySelector( '.swiper-pagination' ),
				clickable: true,
			},
			breakpoints: {
				480:  { slidesPerView: 2 },
				768:  { slidesPerView: 4 },
				1024: { slidesPerView: 5 },
			},
		} );
	} );
} );
