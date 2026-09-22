<?php
/**
 * Массовое «пересохранение» товаров.
 *
 * После некоторых импортов часть товаров пропадала из каталога/фильтров —
 * помогало только открыть карточку товара в админке и нажать «Сохранить
 * атрибуты» на вкладке «Атрибуты» (не просто «Обновить» всей карточкой —
 * этого одного было недостаточно).
 *
 * Причина в самом WooCommerce: обычный wc_get_product()->save() пишет в
 * базу только то, что реально помечено «изменённым» у объекта товара
 * (внутренний диff-механизм WC_Data). Если объект просто загружен и тут
 * же сохранён без единого set_...(), WooCommerce считает, что атрибуты не
 * менялись, и даже не трогает их связи с таксономией — это и есть
 * причина, по которой одно только программное save() ничего не чинило.
 * Кнопка «Сохранить атрибуты» в админке работает иначе: она всегда
 * пересобирает связи заново, какие бы значения ни стояли. Здесь — то же
 * самое для всех товаров сразу: для каждого атрибута-таксономии на
 * товаре термины снимаются и ставятся заново (wp_set_object_terms) —
 * это гарантированно пересчитывает связи и счётчики, а не полагается на
 * то, заметит ли WooCommerce «изменение». Обычный save() тоже вызывается
 * следом — на случай других несинхронизированных полей (видимость в
 * каталоге и т.п.).
 *
 * @package amis
 */

defined( 'ABSPATH' ) || exit;

/**
 * Пункт меню — под «Товары».
 */
function amis_product_resave_menu() {

	add_submenu_page(
		'edit.php?post_type=product',
		__( 'Пересохранить все товары', 'amis' ),
		__( 'Пересохранить товары', 'amis' ),
		'manage_woocommerce',
		'amis-product-resave',
		'amis_product_resave_page'
	);
}
add_action( 'admin_menu', 'amis_product_resave_menu' );

/**
 * Пересобирает связи с таксономиями-атрибутами у одного товара: снимает
 * и заново ставит термины для каждого атрибута с is_taxonomy=1 в
 * _product_attributes. Именно это делает кнопка «Сохранить атрибуты» в
 * админке — в отличие от обычного $product->save(), здесь ничего не
 * зависит от того, посчитал ли WooCommerce данные «изменившимися».
 *
 * @param int $product_id ID товара.
 */
function amis_product_resave_reset_attribute_terms( $product_id ) {

	$attributes_meta = get_post_meta( $product_id, '_product_attributes', true );

	if ( ! is_array( $attributes_meta ) ) {
		return;
	}

	foreach ( $attributes_meta as $taxonomy => $attr ) {

		if ( empty( $attr['is_taxonomy'] ) || ! taxonomy_exists( $taxonomy ) ) {
			continue;
		}

		$term_ids = wp_get_object_terms( $product_id, $taxonomy, array( 'fields' => 'ids' ) );

		if ( is_wp_error( $term_ids ) || ! $term_ids ) {
			continue;
		}

		// Сначала снять, потом поставить заново — гарантированная пересборка
		// связей и счётчиков термина, а не «то же самое — нечего делать».
		wp_set_object_terms( $product_id, array(), $taxonomy );
		wp_set_object_terms( $product_id, $term_ids, $taxonomy );
	}
}

/**
 * Проход по всем товарам: пересборка связей атрибутов + wc_get_product()->save().
 *
 * @return array{done:int,errors:string[]}
 */
function amis_product_resave_run() {

	if ( ! function_exists( 'wc_get_products' ) ) {
		return array( 'done' => 0, 'errors' => array( __( 'WooCommerce выключен.', 'amis' ) ) );
	}

	set_time_limit( 0 ); // Сотни товаров с пересборкой связей — может выйти за обычный лимит хостинга.

	$ids = wc_get_products( array(
		'status' => array( 'publish', 'draft', 'pending', 'private' ),
		'limit'  => -1,
		'return' => 'ids',
	) );

	$done   = 0;
	$errors = array();

	foreach ( $ids as $id ) {

		$product = wc_get_product( $id );

		if ( ! $product ) {
			$errors[] = sprintf( 'ID %d: %s', $id, __( 'не удалось загрузить товар.', 'amis' ) );
			continue;
		}

		amis_product_resave_reset_attribute_terms( $id );
		$product->save();
		++$done;
	}

	return array(
		'done'   => $done,
		'errors' => $errors,
	);
}

/**
 * Страница в админке: кнопка запуска + отчёт.
 */
function amis_product_resave_page() {

	if ( ! current_user_can( 'manage_woocommerce' ) ) {
		wp_die( esc_html__( 'Недостаточно прав.', 'amis' ) );
	}

	$result = null;

	if ( isset( $_POST['amis_product_resave_nonce'] )
		&& wp_verify_nonce( sanitize_key( $_POST['amis_product_resave_nonce'] ), 'amis_product_resave' )
	) {
		$result = amis_product_resave_run();
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Пересохранить все товары', 'amis' ); ?></h1>

		<p>
			<?php esc_html_e( 'Проходит по всем товарам и программно делает то же, что вручную — «Сохранить атрибуты» на вкладке «Атрибуты» плюс «Обновить» всей карточкой: пересобирает связи атрибутов-таксономий, видимость в каталоге и кэш товара. Ничего в данных не меняет, только пересинхронизирует то, что уже сохранено.', 'amis' ); ?>
		</p>
		<p>
			<strong><?php esc_html_e( 'Когда нужно:', 'amis' ); ?></strong>
			<?php esc_html_e( 'если товар корректно выглядит в карточке (атрибуты/наличие проставлены), но не появляется в каталоге или фильтрах, пока его не открыть и не сохранить вручную — обычно это часть товаров после массового импорта.', 'amis' ); ?>
		</p>
		<p>
			<?php esc_html_e( 'На большом каталоге может занять минуту-две — дождитесь отчёта, не закрывайте страницу.', 'amis' ); ?>
		</p>

		<form method="post">
			<?php wp_nonce_field( 'amis_product_resave', 'amis_product_resave_nonce' ); ?>
			<?php submit_button( __( 'Пересохранить все товары сейчас', 'amis' ) ); ?>
		</form>

		<?php if ( null !== $result ) : ?>

			<div class="notice notice-success">
				<p>
					<?php
					printf(
						/* translators: %d — количество пересохранённых товаров. */
						esc_html__( 'Пересохранено товаров: %d.', 'amis' ),
						(int) $result['done']
					);
					?>
				</p>
			</div>

			<?php if ( ! empty( $result['errors'] ) ) : ?>
				<div class="notice notice-error">
					<p><strong><?php esc_html_e( 'Ошибки:', 'amis' ); ?></strong></p>
					<ul>
						<?php foreach ( $result['errors'] as $error ) : ?>
							<li><?php echo esc_html( $error ); ?></li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>

		<?php endif; ?>
	</div>
	<?php
}
