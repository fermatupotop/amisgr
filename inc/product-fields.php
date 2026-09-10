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

			woocommerce_wp_checkbox( array(
				'id'          => '_amis_demo_available',
				'label'       => __( 'Доступен на тест', 'amis' ),
				'desc_tip'    => true,
				'description' => __( 'Показывает кнопку «Взять на тест на 14 дней» на странице товара. Демо-фонд ограничен — включайте только для тех моделей, которые реально можно дать на тест.', 'amis' ),
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

	$text_fields = array( '_amis_gosreestr', '_amis_lead_time' );

	foreach ( $text_fields as $field ) {
		$value = isset( $_POST[ $field ] ) ? sanitize_text_field( wp_unslash( $_POST[ $field ] ) ) : '';
		update_post_meta( $post_id, $field, $value );
	}

	// Числовое поле сортировки.
	$sort = isset( $_POST['_amis_sort_value'] ) ? wc_format_decimal( wp_unslash( $_POST['_amis_sort_value'] ) ) : '';
	update_post_meta( $post_id, '_amis_sort_value', $sort );

	// Чекбокс: при выключенном состоянии поле в $_POST вообще не приходит.
	update_post_meta( $post_id, '_amis_demo_available', isset( $_POST['_amis_demo_available'] ) ? 'yes' : 'no' );

	// Многострочные поля: переносы сохраняем, теги вырезаем.
	$area_fields = array( '_amis_long_text' );

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
