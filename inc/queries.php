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
 * Фильтр «В наличии» на архиве каталога (/shop/ и страницы категорий).
 * Переключается ссылкой с ?instock=1 в woocommerce/archive-product.php —
 * без формы и JS, поэтому правим сразу основной запрос архива.
 *
 * @param WP_Query $query Основной запрос страницы.
 */
function amis_shop_instock_filter( $query ) {

	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}

	if ( ! function_exists( 'is_shop' ) || ! ( is_shop() || is_product_taxonomy() ) ) {
		return;
	}

	if ( empty( $_GET['instock'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- обычный GET-фильтр витрины, без сохранения состояния.
		return;
	}

	$meta_query   = (array) $query->get( 'meta_query' );
	$meta_query[] = array(
		'key'   => '_stock_status',
		'value' => 'instock',
	);

	$query->set( 'meta_query', $meta_query );
}
add_action( 'pre_get_posts', 'amis_shop_instock_filter' );

/**
 * Фильтры «Бренд» и «Серия» на архиве каталога — тот же приём, что и
 * «В наличии»: обычные ссылки с ?brand=/?series=, без формы и JS,
 * правим основной запрос напрямую через tax_query.
 *
 * @param WP_Query $query Основной запрос страницы.
 */
function amis_shop_facet_filter( $query ) {

	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}

	if ( ! function_exists( 'is_shop' ) || ! ( is_shop() || is_product_taxonomy() ) ) {
		return;
	}

	$tax_query = (array) $query->get( 'tax_query' );
	$added     = false;

	$brand = isset( $_GET['brand'] ) ? sanitize_title( wp_unslash( $_GET['brand'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- обычный GET-фильтр витрины, без сохранения состояния.

	if ( $brand && taxonomy_exists( 'product_brand' ) ) {
		$tax_query[] = array(
			'taxonomy' => 'product_brand',
			'field'    => 'slug',
			'terms'    => $brand,
		);
		$added       = true;
	}

	$series = isset( $_GET['series'] ) ? sanitize_title( wp_unslash( $_GET['series'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- обычный GET-фильтр витрины, без сохранения состояния.

	if ( $series && taxonomy_exists( 'pa_series' ) ) {
		$tax_query[] = array(
			'taxonomy' => 'pa_series',
			'field'    => 'slug',
			'terms'    => $series,
		);
		$added       = true;
	}

	if ( ! $added ) {
		return;
	}

	if ( count( $tax_query ) > 1 && empty( $tax_query['relation'] ) ) {
		$tax_query['relation'] = 'AND';
	}

	$query->set( 'tax_query', $tax_query );
}
add_action( 'pre_get_posts', 'amis_shop_facet_filter' );

/**
 * Значения таксономии (бренд/серия), которые реально встречаются у
 * товаров текущей категории — а не все бренды/серии сайта. На /shop/
 * (без категории) берём по всему каталогу.
 *
 * @param string $taxonomy Слаг таксономии ('product_brand' или 'pa_series').
 * @return WP_Term[]
 */
function amis_get_archive_facet_terms( $taxonomy ) {

	if ( ! taxonomy_exists( $taxonomy ) ) {
		return array();
	}

	$args = array(
		'post_type'      => 'product',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'no_found_rows'  => true,
	);

	if ( function_exists( 'is_product_category' ) && ( is_product_category() || is_product_tag() ) ) {
		$queried             = get_queried_object();
		$args['tax_query'][] = array(
			'taxonomy' => $queried->taxonomy,
			'field'    => 'term_id',
			'terms'    => $queried->term_id,
		);
	}

	$product_ids = get_posts( $args );

	if ( ! $product_ids ) {
		return array();
	}

	$terms = wp_get_object_terms( $product_ids, $taxonomy );

	if ( is_wp_error( $terms ) || ! $terms ) {
		return array();
	}

	$unique = array();
	foreach ( $terms as $term ) {
		$unique[ $term->term_id ] = $term;
	}

	usort(
		$unique,
		static function ( $a, $b ) {
			return strcasecmp( $a->name, $b->name );
		}
	);

	return array_values( $unique );
}

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
 * Решение: поле «Значение для сортировки» (_amis_sort_value) из вкладки
 * «Данные АМИС» в блоке товара, куда пишем полосу в мегагерцах —
 * см. inc/product-fields.php.
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
		'meta_key'   => '_amis_sort_value',
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
 * На карточке товара `woocommerce_template_single_add_to_cart()`
 * (woocommerce/single-product.php) сама выводит штатный блок
 * `.stock.out-of-stock` («Нет на складе») — прямо под нашим собственным
 * блоком наличия чуть выше, где уже есть «Под заказ» и срок поставки
 * из _amis_lead_time. Отключаем штатный, чтобы не дублировать и не
 * показывать сухую формулировку рядом с более информативной своей.
 */
add_filter( 'woocommerce_get_stock_html', '__return_empty_string' );

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
