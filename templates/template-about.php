<?php
/**
 * Template Name: О компании
 *
 * Страница «О компании»: цифры, чем занимаемся, почему выбирают нас,
 * команда, реквизиты. Структура подсмотрена у конкурента, тексты и
 * цифры — реальные данные АМИС ГРУПП (год из ОГРН, показатели —
 * те же, что уже на главной).
 *
 * Фотоблок необязателен: если к этой странице в медиатеке прикреплены
 * изображения (Редактировать страницу → Добавить медиафайл), они
 * встанут в сетку справа от текста. Если фото нет — секция просто
 * растягивается на всю ширину, ничего не ломается.
 *
 * @package amis
 */

defined( 'ABSPATH' ) || exit;

get_header();

$gallery = get_attached_media( 'image', get_the_ID() );
$details = amis_company_details();
$short_details = array_intersect_key(
	$details,
	array_flip( array( 'Сокращённое', 'ИНН', 'Юридический адрес' ) )
);
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
		<span class="eyebrow"><?php esc_html_e( 'О компании', 'amis' ); ?></span>
		<h1><?php the_title(); ?></h1>
		<p class="page-lead">
			<?php esc_html_e( 'Поставляем сертифицированное радиоизмерительное оборудование лабораториям, конструкторским бюро и промышленным предприятиям по всей России — от подбора модели до поверки и сервиса.', 'amis' ); ?>
		</p>

		<div class="about-stats">
			<div class="stat">
				<b>2022</b>
				<span><?php esc_html_e( 'Год основания компании', 'amis' ); ?></span>
			</div>
			<div class="stat">
				<b>150<i>+</i></b>
				<span><?php esc_html_e( 'Лабораторий и КБ закупаются регулярно', 'amis' ); ?></span>
			</div>
			<div class="stat">
				<b>24 <?php esc_html_e( 'ч', 'amis' ); ?></b>
				<span><?php esc_html_e( 'От оплаты счёта до отгрузки складской позиции', 'amis' ); ?></span>
			</div>
			<?php if ( class_exists( 'WooCommerce' ) ) : ?>
				<div class="stat">
					<b><?php echo esc_html( wp_count_posts( 'product' )->publish ); ?></b>
					<span><?php esc_html_e( 'Приборов в каталоге', 'amis' ); ?></span>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>

<!-- О компании: текст + фото -->
<section class="section">
	<div class="wrap">
		<div class="s-head">
			<div>
				<h2><?php esc_html_e( 'Чем мы занимаемся', 'amis' ); ?></h2>
			</div>
		</div>

		<div class="about-grid<?php echo $gallery ? '' : ' about-grid--full'; ?>">
			<div class="about-text">
				<p>
					<strong><?php esc_html_e( 'ООО «АМИС ГРУПП»', 'amis' ); ?></strong>
					<?php esc_html_e( '— поставщик контрольно-измерительного оборудования: осциллографов, анализаторов спектра, генераторов сигналов и усилителей мощности. Работаем с 2022 года, отгружаем приборы со склада в Москве по всей России.', 'amis' ); ?>
				</p>
				<p>
					<?php esc_html_e( 'В каталоге — оборудование RIGOL, Siglent, Keysight, Tektronix, АКИП и ПриСТ. Помогаем подобрать модель под задачу: если нужного прибора нет в наличии или сроки не устраивают, инженер предложит аналог с сопоставимыми параметрами и назовёт реальный срок поставки.', 'amis' ); ?>
				</p>
				<p>
					<?php esc_html_e( 'Кроме продажи — поверка и калибровка в аккредитованном ЦСМ, аренда приборов на проект и демо-образец на тест перед покупкой. Работаем по 44-ФЗ и 223-ФЗ, оформляем полный пакет документов для тендеров и бухгалтерии.', 'amis' ); ?>
				</p>

				<?php if ( get_the_content() ) : ?>
					<?php
					while ( have_posts() ) :
						the_post();
						the_content();
					endwhile;
					?>
				<?php endif; ?>
			</div>

			<?php if ( $gallery ) : ?>
				<div class="about-photos">
					<?php foreach ( array_slice( $gallery, 0, 4 ) as $image ) : ?>
						<a href="<?php echo esc_url( wp_get_attachment_url( $image->ID ) ); ?>">
							<?php echo wp_get_attachment_image( $image->ID, 'medium_large' ); ?>
						</a>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>

