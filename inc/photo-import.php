<?php
/**
 * Массовая загрузка фото товаров из ZIP-архива по артикулу.
 *
 * Имя файла (без расширения) = артикул товара. Первое фото по артикулу
 * (без цифры на конце, либо с наименьшим номером) становится главным
 * изображением товара, остальные с суффиксом «_N» — галереей, в порядке
 * номера. Пример на 3 фото: DHO4804.jpg, DHO4804_2.jpg, DHO4804_3.jpg.
 *
 * Сначала пробуем найти товар по ВСЕМУ имени файла как есть (без обрезки
 * суффикса) — на случай, если у товара сам артикул оканчивается на
 * «_2»-подобную комбинацию. Только если такого товара нет — считаем
 * хвост «_N» номером фото в галерее и ищем товар по обрезанному артикулу.
 *
 * Товары, к которым нет фото в архиве, не трогаем. SKU из архива, для
 * которых не нашлось товара на сайте, — просто в отчёте, ничего не
 * создаём (тот же принцип, что и у price-import.php/stock-import.php).
 *
 * @package amis
 */

defined( 'ABSPATH' ) || exit;

/**
 * Пункт меню — под «Товары».
 */
function amis_photo_import_menu() {

	add_submenu_page(
		'edit.php?post_type=product',
		__( 'Импорт фото товаров', 'amis' ),
		__( 'Импорт фото', 'amis' ),
		'manage_woocommerce',
		'amis-photo-import',
		'amis_photo_import_page'
	);
}
add_action( 'admin_menu', 'amis_photo_import_menu' );

/**
 * Разбирает имя файла (без расширения) на артикул и номер фото в галерее.
 *
 * @param string $basename Имя файла без расширения, например "DHO4804_2".
 * @return array{sku:string,index:int} index=1 — главное фото, 2+ — галерея.
 */
function amis_photo_import_parse_filename( $basename ) {

	// 1. Сначала — весь текст как артикул целиком (главное фото).
	if ( wc_get_product_id_by_sku( $basename ) ) {
		return array( 'sku' => $basename, 'index' => 1 );
	}

	// 2. Иначе пробуем отделить числовой суффикс "_N" как номер в галерее.
	if ( preg_match( '/^(.+)_(\d+)$/', $basename, $m ) ) {
		return array( 'sku' => $m[1], 'index' => (int) $m[2] );
	}

	// 3. Совпадений нет вообще — вернём как есть, дальше отчёт покажет «не найден».
	return array( 'sku' => $basename, 'index' => 1 );
}

/**
 * Загружает один файл с диска как вложение (attachment) в медиабиблиотеку.
 *
 * @param string $file_path Путь к файлу на диске (уже распакованному из zip).
 * @param string $title     Заголовок/alt вложения.
 * @return int|WP_Error ID вложения или ошибка.
 */
