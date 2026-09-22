<?php
/**
 * Обновление наличия товаров из CSV-файла складских остатков.
 *
 * Тот же приём, что и у inc/price-import.php: обновляет только то, что уже
 * опубликовано на сайте (совпадение по артикулу), ничего не создаёт. Файл —
 * CSV, не .xlsx (Excel: «Файл → Сохранить как → CSV»). Первая строка —
 * заголовки, среди них должны быть колонки sku и остаток/количество
 * (регистр и порядок колонок не важны, остальные колонки игнорируются).
 *
 * Количество > 0 — товар переводится в «В наличии» с этим количеством на
 * складе (управление остатками включается автоматически). Количество 0 —
 * товар переводится в «Нет на складе», на сайте это и есть «Под заказ»
 * (см. amis_stock_state() в inc/queries.php — она не различает 0 и
 * отсутствие управления остатками, обе показывают «Под заказ»).
 *
 * @package amis
 */

defined( 'ABSPATH' ) || exit;

/**
 * Пункт меню — под «Товары», не отдельный топ-левел пункт.
 */
function amis_stock_import_menu() {

	add_submenu_page(
		'edit.php?post_type=product',
		__( 'Обновить наличие из остатков', 'amis' ),
		__( 'Обновить наличие', 'amis' ),
		'manage_woocommerce',
		'amis-stock-import',
		'amis_stock_import_page'
	);
}
add_action( 'admin_menu', 'amis_stock_import_menu' );

/**
 * Число из ячейки остатков в целое количество на складе. Отрицательные
 * значения (встречаются в выгрузках 1С при пересорте) приводим к нулю —
 * отрицательного остатка на сайте быть не должно.
 *
 * @param string $raw Значение ячейки.
 * @return int|null Количество, null — если это не число.
 */
function amis_stock_import_parse_qty( $raw ) {

	$clean = str_replace( array( ' ', "\xC2\xA0" ), '', (string) $raw );
	$clean = str_replace( ',', '.', $clean );

	if ( '' === $clean || ! is_numeric( $clean ) ) {
		return null;
	}

	return max( 0, (int) round( (float) $clean ) );
}

/**
 * Разбор загруженного CSV: результат по каждой строке.
 *
 * @param string $tmp_path Путь к временно загруженному файлу.
 * @return array{updated:array,skipped:array,errors:array}
 */
