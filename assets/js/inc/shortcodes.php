<?php
/**
 * Шорткоды.
 *
 * Пока один — частотная шкала из hero-секции. Оформлен шорткодом,
 * чтобы вставлять его и на страницы каталога, а не только на главную.
 *
 * @package amis
 */

defined( 'ABSPATH' ) || exit;

/**
 * Шкала «Подбор по рабочему диапазону».
 *
 * Данные берутся из атрибута товара pa_frequency-range:
 *   — название термина  → подпись диапазона («1 – 200 МГц»);
 *   — описание термина  → пояснение («Схемотехника, отладка»);
 *   — счётчик термина   → количество приборов, считается WordPress сам;
 *   — мета amis_cats    → категории через «|», для чипов под шкалой.
 *
 * Использование: [amis_freq_axis selected="2"]
 *
 * @param array $atts Атрибуты шорткода.
 * @return string HTML.
 */
function amis_freq_axis_shortcode( $atts ) {

	$atts = shortcode_atts(
		array(
			'selected' => 2, // Номер активного диапазона, с единицы.
		),
		$atts,
		'amis_freq_axis'
	);

$terms = get_terms( array(
	'taxonomy'   => 'pa_frequency-range',
	'hide_empty' => false,
	'orderby'    => 'id',
	'order'      => 'ASC',
) );

	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		return ''; // Атрибут ещё не заведён — молча ничего не выводим.
	}

	$selected  = max( 1, (int) $atts['selected'] );
	$shop_url  = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' );

	ob_start();
	?>
	<div class="bands" role="tablist" aria-label="<?php esc_attr_e( 'Диапазон частот', 'amis' ); ?>">
		<?php foreach ( array_values( $terms ) as $i => $term ) : ?>
			<?php
			$is_active = ( $i + 1 ) === $selected;
			$cats      = get_term_meta( $term->term_id, 'amis_cats', true );
			$url       = add_query_arg( 'filter_frequency-range', $term->slug, $shop_url );
			?>
			<button
				class="band"
				role="tab"
				aria-selected="<?php echo $is_active ? 'true' : 'false'; ?>"
				data-count="<?php echo esc_attr( $term->count ); ?>"
				data-cats="<?php echo esc_attr( $cats ); ?>"
				data-url="<?php echo esc_url( $url ); ?>"
			>
				<em><?php echo esc_html( $term->name ); ?></em>
				<small><?php echo esc_html( $term->description ); ?></small>
			</button>
		<?php endforeach; ?>
	</div>

	<div class="ruler" aria-hidden="true"></div>

	<div class="axis-out">
		<span class="count">
			<?php esc_html_e( 'В диапазоне', 'amis' ); ?>
			<b id="axBand"></b> —
			<b id="axCount"></b>
			<span id="axWord"><?php esc_html_e( 'приборов', 'amis' ); ?></span>
		</span>
		<span class="chips" id="axChips"></span>
		<span class="go">
			<a class="btn btn-primary" id="axLink" href="<?php echo esc_url( $shop_url ); ?>">
				<?php esc_html_e( 'Открыть подборку', 'amis' ); ?>
			</a>
		</span>
	</div>
	<?php

	return ob_get_clean();
}
add_shortcode( 'amis_freq_axis', 'amis_freq_axis_shortcode' );
