export function initAnimations() {
	// Sticky header shadow
	const header = document.getElementById( 'masthead' );
	if ( header ) {
		window.addEventListener(
			'scroll',
			() => header.classList.toggle( 'scrolled', window.scrollY > 50 ),
			{ passive: true }
		);
	}

	// Scroll reveal with IntersectionObserver
	if ( ! ( 'IntersectionObserver' in window ) ) return;

	const targets = document.querySelectorAll(
		'.category-card, .section-header, .feature-item'
	);

	const io = new IntersectionObserver(
		( entries ) => {
			entries.forEach( ( entry ) => {
				if ( ! entry.isIntersecting ) return;
				entry.target.style.opacity   = '1';
				entry.target.style.transform = 'translateY(0)';
				io.unobserve( entry.target );
			} );
		},
		{ threshold: 0.1 }
	);

	targets.forEach( ( el ) => {
		el.style.opacity    = '0';
		el.style.transform  = 'translateY(20px)';
		el.style.transition = 'opacity .5s ease, transform .5s ease';
		io.observe( el );
	} );
}
