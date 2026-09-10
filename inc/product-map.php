<?php
/**
 * Карта характеристик по категориям.
 *
 * У осциллографа и анализатора спектра параметры разные, поэтому
 * для каждой категории описано, какие атрибуты вынести в плитки
 * под галереей и как сгруппировать остальные в таблице.
 *
 * Категории, которых здесь нет, работают по запасному сценарию:
 * первые четыре атрибута — в плитки, все атрибуты — одним блоком.
 * То есть новую категорию можно завести, не трогая этот файл.
 *
 * @package amis
 */

defined( 'ABSPATH' ) || exit;

/**
 * Карта: слаг категории → ключевые параметры и группы.
 *
 * Значения — слаги атрибутов без префикса pa_.
 *
 * `series` (серия — «DS80000», «MSO8000» и т.п.) заведена во всех
 * категориях приборов, кроме probes: серия имеет смысл только вместе
 * с брендом, поэтому это атрибут, а не подкатегория — иначе один бренд
 * распадался бы на несвязанные ветки дерева категорий в каждом типе
 * прибора. Бренд — отдельно, через штатную таксономию WooCommerce
 * «Бренды» (product_brand), не через атрибут.
 *
 * @return array
 */
function amis_spec_map() {

	return array(

		'oscilloscopes' => array(
			'key'    => array( 'bandwidth', 'channels', 'sample-rate', 'adc-bits' ),
			'groups' => array(
				'Вертикальный тракт'  => array( 'bandwidth', 'channels', 'adc-bits', 'sensitivity', 'input-impedance', 'max-input' ),
				'Горизонтальный тракт' => array( 'sample-rate', 'memory', 'timebase', 'capture-rate' ),
				'Запуск и анализ'      => array( 'trigger-types', 'bus-decode', 'math', 'measurements' ),
				'Интерфейсы и общие'   => array( 'series', 'display', 'interfaces', 'power', 'dimensions', 'weight', 'calibration-interval' ),
			),
		),

		'spectrum-analyzers' => array(
			'key'    => array( 'freq-range', 'rbw', 'danl', 'phase-noise' ),
			'groups' => array(
				'Частотные параметры' => array( 'freq-range', 'freq-accuracy', 'span', 'rbw', 'vbw' ),
				'Амплитудные'          => array( 'danl', 'amp-accuracy', 'max-input', 'attenuator' ),
				'Спектральная чистота' => array( 'phase-noise', 'spurious' ),
				'Интерфейсы и общие'   => array( 'series', 'display', 'interfaces', 'power', 'dimensions', 'weight', 'calibration-interval' ),
			),
		),

		'signal-generators' => array(
			'key'    => array( 'freq-range', 'output-level', 'modulation', 'channels' ),
			'groups' => array(
				'Выходной сигнал'    => array( 'freq-range', 'output-level', 'freq-resolution', 'channels' ),
				'Модуляция'          => array( 'modulation', 'modulation-depth', 'internal-source' ),
				'Форма сигнала'      => array( 'waveforms', 'dac-bits', 'sample-rate', 'memory' ),
				'Интерфейсы и общие' => array( 'series', 'display', 'interfaces', 'power', 'dimensions', 'weight', 'calibration-interval' ),
			),
		),

		'power-amplifiers' => array(
			'key'    => array( 'output-power', 'freq-range', 'gain', 'amp-class' ),
			'groups' => array(
				'Усилительный тракт' => array( 'output-power', 'freq-range', 'gain', 'amp-class', 'flatness' ),
				'Нагрузка и защита'  => array( 'vswr', 'protection', 'cooling' ),
				'Интерфейсы и общие' => array( 'series', 'interfaces', 'power', 'dimensions', 'weight', 'calibration-interval' ),
			),
		),

		'power-supplies' => array(
			'key'    => array( 'voltage', 'current', 'power', 'channels' ),
			'groups' => array(
				'Выходные параметры' => array( 'voltage', 'current', 'power', 'channels' ),
				'Точность и шум'     => array( 'resolution', 'accuracy', 'ripple', 'load-regulation' ),
				'Интерфейсы и общие' => array( 'series', 'display', 'interfaces', 'dimensions', 'weight', 'calibration-interval' ),
			),
		),

		'multimeters' => array(
			'key'    => array( 'digits', 'basic-accuracy', 'dc-voltage', 'functions' ),
			'groups' => array(
				'Метрология'         => array( 'digits', 'basic-accuracy', 'resolution' ),
				'Диапазоны'          => array( 'dc-voltage', 'ac-voltage', 'dc-current', 'resistance', 'capacitance', 'frequency' ),
				'Функции'            => array( 'functions', 'measurement-rate' ),
				'Интерфейсы и общие' => array( 'series', 'display', 'interfaces', 'power', 'dimensions', 'weight', 'calibration-interval' ),
			),
		),

		/**
		 * Пробники — не отдельный прибор, а аксессуар к осциллографу.
		 * Набор нарочно короткий: только то, что подтверждено с сайта
		 * RIGOL или необходимо для понимания совместимости — без полей
		 * «на будущее», которые нечем заполнить.
		 */
		'probes' => array(
			'key'    => array( 'bandwidth', 'probe-type', 'connector', 'compatible-series' ),
			'groups' => array(
				'Характеристики' => array( 'bandwidth', 'probe-type' ),
				'Совместимость'  => array( 'connector', 'compatible-series' ),
			),
		),

	);
}

