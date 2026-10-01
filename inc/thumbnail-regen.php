<?php
/**
 * Разовая перегенерация миниатюр товаров.
 *
 * Нужен после того, как размер 'woocommerce_thumbnail' переключили с
 * обрезки на вписывание (amis_uncrop_woocommerce_thumbnail() в
 * functions.php) — у уже загруженных фото файл миниатюры уже нарезан
 * по старому правилу и лежит на диске, новая регистрация размера на
 * него не влияет сама по себе. Этот инструмент проходит по всем товарам
 * и пересобирает файлы миниатюр (все зарегистрированные размеры, не
 * только woocommerce_thumbnail) для главного фото и фото галереи.
 *
 * @package amis
 */

defined( 'ABSPATH' ) || exit;

/**
 * Пункт меню — под «Товары».
 */
function amis_thumb_regen_menu() {

	add_submenu_page(
		'edit.php?post_type=product',
		__( 'Пересчитать миниатюры', 'amis' ),
		__( 'Пересчитать миниатюры', 'amis' ),
		'manage_woocommerce',
		'amis-thumb-regen',
		'amis_thumb_regen_page'
	);
}
add_action( 'admin_menu', 'amis_thumb_regen_menu' );

/**
 * ID всех изображений товара: главное фото + галерея.
 *
 * @param int $product_id Товар.
 * @return int[]
 */
function amis_thumb_regen_attachment_ids( $product_id ) {

	$ids = array();

	$thumb_id = get_post_thumbnail_id( $product_id );
	if ( $thumb_id ) {
		$ids[] = (int) $thumb_id;
	}

	$gallery_raw = get_post_meta( $product_id, '_product_image_gallery', true );
	if ( $gallery_raw ) {
		foreach ( explode( ',', $gallery_raw ) as $gallery_id ) {
			$gallery_id = absint( $gallery_id );
			if ( $gallery_id ) {
				$ids[] = $gallery_id;
			}
		}
	}

	return array_unique( $ids );
}

/**
 * Проход по всем товарам: пересборка файлов миниатюр.
 *
 * @return array{done:int,skipped:int,errors:array}
 */
function amis_thumb_regen_run() {

	if ( ! function_exists( 'wc_get_products' ) ) {
		return array( 'done' => 0, 'skipped' => 0, 'errors' => array( __( 'WooCommerce выключен.', 'amis' ) ) );
	}

	require_once ABSPATH . 'wp-admin/includes/image.php';

	set_time_limit( 0 ); // Пересборка миниатюр на большом каталоге — не быстрая операция.

	$product_ids = wc_get_products( array(
		'status' => array( 'publish', 'draft', 'pending', 'private' ),
		'limit'  => -1,
		'return' => 'ids',
	) );

	$done    = 0;
	$skipped = 0;
	$errors  = array();
	$seen    = array(); // Одно и то же фото может стоять у нескольких товаров — не пересобираем дважды.

	foreach ( $product_ids as $product_id ) {
		foreach ( amis_thumb_regen_attachment_ids( $product_id ) as $attachment_id ) {

			if ( isset( $seen[ $attachment_id ] ) ) {
				continue;
			}
			$seen[ $attachment_id ] = true;

			$file = get_attached_file( $attachment_id );

			if ( ! $file || ! file_exists( $file ) ) {
				++$skipped;
				continue;
			}

			$metadata = wp_generate_attachment_metadata( $attachment_id, $file );

			if ( ! $metadata ) {
				$errors[] = sprintf( 'ID %d: %s', $attachment_id, __( 'не удалось пересобрать миниатюры.', 'amis' ) );
				continue;
			}

			wp_update_attachment_metadata( $attachment_id, $metadata );
			++$done;
		}
	}

	return array(
		'done'    => $done,
		'skipped' => $skipped,
		'errors'  => $errors,
	);
}

/**
 * Страница в админке: кнопка запуска + отчёт.
 */
function amis_thumb_regen_page() {

	if ( ! current_user_can( 'manage_woocommerce' ) ) {
		wp_die( esc_html__( 'Недостаточно прав.', 'amis' ) );
	}

	$result = null;

	if ( isset( $_POST['amis_thumb_regen_nonce'] )
		&& wp_verify_nonce( sanitize_key( $_POST['amis_thumb_regen_nonce'] ), 'amis_thumb_regen' )
	) {
		$result = amis_thumb_regen_run();
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Пересчитать миниатюры товаров', 'amis' ); ?></h1>

		<p>
			<?php esc_html_e( 'Пересобирает файлы миниатюр (все размеры, включая woocommerce_thumbnail) для главного фото и фото галереи каждого товара — по уже загруженным изображениям на диске, ничего заново не скачивает.', 'amis' ); ?>
		</p>
		<p>
			<strong><?php esc_html_e( 'Когда нужно:', 'amis' ); ?></strong>
			<?php esc_html_e( 'один раз после переключения обрезки миниатюр каталога на «вписывать целиком» — иначе уже загруженные фото останутся обрезанными квадратом по старым файлам, новая настройка подействует только на то, что загрузите заново.', 'amis' ); ?>
		</p>
		<p>
			<?php esc_html_e( 'На большом каталоге может занять несколько минут — дождитесь отчёта, не закрывайте страницу.', 'amis' ); ?>
		</p>

		<form method="post">
			<?php wp_nonce_field( 'amis_thumb_regen', 'amis_thumb_regen_nonce' ); ?>
			<?php submit_button( __( 'Пересчитать миниатюры сейчас', 'amis' ) ); ?>
		</form>

		<?php if ( $result ) : ?>

			<div class="notice notice-success">
				<p>
					<?php
					printf(
						/* translators: 1: пересобрано, 2: пропущено. */
						esc_html__( 'Пересобрано изображений: %1$d. Пропущено (файл не найден): %2$d.', 'amis' ),
						(int) $result['done'],
						(int) $result['skipped']
					);
					?>
				</p>
			</div>

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

		<?php endif; ?>
	</div>
	<?php
}
