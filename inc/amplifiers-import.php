<?php
/**
 * Импорт товаров категории «Усилители мощности» из CSV (сбор данных с
 * сайта поставщика EMCTESTLAB) — создаёт товары ЧЕРНОВИКАМИ, ничего не
 * публикует сразу. Формат файла — см. emc_amplifiers_import.csv в корне
 * темы: sku,title,category,brand,freq_range,output_power,type,standards,
 * short_description,source_url.
 *
 * В отличие от price-import.php/attr-import.php (там точное совпадение по
 * уже существующему SKU) — здесь для несовпавших строк СОЗДАЁТСЯ новый
 * товар, тем же приёмом, что и accessories-import.php.
 *
 * Атрибуты — `freq-range` и `output-power`, те же глобальные таксономии
 * WooCommerce, что уже используются у категории «Анализаторы спектра»
 * (см. inc/product-map.php, ключ 'power-amplifiers' там же ждёт именно
 * эти поля). Термины создаются по мере надобности (wp_insert_term), не
 * строгий режим — в отличие от frequency-range на главной, здесь нет
 * фиксированного набора значений, на которые что-то ещё завязано.
 *
 * @package amis
 */

defined( 'ABSPATH' ) || exit;

/**
 * Пункт меню — под «Товары».
 */
function amis_amp_import_menu() {

	add_submenu_page(
		'edit.php?post_type=product',
		__( 'Импорт усилителей (EMC)', 'amis' ),
		__( 'Импорт усилителей', 'amis' ),
		'manage_woocommerce',
		'amis-amp-import',
		'amis_amp_import_page'
	);
}
add_action( 'admin_menu', 'amis_amp_import_menu' );

/**
 * Термин таксономии по названию — найти или создать.
 *
 * @param string $name     Название термина.
 * @param string $taxonomy Слаг таксономии.
 * @return int|null ID термина.
 */
function amis_amp_import_get_or_create_term( $name, $taxonomy ) {

	$name = trim( $name );

	if ( '' === $name || ! taxonomy_exists( $taxonomy ) ) {
		return null;
	}

	$term = get_term_by( 'name', $name, $taxonomy );

	if ( $term ) {
		return $term->term_id;
	}

	$inserted = wp_insert_term( $name, $taxonomy );

	return is_wp_error( $inserted ) ? null : $inserted['term_id'];
}

/**
 * Глобальный атрибут («Товары → Атрибуты») по слагу — находит существующий
 * или регистрирует новый через wc_create_attribute(). Без этого таксономия
 * `pa_output-power` могла просто ещё не существовать в базе (в отличие от
 * `pa_freq-range` — тот уже заведён у «Анализаторов спектра»), и
 * wp_set_object_terms() на несуществующую таксономию молча ничего не
 * делает — атрибут в карточке товара не появляется, а ошибки не видно.
 *
 * @param string $slug  Слаг атрибута без pa_ (например, 'output-power').
 * @param string $label Человекочитаемое название атрибута.
 * @return string|null Имя таксономии ('pa_...') или null при ошибке.
 */