/**
 * Настройки для конкретного товара по его категории.
 *
 * Берётся первая категория товара, для которой есть запись в карте.
 *
 * @param WC_Product $product Товар.
 * @return array|null
 */
function amis_product_map( $product ) {

	$map  = amis_spec_map();
	$cats = wp_get_post_terms( $product->get_id(), 'product_cat', array( 'fields' => 'slugs' ) );

	if ( is_wp_error( $cats ) ) {
		return null;
	}

	foreach ( $cats as $slug ) {
		if ( isset( $map[ $slug ] ) ) {
			return $map[ $slug ];
		}
	}

	return null; // Категории нет в карте — сработает запасной сценарий.
}

/**
 * Все атрибуты товара в виде «подпись => значение».
 *
 * Работает и с глобальными атрибутами (pa_), и с произвольными.
 * Ключом остаётся слаг без префикса — по нему строятся группы.
 *
 * @param WC_Product $product Товар.
 * @return array
 */
function amis_all_attributes( $product ) {

	$result = array();

	foreach ( $product->get_attributes() as $attribute ) {

		$name  = $attribute->get_name();                       // pa_bandwidth или bandwidth.
		$slug  = 0 === strpos( $name, 'pa_' ) ? substr( $name, 3 ) : $name;
		$value = $product->get_attribute( $name );

		if ( '' === $value ) {
			continue;
		}

		$label = $attribute->is_taxonomy()
			? wc_attribute_label( $name )
			: $name;

		$result[ $slug ] = array(
			'label' => $label,
			'value' => $value,
		);
	}

	return $result;
}

/**
 * Четыре ключевых параметра для плиток под галереей.
 *
 * @param WC_Product $product Товар.
 * @return array
 */
function amis_key_specs( $product ) {

	$all = amis_all_attributes( $product );
	$map = amis_product_map( $product );

	// Категории нет в карте — берём первые четыре атрибута как есть.
	if ( ! $map || empty( $map['key'] ) ) {
		return array_slice( $all, 0, 4, true );
	}

	$keys = array();

	foreach ( $map['key'] as $slug ) {
		if ( isset( $all[ $slug ] ) ) {
			$keys[ $slug ] = $all[ $slug ];
		}
	}

	// Если карта описана, но атрибуты не заполнены — не оставляем пустоту.
	return $keys ? $keys : array_slice( $all, 0, 4, true );
}

/**
 * Характеристики, разложенные по группам.
 *
 * Атрибуты, не попавшие ни в одну группу, добавляются
 * в конец блоком «Прочее» — так ничего не теряется.
 *
 * @param WC_Product $product Товар.
 * @return array Массив «Название группы => массив характеристик».
 */
function amis_grouped_specs( $product ) {

	$all = amis_all_attributes( $product );
	$map = amis_product_map( $product );

	if ( ! $all ) {
		return array();
	}

	// Запасной сценарий: одна группа со всеми характеристиками.
	if ( ! $map || empty( $map['groups'] ) ) {
		return array( __( 'Характеристики', 'amis' ) => $all );
	}

	$groups = array();
	$used   = array();

	foreach ( $map['groups'] as $title => $slugs ) {

		$rows = array();

		foreach ( $slugs as $slug ) {
			if ( isset( $all[ $slug ] ) ) {
				$rows[ $slug ] = $all[ $slug ];
				$used[]        = $slug;
			}
		}

		if ( $rows ) {
			$groups[ $title ] = $rows;
		}
	}

	// Всё, что не описано в карте.
	$rest = array_diff_key( $all, array_flip( $used ) );

	if ( $rest ) {
		$groups[ __( 'Прочее', 'amis' ) ] = $rest;
	}

	return $groups;
}

/**
 * Соседние модели по главному параметру.
 *
 * Ищем в той же категории товары со значением _amis_sort_value
 * ближайшим снизу и сверху. Единицы у каждой категории свои,
 * но сравнение идёт внутри категории, поэтому это безопасно.
 *
 * @param WC_Product $product Товар.
 * @return array{prev:WC_Product|null,next:WC_Product|null}
 */
function amis_get_neighbors( $product ) {

	$result = array( 'prev' => null, 'next' => null );

	$value = get_post_meta( $product->get_id(), '_amis_sort_value', true );

	if ( '' === $value ) {
		return $result;
	}

	$cats = wp_get_post_terms( $product->get_id(), 'product_cat', array( 'fields' => 'slugs' ) );

	if ( is_wp_error( $cats ) || ! $cats ) {
		return $result;
	}

	$base = array(
		'status'   => 'publish',
		'limit'    => 1,
		'category' => $cats,
		'exclude'  => array( $product->get_id() ),
		'meta_key' => '_amis_sort_value',
		'orderby'  => 'meta_value_num',
	);

	// Ближайший снизу: самое большое значение из тех, что меньше текущего.
	$prev = wc_get_products( array_merge( $base, array(
		'order'      => 'DESC',
		'meta_query' => array(
			array(
				'key'     => '_amis_sort_value',
				'value'   => $value,
				'compare' => '<',
				'type'    => 'DECIMAL(10,2)',
			),
		),
	) ) );

	// Ближайший сверху.
	$next = wc_get_products( array_merge( $base, array(
		'order'      => 'ASC',
		'meta_query' => array(
			array(
				'key'     => '_amis_sort_value',
				'value'   => $value,
				'compare' => '>',
				'type'    => 'DECIMAL(10,2)',
			),
		),
	) ) );

	$result['prev'] = $prev ? $prev[0] : null;
	$result['next'] = $next ? $next[0] : null;

	return $result;
}
