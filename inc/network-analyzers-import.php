<?php
/**
 * Импорт товаров категории «Анализаторы цепей» (сейчас — линейка Ceyear
 * 3657) из CSV — та же схема, что у inc/gnss-import.php: товар создаётся
 * ЧЕРНОВИКОМ, повторная загрузка с уже существующим SKU не создаёт новую
 * позицию, а обновляет контентные поля (описание, атрибуты, опции,
 * сравнение, «Обзор продукта»), не трогая название/категорию/бренд/статус.
 *
 * Формат файла:
 * sku,title,slug,category,brand,freq_range,ports,dynamic_range,chassis,
 * specs,sort_value,compare_table,compare_note,short_description,overview,
 * applications,options_own,options_common,source.
 *
 * В отличие от GNSS-оборудования, у линейки Ceyear 3657 даташит даёт
 * ОДНУ таблицу характеристик на все 4 модели (3657A/B/AM/BM) — простой
 * список «Параметр / Значение» без путаницы со столбцами, поэтому вся
 * таблица переносится в specs целиком (см. 'network-analyzers' в
 * inc/product-map.php), а не только 4 ключевых параметра, как у ИНСС/ГНСП.
 *
 * Опции — отдельный блок (inc/product-options.php, _amis_options_own /
 * _amis_options_common): у Ceyear 3657 их больше 25 штук на линейку, с
 * кодом заказа и отдельным назначением — для «Комплекта поставки»
 * (inc/product-package.php) формат не подходит, там только список строк
 * без кода и описания.
 *
 * @package amis
 */

defined( 'ABSPATH' ) || exit;

/**
 * Пункт меню — под «Товары».
 */
function amis_na_import_menu() {

	add_submenu_page(
		'edit.php?post_type=product',
		__( 'Импорт анализаторов цепей (Ceyear)', 'amis' ),
		__( 'Импорт анализаторов цепей', 'amis' ),
		'manage_woocommerce',
		'amis-na-import',
		'amis_na_import_page'
	);
}
add_action( 'admin_menu', 'amis_na_import_menu' );

/**
 * Термин таксономии по названию — найти или создать.
 *
 * @param string $name     Название термина.
 * @param string $taxonomy Слаг таксономии.
 * @return int|null ID термина.
 */
