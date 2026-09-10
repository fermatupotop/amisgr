<?php
/**
 * Пробники и аксессуары: метабокс в админке и вывод на витрине.
 *
 * В отличие от «Комплекта поставки» (inc/product-package.php) — это не
 * текстовые строки, а связки на реальные товары: у пробника/аксессуара
 * своя карточка, артикул и цена, поэтому выбираем их тем же виджетом
 * поиска, что и штатные Upsells/Cross-sells WooCommerce, а не текстом.
 *
 * @package amis
 */

defined( 'ABSPATH' ) || exit;

/* ==========================================================================
   АДМИНКА
   ========================================================================== */

/**
 * Метабокс под основным редактором.
 */
function amis_accessories_add_metabox() {

	add_meta_box(
		'amis-product-accessories',
		__( 'Пробники и аксессуары', 'amis' ),
		'amis_accessories_metabox_render',
		'product',
		'normal',
		'default'
	);
}
add_action( 'add_meta_boxes', 'amis_accessories_add_metabox' );

/**
 * Один select2-виджет поиска товаров — тот же приём, что у штатного
 * поля Upsells в WooCommerce (includes/admin/meta-boxes/views/
 * html-product-data-linked-products.php), просто вынесенный в свой метабокс.
 *
 * @param int    $post_id Товар, на котором стоим (исключаем сам себя из поиска).
 * @param string $field   Имя поля/meta-ключ.
 * @param array  $ids     Уже выбранные ID товаров.
 */
function amis_accessories_render_picker( $post_id, $field, $ids ) {
	?>
	<select
		class="wc-product-search"
		multiple="multiple"
		style="width:100%"
		id="<?php echo esc_attr( $field ); ?>"
		name="<?php echo esc_attr( $field ); ?>[]"
		data-placeholder="<?php esc_attr_e( 'Начните вводить название или артикул…', 'amis' ); ?>"
		data-action="woocommerce_json_search_products_and_variations"
		data-exclude="<?php echo esc_attr( $post_id ); ?>"
	>
		<?php foreach ( $ids as $id ) : ?>
			<?php
			$linked = wc_get_product( $id );
			if ( ! $linked ) {
				continue;
			}
			?>
			<option value="<?php echo esc_attr( $id ); ?>" selected="selected">
				<?php echo esc_html( $linked->get_formatted_name() ); ?>
			</option>
		<?php endforeach; ?>
	</select>
	<?php
}

/**
 * Разметка метабокса.
 *
 * @param WP_Post $post Товар.
 */
