<?php
/**
 * Опции и принадлежности: метабокс в админке и вывод на витрине.
 *
 * В отличие от «Комплекта поставки» (inc/product-package.php, простой
 * список строк) — здесь у каждой позиции есть код заказа и отдельное
 * короткое назначение (нужно для линеек с длинным списком опций вида
 * «3657-S07 — автоматическое удаление влияния оснастки — для...»,
 * например Ceyear 3657). Два отдельных поля вместо одного: «Опции
 * прибора» (которые меняют сам прибор под заказ — апгрейды диапазона,
 * портов) и «Общие опции и принадлежности» (калибровочные комплекты,
 * кабели, кейсы и т.п., обычно одинаковые для всей линейки — тот же
 * приём, что у _amis_compare_table/_amis_compare_note: одинаковый текст
 * копируется в CSV-строку каждой модели линейки).
 *
 * @package amis
 */

defined( 'ABSPATH' ) || exit;

/* ==========================================================================
   АДМИНКА
   ========================================================================== */

/**
 * Метабокс под основным редактором.
 */
function amis_options_add_metabox() {

	add_meta_box(
		'amis-product-options',
		__( 'Опции и принадлежности', 'amis' ),
		'amis_options_metabox_render',
		'product',
		'normal',
		'default'
	);
}
add_action( 'add_meta_boxes', 'amis_options_add_metabox' );

/**
 * Разметка метабокса.
 *
 * @param WP_Post $post Товар.
 */
function amis_options_metabox_render( $post ) {

	wp_nonce_field( 'amis_options_save', 'amis_options_nonce' );

	$own    = get_post_meta( $post->ID, '_amis_options_own', true );
	$common = get_post_meta( $post->ID, '_amis_options_common', true );
	?>
	<div class="amis-options-fields">

		<p class="amis-options-hint">
			<?php esc_html_e( 'По одной опции на строку, формат: Код|Название|Назначение. Строки без «|» или пустые — игнорируются.', 'amis' ); ?>
		</p>

		<div class="amis-options-row">
			<label for="amis_options_own"><strong><?php esc_html_e( 'Опции прибора', 'amis' ); ?></strong></label>
			<p class="amis-options-hint amis-options-hint--indent"><?php esc_html_e( 'Меняют сам прибор под заказ — апгрейд диапазона, числа портов и т.п. Обычно свои у каждой модели линейки.', 'amis' ); ?></p>
			<textarea
				id="amis_options_own"
				name="_amis_options_own"
				rows="6"
				class="widefat"
				placeholder="3657B-221|2-порт, расширение до 9 кГц|Снижает нижнюю границу диапазона до 9 кГц&#10;3657B-400|4-портовое измерение|Апгрейд до 4-портового векторного анализатора"
			><?php echo esc_textarea( $own ); ?></textarea>
		</div>

		<div class="amis-options-row">
			<label for="amis_options_common"><strong><?php esc_html_e( 'Общие опции и принадлежности', 'amis' ); ?></strong></label>
			<p class="amis-options-hint amis-options-hint--indent"><?php esc_html_e( 'Калибровочные комплекты, кабели, кейсы и т.п. — обычно один и тот же список для всей линейки, копируется в карточку каждой модели.', 'amis' ); ?></p>
			<textarea
				id="amis_options_common"
				name="_amis_options_common"
				rows="10"
				class="widefat"
				placeholder="3657-005|Алюминиевый транспортировочный кейс|Для перевозки прибора&#10;20205|Механический калибровочный комплект N-type 50 Ом|Калибровка в полосе DC — 3 ГГц"
			><?php echo esc_textarea( $common ); ?></textarea>
		</div>

	</div>

	<style>
		.amis-options-row{padding:14px 0;border-bottom:1px solid #f0f0f1}
		.amis-options-row:last-child{border-bottom:0}
		.amis-options-row label{display:block;margin-bottom:4px}
		.amis-options-hint{color:#646970;font-size:13px;margin:0 0 12px}
		.amis-options-hint--indent{margin:2px 0 8px}
	</style>
	<?php
}

/**
 * Сохранение.
 *
 * @param int $post_id ID товара.
 */
function amis_options_save( $post_id ) {

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! isset( $_POST['amis_options_nonce'] )
		|| ! wp_verify_nonce( sanitize_key( $_POST['amis_options_nonce'] ), 'amis_options_save' ) ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	foreach ( array( '_amis_options_own', '_amis_options_common' ) as $field ) {

		$value = isset( $_POST[ $field ] )
			? sanitize_textarea_field( wp_unslash( $_POST[ $field ] ) )
			: '';

		if ( '' !== trim( $value ) ) {
			update_post_meta( $post_id, $field, $value );
		} else {
			delete_post_meta( $post_id, $field );
		}
	}
}
add_action( 'save_post_product', 'amis_options_save' );

/* ==========================================================================
   ВИТРИНА
   ========================================================================== */

/**
 * Разбор многострочного поля «Код|Название|Назначение» в массив позиций.
 *
 * @param string $raw Содержимое поля.
 * @return array
 */
function amis_options_parse( $raw ) {

	if ( ! $raw ) {
		return array();
	}

	$lines = preg_split( '/\r\n|\r|\n/', $raw );
	$items = array();

	foreach ( $lines as $line ) {

		$line = trim( $line );

		if ( '' === $line || false === strpos( $line, '|' ) ) {
			continue;
		}

		$parts = array_map( 'trim', explode( '|', $line, 3 ) );

		$items[] = array(
			'code' => $parts[0],
			'name' => isset( $parts[1] ) ? $parts[1] : '',
			'desc' => isset( $parts[2] ) ? $parts[2] : '',
		);
	}

	return $items;
}

/**
 * Опции и принадлежности товара, разбитые на группы.
 *
 * @param int $product_id ID товара.
 * @return array Пустой массив, если данных нет.
 */
function amis_get_product_options( $product_id ) {

	$groups = array();

	$own = amis_options_parse( get_post_meta( $product_id, '_amis_options_own', true ) );

	if ( $own ) {
		$groups[] = array(
			'title' => __( 'Опции прибора', 'amis' ),
			'items' => $own,
		);
	}

	$common = amis_options_parse( get_post_meta( $product_id, '_amis_options_common', true ) );

	if ( $common ) {
		$groups[] = array(
			'title' => __( 'Общие опции и принадлежности', 'amis' ),
			'items' => $common,
		);
	}

	return $groups;
}

/**
 * Вывод блока.
 *
 * @param array $groups Результат amis_get_product_options().
 */
function amis_render_options( $groups ) {

	if ( empty( $groups ) ) {
		return;
	}
	?>
	<div class="opts">

		<?php foreach ( $groups as $group ) : ?>
			<div class="opt-group">
				<h3><?php echo esc_html( $group['title'] ); ?></h3>

				<div class="opt-table-wrap">
					<table class="opt-table">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Код', 'amis' ); ?></th>
								<th><?php esc_html_e( 'Название', 'amis' ); ?></th>
								<th><?php esc_html_e( 'Назначение', 'amis' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $group['items'] as $item ) : ?>
								<tr>
									<td><?php echo esc_html( $item['code'] ); ?></td>
									<td><?php echo esc_html( $item['name'] ); ?></td>
									<td><?php echo esc_html( $item['desc'] ); ?></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			</div>
		<?php endforeach; ?>

		<p class="opts__footer">
			<?php esc_html_e( 'Нужная опция не из списка или не уверены, что выбрать, — уточним у инженера при оформлении заявки.', 'amis' ); ?>
		</p>
	</div>
	<?php
}
