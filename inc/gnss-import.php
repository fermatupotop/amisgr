<?php
/**
 * Импорт товаров категории «Оборудование ГНСС» (бренд RFTEX) из CSV —
 * собрано вручную из PDF-каталога поставщика (имитаторы сигналов,
 * комплексы записи/воспроизведения и т.д.). Создаёт товары ЧЕРНОВИКАМИ.
 * Формат файла — см. gnss_rftex_import.csv в корне темы:
 * sku,title,slug,category,brand,type,channels,gnss_systems,design,specs,sort_value,
 * compare_table,compare_note,
 * short_description,overview,applications,signals,options,source,kit,exclusive.
 *
 * Характеристики в каталоге поставщика не везде устроены одинаково. У
 * имитаторов сигналов (ИНСС) и генераторов помех (ГНСП) — огромные
 * многоуровневые таблицы (десятки строк на товар), ненадёжно парсятся из
 * PDF построчно (столбцы «название/значение» расходятся при извлечении
 * текста) — для них переносим только то, что вытаскивается чисто: 4
 * ключевых параметра (type/channels/gnss_systems/design, см. ключ
 * 'gnss-equipment' в inc/product-map.php) и, где есть, две чистые таблицы
 * по системам ГНСС — «Поддерживаемые сигналы ГНСС» (signals) и «Опции по
 * созвездиям» (options, суффикс « (опция)» — иначе совпало бы с
 * одноимённым атрибутом из signals). А вот у серии ИСПП (системы защиты
 * от помех), ЭКБ-400 (камера) и СЧВС-48Р1 (сервер синхронизации) таблица
 * характеристик в PDF — простой список «Параметр / Значение» без путаницы
 * со столбцами, поэтому для них таблица переносится ПОЛНОСТЬЮ — колонка
 * specs, формат "Параметр:значение|Параметр:значение" (названия
 * параметров см. в соответствующих группах inc/product-map.php).
 *
 * Везде также: обзорный текст (overview — в _amis_long_text) и области
 * применения (applications — в _amis_applications, сетка иконок), если
 * в PDF у конкретной модели есть такой блок. Цены нет нигде в каталоге —
 * оборудование «по запросу», это осознанно (товар без цены сайт уже
 * умеет показывать, см. woocommerce/single-product.php).
 *
 * Та же схема, что у inc/amplifiers-import.php, но с двумя отличиями:
 * категория создаётся сама, если её ещё нет (у «Усилителей мощности»
 * категория уже существовала); и повторный запуск с уже существующим
 * SKU не пропускает строку, а ОБНОВЛЯЕТ контентные поля существующего
 * товара (название/категория/бренд/статус не трогает) — тем же файлом
 * можно дополнять уже созданные товары новыми колонками по мере разбора
 * следующих страниц PDF.
 *
 * @package amis
 */

defined( 'ABSPATH' ) || exit;

/**
 * Пункт меню — под «Товары».
 */
function amis_gnss_import_menu() {

	add_submenu_page(
		'edit.php?post_type=product',
		__( 'Импорт оборудования ГНСС (RFTEX)', 'amis' ),
		__( 'Импорт ГНСС-оборудования', 'amis' ),
		'manage_woocommerce',
		'amis-gnss-import',
		'amis_gnss_import_page'
	);
}
add_action( 'admin_menu', 'amis_gnss_import_menu' );

/**
 * Термин таксономии по названию — найти или создать.
 *
 * @param string $name     Название термина.
 * @param string $taxonomy Слаг таксономии.
 * @return int|null ID термина.
 */
function amis_gnss_import_get_or_create_term( $name, $taxonomy ) {

	$name = trim( $name );

	if ( '' === $name || ! taxonomy_exists( $taxonomy ) ) {
		return null;
	}

	$term = get_term_by( 'name', $name, $taxonomy );

	if ( $term ) {
		return $term->term_id;
	}

	$inserted = wp_insert_term( $name, $taxonomy );

	return is_wp_error( $inserted ) ? null : $inserted['term_id'];
}

