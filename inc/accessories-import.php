<?php
/**
 * Массовое добавление пробников/аксессуаров из CSV + автоматическая
 * привязка к приборам через _amis_related_probes / _amis_related_acc
 * (inc/product-accessories.php).
 *
 * В отличие от price-import.php (там точное совпадение SKU) — здесь
 * колонка «с чем совместим» в прайсе поставщика не содержит точных
 * артикулов приборов, а название категории («Vector Network Analyzer»)
 * или семейства моделей («DG5000 Pro»). Точного алгоритма для этого не
 * существует — ниже эвристика:
 *
 * 1. Похоже на категорию (по словарю англ. фраз) — берём ВСЕ товары
 *    этой категории.
 * 2. Иначе — разбираем как префикс артикула: «DG5000» → «DG5»
 *    (отсекаем незначащие нули), ищем товары с артикулом на этот
 *    префикс. Слово-уточнитель в конце («Pro» в «DG5000 Pro») требуем
 *    дополнительно в артикуле/названии — иначе «DG5000 Pro» зацепил бы
 *    и обычные DG52xx без Pro.
 * 3. Значения через /,& — несколько независимых частей, результат
 *    объединяется.
 * 4. Ничего не подошло — не привязываем. Не гадаем дальше: отчёт после
 *    загрузки показывает по каждой строке, к каким артикулам она
 *    привязалась, чтобы неверные/недостающие связи можно было
 *    поправить руками через метабокс на карточке товара.
 *
 * @package amis
 */

defined( 'ABSPATH' ) || exit;

/* ==========================================================================
   СЛОВАРЬ КАТЕГОРИЙ
   ========================================================================== */

/**
 * Английские фразы → слаг категории сайта. Список неполный по
 * построению — расширяйте по мере появления новых формулировок в
 * прайсах поставщиков.
 *
 * @return array<string,string[]>
 */
function amis_acc_import_category_dictionary() {
	return array(
		'network-analyzers'  => array( 'vector network analyzer', 'network analyzer' ),
		'spectrum-analyzers' => array( 'spectrum analyzer' ),
		'signal-generators'  => array( 'signal generator', 'function generator', 'waveform generator', 'pulse generator' ),
		'multimeters'        => array( 'multimeter', 'dmm accessories' ),
		'power-supplies'     => array( 'power supply' ),
		'electronic-loads'   => array( 'electronic load' ),
		'modular-systems'    => array( 'data acquisition', 'switch system' ),
		'oscilloscopes'      => array( 'oscilloscope' ),
		'power-amplifiers'   => array( 'power amplifier' ),
	);
}

/**
 * Есть ли среди фраз словаря такая, что входит в переданный текст.
 *
 * @param string $text Нормализованный (lowercase) текст.
 * @return string|null Слаг категории или null.
 */
function amis_acc_import_match_category( $text ) {

	foreach ( amis_acc_import_category_dictionary() as $slug => $phrases ) {
		foreach ( $phrases as $phrase ) {
			if ( false !== strpos( $text, $phrase ) ) {
				return $slug;
			}
		}
	}
	return null;
}

/* ==========================================================================
   РАЗБОР КОЛОНКИ «СОВМЕСТИМО С»
   ========================================================================== */

/**
 * Артикулы товаров, чей SKU начинается на префикс (case-insensitive),
 * опционально дополнительно отфильтрованные по словам, которые должны
 * встречаться в артикуле или названии.
 *
 * @param string   $prefix   Префикс SKU.
 * @param string[] $keywords Доп. слова-уточнители (могут быть пустыми).
 * @return int[] ID товаров.
 */
function amis_acc_import_find_by_prefix( $prefix, $keywords = array() ) {
	global $wpdb;

	if ( mb_strlen( $prefix ) < 2 ) {
		return array();
	}

	$like = $wpdb->esc_like( $prefix ) . '%';

	$ids = $wpdb->get_col(
		$wpdb->prepare(
			"SELECT p.ID FROM {$wpdb->posts} p
			INNER JOIN {$wpdb->postmeta} pm ON pm.post_id = p.ID AND pm.meta_key = '_sku'
			WHERE p.post_type = 'product' AND p.post_status = 'publish' AND pm.meta_value LIKE %s",
			$like
		)
	);

	$ids = array_map( 'absint', $ids );

	if ( ! $ids || ! $keywords ) {
		return $ids;
	}

	// Доп. слова должны встречаться либо в артикуле, либо в названии товара.
	return array_values(
		array_filter(
			$ids,
			static function ( $id ) use ( $keywords ) {
				$haystack = mb_strtolower( get_the_title( $id ) . ' ' . get_post_meta( $id, '_sku', true ) );
				foreach ( $keywords as $word ) {
					if ( false === mb_strpos( $haystack, mb_strtolower( $word ) ) ) {
						return false;
					}
				}
				return true;
			}
		)
	);
}

