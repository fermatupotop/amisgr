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

		/**
		 * Оборудование для тестирования и симуляции ГНСС (RFTEX) —
		 * имитаторы сигналов, комплексы записи/воспроизведения и т.д.
		 * Характеристики в каталоге поставщика — огромные многоуровневые
		 * таблицы (десятки строк), ненадёжно парсятся из PDF построчно
		 * (столбцы «название/значение» расходятся при извлечении текста).
		 * По решению пользователя переносим только 4 ключевых параметра
		 * из собственного блока «Ключевые параметры» каталога поставщика,
		 * а не всю таблицу — она не настолько надёжна, чтобы её публиковать.
		 *
		 * Важно: все эти атрибуты — обычные (не глобальная таксономия),
		 * заводятся через amis_gnss_import_set_custom_attribute() в
		 * inc/gnss-import.php. Для таких атрибутов amis_all_attributes()
		 * берёт слагом не sanitize_title(), а ТОЧНЫЙ текст названия —
		 * так WooCommerce хранит имя у нетаксономийных атрибутов (отличие
		 * от pa_-атрибутов, где слаг — это имя таксономии). Поэтому здесь
		 * строки ниже должны совпадать с названием атрибута побуквенно,
		 * включая регистр — не придуманные латинские слаги.
		 */
		'gnss-equipment' => array(
			'key'    => array( 'Тип', 'Каналы', 'Поддерживаемые системы ГНСС', 'Исполнение' ),
			'groups' => array(
				'Ключевые параметры'          => array( 'Тип', 'Каналы', 'Поддерживаемые системы ГНСС', 'Исполнение' ),
				'Поддерживаемые сигналы ГНСС' => array( 'GPS', 'ГЛОНАСС', 'BeiDou', 'Galileo', 'SBAS', 'QZSS', 'NavIC', 'AWGN' ),
				/**
				 * «Опции по созвездиям» — та же группировка систем, что и
				 * в сигналах выше, поэтому названия атрибутов с суффиксом
				 * « (опция)»: иначе атрибут «GPS» из этой группы перезаписал
				 * бы в _product_attributes одноимённый «GPS» из сигналов —
				 * ключ там строится как sanitize_title(название), и для
				 * двух разных атрибутов с одинаковым названием получился
				 * бы один и тот же ключ. Суффикс добавляется автоматически
				 * в amis_gnss_import_process() (колонка CSV "options"), в
				 * карте здесь просто то же самое значение с суффиксом.
				 */
				'Опции по созвездиям'         => array( 'GPS (опция)', 'ГЛОНАСС (опция)', 'BeiDou (опция)', 'Galileo (опция)', 'QZSS/NavIC (опция)', 'SBAS L1 (опция)', 'SBAS L5 (опция)', 'Спецрежимы (опция)' ),
				/**
				 * Три группы ниже — для серии ИСПП (системы защиты от помех),
				 * ЭКБ-400 (безэховая камера) и СЧВС-48Р1 (сервер синхронизации).
				 * В отличие от гигантских таблиц ИНСС/ГНСП, у этих моделей
				 * таблица характеристик в PDF — простой список «Параметр /
				 * Значение» без путаницы со столбцами, поэтому перенесена
				 * полностью (колонка CSV "specs" в inc/gnss-import.php).
				 * Список атрибутов ниже — объединение того, что встречается
				 * хотя бы у одной модели серии; для конкретного товара в
				 * характеристиках покажутся только реально заполненные.
				 */
				'Характеристики помехозащиты (ИСПП)' => array(
					'Элементов антенной решётки', 'Диапазоны частот',
					'Полоса пропускания', 'Полоса пропускания (осн. диапазон)', 'Полоса пропускания (доп. диапазон)',
					'Подавление одиночной помехи (J/S)', 'Подавление трёх помех (J/S)',
					'Подавление семи помех (J/S)', 'Подавление пятнадцати помех (J/S)',
					'Подавление (осн. диапазон): одиночной/трёх/семи помех',
					'Подавление (доп. диапазон): одиночной/трёх/семи помех',
					'Выходной уровень сигнала', 'Напряжение питания', 'Потребляемая мощность',
					'Разъём питания и I/O', 'Габариты', 'Масса', 'Рабочая температура', 'Температура хранения',
				),
				'Характеристики камеры (ЭКБ)' => array(
					'Рабочий диапазон частот', 'Эффективность экранирования',
					'Габаритные размеры (по экрану)', 'Внутренний объём (по РПМ)',
					'Масса камеры', 'Диапазон поглощения РПМ', 'Применяемый РПМ', 'Проём двери в чистоте',
				),
				'Характеристики синхронизации (СЧВС)' => array(
					'Кратковременная стабильность (девиация Аллана)', 'Точность синхронизации 1PPS',
					'Точность подстройки рубидиевого стандарта', 'Точность хода в режиме удержания',
					'Воспроизводимость частоты', 'Старение', 'Время прогрева', 'Питание сервера',
					'Рабочая температура сервера', 'Температура хранения сервера', 'Относительная влажность',
					'Габариты сервера', 'Масса сервера', 'Конфигурация выходов 10 МГц', 'Конфигурация выходов 1PPS',
				),
				/**
				 * Полные характеристики имитаторов (ИНСС-8000/4000) и
				 * комплексов записи-воспроизведения (ИНСС-4400/4200) —
				 * раньше здесь были только 4 ключевых параметра, полную
				 * таблицу не переносили (риск перепутать столбцы при
				 * извлечении текста из PDF). Перепроверено построчно по
				 * исходному PDF и сверено с независимым источником —
				 * колонка CSV "specs", см. inc/gnss-import.php.
				 */
				'Группировки, дальность и точность' => array(
					'Количество спутников в группировке', 'Количество одновременно видимых спутников',
					'Максимальная ёмкость', 'Диапазон высоты при моделировании',
					'Относительная скорость', 'Относительное ускорение', 'Относительный рывок',
					'Точность псевдодальности', 'Точность скорости изменения псевдодальности',
					'Межканальная согласованность',
				),
				'Чистота спектра и уровни сигнала' => array(
					'Фазовый шум при 100 Гц', 'Фазовый шум при 1 кГц', 'Фазовый шум при 10 кГц', 'Фазовый шум при 100 кГц',
					'Уровень побочных спектральных составляющих', 'Уровень гармонических составляющих',
					'Диапазон уровня несущей (LRF)', 'Диапазон уровня несущей (HRF)',
					'Погрешность установки уровня',
				),
				'Опорная частота и 1PPS' => array(
					'Опорная частота 10 МГц, вход', 'Опорная частота 10 МГц, выход', 'Стабильность выходной частоты',
					'Метка времени 1PPS, вход', 'Метка времени 1PPS, выход',
				),
				'Электропитание и конструктив (ИНСС)' => array(
					'Входное напряжение', 'Адаптер, вход', 'Адаптер, выход',
					'Потребляемая мощность', 'Время работы от аккумулятора',
					'Монтаж', 'Высота', 'Габариты (ИНСС)', 'Масса (ИНСС)',
				),
				'Запись и воспроизведение сигнала' => array(
					'Каналы записи сигнала', 'Каналы воспроизведения сигнала',
					'Диапазон входного уровня', 'Диапазон регулировки усиления', 'Диапазон регулировки ослабления',
					'Шаг регулировки затухания', 'Полоса записи сигнала', 'Точность оцифровки сигнала (I/Q)',
					'Уровень побочных спектральных составляющих (запись)', 'Уровень гармонических составляющих (запись)',
				),
				'Хранение и интерфейсы' => array(
					'Локальное хранилище', 'Сетевое хранилище', 'Поддержка воспроизведения',
					'Приём потока данных', 'Управление с ПК', 'Локальное управление', 'Удалённое управление',
					'Дисплей', 'Каскадирование устройств',
				),
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
