<?php
/**
 * Разовый пересчёт URL (slug) товаров по артикулу.
 *
 * После массового импорта через штатный CSV-импортёр WooCommerce slug
 * товара строился из поля name («Осциллограф RIGOL DHO1104» →
 * «осциллограф-rigol-dho1104» — WordPress не транслитерирует кириллицу
 * в CSV-импорте), а не из артикула, как хотелось. Этот инструмент
 * проходит по всем товарам и переставляет slug на sanitize_title(SKU).
 *
 * Не так рискованно, как может показаться: при смене post_name у уже
 * опубликованного поста WordPress сам сохраняет старый slug
 * (_wp_old_slug) и настраивает автоматический редирект на новый —
 * штатный механизм wp_old_slug_redirect(), без дополнительного кода.
 *
 * @package amis
 */

defined( 'ABSPATH' ) || exit;

/**
 * Пункт меню — под «Товары».
 */
function amis_slug_fix_menu() {

	add_submenu_page(
		'edit.php?post_type=product',
		__( 'Пересчитать URL по артикулу', 'amis' ),
		__( 'Пересчитать URL', 'amis' ),
		'manage_woocommerce',
		'amis-slug-fix',
		'amis_slug_fix_page'
	);
}
add_action( 'admin_menu', 'amis_slug_fix_menu' );

/**
 * Проход по всем товарам: у кого slug не совпадает с sanitize_title(SKU) —
 * переставляет. Пустой SKU — пропускает, ничего не ломает.
 *
 * @return array{changed:array,skipped_empty_sku:string[],unchanged:int}
 */
function amis_slug_fix_run() {

	$ids = get_posts( array(
		'post_type'      => 'product',
		'post_status'    => array( 'publish', 'draft', 'pending', 'private' ),
		'posts_per_page'  => -1,
		'fields'          => 'ids',
		'no_found_rows'   => true,
	) );

	$changed          = array();
	$skipped_empty    = array();
	$unchanged        = 0;

	foreach ( $ids as $id ) {

		$sku = get_post_meta( $id, '_sku', true );

		if ( '' === trim( (string) $sku ) ) {
			$skipped_empty[] = get_the_title( $id ) . ' (ID ' . $id . ')';
			continue;
		}

		$old_slug = get_post_field( 'post_name', $id );
		$new_slug = sanitize_title( $sku );

		if ( '' === $new_slug || $old_slug === $new_slug ) {
			++$unchanged;
			continue;
		}

		$unique_slug = wp_unique_post_slug( $new_slug, $id, get_post_status( $id ), 'product', 0 );

		wp_update_post( array(
			'ID'        => $id,
			'post_name' => $unique_slug,
		) );

		$changed[] = array(
			'sku'      => $sku,
			'old_slug' => $old_slug,
			'new_slug' => $unique_slug,
		);
	}

	return array(
		'changed'           => $changed,
		'skipped_empty_sku' => $skipped_empty,
		'unchanged'         => $unchanged,
	);
}

/**
 * Страница в админке: кнопка запуска + отчёт.
 */
function amis_slug_fix_page() {

	if ( ! current_user_can( 'manage_woocommerce' ) ) {
		wp_die( esc_html__( 'Недостаточно прав.', 'amis' ) );
	}

	$result = null;

	if ( isset( $_POST['amis_slug_fix_nonce'] )
		&& wp_verify_nonce( sanitize_key( $_POST['amis_slug_fix_nonce'] ), 'amis_slug_fix' )
	) {
		$result = amis_slug_fix_run();
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Пересчитать URL товаров по артикулу', 'amis' ); ?></h1>

		<p><?php esc_html_e( 'Ставит адрес товара равным его артикулу (SKU) — например, /product/dho1104/ вместо /product/osciллограф-rigol-dho1104-старого-вида/. Проверяет все товары сразу, ничего заранее выбирать не нужно. Товары без артикула не трогает.', 'amis' ); ?></p>

		<form method="post">
			<?php wp_nonce_field( 'amis_slug_fix', 'amis_slug_fix_nonce' ); ?>
			<?php submit_button( __( 'Пересчитать URL сейчас', 'amis' ) ); ?>
		</form>

		<?php if ( null !== $result ) : ?>

			<div class="notice notice-success">
				<p>
					<?php
					printf(
						/* translators: 1: изменено, 2: без артикула, 3: уже верно. */
						esc_html__( 'Изменено адресов: %1$d. Без артикула (пропущено): %2$d. Уже были верными: %3$d.', 'amis' ),
						count( $result['changed'] ),
						count( $result['skipped_empty_sku'] ),
						$result['unchanged']
					);
					?>
				</p>
			</div>

			<?php if ( $result['changed'] ) : ?>
				<h2><?php esc_html_e( 'Изменено', 'amis' ); ?></h2>
				<table class="widefat striped">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Артикул', 'amis' ); ?></th>
							<th><?php esc_html_e( 'Было', 'amis' ); ?></th>
							<th><?php esc_html_e( 'Стало', 'amis' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $result['changed'] as $row ) : ?>
							<tr>
								<td><?php echo esc_html( $row['sku'] ); ?></td>
								<td><?php echo esc_html( $row['old_slug'] ); ?></td>
								<td><?php echo esc_html( $row['new_slug'] ); ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>

			<?php if ( $result['skipped_empty_sku'] ) : ?>
				<h2><?php esc_html_e( 'Без артикула — не тронуты', 'amis' ); ?></h2>
				<p><?php echo esc_html( implode( ', ', $result['skipped_empty_sku'] ) ); ?></p>
			<?php endif; ?>

		<?php endif; ?>
	</div>
	<?php
}
