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

/**
 * Отложенная загрузка карты: iframe вставляется по клику.
 */
(function () {
	'use strict';

	var holder = document.getElementById( 'amis-map' );

	if ( ! holder ) {
		return;
	}

	var button = holder.querySelector( '.map-load' );

	if ( ! button ) {
		return;
	}

	button.addEventListener( 'click', function () {
		var frame = document.createElement( 'iframe' );

		frame.src = holder.dataset.src;
		frame.loading = 'lazy';
		frame.allowFullscreen = true;
		frame.title = 'Карта проезда';

		holder.innerHTML = '';
		holder.appendChild( frame );
	} );
})();

/**
 * Уведомление о cookie. Показываем, если пользователь ещё не нажимал
 * «Понятно» — согласие запоминаем в localStorage, без установки cookie
 * ради баннера про cookie.
 */
(function () {
	'use strict';

	var STORAGE_KEY = 'amis_cookie_ack';
	var notice = document.getElementById( 'amis-cookie-notice' );
	var button = document.getElementById( 'amis-cookie-ok' );

	if ( ! notice || ! button ) {
		return;
	}

	try {
		if ( localStorage.getItem( STORAGE_KEY ) ) {
			return;
		}
	} catch ( e ) {
		// Приватный режим/запрет доступа к хранилищу — просто показываем баннер каждый раз.
	}

	notice.classList.add( 'is-visible' );

	button.addEventListener( 'click', function () {
		notice.classList.remove( 'is-visible' );
		try {
			localStorage.setItem( STORAGE_KEY, '1' );
		} catch ( e ) {
			// Не критично: баннер просто будет показываться заново.
		}
	} );
})();