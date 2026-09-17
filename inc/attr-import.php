<?php
/**
 * Массовое заполнение глобальных атрибутов товара по SKU из CSV.
 *
 * Тот же принцип, что и price-import.php/rename-import.php: находит товар
 * по точному совпадению артикула и меняет только атрибуты — ничего не
 * создаёт для несовпавших строк. Атрибуты — глобальные таксономии
 * WooCommerce (pa_bandwidth и т.д.), те же, что используются в фильтрах
 * и в карте характеристик (inc/product-map.php).
 *
 * @package amis
 */

defined( 'ABSPATH' ) || exit;

/**
 * Колонка CSV → слаг глобального атрибута (без префикса pa_) + режим.
 * Дополнить массив — единственное, что нужно, если появятся новые
 * колонки/атрибуты для массового заполнения.
 *
 * 'strict' => true — термин должен уже существовать в таксономии,
 * новый НЕ создаётся, несовпадение считается ошибкой. Нужно для
 * атрибутов с фиксированным набором терминов, завязанных на что-то
 * ещё в коде — сейчас это только 'frequency-range' (ровно 6 терминов,
 * подписи шкалы в hero, README «Шаг 3»; opечатка в CSV создала бы
 * 7-й «сиротский» термин, не подключённый к виджету шкалы).
 * Для остальных полей — свободный текст, отсутствующий термин
 * создаётся сам (это и есть основная причина существования инструмента).
 */
function amis_attr_import_supported_fields() {
	return array(
		'bandwidth'       => array( 'attribute' => 'bandwidth', 'strict' => false ),
		'channels'        => array( 'attribute' => 'channels', 'strict' => false ),
		'sample-rate'     => array( 'attribute' => 'sample-rate', 'strict' => false ),
		'sample_rate'     => array( 'attribute' => 'sample-rate', 'strict' => false ), // на случай, если в CSV подчёркивание вместо дефиса.
		'memory'          => array( 'attribute' => 'memory', 'strict' => false ),
		'series'          => array( 'attribute' => 'series', 'strict' => false ),
		// Анализаторы спектра (inc/product-map.php, категория spectrum-analyzers).
		'freq-range'      => array( 'attribute' => 'freq-range', 'strict' => false ),
		'rbw'             => array( 'attribute' => 'rbw', 'strict' => false ),
		'danl'            => array( 'attribute' => 'danl', 'strict' => false ),
		'phase-noise'     => array( 'attribute' => 'phase-noise', 'strict' => false ),
		// Шкала на главной (README «Шаг 3») — строго один из 6 готовых терминов.
		'frequency-range' => array( 'attribute' => 'frequency-range', 'strict' => true ),
	);
}

/**
 * Пункт меню — под «Товары».
 */
function amis_attr_import_menu() {

	add_submenu_page(
		'edit.php?post_type=product',
		__( 'Импорт характеристик по SKU', 'amis' ),
		__( 'Импорт характеристик', 'amis' ),
		'manage_woocommerce',
		'amis-attr-import',
		'amis_attr_import_page'
	);
}
add_action( 'admin_menu', 'amis_attr_import_menu' );

/**
 * Ставит одно значение глобального атрибута товару: находит термин по
 * названию в нужной таксономии (создаёт, если такого значения ещё не
 * было и не включён строгий режим) и регистрирует таксономию в
 * _product_attributes, иначе WooCommerce не покажет атрибут как
 * «включённый» на товаре.
 *
 * Заменяет прежнее значение этого атрибута, а не добавляет к нему —
 * это характеристики-одиночки («Полоса: 100 МГц»), не список тегов.
 *
 * @param int    $product_id ID товара.
 * @param string $taxonomy   Например, 'pa_bandwidth'.
 * @param string $value      Значение, как в ячейке CSV.
 * @param bool   $strict     true — термин должен уже существовать,
 *                           новый не создаётся (см. amis_attr_import_supported_fields()).
 * @return bool|string true — успех, 'not_found' — строгий режим и термина нет, false — прочая ошибка.
 */
