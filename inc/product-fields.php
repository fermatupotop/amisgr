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

	</div>
	<?php
}
add_action( 'woocommerce_product_data_panels', 'amis_product_data_panel' );

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

	// Многострочные поля: переносы сохраняем, теги вырезаем.
	$area_fields = array( '_amis_long_text' );

	foreach ( $area_fields as $field ) {
		$value = isset( $_POST[ $field ] ) ? sanitize_textarea_field( wp_unslash( $_POST[ $field ] ) ) : '';
		update_post_meta( $post_id, $field, $value );
	}
}
add_action( 'woocommerce_process_product_meta', 'amis_save_product_fields' );


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
