<?php
/**
 * Страница товара, вариант «Даташит».
 *
 * Файл лежит в папке woocommerce/ дочерней темы — WooCommerce ищет
 * шаблоны сначала там и только потом в самом плагине. Переопределение
 * работает автоматически, регистрировать ничего не нужно.
 *
 * Мы почти не используем стандартные хуки вывода
 * (woocommerce_single_product_summary и соседние), поэтому часть плагинов,
 * которые в них встраиваются, здесь работать не будет. Взамен —
 * полный контроль над разметкой. Микроразметку Schema.org, которую
 * обычно генерирует один из этих хуков, вызываем вручную.
 *
 * @package amis
 */

defined( 'ABSPATH' ) || exit;

get_header( 'shop' );

global $product;

if ( ! is_a( $product, 'WC_Product' ) ) {
	$product = wc_get_product( get_the_ID() );
}

$product_id = $product->get_id();

// Собственные поля.
$gosreestr = get_post_meta( $product_id, '_amis_gosreestr', true );
$lead_time = get_post_meta( $product_id, '_amis_lead_time', true );
$kit       = amis_parse_lines( get_post_meta( $product_id, '_amis_kit', true ) );
$docs      = amis_parse_lines( get_post_meta( $product_id, '_amis_docs', true ) );
$long_text = get_post_meta( $product_id, '_amis_long_text', true );

// Характеристики.
$key_specs = amis_key_specs( $product );
$groups    = amis_grouped_specs( $product );
$neighbors = amis_get_neighbors( $product );

// Микроразметка товара для поисковиков.
if ( isset( WC()->structured_data ) ) {
	WC()->structured_data->generate_product_data( $product );
}
?>

<?php do_action( 'woocommerce_before_main_content' ); // Уведомления, хлебные крошки плагинов. ?>

<!-- Хлебные крошки -->
<nav class="crumbs">
	<div class="wrap">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Главная', 'amis' ); ?></a><span>/</span>
		<a href="<?php echo esc_url( amis_shop_url() ); ?>"><?php esc_html_e( 'Каталог', 'amis' ); ?></a><span>/</span>
		<?php
		$cats = wp_get_post_terms( $product_id, 'product_cat' );
		if ( ! is_wp_error( $cats ) && $cats ) :
			?>
			<a href="<?php echo esc_url( get_term_link( $cats[0] ) ); ?>"><?php echo esc_html( $cats[0]->name ); ?></a><span>/</span>
		<?php endif; ?>
		<span><?php the_title(); ?></span>
	</div>
</nav>

<div id="product-<?php echo esc_attr( $product_id ); ?>" <?php wc_product_class( 'amis-single', $product ); ?>>

	<!-- Шапка товара -->
	<section class="head">
		<div class="wrap head-top">
			<div>
				<h1><?php the_title(); ?></h1>

				<?php if ( $product->get_short_description() ) : ?>
					<p class="subtitle"><?php echo esc_html( wp_strip_all_tags( $product->get_short_description() ) ); ?></p>
				<?php endif; ?>

				<div class="head-meta">
					<?php if ( $product->get_sku() ) : ?>
						<span><?php esc_html_e( 'Артикул:', 'amis' ); ?> <b><?php echo esc_html( $product->get_sku() ); ?></b></span>
					<?php endif; ?>

					<?php
					// Производителя берём из глобального атрибута pa_brand, если он заполнен.
					$brand = $product->get_attribute( 'pa_brand' );
					if ( $brand ) :
						?>
						<span><?php esc_html_e( 'Производитель:', 'amis' ); ?> <b><?php echo esc_html( $brand ); ?></b></span>
					<?php endif; ?>

					<?php
					$warranty = $product->get_attribute( 'pa_warranty' );
					if ( $warranty ) :
						?>
						<span><?php esc_html_e( 'Гарантия:', 'amis' ); ?> <b><?php echo esc_html( $warranty ); ?></b></span>
					<?php endif; ?>
				</div>
			</div>

			<?php
			// Блок Госреестра показываем только если номер заполнен:
			// не все приборы внесены в реестр, и врать тут нельзя.
			if ( $gosreestr ) :
				?>
				<span class="reg">
					<svg width="17" height="17" viewBox="0 0 17 17" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M3 9l3.5 3.5L14 5"/></svg>
					<?php
					/* translators: %s — номер в Госреестре СИ. */
					printf( esc_html__( 'В Госреестре СИ № %s', 'amis' ), esc_html( $gosreestr ) );
					?>
				</span>
			<?php endif; ?>
		</div>
	</section>

	<!-- Главный экран -->
	<div class="wrap main">

