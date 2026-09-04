/**
 * Выбор PDF через медиабиблиотеку WordPress.
 */
jQuery( function ( $ ) {
	'use strict';

	var frame = null;

	$( '.amis-docs-fields' ).on( 'click', '.amis-doc-select', function ( e ) {
		e.preventDefault();

		var $row = $( this ).closest( '.amis-doc-row' );

		// Каждый раз новое окно: иначе выбор запомнится от прошлой строки.
		frame = wp.media( {
			title: 'Выберите PDF',
			library: { type: 'application/pdf' },
			button: { text: 'Прикрепить' },
			multiple: false
		} );

		frame.on( 'select', function () {
			var file = frame.state().get( 'selection' ).first().toJSON();

			$row.find( '.amis-doc-id' ).val( file.id );
			$row.find( '.amis-doc-filename' ).text( file.filename );
			$row.find( '.amis-doc-remove' ).show();
		} );

		frame.open();
	} );

	$( '.amis-docs-fields' ).on( 'click', '.amis-doc-remove', function ( e ) {
		e.preventDefault();

		var $row = $( this ).closest( '.amis-doc-row' );

		$row.find( '.amis-doc-id' ).val( '' );
		$row.find( '.amis-doc-filename' ).text( '' );
		$( this ).hide();
	} );
} );