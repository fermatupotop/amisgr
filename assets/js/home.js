/**
 * Частотная шкала главной страницы.
 * Никаких зависимостей: jQuery здесь не нужен.
 */
(function () {
	'use strict';

	var bands = document.querySelectorAll( '.band' );
	if ( ! bands.length ) {
		return;
	}

	var elBand  = document.getElementById( 'axBand' ),
		elCount = document.getElementById( 'axCount' ),
		elWord  = document.getElementById( 'axWord' ),
		elChips = document.getElementById( 'axChips' ),
		elLink  = document.getElementById( 'axLink' );

	/**
	 * Склонение существительного после числительного.
	 * 1 прибор / 2 прибора / 5 приборов
	 */
	function plural( n, forms ) {
		var n10 = n % 10,
			n100 = n % 100;

		if ( n10 === 1 && n100 !== 11 ) {
			return forms[0];
		}
		if ( n10 >= 2 && n10 <= 4 && ( n100 < 10 || n100 >= 20 ) ) {
			return forms[1];
		}
		return forms[2];
	}

	function render( btn ) {
		Array.prototype.forEach.call( bands, function ( b ) {
			b.setAttribute( 'aria-selected', 'false' );
		} );
		btn.setAttribute( 'aria-selected', 'true' );

		var count = parseInt( btn.dataset.count, 10 ) || 0;

		if ( elBand ) {
			elBand.textContent = btn.querySelector( 'em' ).textContent;
		}
		if ( elCount ) {
			elCount.textContent = count;
		}
		if ( elWord ) {
			elWord.textContent = plural( count, [ 'прибор', 'прибора', 'приборов' ] );
		}
		if ( elLink && btn.dataset.url ) {
			elLink.href = btn.dataset.url;
		}

		if ( elChips ) {
			elChips.innerHTML = '';
			( btn.dataset.cats || '' ).split( '|' ).forEach( function ( c ) {
				if ( ! c ) {
					return;
				}
				var a = document.createElement( 'a' );
				a.className = 'chip';
				a.href = btn.dataset.url || '#';
				a.textContent = c;
				elChips.appendChild( a );
			} );
		}
	}

	Array.prototype.forEach.call( bands, function ( b ) {
		b.addEventListener( 'click', function () {
			render( b );
		} );
	} );

	render( document.querySelector( '.band[aria-selected="true"]' ) || bands[0] );
})();
