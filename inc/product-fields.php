<?php
/**
 * Дополнительные поля товара — без ACF.
 *
 * Поля добавляются прямо во вкладки блока «Данные товара» через штатные
 * хуки WooCommerce. Плюс подхода: ноль плагинов, поля лежат рядом с ценой
 * и складом. Минус: нет визуального редактора, форматирование задаётся
 * простыми правилами (пустая строка — абзац, строка с «## » — подзаголовок).
 *
 * @package amis
 */

defined( 'ABSPATH' ) || exit;

/**
 * Своя вкладка в блоке «Данные товара».
 *
 * @param array $tabs Вкладки.
 * @return array
 */
function amis_product_data_tab( $tabs ) {

	$tabs['amis'] = array(
		'label'    => __( 'Данные АМИС', 'amis' ),
		'target'   => 'amis_product_data',
		'class'    => array(),
		'priority' => 25,
	);

	return $tabs;
}
add_filter( 'woocommerce_product_data_tabs', 'amis_product_data_tab' );

/**
 * Содержимое вкладки.
 */
function amis_product_data_panel() {

	global $post;
	?>
	<div id="amis_product_data" class="panel woocommerce_options_panel hidden">

		<div class="options_group">
			<?php
			woocommerce_wp_text_input( array(
				'id'          => '_amis_gosreestr',
				'label'       => __( 'Номер в Госреестре СИ', 'amis' ),
				'placeholder' => '89451-23',
				'desc_tip'    => true,
				'description' => __( 'Оставьте пустым, если прибор не внесён в реестр — блок просто не появится на странице.', 'amis' ),
			) );

			woocommerce_wp_text_input( array(
				'id'                => '_amis_sort_value',
				'label'             => __( 'Значение для сортировки', 'amis' ),
				'placeholder'       => '800',
				'type'              => 'number',
				'custom_attributes' => array( 'step' => 'any', 'min' => '0' ),
				'desc_tip'          => true,
				'description'       => __( 'Главный параметр числом, в единых единицах внутри категории: для осциллографов — полоса в МГц, для усилителей — мощность в Вт. По нему строится сортировка таблиц и подбор соседних моделей.', 'amis' ),
			) );

			woocommerce_wp_text_input( array(
				'id'          => '_amis_lead_time',
				'label'       => __( 'Срок поставки под заказ', 'amis' ),
				'placeholder' => '10–14 дней',
				'desc_tip'    => true,
				'description' => __( 'Показывается, когда товара нет на складе.', 'amis' ),
			) );

			woocommerce_wp_text_input( array(
				'id'          => '_amis_promo_text',
				'label'       => __( 'Акция: что дарим', 'amis' ),
				'placeholder' => 'Опции PA, EMI, B40, AMK',
				'desc_tip'    => true,
				'description' => __( 'Короткий список того, что идёт бесплатно. Блок на странице появляется, только если заполнено и это, и дата ниже.', 'amis' ),
			) );

			woocommerce_wp_text_input( array(
				'id'          => '_amis_promo_until',
				'label'       => __( 'Акция действует до', 'amis' ),
				'type'        => 'date',
				'desc_tip'    => true,
				'description' => __( 'После этой даты блок с акцией на странице сам перестанет показываться — не нужно потом убирать вручную.', 'amis' ),
			) );

			woocommerce_wp_checkbox( array(
				'id'          => '_amis_demo_available',
				'label'       => __( 'Доступен на тест', 'amis' ),
				'desc_tip'    => true,
				'description' => __( 'Показывает кнопку «Взять на тест на 14 дней» на странице товара. Демо-фонд ограничен — включайте только для тех моделей, которые реально можно дать на тест.', 'amis' ),
			) );

			woocommerce_wp_checkbox( array(
				'id'          => '_amis_exclusive',
				'label'       => __( 'Эксклюзив', 'amis' ),
				'desc_tip'    => true,
				'description' => __( 'В каталоге вместо обычной ленты наличия («Со склада»/«Под заказ»/«Хит продаж») показывает ленту «Эксклюзив» — для товаров, которых нет у других продавцов (собственная сборка, расширенная комплектация и т.п.). Не связано со складом — наличие на странице товара показывается как обычно.', 'amis' ),
			) );
			?>
		</div>

		<div class="options_group">
			<?php
			woocommerce_wp_textarea_input( array(
				'id'          => '_amis_long_text',
				'label'       => __( 'Развёрнутый материал', 'amis' ),
				'placeholder' => "## Полоса пропускания\nПравило пяти гармоник...\n\n## Пробники\nВ комплект входят...",
				'desc_tip'    => true,
				'description' => __( 'Выводится внизу страницы, под документами. Строка, начинающаяся с ##, становится подзаголовком. Пустая строка разделяет абзацы. Строка, начинающаяся с дефиса, — пункт списка.', 'amis' ),
				'style'       => 'height:220px',
			) );

			woocommerce_wp_textarea_input( array(
				'id'          => '_amis_applications',
				'label'       => __( 'Области применения', 'amis' ),
				'placeholder' => "receiver|Разработка и проверка GNSS-приёмников\nantenna|Испытания антенн с управляемой диаграммой направленности",
				'desc_tip'    => true,
				'description' => __( 'По одной строке на карточку: слаг иконки, вертикальная черта, текст. Доступные слаги — см. amis_app_icon_slugs() в inc/product-fields.php. Выводится сеткой карточек с иконками сразу после описания товара.', 'amis' ),
				'style'       => 'height:140px',
			) );

			woocommerce_wp_textarea_input( array(
				'id'          => '_amis_compare_table',
				'label'       => __( 'Сравнение моделей линейки', 'amis' ),
				'placeholder' => "Параметр|RFTEX ИНСС-8000|RFTEX ИНСС-4000\nИсполнение|Стационарное, 19″, 4U|Портативное\nКаналы|До 864|144",
				'desc_tip'    => true,
				'description' => __( 'Первая строка — заголовки столбцов (первый — «Параметр», дальше по одному названию модели), остальные строки — данные в том же порядке ячеек через «|». Строка текущего товара в таблице подсвечивается автоматически (сравнение по названию). На всех моделях линейки имеет смысл проставлять одинаковый текст.', 'amis' ),
				'style'       => 'height:140px',
			) );

			woocommerce_wp_textarea_input( array(
				'id'          => '_amis_compare_note',
				'label'       => __( 'Рекомендация по выбору', 'amis' ),
				'placeholder' => 'Для стационарной лаборатории — ИНСС-8000, для полевых выездов — ИНСС-4000.',
				'desc_tip'    => true,
				'description' => __( 'Короткий текст под таблицей сравнения — как выбрать между моделями линейки.', 'amis' ),
				'style'       => 'height:80px',
			) );
			?>
		</div>

		<div class="options_group">
			<?php amis_screens_field_render( $post->ID ); ?>
		</div>

	</div>
	<?php
}
add_action( 'woocommerce_product_data_panels', 'amis_product_data_panel' );

