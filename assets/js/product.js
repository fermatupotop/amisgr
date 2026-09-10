(function () {
	'use strict';

	/**
	 * Скопировано на каждый .gallery отдельно — на странице товара их
	 * может быть две (фото прибора и скриншоты экрана), и переключение
	 * в одной не должно задевать слайды другой.
	 */
	document.querySelectorAll( '.gallery' ).forEach( function ( gallery ) {

		var thumbs = gallery.querySelectorAll( '.gal-thumb' ),
			slides = gallery.querySelectorAll( '.gal-slide' );

		if ( ! thumbs.length ) {
			return;
		}

		function show( index ) {
			slides.forEach( function ( s ) {
				s.classList.toggle( 'on', s.dataset.slide === index );
			} );
			thumbs.forEach( function ( t ) {
				t.classList.toggle( 'on', t.dataset.slide === index );
			} );
		}

		thumbs.forEach( function ( t ) {
			t.addEventListener( 'click', function () {
				show( t.dataset.slide );
			} );
		} );
	} );
})();

/**
 * Лайтбокс для скриншотов/фото экрана (лента .screens в single-product.php).
 * Свой, без библиотек — не тянуть PhotoSwipe ради одной ленты картинок.
 * Элементы группируются по data-lightbox, чтобы «предыдущее/следующее»
 * ходило по своей ленте, если их на странице несколько.
 */
(function () {
	'use strict';

	var triggers = document.querySelectorAll( '[data-lightbox]' );

	if ( ! triggers.length ) {
		return;
	}

	var groups = {};

	triggers.forEach( function ( el ) {
		var key = el.dataset.lightbox;
		( groups[ key ] = groups[ key ] || [] ).push( el );
	} );

	var overlay = document.createElement( 'div' );
	overlay.className = 'lightbox';
	overlay.hidden = true;
	overlay.innerHTML =
		'<button type="button" class="lightbox__close" aria-label="Закрыть">&times;</button>' +
		'<button type="button" class="lightbox__nav lightbox__nav--prev" aria-label="Предыдущее фото">&lsaquo;</button>' +
		'<img class="lightbox__img" alt="">' +
		'<button type="button" class="lightbox__nav lightbox__nav--next" aria-label="Следующее фото">&rsaquo;</button>';
	document.body.appendChild( overlay );

	var imgEl  = overlay.querySelector( '.lightbox__img' ),
		prevEl = overlay.querySelector( '.lightbox__nav--prev' ),
		nextEl = overlay.querySelector( '.lightbox__nav--next' ),
		current = [],
		index   = 0;

	function show( i ) {
		index = ( i + current.length ) % current.length;
		var el = current[ index ],
			pic = el.querySelector( 'img' );
		imgEl.src = el.getAttribute( 'href' );
		imgEl.alt = pic ? pic.alt : '';
	}

	function open( group, startEl ) {
		current = groups[ group ];
		prevEl.hidden = nextEl.hidden = current.length < 2;
		show( current.indexOf( startEl ) );
		overlay.hidden = false;
		document.body.classList.add( 'lightbox-open' );
	}

	function close() {
		overlay.hidden = true;
		imgEl.src = '';
		document.body.classList.remove( 'lightbox-open' );
	}

	triggers.forEach( function ( el ) {
		el.addEventListener( 'click', function ( e ) {
			e.preventDefault();
			open( el.dataset.lightbox, el );
		} );
	} );

	overlay.addEventListener( 'click', function ( e ) {
		if ( e.target === overlay ) {
			close();
		}
	} );

	overlay.querySelector( '.lightbox__close' ).addEventListener( 'click', close );
	prevEl.addEventListener( 'click', function () { show( index - 1 ); } );
	nextEl.addEventListener( 'click', function () { show( index + 1 ); } );

	document.addEventListener( 'keydown', function ( e ) {
		if ( overlay.hidden ) {
			return;
		}
		if ( 'Escape' === e.key ) {
			close();
		} else if ( 'ArrowLeft' === e.key ) {
			show( index - 1 );
		} else if ( 'ArrowRight' === e.key ) {
			show( index + 1 );
		}
	} );
})();