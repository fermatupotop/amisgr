<?php
/**
 * Обновление названий товаров по SKU из CSV.
 *
 * Тот же принцип, что и price-import.php: находит товар по точному
 * совпадению артикула и меняет только название — ничего не создаёт,
 * несовпавшие артикулы просто пропускает.
 *
 * Понадобился после того, как импорт аксессуаров (inc/accessories-import.php)
 * создал товары с названием прямо из английского short_description —
 * этим инструментом название переводится/поправляется одним файлом,
 * без захода в каждый товар руками.
 *
 * @package amis
 */

defined( 'ABSPATH' ) || exit;

/**
 * Пункт меню — под «Товары».
 */
function amis_rename_import_menu() {

	add_submenu_page(
		'edit.php?post_type=product',
		__( 'Обновить названия по SKU', 'amis' ),
		__( 'Обновить названия', 'amis' ),
		'manage_woocommerce',
		'amis-rename-import',
		'amis_rename_import_page'
	);
}
add_action( 'admin_menu', 'amis_rename_import_menu' );

/**
 * Разбор загруженного CSV: результат по каждой строке.
 *
 * @param string $tmp_path Путь к временно загруженному файлу.
 * @return array{updated:array,skipped:array,errors:array}
 */
function amis_rename_import_process( $tmp_path ) {

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

	$sku_col  = array_search( 'sku', $header, true );
	$name_col = array_search( 'name', $header, true );

	if ( false === $sku_col || false === $name_col ) {
		$result['errors'][] = __( 'В первой строке файла не нашлось колонок «sku» и «name».', 'amis' );
		fclose( $handle ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fclose
		return $result;
	}

	while ( false !== ( $row = fgetcsv( $handle, 0, $delimiter ) ) ) { // phpcs:ignore Squiz.PHP.DisallowMultipleAssignments.FoundInControlStructure

		$sku = isset( $row[ $sku_col ] ) ? trim( (string) $row[ $sku_col ] ) : '';

		if ( '' === $sku ) {
			continue;
		}

		$name = isset( $row[ $name_col ] ) ? trim( (string) $row[ $name_col ] ) : '';

		if ( '' === $name ) {
			$result['errors'][] = sprintf(
				/* translators: %s — артикул. */
				__( '%s: пустое название, пропущено.', 'amis' ),
				$sku
			);
			continue;
		}

		$product_id = wc_get_product_id_by_sku( $sku );

		if ( ! $product_id ) {
			$result['skipped'][] = $sku;
			continue;
		}

		$old_name = get_the_title( $product_id );

		wp_update_post( array(
			'ID'         => $product_id,
			'post_title' => $name,
		) );

		$result['updated'][] = array(
			'sku'      => $sku,
			'old_name' => $old_name,
			'new_name' => $name,
		);
	}

	fclose( $handle ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fclose

	return $result;
}

/**
 * Страница в админке: форма загрузки + отчёт после обработки.
 */
function amis_rename_import_page() {

	if ( ! current_user_can( 'manage_woocommerce' ) ) {
		wp_die( esc_html__( 'Недостаточно прав.', 'amis' ) );
	}

	$result = null;

	if ( isset( $_POST['amis_rename_import_nonce'] )
		&& wp_verify_nonce( sanitize_key( $_POST['amis_rename_import_nonce'] ), 'amis_rename_import' )
		&& ! empty( $_FILES['amis_rename_file']['tmp_name'] )
	) {

		$file = $_FILES['amis_rename_file']; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- проверяется ниже (код ошибки, расширение).

		if ( UPLOAD_ERR_OK !== $file['error'] ) {
			$result = array( 'errors' => array( __( 'Ошибка загрузки файла.', 'amis' ) ), 'updated' => array(), 'skipped' => array() );
		} else {
			$ext = strtolower( pathinfo( $file['name'], PATHINFO_EXTENSION ) );

			if ( 'csv' !== $ext ) {
				$result = array(
					'errors'  => array( __( 'Нужен файл в формате CSV.', 'amis' ) ),
					'updated' => array(),
					'skipped' => array(),
				);
			} else {
				$result = amis_rename_import_process( $file['tmp_name'] );
			}
		}
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Обновить названия товаров по SKU', 'amis' ); ?></h1>

		<p><?php esc_html_e( 'CSV с колонками «sku» и «name». Меняет только название у товара с совпавшим артикулом — остальные поля не трогает. Несовпавшие артикулы пропускаются.', 'amis' ); ?></p>

		<form method="post" enctype="multipart/form-data">
			<?php wp_nonce_field( 'amis_rename_import', 'amis_rename_import_nonce' ); ?>
			<input type="file" name="amis_rename_file" accept=".csv" required>
			<?php submit_button( __( 'Загрузить и обновить названия', 'amis' ) ); ?>
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
						esc_html__( 'Обновлено названий: %1$d. Пропущено (нет на сайте): %2$d.', 'amis' ),
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
							<th><?php esc_html_e( 'Было', 'amis' ); ?></th>
							<th><?php esc_html_e( 'Стало', 'amis' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $result['updated'] as $row ) : ?>
							<tr>
								<td><?php echo esc_html( $row['sku'] ); ?></td>
								<td><?php echo esc_html( $row['old_name'] ); ?></td>
								<td><?php echo esc_html( $row['new_name'] ); ?></td>
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