function amis_attr_import_set_value( $product_id, $taxonomy, $value, $strict = false ) {

	$value = trim( (string) $value );

	if ( '' === $value || ! taxonomy_exists( $taxonomy ) ) {
		return false;
	}

	$term = get_term_by( 'name', $value, $taxonomy );

	if ( ! $term ) {

		if ( $strict ) {
			return 'not_found';
		}

		$inserted = wp_insert_term( $value, $taxonomy );
		if ( is_wp_error( $inserted ) ) {
			return false;
		}
		$term_id = $inserted['term_id'];
	} else {
		$term_id = $term->term_id;
	}

	wp_set_object_terms( $product_id, array( $term_id ), $taxonomy, false );

	$attributes = get_post_meta( $product_id, '_product_attributes', true );
	if ( ! is_array( $attributes ) ) {
		$attributes = array();
	}

	$attributes[ $taxonomy ] = array(
		'name'         => $taxonomy,
		'value'        => '',
		'is_visible'   => 1,
		'is_variation' => 0,
		'is_taxonomy'  => 1,
	);
	update_post_meta( $product_id, '_product_attributes', $attributes );

	return true;
}

/**
 * Разбор загруженного CSV: результат по каждой строке.
 *
 * @param string $tmp_path Путь к временно загруженному файлу.
 * @return array{updated:array,skipped:array,errors:array}
 */
