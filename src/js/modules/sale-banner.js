/**
 * Sale banner countdown (inc/sale-banner.php): "Još 2d 05:12:09", hidden when the sale ends.
 */
export function initSaleBanner() {
	const banner = document.querySelector( '.sale-banner[data-sale-end]' );
	if ( ! banner ) return;

	const end       = new Date( banner.dataset.saleEnd ).getTime();
	const countdown = banner.querySelector( '.sale-banner__countdown' );
	if ( Number.isNaN( end ) || ! countdown ) return;

	const pad = ( n ) => String( n ).padStart( 2, '0' );

	const tick = () => {
		const left = Math.floor( ( end - Date.now() ) / 1000 );
		if ( left <= 0 ) {
			banner.remove();
			return false;
		}
		const days  = Math.floor( left / 86400 );
		const hours = Math.floor( ( left % 86400 ) / 3600 );
		const mins  = Math.floor( ( left % 3600 ) / 60 );
		const secs  = left % 60;
		countdown.textContent = `Još ${ days > 0 ? days + 'd ' : '' }${ pad( hours ) }:${ pad( mins ) }:${ pad( secs ) }`;
		return true;
	};

	if ( tick() ) {
		const timer = setInterval( () => tick() || clearInterval( timer ), 1000 );
	}
}