<!-- Почему выбирают нас -->
<section class="section section--panel">
	<div class="wrap">
		<div class="s-head">
			<div>
				<h2><?php esc_html_e( 'Почему выбирают нас', 'amis' ); ?></h2>
				<p><?php esc_html_e( 'Шесть причин, по которым лаборатории и КБ закупаются у нас регулярно.', 'amis' ); ?></p>
			</div>
		</div>

		<div class="why-grid">
			<?php
			$why = array(
				array(
					'title' => __( 'Несколько производителей', 'amis' ),
					'text'  => __( 'RIGOL, Siglent, Keysight, Tektronix, АКИП и ПриСТ — подбираем модель по параметрам, а не по одному бренду.', 'amis' ),
					'icon'  => '<path d="M4 4h16M4 4v16M4 4l16 16M20 4v16M20 20H4"/>',
				),
				array(
					'title' => __( 'Склад в Москве', 'amis' ),
					'text'  => __( 'Отгружаем складские позиции в течение 24 часов после оплаты счёта.', 'amis' ),
					'icon'  => '<path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>',
				),
				array(
					'title' => __( 'Поверка и сертификаты', 'amis' ),
					'text'  => __( 'Первичная поверка в аккредитованном ЦСМ, сертификаты соответствия и паспорта изделий — по запросу.', 'amis' ),
					'icon'  => '<path d="M9 12l2 2 4-4"/><circle cx="12" cy="12" r="10"/>',
				),
				array(
					'title' => __( 'Документы для тендеров', 'amis' ),
					'text'  => __( 'Работаем по 44-ФЗ и 223-ФЗ, оформляем счета, УПД и ЭДО через Диадок, СБИС и Контур.', 'amis' ),
					'icon'  => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="9" y1="13" x2="15" y2="13"/><line x1="9" y1="17" x2="15" y2="17"/>',
				),
				array(
					'title' => __( 'Демо и аренда', 'amis' ),
					'text'  => __( 'Отправляем прибор на тест на 14 дней, сдаём в аренду с зачётом в выкуп.', 'amis' ),
					'icon'  => '<polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/>',
				),
				array(
					'title' => __( 'Инженер, а не оператор', 'amis' ),
					'text'  => __( 'На вопросы отвечает инженер по применению — обычно в течение того же рабочего дня.', 'amis' ),
					'icon'  => '<path d="M12 1a9 9 0 0 0-9 9v7a2 2 0 0 0 2 2h1a1 1 0 0 0 1-1v-4a1 1 0 0 0-1-1H5v-3a7 7 0 0 1 14 0v3h-1a1 1 0 0 0-1 1v4a1 1 0 0 0 1 1h1a2 2 0 0 0 2-2v-7a9 9 0 0 0-9-9z"/>',
				),
			);

			foreach ( $why as $item ) :
				?>
				<div class="why-card">
					<span class="c-ico">
						<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
							<?php echo $item['icon']; // phpcs:ignore WordPress.Security.EscapeOutput -- статичный, захардкоженный SVG-путь, не пользовательский ввод. ?>
						</svg>
					</span>
					<h3><?php echo esc_html( $item['title'] ); ?></h3>
					<p><?php echo esc_html( $item['text'] ); ?></p>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<!-- Наша команда -->
<section class="section">
	<div class="wrap">
		<div class="s-head">
			<div>
				<h2><?php esc_html_e( 'Наша команда', 'amis' ); ?></h2>
				<p class="team-lead"><?php esc_html_e( 'Три специалиста ведут сделку от заявки до отгрузки — пишите сразу профильному инженеру, без переключения между отделами.', 'amis' ); ?></p>
			</div>
		</div>

		<div class="managers">
			<?php foreach ( amis_company_managers() as $person ) : ?>
				<div class="manager">
					<span class="manager__ava">
						<?php
						$parts    = preg_split( '/\s+/', $person['name'] );
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

<!-- Реквизиты -->
<section class="section section--panel">
	<div class="wrap">
		<div class="s-head">
			<div>
				<h2><?php esc_html_e( 'Юридическая информация', 'amis' ); ?></h2>
				<p><?php esc_html_e( 'Полный список реквизитов и карточка предприятия — на странице контактов.', 'amis' ); ?></p>
			</div>
			<a class="s-link" href="<?php echo esc_url( home_url( '/contact/#details' ) ); ?>">
				<?php esc_html_e( 'Все реквизиты', 'amis' ); ?>
			</a>
		</div>

		<dl class="details details--narrow">
			<?php foreach ( $short_details as $label => $value ) : ?>
				<div class="details__row">
					<dt><?php echo esc_html( $label ); ?></dt>
					<dd><?php echo esc_html( $value ); ?></dd>
				</div>
			<?php endforeach; ?>
		</dl>
	</div>
</section>

<!-- CTA -->
<section class="section">
	<div class="wrap d-cta">
		<div>
			<h2><?php esc_html_e( 'Остались вопросы о компании или сотрудничестве?', 'amis' ); ?></h2>
			<p><?php esc_html_e( 'Расскажем подробнее об опыте с похожими задачами и подготовим коммерческое предложение.', 'amis' ); ?></p>
		</div>
		<div class="d-cta__actions">
			<?php echo amis_phone_link( 'free', 'btn btn-primary' ); // phpcs:ignore WordPress.Security.EscapeOutput -- экранирование внутри функции. ?>
			<a class="btn btn-ghost" href="<?php echo esc_url( home_url( '/contact/#write' ) ); ?>"><?php esc_html_e( 'Написать нам', 'amis' ); ?></a>
		</div>
	</div>
</section>

<?php
get_footer();
