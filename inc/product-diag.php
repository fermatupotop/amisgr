<?php
/**
 * Диагностика товара по артикулу — почему товар не попадает в фильтр каталога.
 *
 * Временный инструмент для разбора конкретного бага: часть товаров не
 * показывалась в фильтре «Диапазон частот» (и, возможно, в каталоге вообще),
 * пока карточку не открывали и не сохраняли вручную. Программные попытки
 * пересобрать связи (inc/product-resave.php) не помогли — значит, дело не
 * в том, что мы предполагали, и нужно увидеть реальное состояние в базе,
 * а не гадать дальше. Можно удалить, когда причина найдена и починена.
 *
 * @package amis
 */

defined( 'ABSPATH' ) || exit;

/**
 * Перехватывает реальный SQL основного запроса на /shop/ с активным
 * фильтром диапазона частот и складывает во временную опцию — чтобы
 * посмотреть его потом на странице диагностики, не гадая, какой из
 * pre_get_posts-хуков (наш или WooCommerce) на самом деле исключает товар.
 * Живёт постоянно, но пишет только при этом конкретном условии — цена
 * почти нулевая, а найти причину без этого не получалось.
 */
function amis_diag_capture_shop_sql( $sql, $query ) {

	if ( ! is_admin() && $query->is_main_query()
		&& function_exists( 'is_shop' ) && ( is_shop() || is_product_taxonomy() )
		&& ! empty( $_GET['filter_frequency-range'] ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- только диагностика, ничего не сохраняется в базу.
	) {
		update_option( 'amis_diag_last_sql', $sql, false );
	}

	return $sql;
}
add_filter( 'posts_request', 'amis_diag_capture_shop_sql', 999, 2 );

/**
 * Пункт меню — под «Товары».
 */
function amis_product_diag_menu() {

	add_submenu_page(
		'edit.php?post_type=product',
		__( 'Диагностика товара', 'amis' ),
		__( 'Диагностика товара', 'amis' ),
		'manage_woocommerce',
		'amis-product-diag',
		'amis_product_diag_page'
	);
}
add_action( 'admin_menu', 'amis_product_diag_menu' );

/**
 * Собирает диагностику по одному товару.
 *
 * @param string $sku Артикул.
 * @return array|null
 */
function amis_product_diag_collect( $sku ) {

	$product_id = wc_get_product_id_by_sku( $sku );

	if ( ! $product_id ) {
		return null;
	}

	global $wpdb;

	$product = wc_get_product( $product_id );

	$data = array(
		'id'               => $product_id,
		'sku'              => $sku,
		'title'            => get_the_title( $product_id ),
		'status'           => get_post_status( $product_id ),
		'object_cache'     => wp_using_ext_object_cache(),
		'hide_outofstock'  => get_option( 'woocommerce_hide_out_of_stock_items' ), // 'yes' — и товар с термином outofstock пропадёт из каталога штатно, кодом темы это не лечится.
	);

	// Видимость в каталоге.
	$visibility_terms       = wp_get_object_terms( $product_id, 'product_visibility', array( 'fields' => 'slugs' ) );
	$data['visibility_terms'] = is_wp_error( $visibility_terms ) ? array( 'ERROR: ' . $visibility_terms->get_error_message() ) : $visibility_terms;
	$data['catalog_visibility_prop'] = $product ? $product->get_catalog_visibility() : 'n/a';

	// Наличие.
	$data['stock_status']  = $product ? $product->get_stock_status() : 'n/a';
	$data['stock_qty']     = $product ? $product->get_stock_quantity() : 'n/a';
	$data['manage_stock']  = $product ? ( $product->get_manage_stock() ? 'yes' : 'no' ) : 'n/a';
	$data['has_stock_meta'] = metadata_exists( 'post', $product_id, '_stock_status' );

	// Атрибут «Диапазон частот» — через WP API (кэш) и напрямую через SQL (без кэша).
	$freq_terms_api = wp_get_object_terms( $product_id, 'pa_frequency-range', array( 'fields' => 'all' ) );
	$data['freq_terms_api'] = is_wp_error( $freq_terms_api )
		? array( 'ERROR: ' . $freq_terms_api->get_error_message() )
		: wp_list_pluck( $freq_terms_api, 'name', 'term_id' );

	$freq_taxonomy = get_taxonomy( 'pa_frequency-range' );
	$freq_tt_ids   = array();

	if ( $freq_taxonomy ) {
		$freq_tt_ids = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT tr.term_taxonomy_id, tt.term_id, tt.count, t.name, t.slug
				 FROM {$wpdb->term_relationships} tr
				 INNER JOIN {$wpdb->term_taxonomy} tt ON tt.term_taxonomy_id = tr.term_taxonomy_id
				 INNER JOIN {$wpdb->terms} t ON t.term_id = tt.term_id
				 WHERE tr.object_id = %d AND tt.taxonomy = 'pa_frequency-range'",
				$product_id
			)
		);
	}
	$data['freq_terms_sql'] = $freq_tt_ids;

	// _product_attributes meta — что видит вкладка «Атрибуты» в админке.
	$attributes_meta = get_post_meta( $product_id, '_product_attributes', true );
	$data['product_attributes_meta'] = isset( $attributes_meta['pa_frequency-range'] ) ? $attributes_meta['pa_frequency-range'] : null;

	// Тот же tax_query, что использует archive-product.php — реально ли WP_Query находит товар.
	if ( $freq_tt_ids ) {
		$term_id_to_check = $freq_tt_ids[0]->term_id;
		$test_query        = new WP_Query( array(
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'no_found_rows'  => true,
			'tax_query'      => array(
				array(
					'taxonomy' => 'pa_frequency-range',
					'field'    => 'term_id',
					'terms'    => $term_id_to_check,
				),
			),
		) );
		$data['found_by_tax_query'] = in_array( $product_id, $test_query->posts, true );
		$data['tax_query_total']    = count( $test_query->posts );
	} else {
		$data['found_by_tax_query'] = null;
		$data['tax_query_total']    = null;
	}

	// Настоящий запрос к /shop/, как у живого посетителя — проходит через
	// ВСЕ pre_get_posts (WC_Query, amis_shop_facet_filter,
	// amis_shop_instock_first_sort), в отличие от ручного WP_Query выше,
	// который эти хуки не задевает (is_shop()/is_main_query() там ложны).
	if ( $freq_tt_ids ) {
		delete_option( 'amis_diag_last_sql' );

		$shop_url  = add_query_arg( 'filter_frequency-range', $freq_tt_ids[0]->slug, function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' ) );
		$response  = wp_remote_get( $shop_url, array( 'timeout' => 20, 'sslverify' => false ) );

		if ( is_wp_error( $response ) ) {
			$data['live_shop_check'] = 'ERROR: ' . $response->get_error_message();
		} else {
			$body                    = wp_remote_retrieve_body( $response );
			$data['live_shop_url']   = $shop_url;
			$data['live_shop_found'] = false !== strpos( $body, esc_html( $sku ) );
			preg_match( '/(\d+)\s*товар/u', wp_strip_all_tags( $body ), $m );
			$data['live_shop_count'] = $m ? (int) $m[1] : null;
		}

		$data['live_shop_sql'] = get_option( 'amis_diag_last_sql', '' );
	}

	return $data;
}

