<?php
/**
 * Template Name: Главная АМИС
 *
 * Шаблон главной страницы. Чтобы его применить: создайте страницу,
 * в блоке «Страница» справа выберите «Шаблон → Главная АМИС»,
 * затем Настройки → Чтение → «На главной странице отображать: статическую страницу».
 *
 * Статичные тексты (hero, услуги, преимущества) лежат прямо здесь —
 * их правите в коде. Динамика (категории, товары, таблица, блог)
 * тянется из базы и обновляется сама.
 *
 * @package amis
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<!-- ===== HERO ===== -->
<section class="hero">
	<div class="wrap">
		<div class="hero-top">
			<div>
				<h1>Радиоизмерительные приборы для лабораторий и разработки</h1>
				<p class="hero-lead">
					Осциллографы, анализаторы спектра, генераторы и усилители мощности с поверкой,
					гарантией и внесением в Госреестр СИ. Отгружаем со склада в Москве, помогаем
					закрыть требования входного контроля.
				</p>
			</div>
			<div class="hero-aside">
				<p>
					Нужно сначала попробовать? Отправим прибор в вашу лабораторию на две недели,
					чтобы вы проверили его на реальных сигналах до покупки.
				</p>
				<a class="btn btn-ghost" href="<?php echo esc_url( home_url( '/demo/' ) ); ?>">
					Заказать прибор на тест
				</a>
			</div>
		</div>
	</div>

	<!-- Частотная шкала: данные из атрибута pa_frequency-range -->
	<div class="wrap">
		<div class="axis">
			<div class="axis-head">
				<h2>Подбор по рабочему диапазону</h2>
				<p>Выберите участок шкалы — покажем приборы, которые в нём работают</p>
			</div>
			<?php echo do_shortcode( '[amis_freq_axis selected="2"]' ); ?>
		</div>
	</div>
</section>

<!-- ===== ЦИФРЫ ===== -->
<section class="trust">
	<div class="wrap">
		<div>
			<b><?php echo esc_html( wp_count_posts( 'product' )->publish ); ?></b>
			<span>приборов в каталоге, из них 214 на складе</span>
		</div>
		<div>
			<b>24 ч</b>
			<span>от оплаты счёта до отгрузки складской позиции</span>
		</div>
		<div>
			<b>150<i>+</i></b>
			<span>лабораторий и КБ закупаются регулярно</span>
		</div>
		<div>
			<b>с 2022</b>
			<span>поставляем измерительную технику по всей России</span>
		</div>
	</div>
</section>

<!-- ===== КАТЕГОРИИ ===== -->
<section class="section">
	<div class="wrap">
		<div class="s-head">
			<div>
				<h2>Шесть направлений каталога</h2>
				<p>
					Под каждой категорией — ключевой параметр, по которому её обычно выбирают.
					Фильтры в каталоге настроены на те же характеристики.
				</p>
			</div>
			<a class="s-link" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>">Весь каталог</a>
		</div>
</div>
		<div class="wrap wrap--wide">
		<div class="cats">
			<?php foreach ( amis_get_top_categories( 6 ) as $cat ) : ?>
				<a class="cat" href="<?php echo esc_url( get_term_link( $cat ) ); ?>">
					<div class="cat-top">
						<h3><?php echo esc_html( $cat->name ); ?></h3>
					</div>

					<?php if ( $cat->description ) : ?>
						<p><?php echo esc_html( $cat->description ); ?></p>
					<?php endif; ?>

					<?php
					/**
					 * Ключевые параметры категории хранятся в мете термина
					 * amis_spec_label / amis_spec_value (через «|» для нескольких пар).
					 * Добавить поля к термину можно хуком product_cat_edit_form_fields —
					 * см. README, раздел «Шаг 6».
					 */
					$labels = array_filter( explode( '|', (string) get_term_meta( $cat->term_id, 'amis_spec_label', true ) ) );
					$values = array_filter( explode( '|', (string) get_term_meta( $cat->term_id, 'amis_spec_value', true ) ) );
					?>

					<?php if ( $labels ) : ?>
						<dl>
							<?php foreach ( $labels as $i => $label ) : ?>
								<dt><?php echo esc_html( $label ); ?></dt>
								<dd><?php echo esc_html( isset( $values[ $i ] ) ? $values[ $i ] : '—' ); ?></dd>
							<?php endforeach; ?>
						</dl>
					<?php endif; ?>

					<div class="cat-foot">
						<span class="num">
							<?php
							/* translators: %d — количество товаров в категории. */
							printf( esc_html__( '%d моделей', 'amis' ), (int) $cat->count );
							?>
						</span>
						<span><?php esc_html_e( 'Перейти', 'amis' ); ?></span>
					</div>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<!-- ===== ПАРАМЕТРИЧЕСКАЯ ТАБЛИЦА ===== -->
