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
 * Колонка CSV → слаг глобального атрибута (без префикса pa_).
 * Дополнить массив — единственное, что нужно, если появятся новые
 * колонки/атрибуты для массового заполнения.
 */
function amis_attr_import_supported_fields() {
	return array(
		'bandwidth'   => 'bandwidth',
		'channels'    => 'channels',
		'sample-rate' => 'sample-rate',
		'sample_rate' => 'sample-rate', // на случай, если в CSV подчёркивание вместо дефиса.
		'memory'      => 'memory',
		'series'      => 'series',
		// Анализаторы спектра (inc/product-map.php, категория spectrum-analyzers).
		'freq-range'  => 'freq-range',
		'rbw'         => 'rbw',
		'danl'        => 'danl',
		'phase-noise' => 'phase-noise',
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
 * было) и регистрирует таксономию в _product_attributes, иначе
 * WooCommerce не покажет атрибут как «включённый» на товаре.
 *
 * Заменяет прежнее значение этого атрибута, а не добавляет к нему —
 * это характеристики-одиночки («Полоса: 100 МГц»), не список тегов.
 *
 * @param int    $product_id ID товара.
 * @param string $taxonomy   Например, 'pa_bandwidth'.
 * @param string $value      Значение, как в ячейке CSV.
 * @return bool
 */
function amis_attr_import_set_value( $product_id, $taxonomy, $value ) {

	$value = trim( (string) $value );

	if ( '' === $value || ! taxonomy_exists( $taxonomy ) ) {
		return false;
	}

	$term = get_term_by( 'name', $value, $taxonomy );

	if ( ! $term ) {
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
	$field_cols = array(); // slug атрибута => номер колонки.

	foreach ( amis_attr_import_supported_fields() as $col_name => $attr_slug ) {
		$idx = array_search( $col_name, $header, true );
		if ( false !== $idx ) {
			$field_cols[ $attr_slug ] = $idx;
		}
	}

	if ( ! $field_cols ) {
		$result['errors'][] = __( 'Не нашлось ни одной колонки из поддерживаемых: bandwidth, channels, sample-rate, memory, series, freq-range, rbw, danl, phase-noise.', 'amis' );
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

		foreach ( $field_cols as $attr_slug => $col_idx ) {

			$value = isset( $row[ $col_idx ] ) ? $row[ $col_idx ] : '';

			if ( '' === trim( (string) $value ) ) {
				continue; // Пустая ячейка — не трогаем то, что уже стоит на товаре.
			}

			$ok = amis_attr_import_set_value( $product_id, 'pa_' . $attr_slug, $value );

			if ( $ok ) {
				$set_fields[] = $attr_slug . ': ' . trim( (string) $value );
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
			<?php esc_html_e( 'Загрузите CSV с колонкой «sku» и любыми из: bandwidth, channels, sample-rate (или sample_rate), memory, series, freq-range, rbw, danl, phase-noise. Заполняются только те колонки, что реально есть в файле, и только непустые ячейки — то, что уже стоит на товаре и не упомянуто в файле, не трогается. Значений, которых ещё не было среди терминов атрибута, — создаются автоматически.', 'amis' ); ?>
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