function amis_stock_import_process( $tmp_path ) {

	$result = array(
		'updated' => array(),
		'skipped' => array(),
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

	// Excel в русской локали сохраняет CSV с разделителем «;», а не «,»,
	// и почти всегда добавляет невидимый BOM в начало файла — оба случая
	// ломают fgetcsv() с настройками по умолчанию, если их не разобрать руками.
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

	// Разные выгрузки называют эти колонки по-разному — принимаем несколько
	// распространённых вариантов вместо жёсткого требования к одному имени.
	$sku_names = array( 'sku', 'артикул' );
	$qty_names = array( 'qty', 'quantity', 'stock', 'остаток', 'количество', 'кол-во', 'наличие' );

	$sku_col = false;
	$qty_col = false;

	foreach ( $sku_names as $name ) {
		$found = array_search( $name, $header, true );
		if ( false !== $found ) {
			$sku_col = $found;
			break;
		}
	}

	foreach ( $qty_names as $name ) {
		$found = array_search( $name, $header, true );
		if ( false !== $found ) {
			$qty_col = $found;
			break;
		}
	}

	if ( false === $sku_col || false === $qty_col ) {
		$result['errors'][] = __( 'В первой строке файла не нашлось колонок «sku» и «qty» (или «артикул»/«остаток»/«количество»).', 'amis' );
		fclose( $handle ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fclose
		return $result;
	}

	while ( false !== ( $row = fgetcsv( $handle, 0, $delimiter ) ) ) { // phpcs:ignore Squiz.PHP.DisallowMultipleAssignments.FoundInControlStructure

		$sku = isset( $row[ $sku_col ] ) ? trim( (string) $row[ $sku_col ] ) : '';

		if ( '' === $sku ) {
			continue; // Пустая строка — не считаем ни успехом, ни ошибкой.
		}

		$qty = isset( $row[ $qty_col ] ) ? amis_stock_import_parse_qty( $row[ $qty_col ] ) : null;

		if ( null === $qty ) {
			$result['errors'][] = sprintf(
				/* translators: %s — артикул. */
				__( '%s: не удалось прочитать остаток.', 'amis' ),
				$sku
			);
			continue;
		}

		$product_id = wc_get_product_id_by_sku( $sku );

		if ( ! $product_id ) {
			$result['skipped'][] = $sku;
			continue;
		}

		$product = wc_get_product( $product_id );

		if ( ! $product ) {
			$result['skipped'][] = $sku;
			continue;
		}

		$old_status = $product->get_stock_status();
		$old_qty    = $product->get_stock_quantity();

		$product->set_manage_stock( true );
		$product->set_stock_quantity( $qty );
		$product->set_stock_status( $qty > 0 ? 'instock' : 'outofstock' );
		$product->save();

		$result['updated'][] = array(
			'sku'       => $sku,
			'name'      => $product->get_name(),
			'old_qty'   => $old_qty,
			'new_qty'   => $qty,
			'old_status' => $old_status,
			'new_status' => $product->get_stock_status(),
		);
	}

	fclose( $handle ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fclose

	return $result;
}

/**
 * Человекочитаемая подпись статуса наличия для отчёта — те же значения,
 * что понимает WooCommerce (instock/outofstock/onbackorder).
 *
 * @param string $status Статус как хранит WooCommerce.
 * @return string
 */
function amis_stock_import_status_label( $status ) {

	$labels = array(
		'instock'    => __( 'В наличии', 'amis' ),
		'outofstock' => __( 'Под заказ', 'amis' ),
		'onbackorder' => __( 'Под заказ (предзаказ)', 'amis' ),
	);

	return isset( $labels[ $status ] ) ? $labels[ $status ] : $status;
}

/**
 * Страница в админке: форма загрузки + отчёт после обработки.
 */
function amis_stock_import_page() {

	if ( ! current_user_can( 'manage_woocommerce' ) ) {
		wp_die( esc_html__( 'Недостаточно прав.', 'amis' ) );
	}

	$result = null;

	if ( isset( $_POST['amis_stock_import_nonce'] )
		&& wp_verify_nonce( sanitize_key( $_POST['amis_stock_import_nonce'] ), 'amis_stock_import' )
		&& ! empty( $_FILES['amis_stock_file']['tmp_name'] )
	) {

		$file = $_FILES['amis_stock_file']; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- обрабатывается ниже: проверка кода ошибки, затем расширения, путь не выводится и не используется как есть.

		if ( UPLOAD_ERR_OK !== $file['error'] ) {
			$result = array(
				'errors'  => array( __( 'Ошибка загрузки файла.', 'amis' ) ),
				'updated' => array(),
				'skipped' => array(),
			);
		} else {
			$ext = strtolower( pathinfo( $file['name'], PATHINFO_EXTENSION ) );

			if ( 'csv' !== $ext ) {
				$result = array(
					'errors'  => array( __( 'Нужен файл в формате CSV (сохраните Excel-файл как CSV и загрузите снова).', 'amis' ) ),
					'updated' => array(),
					'skipped' => array(),
				);
			} else {
				$result = amis_stock_import_process( $file['tmp_name'] );
			}
		}
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Обновить наличие из остатков', 'amis' ); ?></h1>

		<p>
			<?php esc_html_e( 'Загрузите CSV-файл с колонками «sku» и «qty» (порядок и регистр не важны — принимаются также «артикул»/«остаток»/«количество»/«кол-во»). Наличие обновится только у товаров, которые уже есть на сайте и совпали по артикулу.', 'amis' ); ?>
		</p>
		<p>
			<?php esc_html_e( 'Количество больше нуля — товар становится «В наличии» с этим остатком. Ноль — «Под заказ». Управление остатками включается автоматически.', 'amis' ); ?>
		</p>
		<p>
			<?php esc_html_e( 'Если файл в Excel — сохраните его через «Файл → Сохранить как → CSV» перед загрузкой.', 'amis' ); ?>
		</p>

		<form method="post" enctype="multipart/form-data">
			<?php wp_nonce_field( 'amis_stock_import', 'amis_stock_import_nonce' ); ?>
			<input type="file" name="amis_stock_file" accept=".csv" required>
			<?php submit_button( __( 'Загрузить и обновить наличие', 'amis' ) ); ?>
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
						/* translators: 1: сколько обновлено, 2: сколько пропущено. */
						esc_html__( 'Обновлено товаров: %1$d. Пропущено (нет на сайте): %2$d.', 'amis' ),
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
							<th><?php esc_html_e( 'Товар', 'amis' ); ?></th>
							<th><?php esc_html_e( 'Было', 'amis' ); ?></th>
							<th><?php esc_html_e( 'Стало', 'amis' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $result['updated'] as $row ) : ?>
							<tr>
								<td><?php echo esc_html( $row['sku'] ); ?></td>
								<td><?php echo esc_html( $row['name'] ); ?></td>
								<td>
									<?php
									echo esc_html( amis_stock_import_status_label( $row['old_status'] ) );
									if ( null !== $row['old_qty'] ) {
										echo ' (' . esc_html( $row['old_qty'] ) . ')';
									}
									?>
								</td>
								<td>
									<?php
									echo esc_html( amis_stock_import_status_label( $row['new_status'] ) );
									echo ' (' . esc_html( $row['new_qty'] ) . ')';
									?>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>

			<?php if ( ! empty( $result['skipped'] ) ) : ?>
				<h2><?php esc_html_e( 'Пропущено — нет такого артикула на сайте', 'amis' ); ?></h2>
				<p><?php echo esc_html( implode( ', ', $result['skipped'] ) ); ?></p>
			<?php endif; ?>

		<?php endif; ?>
	</div>
	<?php
}
