<?php
/**
 * Карточка товара для главной.
 *
 * Ожидает переменную $args['product'] — объект WC_Product.
 * Вызывается так:
 *   get_template_part( 'template-parts/product-card', null, array( 'product' => $product ) );
 *
 * Третий аргумент get_template_part() появился в WordPress 5.5 —
 * это современная замена глобальным переменным и set_query_var().
 *
 * @package amis
 */

defined( 'ABSPATH' ) || exit;

$product = isset( $args['product'] ) ? $args['product'] : null;

if ( ! $product instanceof WC_Product ) {
	return;
}

$stock = amis_stock_state( $product );
$id    = $product->get_id();
?>

<article class="card">

	<?php if ( $product->is_featured() ) : ?>
		<span class="tag"><?php esc_html_e( 'Хит продаж', 'amis' ); ?></span>
	<?php elseif ( ! $product->is_in_stock() ) : ?>
		<span class="tag tag--g"><?php esc_html_e( 'Под заказ', 'amis' ); ?></span>
	<?php else : ?>
		<span class="tag"><?php esc_html_e( 'Со склада', 'amis' ); ?></span>
	<?php endif; ?>

	<div class="card-img">
		<a href="<?php echo esc_url( get_permalink( $id ) ); ?>">
			<?php echo $product->get_image( 'woocommerce_thumbnail' ); // phpcs:ignore WordPress.Security.EscapeOutput — WooCommerce возвращает готовый <img>. ?>
		</a>
	</div>

	<div class="card-b">

		<span class="art"><?php echo esc_html( $product->get_sku() ); ?></span>

		<h3>
			<a href="<?php echo esc_url( get_permalink( $id ) ); ?>">
				<?php echo esc_html( $product->get_name() ); ?>
			</a>
		</h3>

		<ul class="spec">
			<?php
			/**
			 * Три ключевые характеристики. Какие именно — зависит от категории,
			 * поэтому список слагов атрибутов держим здесь одним массивом:
			 * поменять набор можно в одном месте.
			 */
			$specs = array(
				'bandwidth'  => __( 'Полоса', 'amis' ),
				'channels'   => __( 'Каналы', 'amis' ),
				'sample-rate' => __( 'Дискретизация', 'amis' ),
			);

			foreach ( $specs as $slug => $label ) :
				$value = $product->get_attribute( 'pa_' . $slug );
				if ( ! $value ) {
					$value = $product->get_attribute( $slug );
				}
				?>
				<li>
					<span><?php echo esc_html( $label ); ?></span>
					<b><?php echo esc_html( $value ); ?></b>
				</li>
			<?php endforeach; ?>
		</ul>

		<?php
		/**
		 * Лента в углу карточки уже показывает «Под заказ», когда товар
		 * не в наличии и не «Хит продаж» (см. разметку ленты выше) — эта
		 * строка тогда повторяла бы то же самое без новой информации.
		 * Не прячем её, если товар «Хит продаж»: там лента говорит про
		 * другое, и нижняя строка — единственное место, где видно наличие.
		 */
		$repeats_ribbon = ! $product->is_featured() && ! $product->is_in_stock();
		?>
		<?php if ( ! $repeats_ribbon ) : ?>
			<div class="<?php echo esc_attr( $stock['class'] ); ?> avail-row">
				<i></i> <?php echo esc_html( $stock['label'] ); ?>
			</div>
		<?php endif; ?>

		<div class="card-f">
			<?php if ( $product->get_price() ) : ?>

				<span class="p">
					<?php echo wp_kses_post( $product->get_price_html() ); ?>
					<small><?php esc_html_e( 'с НДС 22%', 'amis' ); ?></small>
				</span>

				<?php
				/**
				 * Кнопка «В корзину». data-quantity и класс add_to_cart_button
				 * обязательны — на них завязан AJAX WooCommerce.
				 */
				?>
				<button
					class="buy add_to_cart_button ajax_add_to_cart"
					data-product_id="<?php echo esc_attr( $id ); ?>"
					data-quantity="1"
					aria-label="<?php esc_attr_e( 'Добавить в корзину', 'amis' ); ?>"
				>
					<svg width="19" height="19" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
						<path d="M3 4h2l2 9h8l2-6H6" />
						<circle cx="8.5" cy="16" r="1.2" />
						<circle cx="14.5" cy="16" r="1.2" />
					</svg>
				</button>

			<?php else : ?>

				<span class="req"><?php esc_html_e( 'Цена по запросу', 'amis' ); ?></span>
				<a class="btn btn-primary btn-sm" href="#contact"><?php esc_html_e( 'Запросить', 'amis' ); ?></a>

			<?php endif; ?>
		</div>

	</div>
</article>