/**
 * Значащий префикс из «модельного» слова: отсекаем незначащие нули
 * в конце числовой части («DG5000» → «DG5»), чтобы искать по всему
 * семейству, а не по точному числу. У слов без цифр (MSO, COMMON,
 * ALL...) префикс — само слово целиком, естественным образом ничего
 * не найдёт для служебных пометок типа COMMON/ALL — это осознанно, не
 * приходится вести отдельный чёрный список.
 *
 * @param string $word Одно слово из колонки совместимости.
 * @return string
 */
function amis_acc_import_word_prefix( $word ) {

	if ( ! preg_match( '/^([A-Za-z]+)(\d+)/', $word, $m ) ) {
		return $word;
	}

	$digits = rtrim( $m[2], '0' );

	if ( '' === $digits ) {
		$digits = substr( $m[2], 0, 1 );
	}

	return $m[1] . $digits;
}

/**
 * ID товаров, с которыми совместим аксессуар, по «сырому» значению
 * колонки прайса (например, «Vector Network Analyzer» или
 * «DG5000 Pro» или «DP2000/DP900»).
 *
 * @param string $raw Значение колонки.
 * @return array{ids:int[],labels:string[]} labels — для отчёта, что
 *         именно сработало (категория/префикс), по каждой части.
 */
function amis_acc_import_resolve_targets( $raw ) {

	$ids    = array();
	$labels = array();

	$groups = preg_split( '/[\/,&]/', (string) $raw );

	foreach ( $groups as $group ) {

		$group = trim( $group );

		if ( '' === $group ) {
			continue;
		}

		$normalized = mb_strtolower( $group );

		$cat_slug = amis_acc_import_match_category( $normalized );

		if ( $cat_slug ) {
			$found = get_posts( array(
				'post_type'      => 'product',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'no_found_rows'  => true,
				'tax_query'      => array(
					array(
						'taxonomy' => 'product_cat',
						'field'    => 'slug',
						'terms'    => $cat_slug,
					),
				),
			) );

			if ( $found ) {
				$ids[]    = $found;
				$labels[] = 'категория «' . $cat_slug . '»';
			}
			continue;
		}

		$words = preg_split( '/\s+/', $group );
		$words = array_values( array_filter( $words ) );

		if ( ! $words ) {
			continue;
		}

		// «Pro» на конце — не отдельная модель, а уточнение к предыдущему слову.
		$qualifier = array();
		if ( count( $words ) > 1 && 'pro' === mb_strtolower( end( $words ) ) ) {
			$qualifier = array( array_pop( $words ) );
		}

		foreach ( $words as $word ) {

			$prefix = amis_acc_import_word_prefix( $word );
			$found  = amis_acc_import_find_by_prefix( $prefix, $qualifier );

			if ( $found ) {
				$ids[]    = $found;
				$labels[] = 'по артикулу «' . $prefix . ( $qualifier ? ' + ' . implode( ', ', $qualifier ) : '' ) . '»';
			}
		}
	}

	$flat = $ids ? array_unique( array_merge( ...$ids ) ) : array();

	return array(
		'ids'    => $flat,
		'labels' => $labels,
	);
}

/* ==========================================================================
   СОЗДАНИЕ ТОВАРА И ПРИВЯЗКА
   ========================================================================== */

/**
 * Термин категории по имени — берёт существующий или создаёт новый
 * (штатное поведение WooCommerce-импортёра при незнакомой категории).
 *
 * @param string $name Имя категории.
 * @return int Term ID.
 */
function amis_acc_import_get_or_create_category( $name ) {

	$term = get_term_by( 'name', $name, 'product_cat' );

	if ( $term ) {
		return $term->term_id;
	}

	$created = wp_insert_term( $name, 'product_cat' );

	return is_wp_error( $created ) ? 0 : $created['term_id'];
}

/**
 * Число из ячейки прайса: пробелы (обычные и неразрывные) — как
 * разделители тысяч, всё, что не цифра и не точка/запятая — отбрасываем
 * (так уходит мусор вроде «?» на месте кириллического «₴»/«р.»).
 *
 * @param string $raw Ячейка.
 * @return string Строка-число или ''.
 */