<?php $scopes = amis_get_scopes_by_bandwidth( 5 ); ?>
<?php if ( $scopes ) : ?>
<section class="section section--panel">
	<div class="wrap">
		<div class="s-head">
			<div>
				<h2>Осциллографы: сравнение по полосе</h2>
				<p>
					Самая частая задача — уложиться в полосу и не переплатить за лишние гигагерцы.
					Ниже модели, которые держим на складе, в порядке роста полосы пропускания.
				</p>
			</div>
			<a class="s-link" href="<?php echo esc_url( home_url( '/catalog/oscilloscopes/' ) ); ?>">
				Все модели с фильтрами
			</a>
		</div>
	</div>
		<div class="wrap wrap--wide">
		<div class="tbl-wrap">
			<table>
				<thead>
					<tr>
						<th><?php esc_html_e( 'Модель', 'amis' ); ?></th>
						<th><?php esc_html_e( 'Полоса', 'amis' ); ?></th>
						<th><?php esc_html_e( 'Каналов', 'amis' ); ?></th>
						<th><?php esc_html_e( 'Дискретизация', 'amis' ); ?></th>
						<th><?php esc_html_e( 'Память', 'amis' ); ?></th>
						<th><?php esc_html_e( 'Наличие', 'amis' ); ?></th>
						<th><?php esc_html_e( 'Цена', 'amis' ); ?></th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $scopes as $product ) : ?>
						<?php $stock = amis_stock_state( $product ); ?>
						<tr>
							<td class="model">
								<?php echo esc_html( $product->get_name() ); ?>
								<small><?php echo esc_html( $product->get_short_description() ); ?></small>
							</td>
							<td class="n"><?php echo esc_html( amis_attr( $product, 'bandwidth' ) ); ?></td>
							<td class="n"><?php echo esc_html( amis_attr( $product, 'channels' ) ); ?></td>
							<td class="n"><?php echo esc_html( amis_attr( $product, 'sample-rate' ) ); ?></td>
							<td class="n"><?php echo esc_html( amis_attr( $product, 'memory' ) ); ?></td>
							<td>
								<span class="<?php echo esc_attr( $stock['class'] ); ?>">
									<i></i> <?php echo esc_html( $stock['label'] ); ?>
								</span>
							</td>
							<td class="price">
								<?php if ( $product->get_price() ) : ?>
									<?php echo wp_kses_post( $product->get_price_html() ); ?>
									<small><?php esc_html_e( 'с НДС', 'amis' ); ?></small>
								<?php else : ?>
									<?php esc_html_e( 'По запросу', 'amis' ); ?>
									<small><?php esc_html_e( 'КП за 1 день', 'amis' ); ?></small>
								<?php endif; ?>
							</td>
							<td>
								<a class="btn btn-ghost btn-sm" href="<?php echo esc_url( get_permalink( $product->get_id() ) ); ?>">
									<?php esc_html_e( 'Открыть', 'amis' ); ?>
								</a>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<div class="tbl-foot">
			<span>Не нашли подходящую полосу? Инженер подберёт модель и пришлёт КП в течение рабочего дня.</span>
			<a class="btn btn-dark btn-sm" href="#contact">Задать параметры</a>
		</div>
			</div>
</section>
<?php endif; ?>

<!-- ===== ТОВАРЫ СО СКЛАДА ===== -->
<?php $instock = amis_get_instock_products( 4 ); ?>
<?php if ( $instock ) : ?>
<section class="section">
	<div class="wrap">
		<div class="s-head">
			<div>
				<h2>Со склада в Москве</h2>
				<p>Позиции, которые отгружаем в течение суток после оплаты счёта — с поверкой и полным комплектом документов.</p>
			</div>
			<a class="s-link" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>">Все складские позиции</a>
		</div>
	</div>
	<div class="wrap wrap--wide">
		<div class="grid4">
			<?php foreach ( $instock as $product ) : ?>
				<?php get_template_part( 'template-parts/product-card', null, array( 'product' => $product ) ); ?>
			<?php endforeach; ?>
		</div>
	</div>
</section>
<?php endif; ?>