function amis_photo_import_upload_attachment( $file_path, $title ) {

	require_once ABSPATH . 'wp-admin/includes/image.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';

	$filename = wp_basename( $file_path );
	$contents = file_get_contents( $file_path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents -- локальный файл из своего временного каталога, не удалённый URL.

	if ( false === $contents ) {
		return new WP_Error( 'amis_photo_read', __( 'Не удалось прочитать файл.', 'amis' ) );
	}

	$upload = wp_upload_bits( $filename, null, $contents );

	if ( ! empty( $upload['error'] ) ) {
		return new WP_Error( 'amis_photo_upload', $upload['error'] );
	}

	$filetype = wp_check_filetype( $upload['file'], null );

	if ( empty( $filetype['type'] ) || 0 !== strpos( $filetype['type'], 'image/' ) ) {
		wp_delete_file( $upload['file'] );
		return new WP_Error( 'amis_photo_type', __( 'Файл не является изображением.', 'amis' ) );
	}

	$attachment_id = wp_insert_attachment(
		array(
			'post_mime_type' => $filetype['type'],
			'post_title'     => $title,
			'post_content'   => '',
			'post_status'    => 'inherit',
		),
		$upload['file']
	);

	if ( is_wp_error( $attachment_id ) ) {
		return $attachment_id;
	}

	$attach_data = wp_generate_attachment_metadata( $attachment_id, $upload['file'] );
	wp_update_attachment_metadata( $attachment_id, $attach_data );
	update_post_meta( $attachment_id, '_wp_attachment_image_alt', $title );

	return $attachment_id;
}

/**
 * Рекурсивно удаляет временную папку распаковки.
 *
 * @param string $dir Путь к папке.
 */
function amis_photo_import_rrmdir( $dir ) {

	if ( ! is_dir( $dir ) ) {
		return;
	}

	$items = scandir( $dir );

	foreach ( $items as $item ) {
		if ( '.' === $item || '..' === $item ) {
			continue;
		}
		$path = $dir . '/' . $item;
		if ( is_dir( $path ) ) {
			amis_photo_import_rrmdir( $path );
		} else {
			wp_delete_file( $path );
		}
	}

	rmdir( $dir ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_rmdir -- своя временная папка распаковки, не файл темы/контента.
}

/**
 * Основная обработка: распаковка zip, сопоставление файлов с товарами,
 * загрузка фото.
 *
 * @param string $zip_path Путь к загруженному zip-файлу.
 * @return array{updated:array,skipped:array,errors:array}
 */
function amis_photo_import_process( $zip_path ) {

	$result = array(
		'updated' => array(), // sku => array( 'title' => ..., 'count' => N )
		'skipped' => array(), // строки "файл — причина"
		'errors'  => array(),
	);

	if ( ! class_exists( 'ZipArchive' ) ) {
		$result['errors'][] = __( 'На сервере недоступен модуль PHP ZipArchive — импорт невозможен.', 'amis' );
		return $result;
	}

	$upload_dir = wp_upload_dir();
	$tmp_dir    = $upload_dir['basedir'] . '/amis-photo-import-' . uniqid();

	if ( ! wp_mkdir_p( $tmp_dir ) ) {
		$result['errors'][] = __( 'Не удалось создать временную папку для распаковки.', 'amis' );
		return $result;
	}

	$zip = new ZipArchive();

	if ( true !== $zip->open( $zip_path ) ) {
		$result['errors'][] = __( 'Не удалось открыть zip-архив.', 'amis' );
		amis_photo_import_rrmdir( $tmp_dir );
		return $result;
	}

	$zip->extractTo( $tmp_dir );
	$zip->close();

	// Собираем плоский список файлов-изображений, игнорируя служебные
	// файлы (__MACOSX, .DS_Store) и возможные вложенные папки в архиве.
	$files      = array();
	$extensions = array( 'jpg', 'jpeg', 'png', 'webp' );

	$iterator = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $tmp_dir, FilesystemIterator::SKIP_DOTS ) );

	foreach ( $iterator as $file_info ) {

		if ( ! $file_info->isFile() ) {
			continue;
		}

		$path = $file_info->getPathname();

		if ( false !== strpos( $path, '__MACOSX' ) || 0 === strpos( $file_info->getFilename(), '.' ) ) {
			continue;
		}

		$ext = strtolower( $file_info->getExtension() );

		if ( ! in_array( $ext, $extensions, true ) ) {
			continue;
		}

		$files[] = $path;
	}

	if ( ! $files ) {
		$result['errors'][] = __( 'В архиве не нашлось ни одного изображения (jpg/jpeg/png/webp).', 'amis' );
		amis_photo_import_rrmdir( $tmp_dir );
		return $result;
	}

	// Группируем файлы по артикулу, каждый — с номером в галерее.
	$groups = array(); // sku => array( index => filepath )

	foreach ( $files as $path ) {
		$basename = pathinfo( $path, PATHINFO_FILENAME );
		$parsed   = amis_photo_import_parse_filename( $basename );

		$groups[ $parsed['sku'] ][ $parsed['index'] ] = $path;
	}

	foreach ( $groups as $sku => $photos ) {

		$product_id = wc_get_product_id_by_sku( $sku );

		if ( ! $product_id ) {
			$result['skipped'][] = sprintf(
				/* translators: %s — артикул. */
				__( '%s: нет такого товара на сайте.', 'amis' ),
				$sku
			);
			continue;
		}

		/**
		 * Если у товара уже есть главное фото — пропускаем целиком.
		 * Без этого повторная загрузка того же (или дополненного новыми
		 * файлами) архива каждый раз создавала бы новые вложения в
		 * медиабиблиотеке поверх старых, а старые просто отвязывались от
		 * товара, но не удалялись — библиотека засорялась бы дублями.
		 * Хотите заменить фото у конкретного товара — проще руками в
		 * карточке, чем через этот инструмент.
		 */
		if ( has_post_thumbnail( $product_id ) ) {
			$result['skipped'][] = sprintf(
				/* translators: %s — артикул. */
				__( '%s: у товара уже есть фото, пропущено.', 'amis' ),
				$sku
			);
			continue;
		}

		ksort( $photos ); // Главное фото (индекс 1) — первым.

		$title       = get_the_title( $product_id );
		$gallery_ids = array();
		$count       = 0;
		$i           = 0;

		foreach ( $photos as $index => $path ) {

			++$i;
			$photo_title = $title . ( $i > 1 ? ' — фото ' . $i : '' );

			$attachment_id = amis_photo_import_upload_attachment( $path, $photo_title );

			if ( is_wp_error( $attachment_id ) ) {
				$result['errors'][] = $sku . ' (' . wp_basename( $path ) . '): ' . $attachment_id->get_error_message();
				continue;
			}

			if ( 1 === $i ) {
				set_post_thumbnail( $product_id, $attachment_id );
			} else {
				$gallery_ids[] = $attachment_id;
			}

			++$count;
		}

		if ( $gallery_ids ) {
			update_post_meta( $product_id, '_product_image_gallery', implode( ',', $gallery_ids ) );
		}

		if ( $count ) {
			$result['updated'][] = array(
				'sku'   => $sku,
				'title' => $title,
				'count' => $count,
			);
		}
	}

	amis_photo_import_rrmdir( $tmp_dir );

	return $result;
}