/**
 * Страница в админке: форма ввода SKU + дамп диагностики.
 */
function amis_product_diag_page() {

	if ( ! current_user_can( 'manage_woocommerce' ) ) {
		wp_die( esc_html__( 'Недостаточно прав.', 'amis' ) );
	}

	$sku  = isset( $_POST['amis_diag_sku'] ) ? sanitize_text_field( wp_unslash( $_POST['amis_diag_sku'] ) ) : '';
	$data = null;

	if ( $sku
		&& isset( $_POST['amis_product_diag_nonce'] )
		&& wp_verify_nonce( sanitize_key( $_POST['amis_product_diag_nonce'] ), 'amis_product_diag' )
	) {
		$data = amis_product_diag_collect( $sku );
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Диагностика товара', 'amis' ); ?></h1>
		<p><?php esc_html_e( 'Введите артикул товара, который не показывается в фильтре каталога, — покажет реальное состояние в базе.', 'amis' ); ?></p>

		<form method="post">
			<?php wp_nonce_field( 'amis_product_diag', 'amis_product_diag_nonce' ); ?>
			<input type="text" name="amis_diag_sku" value="<?php echo esc_attr( $sku ); ?>" placeholder="<?php esc_attr_e( 'Артикул (SKU)', 'amis' ); ?>" style="width:280px">
			<?php submit_button( __( 'Показать', 'amis' ), 'primary', 'submit', false ); ?>
		</form>

		<?php if ( $sku && null === $data ) : ?>
			<div class="notice notice-error"><p><?php esc_html_e( 'Товар с таким артикулом не найден.', 'amis' ); ?></p></div>
		<?php elseif ( $data ) : ?>
			<table class="widefat striped" style="margin-top:20px;max-width:900px">
				<tbody>
					<tr><th style="width:280px"><?php esc_html_e( 'ID товара', 'amis' ); ?></th><td><?php echo esc_html( $data['id'] ); ?></td></tr>
					<tr><th><?php esc_html_e( 'Название', 'amis' ); ?></th><td><?php echo esc_html( $data['title'] ); ?></td></tr>
					<tr><th><?php esc_html_e( 'post_status', 'amis' ); ?></th><td><?php echo esc_html( $data['status'] ); ?></td></tr>
					<tr><th><?php esc_html_e( 'Внешний object cache активен', 'amis' ); ?></th><td><?php echo $data['object_cache'] ? 'ДА' : 'нет'; ?></td></tr>
					<tr><th><?php esc_html_e( 'Настройка WooCommerce: скрывать товары не в наличии', 'amis' ); ?></th>
						<td>
							<?php
							$hide = $data['hide_outofstock'];
							echo 'yes' === $hide ? '<strong>ВКЛЮЧЕНА (yes)</strong> — товары с термином outofstock скрыты из каталога штатно' : esc_html( $hide ? $hide : '(выключена / пусто)' );
							?>
						</td>
					</tr>
					<tr><th><?php esc_html_e( 'Видимость: catalog_visibility (свойство товара)', 'amis' ); ?></th><td><?php echo esc_html( $data['catalog_visibility_prop'] ); ?></td></tr>
					<tr><th><?php esc_html_e( 'Видимость: термины product_visibility', 'amis' ); ?></th><td><?php echo esc_html( implode( ', ', (array) $data['visibility_terms'] ) ?: '(нет терминов)' ); ?></td></tr>
					<tr><th><?php esc_html_e( 'stock_status', 'amis' ); ?></th><td><?php echo esc_html( $data['stock_status'] ); ?></td></tr>
					<tr><th><?php esc_html_e( 'stock_qty', 'amis' ); ?></th><td><?php echo esc_html( (string) $data['stock_qty'] ); ?></td></tr>
					<tr><th><?php esc_html_e( 'manage_stock', 'amis' ); ?></th><td><?php echo esc_html( $data['manage_stock'] ); ?></td></tr>
					<tr><th><?php esc_html_e( 'Мета _stock_status вообще существует', 'amis' ); ?></th><td><?php echo $data['has_stock_meta'] ? 'да' : 'НЕТ'; ?></td></tr>
					<tr><th><?php esc_html_e( 'pa_frequency-range через wp_get_object_terms()', 'amis' ); ?></th>
						<td><pre style="white-space:pre-wrap;margin:0"><?php echo esc_html( wp_json_encode( $data['freq_terms_api'], JSON_UNESCAPED_UNICODE ) ); ?></pre></td>
					</tr>
					<tr><th><?php esc_html_e( 'pa_frequency-range напрямую через SQL', 'amis' ); ?></th>
						<td><pre style="white-space:pre-wrap;margin:0"><?php echo esc_html( wp_json_encode( $data['freq_terms_sql'], JSON_UNESCAPED_UNICODE ) ); ?></pre></td>
					</tr>
					<tr><th><?php esc_html_e( '_product_attributes[pa_frequency-range] (мета)', 'amis' ); ?></th>
						<td><pre style="white-space:pre-wrap;margin:0"><?php echo esc_html( wp_json_encode( $data['product_attributes_meta'], JSON_UNESCAPED_UNICODE ) ); ?></pre></td>
					</tr>
					<tr><th><?php esc_html_e( 'Находит ли тестовый WP_Query (tax_query) этот товар', 'amis' ); ?></th>
						<td>
							<?php
							if ( null === $data['found_by_tax_query'] ) {
								esc_html_e( 'нет привязки к термину — проверка не выполнялась', 'amis' );
							} else {
								echo $data['found_by_tax_query'] ? 'ДА, находит' : 'НЕТ, не находит';
								echo ' — ' . esc_html( sprintf( 'всего в результате: %d', $data['tax_query_total'] ) );
							}
							?>
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Настоящая страница /shop/?filter_frequency-range=… (живой HTTP-запрос)', 'amis' ); ?></th>
						<td>
							<?php if ( isset( $data['live_shop_url'] ) ) : ?>
								<?php echo $data['live_shop_found'] ? 'ДА, товар есть на странице' : 'НЕТ, товара на странице нет'; ?>
								— <?php echo esc_html( sprintf( 'счётчик страницы: %s', null !== $data['live_shop_count'] ? $data['live_shop_count'] : '?' ) ); ?>
								<br><small><?php echo esc_html( $data['live_shop_url'] ); ?></small>
							<?php elseif ( isset( $data['live_shop_check'] ) ) : ?>
								<?php echo esc_html( $data['live_shop_check'] ); ?>
							<?php else : ?>
								<?php esc_html_e( 'нет привязки к термину — проверка не выполнялась', 'amis' ); ?>
							<?php endif; ?>
						</td>
					</tr>
				</tbody>
			</table>

			<?php if ( ! empty( $data['live_shop_sql'] ) ) : ?>
				<h2><?php esc_html_e( 'Реальный SQL основного запроса /shop/ с этим фильтром', 'amis' ); ?></h2>
				<textarea readonly rows="8" style="width:100%;max-width:1100px;font-family:monospace;font-size:12.5px"><?php echo esc_textarea( $data['live_shop_sql'] ); ?></textarea>
			<?php endif; ?>
		<?php endif; ?>
	</div>
	<?php
}