<?php
$gallery = $product->get_gallery_image_ids();
$main_id = $product->get_image_id();

// Все изображения одним массивом: главное первым.
$images = $main_id ? array_merge( array( $main_id ), $gallery ) : $gallery;
?>

<div class="gallery">
	<div class="gal-big">
		<?php if ( $images ) : ?>
			<?php foreach ( $images as $i => $image_id ) : ?>
				<div class="gal-slide <?php echo 0 === $i ? 'on' : ''; ?>" data-slide="<?php echo esc_attr( $i ); ?>">
					<?php echo wp_get_attachment_image( $image_id, 'large' ); ?>
				</div>
			<?php endforeach; ?>
		<?php else : ?>
			<?php echo $product->get_image( 'woocommerce_single' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		<?php endif; ?>
	</div>

	<?php if ( count( $images ) > 1 ) : ?>
		<div class="gal-thumbs">
			<?php foreach ( $images as $i => $image_id ) : ?>
				<button
					class="gal-thumb <?php echo 0 === $i ? 'on' : ''; ?>"
					data-slide="<?php echo esc_attr( $i ); ?>"
					aria-label="<?php echo esc_attr( sprintf( 'Фото %d', $i + 1 ) ); ?>"
				>
					<?php echo wp_get_attachment_image( $image_id, 'thumbnail' ); ?>
				</button>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</div>

		<!-- Панель покупки -->
		<aside class="buy">
			<div class="buy-price">
				<?php if ( $product->get_price() ) : ?>
					<div class="price"><?php echo wp_kses_post( $product->get_price_html() ); ?></div>
					<div class="price-note"><?php esc_html_e( 'с НДС 20%, включая первичную поверку', 'amis' ); ?></div>
				<?php else : ?>
					<div class="price" style="font-size:22px"><?php esc_html_e( 'Цена по запросу', 'amis' ); ?></div>
					<div class="price-note"><?php esc_html_e( 'Пришлём КП в течение рабочего дня', 'amis' ); ?></div>
				<?php endif; ?>

				<?php $stock = amis_stock_state( $product ); ?>
				<div class="stock <?php echo esc_attr( $product->is_in_stock() ? '' : 'stock--o' ); ?>">
					<i></i>
					<?php
					echo esc_html( $stock['label'] );
					if ( ! $product->is_in_stock() && $lead_time ) {
						echo esc_html( ', ' . $lead_time );
					}
					?>
				</div>
			</div>

			<div class="buy-acts">
				<?php
				// Штатная форма добавления в корзину: даёт работающий AJAX,
				// вариации и проверку остатков. Стилизуем через CSS.
				if ( $product->get_price() ) {
					woocommerce_template_single_add_to_cart();
				}
				?>
				<a class="btn btn-ghost btn-block" href="<?php echo esc_url( home_url( '/request/?sku=' . rawurlencode( $product->get_sku() ) ) ); ?>">
					<?php esc_html_e( 'Запросить счёт', 'amis' ); ?>
				</a>
				<a class="btn btn-ghost btn-block" href="<?php echo esc_url( home_url( '/demo/' ) ); ?>">
					<?php esc_html_e( 'Взять на тест на 14 дней', 'amis' ); ?>
				</a>
			</div>

			<div class="buy-list">
				<div>
					<svg width="17" height="17" viewBox="0 0 17 17" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M3 9l3.5 3.5L14 5"/></svg>
					<?php esc_html_e( 'Отгрузка в течение 24 часов после оплаты', 'amis' ); ?>
				</div>
				<div>
					<svg width="17" height="17" viewBox="0 0 17 17" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M3 9l3.5 3.5L14 5"/></svg>
					<?php esc_html_e( 'Свидетельство о поверке ЦСМ в комплекте', 'amis' ); ?>
				</div>
				<div>
					<svg width="17" height="17" viewBox="0 0 17 17" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M3 9l3.5 3.5L14 5"/></svg>
					<?php esc_html_e( 'Безналичный расчёт, работа по 44-ФЗ и 223-ФЗ', 'amis' ); ?>
				</div>
			</div>

			<div class="mgr">
				<span class="mgr-ava"><?php echo esc_html( 'АК' ); ?></span>
				<div>
					<b><?php esc_html_e( 'Алексей Кузнецов', 'amis' ); ?></b>
					<span><?php esc_html_e( 'Инженер по КИП', 'amis' ); ?></span>
					<a href="tel:+74957700497">+7 (495) 770-04-97</a>
				</div>
			</div>
		</aside>
	</div>

	<!-- Описание -->
	<?php if ( $product->get_description() ) : ?>
		<section class="sec">
			<div class="wrap about">
				<div>
					<h2><?php esc_html_e( 'Описание', 'amis' ); ?></h2>
					<?php echo wp_kses_post( wpautop( $product->get_description() ) ); ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<!-- Характеристики -->
	<?php if ( $groups ) : ?>
		<section class="sec">
			<div class="wrap">
				<h2><?php esc_html_e( 'Технические характеристики', 'amis' ); ?></h2>
				<div class="specs">
					<?php foreach ( $groups as $title => $rows ) : ?>
						<div class="spec-group">
							<h3><?php echo esc_html( $title ); ?></h3>
							<dl>
								<?php foreach ( $rows as $row ) : ?>
									<dt><?php echo esc_html( $row['label'] ); ?></dt>
									<dd><?php echo esc_html( $row['value'] ); ?></dd>
								<?php endforeach; ?>
							</dl>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<!-- Комплект поставки -->
	<?php if ( $kit ) : ?>
		<section class="sec">
			<div class="wrap">
				<h2><?php esc_html_e( 'Комплект поставки', 'amis' ); ?></h2>
				<ul class="kit">
					<?php foreach ( $kit as $item ) : ?>
						<li>
							<svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M3 8.5L6.5 12 13 4.5"/></svg>
							<?php echo esc_html( $item[0] ); ?>
							<?php if ( isset( $item[1] ) ) : ?>
								<span><?php echo esc_html( $item[1] ); ?></span>
							<?php endif; ?>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		</section>
	<?php endif; ?>

	<!-- Документы -->
	<?php if ( $docs ) : ?>
		<section class="sec">
			<div class="wrap">
				<h2><?php esc_html_e( 'Документация', 'amis' ); ?></h2>
				<p class="sec-lead"><?php esc_html_e( 'Файлы доступны без регистрации — можно приложить к заявке на закупку или к обоснованию НМЦК.', 'amis' ); ?></p>
				<div class="docs">
					<?php foreach ( $docs as $doc ) : ?>
						<a class="doc" href="<?php echo esc_url( isset( $doc[1] ) ? $doc[1] : '#' ); ?>" target="_blank" rel="noopener">
							<svg width="26" height="26" viewBox="0 0 26 26" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M15 2H7a2 2 0 0 0-2 2v18a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M15 2v6h6"/></svg>
							<div>
								<b><?php echo esc_html( $doc[0] ); ?></b>
								<?php if ( isset( $doc[2] ) ) : ?>
									<span><?php echo esc_html( $doc[2] ); ?></span>
								<?php endif; ?>
							</div>
						</a>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<!-- Развёрнутый материал -->
	<?php if ( $long_text ) : ?>
		<section class="sec">
			<div class="wrap long">
				<h2 style="margin-bottom:22px"><?php esc_html_e( 'Что учесть при выборе', 'amis' ); ?></h2>
				<?php echo wp_kses_post( amis_render_long_text( $long_text ) ); ?>
			</div>
		</section>
	<?php endif; ?>

	<!-- Поверка, гарантия, доставка -->
	<section class="sec">
		<div class="wrap">
			<h2><?php esc_html_e( 'Поверка, гарантия, доставка', 'amis' ); ?></h2>
			<div class="verify">
				<div>
					<h3><?php esc_html_e( 'Поверка', 'amis' ); ?></h3>
					<p><?php esc_html_e( 'Первичная поверка в аккредитованном ЦСМ входит в стоимость. Напомним о сроке очередной поверки заранее.', 'amis' ); ?></p>
				</div>
				<div>
					<h3><?php esc_html_e( 'Гарантия', 'amis' ); ?></h3>
					<p><?php esc_html_e( 'Гарантия производителя, сервисный центр в Москве, подменный прибор на время ремонта.', 'amis' ); ?></p>
				</div>
				<div>
					<h3><?php esc_html_e( 'Доставка', 'amis' ); ?></h3>
					<p><?php esc_html_e( 'По Москве — курьером за наш счёт. По России — транспортной компанией, отгрузка в течение 24 часов после оплаты.', 'amis' ); ?></p>
				</div>
			</div>
		</div>
	</section>

	<!-- Соседние модели -->
	<?php if ( $neighbors['prev'] || $neighbors['next'] ) : ?>
		<section class="sec">
			<div class="wrap">
				<h2><?php esc_html_e( 'Соседние модели в линейке', 'amis' ); ?></h2>
				<p class="sec-lead"><?php esc_html_e( 'Если параметров избыточно или, наоборот, впритык — вот что стоит сравнить.', 'amis' ); ?></p>
				<div class="alt">

					<?php
					// Три карточки: ступень ниже, текущая, ступень выше.
					$cards = array(
						array( __( 'Ступень ниже', 'amis' ), $neighbors['prev'], false ),
						array( __( 'Вы смотрите', 'amis' ), $product, true ),
						array( __( 'Ступень выше', 'amis' ), $neighbors['next'], false ),
					);

					foreach ( $cards as $card ) :
						list( $tag, $item, $is_current ) = $card;

						if ( ! $item ) {
							continue;
						}

						$item_specs = array_slice( amis_key_specs( $item ), 0, 3, true );
						$tag_class  = $is_current ? 'alt-c this' : 'alt-c';
						?>

						<?php if ( $is_current ) : ?>
							<div class="<?php echo esc_attr( $tag_class ); ?>">
						<?php else : ?>
							<a class="<?php echo esc_attr( $tag_class ); ?>" href="<?php echo esc_url( get_permalink( $item->get_id() ) ); ?>">
						<?php endif; ?>

							<span class="alt-tag"><?php echo esc_html( $tag ); ?></span>
							<h3><?php echo esc_html( $item->get_name() ); ?></h3>

							<dl>
								<?php foreach ( $item_specs as $spec ) : ?>
									<dt><?php echo esc_html( $spec['label'] ); ?></dt>
									<dd><?php echo esc_html( $spec['value'] ); ?></dd>
								<?php endforeach; ?>
							</dl>

							<span class="alt-p">
								<?php echo $item->get_price() ? wp_kses_post( $item->get_price_html() ) : esc_html__( 'По запросу', 'amis' ); ?>
							</span>

						<?php if ( $is_current ) : ?>
							</div>
						<?php else : ?>
							</a>
						<?php endif; ?>

					<?php endforeach; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<!-- Нижний призыв -->
	<section class="foot-cta">
		<div class="wrap">
			<div>
				<h2><?php esc_html_e( 'Остались вопросы по прибору?', 'amis' ); ?></h2>
				<p><?php esc_html_e( 'Инженер сравнит модели под вашу задачу, проверит совместимость с имеющимися пробниками и ПО, посчитает комплект.', 'amis' ); ?></p>
			</div>
			<a class="btn btn-primary" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">
				<?php esc_html_e( 'Задать вопрос инженеру', 'amis' ); ?>
			</a>
		</div>
	</section>

</div>

<?php do_action( 'woocommerce_after_main_content' ); ?>

<?php get_footer( 'shop' );