function amis_acc_import_parse_price( $raw ) {

	$clean = str_replace( array( ' ', "\xC2\xA0" ), '', (string) $raw );
	$clean = str_replace( ',', '.', $clean );
	$clean = preg_replace( '/[^0-9.]/', '', $clean );

	if ( '' === $clean || ! is_numeric( $clean ) ) {
		return '';
	}

	return (string) (float) $clean;
}

/**
 * Снимает привязку аксессуара со ВСЕХ приборов, где он сейчас стоит —
 * и в _amis_related_probes, и в _amis_related_acc (на случай, если тип
 * товара при повторной загрузке определился иначе). Нужно для того,
 * чтобы повторная загрузка исправленного CSV пересчитывала связи с
 * нуля, а не только добавляла новые поверх старых, неправильных.
 *
 * @param int $accessory_id ID аксессуара.
 */
function amis_acc_import_unlink_everywhere( $accessory_id ) {

	foreach ( array( '_amis_related_probes', '_amis_related_acc' ) as $meta_key ) {

		$query = new WP_Query( array(
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'no_found_rows'  => true,
			'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- то же обоснование, что и в amis_get_compatible_instruments() (inc/product-accessories.php): связок мало.
				array(
					'key'     => $meta_key,
					'value'   => 'i:' . (int) $accessory_id . ';',
					'compare' => 'LIKE',
				),
			),
		) );

		foreach ( $query->posts as $target_id ) {

			$linked = array_map( 'absint', (array) get_post_meta( $target_id, $meta_key, true ) );
			$linked = array_values( array_diff( $linked, array( (int) $accessory_id ) ) );

			if ( $linked ) {
				update_post_meta( $target_id, $meta_key, $linked );
			} else {
				delete_post_meta( $target_id, $meta_key );
			}
		}
	}
}

/**
 * Разбор одной строки CSV: создаёт товар (если такого SKU ещё нет) и
 * привязывает его к найденным приборам. Перед привязкой снимает все
 * старые связи этого аксессуара (см. amis_acc_import_unlink_everywhere) —
 * повторная загрузка исправленного файла пересчитывает связи с нуля,
 * а не наслаивает новые поверх неправильных старых.
 *
 * @param array $row Ассоц. массив 'sku','description','compat','price'.
 * @return array Данные строки для отчёта.
 */
function amis_acc_import_process_row( $row ) {

	$report = array(
		'sku'      => $row['sku'],
		'status'   => '',
		'targets'  => array(),
		'unmatched_reason' => '',
	);

	$existing_id = wc_get_product_id_by_sku( $row['sku'] );
	$is_probe    = false !== mb_stripos( $row['description'], 'probe' );
	$meta_key    = $is_probe ? '_amis_related_probes' : '_amis_related_acc';

	if ( $existing_id ) {
		$accessory_id     = $existing_id;
		$report['status'] = 'уже существует, только привязка';
	} else {

		$price = amis_acc_import_parse_price( $row['price'] );
		$name  = '' !== trim( $row['description'] ) ? $row['description'] : $row['sku'];

		$product = new WC_Product_Simple();
		$product->set_name( $name );
		$product->set_sku( $row['sku'] );
		$product->set_short_description( $row['description'] );
		if ( '' !== $price ) {
			$product->set_regular_price( $price );
		}
		// Черновик — данные из прайса поставщика часто неполные/сырые,
		// пусть перед публикацией кто-то посмотрит глазами.
		$product->set_status( 'draft' );

		$cat_name = $is_probe ? 'Пробники' : 'Аксессуары';
		$cat_id   = amis_acc_import_get_or_create_category( $cat_name );
		if ( $cat_id ) {
			$product->set_category_ids( array( $cat_id ) );
		}

		$accessory_id     = $product->save();
		$report['status'] = 'создан (черновик)';
	}

	if ( ! $accessory_id ) {
		$report['status']           = 'ошибка создания';
		$report['unmatched_reason'] = 'не удалось сохранить товар';
		return $report;
	}

	amis_acc_import_unlink_everywhere( $accessory_id );

	$resolved = amis_acc_import_resolve_targets( $row['compat'] );

	if ( ! $resolved['ids'] ) {
		$report['unmatched_reason'] = 'не распознано: «' . $row['compat'] . '»';
		return $report;
	}

	foreach ( $resolved['ids'] as $target_id ) {

		$linked = array_map( 'absint', (array) get_post_meta( $target_id, $meta_key, true ) );

		if ( in_array( (int) $accessory_id, $linked, true ) ) {
			continue;
		}

		$linked[] = (int) $accessory_id;
		update_post_meta( $target_id, $meta_key, $linked );

		$report['targets'][] = get_post_meta( $target_id, '_sku', true ) ?: ( 'ID' . $target_id );
	}

	return $report;
}