function amis_na_import_get_or_create_term( $name, $taxonomy ) {

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
 * Категория товара по слагу — найти или создать с явным слагом и названием.
 *
 * @param string $slug Слаг категории.
 * @param string $name Название категории.
 * @return int|null ID термина.
 */
function amis_na_import_get_or_create_category( $slug, $name ) {

	$term = get_term_by( 'slug', $slug, 'product_cat' );

	if ( $term ) {
		return $term->term_id;
	}

	$inserted = wp_insert_term( $name, 'product_cat', array( 'slug' => $slug ) );

	return is_wp_error( $inserted ) ? null : $inserted['term_id'];
}

/**
 * Ставит товару обычный (не таксономия) атрибут. Тот же приём, что
 * amis_gnss_import_set_custom_attribute() в inc/gnss-import.php.
 *
 * @param int    $product_id Товар.
 * @param string $label      Название атрибута.
 * @param string $value      Значение.
 * @param int    $position   Порядок на вкладке «Атрибуты».
 */
function amis_na_import_set_custom_attribute( $product_id, $label, $value, $position ) {

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
		'position'     => $position,
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
 * @return array{created:array,updated:array,errors:array}
 */
function amis_na_import_process( $tmp_path ) {

	$result = array(
		'created' => array(),
		'updated' => array(),
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

		$existing_id = wc_get_product_id_by_sku( $sku );
		$is_update   = (bool) $existing_id;

		if ( $is_update ) {

			$product_id = $existing_id;
			$title      = get_the_title( $product_id );

		} else {

			$title = $get( $row, $cols, 'title' );

			if ( '' === $title ) {
				$result['errors'][] = $sku . ': ' . __( 'нет названия.', 'amis' );
				continue;
			}

			$product = new WC_Product_Simple();
			$product->set_name( $title );
			$product->set_sku( $sku );
			$product->set_status( 'draft' ); // Обязательно проверить руками перед публикацией.

			$category_slug = $get( $row, $cols, 'category' );
			if ( $category_slug ) {
				$cat_id = amis_na_import_get_or_create_category( $category_slug, __( 'Анализаторы цепей', 'amis' ) );
				if ( $cat_id ) {
					$product->set_category_ids( array( $cat_id ) );
				}
			}

			$product_id = $product->save();

			if ( ! $product_id ) {
				$result['errors'][] = $sku . ': ' . __( 'не удалось создать товар.', 'amis' );
				continue;
			}

			$brand = $get( $row, $cols, 'brand' );
			if ( $brand ) {
				$brand_term_id = amis_na_import_get_or_create_term( $brand, 'product_brand' );
				if ( $brand_term_id ) {
					wp_set_object_terms( $product_id, array( $brand_term_id ), 'product_brand', false );
				}
			}
		}

		// Адрес товара — латиницей, явно из колонки slug (см. тот же приём
		// и его обоснование в inc/gnss-import.php).
		$slug = $get( $row, $cols, 'slug' );
		if ( $slug ) {
			wp_update_post( array(
				'ID'        => $product_id,
				'post_name' => $slug,
			) );
		}

		$short_description = $get( $row, $cols, 'short_description' );
		if ( $short_description ) {
			$product_obj = wc_get_product( $product_id );
			$product_obj->set_short_description( $short_description );
			$product_obj->save();
		}

		// 4 ключевых параметра — те же, что ждёт карта характеристик
		// 'network-analyzers' в inc/product-map.php.
		amis_na_import_set_custom_attribute( $product_id, 'Диапазон частот', $get( $row, $cols, 'freq_range' ), 0 );
		amis_na_import_set_custom_attribute( $product_id, 'Число портов', $get( $row, $cols, 'ports' ), 1 );
		amis_na_import_set_custom_attribute( $product_id, 'Динамический диапазон системы', $get( $row, $cols, 'dynamic_range' ), 2 );
		amis_na_import_set_custom_attribute( $product_id, 'Корпус', $get( $row, $cols, 'chassis' ), 3 );

		/**
		 * «Технические характеристики» — колонка specs, формат
		 * "Параметр:значение|Параметр:значение". У линейки Ceyear 3657
		 * даташит даёт простой список без путаницы со столбцами —
		 * переносится целиком (см. docblock в начале файла).
		 */
		$specs    = $get( $row, $cols, 'specs' );
		$position = 4;

		if ( $specs ) {
			foreach ( explode( '|', $specs ) as $pair ) {
				if ( false === strpos( $pair, ':' ) ) {
					continue;
				}
				list( $label, $value ) = array_map( 'trim', explode( ':', $pair, 2 ) );
				amis_na_import_set_custom_attribute( $product_id, $label, $value, $position );
				++$position;
			}
		}

		$sort_value = $get( $row, $cols, 'sort_value' );
		if ( '' !== $sort_value ) {
			update_post_meta( $product_id, '_amis_sort_value', wc_format_decimal( $sort_value ) );
		}

		$overview = $get( $row, $cols, 'overview' );
		if ( $overview ) {
			update_post_meta( $product_id, '_amis_long_text', str_replace( '\n', "\n", $overview ) );
		}

		$applications_raw = $get( $row, $cols, 'applications' );
		if ( $applications_raw ) {
			$lines = array();
			foreach ( explode( '|', $applications_raw ) as $pair ) {
				if ( false === strpos( $pair, ':' ) ) {
					continue;
				}
				list( $icon_slug, $label ) = array_map( 'trim', explode( ':', $pair, 2 ) );
				$lines[] = $icon_slug . '|' . $label;
			}
			if ( $lines ) {
				update_post_meta( $product_id, '_amis_applications', implode( "\n", $lines ) );
			}
		}

		$compare_table = $get( $row, $cols, 'compare_table' );
		if ( $compare_table ) {
			update_post_meta( $product_id, '_amis_compare_table', str_replace( '\n', "\n", $compare_table ) );
		}

		$compare_note = $get( $row, $cols, 'compare_note' );
		if ( $compare_note ) {
			update_post_meta( $product_id, '_amis_compare_note', $compare_note );
		}

		/**
		 * Опции прибора и общие опции/принадлежности — inc/product-options.php,
		 * формат строки "Код|Название|Назначение" (реальный перенос строки
		 * внутри ячейки CSV в кавычках разделяет позиции).
		 */
		$options_own = $get( $row, $cols, 'options_own' );
		if ( $options_own ) {
			update_post_meta( $product_id, '_amis_options_own', str_replace( '\n', "\n", $options_own ) );
		}

		$options_common = $get( $row, $cols, 'options_common' );
		if ( $options_common ) {
			update_post_meta( $product_id, '_amis_options_common', str_replace( '\n', "\n", $options_common ) );
		}

		$source = $get( $row, $cols, 'source' );
		if ( $source ) {
			update_post_meta( $product_id, '_amis_import_source', $source );
		}

		if ( $is_update ) {
			$result['updated'][] = array(
				'sku'   => $sku,
				'title' => $title,
				'id'    => $product_id,
			);
		} else {
			$result['created'][] = array(
				'sku'   => $sku,
				'title' => $title,
				'id'    => $product_id,
			);
		}
	}

	fclose( $handle ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fclose

	return $result;
}

/**
 * Страница в админке: форма загрузки + отчёт.
 */
function amis_na_import_page() {

	if ( ! current_user_can( 'manage_woocommerce' ) ) {
		wp_die( esc_html__( 'Недостаточно прав.', 'amis' ) );
	}

	$result = null;

	if ( isset( $_POST['amis_na_import_nonce'] )
		&& wp_verify_nonce( sanitize_key( $_POST['amis_na_import_nonce'] ), 'amis_na_import' )
		&& ! empty( $_FILES['amis_na_file']['tmp_name'] )
	) {

		$file = $_FILES['amis_na_file']; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- обрабатывается ниже: проверка кода ошибки, затем расширения.

		if ( UPLOAD_ERR_OK !== $file['error'] ) {
			$result = array(
				'errors'  => array( __( 'Ошибка загрузки файла.', 'amis' ) ),
				'created' => array(),
				'updated' => array(),
			);
		} else {
			$ext = strtolower( pathinfo( $file['name'], PATHINFO_EXTENSION ) );

			if ( 'csv' !== $ext ) {
				$result = array(
					'errors'  => array( __( 'Нужен файл в формате CSV.', 'amis' ) ),
					'created' => array(),
					'updated' => array(),
				);
			} else {
				$result = amis_na_import_process( $file['tmp_name'] );
			}
		}
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Импорт анализаторов цепей (Ceyear)', 'amis' ); ?></h1>

		<p>
			<?php esc_html_e( 'Загрузите CSV со столбцами: sku, title, slug, category, brand, freq_range, ports, dynamic_range, chassis, specs, sort_value, compare_table, compare_note, short_description, overview, applications, options_own, options_common, source.', 'amis' ); ?>
		</p>
		<p>
			<?php esc_html_e( 'freq_range/ports/dynamic_range/chassis — 4 ключевых параметра (плитки под галереей), остальные характеристики — колонка specs: Параметр:значение|Параметр:значение|… (названия параметров — см. группы «network-analyzers» в inc/product-map.php).', 'amis' ); ?><br>
			<?php esc_html_e( 'compare_table/compare_note — «Сравнение моделей линейки», одинаковый текст в строке каждой модели (формат — см. inc/gnss-import.php).', 'amis' ); ?><br>
			<?php esc_html_e( 'options_own — опции, которые меняют сам прибор (апгрейд диапазона/портов), обычно свои у каждой модели. options_common — калибровочные комплекты, кабели, кейсы и т.п., обычно один список на всю линейку. Формат обеих колонок одинаковый: Код|Название|Назначение, по одной опции на строку (inc/product-options.php).', 'amis' ); ?><br>
			<?php esc_html_e( 'slug — адрес товара латиницей. sort_value — число для «Соседних моделей в линейке» (общий принцип — см. inc/gnss-import.php).', 'amis' ); ?>
		</p>
		<p>
			<?php esc_html_e( 'Новый артикул — товар создаётся ЧЕРНОВИКОМ. Уже существующий артикул — название/категория/бренд/статус не трогаются, обновляются только контентные поля: тем же файлом можно дополнять уже созданные товары новыми колонками.', 'amis' ); ?>
		</p>
		<p>
			<strong><?php esc_html_e( 'После импорта обязательно:', 'amis' ); ?></strong>
			<?php esc_html_e( 'WooCommerce → Статус → Инструменты → «Regenerate the product attributes lookup table».', 'amis' ); ?>
		</p>

		<form method="post" enctype="multipart/form-data">
			<?php wp_nonce_field( 'amis_na_import', 'amis_na_import_nonce' ); ?>
			<input type="file" name="amis_na_file" accept=".csv" required>
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
						/* translators: 1: создано, 2: обновлено. */
						esc_html__( 'Создано черновиков: %1$d. Обновлено существующих: %2$d.', 'amis' ),
						count( $result['created'] ),
						count( $result['updated'] )
					);
					?>
				</p>
			</div>

			<?php
			$tables = array(
				'created' => __( 'Создано', 'amis' ),
				'updated' => __( 'Обновлено', 'amis' ),
			);
			?>
			<?php foreach ( $tables as $key => $label ) : ?>
				<?php if ( ! empty( $result[ $key ] ) ) : ?>
					<h2><?php echo esc_html( $label ); ?></h2>
					<table class="widefat striped">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Артикул', 'amis' ); ?></th>
								<th><?php esc_html_e( 'Название', 'amis' ); ?></th>
								<th><?php esc_html_e( 'Ссылка', 'amis' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $result[ $key ] as $row ) : ?>
								<tr>
									<td><?php echo esc_html( $row['sku'] ); ?></td>
									<td><?php echo esc_html( $row['title'] ); ?></td>
									<td><a href="<?php echo esc_url( get_edit_post_link( $row['id'] ) ); ?>"><?php esc_html_e( 'Открыть в редакторе', 'amis' ); ?></a></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				<?php endif; ?>
			<?php endforeach; ?>

		<?php endif; ?>
	</div>
	<?php
}
