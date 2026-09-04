<?php
/**
 * Комплект поставки: метабокс в админке и вывод на витрине.
 *
 * @package amis
 */

defined( 'ABSPATH' ) || exit;

/**
 * Что добавляется при заказе прибора с поверкой.
 *
 * Состав одинаков для всех приборов, поэтому задан в коде:
 * то, что не меняется, не должно вводиться руками — иначе
 * формулировки со временем разъедутся по карточкам.
 *
 * @return array
 */
function amis_package_verification_items() {

	return array(
		'Свидетельство о поверке — в бумажном виде',
		'Знак поверки на корпусе прибора',
	);
}

/* ==========================================================================
   АДМИНКА
   ========================================================================== */

/**
 * Метабокс под основным редактором.
 */
function amis_package_add_metabox() {

	add_meta_box(
		'amis-product-package',
		__( 'Комплект поставки', 'amis' ),
		'amis_package_metabox_render',
		'product',
		'normal',
		'default'
	);
}
add_action( 'add_meta_boxes', 'amis_package_add_metabox' );

/**
 * Разметка метабокса.
 *
 * @param WP_Post $post Товар.
 */
function amis_package_metabox_render( $post ) {

	wp_nonce_field( 'amis_package_save', 'amis_package_nonce' );

	$base         = get_post_meta( $post->ID, '_amis_package_base', true );
	$request      = get_post_meta( $post->ID, '_amis_package_request', true );
	$verification = get_post_meta( $post->ID, '_amis_package_verification', true );

	// Значения по умолчанию для нового товара — экономят время при сотне позиций.
	if ( '' === $base ) {
		$base = "Руководство по эксплуатации (в электронном виде)\nКабель питания\nГарантийный талон";
	}

	if ( '' === $request ) {
		$request = 'Технический паспорт';
	}
	?>
	<div class="amis-package-fields">

		<p class="amis-package-hint">
			<?php esc_html_e( 'По одной позиции на строку. Пустые строки игнорируются. Если оба списка пусты и поверка не отмечена — блок на странице товара не появится.', 'amis' ); ?>
		</p>

		<div class="amis-package-row">
			<label for="amis_package_base"><strong><?php esc_html_e( 'Базовая комплектация', 'amis' ); ?></strong></label>
			<textarea
				id="amis_package_base"
				name="_amis_package_base"
				rows="6"
				class="widefat"
				placeholder="Руководство по эксплуатации (в электронном виде)&#10;Кабель питания&#10;Пробник PVP2350 — 4 шт.&#10;Кабель USB"
			><?php echo esc_textarea( $base ); ?></textarea>
		</div>

		<div class="amis-package-row">
			<label for="amis_package_request"><strong><?php esc_html_e( 'По запросу', 'amis' ); ?></strong></label>
			<textarea
				id="amis_package_request"
				name="_amis_package_request"
				rows="3"
				class="widefat"
				placeholder="Технический паспорт"
			><?php echo esc_textarea( $request ); ?></textarea>
		</div>

		<div class="amis-package-row">
			<label>
				<input
					type="checkbox"
					name="_amis_package_verification"
					value="1"
					<?php checked( $verification, '1' ); ?>
				>
				<strong><?php esc_html_e( 'Прибор может поставляться с поверкой', 'amis' ); ?></strong>
			</label>

			<p class="amis-package-hint amis-package-hint--indent">
				<?php esc_html_e( 'Поверка оплачивается отдельно. При включении на странице появится блок:', 'amis' ); ?>
				<?php echo esc_html( implode( '; ', amis_package_verification_items() ) ); ?>
			</p>
		</div>

	</div>

	<style>
		.amis-package-row{padding:14px 0;border-bottom:1px solid #f0f0f1}
		.amis-package-row:last-child{border-bottom:0}
		.amis-package-row label{display:block;margin-bottom:6px}
		.amis-package-hint{color:#646970;font-size:13px;margin:0 0 12px}
		.amis-package-hint--indent{margin:6px 0 0 24px}
	</style>
	<?php
}

/**
 * Сохранение.
 *
 * @param int $post_id ID товара.
 */
function amis_package_save( $post_id ) {

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! isset( $_POST['amis_package_nonce'] )
		|| ! wp_verify_nonce( sanitize_key( $_POST['amis_package_nonce'] ), 'amis_package_save' ) ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	foreach ( array( '_amis_package_base', '_amis_package_request' ) as $field ) {

		$value = isset( $_POST[ $field ] )
			? sanitize_textarea_field( wp_unslash( $_POST[ $field ] ) )
			: '';

		if ( '' !== trim( $value ) ) {
			update_post_meta( $post_id, $field, $value );
		} else {
			delete_post_meta( $post_id, $field );
		}
	}

	// Снятый чекбокс просто не приходит в $_POST — отсюда else.
	if ( ! empty( $_POST['_amis_package_verification'] ) ) {
		update_post_meta( $post_id, '_amis_package_verification', '1' );
	} else {
		delete_post_meta( $post_id, '_amis_package_verification' );
	}
}
add_action( 'save_post_product', 'amis_package_save' );

/* ==========================================================================
   ВИТРИНА
   ========================================================================== */

/**
 * Разбор многострочного поля в массив позиций.
 *
 * ltrim снимает маркеры списка: часть людей по привычке
 * начинает строку с дефиса или буллита.
 *
 * @param string $raw Содержимое поля.
 * @return array
 */
function amis_package_parse( $raw ) {

	if ( ! $raw ) {
		return array();
	}

	$lines = preg_split( '/\r\n|\r|\n/', $raw );
	$lines = array_map( 'trim', $lines );

	$lines = array_filter( $lines, function ( $line ) {
		return '' !== $line;
	} );

	return array_map( function ( $line ) {
		return ltrim( $line, "-*• \t" );
	}, $lines );
}

/**
 * Комплект поставки товара, разбитый на группы.
 *
 * @param int $product_id ID товара.
 * @return array Пустой массив, если данных нет.
 */
function amis_get_product_package( $product_id ) {

	$groups = array();

	$base = amis_package_parse( get_post_meta( $product_id, '_amis_package_base', true ) );

	if ( $base ) {
		$groups[] = array(
			'title' => __( 'Базовая комплектация', 'amis' ),
			'note'  => '',
			'mod'   => 'base',
			'items' => $base,
		);
	}

	$request = amis_package_parse( get_post_meta( $product_id, '_amis_package_request', true ) );

	if ( $request ) {
		$groups[] = array(
			'title' => __( 'По запросу', 'amis' ),
			'note'  => __( 'Укажите при оформлении заказа', 'amis' ),
			'mod'   => 'request',
			'items' => $request,
		);
	}

	if ( '1' === get_post_meta( $product_id, '_amis_package_verification', true ) ) {
		$groups[] = array(
			'title' => __( 'При заказе с поверкой', 'amis' ),
			'note'  => __( 'Поверка оплачивается отдельно', 'amis' ),
			'mod'   => 'verification',
			'items' => amis_package_verification_items(),
		);
	}

	return $groups;
}

/**
 * Вывод блока.
 *
 * @param array $groups Результат amis_get_product_package().
 */
function amis_render_package( $groups ) {

	if ( empty( $groups ) ) {
		return;
	}
	?>
	<div class="pack">

		<?php foreach ( $groups as $group ) : ?>
			<div class="pack__group pack__group--<?php echo esc_attr( $group['mod'] ); ?>">

				<div class="pack__head">
					<h3 class="pack__title"><?php echo esc_html( $group['title'] ); ?></h3>
					<?php if ( $group['note'] ) : ?>
						<span class="pack__note"><?php echo esc_html( $group['note'] ); ?></span>
					<?php endif; ?>
				</div>

				<ul class="pack__list">
					<?php foreach ( $group['items'] as $item ) : ?>
						<li><?php echo esc_html( $item ); ?></li>
					<?php endforeach; ?>
				</ul>

			</div>
		<?php endforeach; ?>

		<p class="pack__footer">
			<?php esc_html_e( 'Состав комплекта может незначительно отличаться в зависимости от партии поставки. Точный перечень подтвердим при выставлении счёта.', 'amis' ); ?>
		</p>
	</div>
	<?php
}