function amis_amp_import_ensure_attribute_taxonomy( $slug, $label ) {

	if ( ! function_exists( 'wc_attribute_taxonomy_name' ) ) {
		return null;
	}

	$taxonomy = wc_attribute_taxonomy_name( $slug );

	if ( taxonomy_exists( $taxonomy ) ) {
		return $taxonomy;
	}

	if ( ! function_exists( 'wc_create_attribute' ) ) {
		return null;
	}

	/**
	 * Защита от дублей: WC_Post_types::register_taxonomies() (штатный
	 * механизм WooCommerce) не подхватывает новый атрибут в ТОМ ЖЕ
	 * запросе — taxonomy_exists() выше продолжал бы возвращать false на
	 * каждой следующей строке CSV, и wc_create_attribute() создавал бы
	 * новый атрибут заново на каждой из них. Поэтому сначала проверяем
	 * саму таблицу атрибутов WooCommerce напрямую — если атрибут с таким
	 * именем там уже есть (создан на предыдущей строке этого же запуска),
	 * просто регистрируем его таксономию, не создавая новую запись.
	 */
	$existing_id = function_exists( 'wc_attribute_taxonomy_id_by_name' )
		? wc_attribute_taxonomy_id_by_name( $slug )
		: 0;

	if ( ! $existing_id ) {
		$attribute_id = wc_create_attribute( array(
			'name'         => $label,
			'slug'         => $slug,
			'type'         => 'select',
			'order_by'     => 'menu_order',
			'has_archives' => false,
		) );

		if ( is_wp_error( $attribute_id ) ) {
			return null;
		}

		delete_transient( 'wc_attribute_taxonomies' );
	}

	// Регистрируем таксономию сами, не полагаясь на то, подхватит ли её
	// внутренний механизм WooCommerce в этом же запросе.
	register_taxonomy(
		$taxonomy,
		'product',
		array(
			'hierarchical' => false,
			'show_ui'      => false,
			'query_var'    => true,
			'rewrite'      => false,
		)
	);

	return taxonomy_exists( $taxonomy ) ? $taxonomy : null;
}

/**
 * Ставит товару глобальный атрибут-таксономию: термин + запись в
 * _product_attributes (иначе вкладка «Атрибуты» в админке его не увидит,
 * см. amis_attr_import_set_value() в inc/attr-import.php — тот же приём).
 * Атрибут («Товары → Атрибуты») при необходимости создаётся сам.
 *
 * @param int    $product_id Товар.
 * @param string $slug       Слаг атрибута без pa_ ('freq-range' / 'output-power').
 * @param string $label      Название атрибута (для создания, если его ещё нет).
 * @param string $value      Значение как текст.
 */
function amis_amp_import_set_attribute( $product_id, $slug, $label, $value ) {

	$value = trim( (string) $value );

	if ( '' === $value ) {
		return;
	}

	$taxonomy = amis_amp_import_ensure_attribute_taxonomy( $slug, $label );

	if ( ! $taxonomy ) {
		return;
	}

	$term_id = amis_amp_import_get_or_create_term( $value, $taxonomy );

	if ( ! $term_id ) {
		return;
	}

	wp_set_object_terms( $product_id, array( $term_id ), $taxonomy, false );

	$attributes             = get_post_meta( $product_id, '_product_attributes', true );
	$attributes             = is_array( $attributes ) ? $attributes : array();
	$attributes[ $taxonomy ] = array(
		'name'         => $taxonomy,
		'value'        => '',
		'is_visible'   => 1,
		'is_variation' => 0,
		'is_taxonomy'  => 1,
	);

	update_post_meta( $product_id, '_product_attributes', $attributes );
}

/**
 * Ставит товару обычный (не таксономия) атрибут — «Тип», «Стандарты» и
 * подобные произвольные текстовые поля, которым не нужна регистрация в
 * «Товары → Атрибуты» и общий список значений по сайту.
 *
 * @param int    $product_id Товар.
 * @param string $label      Название атрибута («Тип», «Стандарты»).
 * @param string $value      Значение.
 */
function amis_amp_import_set_custom_attribute( $product_id, $label, $value ) {

	$value = trim( (string) $value );

	if ( '' === $value ) {
		return;
	}

	$attributes = get_post_meta( $product_id, '_product_attributes', true );
	$attributes = is_array( $attributes ) ? $attributes : array();
	$key        = sanitize_title( $label );

	$attributes[ $key ] = array(
		'name'         => $label,
		'value'        => $value,
		'position'     => count( $attributes ),
		'is_visible'   => 1,
		'is_variation' => 0,
		'is_taxonomy'  => 0,
	);

	update_post_meta( $product_id, '_product_attributes', $attributes );
}

/**
 * Разбор загруженного CSV: результат по каждой строке.
 *
 * @param string $tmp_path Путь к временно загруженному файлу.
 * @return array{created:array,skipped:array,errors:array}
 */
