<?php
/**
 * Template Name: Поверка и калибровка
 *
 * Подтверждённые пользователем условия (не выдумка, использовать как есть):
 * поверку выполняет АККРЕДИТОВАННЫЙ ЦСМ-ПАРТНЁР, не мы сами — мы организуем
 * и сопровождаем; первичная поверка входит в стоимость поставки; типовой
 * срок 5–10 рабочих дней; на выходе запись во ФГИС «Аршин», бумажное
 * свидетельство и знак поверки; принимаем ТОЛЬКО оборудование, поставленное
 * нами.
 *
 * Чего здесь СОЗНАТЕЛЬНО нет, потому что не подтверждено: собственная
 * аккредитация на поверку, ведение графика МПИ по парку клиента (было на
 * главной, оттуда убрано по решению пользователя), вывоз и возврат прибора
 * нашими силами, состав документов по калибровке и её стоимость.
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
		<span class="eyebrow"><?php esc_html_e( 'Поверка и калибровка', 'amis' ); ?></span>
		<h1><?php the_title(); ?></h1>
		<p class="page-lead">
			<?php esc_html_e( 'Организуем поверку оборудования, поставленного нами, в аккредитованном центре стандартизации и метрологии. Первичная поверка входит в стоимость поставки — прибор приезжает к вам готовым к работе, со сведениями во ФГИС «Аршин».', 'amis' ); ?>
		</p>
	</div>
</section>

<!-- Условия -->
<section class="section">
	<div class="wrap">
		<div class="s-head">
			<div>
				<h2><?php esc_html_e( 'Как это устроено', 'amis' ); ?></h2>
				<p><?php esc_html_e( 'Поверку выполняет аккредитованный ЦСМ, мы берём на себя организацию, документы и общение с центром.', 'amis' ); ?></p>
			</div>
		</div>

		<div class="warranty-grid">

			<div class="warranty-card">
				<span class="c-ico">
					<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
						<path d="M20.59 13.41 13.42 20.6a2 2 0 0 1-2.83 0l-7.17-7.18a2 2 0 0 1-.59-1.41V4a2 2 0 0 1 2-2h8.01a2 2 0 0 1 1.41.59l6.34 6.34a2 2 0 0 1 0 2.83z"/><line x1="7" y1="7" x2="7.01" y2="7"/>
					</svg>
				</span>
				<b><?php esc_html_e( 'В цене прибора', 'amis' ); ?></b>
				<span><?php esc_html_e( 'Первичная поверка', 'amis' ); ?></span>
				<ul class="d-list">
					<li><?php esc_html_e( 'При покупке у нас входит в стоимость поставки', 'amis' ); ?></li>
					<li><?php esc_html_e( 'Отдельно оплачивать её не нужно', 'amis' ); ?></li>
					<li><?php esc_html_e( 'Прибор отгружаем уже с документами о поверке', 'amis' ); ?></li>
				</ul>
			</div>

			<div class="warranty-card">
				<span class="c-ico">
					<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
						<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
					</svg>
				</span>
				<b>5–10 <?php esc_html_e( 'дней', 'amis' ); ?></b>
				<span><?php esc_html_e( 'Типовой срок', 'amis' ); ?></span>
				<ul class="d-list">
					<li><?php esc_html_e( 'Рабочих дней с момента передачи прибора в ЦСМ', 'amis' ); ?></li>
					<li><?php esc_html_e( 'Точный срок подтверждаем при оформлении заявки', 'amis' ); ?></li>
					<li><?php esc_html_e( 'Зависит от типа прибора и загрузки центра', 'amis' ); ?></li>
				</ul>
			</div>

			<div class="warranty-card">
				<span class="c-ico">
					<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
						<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/>
					</svg>
				</span>
				<b><?php esc_html_e( 'ЦСМ', 'amis' ); ?></b>
				<span><?php esc_html_e( 'Кто поверяет', 'amis' ); ?></span>
				<ul class="d-list">
					<li><?php esc_html_e( 'Аккредитованный центр стандартизации и метрологии', 'amis' ); ?></li>
					<li><?php esc_html_e( 'Подбираем центр с нужной областью аккредитации', 'amis' ); ?></li>
					<li><?php esc_html_e( 'Заявку, передачу прибора и документы ведём мы', 'amis' ); ?></li>
				</ul>
			</div>

		</div>

		<div class="d-note">
			<span>
				<b><?php esc_html_e( 'Важно:', 'amis' ); ?></b>
				<?php esc_html_e( 'на поверку принимаем оборудование, поставленное нами, — по своим поставкам мы знаем комплектацию, историю прибора и условия гарантии. Если прибор куплен в другом месте, напишите нам: подскажем, куда обратиться.', 'amis' ); ?>
			</span>
		</div>
	</div>
</section>

<!-- Документы -->
<section class="section section--panel">
	<div class="wrap">
		<div class="s-head">
			<div>
				<h2><?php esc_html_e( 'Что вы получаете', 'amis' ); ?></h2>
				<p><?php esc_html_e( 'Результат поверки — не бумажка «для галочки», а подтверждённый статус прибора как средства измерений.', 'amis' ); ?></p>
			</div>
		</div>

		<div class="d-docs">

			<div class="d-doc">
				<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
					<rect x="2" y="4" width="20" height="16" rx="2"/><path d="M2 9h20"/><path d="M7 14h5"/>
				</svg>
				<div>
					<b><?php esc_html_e( 'Запись во ФГИС «Аршин»', 'amis' ); ?></b>
					<span><?php esc_html_e( 'Сведения о поверке в государственном реестре — основной юридический результат', 'amis' ); ?></span>
				</div>
			</div>

			<div class="d-doc">
				<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
					<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="8" y1="13" x2="16" y2="13"/><line x1="8" y1="17" x2="13" y2="17"/>
				</svg>
				<div>
					<b><?php esc_html_e( 'Свидетельство о поверке', 'amis' ); ?></b>
					<span><?php esc_html_e( 'Бумажный документ — для службы качества, СМК и требований заказчика', 'amis' ); ?></span>
				</div>
			</div>

			<div class="d-doc">
				<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
					<circle cx="12" cy="8" r="6"/><path d="M8.21 13.89 7 23l5-3 5 3-1.21-9.12"/>
				</svg>
				<div>
					<b><?php esc_html_e( 'Знак поверки', 'amis' ); ?></b>
					<span><?php esc_html_e( 'Наклейка или клеймо на приборе либо отметка в паспорте', 'amis' ); ?></span>
				</div>
			</div>

		</div>
	</div>
</section>

<!-- Поверка и калибровка -->
<section class="section">
	<div class="wrap">
		<div class="s-head">
			<div>
				<h2><?php esc_html_e( 'Поверка и калибровка — не одно и то же', 'amis' ); ?></h2>
				<p><?php esc_html_e( 'Их часто путают, а нужны они в разных ситуациях. Если не уверены, что требуется вам, — спросите, разберём по вашей задаче.', 'amis' ); ?></p>
			</div>
		</div>

		<div class="cover-grid">

			<div class="cover-card">
				<h3><?php esc_html_e( 'Поверка', 'amis' ); ?></h3>
				<p><?php esc_html_e( 'Обязательная процедура для средств измерений, которые применяются в сферах государственного регулирования: приёмка работ, испытания, метрологический контроль. Отвечает на один вопрос — соответствует прибор установленным требованиям или нет.', 'amis' ); ?></p>
				<ul class="cover-list cover-list--yes">
					<?php
					$ver_points = array(
						__( 'Проводит организация, аккредитованная на поверку', 'amis' ),
						__( 'Результат — сведения во ФГИС «Аршин»', 'amis' ),
						__( 'Действует до следующего межповерочного интервала', 'amis' ),
					);

					foreach ( $ver_points as $item ) :
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
				<h3><?php esc_html_e( 'Калибровка', 'amis' ); ?></h3>
				<p><?php esc_html_e( 'Добровольная процедура. Показывает не «годен / не годен», а реальные отклонения прибора от эталона — с конкретными числами. Нужна там, где важна точность самих измерений: разработка, лаборатория, внутренний контроль качества.', 'amis' ); ?></p>
				<ul class="cover-list cover-list--yes">
					<?php
					$cal_points = array(
						__( 'Не заменяет поверку там, где поверка обязательна', 'amis' ),
						__( 'Показывает фактические погрешности прибора', 'amis' ),
						__( 'Периодичность вы определяете сами', 'amis' ),
					);

					foreach ( $cal_points as $item ) :
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

		</div>

		<div class="d-note">
			<span>
				<b><?php esc_html_e( 'Нужна калибровка?', 'amis' ); ?></b>
				<?php esc_html_e( 'Организуем её по запросу для поставленных нами приборов — состав работ, документы и стоимость согласуем с лабораторией под вашу задачу.', 'amis' ); ?>
			</span>
		</div>
	</div>
</section>

<!-- Как заказать -->
<section class="section section--panel">
	<div class="wrap">
		<div class="s-head">
			<div>
				<h2><?php esc_html_e( 'Как заказать поверку', 'amis' ); ?></h2>
				<p><?php esc_html_e( 'Периодическая поверка — когда истекает межповерочный интервал прибора, купленного у нас.', 'amis' ); ?></p>
			</div>
		</div>

		<div class="steps">
			<?php
			$ver_steps = array(
				array(
					'title' => __( 'Заявка', 'amis' ),
					'text'  => __( 'Назовите модель и заводской номер прибора — по своей поставке мы поднимем его историю сами.', 'amis' ),
				),
				array(
					'title' => __( 'Согласование', 'amis' ),
					'text'  => __( 'Подтверждаем стоимость и срок, подбираем ЦСМ с подходящей областью аккредитации.', 'amis' ),
				),
				array(
					'title' => __( 'Передача прибора', 'amis' ),
					'text'  => __( 'Привезите прибор к нам или отправьте транспортной компанией — дальше передаём его в ЦСМ.', 'amis' ),
				),
				array(
					'title' => __( 'Возврат с документами', 'amis' ),
					'text'  => __( 'Отдаём прибор со свидетельством и знаком поверки, сведения уходят во ФГИС «Аршин».', 'amis' ),
				),
			);

			foreach ( $ver_steps as $i => $step ) :
				?>
				<div class="step">
					<span class="n"><?php echo esc_html( sprintf( '%02d', $i + 1 ) ); ?></span>
					<h3><?php echo esc_html( $step['title'] ); ?></h3>
					<p><?php echo esc_html( $step['text'] ); ?></p>
				</div>
			<?php endforeach; ?>
		</div>

		<?php
		// Адрес приёма — тот же, что у офиса и склада, берём из реквизитов, а не текстом.
		$amis_details     = amis_company_details();
		$amis_office_addr = isset( $amis_details['Фактический адрес'] ) ? $amis_details['Фактический адрес'] : '';
		?>
		<div class="d-note" style="margin-top:32px">
			<span>
				<b><?php esc_html_e( 'Адрес приёма:', 'amis' ); ?></b>
				<?php esc_html_e( 'тот же, что у офиса и сервисного центра —', 'amis' ); ?>
				<?php echo esc_html( $amis_office_addr ); ?>.
				<?php esc_html_e( 'Режим работы: пн–пт, 09:00–18:00.', 'amis' ); ?>
			</span>
		</div>
	</div>
</section>

<!-- CTA -->
<section class="section">
	<div class="wrap d-cta">
		<div>
			<h2><?php esc_html_e( 'Подходит срок поверки?', 'amis' ); ?></h2>
			<p><?php esc_html_e( 'Назовите модель и заводской номер прибора — подтвердим стоимость и срок, оформим заявку в ЦСМ.', 'amis' ); ?></p>
		</div>
		<div class="d-cta__actions">
			<?php echo amis_phone_link( 'free', 'btn btn-primary' ); // phpcs:ignore WordPress.Security.EscapeOutput -- экранирование внутри функции. ?>
			<a class="btn btn-ghost" href="<?php echo esc_url( home_url( '/contact/#write' ) ); ?>"><?php esc_html_e( 'Написать нам', 'amis' ); ?></a>
		</div>
	</div>
</section>

<?php
get_footer();