/**
 * Категория товара по слагу — найти или создать с явным слагом и названием.
 * В отличие от amis_gnss_import_get_or_create_term(), слаг здесь задаётся
 * явно (а не через sanitize_title(name)) — см. тот же приём для новых
 * категорий в README/CLAUDE.md, иначе из кириллицы вышел бы
 * URL-кодированный слаг.
 *
 * @param string $slug Слаг категории.
 * @param string $name Название категории.
 * @return int|null ID термина.
 */
function amis_gnss_import_get_or_create_category( $slug, $name ) {

	$term = get_term_by( 'slug', $slug, 'product_cat' );

	if ( $term ) {
		return $term->term_id;
	}

	$inserted = wp_insert_term( $name, 'product_cat', array( 'slug' => $slug ) );

	return is_wp_error( $inserted ) ? null : $inserted['term_id'];
}

/**
 * Ставит товару обычный (не таксономия) атрибут — все 4 ключевых параметра
 * этой категории сделаны так же, как «Тип»/«Стандарты» у усилителей: это
 * свободный текст без фиксированного набора значений, регистрировать
 * отдельную таксономию в «Товары → Атрибуты» для них смысла нет (см.
 * обсуждение в inc/amplifiers-import.php).
 *
 * @param int    $product_id Товар.
 * @param string $label      Название атрибута.
 * @param string $value      Значение.
 * @param int    $position   Порядок на вкладке «Атрибуты».
 */
function amis_gnss_import_set_custom_attribute( $product_id, $label, $value, $position ) {

	$value = trim( (string) $value );

	if ( '' === $value ) {
		return;
	}

	$attributes = get_post_meta( $product_id, '_product_attributes', true );
	$attributes = is_array( $attributes ) ? $attributes : array();
	$key        = sanitize_title( $label );

	$attributes[ $key ] = array(
		'name'         => $label,
		'value'        => $value,
		'position'     => $position,
		'is_visible'   => 1,
		'is_variation' => 0,
		'is_taxonomy'  => 0,
	);

	update_post_meta( $product_id, '_product_attributes', $attributes );
}

/**
 * Разбор загруженного CSV: результат по каждой строке.
 *
 * @param string $tmp_path Путь к временно загруженному файлу.
 * @return array{created:array,updated:array,errors:array}
 */