function amis_amp_import_process( $tmp_path ) {

	$result = array(
		'created' => array(),
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

	$first_line = preg_replace( '/^\xEF\xBB\xBF/', '', $first_line ); // BOM.

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

	$required = array( 'sku', 'title', 'category' );
	$cols     = array();

	foreach ( $header as $i => $name ) {
		$cols[ $name ] = $i;
	}

	foreach ( $required as $name ) {
		if ( ! isset( $cols[ $name ] ) ) {
			$result['errors'][] = sprintf(
				/* translators: %s — имя колонки. */
				__( 'В файле не нашлось обязательной колонки «%s».', 'amis' ),
				$name
			);
			fclose( $handle ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fclose
			return $result;
		}
	}

	$get = static function ( $row, $cols, $name ) {
		return isset( $cols[ $name ], $row[ $cols[ $name ] ] ) ? trim( (string) $row[ $cols[ $name ] ] ) : '';
	};

	while ( false !== ( $row = fgetcsv( $handle, 0, $delimiter ) ) ) { // phpcs:ignore Squiz.PHP.DisallowMultipleAssignments.FoundInControlStructure

		$sku = $get( $row, $cols, 'sku' );

		if ( '' === $sku ) {
			continue; // Пустая строка.
		}

		if ( wc_get_product_id_by_sku( $sku ) ) {
			$result['skipped'][] = $sku . ' — ' . __( 'такой артикул уже есть на сайте', 'amis' );
			continue;
		}

		$title = $get( $row, $cols, 'title' );

		if ( '' === $title ) {
			$result['errors'][] = $sku . ': ' . __( 'нет названия.', 'amis' );
			continue;
		}

		$product = new WC_Product_Simple();
		$product->set_name( $title );
		$product->set_sku( $sku );
		$product->set_status( 'draft' ); // Обязательно проверить руками перед публикацией.

		$short_description = $get( $row, $cols, 'short_description' );
		if ( $short_description ) {
			$product->set_short_description( $short_description );
		}

		$category_slug = $get( $row, $cols, 'category' );
		if ( $category_slug ) {
			$cat_term = get_term_by( 'slug', $category_slug, 'product_cat' );
			if ( $cat_term ) {
				$product->set_category_ids( array( $cat_term->term_id ) );
			}
		}

		$product_id = $product->save();

		if ( ! $product_id ) {
			$result['errors'][] = $sku . ': ' . __( 'не удалось создать товар.', 'amis' );
			continue;
		}

		$brand = $get( $row, $cols, 'brand' );
		if ( $brand ) {
			$brand_term_id = amis_amp_import_get_or_create_term( $brand, 'product_brand' );
			if ( $brand_term_id ) {
				wp_set_object_terms( $product_id, array( $brand_term_id ), 'product_brand', false );
			}
		}

		amis_amp_import_set_attribute( $product_id, 'freq-range', 'Диапазон частот', $get( $row, $cols, 'freq_range' ) );
		amis_amp_import_set_attribute( $product_id, 'output-power', 'Выходная мощность', $get( $row, $cols, 'output_power' ) );

		// Обычные (не таксономия) атрибуты — сразу видны на вкладке
		// «Атрибуты», без регистрации в «Товары → Атрибуты».
		amis_amp_import_set_custom_attribute( $product_id, 'Тип', $get( $row, $cols, 'type' ) );
		amis_amp_import_set_custom_attribute( $product_id, 'Стандарты', $get( $row, $cols, 'standards' ) );

		$source_url = $get( $row, $cols, 'source_url' );
		if ( $source_url ) {
			update_post_meta( $product_id, '_amis_import_source', $source_url );
		}

		$result['created'][] = array(
			'sku'   => $sku,
			'title' => $title,
			'id'    => $product_id,
		);
	}

	fclose( $handle ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fclose

	return $result;
}

/**
 * Страница в админке: форма загрузки + отчёт.
 */
function amis_amp_import_page() {

	if ( ! current_user_can( 'manage_woocommerce' ) ) {
		wp_die( esc_html__( 'Недостаточно прав.', 'amis' ) );
	}

	$result = null;

	if ( isset( $_POST['amis_amp_import_nonce'] )
		&& wp_verify_nonce( sanitize_key( $_POST['amis_amp_import_nonce'] ), 'amis_amp_import' )
		&& ! empty( $_FILES['amis_amp_file']['tmp_name'] )
	) {

		$file = $_FILES['amis_amp_file']; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- обрабатывается ниже: проверка кода ошибки, затем расширения.

		if ( UPLOAD_ERR_OK !== $file['error'] ) {
			$result = array(
				'errors'  => array( __( 'Ошибка загрузки файла.', 'amis' ) ),
				'created' => array(),
				'skipped' => array(),
			);
		} else {
			$ext = strtolower( pathinfo( $file['name'], PATHINFO_EXTENSION ) );

			if ( 'csv' !== $ext ) {
				$result = array(
					'errors'  => array( __( 'Нужен файл в формате CSV.', 'amis' ) ),
					'created' => array(),
					'skipped' => array(),
				);
			} else {
				$result = amis_amp_import_process( $file['tmp_name'] );
			}
		}
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Импорт усилителей (EMC)', 'amis' ); ?></h1>

		<p>
			<?php esc_html_e( 'Загрузите CSV со столбцами: sku, title, category, brand, freq_range, output_power, type, standards, short_description, source_url.', 'amis' ); ?>
		</p>
		<p>
			<?php esc_html_e( 'Товары создаются ЧЕРНОВИКАМИ — ничего не публикуется на сайт автоматически. Проверьте карточки в «Товары → Все товары» (фильтр «Черновик») и опубликуйте вручную то, что готово.', 'amis' ); ?>
		</p>
		<p>
			<strong><?php esc_html_e( 'После импорта обязательно:', 'amis' ); ?></strong>
			<?php esc_html_e( 'WooCommerce → Статус → Инструменты → «Regenerate the product attributes lookup table» — иначе новые товары не будут находиться в фильтрах каталога (см. историю разбора этого бага).', 'amis' ); ?>
		</p>

		<form method="post" enctype="multipart/form-data">
			<?php wp_nonce_field( 'amis_amp_import', 'amis_amp_import_nonce' ); ?>
			<input type="file" name="amis_amp_file" accept=".csv" required>
			<?php submit_button( __( 'Загрузить и создать черновики', 'amis' ) ); ?>
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
						/* translators: 1: создано, 2: пропущено. */
						esc_html__( 'Создано черновиков: %1$d. Пропущено (SKU уже есть): %2$d.', 'amis' ),
						count( $result['created'] ),
						count( $result['skipped'] )
					);
					?>
				</p>
			</div>

			<?php if ( ! empty( $result['created'] ) ) : ?>
				<h2><?php esc_html_e( 'Создано', 'amis' ); ?></h2>
				<table class="widefat striped">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Артикул', 'amis' ); ?></th>
							<th><?php esc_html_e( 'Название', 'amis' ); ?></th>
							<th><?php esc_html_e( 'Ссылка', 'amis' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $result['created'] as $row ) : ?>
							<tr>
								<td><?php echo esc_html( $row['sku'] ); ?></td>
								<td><?php echo esc_html( $row['title'] ); ?></td>
								<td><a href="<?php echo esc_url( get_edit_post_link( $row['id'] ) ); ?>"><?php esc_html_e( 'Открыть в редакторе', 'amis' ); ?></a></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>

			<?php if ( ! empty( $result['skipped'] ) ) : ?>
				<h2><?php esc_html_e( 'Пропущено', 'amis' ); ?></h2>
				<p><?php echo esc_html( implode( '; ', $result['skipped'] ) ); ?></p>
			<?php endif; ?>

		<?php endif; ?>
	</div>
	<?php
}