/**
 * Страница в админке: форма загрузки + отчёт.
 */
function amis_photo_import_page() {

	if ( ! current_user_can( 'manage_woocommerce' ) ) {
		wp_die( esc_html__( 'Недостаточно прав.', 'amis' ) );
	}

	$result = null;

	if ( isset( $_POST['amis_photo_import_nonce'] )
		&& wp_verify_nonce( sanitize_key( $_POST['amis_photo_import_nonce'] ), 'amis_photo_import' )
		&& ! empty( $_FILES['amis_photo_zip']['tmp_name'] )
	) {

		$file = $_FILES['amis_photo_zip']; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- обрабатывается ниже: проверка кода ошибки, затем расширения.

		if ( UPLOAD_ERR_OK !== $file['error'] ) {
			$result = array(
				'errors'  => array( __( 'Ошибка загрузки файла.', 'amis' ) ),
				'updated' => array(),
				'skipped' => array(),
			);
		} else {
			$ext = strtolower( pathinfo( $file['name'], PATHINFO_EXTENSION ) );

			if ( 'zip' !== $ext ) {
				$result = array(
					'errors'  => array( __( 'Нужен файл в формате ZIP.', 'amis' ) ),
					'updated' => array(),
					'skipped' => array(),
				);
			} else {
				$result = amis_photo_import_process( $file['tmp_name'] );
			}
		}
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Импорт фото товаров', 'amis' ); ?></h1>

		<p>
			<?php esc_html_e( 'Загрузите ZIP-архив с фото. Имя файла (без расширения) должно совпадать с артикулом товара — например DHO4804.jpg. Несколько фото на один товар: DHO4804.jpg (главное), DHO4804_2.jpg, DHO4804_3.jpg (галерея, по номеру).', 'amis' ); ?>
		</p>
		<p>
			<?php esc_html_e( 'Формат — jpg/jpeg/png/webp. Товары без совпавшего файла не трогаются, товары, у которых фото уже есть, — тоже (безопасно грузить архив повторно, если он пополнился новыми файлами). Alt и заголовок фото проставляются автоматически из названия товара.', 'amis' ); ?>
		</p>

		<form method="post" enctype="multipart/form-data">
			<?php wp_nonce_field( 'amis_photo_import', 'amis_photo_import_nonce' ); ?>
			<input type="file" name="amis_photo_zip" accept=".zip" required>
			<?php submit_button( __( 'Загрузить и разложить фото', 'amis' ) ); ?>
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
						/* translators: 1: сколько товаров получили фото, 2: сколько файлов пропущено. */
						esc_html__( 'Фото проставлены у товаров: %1$d. Не найдено товара для файла: %2$d.', 'amis' ),
						count( $result['updated'] ),
						count( $result['skipped'] )
					);
					?>
				</p>
			</div>

			<?php if ( ! empty( $result['updated'] ) ) : ?>
				<h2><?php esc_html_e( 'Загружено', 'amis' ); ?></h2>
				<table class="widefat striped">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Артикул', 'amis' ); ?></th>
							<th><?php esc_html_e( 'Товар', 'amis' ); ?></th>
							<th><?php esc_html_e( 'Фото', 'amis' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $result['updated'] as $row ) : ?>
							<tr>
								<td><?php echo esc_html( $row['sku'] ); ?></td>
								<td><?php echo esc_html( $row['title'] ); ?></td>
								<td><?php echo esc_html( $row['count'] ); ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>

			<?php if ( ! empty( $result['skipped'] ) ) : ?>
				<h2><?php esc_html_e( 'Не найдено товара', 'amis' ); ?></h2>
				<p><?php echo esc_html( implode( '; ', $result['skipped'] ) ); ?></p>
			<?php endif; ?>

		<?php endif; ?>
	</div>
	<?php
}
