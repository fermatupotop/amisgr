(function () {
	'use strict';

	var thumbs = document.querySelectorAll( '.gal-thumb' ),
		slides = document.querySelectorAll( '.gal-slide' );

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
})();