<!-- ===== УСЛУГИ ===== -->
<section class="section section--panel">
	<div class="wrap">
		<div class="s-head">
			<div>
				<h2>Не только продаём</h2>
				<p>Четыре сервиса вокруг прибора — от теста до утилизации старого парка.</p>
			</div>
		</div>

		<div class="serv">
			<?php
			$services = array(
				array( 'Прибор на тест', 'Отправляем демо-образец в вашу лабораторию на 14 дней. Доставка за наш счёт.', '/demo/', 'Оставить заявку на демо' ),
				array( 'Поверка и калибровка', 'Первичная поверка в аккредитованном ЦСМ входит в стоимость. Ведём график МПИ по вашему парку.', '/verification/', 'Как проходит поверка' ),
				array( 'Аренда на проект', 'Сдаём в аренду от недели, арендные платежи засчитываем при выкупе.', '/rent/', 'Условия аренды' ),
				array( 'Trade-in старого парка', 'Оцениваем ваши приборы и засчитываем их стоимость в счёт новых.', '/trade-in/', 'Оценить приборы' ),
			);

			foreach ( $services as $i => $s ) :
				?>
				<div class="serv-i">
					<span class="n"><?php echo esc_html( sprintf( '%02d', $i + 1 ) ); ?></span>
					<div>
						<h3><?php echo esc_html( $s[0] ); ?></h3>
						<p><?php echo esc_html( $s[1] ); ?></p>
						<a href="<?php echo esc_url( home_url( $s[2] ) ); ?>"><?php echo esc_html( $s[3] ); ?></a>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<!-- ===== БРЕНДЫ ===== -->
<section class="section section--tight">
	<div class="wrap" style="padding-top:80px">
		<div class="s-head" style="margin-bottom:22px">
			<div><h2 style="font-size:23px">Производители в каталоге</h2></div>
		</div>
		<div class="brands">
			<?php
			$brands = array( 'АКИП', 'ПриСТ', 'RIGOL', 'SIGLENT', 'GW Instek', 'Keysight', 'Tektronix' );

			foreach ( $brands as $brand ) :
				?>
				<span class="brand"><?php echo esc_html( $brand ); ?></span>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<!-- ===== КОНТАКТ ===== -->
<section class="section" id="contact">
	<div class="wrap contact">
		<div>
			<h2>Пришлите параметры — соберём подборку</h2>
			<p>
				Опишите задачу в свободной форме или прикрепите ТЗ. Инженер предложит 2–3 модели
				с ценами, сроками и обоснованием. Обычно отвечаем в тот же рабочий день.
			</p>
			<p style="margin-top:26px">
				<a class="f-phone" href="tel:+74957700497" style="color:var(--ink);font-size:26px">+7 (495) 770-04-97</a>
				<a href="mailto:info@amisgr.ru" style="color:var(--gray)">info@amisgr.ru</a>
			</p>
		</div>

		<div class="form">
			<h3>Запрос коммерческого предложения</h3>
			<p>Заполните три поля — остальное уточним при ответе.</p>
			<?php
			/**
			 * Форму подключаем плагином (Contact Form 7 или WPForms) —
			 * самописную обработку POST лучше не делать: спам, nonce, GDPR.
			 * Замените ID на свой.
			 */
			echo do_shortcode( '[contact-form-7 id="5bd3250" title="Запрос КП"]' );
			?>
		</div>
	</div>
</section>

<!-- ===== БЛОГ ===== -->
<?php
$posts_query = new WP_Query( array(
	'post_type'           => 'post',
	'posts_per_page'      => 3,
	'ignore_sticky_posts' => true,
) );
?>
<?php if ( $posts_query->have_posts() ) : ?>
<section class="section section--panel">
	<div class="wrap">
		<div class="s-head">
			<div>
				<h2>База знаний</h2>
				<p>Пишем о том, что реально спрашивают в переписке.</p>
			</div>
			<a class="s-link" href="<?php echo esc_url( get_permalink( get_option( 'page_for_posts' ) ) ); ?>">Все материалы</a>
		</div>

		<div class="posts">
			<?php while ( $posts_query->have_posts() ) : ?>
				<?php $posts_query->the_post(); ?>
				<a class="post" href="<?php the_permalink(); ?>">
					<div class="meta"><?php echo esc_html( get_the_date( 'd.m.Y' ) ); ?></div>
					<h3><?php the_title(); ?></h3>
					<p><?php echo esc_html( get_the_excerpt() ); ?></p>
					<span class="rd"><?php esc_html_e( 'Читать статью', 'amis' ); ?></span>
				</a>
			<?php endwhile; ?>
		</div>
	</div>
</section>
<?php endif; ?>
<?php wp_reset_postdata(); // Обязательно после кастомного WP_Query. ?>

<!-- ===== SEO-ТЕКСТ ===== -->
<?php if ( get_the_content() ) : ?>
<section class="section">
	<div class="wrap seo">
		<div>
			<h2>Радиоизмерительные приборы в Москве</h2>
		</div>
		<div>
			<?php
			while ( have_posts() ) :
				the_post();
				the_content();
			endwhile;
			?>
		</div>
	</div>
</section>
<?php endif; ?>

<?php
get_footer();