/**
 * Подключаем медиатеку WordPress только на странице редактирования
 * товара — она не нужна на других экранах, тянуть её везде незачем.
 */
function amis_enqueue_media_for_products() {

	$screen = get_current_screen();

	if ( $screen && 'product' === $screen->id ) {
		wp_enqueue_media();
	}
}
add_action( 'admin_enqueue_scripts', 'amis_enqueue_media_for_products' );

/**
 * Поле «Скриншоты и фото экрана» — не сам прибор (для этого есть штатная
 * галерея WooCommerce), а отдельные фото вроде показаний на экране,
 * снимков интерфейса и так далее. Хранится как строка ID через запятую —
 * тот же формат, что у штатного _product_image_gallery.
 *
 * @param int $post_id ID товара.
 */
function amis_screens_field_render( $post_id ) {

	$ids = amis_get_product_screens( $post_id );
	?>
	<p class="form-field">
		<label><?php esc_html_e( 'Скриншоты и фото экрана', 'amis' ); ?></label>
	</p>
	<p class="amis-screens-hint">
		<?php esc_html_e( 'Не фото самого прибора (для этого есть основная галерея выше) — показания экрана, интерфейс, осциллограммы и т.п. Выводится отдельной галереей на странице товара, между описанием и характеристиками.', 'amis' ); ?>
	</p>

	<ul class="amis-screens-list" id="amis-screens-list">
		<?php foreach ( $ids as $id ) : ?>
			<?php $src = wp_get_attachment_image_src( $id, 'thumbnail' ); ?>
			<?php if ( ! $src ) : continue; endif; ?>
			<li data-id="<?php echo esc_attr( $id ); ?>">
				<img src="<?php echo esc_url( $src[0] ); ?>" alt="">
				<button type="button" class="amis-screens-remove" aria-label="<?php esc_attr_e( 'Убрать', 'amis' ); ?>">&times;</button>
			</li>
		<?php endforeach; ?>
	</ul>

	<button type="button" class="button" id="amis-screens-add"><?php esc_html_e( 'Добавить фото', 'amis' ); ?></button>
	<input type="hidden" name="_amis_screens" id="amis-screens-input" value="<?php echo esc_attr( implode( ',', $ids ) ); ?>">

	<style>
		.amis-screens-hint{color:#646970;font-size:13px;margin:0 0 12px}
		.amis-screens-list{display:flex;flex-wrap:wrap;gap:8px;margin:0 0 12px;padding:0;list-style:none}
		.amis-screens-list:empty{margin:0}
		.amis-screens-list li{position:relative;width:64px;height:64px;border:1px solid #dcdcde;border-radius:2px;overflow:hidden}
		.amis-screens-list img{width:100%;height:100%;object-fit:cover;display:block}
		.amis-screens-remove{
			position:absolute;top:2px;right:2px;width:18px;height:18px;line-height:16px;padding:0;
			border:0;border-radius:50%;background:rgba(0,0,0,.65);color:#fff;font-size:13px;cursor:pointer;
		}
	</style>

	<script>
	( function ( $ ) {
		'use strict';

		var frame,
			$list  = $( '#amis-screens-list' ),
			$input = $( '#amis-screens-input' );

		function serialize() {
			var ids = [];
			$list.find( 'li' ).each( function () {
				ids.push( $( this ).data( 'id' ) );
			} );
			$input.val( ids.join( ',' ) );
		}

		$( '#amis-screens-add' ).on( 'click', function ( e ) {
			e.preventDefault();

			if ( frame ) {
				frame.open();
				return;
			}

			frame = wp.media( {
				title:    '<?php echo esc_js( __( 'Выберите изображения', 'amis' ) ); ?>',
				button:   { text: '<?php echo esc_js( __( 'Добавить', 'amis' ) ); ?>' },
				multiple: true,
				library:  { type: 'image' },
			} );

			frame.on( 'select', function () {
				frame.state().get( 'selection' ).each( function ( attachment ) {
					var data  = attachment.toJSON(),
						thumb = ( data.sizes && data.sizes.thumbnail ) ? data.sizes.thumbnail.url : data.url;

					$list.append(
						$( '<li>' ).attr( 'data-id', data.id ).append(
							$( '<img>' ).attr( 'src', thumb ),
							$( '<button>' ).attr( { type: 'button', 'aria-label': '<?php echo esc_js( __( 'Убрать', 'amis' ) ); ?>' } ).addClass( 'amis-screens-remove' ).html( '&times;' )
						)
					);
				} );
				serialize();
			} );

			frame.open();
		} );

		$list.on( 'click', '.amis-screens-remove', function ( e ) {
			e.preventDefault();
			$( this ).closest( 'li' ).remove();
			serialize();
		} );
	} )( jQuery );
	</script>
	<?php
}

/**
 * Сохранение полей.
 *
 * WooCommerce уже проверил nonce и права до вызова этого хука,
 * поэтому здесь достаточно санитизации значений.
 *
 * @param int $post_id ID товара.
 */
function amis_save_product_fields( $post_id ) {

	$text_fields = array( '_amis_gosreestr', '_amis_lead_time', '_amis_promo_text' );

	foreach ( $text_fields as $field ) {
		$value = isset( $_POST[ $field ] ) ? sanitize_text_field( wp_unslash( $_POST[ $field ] ) ) : '';
		update_post_meta( $post_id, $field, $value );
	}

	// Числовое поле сортировки.
	$sort = isset( $_POST['_amis_sort_value'] ) ? wc_format_decimal( wp_unslash( $_POST['_amis_sort_value'] ) ) : '';
	update_post_meta( $post_id, '_amis_sort_value', $sort );

	// Дата окончания акции — input[type=date] отдаёт готовый Y-m-d, доверяем формату браузера.
	$promo_until = isset( $_POST['_amis_promo_until'] ) ? sanitize_text_field( wp_unslash( $_POST['_amis_promo_until'] ) ) : '';
	update_post_meta( $post_id, '_amis_promo_until', $promo_until );

	// Чекбокс: при выключенном состоянии поле в $_POST вообще не приходит.
	update_post_meta( $post_id, '_amis_demo_available', isset( $_POST['_amis_demo_available'] ) ? 'yes' : 'no' );
	update_post_meta( $post_id, '_amis_exclusive', isset( $_POST['_amis_exclusive'] ) ? 'yes' : 'no' );

	// Многострочные поля: переносы сохраняем, теги вырезаем.
	$area_fields = array( '_amis_long_text', '_amis_applications', '_amis_compare_table', '_amis_compare_note' );

	foreach ( $area_fields as $field ) {
		$value = isset( $_POST[ $field ] ) ? sanitize_textarea_field( wp_unslash( $_POST[ $field ] ) ) : '';
		update_post_meta( $post_id, $field, $value );
	}

	// Скриншоты: строка ID через запятую, собранная JS-виджетом в amis_screens_field_render().
	$screens = isset( $_POST['_amis_screens'] ) ? sanitize_text_field( wp_unslash( $_POST['_amis_screens'] ) ) : '';
	$ids     = array_filter( array_map( 'absint', explode( ',', $screens ) ) );
	update_post_meta( $post_id, '_amis_screens', implode( ',', $ids ) );
}
add_action( 'woocommerce_process_product_meta', 'amis_save_product_fields' );

/**
 * ID скриншотов товара — распакованная строка _amis_screens.
 *
 * @param int $product_id ID товара.
 * @return int[]
 */
function amis_get_product_screens( $product_id ) {

	$raw = get_post_meta( $product_id, '_amis_screens', true );

	if ( ! $raw ) {
		return array();
	}

	return array_values( array_filter( array_map( 'absint', explode( ',', $raw ) ) ) );
}


/**
 * Простейшая разметка развёрнутого текста в HTML.
 *
 * Поддерживает: «## Заголовок», «- пункт списка», пустая строка — новый абзац.
 * Этого хватает, чтобы писать текст без единого тега.
 *
 * @param string $raw Сырой текст.
 * @return string Готовый HTML.
 */
function amis_render_long_text( $raw ) {

	$lines  = preg_split( '/\r\n|\r|\n/', (string) $raw );
	$html   = '';
	$buffer = array(); // Накопитель абзаца.
	$list   = array(); // Накопитель списка.

	// Сбрасывает накопленный абзац в разметку.
	$flush_paragraph = function () use ( &$buffer, &$html ) {
		if ( $buffer ) {
			$html .= '<p>' . esc_html( implode( ' ', $buffer ) ) . '</p>';
			$buffer = array();
		}
	};

	// Сбрасывает накопленный список.
	$flush_list = function () use ( &$list, &$html ) {
		if ( $list ) {
			$html .= '<ul>';
			foreach ( $list as $item ) {
				$html .= '<li>' . esc_html( $item ) . '</li>';
			}
			$html .= '</ul>';
			$list = array();
		}
	};

	foreach ( $lines as $line ) {

		$line = trim( $line );

		if ( '' === $line ) {
			$flush_paragraph();
			$flush_list();
			continue;
		}

		if ( 0 === strpos( $line, '## ' ) ) {
			$flush_paragraph();
			$flush_list();
			$html .= '<h3>' . esc_html( substr( $line, 3 ) ) . '</h3>';
			continue;
		}

		if ( 0 === strpos( $line, '- ' ) ) {
			$flush_paragraph();
			$list[] = substr( $line, 2 );
			continue;
		}

		$flush_list();
		$buffer[] = $line;
	}

	$flush_paragraph();
	$flush_list();

	return $html;
}

/**
 * Набор инлайн-SVG иконок для сетки «Области применения» (поле
 * _amis_applications) — 10 тематических штук, набор фиксирован: каждая
 * покрывает одну из повторяющихся тем в каталогах измерительного/ГНСС-
 * оборудования, новый слаг добавлять сюда же по мере надобности.
 * Стиль — как у остальных инлайн-иконок сайта (stroke=currentColor,
 * viewBox 0 0 24 24, см. templates/template-warranty.php).
 *
 * @return array<string,string> Слаг => markup <svg>.
 */
function amis_app_icon_library() {

	$attrs = 'width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"';

	return array(
		// Приёмник / микросхема.
		'receiver'  => "<svg {$attrs}><rect x=\"7\" y=\"7\" width=\"10\" height=\"10\" rx=\"1\"/><path d=\"M9 2v3M15 2v3M9 19v3M15 19v3M2 9h3M2 15h3M19 9h3M19 15h3\"/></svg>",
		// Антенна с диаграммой направленности.
		'antenna'   => "<svg {$attrs}><path d=\"M12 21V9\"/><path d=\"M8 9l4-6 4 6\"/><path d=\"M4 13a8 8 0 0 1 16 0\"/></svg>",
		// Лаборатория / колба.
		'lab'       => "<svg {$attrs}><path d=\"M10 2v6L4.5 18a2 2 0 0 0 1.8 3h11.4a2 2 0 0 0 1.8-3L14 8V2\"/><path d=\"M8.5 2h7M7 14h10\"/></svg>",
		// Спутник.
		'satellite' => "<svg {$attrs}><rect x=\"8\" y=\"8\" width=\"8\" height=\"8\" rx=\"1\" transform=\"rotate(45 12 12)\"/><path d=\"M4 10l2 2M20 14l-2-2M9 19l-3 3M15 5l3-3\"/></svg>",
		// Часы / синхронизация времени.
		'clock'     => "<svg {$attrs}><circle cx=\"12\" cy=\"12\" r=\"9\"/><path d=\"M12 7v5l3 3\"/></svg>",
		// Щит / защита критической инфраструктуры.
		'shield'    => "<svg {$attrs}><path d=\"M12 2l8 4v6c0 5-3.5 8.5-8 10-4.5-1.5-8-5-8-10V6l8-4z\"/></svg>",
		// БПЛА.
		'drone'     => "<svg {$attrs}><circle cx=\"12\" cy=\"12\" r=\"2.5\"/><path d=\"M5 5l4.5 4.5M19 5l-4.5 4.5M5 19l4.5-4.5M19 19l-4.5-4.5\"/><circle cx=\"5\" cy=\"5\" r=\"2\"/><circle cx=\"19\" cy=\"5\" r=\"2\"/><circle cx=\"5\" cy=\"19\" r=\"2\"/><circle cx=\"19\" cy=\"19\" r=\"2\"/></svg>",
		// Помехи / спуфинг.
		'jamming'   => "<svg {$attrs}><path d=\"M3 12h4l2-6 4 12 2-6h6\"/></svg>",
		// Полевые испытания.
		'field'     => "<svg {$attrs}><path d=\"M12 21s-7-5.5-7-11a7 7 0 0 1 14 0c0 5.5-7 11-7 11z\"/><circle cx=\"12\" cy=\"10\" r=\"2.5\"/></svg>",
		// Алгоритмы / прошивки.
		'code'      => "<svg {$attrs}><path d=\"M8 9l-4 3 4 3M16 9l4 3-4 3M13 6l-2 12\"/></svg>",
	);
}

/**
 * Список допустимых слагов — для подсказки в админке.
 *
 * @return string[]
 */
function amis_app_icon_slugs() {
	return array_keys( amis_app_icon_library() );
}

/**
 * Разбирает поле _amis_applications («слаг|текст» по строке) в массив.
 * Неизвестный слаг не роняет вывод — просто берётся заглушка-метка.
 *
 * @param string $raw Сырое значение поля.
 * @return array<int,array{icon:string,label:string}>
 */
function amis_parse_applications( $raw ) {

	$lines  = preg_split( '/\r\n|\r|\n/', (string) $raw );
	$icons  = amis_app_icon_library();
	$result = array();

	foreach ( $lines as $line ) {

		$line = trim( $line );

		if ( '' === $line || false === strpos( $line, '|' ) ) {
			continue;
		}

		list( $slug, $label ) = array_map( 'trim', explode( '|', $line, 2 ) );

		if ( '' === $label || ! isset( $icons[ $slug ] ) ) {
			continue;
		}

		$result[] = array(
			'icon'  => $slug,
			'svg'   => $icons[ $slug ],
			'label' => $label,
		);
	}

	return $result;
}

/**
 * Разбирает поле _amis_compare_table («ячейка|ячейка|...» по строке,
 * первая строка — заголовки) в массив для рендера таблицы сравнения.
 *
 * @param string $raw Сырое значение поля.
 * @return array{header:string[],rows:string[][]} Пусто, если заголовков
 *                                                 меньше 2 столбцов (нечего сравнивать).
 */
function amis_parse_compare_table( $raw ) {

	$lines  = array_values( array_filter( preg_split( '/\r\n|\r|\n/', (string) $raw ), 'strlen' ) );
	$empty  = array( 'header' => array(), 'rows' => array() );

	if ( ! $lines ) {
		return $empty;
	}

	$header = array_map( 'trim', explode( '|', array_shift( $lines ) ) );

	if ( count( $header ) < 2 ) {
		return $empty;
	}

	$rows = array();

	foreach ( $lines as $line ) {
		$rows[] = array_map( 'trim', explode( '|', $line ) );
	}

	return array(
		'header' => $header,
		'rows'   => $rows,
	);
}