function amis_accessories_metabox_render( $post ) {

	wp_nonce_field( 'amis_accessories_save', 'amis_accessories_nonce' );

	$probes = array_map( 'absint', (array) get_post_meta( $post->ID, '_amis_related_probes', true ) );
	$acc    = array_map( 'absint', (array) get_post_meta( $post->ID, '_amis_related_acc', true ) );
	?>
	<div class="amis-accessories-fields">

		<p class="amis-accessories-hint">
			<?php esc_html_e( 'Ищите по названию или артикулу среди уже опубликованных товаров. У одного прибора может быть заполнено одно поле, оба или ни одного — блок на странице появится только под то, что реально заполнено.', 'amis' ); ?>
		</p>

		<div class="amis-accessories-row">
			<label for="_amis_related_probes"><strong><?php esc_html_e( 'Совместимые пробники', 'amis' ); ?></strong></label>
			<?php amis_accessories_render_picker( $post->ID, '_amis_related_probes', $probes ); ?>
		</div>

		<div class="amis-accessories-row">
			<label for="_amis_related_acc"><strong><?php esc_html_e( 'Аксессуары', 'amis' ); ?></strong></label>
			<p class="amis-accessories-hint">
				<?php esc_html_e( 'Сумки, крепления, программные опции (лицензионные ключи) — всё, что не подходит под «пробники».', 'amis' ); ?>
			</p>
			<?php amis_accessories_render_picker( $post->ID, '_amis_related_acc', $acc ); ?>
		</div>

	</div>

	<style>
		.amis-accessories-row{padding:14px 0;border-bottom:1px solid #f0f0f1}
		.amis-accessories-row:last-child{border-bottom:0}
		.amis-accessories-row label{display:block;margin-bottom:8px}
		.amis-accessories-hint{color:#646970;font-size:13px;margin:0 0 12px}
	</style>
	<?php
}

/**
 * Сохранение.
 *
 * @param int $post_id ID товара.
 */
function amis_accessories_save( $post_id ) {

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! isset( $_POST['amis_accessories_nonce'] )
		|| ! wp_verify_nonce( sanitize_key( $_POST['amis_accessories_nonce'] ), 'amis_accessories_save' ) ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	foreach ( array( '_amis_related_probes', '_amis_related_acc' ) as $field ) {

		$ids = isset( $_POST[ $field ] ) ? array_map( 'absint', wp_unslash( (array) $_POST[ $field ] ) ) : array();
		$ids = array_values( array_filter( $ids ) );

		if ( $ids ) {
			update_post_meta( $post_id, $field, $ids );
		} else {
			delete_post_meta( $post_id, $field );
		}
	}
}
add_action( 'save_post_product', 'amis_accessories_save' );

/* ==========================================================================
   ВИТРИНА
   ========================================================================== */

/**
 * Товары из связки — только реальные, опубликованные и купибельные.
 *
 * @param int    $product_id ID товара.
 * @param string $meta_key   '_amis_related_probes' или '_amis_related_acc'.
 * @return WC_Product[]
 */
function amis_get_related_accessories( $product_id, $meta_key ) {

	$ids = array_map( 'absint', (array) get_post_meta( $product_id, $meta_key, true ) );

	if ( ! $ids ) {
		return array();
	}

	$products = array();

	foreach ( $ids as $id ) {
		$product = wc_get_product( $id );
		if ( $product && $product->is_visible() ) {
			$products[] = $product;
		}
	}

	return $products;
}

/**
 * Обратная связь: на странице пробника/аксессуара показываем, с какими
 * приборами он связан. Отдельного поля на самом пробнике для этого нет —
 * ищем среди всех товаров тех, у кого этот ID стоит в _amis_related_probes
 * или _amis_related_acc, чтобы связь задавалась один раз и не расходилась.
 *
 * @param int $accessory_id ID пробника/аксессуара.
 * @return WC_Product[]
 */
function amis_get_compatible_instruments( $accessory_id ) {

	$found = array();

	foreach ( array( '_amis_related_probes', '_amis_related_acc' ) as $meta_key ) {

		$query = new WP_Query( array(
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'no_found_rows'  => true,
			'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- связок мало, каталог не настолько большой, чтобы это было узким местом.
				array(
					'key'     => $meta_key,
					'value'   => 'i:' . (int) $accessory_id . ';',
					'compare' => 'LIKE',
				),
			),
		) );

		foreach ( $query->posts as $instrument_id ) {
			$found[ $instrument_id ] = true;
		}
	}

	if ( ! $found ) {
		return array();
	}

	$products = array();

	foreach ( array_keys( $found ) as $instrument_id ) {
		$product = wc_get_product( $instrument_id );
		if ( $product && $product->is_visible() ) {
			$products[] = $product;
		}
	}

	return $products;
}

/**
 * Пробники и аксессуары товара, разбитые на группы — по образцу
 * amis_get_product_package() в inc/product-package.php.
 *
 * @param int $product_id ID товара.
 * @return array Пустой массив, если данных нет.
 */
function amis_get_product_accessories( $product_id ) {

	$groups = array();

	$probes = amis_get_related_accessories( $product_id, '_amis_related_probes' );

	if ( $probes ) {
		$groups[] = array(
			'title' => __( 'Совместимые пробники', 'amis' ),
			'items' => $probes,
		);
	}

	$acc = amis_get_related_accessories( $product_id, '_amis_related_acc' );

	if ( $acc ) {
		$groups[] = array(
			'title' => __( 'Аксессуары', 'amis' ),
			'items' => $acc,
		);
	}

	return $groups;
}

/**
 * Вывод блока.
 *
 * @param array $groups Результат amis_get_product_accessories().
 */
function amis_render_accessories( $groups ) {

	if ( empty( $groups ) ) {
		return;
	}
	?>
	<div class="acc">

		<?php foreach ( $groups as $group ) : ?>
			<div class="acc__group">

				<h3 class="acc__title"><?php echo esc_html( $group['title'] ); ?></h3>

				<div class="acc__list">
					<?php foreach ( $group['items'] as $item ) : ?>
						<a class="acc__card" href="<?php echo esc_url( get_permalink( $item->get_id() ) ); ?>">

							<span class="acc__img">
								<?php echo $item->get_image( 'woocommerce_thumbnail' ); // phpcs:ignore WordPress.Security.EscapeOutput — WooCommerce возвращает готовый <img>. ?>
							</span>

							<span class="acc__body">
								<span class="acc__name"><?php echo esc_html( $item->get_name() ); ?></span>
								<?php if ( $item->get_sku() ) : ?>
									<span class="acc__sku"><?php echo esc_html( $item->get_sku() ); ?></span>
								<?php endif; ?>
							</span>

							<span class="acc__price">
								<?php if ( $item->get_price() ) : ?>
									<?php echo wp_kses_post( $item->get_price_html() ); ?>
								<?php else : ?>
									<?php esc_html_e( 'По запросу', 'amis' ); ?>
								<?php endif; ?>
							</span>

						</a>
					<?php endforeach; ?>
				</div>

			</div>
		<?php endforeach; ?>

	</div>
	<?php
}
