<?php
/**
 * Страница «Доставка и оплата».
 *
 * Файл назван по слагу страницы: WordPress найдёт его автоматически
 * для страницы с адресом /payment/. Выбирать шаблон в админке не нужно.
 *
 * Цифры, сроки и названия перевозчиков ниже — заглушки для вёрстки,
 * их нужно заменить на реальные перед публикацией.
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
<section class="section section--head">
	<div class="wrap">
		<span class="eyebrow"><?php esc_html_e( 'Доставка по всей России', 'amis' ); ?></span>
		<h1><?php the_title(); ?></h1>
		<p class="page-lead">
			<?php esc_html_e( 'Быстрая и надёжная доставка измерительного оборудования в любой регион России. Работаем по счёту и с отсрочкой платежа для юрлиц.', 'amis' ); ?>
		</p>
	</div>
</section>

<!-- Способы доставки -->
<section class="section">
	<div class="wrap">
		<div class="s-head">
			<div>
				<h2><?php esc_html_e( 'Способы доставки', 'amis' ); ?></h2>
				<p><?php esc_html_e( 'Выберите удобный для вас способ получения заказа.', 'amis' ); ?></p>
			</div>
		</div>

		<div class="d-methods">

			<div class="d-card">
				<span class="c-ico">
					<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
						<rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/>
					</svg>
				</span>
				<h3><?php esc_html_e( 'Курьерская доставка', 'amis' ); ?></h3>
				<p><?php esc_html_e( 'Доставка до двери транспортной компанией — СДЭК, Деловые Линии или другой на ваш выбор.', 'amis' ); ?></p>
				<ul class="d-list">
					<li><?php esc_html_e( 'Москва и МО: 1–2 дня', 'amis' ); ?></li>
					<li><?php esc_html_e( 'Регионы РФ: 3–7 дней', 'amis' ); ?></li>
					<li><?php esc_html_e( 'Отслеживание посылки', 'amis' ); ?></li>
				</ul>
				<div class="d-price">
					<span><?php esc_html_e( 'Стоимость', 'amis' ); ?></span>
					<b><?php esc_html_e( 'от 1500 ₽', 'amis' ); ?></b>
				</div>
			</div>

			<div class="d-card">
				<span class="c-ico">
					<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
						<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>
					</svg>
				</span>
				<h3><?php esc_html_e( 'Водитель-экспедитор', 'amis' ); ?></h3>
				<p><?php esc_html_e( 'Доставим до вашей компании нашим сотрудником — для хрупких и крупногабаритных приборов.', 'amis' ); ?></p>
				<ul class="d-list">
					<li><?php esc_html_e( 'Доставка в пределах МКАД', 'amis' ); ?></li>
					<li><?php esc_html_e( 'Передача из рук в руки', 'amis' ); ?></li>
					<li><?php esc_html_e( 'Подпись документов на месте', 'amis' ); ?></li>
				</ul>
				<div class="d-price">
					<span><?php esc_html_e( 'Стоимость', 'amis' ); ?></span>
					<b><?php esc_html_e( 'Бесплатно от 100 000 ₽', 'amis' ); ?></b>
				</div>
			</div>

			<div class="d-card">
				<span class="c-ico">
					<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
						<path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>
					</svg>
				</span>
				<h3><?php esc_html_e( 'Самовывоз', 'amis' ); ?></h3>
				<p><?php esc_html_e( 'Заберите заказ с нашего склада в Москве в удобное время.', 'amis' ); ?></p>
				<ul class="d-list">
					<li><?php esc_html_e( 'Алтуфьевское шоссе, 48к1', 'amis' ); ?></li>
					<li><?php esc_html_e( 'Пн–Пт: 09:00–18:00', 'amis' ); ?></li>
					<li><?php esc_html_e( 'Желательна предварительная запись', 'amis' ); ?></li>
				</ul>
				<div class="d-price">
					<span><?php esc_html_e( 'Стоимость', 'amis' ); ?></span>
					<b><?php esc_html_e( 'Бесплатно', 'amis' ); ?></b>
				</div>
			</div>

		</div>
	</div>
</section>

<!-- Сроки доставки -->
<section class="section section--panel">
	<div class="wrap">
		<div class="s-head">
			<div>
				<h2><?php esc_html_e( 'Сроки доставки', 'amis' ); ?></h2>
				<p><?php esc_html_e( 'Ориентировочные сроки в зависимости от региона.', 'amis' ); ?></p>
			</div>
		</div>

		<div class="d-table-wrap">
			<table class="d-table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Регион', 'amis' ); ?></th>
						<th><?php esc_html_e( 'Срок доставки', 'amis' ); ?></th>
						<th><?php esc_html_e( 'Стоимость', 'amis' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php
					$rows = array(
						array( 'Москва и Московская область', '1–2 рабочих дня', 'от 1500 ₽' ),
						array( 'Санкт-Петербург и ЛО', '2–3 рабочих дня', 'от 2000 ₽' ),
						array( 'Центральный федеральный округ', '2–4 рабочих дня', 'от 2000 ₽' ),
						array( 'Северо-Западный ФО', '3–5 рабочих дней', 'от 2500 ₽' ),
						array( 'Южный и Северо-Кавказский ФО', '4–6 рабочих дней', 'от 3000 ₽' ),
						array( 'Приволжский ФО', '3–5 рабочих дней', 'от 3000 ₽' ),
						array( 'Уральский ФО', '4–6 рабочих дней', 'от 3500 ₽' ),
						array( 'Сибирский ФО', '5–8 рабочих дней', 'от 4000 ₽' ),
						array( 'Дальневосточный ФО', '7–10 рабочих дней', 'от 5000 ₽' ),
					);

					foreach ( $rows as $row ) :
						?>
						<tr>
							<td><?php echo esc_html( $row[0] ); ?></td>
							<td><b><?php echo esc_html( $row[1] ); ?></b></td>
							<td><?php echo esc_html( $row[2] ); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>

		<div class="d-note">
			<b><?php esc_html_e( 'Обратите внимание:', 'amis' ); ?></b>
			<?php esc_html_e( 'точная стоимость и срок доставки рассчитываются индивидуально для каждого заказа в зависимости от веса, габаритов и удалённости региона. Менеджер свяжется с вами после оформления заказа.', 'amis' ); ?>
		</div>
	</div>
</section>

<!-- Способы оплаты -->
<section class="section">
	<div class="wrap">
		<div class="s-head">
			<div>
				<h2><?php esc_html_e( 'Способы оплаты', 'amis' ); ?></h2>
				<p><?php esc_html_e( 'Удобные способы оплаты для юридических лиц.', 'amis' ); ?></p>
			</div>
		</div>

		<div class="d-methods d-methods--pay">

			<div class="d-card d-card--center">
				<span class="c-ico">
					<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
						<rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/>
					</svg>
				</span>
				<h3><?php esc_html_e( 'Безналичный расчёт', 'amis' ); ?></h3>
				<p><?php esc_html_e( 'Оплата по счёту для юридических лиц и ИП, с НДС 22%.', 'amis' ); ?></p>
			</div>

			<div class="d-card d-card--center">
				<span class="c-ico">
					<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
						<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="9" y1="13" x2="15" y2="13"/><line x1="9" y1="17" x2="15" y2="17"/>
					</svg>
				</span>
				<h3><?php esc_html_e( 'Электронный документооборот', 'amis' ); ?></h3>
				<p><?php esc_html_e( 'Работаем через Диадок, СБИС и Контур.', 'amis' ); ?></p>
			</div>

			<div class="d-card d-card--center">
				<span class="c-ico">
					<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
						<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
					</svg>
				</span>
				<h3><?php esc_html_e( 'Отсрочка платежа', 'amis' ); ?></h3>
				<p><?php esc_html_e( 'Возможна для постоянных клиентов по договору.', 'amis' ); ?></p>
			</div>

		</div>
	</div>
</section>

<!-- Документы с заказом -->
<section class="section section--panel">
	<div class="wrap">
		<div class="s-head">
			<div>
				<h2><?php esc_html_e( 'Документы с заказом', 'amis' ); ?></h2>
				<p><?php esc_html_e( 'Полный пакет документов для бухгалтерии.', 'amis' ); ?></p>
			</div>
		</div>

		<div class="d-docs">
			<?php
			$docs = array(
				array( 'Счёт на оплату', 'С реквизитами и назначением платежа' ),
				array( 'Договор поставки', 'При необходимости, заключаем до отгрузки' ),
				array( 'Товарная накладная', 'УПД для бухгалтерии' ),
				array( 'Счёт-фактура', 'Для зачёта НДС при необходимости' ),
				array( 'Сертификаты', 'Сертификаты соответствия и декларации по запросу' ),
				array( 'Паспорт изделия', 'Технический паспорт на прибор по запросу' ),
			);

			foreach ( $docs as $doc ) :
				?>
				<div class="d-doc">
					<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
						<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/>
					</svg>
					<div>
						<b><?php echo esc_html( $doc[0] ); ?></b>
						<span><?php echo esc_html( $doc[1] ); ?></span>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<!-- CTA -->
<section class="section">
	<div class="wrap d-cta">
		<div>
			<h2><?php esc_html_e( 'Остались вопросы по доставке или оплате?', 'amis' ); ?></h2>
			<p><?php esc_html_e( 'Менеджер по закупкам поможет с документами, тендерами и графиком отгрузки.', 'amis' ); ?></p>
		</div>
		<div class="d-cta__actions">
			<?php echo amis_phone_link( 'free', 'btn btn-primary' ); // phpcs:ignore WordPress.Security.EscapeOutput -- экранирование внутри функции. ?>
			<a class="btn btn-ghost" href="<?php echo esc_url( home_url( '/contact/#write' ) ); ?>"><?php esc_html_e( 'Написать нам', 'amis' ); ?></a>
		</div>
	</div>
</section>

<?php
get_footer();
