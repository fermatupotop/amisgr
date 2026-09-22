/**
 * Мобильное меню. Работает на всех страницах.
 */
(function () {
	'use strict';

	var burger = document.querySelector( '.burger' ),
		nav = document.getElementById( 'amis-nav' );

	if ( ! burger || ! nav ) {
		return;
	}

	burger.addEventListener( 'click', function () {
		var open = nav.classList.toggle( 'is-open' );
		burger.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
	} );

	// Esc закрывает меню — привычное поведение.
	document.addEventListener( 'keydown', function ( e ) {
		if ( 'Escape' === e.key && nav.classList.contains( 'is-open' ) ) {
			nav.classList.remove( 'is-open' );
			burger.setAttribute( 'aria-expanded', 'false' );
			burger.focus();
		}
	} );
})();
