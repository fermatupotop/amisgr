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

	/**
	 * Шкала «Диапазон частот» с главной (шорткод amis_freq_axis) ссылается
	 * на /shop/?filter_frequency-range=slug — тот же query-var, что у штатного
	 * layered-nav виджета WooCommerce, но у атрибута выключены «Архивы» и
	 * сам виджет на сайте не используется, поэтому core его не фильтрует —
	 * без этого блока ссылка «Открыть подборку» показывала пустой каталог.
	 */
	$freq_range = isset( $_GET['filter_frequency-range'] ) ? sanitize_title( wp_unslash( $_GET['filter_frequency-range'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- обычный GET-фильтр витрины, без сохранения состояния.

	if ( $freq_range && taxonomy_exists( 'pa_frequency-range' ) ) {
		$tax_query[] = array(
			'taxonomy' => 'pa_frequency-range',
			'field'    => 'slug',
			'terms'    => $freq_range,
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
 * Сколько опубликованных товаров сейчас в наличии — для цифры на главной
 * («N приборов в каталоге, из них M на складе»). Раньше M было вписано
 * в тексте вручную и расходилось с реальным складом; здесь — реальный счёт.
 *
 * @return int
 */
function amis_count_instock_products() {

	if ( ! function_exists( 'wc_get_products' ) ) {
		return 0; // WooCommerce выключен — не роняем сайт.
	}

	$ids = wc_get_products( array(
		'status'       => 'publish',
		'limit'        => -1,
		'stock_status' => 'instock',
		'return'       => 'ids',
	) );

	return count( $ids );
}

/**
 * Текст вида «100 МГц»/«13 ГГц» → число в МГц, для сравнения диапазонов.
 * null, если распознать не удалось (пусто, другой формат и т.п.) —
 * такой товар просто не участвует в подсчёте минимума/максимума.
 *
 * @param string $text Значение атрибута как есть.
 * @return float|null
 */
function amis_parse_mhz_text( $text ) {

	$text = trim( (string) $text );

	if ( '' === $text || ! preg_match( '/([\d]+(?:[.,]\d+)?)\s*(ГГц|МГц|GHz|MHz)/iu', $text, $m ) ) {
		return null;
	}

	$number = (float) str_replace( ',', '.', $m[1] );
	$unit   = mb_strtolower( $m[2] );

	if ( in_array( $unit, array( 'ггц', 'ghz' ), true ) ) {
		$number *= 1000;
	}

	return $number;
}

/**
 * Число в МГц → читаемая строка («100 МГц»/«13 ГГц») — обратная операция
 * к amis_parse_mhz_text().
 *
 * @param float $mhz Значение в мегагерцах.
 * @return string
 */
function amis_format_mhz_range_value( $mhz ) {

	if ( $mhz >= 1000 ) {
		$ghz = $mhz / 1000;
		$ghz = floor( $ghz ) === $ghz ? (string) (int) $ghz : (string) round( $ghz, 1 );
		return str_replace( '.', ',', $ghz ) . ' ГГц';
	}

	return ( (int) $mhz ) . ' МГц';
}

/**
 * Просто число из текста, без единицы («4», «2 канала», «8» → 4/2/8).
 * Для атрибутов без единиц измерения (каналы, порты и т.п.) — пара
 * к amis_parse_mhz_text() для тех, что с единицами.
 *
 * @param string $text Значение атрибута как есть.
 * @return float|null
 */
function amis_parse_number_text( $text ) {

	$text = trim( (string) $text );

	if ( '' === $text || ! preg_match( '/([\d]+(?:[.,]\d+)?)/u', $text, $m ) ) {
		return null;
	}

	return (float) str_replace( ',', '.', $m[1] );
}

/**
 * Число → строка, без единицы — обратная операция к amis_parse_number_text().
 *
 * @param float $number Значение.
 * @return string
 */
function amis_format_number_range_value( $number ) {

	return floor( $number ) === $number
		? (string) (int) $number
		: str_replace( '.', ',', (string) $number );
}

/**
 * Категории, у которых часть «Ключевых параметров» на карточке (главная,
 * amis_spec_label/amis_spec_value, значения через «|») можно посчитать по
 * факту — по реальным атрибутам товаров, а не держать текстом руками (тот
 * устаревает по мере пополнения каталога, как было с «214 на складе»).
 *
 * Слаг категории => [индекс в $values (0 — первая пара label/value и т.д.)
 * => [слаг атрибута без pa_, функция «текст → число», функция «число →
 * текст»]]. Единица получается из парсера, поэтому в одну настройку можно
 * объединять только атрибуты в одних и тех же единицах по всему каталогу.
 *
 * @return array
 */
function amis_category_sort_range_config() {
	return array(
		'oscilloscopes' => array(
			0 => array( // Первая пара термина — «Полоса».
				'attribute' => 'bandwidth',
				'parse'     => 'amis_parse_mhz_text',
				'format'    => 'amis_format_mhz_range_value',
			),
			1 => array( // Вторая пара термина — «Каналы».
				'attribute' => 'channels',
				'parse'     => 'amis_parse_number_text',
				'format'    => 'amis_format_number_range_value',
			),
		),
	);
}

/**
 * Диапазоны «мин – макс» по ключевым атрибутам категории (см.
 * amis_category_sort_range_config()) — по индексу в $values термина.
 * Пустой массив, если для категории нет настройки; отдельный индекс
 * просто отсутствует в результате, если ни один товар не распознался —
 * тогда вызывающий код должен оставить на этом месте прежний ручной
 * текст термина, а не показать пустоту.
 *
 * @param WP_Term $term Категория.
 * @return array Индекс => готовая строка диапазона.
 */
function amis_category_sort_ranges( $term ) {

	$config = amis_category_sort_range_config();

	if ( ! isset( $config[ $term->slug ] ) || ! function_exists( 'wc_get_products' ) ) {
		return array();
	}

	$products = wc_get_products( array(
		'status'   => 'publish',
		'limit'    => -1,
		'category' => array( $term->slug ),
	) );

	if ( ! $products ) {
		return array();
	}

	$results = array();

	foreach ( $config[ $term->slug ] as $index => $setup ) {

		if ( ! is_callable( $setup['parse'] ) || ! is_callable( $setup['format'] ) ) {
			continue;
		}

		$values = array();

		foreach ( $products as $product ) {

			$raw = $product->get_attribute( 'pa_' . $setup['attribute'] );

			if ( ! $raw ) {
				$raw = $product->get_attribute( $setup['attribute'] );
			}

			$parsed = call_user_func( $setup['parse'], $raw );

			if ( null !== $parsed ) {
				$values[] = $parsed;
			}
		}

		if ( ! $values ) {
			continue;
		}

		sort( $values );

		$format = $setup['format'];
		$min    = reset( $values );
		$max    = end( $values );

		$results[ $index ] = ( $min === $max )
			? call_user_func( $format, $min )
			: call_user_func( $format, $min ) . ' – ' . call_user_func( $format, $max );
	}

	return $results;
}

/**
 * Дата последнего изменения среди опубликованных записей — для строки
 * «Обновлено: ДД.ММ.ГГГГ» под заголовком «База знаний» на главной.
 * Реальная дата, не вписанная руками (та же логика, что и у количества
 * товаров на складе).
 *
 * @return string|null Дата в формате d.m.Y, null — если записей ещё нет.
 */
function amis_latest_post_update_date() {

	$posts = get_posts( array(
		'post_type'      => 'post',
		'post_status'    => 'publish',
		'posts_per_page' => 1,
		'orderby'        => 'modified',
		'order'          => 'DESC',
		'fields'         => 'ids',
		'no_found_rows'  => true,
	) );

	if ( ! $posts ) {
		return null;
	}

	return get_the_modified_date( 'd.m.Y', $posts[0] );
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
