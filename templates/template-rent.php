<?php
/**
 * Template Name: Аренда приборов
 *
 * Условия аренды — подтверждённые пользователем данные, не выдумка:
 * минимальный срок неделя, арендные платежи засчитываются при выкупе
 * (эти два уже стоят на главной), ставка считается индивидуально,
 * залог не требуется, договор только с юрлицами и ИП, в стоимость
 * входят доставка в обе стороны и комплект аксессуаров.
 *
 * Чего здесь СОЗНАТЕЛЬНО нет, потому что не подтверждено: поверка внутри
 * арендной ставки, подменный прибор на время ремонта, аренда физлицам,
 * конкретные ставки в рублях или процентах. Появятся условия — дописать,
 * разметка уже готова.
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
		<span class="eyebrow"><?php esc_html_e( 'Аренда приборов', 'amis' ); ?></span>
		<h1><?php the_title(); ?></h1>
		<p class="page-lead">
			<?php esc_html_e( 'Сдаём радиоизмерительное оборудование из каталога — RIGOL, Siglent, Keysight, Tektronix, АКИП, ПриСТ — на срок от недели. Если после аренды решите оставить прибор себе, арендные платежи засчитаем в стоимость выкупа.', 'amis' ); ?>
		</p>
	</div>
</section>

<!-- Условия аренды -->
<section class="section">
	<div class="wrap">
		<div class="s-head">
			<div>
				<h2><?php esc_html_e( 'Условия аренды', 'amis' ); ?></h2>
				<p><?php esc_html_e( 'Работаем по договору аренды с юридическими лицами и ИП, оплата по безналичному расчёту.', 'amis' ); ?></p>
			</div>
		</div>

		<div class="warranty-grid">

			<div class="warranty-card">
				<span class="c-ico">
					<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
						<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>
					</svg>
				</span>
				<b><?php esc_html_e( 'От 1 недели', 'amis' ); ?></b>
				<span><?php esc_html_e( 'Срок аренды', 'amis' ); ?></span>
				<ul class="d-list">
					<li><?php esc_html_e( 'Минимальный срок — неделя, дальше любой под ваш проект', 'amis' ); ?></li>
					<li><?php esc_html_e( 'Срок и порядок возврата фиксируем в договоре', 'amis' ); ?></li>
					<li><?php esc_html_e( 'Продление согласовываем до окончания аренды', 'amis' ); ?></li>
				</ul>
			</div>

			<div class="warranty-card">
				<span class="c-ico">
					<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
						<line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
					</svg>
				</span>
				<b><?php esc_html_e( 'По запросу', 'amis' ); ?></b>
				<span><?php esc_html_e( 'Стоимость', 'amis' ); ?></span>
				<ul class="d-list">
					<li><?php esc_html_e( 'Ставка зависит от прибора и срока — считаем под задачу', 'amis' ); ?></li>
					<li><?php esc_html_e( 'Расчёт присылаем вместе с предложением по приборам', 'amis' ); ?></li>
					<li><?php esc_html_e( 'Безналичный расчёт, полный пакет закрывающих документов', 'amis' ); ?></li>
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
					<li><?php esc_html_e( 'Аренда для юридических лиц и ИП', 'amis' ); ?></li>
					<li><?php esc_html_e( 'Обеспечение — сам договор, залог вносить не нужно', 'amis' ); ?></li>
					<li><?php esc_html_e( 'Договор, счёт и акты готовим в день согласования', 'amis' ); ?></li>
				</ul>
			</div>

		</div>

		<div class="d-note">
			<span>
				<b><?php esc_html_e( 'Аренда с выкупом:', 'amis' ); ?></b>
				<?php esc_html_e( 'если по итогам работы прибор нужен насовсем — засчитываем уже внесённые арендные платежи в его стоимость. Взять на время и оставить себе выходит дешевле, чем арендовать и купить отдельно.', 'amis' ); ?>
			</span>
		</div>
	</div>
</section>

<!-- Что входит -->
<section class="section section--panel">
	<div class="wrap">
		<div class="s-head">
			<div>
				<h2><?php esc_html_e( 'Что входит в аренду', 'amis' ); ?></h2>
			</div>
		</div>

		<div class="cover-grid">

			<div class="cover-card">
				<h3><?php esc_html_e( 'Берём на себя', 'amis' ); ?></h3>
				<ul class="cover-list cover-list--yes">
					<?php
					$rent_included = array(
						__( 'Доставку прибора до вашего объекта и возврат обратно', 'amis' ),
						__( 'Комплект аксессуаров: пробники, кабели, блоки питания', 'amis' ),
						__( 'Проверку работоспособности прибора перед выдачей', 'amis' ),
						__( 'Документы для бухгалтерии: договор, счёт, акты', 'amis' ),
					);

					foreach ( $rent_included as $item ) :
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
					$rent_excluded = array(
						__( 'Эксплуатация прибора по инструкции производителя', 'amis' ),
						__( 'Сохранность прибора и комплекта на время аренды', 'amis' ),
						__( 'Возврат в срок и в полной комплектации', 'amis' ),
						__( 'Поверка под конкретные работы, если нужен допуск, — оформляем отдельно', 'amis' ),
					);

					foreach ( $rent_excluded as $item ) :
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

<!-- Кому подходит -->
<section class="section">
	<div class="wrap">
		<div class="s-head">
			<div>
				<h2><?php esc_html_e( 'Когда аренда выгоднее покупки', 'amis' ); ?></h2>
				<p><?php esc_html_e( 'Три ситуации, в которых к нам чаще всего приходят за арендой.', 'amis' ); ?></p>
			</div>
		</div>

		<div class="why-grid">

			<div class="why-card">
				<span class="c-ico">
					<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
						<path d="M12 2 2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5M2 12l10 5 10-5"/>
					</svg>
				</span>
				<h3><?php esc_html_e( 'Разовый проект', 'amis' ); ?></h3>
				<p><?php esc_html_e( 'Прибор нужен под один объект или контракт, а дальше будет простаивать на складе. Платите только за время работы.', 'amis' ); ?></p>
			</div>

			<div class="why-card">
				<span class="c-ico">
					<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
						<path d="M3 3v18h18"/><path d="M7 15l4-5 4 3 5-7"/>
					</svg>
				</span>
				<h3><?php esc_html_e( 'Пиковая загрузка', 'amis' ); ?></h3>
				<p><?php esc_html_e( 'Своего парка не хватает на все бригады или сразу несколько объектов. Аренда закрывает пик, не раздувая закупку.', 'amis' ); ?></p>
			</div>

			<div class="why-card">
				<span class="c-ico">
					<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
						<circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
					</svg>
				</span>
				<h3><?php esc_html_e( 'Проверка перед закупкой', 'amis' ); ?></h3>
				<p><?php esc_html_e( 'Прибор берётся в работу вдолгую, и нужно убедиться, что он подходит. Аренда идёт в зачёт, если решите его выкупить.', 'amis' ); ?></p>
			</div>

		</div>

		<div class="d-note">
			<span>
				<b><?php esc_html_e( 'Нужен не проект, а короткий тест?', 'amis' ); ?></b>
				<?php esc_html_e( 'Демо-образец на 14 дней мы отправляем бесплатно — это отдельная услуга:', 'amis' ); ?>
				<a href="<?php echo esc_url( home_url( '/demo/' ) ); ?>"><?php esc_html_e( 'прибор на тест', 'amis' ); ?></a>.
			</span>
		</div>
	</div>
</section>

<!-- Как оформить -->
<section class="section section--panel">
	<div class="wrap">
		<div class="s-head">
			<div>
				<h2><?php esc_html_e( 'Как взять прибор в аренду', 'amis' ); ?></h2>
				<p><?php esc_html_e( 'От заявки до прибора на объекте — четыре шага, подбор и логистика на нас.', 'amis' ); ?></p>
			</div>
		</div>

		<div class="steps">
			<?php
			$rent_steps = array(
				array(
					'title' => __( 'Заявка', 'amis' ),
					'text'  => __( 'Назовите прибор или опишите задачу, срок аренды и город — этого достаточно для расчёта.', 'amis' ),
				),
				array(
					'title' => __( 'Подбор и расчёт', 'amis' ),
					'text'  => __( 'Предлагаем подходящие приборы из наличия и присылаем ставку аренды на ваш срок.', 'amis' ),
				),
				array(
					'title' => __( 'Договор', 'amis' ),
					'text'  => __( 'Заключаем договор аренды и выставляем счёт. Залог не нужен, оплата безналичная.', 'amis' ),
				),
				array(
					'title' => __( 'Доставка', 'amis' ),
					'text'  => __( 'Привозим прибор с полным комплектом, по окончании аренды забираем его сами.', 'amis' ),
				),
			);

			foreach ( $rent_steps as $i => $step ) :
				?>
				<div class="step">
					<span class="n"><?php echo esc_html( sprintf( '%02d', $i + 1 ) ); ?></span>
					<h3><?php echo esc_html( $step['title'] ); ?></h3>
					<p><?php echo esc_html( $step['text'] ); ?></p>
				</div>
			<?php endforeach; ?>
		</div>

		<div class="d-note" style="margin-top:32px">
			<span>
				<b><?php esc_html_e( 'Наличие уточняйте:', 'amis' ); ?></b>
				<?php esc_html_e( 'арендный парк меняется, поэтому конкретную модель и срок её готовности подтверждаем при расчёте. Если нужного прибора нет свободным — предложим замену с теми же характеристиками.', 'amis' ); ?>
			</span>
		</div>
	</div>
</section>

<!-- CTA -->
<section class="section">
	<div class="wrap d-cta">
		<div>
			<h2><?php esc_html_e( 'Нужен прибор на проект?', 'amis' ); ?></h2>
			<p><?php esc_html_e( 'Расскажите о задаче и сроке — подберём прибор из наличия и посчитаем аренду.', 'amis' ); ?></p>
		</div>
		<div class="d-cta__actions">
			<?php echo amis_phone_link( 'free', 'btn btn-primary' ); // phpcs:ignore WordPress.Security.EscapeOutput -- экранирование внутри функции. ?>
			<a class="btn btn-ghost" href="<?php echo esc_url( home_url( '/contact/#write' ) ); ?>"><?php esc_html_e( 'Написать нам', 'amis' ); ?></a>
		</div>
	</div>
</section>

<?php
get_footer();
