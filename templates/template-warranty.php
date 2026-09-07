<?php
/**
 * Template Name: Гарантия и сервис
 *
 * Сроки гарантии (3 года / 1 год / бессрочный постгарантийный сервис) и
 * адрес сервисного центра — подтверждённые пользователем данные, реального
 * количества дней на ремонт нет, поэтому в шаге «Диагностика» срок не
 * назван — сообщается индивидуально после осмотра прибора.
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
		<span class="eyebrow"><?php esc_html_e( 'Гарантия и сервис', 'amis' ); ?></span>
		<h1><?php the_title(); ?></h1>
		<p class="page-lead">
			<?php esc_html_e( 'ООО «АМИС ГРУПП» — гарантийный сервис по всем брендам каталога: RIGOL, Siglent, Keysight, Tektronix, АКИП и ПриСТ. Обеспечиваем гарантийную и постгарантийную поддержку всего поставленного оборудования.', 'amis' ); ?>
		</p>
	</div>
</section>

<!-- Сроки гарантии -->
<section class="section">
	<div class="wrap">
		<div class="s-head">
			<div>
				<h2><?php esc_html_e( 'Сроки гарантии', 'amis' ); ?></h2>
				<p><?php esc_html_e( 'Срок зависит от типа оборудования и указывается в гарантийном талоне при отгрузке.', 'amis' ); ?></p>
			</div>
		</div>

		<div class="warranty-grid">

			<div class="warranty-card">
				<span class="c-ico">
					<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
						<rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/>
					</svg>
				</span>
				<b>3 <?php esc_html_e( 'года', 'amis' ); ?></b>
				<span><?php esc_html_e( 'Основное оборудование', 'amis' ); ?></span>
				<ul class="d-list">
					<li><?php esc_html_e( 'Осциллографы, анализаторы спектра, генераторы, усилители мощности', 'amis' ); ?></li>
					<li><?php esc_html_e( 'Бесплатный ремонт', 'amis' ); ?></li>
					<li><?php esc_html_e( 'Замена неисправных узлов', 'amis' ); ?></li>
				</ul>
			</div>

			<div class="warranty-card">
				<span class="c-ico">
					<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
						<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>
					</svg>
				</span>
				<b>1 <?php esc_html_e( 'год', 'amis' ); ?></b>
				<span><?php esc_html_e( 'Аксессуары и принадлежности', 'amis' ); ?></span>
				<ul class="d-list">
					<li><?php esc_html_e( 'Пробники, щупы, кабели, блоки питания', 'amis' ); ?></li>
					<li><?php esc_html_e( 'Замена при заводском браке', 'amis' ); ?></li>
					<li><?php esc_html_e( 'Проверка перед заменой', 'amis' ); ?></li>
				</ul>
			</div>

			<div class="warranty-card">
				<span class="c-ico">
					<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
						<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
					</svg>
				</span>
				<b><?php esc_html_e( 'Бессрочно', 'amis' ); ?></b>
				<span><?php esc_html_e( 'Постгарантийный сервис', 'amis' ); ?></span>
				<ul class="d-list">
					<li><?php esc_html_e( 'Платный ремонт после окончания гарантии', 'amis' ); ?></li>
					<li><?php esc_html_e( 'Поверка и калибровка', 'amis' ); ?></li>
					<li><?php esc_html_e( 'Поставка запчастей', 'amis' ); ?></li>
				</ul>
			</div>

		</div>
	</div>
</section>

<!-- Что покрывает гарантия -->
<section class="section section--panel">
	<div class="wrap">
		<div class="s-head">
			<div>
				<h2><?php esc_html_e( 'Что покрывает гарантия', 'amis' ); ?></h2>
			</div>
		</div>

		<div class="cover-grid">

			<div class="cover-card">
				<h3><?php esc_html_e( 'Гарантия действует', 'amis' ); ?></h3>
				<ul class="cover-list cover-list--yes">
					<?php
					$covered = array(
						__( 'Производственный брак и дефекты', 'amis' ),
						__( 'Неисправности комплектующих от производителя', 'amis' ),
						__( 'Проблемы с программным обеспечением прибора', 'amis' ),
						__( 'Отклонения от заявленных технических характеристик', 'amis' ),
					);

					foreach ( $covered as $item ) :
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
				<h3><?php esc_html_e( 'Гарантия не действует', 'amis' ); ?></h3>
				<ul class="cover-list cover-list--no">
					<?php
					$not_covered = array(
						__( 'Механические повреждения (удары, падения)', 'amis' ),
						__( 'Попадание жидкости внутрь корпуса', 'amis' ),
						__( 'Нарушение условий эксплуатации', 'amis' ),
						__( 'Самостоятельный ремонт или вскрытие прибора', 'amis' ),
						__( 'Использование неоригинальных аксессуаров, повлёкшее поломку', 'amis' ),
					);

					foreach ( $not_covered as $item ) :
						?>
						<li>
							<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
								<circle cx="12" cy="12" r="10"/><line x1="9" y1="9" x2="15" y2="15"/><line x1="15" y1="9" x2="9" y2="15"/>
							</svg>
							<?php echo esc_html( $item ); ?>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>

		</div>
	</div>
</section>

<!-- Как оформить гарантийный случай -->
<section class="section">
	<div class="wrap">
		<div class="s-head">
			<div>
				<h2><?php esc_html_e( 'Как оформить гарантийный случай', 'amis' ); ?></h2>
				<p><?php esc_html_e( 'Ваше время участия — два звонка и передача прибора нам или транспортной компании.', 'amis' ); ?></p>
			</div>
		</div>

		<div class="steps">
			<?php
			$steps = array(
				array(
					'title' => __( 'Свяжитесь с нами', 'amis' ),
					'text'  => __( 'Опишите неисправность по телефону или почте — подскажем, гарантийный ли это случай, и дальнейшие шаги.', 'amis' ),
				),
				array(
					'title' => __( 'Диагностика', 'amis' ),
					'text'  => __( 'Определяем причину удалённо или после осмотра прибора инженером — тогда же называем срок ремонта.', 'amis' ),
				),
				array(
					'title' => __( 'Передача прибора', 'amis' ),
					'text'  => __( 'Пришлите прибор нам сами или транспортной компанией, приложите документы на покупку.', 'amis' ),
				),
				array(
					'title' => __( 'Ремонт и возврат', 'amis' ),
					'text'  => __( 'Ремонтируем или заменяем прибор и отправляем обратно по указанному адресу.', 'amis' ),
				),
			);

			foreach ( $steps as $i => $step ) :
				?>
				<div class="step">
					<span class="n"><?php echo esc_html( sprintf( '%02d', $i + 1 ) ); ?></span>
					<h3><?php echo esc_html( $step['title'] ); ?></h3>
					<p><?php echo esc_html( $step['text'] ); ?></p>
				</div>
			<?php endforeach; ?>
		</div>

		<div class="d-note" style="margin-top:32px">
			<b><?php esc_html_e( 'Обратите внимание:', 'amis' ); ?></b>
			<?php esc_html_e( 'гарантийный случай подтверждается после осмотра прибора инженером. Точный срок ремонта зависит от того, нужны ли запчасти от производителя.', 'amis' ); ?>
		</div>
	</div>
</section>

<!-- Сервисный центр -->
<section class="section section--panel">
	<div class="wrap">
		<div class="s-head">
			<div>
				<h2><?php esc_html_e( 'Сервисный центр', 'amis' ); ?></h2>
				<p><?php esc_html_e( 'Приём, диагностика и ремонт — по адресу нашего офиса и склада в Москве.', 'amis' ); ?></p>
			</div>
		</div>

		<div class="service-grid">

			<div>
				<h3><?php esc_html_e( 'Почему выбирают наш сервис', 'amis' ); ?></h3>
				<ul class="cover-list cover-list--yes">
					<?php
					$why_service = array(
						__( 'Работаем с оборудованием RIGOL, Siglent, Keysight, Tektronix, АКИП и ПриСТ', 'amis' ),
						__( 'Используем сертифицированные комплектующие производителей', 'amis' ),
						__( 'Собственные стенды для диагностики и поверки', 'amis' ),
						__( 'Называем срок ремонта сразу после диагностики, без ожидания «на потом»', 'amis' ),
					);

					foreach ( $why_service as $item ) :
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

			<div>
				<h3><?php esc_html_e( 'Контакты сервисного центра', 'amis' ); ?></h3>
				<div class="contact-cards" style="grid-template-columns:1fr">

					<div class="c-card">
						<span class="c-ico">
							<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
								<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
							</svg>
						</span>
						<div>
							<span class="c-label"><?php esc_html_e( 'Телефон', 'amis' ); ?></span>
							<?php echo amis_phone_link( 'msk', 'c-value c-value--mono' ); // phpcs:ignore WordPress.Security.EscapeOutput -- экранирование внутри функции. ?>
						</div>
					</div>

					<div class="c-card">
						<span class="c-ico">
							<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
								<path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/>
							</svg>
						</span>
						<div>
							<span class="c-label"><?php esc_html_e( 'Почта', 'amis' ); ?></span>
							<a class="c-value" href="mailto:<?php echo esc_attr( amis_company_email() ); ?>"><?php echo esc_html( amis_company_email() ); ?></a>
						</div>
					</div>

					<div class="c-card">
						<span class="c-ico">
							<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
								<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>
							</svg>
						</span>
						<div>
							<span class="c-label"><?php esc_html_e( 'Адрес', 'amis' ); ?></span>
							<span class="c-value c-value--sm">127566, Москва,<br>Алтуфьевское шоссе, 48к1</span>
						</div>
					</div>

					<div class="c-card">
						<span class="c-ico">
							<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
								<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
							</svg>
						</span>
						<div>
							<span class="c-label"><?php esc_html_e( 'Режим работы', 'amis' ); ?></span>
							<span class="c-value c-value--sm">
								<strong><?php esc_html_e( 'Пн–Пт:', 'amis' ); ?></strong> 09:00–18:00<br>
								<strong><?php esc_html_e( 'Сб–Вс:', 'amis' ); ?></strong> <?php esc_html_e( 'выходной', 'amis' ); ?>
							</span>
						</div>
					</div>

				</div>
			</div>

		</div>
	</div>
</section>

<!-- CTA -->
<section class="section">
	<div class="wrap d-cta">
		<div>
			<h2><?php esc_html_e( 'Возникла неисправность?', 'amis' ); ?></h2>
			<p><?php esc_html_e( 'Опишите проблему — подскажем, гарантийный ли это случай, и что делать дальше.', 'amis' ); ?></p>
		</div>
		<div class="d-cta__actions">
			<?php echo amis_phone_link( 'free', 'btn btn-primary' ); // phpcs:ignore WordPress.Security.EscapeOutput -- экранирование внутри функции. ?>
			<a class="btn btn-ghost" href="<?php echo esc_url( home_url( '/contact/#write' ) ); ?>"><?php esc_html_e( 'Написать нам', 'amis' ); ?></a>
		</div>
	</div>
</section>

<?php
get_footer();
