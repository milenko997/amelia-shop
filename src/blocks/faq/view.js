document.querySelectorAll( '.faq-block__item' ).forEach( ( item ) => {
	const btn    = item.querySelector( '.faq-block__question' );
	const answer = item.querySelector( '.faq-block__answer' );
	if ( ! btn || ! answer ) return;

	btn.addEventListener( 'click', () => {
		const isOpen = btn.getAttribute( 'aria-expanded' ) === 'true';

		item.closest( '.faq-block__list' ).querySelectorAll( '.faq-block__item' ).forEach( ( other ) => {
			if ( other === item ) return;
			other.querySelector( '.faq-block__question' ).setAttribute( 'aria-expanded', 'false' );
			other.querySelector( '.faq-block__answer' ).classList.remove( 'is-open' );
			other.classList.remove( 'is-open' );
		} );

		btn.setAttribute( 'aria-expanded', String( ! isOpen ) );
		answer.classList.toggle( 'is-open', ! isOpen );
		item.classList.toggle( 'is-open', ! isOpen );
	} );
} );
