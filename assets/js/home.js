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
		elLink  = document.getElementById( 'axLink' ),
		wave      = document.querySelector( '.axis-wave' ),
		container = document.querySelector( '.bands' );

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

	/**
	 * Подсветка участка волны над активной кнопкой.
	 */
	function highlightWave( btn ) {
		if ( ! wave || ! container ) {
			return;
		}

		// Координаты берём относительно волны, а не контейнера кнопок:
		// это разные по ширине элементы.
		var waveBox = wave.getBoundingClientRect(),
			btnBox = btn.getBoundingClientRect();

		if ( ! waveBox.width ) {
			return;
		}

		var left = ( btnBox.left - waveBox.left ) / waveBox.width * 100,
			right = ( waveBox.right - btnBox.right ) / waveBox.width * 100;

		wave.style.setProperty( '--wave-left', Math.max( 0, left ) + '%' );
		wave.style.setProperty( '--wave-right', Math.max( 0, right ) + '%' );
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

		highlightWave( btn );
	}

	Array.prototype.forEach.call( bands, function ( b ) {
		b.addEventListener( 'click', function () {
			render( b );
		} );
	} );

	// Пересчёт подсветки при изменении размеров окна.
	// Таймер гасит десятки срабатываний при перетаскивании края.
	var resizeTimer;

	window.addEventListener( 'resize', function () {
		clearTimeout( resizeTimer );
		resizeTimer = setTimeout( function () {
			var active = document.querySelector( '.band[aria-selected="true"]' );
			if ( active ) {
				highlightWave( active );
			}
		}, 150 );
	} );

	render( document.querySelector( '.band[aria-selected="true"]' ) || bands[0] );
})();