function amis_gnss_import_process( $tmp_path ) {

	$result = array(
		'created' => array(),
		'updated' => array(),
		'errors'  => array(),
	);

	$handle = fopen( $tmp_path, 'r' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fopen -- временный файл загрузки формы, не файл темы/контента.

	if ( ! $handle ) {
		$result['errors'][] = __( 'Не удалось открыть файл.', 'amis' );
		return $result;
	}

	$first_line = fgets( $handle );

	if ( false === $first_line ) {
		$result['errors'][] = __( 'Файл пустой.', 'amis' );
		fclose( $handle ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fclose
		return $result;
	}

	$first_line = preg_replace( '/^\xEF\xBB\xBF/', '', $first_line ); // BOM.

	$delimiter    = ',';
	$best_columns = 0;

	foreach ( array( ',', ';', "\t" ) as $candidate ) {
		$columns = count( str_getcsv( $first_line, $candidate ) );
		if ( $columns > $best_columns ) {
			$best_columns = $columns;
			$delimiter    = $candidate;
		}
	}

	$header = str_getcsv( $first_line, $delimiter );

	if ( ! $header ) {
		$result['errors'][] = __( 'Файл не читается как CSV.', 'amis' );
		fclose( $handle ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fclose
		return $result;
	}

	$header = array_map(
		static function ( $col ) {
			return strtolower( trim( (string) $col ) );
		},
		$header
	);

	$required = array( 'sku', 'title', 'category' );
	$cols     = array();

	foreach ( $header as $i => $name ) {
		$cols[ $name ] = $i;
	}

	foreach ( $required as $name ) {
		if ( ! isset( $cols[ $name ] ) ) {
			$result['errors'][] = sprintf(
				/* translators: %s — имя колонки. */
				__( 'В файле не нашлось обязательной колонки «%s».', 'amis' ),
				$name
			);
			fclose( $handle ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fclose
			return $result;
		}
	}

	$get = static function ( $row, $cols, $name ) {
		return isset( $cols[ $name ], $row[ $cols[ $name ] ] ) ? trim( (string) $row[ $cols[ $name ] ] ) : '';
	};

	while ( false !== ( $row = fgetcsv( $handle, 0, $delimiter ) ) ) { // phpcs:ignore Squiz.PHP.DisallowMultipleAssignments.FoundInControlStructure

		$sku = $get( $row, $cols, 'sku' );

		if ( '' === $sku ) {
			continue; // Пустая строка.
		}

		$existing_id = wc_get_product_id_by_sku( $sku );
		$is_update   = (bool) $existing_id;

		if ( $is_update ) {

			// Товар уже на сайте — название/категорию/бренд/статус не
			// трогаем (их могли поправить руками после первого импорта),
			// обновляем только контентные поля ниже: описание, атрибуты,
			// «Обзор продукта», «Области применения». Так одним и тем же
			// CSV можно и добавлять новые товары, и дополнять уже
			// созданные данными из новых колонок.
			$product_id = $existing_id;
			$title      = get_the_title( $product_id );

		} else {

			$title = $get( $row, $cols, 'title' );

			if ( '' === $title ) {
				$result['errors'][] = $sku . ': ' . __( 'нет названия.', 'amis' );
				continue;
			}

			$product = new WC_Product_Simple();
			$product->set_name( $title );
			$product->set_sku( $sku );
			$product->set_status( 'draft' ); // Обязательно проверить руками перед публикацией.

			$category_slug = $get( $row, $cols, 'category' );
			if ( $category_slug ) {
				$cat_id = amis_gnss_import_get_or_create_category( $category_slug, __( 'Оборудование ГНСС', 'amis' ) );
				if ( $cat_id ) {
					$product->set_category_ids( array( $cat_id ) );
				}
			}

			$product_id = $product->save();

			if ( ! $product_id ) {
				$result['errors'][] = $sku . ': ' . __( 'не удалось создать товар.', 'amis' );
				continue;
			}

			$brand = $get( $row, $cols, 'brand' );
			if ( $brand ) {
				$brand_term_id = amis_gnss_import_get_or_create_term( $brand, 'product_brand' );
				if ( $brand_term_id ) {
					wp_set_object_terms( $product_id, array( $brand_term_id ), 'product_brand', false );
				}
			}
		}

		/**
		 * Адрес товара — латиницей, явно из колонки slug, а не из русского
		 * названия. Без этого WordPress сам строит slug из $title
		 * («RFTEX ИНСС-8000»), и получается percent-encoded кириллица в
		 * адресе — работает, но не единообразно с остальным каталогом
		 * (там slug — латиница по артикулу, см. inc/slug-fix.php). Здесь
		 * обычный slug-fix.php не поможет: сам артикул тоже на кириллице.
		 * wp_update_post() сам прогонит значение через sanitize_title() —
		 * для уже латинской строки это не меняет её, просто гарантирует
		 * валидный слаг.
		 */
		$slug = $get( $row, $cols, 'slug' );
		if ( $slug ) {
			wp_update_post( array(
				'ID'        => $product_id,
				'post_name' => $slug,
			) );
		}

		$short_description = $get( $row, $cols, 'short_description' );
		if ( $short_description ) {
			$product_obj = wc_get_product( $product_id );
			$product_obj->set_short_description( $short_description );
			$product_obj->save();
		}

		// 4 ключевых параметра из CSV — те же 4, что ждёт карта
		// характеристик 'gnss-equipment' в inc/product-map.php.
		amis_gnss_import_set_custom_attribute( $product_id, 'Тип', $get( $row, $cols, 'type' ), 0 );
		amis_gnss_import_set_custom_attribute( $product_id, 'Каналы', $get( $row, $cols, 'channels' ), 1 );
		amis_gnss_import_set_custom_attribute( $product_id, 'Поддерживаемые системы ГНСС', $get( $row, $cols, 'gnss_systems' ), 2 );
		amis_gnss_import_set_custom_attribute( $product_id, 'Исполнение', $get( $row, $cols, 'design' ), 3 );

		// «Поддерживаемые сигналы ГНСС» — колонка signals в формате
		// "Система:значение|Система:значение", например
		// "GPS:L1CA, L1C, L5|ГЛОНАСС:G1, G2, G3". Пусто — просто не
		// добавляем ни одной строки этой группы, не ошибка (не для
		// всех товаров поставщик даёт эту таблицу чисто и однозначно,
		// см. docblock в начале файла).
		$signals  = $get( $row, $cols, 'signals' );
		$position = 4;

		if ( $signals ) {
			foreach ( explode( '|', $signals ) as $pair ) {
				if ( false === strpos( $pair, ':' ) ) {
					continue;
				}
				list( $system, $value ) = array_map( 'trim', explode( ':', $pair, 2 ) );
				amis_gnss_import_set_custom_attribute( $product_id, $system, $value, $position );
				++$position;
			}
		}

		/**
		 * «Опции по созвездиям» — колонка options, тот же формат, что и
		 * signals: "Группа:значение|Группа:значение". Названия систем
		 * здесь те же (GPS, ГЛОНАСС и т.д.), что и в «Поддерживаемых
		 * сигналах» выше — добавляем суффикс « (опция)», иначе получился
		 * бы тот же ключ атрибута и одна группа перезаписала бы другую
		 * (см. комментарий у 'Опции по созвездиям' в inc/product-map.php).
		 */
		$options = $get( $row, $cols, 'options' );

		if ( $options ) {
			foreach ( explode( '|', $options ) as $pair ) {
				if ( false === strpos( $pair, ':' ) ) {
					continue;
				}
				list( $system, $value ) = array_map( 'trim', explode( ':', $pair, 2 ) );
				amis_gnss_import_set_custom_attribute( $product_id, $system . ' (опция)', $value, $position );
				++$position;
			}
		}

		/**
		 * «Технические характеристики» — колонка specs, формат
		 * "Параметр:значение|Параметр:значение". В отличие от signals/
		 * options, здесь названия параметров уже сами по себе уникальны
		 * (не повторяют друг друга внутри товара), суффикс не нужен.
		 * Используется для серий, у которых таблица характеристик в PDF —
		 * простой список без путаницы со столбцами (ИСПП, ЭКБ-400,
		 * СЧВС-48Р1) — см. группы в inc/product-map.php.
		 */
		$specs = $get( $row, $cols, 'specs' );

		if ( $specs ) {
			foreach ( explode( '|', $specs ) as $pair ) {
				if ( false === strpos( $pair, ':' ) ) {
					continue;
				}
				list( $label, $value ) = array_map( 'trim', explode( ':', $pair, 2 ) );
				amis_gnss_import_set_custom_attribute( $product_id, $label, $value, $position );
				++$position;
			}
		}

		/**
		 * «Соседние модели в линейке» на странице товара (amis_get_neighbors(),
		 * inc/product-map.php) работают по числовому полю _amis_sort_value —
		 * без него блок просто не показывается. Сравнивать по этому полю
		 * имеет смысл только однородные товары (та же логика, что у
		 * осциллографов/полосы пропускания) — для товаров без заполненной
		 * колонки sort_value поле просто не трогаем, они не участвуют в
		 * подборе соседей ни у кого (amis_get_neighbors() фильтрует запрос
		 * по наличию этого meta-поля).
		 */
		$sort_value = $get( $row, $cols, 'sort_value' );
		if ( '' !== $sort_value ) {
			update_post_meta( $product_id, '_amis_sort_value', wc_format_decimal( $sort_value ) );
		}

		// «Обзор продукта» — в поле _amis_long_text (поддерживает
		// ## подзаголовки и - буллиты, см. amis_render_long_text()).
		$overview = $get( $row, $cols, 'overview' );
		if ( $overview ) {
			update_post_meta( $product_id, '_amis_long_text', str_replace( '\n', "\n", $overview ) );
		}

		// «Области применения» — колонка applications в формате
		// "слаг:текст|слаг:текст", слаги — см. amis_app_icon_slugs().
		$applications_raw = $get( $row, $cols, 'applications' );
		if ( $applications_raw ) {
			$lines = array();
			foreach ( explode( '|', $applications_raw ) as $pair ) {
				if ( false === strpos( $pair, ':' ) ) {
					continue;
				}
				list( $icon_slug, $label ) = array_map( 'trim', explode( ':', $pair, 2 ) );
				$lines[] = $icon_slug . '|' . $label;
			}
			if ( $lines ) {
				update_post_meta( $product_id, '_amis_applications', implode( "\n", $lines ) );
			}
		}

		/**
		 * «Сравнение моделей линейки» — колонка compare_table, строки
		 * разделены переносом (реальным — ячейка CSV в кавычках), ячейки
		 * внутри строки — «|». Первая строка — заголовки столбцов. Таблица
		 * одинаковая для всех моделей линейки — заполняется один и тот же
		 * текст в каждой строке CSV, разбор — amis_parse_compare_table()
		 * в inc/product-fields.php, подсветка текущей модели — по
		 * совпадению заголовка столбца с названием товара (на уровне
		 * рендера, не здесь).
		 */
		$compare_table = $get( $row, $cols, 'compare_table' );
		if ( $compare_table ) {
			update_post_meta( $product_id, '_amis_compare_table', str_replace( '\n', "\n", $compare_table ) );
		}

		$compare_note = $get( $row, $cols, 'compare_note' );
		if ( $compare_note ) {
			update_post_meta( $product_id, '_amis_compare_note', $compare_note );
		}

		$source = $get( $row, $cols, 'source' );
		if ( $source ) {
			update_post_meta( $product_id, '_amis_import_source', $source );
		}

		/**
		 * «Комплект поставки» — колонка kit, по одной позиции на строку
		 * (реальный перенос внутри CSV-ячейки в кавычках). Поле то же,
		 * что у метабокса «Комплект поставки» (inc/product-package.php,
		 * _amis_package_base) — используется, когда у конкретной модели
		 * состав поставки отличается от соседних по линейке и это важно
		 * показать на странице (например, в комплект входят антенны/
		 * запасной аккумулятор/кейс, которых нет у остальных моделей).
		 * Пусто — поле не трогаем, у большинства товаров ГНСС-оборудования
		 * заполнять его вручную в метабоксе смысла нет (поставка «по
		 * запросу», состав обсуждается с менеджером).
		 */
		$kit = $get( $row, $cols, 'kit' );
		if ( $kit ) {
			update_post_meta( $product_id, '_amis_package_base', str_replace( '\n', "\n", $kit ) );
		}

		/**
		 * «Эксклюзив» — колонка exclusive (1/true/yes), то же поле, что у
		 * чекбокса «Эксклюзив» в метабоксе «Данные АМИС»
		 * (_amis_exclusive, inc/product-fields.php). Меняет ленту в углу
		 * карточки в каталоге на «Эксклюзив» вместо обычной «Со складе»/
		 * «Под заказ»/«Хит продаж» — для товаров, которых нет у других
		 * продавцов. Значение не трогаем, если колонки нет вовсе (пусто —
		 * не значит «снять флаг», проверяется только непустое значение).
		 */
		$exclusive = $get( $row, $cols, 'exclusive' );
		if ( '' !== $exclusive ) {
			$is_exclusive = in_array( strtolower( $exclusive ), array( '1', 'true', 'yes', 'да' ), true );
			update_post_meta( $product_id, '_amis_exclusive', $is_exclusive ? 'yes' : 'no' );
		}

		if ( $is_update ) {
			$result['updated'][] = array(
				'sku'   => $sku,
				'title' => $title,
				'id'    => $product_id,
			);
		} else {
			$result['created'][] = array(
				'sku'   => $sku,
				'title' => $title,
				'id'    => $product_id,
			);
		}
	}

	fclose( $handle ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fclose

	return $result;
}

/**
 * Страница в админке: форма загрузки + отчёт.
 */
function amis_gnss_import_page() {

	if ( ! current_user_can( 'manage_woocommerce' ) ) {
		wp_die( esc_html__( 'Недостаточно прав.', 'amis' ) );
	}

	$result = null;

	if ( isset( $_POST['amis_gnss_import_nonce'] )
		&& wp_verify_nonce( sanitize_key( $_POST['amis_gnss_import_nonce'] ), 'amis_gnss_import' )
		&& ! empty( $_FILES['amis_gnss_file']['tmp_name'] )
	) {

		$file = $_FILES['amis_gnss_file']; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- обрабатывается ниже: проверка кода ошибки, затем расширения.

		if ( UPLOAD_ERR_OK !== $file['error'] ) {
			$result = array(
				'errors'  => array( __( 'Ошибка загрузки файла.', 'amis' ) ),
				'created' => array(),
				'updated' => array(),
			);
		} else {
			$ext = strtolower( pathinfo( $file['name'], PATHINFO_EXTENSION ) );

			if ( 'csv' !== $ext ) {
				$result = array(
					'errors'  => array( __( 'Нужен файл в формате CSV.', 'amis' ) ),
					'created' => array(),
					'updated' => array(),
				);
			} else {
				$result = amis_gnss_import_process( $file['tmp_name'] );
			}
		}
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Импорт оборудования ГНСС (RFTEX)', 'amis' ); ?></h1>

		<p>
			<?php esc_html_e( 'Загрузите CSV со столбцами: sku, title, slug, category, brand, type, channels, gnss_systems, design, short_description, overview, applications, signals, options, specs, sort_value, compare_table, compare_note, source, kit.', 'amis' ); ?>
		</p>
		<p>
			<?php esc_html_e( 'kit — «Комплект поставки» (поле метабокса _amis_package_base): по одной позиции на строку. Заполняйте, только если состав поставки у этой модели важно показать отдельно (например, отличается от соседних по линейке) — для остальных товаров ГНСС-оборудования оставляйте пустым.', 'amis' ); ?><br>
			<?php esc_html_e( 'exclusive — 1/yes/да, чтобы в каталоге вместо обычной ленты наличия показывалась лента «Эксклюзив» (товаров, которых нет у других продавцов). Оставьте пустым — поле не тронется.', 'amis' ); ?>
		</p>
		<p>
			<?php esc_html_e( 'overview — текст для блока «Обзор продукта» (строка «## Заголовок» — подзаголовок, строка «- текст» — пункт списка).', 'amis' ); ?><br>
			<?php esc_html_e( 'applications — «Области применения»: слаг_иконки:текст|слаг_иконки:текст|… (слаги — см. amis_app_icon_slugs() в inc/product-fields.php).', 'amis' ); ?><br>
			<?php esc_html_e( 'signals — «Поддерживаемые сигналы ГНСС»: Система:значение|Система:значение|…, например GPS:L1CA, L1C, L5|ГЛОНАСС:G1, G2, G3.', 'amis' ); ?><br>
			<?php esc_html_e( 'options — «Опции по созвездиям», тот же формат: Группа:значение|Группа:значение|… (названия систем можно те же, что в signals — суффикс «(опция)» добавляется автоматически).', 'amis' ); ?><br>
			<?php esc_html_e( 'specs — полные «Технические характеристики» для серий ИСПП/ЭКБ-400/СЧВС-48Р1: Параметр:значение|Параметр:значение|… (названия параметров — см. группы в inc/product-map.php, чтобы характеристика встала в нужный раздел таблицы).', 'amis' ); ?><br>
			<?php esc_html_e( 'slug — адрес товара латиницей (например inss-8000). Без этой колонки WordPress сам построит адрес из русского названия — получится нечитаемая percent-encoded кириллица в URL.', 'amis' ); ?><br>
			<?php esc_html_e( 'sort_value — число для блока «Соседние модели в линейке» (ближайшие по значению товары той же категории). Сравнивать имеет смысл только однородные товары одной линейки — для остальных колонку оставляйте пустой.', 'amis' ); ?><br>
			<?php esc_html_e( 'compare_table — таблица «Сравнение моделей линейки»: первая строка файла внутри ячейки — заголовки столбцов через «|» (первый — «Параметр», дальше модели), остальные строки — данные. Одинаковый текст для всех моделей линейки. compare_note — короткая рекомендация под таблицей, тоже одинаковая.', 'amis' ); ?>
		</p>
		<p>
			<?php esc_html_e( 'Новый артикул — товар создаётся ЧЕРНОВИКОМ (категория и бренд проставляются автоматически). Уже существующий артикул — название/категория/бренд/статус не трогаются, обновляются только описание, атрибуты, «Обзор продукта» и «Области применения»: тем же файлом можно дополнять уже созданные товары новыми колонками.', 'amis' ); ?>
		</p>
		<p>
			<strong><?php esc_html_e( 'После импорта обязательно:', 'amis' ); ?></strong>
			<?php esc_html_e( 'WooCommerce → Статус → Инструменты → «Regenerate the product attributes lookup table».', 'amis' ); ?>
		</p>

		<form method="post" enctype="multipart/form-data">
			<?php wp_nonce_field( 'amis_gnss_import', 'amis_gnss_import_nonce' ); ?>
			<input type="file" name="amis_gnss_file" accept=".csv" required>
			<?php submit_button( __( 'Загрузить и создать черновики', 'amis' ) ); ?>
		</form>

		<?php if ( $result ) : ?>

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

			<div class="notice notice-success">
				<p>
					<?php
					printf(
						/* translators: 1: создано, 2: обновлено. */
						esc_html__( 'Создано черновиков: %1$d. Обновлено существующих: %2$d.', 'amis' ),
						count( $result['created'] ),
						count( $result['updated'] )
					);
					?>
				</p>
			</div>

			<?php
			$tables = array(
				'created' => __( 'Создано', 'amis' ),
				'updated' => __( 'Обновлено', 'amis' ),
			);
			?>
			<?php foreach ( $tables as $key => $label ) : ?>
				<?php if ( ! empty( $result[ $key ] ) ) : ?>
					<h2><?php echo esc_html( $label ); ?></h2>
					<table class="widefat striped">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Артикул', 'amis' ); ?></th>
								<th><?php esc_html_e( 'Название', 'amis' ); ?></th>
								<th><?php esc_html_e( 'Ссылка', 'amis' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $result[ $key ] as $row ) : ?>
								<tr>
									<td><?php echo esc_html( $row['sku'] ); ?></td>
									<td><?php echo esc_html( $row['title'] ); ?></td>
									<td><a href="<?php echo esc_url( get_edit_post_link( $row['id'] ) ); ?>"><?php esc_html_e( 'Открыть в редакторе', 'amis' ); ?></a></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				<?php endif; ?>
			<?php endforeach; ?>

		<?php endif; ?>
	</div>
	<?php
}
