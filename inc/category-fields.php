<?php
/**
 * Поля «Ключевые параметры» у категории товаров — обычная админка
 * WooCommerce (Товары → Категории → редактирование), без стороннего
 * плагина Term Meta. Хранится в term meta `amis_spec_label` /
 * `amis_spec_value` (пары через «|») — их же читает карточка категории
 * на главной (templates/template-home.php, блок «Направления каталога»).
 *
 * До этого поля нужно было проставлять руками через сторонний плагин
 * или напрямую в БД (см. README, «Шаг 6») — давно считалось хорошим
 * первым самостоятельным заданием, но так и не было сделано, из-за чего
 * у части категорий (например, «Анализаторы цепей») на карточке главной
 * не было строки с характеристикой вовсе.
 *
 * Поддерживаются 2 пары «параметр — значение» — этого достаточно:
 * у большинства категорий ручная одна пара, у осциллографов (где можно
 * показать две) они вообще считаются по факту из атрибутов товаров
 * (amis_category_sort_ranges(), inc/queries.php) и это поле не трогают.
 *
 * @package amis
 */

defined( 'ABSPATH' ) || exit;

/**
 * Поля на экране «Добавить категорию».
 */
function amis_category_fields_add() {
	?>
	<div class="form-field">
		<label for="amis_spec_label_1"><?php esc_html_e( 'Характеристика 1 — название', 'amis' ); ?></label>
		<input type="text" name="amis_spec_label_1" id="amis_spec_label_1" placeholder="Диапазон частот">
	</div>
	<div class="form-field">
		<label for="amis_spec_value_1"><?php esc_html_e( 'Характеристика 1 — значение', 'amis' ); ?></label>
		<input type="text" name="amis_spec_value_1" id="amis_spec_value_1" placeholder="9 кГц – 110 ГГц">
	</div>
	<div class="form-field">
		<label for="amis_spec_label_2"><?php esc_html_e( 'Характеристика 2 — название (необязательно)', 'amis' ); ?></label>
		<input type="text" name="amis_spec_label_2" id="amis_spec_label_2" placeholder="Порты">
	</div>
	<div class="form-field">
		<label for="amis_spec_value_2"><?php esc_html_e( 'Характеристика 2 — значение', 'amis' ); ?></label>
		<input type="text" name="amis_spec_value_2" id="amis_spec_value_2" placeholder="2–4">
	</div>
	<?php
}
add_action( 'product_cat_add_form_fields', 'amis_category_fields_add' );

/**
 * Поля на экране «Редактировать категорию».
 *
 * @param WP_Term $term Категория.
 */
function amis_category_fields_edit( $term ) {

	$labels = array_filter( explode( '|', (string) get_term_meta( $term->term_id, 'amis_spec_label', true ) ) );
	$values = array_filter( explode( '|', (string) get_term_meta( $term->term_id, 'amis_spec_value', true ) ) );
	$labels = array_values( $labels );
	$values = array_values( $values );
	?>
	<tr class="form-field">
		<th scope="row"><label for="amis_spec_label_1"><?php esc_html_e( 'Характеристика 1 — название', 'amis' ); ?></label></th>
		<td><input type="text" name="amis_spec_label_1" id="amis_spec_label_1" value="<?php echo esc_attr( $labels[0] ?? '' ); ?>" placeholder="Диапазон частот"></td>
	</tr>
	<tr class="form-field">
		<th scope="row"><label for="amis_spec_value_1"><?php esc_html_e( 'Характеристика 1 — значение', 'amis' ); ?></label></th>
		<td><input type="text" name="amis_spec_value_1" id="amis_spec_value_1" value="<?php echo esc_attr( $values[0] ?? '' ); ?>" placeholder="9 кГц – 110 ГГц"></td>
	</tr>
	<tr class="form-field">
		<th scope="row"><label for="amis_spec_label_2"><?php esc_html_e( 'Характеристика 2 — название (необязательно)', 'amis' ); ?></label></th>
		<td><input type="text" name="amis_spec_label_2" id="amis_spec_label_2" value="<?php echo esc_attr( $labels[1] ?? '' ); ?>" placeholder="Порты"></td>
	</tr>
	<tr class="form-field">
		<th scope="row"><label for="amis_spec_value_2"><?php esc_html_e( 'Характеристика 2 — значение', 'amis' ); ?></label></th>
		<td><input type="text" name="amis_spec_value_2" id="amis_spec_value_2" value="<?php echo esc_attr( $values[1] ?? '' ); ?>" placeholder="2–4"></td>
	</tr>
	<?php
	/**
	 * Для категорий с автосчётом (сейчас — только oscilloscopes,
	 * amis_category_sort_range_config() в inc/queries.php) значение этой
	 * пары на карточке всё равно подменяется реальным диапазоном по
	 * товарам — предупреждаем, чтобы не казалось, что поле не сохранилось.
	 */
	$auto_config = function_exists( 'amis_category_sort_range_config' ) ? amis_category_sort_range_config() : array();
	if ( isset( $auto_config[ $term->slug ] ) ) :
		?>
		<tr class="form-field">
			<th scope="row"></th>
			<td>
				<p class="description">
					<?php esc_html_e( 'У этой категории часть значений на главной считается автоматически по товарам (amis_category_sort_range_config() в inc/queries.php) — то, что впишете здесь, может быть переопределено.', 'amis' ); ?>
				</p>
			</td>
		</tr>
	<?php endif; ?>
	<?php
}
add_action( 'product_cat_edit_form_fields', 'amis_category_fields_edit', 10, 1 );

/**
 * Сохранение — оба хука (создание и редактирование категории).
 *
 * @param int $term_id Категория.
 */
function amis_category_fields_save( $term_id ) {

	if ( ! isset( $_POST['amis_spec_label_1'] ) ) {
		return;
	}

	$pairs = array(
		array(
			sanitize_text_field( wp_unslash( $_POST['amis_spec_label_1'] ?? '' ) ),
			sanitize_text_field( wp_unslash( $_POST['amis_spec_value_1'] ?? '' ) ),
		),
		array(
			sanitize_text_field( wp_unslash( $_POST['amis_spec_label_2'] ?? '' ) ),
			sanitize_text_field( wp_unslash( $_POST['amis_spec_value_2'] ?? '' ) ),
		),
	);

	$labels = array();
	$values = array();

	foreach ( $pairs as $pair ) {
		list( $label, $value ) = $pair;
		if ( '' === $label || '' === $value ) {
			continue;
		}
		$labels[] = $label;
		$values[] = $value;
	}

	if ( $labels ) {
		update_term_meta( $term_id, 'amis_spec_label', implode( '|', $labels ) );
		update_term_meta( $term_id, 'amis_spec_value', implode( '|', $values ) );
	} else {
		delete_term_meta( $term_id, 'amis_spec_label' );
		delete_term_meta( $term_id, 'amis_spec_value' );
	}
}
add_action( 'created_product_cat', 'amis_category_fields_save' );
add_action( 'edited_product_cat', 'amis_category_fields_save' );
