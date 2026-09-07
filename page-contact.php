<?php
/**
 * Страница «Контакты».
 *
 * Файл назван по слагу страницы: WordPress найдёт его автоматически
 * для страницы с адресом /contact/. Выбирать шаблон в админке не нужно.
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
		<h1><?php the_title(); ?></h1>
		<p class="page-lead">
			<?php esc_html_e( 'Отвечаем на письма в течение двух часов в рабочее время. Если вопрос срочный — звоните, инженер на линии.', 'amis' ); ?>
		</p>
	</div>
</section>

<!-- Контакты и форма -->
<section class="section section--tight">
	<div class="wrap contact-grid">

		<div class="contact-main">

			<div class="contact-cards">

				<div class="c-card">
					<span class="c-ico">
						<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
							<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
						</svg>
					</span>
					<div>
						<span class="c-label"><?php esc_html_e( 'Телефон', 'amis' ); ?></span>
						<a class="c-value c-value--mono" href="tel:+74957700497">+7 (495) 770-04-97</a>
						<span class="c-note"><?php esc_html_e( 'Многоканальный', 'amis' ); ?></span>
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
						<a class="c-value" href="mailto:info@amisgr.ru">info@amisgr.ru</a>
						<span class="c-note"><?php esc_html_e( 'Для заявок и спецификаций', 'amis' ); ?></span>
					</div>
				</div>

				<div class="c-card">
					<span class="c-ico">
						<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
							<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>
						</svg>
					</span>
					<div>
						<span class="c-label"><?php esc_html_e( 'Офис и склад', 'amis' ); ?></span>
						<span class="c-value c-value--sm">127566, Москва,<br>Алтуфьевское шоссе, 48к1</span>
						<span class="c-note"><?php esc_html_e( 'Отгрузка со склада по этому же адресу', 'amis' ); ?></span>
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
						<span class="c-note"><?php esc_html_e( 'Отгрузка до 17:00', 'amis' ); ?></span>
					</div>
				</div>

			</div>

			<?php
			// Текст из редактора страницы: схема проезда, парковка и прочее.
			if ( get_the_content() ) :
				?>
				<div class="contact-text">
					<?php
					while ( have_posts() ) :
						the_post();
						the_content();
					endwhile;
					?>
				</div>
			<?php endif; ?>

		</div>

		<!-- Форма -->
		<aside class="contact-form" id="write">
			<h2><?php esc_html_e( 'Написать нам', 'amis' ); ?></h2>
			<p class="form-lead"><?php esc_html_e( 'Опишите задачу или приложите спецификацию — ответим в тот же рабочий день.', 'amis' ); ?></p>
			<?php echo do_shortcode( '[contact-form-7 id="791225d" title="Запрос КП"]' ); ?>
		</aside>

	</div>
</section>

<!-- Менеджеры -->
<section class="section">
	<div class="wrap">
		<div class="s-head">
			<div>
				<h2><?php esc_html_e( 'Кому писать по вашему вопросу', 'amis' ); ?></h2>
				<p><?php esc_html_e( 'Чтобы не переключали между отделами — пишите сразу профильному инженеру.', 'amis' ); ?></p>
			</div>
		</div>

		<div class="managers">
			<?php foreach ( amis_company_managers() as $person ) : ?>
				<div class="manager">
					<span class="manager__ava">
						<?php
						// Инициалы вместо фото: пока нет снимков, выглядит опрятнее заглушки.
						$parts = preg_split( '/\s+/', $person['name'] );
						$initials = mb_substr( $parts[0], 0, 1 );

						if ( isset( $parts[1] ) ) {
							$initials .= mb_substr( $parts[1], 0, 1 );
						}

						echo esc_html( mb_strtoupper( $initials ) );
						?>
					</span>

					<div class="manager__body">
						<b class="manager__name"><?php echo esc_html( $person['name'] ); ?></b>
						<span class="manager__role"><?php echo esc_html( $person['role'] ); ?></span>
						<span class="manager__area"><?php echo esc_html( $person['area'] ); ?></span>

						<div class="manager__contacts">
							<a href="tel:<?php echo esc_attr( preg_replace( '/\D/', '', $person['phone'] ) ); ?>">
								<?php echo esc_html( $person['phone'] ); ?>
								<?php if ( $person['ext'] ) : ?>
									<span class="manager__ext"><?php esc_html_e( 'доб.', 'amis' ); ?> <?php echo esc_html( $person['ext'] ); ?></span>
								<?php endif; ?>
							</a>
							<a href="mailto:<?php echo esc_attr( $person['email'] ); ?>"><?php echo esc_html( $person['email'] ); ?></a>
						</div>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<!-- Карта -->
<section class="section">
	<div class="wrap">
		<div class="s-head" style="margin-bottom:22px">
			<div><h2><?php esc_html_e( 'Как нас найти', 'amis' ); ?></h2></div>
			<a class="s-link" href="https://yandex.ru/maps/?text=Москва,+Алтуфьевское+шоссе,+48к1" target="_blank" rel="noopener">
				<?php esc_html_e( 'Открыть в Яндекс.Картах', 'amis' ); ?>
			</a>
		</div>

		<?php
		/**
		 * Карта грузится по клику, а не сразу.
		 *
		 * Виджет Яндекса тянет около 800 КБ скриптов. Если вставить его
		 * напрямую, страница потеряет секунду на загрузке ради блока,
		 * который смотрит меньшая часть посетителей.
		 */
		?>
		<div class="map-holder" id="amis-map" data-src="https://yandex.ru/map-widget/v1/?ll=37.583%2C55.878&z=16&text=Алтуфьевское%20шоссе%2048к1">
			<button class="map-load" type="button">
				<svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
					<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>
				</svg>
				<span><?php esc_html_e( 'Показать карту', 'amis' ); ?></span>
				<small><?php esc_html_e( 'Загрузится виджет Яндекс.Карт', 'amis' ); ?></small>
			</button>
		</div>
	</div>
</section>

<!-- Реквизиты -->
<section class="section">
	<div class="wrap">
		<div class="s-head">
			<div>
				<h2><?php esc_html_e( 'Реквизиты', 'amis' ); ?></h2>
				<p><?php esc_html_e( 'Можно скопировать со страницы или скачать карточку предприятия одним файлом.', 'amis' ); ?></p>
			</div>
			<a class="btn btn-ghost btn-sm" href="<?php echo esc_url( home_url( '/documents/rekvizity.pdf' ) ); ?>" download>
				<?php esc_html_e( 'Скачать карточку (PDF)', 'amis' ); ?>
			</a>
		</div>

		<dl class="details">
			<?php foreach ( amis_company_details() as $label => $value ) : ?>
				<div class="details__row">
					<dt><?php echo esc_html( $label ); ?></dt>
					<dd><?php echo esc_html( $value ); ?></dd>
				</div>
			<?php endforeach; ?>
		</dl>
	</div>
</section>

<?php
get_footer();