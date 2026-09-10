<?php
/**
 * Выборки товаров для главной страницы.
 *
 * Здесь собраны все обращения к базе. Держать их отдельно от вёрстки —
 * привычка, которая окупается: когда понадобится изменить условие отбора,
 * вы правите одну функцию, а не ищете WP_Query по всем шаблонам.
 *
 * @package amis
 */

defined( 'ABSPATH' ) || exit;

/**
 * Товары в наличии на складе.
 *
 * WC_Product_Query — обёртка WooCommerce над WP_Query. Она понимает
 * товарные поля напрямую ('stock_status'), без ручных meta_query,
 * и возвращает готовые объекты WC_Product, а не посты.
 *
 * @param int $limit Сколько товаров вернуть.
 * @return WC_Product[]
 */
function amis_get_instock_products( $limit = 4 ) {

	if ( ! function_exists( 'wc_get_products' ) ) {
		return array(); // WooCommerce выключен — не роняем сайт.
	}

	return wc_get_products( array(
		'status'       => 'publish',
		'limit'        => $limit,
		'stock_status' => 'instock',
		'featured'     => true,      // Отмечайте звёздочкой в списке товаров.
		'orderby'      => 'date',
		'order'        => 'DESC',
	) );
}

/**
 * Осциллографы для сравнительной таблицы, по возрастанию полосы пропускания.
 *
 * Полоса лежит в атрибуте pa_bandwidth как текст («100 МГц»), поэтому
 * сортировать по нему нельзя — «1 ГГц» окажется раньше «70 МГц».
 * Решение: числовое поле amis_bandwidth_mhz, куда пишем полосу в мегагерцах.
 * Без подчёркивания в начале имени намеренно — поле с подчёркивания
 * WordPress считает служебным и прячет из блока «Произвольные поля»
 * в админке, редактировать такое неудобно.
 *
 * @param int $limit Количество строк таблицы.
 * @return WC_Product[]
 */
function amis_get_scopes_by_bandwidth( $limit = 5 ) {

	if ( ! function_exists( 'wc_get_products' ) ) {
		return array();
	}

	return wc_get_products( array(
		'status'     => 'publish',
		'limit'      => $limit,
		'category'   => array( 'oscilloscopes' ), // Слаг категории.
		'meta_key'   => 'amis_bandwidth_mhz',
		'orderby'    => 'meta_value_num',
		'order'      => 'ASC',
	) );
}

/**
 * Значение атрибута товара одной строкой.
 *
 * get_attribute() возвращает значения через запятую и уже
 * экранировать их не нужно — но в шаблоне всё равно оборачиваем в esc_html().
 *
 * @param WC_Product $product   Товар.
 * @param string     $attribute Слаг атрибута без префикса pa_.
 * @param string     $fallback  Что показать, если атрибут не заполнен.
 * @return string
 */
function amis_attr( $product, $attribute, $fallback = '—' ) {

	if ( ! $product instanceof WC_Product ) {
		return $fallback;
	}

	$value = $product->get_attribute( 'pa_' . $attribute );
	
	if ( ! $value ) {
	$value = $product->get_attribute( $attribute );
	}

	return '' !== $value ? $value : $fallback;
}

/**
 * Наличие товара в человеческом виде: статус, класс и текст.
 *
 * @param WC_Product $product Товар.
 * @return array{class:string,label:string}
 */
function amis_stock_state( $product ) {

	if ( ! $product instanceof WC_Product || ! $product->is_in_stock() ) {
		return array(
			'class' => 'avail avail--o',
			'label' => __( 'Под заказ', 'amis' ),
		);
	}

	$qty = $product->get_stock_quantity();

	if ( null !== $qty && $qty > 0 ) {
		return array(
			'class' => 'avail',
			/* translators: %d — количество на складе. */
			'label' => sprintf( __( 'На складе, %d шт.', 'amis' ), (int) $qty ),
		);
	}

	return array(
		'class' => 'avail',
		'label' => __( 'На складе', 'amis' ),
	);
}

/**
 * Категории верхнего уровня каталога.
 *
 * @param int $limit Сколько категорий вывести.
 * @return WP_Term[]
 */
function amis_get_top_categories( $limit = 6 ) {

	// «Misc» — служебная категория по умолчанию для товаров без темы,
	// не настоящий раздел каталога. Исключаем до применения $limit,
	// чтобы список не терял место под неё.
	$exclude = array();
	$misc    = get_term_by( 'slug', 'misc', 'product_cat' );

	if ( $misc ) {
		$exclude[] = $misc->term_id;
	}

	$terms = get_terms( array(
		'taxonomy'   => 'product_cat',
		'parent'     => 0,
		'hide_empty' => false,
		'number'     => $limit,
		'orderby'    => 'menu_order',
		'exclude'    => $exclude,
	) );

	return is_wp_error( $terms ) ? array() : $terms;
}
