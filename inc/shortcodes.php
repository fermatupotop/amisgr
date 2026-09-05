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
	<svg class="axis-wave" viewBox="0 0 1200 90" preserveAspectRatio="none" aria-hidden="true">
	<!-- Приглушённая линия: видна целиком, задаёт форму -->
	<path class="axis-wave__base" d="M0 45c40 0 40-30 80-30s40 30 80 30 40-26 80-26 40 26 80 26 30-22 60-22 30 22 60 22 24-19 48-19 24 19 48 19 20-17 40-17 20 17 40 17 16-15 32-15 16 15 32 15 13-13 26-13 13 13 26 13 11-11 22-11 11 11 22 11 9-10 18-10 9 10 18 10 8-9 16-9 8 9 16 9 7-8 14-8 7 8 14 8 6-7 12-7 6 7 12 7 5-6 10-6 5 6 10 6h120"/>
	<!-- Яркая линия поверх: обрезается по активному диапазону -->
	<path class="axis-wave__live" d="M0 45c40 0 40-30 80-30s40 30 80 30 40-26 80-26 40 26 80 26 30-22 60-22 30 22 60 22 24-19 48-19 24 19 48 19 20-17 40-17 20 17 40 17 16-15 32-15 16 15 32 15 13-13 26-13 13 13 26 13 11-11 22-11 11 11 22 11 9-10 18-10 9 10 18 10 8-9 16-9 8 9 16 9 7-8 14-8 7 8 14 8 6-7 12-7 6 7 12 7 5-6 10-6 5 6 10 6h120"/>
</svg>
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
