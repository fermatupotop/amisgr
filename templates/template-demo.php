<?php
/**
 * Template Name: Прибор на тест
 *
 * Условия — подтверждённые пользователем данные, не выдумка: срок теста
 * 14 дней, доставка за наш счёт, только юридическим лицам и ИП, залог не
 * нужен — оформляется простым договором. По итогам теста прибор либо
 * возвращается, либо покупается отдельным заказом — в отличие от аренды
 * (templates/template-rent.php), стоимость теста в покупку НЕ засчитывается,
 * это бесплатная отдельная услуга.
 *
 * Демо-фонд ограничен — точная модель уточняется по запросу, поэтому
 * страница не перечисляет конкретные приборы. На самой карточке товара
 * кнопка «Взять на тест» показывается только если включён чекбокс
 * «Доступен на тест» в «Данные АМИС» (inc/product-fields.php,
 * _amis_demo_available) — эта страница ссылается туда же, а не дублирует.
 *
 * @package amis
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<nav class="crumbs">
	<div class="wrap">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Главная', 'amis' ); ?></a>
		<span>/</span>
		<span><?php the_title(); ?></span>
	</div>
</nav>

<!-- Шапка страницы -->
<section class="section section--head section--dark">
	<div class="wrap">
		<span class="eyebrow"><?php esc_html_e( 'Демонстрация оборудования', 'amis' ); ?></span>
		<h1><?php the_title(); ?></h1>
		<p class="page-lead">
			<?php esc_html_e( 'Отправляем демо-образец в вашу лабораторию на 14 дней — оцените прибор на реальных задачах перед покупкой. Доставка за наш счёт.', 'amis' ); ?>
		</p>
	</div>
</section>

<!-- Условия -->
<section class="section">
	<div class="wrap">
		<div class="s-head">
			<div>
				<h2><?php esc_html_e( 'Условия теста', 'amis' ); ?></h2>
				<p><?php esc_html_e( 'Работаем с юридическими лицами и ИП, оформляем простым договором.', 'amis' ); ?></p>
			</div>
		</div>

		<div class="warranty-grid">

			<div class="warranty-card">
				<span class="c-ico">
					<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
						<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>
					</svg>
				</span>
				<b><?php esc_html_e( '14 дней', 'amis' ); ?></b>
				<span><?php esc_html_e( 'Срок теста', 'amis' ); ?></span>
				<ul class="d-list">
					<li><?php esc_html_e( 'Фиксированный срок — достаточно, чтобы прогнать прибор на реальных задачах', 'amis' ); ?></li>
					<li><?php esc_html_e( 'Отправляем и забираем сами, даты фиксируем в договоре', 'amis' ); ?></li>
					<li><?php esc_html_e( 'Нужно чуть больше времени — обсудим при заявке', 'amis' ); ?></li>
				</ul>
			</div>

			<div class="warranty-card">
				<span class="c-ico">
					<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
						<line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
					</svg>
				</span>
				<b><?php esc_html_e( 'Бесплатно', 'amis' ); ?></b>
				<span><?php esc_html_e( 'Стоимость', 'amis' ); ?></span>
				<ul class="d-list">
					<li><?php esc_html_e( 'Демо-образец предоставляется без арендной платы', 'amis' ); ?></li>
					<li><?php esc_html_e( 'Доставка за наш счёт', 'amis' ); ?></li>
					<li><?php esc_html_e( 'Отдельная услуга: в отличие от аренды, платежи в счёт покупки не идут', 'amis' ); ?></li>
				</ul>
			</div>

			<div class="warranty-card">
				<span class="c-ico">
					<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
						<path d="M20 6H4a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2z"/><path d="M16 6V4a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/>
					</svg>
				</span>
				<b><?php esc_html_e( 'Без залога', 'amis' ); ?></b>
				<span><?php esc_html_e( 'Оформление', 'amis' ); ?></span>
				<ul class="d-list">
					<li><?php esc_html_e( 'Тест — только для юридических лиц и ИП', 'amis' ); ?></li>
					<li><?php esc_html_e( 'Обеспечение — сам договор, залог вносить не нужно', 'amis' ); ?></li>
					<li><?php esc_html_e( 'После теста: возвращаете прибор либо покупаете отдельным заказом', 'amis' ); ?></li>
				</ul>
			</div>

		</div>

		<div class="d-note">
			<span>
				<b><?php esc_html_e( 'Демо-фонд ограничен:', 'amis' ); ?></b>
				<?php esc_html_e( 'на тест доступна только часть моделей каталога. Точное название прибора и его доступность на нужные даты уточняем по запросу — оставьте заявку, ответим в тот же рабочий день.', 'amis' ); ?>
			</span>
		</div>
	</div>
</section>

<!-- Что входит -->
<section class="section section--panel">
	<div class="wrap">
		<div class="s-head">
			<div>
				<h2><?php esc_html_e( 'Что входит в тест', 'amis' ); ?></h2>
			</div>
		</div>

		<div class="cover-grid">

			<div class="cover-card">
				<h3><?php esc_html_e( 'Берём на себя', 'amis' ); ?></h3>
				<ul class="cover-list cover-list--yes">
					<?php
					$demo_included = array(
						__( 'Доставку демо-образца и его возврат — за наш счёт', 'amis' ),
						__( 'Проверку работоспособности прибора перед отправкой', 'amis' ),
						__( 'Договор на передачу оборудования во временное пользование', 'amis' ),
					);

					foreach ( $demo_included as $item ) :
						?>
						<li>
							<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
								<circle cx="12" cy="12" r="10"/><polyline points="8 12 11 15 16 9"/>
							</svg>
							<?php echo esc_html( $item ); ?>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>

			<div class="cover-card">
				<h3><?php esc_html_e( 'Остаётся на вашей стороне', 'amis' ); ?></h3>
				<ul class="cover-list cover-list--no">
					<?php
					$demo_excluded = array(
						__( 'Эксплуатация прибора по инструкции производителя', 'amis' ),
						__( 'Сохранность прибора на время теста', 'amis' ),
						__( 'Возврат в срок и в полной комплектации, если не выкупаете', 'amis' ),
					);

					foreach ( $demo_excluded as $item ) :
						?>
						<li>
							<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
								<circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
							</svg>
							<?php echo esc_html( $item ); ?>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>

		</div>
	</div>
</section>

<!-- Как оформить -->
<section class="section">
	<div class="wrap">
		<div class="s-head">
			<div>
				<h2><?php esc_html_e( 'Как взять прибор на тест', 'amis' ); ?></h2>
				<p><?php esc_html_e( 'От заявки до прибора в лаборатории — три шага.', 'amis' ); ?></p>
			</div>
		</div>

		<div class="steps">
			<?php
			$demo_steps = array(
				array(
					'title' => __( 'Заявка', 'amis' ),
					'text'  => __( 'Назовите прибор или задачу, под которую нужен тест, — уточним, доступна ли модель на нужные даты.', 'amis' ),
				),
				array(
					'title' => __( 'Договор', 'amis' ),
					'text'  => __( 'Оформляем передачу оборудования во временное пользование, без залога.', 'amis' ),
				),
				array(
					'title' => __( 'Доставка и тест', 'amis' ),
					'text'  => __( 'Привозим прибор за свой счёт, через 14 дней забираем — либо оформляем покупку отдельным заказом.', 'amis' ),
				),
			);

			foreach ( $demo_steps as $i => $step ) :
				?>
				<div class="step">
					<span class="n"><?php echo esc_html( sprintf( '%02d', $i + 1 ) ); ?></span>
					<h3><?php echo esc_html( $step['title'] ); ?></h3>
					<p><?php echo esc_html( $step['text'] ); ?></p>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<!-- CTA -->
<section class="section section--panel">
	<div class="wrap d-cta">
		<div>
			<h2><?php esc_html_e( 'Хотите протестировать прибор перед покупкой?', 'amis' ); ?></h2>
			<p><?php esc_html_e( 'Расскажите, какой прибор и для какой задачи — уточним доступность на тест.', 'amis' ); ?></p>
		</div>
		<div class="d-cta__actions">
			<?php echo amis_phone_link( 'free', 'btn btn-primary' ); // phpcs:ignore WordPress.Security.EscapeOutput -- экранирование внутри функции. ?>
			<a class="btn btn-ghost" href="<?php echo esc_url( home_url( '/contact/#write' ) ); ?>"><?php esc_html_e( 'Написать нам', 'amis' ); ?></a>
		</div>
	</div>
</section>

<?php
get_footer();