/* ==========================================================================
   CSV И АДМИНКА
   ========================================================================== */

/**
 * Пункт меню — под «Товары».
 */
function amis_acc_import_menu() {

	add_submenu_page(
		'edit.php?post_type=product',
		__( 'Импорт пробников и аксессуаров', 'amis' ),
		__( 'Импорт аксессуаров', 'amis' ),
		'manage_woocommerce',
		'amis-accessories-import',
		'amis_acc_import_page'
	);
}
add_action( 'admin_menu', 'amis_acc_import_menu' );

/**
 * Разбор файла целиком.
 *
 * @param string $tmp_path Путь к загруженному файлу.
 * @return array{rows:array,errors:string[]}
 */
function amis_acc_import_read_csv( $tmp_path ) {

	$errors = array();
	$rows   = array();

	$handle = fopen( $tmp_path, 'r' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fopen -- временный файл загрузки формы.

	if ( ! $handle ) {
		return array( 'rows' => array(), 'errors' => array( 'Не удалось открыть файл.' ) );
	}

	$first_line = fgets( $handle );

	if ( false === $first_line ) {
		fclose( $handle ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fclose
		return array( 'rows' => array(), 'errors' => array( 'Файл пустой.' ) );
	}

	$first_line = preg_replace( '/^\xEF\xBB\xBF/', '', $first_line );

	if ( ! mb_check_encoding( $first_line, 'UTF-8' ) ) {
		$first_line = mb_convert_encoding( $first_line, 'UTF-8', 'Windows-1251' );
	}

	$delimiter    = ',';
	$best_columns = 0;

	foreach ( array( ',', ';', "\t" ) as $candidate ) {
		$columns = count( str_getcsv( $first_line, $candidate ) );
		if ( $columns > $best_columns ) {
			$best_columns = $columns;
			$delimiter    = $candidate;
		}
	}

	$header = array_map(
		static function ( $col ) {
			return mb_strtolower( trim( (string) $col ) );
		},
		str_getcsv( $first_line, $delimiter )
	);

	$sku_col    = amis_acc_import_find_column( $header, array( 'sku', 'артикул' ) );
	$desc_col   = amis_acc_import_find_column( $header, array( 'short_description', 'description', 'описание' ) );
	$compat_col = amis_acc_import_find_column( $header, array( 'adapt', 'compatibility', 'совместимость', 'подходит', 'category' ) );
	$price_col  = amis_acc_import_find_column( $header, array( 'price', 'regular_price', 'цена' ) );

	if ( false === $sku_col || false === $compat_col ) {
		fclose( $handle ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fclose
		return array(
			'rows'   => array(),
			'errors' => array( 'В файле не нашлось колонок «sku» и «adapt» (или «compatibility»/«category»).' ),
		);
	}

	while ( false !== ( $line = fgets( $handle ) ) ) { // phpcs:ignore Squiz.PHP.DisallowMultipleAssignments.FoundInControlStructure

		if ( ! mb_check_encoding( $line, 'UTF-8' ) ) {
			$line = mb_convert_encoding( $line, 'UTF-8', 'Windows-1251' );
		}

		$row = str_getcsv( $line, $delimiter );

		$sku = isset( $row[ $sku_col ] ) ? trim( (string) $row[ $sku_col ] ) : '';

		if ( '' === $sku ) {
			continue;
		}

		$rows[] = array(
			'sku'         => $sku,
			'description' => false !== $desc_col && isset( $row[ $desc_col ] ) ? trim( (string) $row[ $desc_col ] ) : '',
			'compat'      => false !== $compat_col && isset( $row[ $compat_col ] ) ? trim( (string) $row[ $compat_col ] ) : '',
			'price'       => false !== $price_col && isset( $row[ $price_col ] ) ? trim( (string) $row[ $price_col ] ) : '',
		);
	}

	fclose( $handle ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fclose

	return array( 'rows' => $rows, 'errors' => $errors );
}

/**
 * Индекс колонки по одному из принятых названий.
 *
 * @param string[] $header Заголовки, уже в lowercase.
 * @param string[] $names  Принятые варианты названия.
 * @return int|false
 */
function amis_acc_import_find_column( $header, $names ) {
	foreach ( $names as $name ) {
		$found = array_search( $name, $header, true );
		if ( false !== $found ) {
			return $found;
		}
	}
	return false;
}

/**
 * Страница в админке: форма загрузки + отчёт после обработки.
 */
function amis_acc_import_page() {

	if ( ! current_user_can( 'manage_woocommerce' ) ) {
		wp_die( esc_html__( 'Недостаточно прав.', 'amis' ) );
	}

	$report_rows = null;
	$parse_errors = array();

	if ( isset( $_POST['amis_acc_import_nonce'] )
		&& wp_verify_nonce( sanitize_key( $_POST['amis_acc_import_nonce'] ), 'amis_acc_import' )
		&& ! empty( $_FILES['amis_acc_file']['tmp_name'] )
	) {

		$file = $_FILES['amis_acc_file']; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- проверяется ниже (код ошибки, расширение), путь не выводится и не используется напрямую.

		if ( UPLOAD_ERR_OK !== $file['error'] ) {
			$parse_errors[] = 'Ошибка загрузки файла.';
		} elseif ( 'csv' !== strtolower( pathinfo( $file['name'], PATHINFO_EXTENSION ) ) ) {
			$parse_errors[] = 'Нужен файл в формате CSV (сохраните Excel-файл как CSV и загрузите снова).';
		} else {

			$parsed       = amis_acc_import_read_csv( $file['tmp_name'] );
			$parse_errors = $parsed['errors'];

			if ( $parsed['rows'] ) {
				$report_rows = array();
				foreach ( $parsed['rows'] as $row ) {
					$report_rows[] = amis_acc_import_process_row( $row );
				}
			}
		}
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Импорт пробников и аксессуаров', 'amis' ); ?></h1>

		<p>
			<?php esc_html_e( 'CSV с колонками sku, short_description (или description), adapt (или compatibility/category) — с чем совместим, price. Новые товары создаются черновиками и попадают в «Пробники» или «Аксессуары» (по слову «probe» в описании). Уже существующие по SKU не дублируются — только привязка к прибору.', 'amis' ); ?>
		</p>

		<form method="post" enctype="multipart/form-data">
			<?php wp_nonce_field( 'amis_acc_import', 'amis_acc_import_nonce' ); ?>
			<input type="file" name="amis_acc_file" accept=".csv" required>
			<?php submit_button( __( 'Загрузить и обработать', 'amis' ) ); ?>
		</form>

		<?php if ( $parse_errors ) : ?>
			<div class="notice notice-error">
				<p><strong><?php esc_html_e( 'Ошибки:', 'amis' ); ?></strong></p>
				<ul>
					<?php foreach ( $parse_errors as $error ) : ?>
						<li><?php echo esc_html( $error ); ?></li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php endif; ?>

		<?php if ( null !== $report_rows ) : ?>

			<?php
			$matched   = array_filter( $report_rows, static function ( $r ) { return ! empty( $r['targets'] ); } );
			$unmatched = array_filter( $report_rows, static function ( $r ) { return empty( $r['targets'] ); } );
			?>

			<div class="notice notice-success">
				<p>
					<?php
					printf(
						/* translators: 1: обработано строк, 2: с привязкой, 3: без привязки. */
						esc_html__( 'Обработано строк: %1$d. С привязкой к прибору: %2$d. Без привязки (см. таблицу ниже): %3$d.', 'amis' ),
						count( $report_rows ),
						count( $matched ),
						count( $unmatched )
					);
					?>
				</p>
			</div>

			<h2><?php esc_html_e( 'Обработано', 'amis' ); ?></h2>
			<table class="widefat striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Артикул', 'amis' ); ?></th>
						<th><?php esc_html_e( 'Товар', 'amis' ); ?></th>
						<th><?php esc_html_e( 'Привязан к', 'amis' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $report_rows as $row ) : ?>
						<tr>
							<td><?php echo esc_html( $row['sku'] ); ?></td>
							<td><?php echo esc_html( $row['status'] ); ?></td>
							<td>
								<?php if ( $row['targets'] ) : ?>
									<?php echo esc_html( implode( ', ', $row['targets'] ) ); ?>
								<?php else : ?>
									<em><?php echo esc_html( $row['unmatched_reason'] ); ?></em>
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

		<?php endif; ?>
	</div>
	<?php
}