function amis_attr_import_process( $tmp_path ) {

	$result = array(
		'updated' => array(),
		'skipped' => array(),
		'errors'  => array(),
	);

	$handle = fopen( $tmp_path, 'r' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fopen -- временный файл загрузки формы.

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

	// BOM и разделитель ";" у Excel в русской локали — та же обработка,
	// что и в price-import.php/rename-import.php.
	$first_line = preg_replace( '/^\xEF\xBB\xBF/', '', $first_line );

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
	$header = array_map(
		static function ( $col ) {
			return mb_strtolower( trim( (string) $col ) );
		},
		$header
	);

	$sku_col = array_search( 'sku', $header, true );

	if ( false === $sku_col ) {
		$result['errors'][] = __( 'В первой строке файла не нашлось колонки «sku».', 'amis' );
		fclose( $handle ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fclose
		return $result;
	}

	// Какие из поддерживаемых колонок реально есть в этом файле.
	$field_cols = array(); // slug атрибута => ['col' => номер колонки, 'strict' => bool].

	foreach ( amis_attr_import_supported_fields() as $col_name => $config ) {
		$idx = array_search( $col_name, $header, true );
		if ( false !== $idx ) {
			$field_cols[ $config['attribute'] ] = array(
				'col'    => $idx,
				'strict' => $config['strict'],
			);
		}
	}

	if ( ! $field_cols ) {
		$result['errors'][] = __( 'Не нашлось ни одной колонки из поддерживаемых: bandwidth, channels, sample-rate, memory, series, freq-range, rbw, danl, phase-noise, frequency-range.', 'amis' );
		fclose( $handle ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fclose
		return $result;
	}

	while ( false !== ( $row = fgetcsv( $handle, 0, $delimiter ) ) ) { // phpcs:ignore Squiz.PHP.DisallowMultipleAssignments.FoundInControlStructure

		$sku = isset( $row[ $sku_col ] ) ? trim( (string) $row[ $sku_col ] ) : '';

		if ( '' === $sku ) {
			continue;
		}

		$product_id = wc_get_product_id_by_sku( $sku );

		if ( ! $product_id ) {
			$result['skipped'][] = $sku;
			continue;
		}

		$set_fields = array();

		foreach ( $field_cols as $attr_slug => $col ) {

			$value = isset( $row[ $col['col'] ] ) ? $row[ $col['col'] ] : '';
			$value = trim( (string) $value );

			if ( '' === $value ) {
				continue; // Пустая ячейка — не трогаем то, что уже стоит на товаре.
			}

			$outcome = amis_attr_import_set_value( $product_id, 'pa_' . $attr_slug, $value, $col['strict'] );

			if ( true === $outcome ) {
				$set_fields[] = $attr_slug . ': ' . $value;
			} elseif ( 'not_found' === $outcome ) {
				$result['errors'][] = sprintf(
					/* translators: 1: артикул, 2: слаг атрибута, 3: значение из CSV. */
					__( '%1$s: для «%2$s» нет термина «%3$s» — строгий режим, новый не создан. Проверьте написание или заведите термин в Товары → Атрибуты сами.', 'amis' ),
					$sku,
					$attr_slug,
					$value
				);
			}
		}

		if ( $set_fields ) {
			wc_delete_product_transients( $product_id );
			$result['updated'][] = array(
				'sku'    => $sku,
				'fields' => $set_fields,
			);
		} else {
			$result['skipped'][] = $sku . ' (' . __( 'нет данных в файле', 'amis' ) . ')';
		}
	}

	fclose( $handle ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fclose

	return $result;
}

/**
 * Страница в админке: форма загрузки + отчёт после обработки.
 */
function amis_attr_import_page() {

	if ( ! current_user_can( 'manage_woocommerce' ) ) {
		wp_die( esc_html__( 'Недостаточно прав.', 'amis' ) );
	}

	$result = null;

	if ( isset( $_POST['amis_attr_import_nonce'] )
		&& wp_verify_nonce( sanitize_key( $_POST['amis_attr_import_nonce'] ), 'amis_attr_import' )
		&& ! empty( $_FILES['amis_attr_file']['tmp_name'] )
	) {

		$file = $_FILES['amis_attr_file']; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- проверяется ниже (код ошибки, расширение).

		if ( UPLOAD_ERR_OK !== $file['error'] ) {
			$result = array( 'errors' => array( __( 'Ошибка загрузки файла.', 'amis' ) ), 'updated' => array(), 'skipped' => array() );
		} else {
			$ext = strtolower( pathinfo( $file['name'], PATHINFO_EXTENSION ) );

			if ( 'csv' !== $ext ) {
				$result = array(
					'errors'  => array( __( 'Нужен файл в формате CSV (сохраните Excel-файл как CSV и загрузите снова).', 'amis' ) ),
					'updated' => array(),
					'skipped' => array(),
				);
			} else {
				$result = amis_attr_import_process( $file['tmp_name'] );
			}
		}
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Импорт характеристик по SKU', 'amis' ); ?></h1>

		<p>
			<?php esc_html_e( 'Загрузите CSV с колонкой «sku» и любыми из: bandwidth, channels, sample-rate (или sample_rate), memory, series, freq-range, rbw, danl, phase-noise, frequency-range. Заполняются только те колонки, что реально есть в файле, и только непустые ячейки — то, что уже стоит на товаре и не упомянуто в файле, не трогается. Значений, которых ещё не было среди терминов атрибута, — создаются автоматически, КРОМЕ frequency-range: там ровно 6 готовых терминов шкалы на главной, новый не создастся — при несовпадении строка попадёт в «Ошибки», проверьте написание.', 'amis' ); ?>
		</p>

		<form method="post" enctype="multipart/form-data">
			<?php wp_nonce_field( 'amis_attr_import', 'amis_attr_import_nonce' ); ?>
			<input type="file" name="amis_attr_file" accept=".csv" required>
			<?php submit_button( __( 'Загрузить и заполнить характеристики', 'amis' ) ); ?>
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
						/* translators: 1: обновлено, 2: пропущено. */
						esc_html__( 'Обновлено товаров: %1$d. Пропущено: %2$d.', 'amis' ),
						count( $result['updated'] ),
						count( $result['skipped'] )
					);
					?>
				</p>
			</div>

			<?php if ( ! empty( $result['updated'] ) ) : ?>
				<h2><?php esc_html_e( 'Обновлено', 'amis' ); ?></h2>
				<table class="widefat striped">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Артикул', 'amis' ); ?></th>
							<th><?php esc_html_e( 'Заполненные поля', 'amis' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $result['updated'] as $row ) : ?>
							<tr>
								<td><?php echo esc_html( $row['sku'] ); ?></td>
								<td><?php echo esc_html( implode( '; ', $row['fields'] ) ); ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>

			<?php if ( ! empty( $result['skipped'] ) ) : ?>
				<h2><?php esc_html_e( 'Пропущено', 'amis' ); ?></h2>
				<p><?php echo esc_html( implode( ', ', $result['skipped'] ) ); ?></p>
			<?php endif; ?>

		<?php endif; ?>
	</div>
	<?php
}
