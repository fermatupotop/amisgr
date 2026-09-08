<?php
/**
 * Template Name: Как сделать заказ
 *
 * Ссылка в footer.php («Как сделать заказ») уже ведёт на /how-to-order/ —
 * когда страница будет создана в админке, назначить ей этот шаблон.
 *
 * Шаги и способы оформления заявки описывают реальный сценарий из
 * woocommerce/single-product.php: кнопка «В корзину» (штатный WooCommerce
 * add-to-cart) для позиций с ценой и «Запросить счёт» (/request/) для
 * остальных случаев — эта страница ничего не придумывает, а поясняет то,
 * что уже реализовано на карточке товара.
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
		<span class="eyebrow"><?php esc_html_e( 'Заказ и поставка', 'amis' ); ?></span>
		<h1><?php the_title(); ?></h1>
		<p class="page-lead">
			<?php esc_html_e( 'Оформить заказ можно прямо на сайте или через менеджера — счёт, документы и срок поставки согласуем на этапе подтверждения заявки. Работаем как с организациями по безналичному расчёту, так и с частными покупателями.', 'amis' ); ?>
		</p>
	</div>
</section>

<!-- Как это работает -->
<section class="section">
	<div class="wrap">
		<div class="s-head">
			<div>
				<h2><?php esc_html_e( 'Как это работает', 'amis' ); ?></h2>
				<p><?php esc_html_e( 'От выбора прибора до отгрузки — четыре шага.', 'amis' ); ?></p>
			</div>
		</div>

		<div class="steps">
			<?php
			$steps = array(
				array(
					'title' => __( 'Выберите прибор', 'amis' ),
					'text'  => __( 'В каталоге — по бренду, типу и характеристикам. Не уверены в модели — напишите или позвоните менеджеру, поможем подобрать.', 'amis' ),
				),
				array(
					'title' => __( 'Оформите заявку', 'amis' ),
					'text'  => __( 'На странице товара — «В корзину» для позиций с ценой, «Запросить счёт» для остальных. Быстрее для юрлиц и позиций без цены на сайте.', 'amis' ),
				),
				array(
					'title' => __( 'Подтверждение и счёт', 'amis' ),
					'text'  => __( 'Менеджер уточнит наличие и срок поставки, пришлёт счёт и, если нужно, договор — в течение рабочего дня.', 'amis' ),
				),
				array(
					'title' => __( 'Оплата и отгрузка', 'amis' ),
					'text'  => __( 'После оплаты отгружаем со склада в Москве в течение 24 часов или сообщаем срок поступления от производителя.', 'amis' ),
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
	</div>
</section>

<!-- Способы оформить заявку -->
<section class="section section--panel">
	<div class="wrap">
		<div class="s-head">
			<div>
				<h2><?php esc_html_e( 'Как удобнее оформить заявку', 'amis' ); ?></h2>
				<p><?php esc_html_e( 'Выберите способ — результат одинаковый: подтверждение и счёт от менеджера.', 'amis' ); ?></p>
			</div>
		</div>

		<div class="d-methods">

			<div class="d-card">
				<span class="c-ico">
					<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
						<circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
					</svg>
				</span>
				<h3><?php esc_html_e( 'На сайте', 'amis' ); ?></h3>
				<p><?php esc_html_e( 'Добавьте прибор в корзину и оформите заказ онлайн — для позиций с ценой на сайте.', 'amis' ); ?></p>
				<ul class="d-list">
					<li><?php esc_html_e( 'Корзина и оформление на сайте', 'amis' ); ?></li>
					<li><?php esc_html_e( 'Подтверждение по почте', 'amis' ); ?></li>
				</ul>
			</div>

			<div class="d-card">
				<span class="c-ico">
					<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
						<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="9" y1="13" x2="15" y2="13"/><line x1="9" y1="17" x2="15" y2="17"/>
					</svg>
				</span>
				<h3><?php esc_html_e( 'Запросить счёт', 'amis' ); ?></h3>
				<p><?php esc_html_e( 'Кнопка «Запросить счёт» на странице товара — для юрлиц, тендеров и позиций без цены на сайте.', 'amis' ); ?></p>
				<ul class="d-list">
					<li><?php esc_html_e( 'Счёт с реквизитами на почту', 'amis' ); ?></li>
					<li><?php esc_html_e( 'Договор — при необходимости', 'amis' ); ?></li>
				</ul>
			</div>

			<div class="d-card">
				<span class="c-ico">
					<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
						<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
					</svg>
				</span>
				<h3><?php esc_html_e( 'По телефону или почте', 'amis' ); ?></h3>
				<p><?php esc_html_e( 'Если удобнее голосом или разово — назовите модель и количество, оформим заявку сами.', 'amis' ); ?></p>
				<ul class="d-list">
					<li><?php echo amis_phone_link( 'free' ); // phpcs:ignore WordPress.Security.EscapeOutput -- экранирование внутри функции. ?></li>
					<li><a href="mailto:<?php echo esc_attr( amis_company_email() ); ?>"><?php echo esc_html( amis_company_email() ); ?></a></li>
				</ul>
			</div>

		</div>
	</div>
</section>

<!-- Что понадобится для заявки -->
<section class="section">
	<div class="wrap">
		<div class="s-head">
			<div>
				<h2><?php esc_html_e( 'Что понадобится для заявки', 'amis' ); ?></h2>
				<p><?php esc_html_e( 'Реквизиты можно прислать и позже — для подтверждения заказа достаточно контактов и списка позиций.', 'amis' ); ?></p>
			</div>
		</div>

		<div class="cover-grid">

			<div class="cover-card">
				<h3><?php esc_html_e( 'Для юридических лиц', 'amis' ); ?></h3>
				<ul class="cover-list cover-list--yes">
					<?php
					$legal = array(
						__( 'Наименование организации и ИНН', 'amis' ),
						__( 'ФИО и контакты ответственного', 'amis' ),
						__( 'Список позиций и количество', 'amis' ),
						__( 'Реквизиты для счёта — можно прислать позже', 'amis' ),
					);

					foreach ( $legal as $item ) :
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
				<h3><?php esc_html_e( 'Для физических лиц', 'amis' ); ?></h3>
				<ul class="cover-list cover-list--yes">
					<?php
					$personal = array(
						__( 'ФИО и телефон', 'amis' ),
						__( 'Адрес доставки или способ получения', 'amis' ),
						__( 'Список позиций и количество', 'amis' ),
					);

					foreach ( $personal as $item ) :
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

		<div class="d-note" style="margin-top:32px">
			<b><?php esc_html_e( 'Доставка, оплата и документы:', 'amis' ); ?></b>
			<?php esc_html_e( 'подробные условия и полный список сопроводительных документов — на странице', 'amis' ); ?>
			<a href="<?php echo esc_url( home_url( '/payment/' ) ); ?>"><?php esc_html_e( '«Доставка и оплата»', 'amis' ); ?></a>.
		</div>
	</div>
</section>

<!-- CTA -->
<section class="section">
	<div class="wrap d-cta">
		<div>
			<h2><?php esc_html_e( 'Готовы сделать заказ?', 'amis' ); ?></h2>
			<p><?php esc_html_e( 'Позвоните нам или выберите прибор в каталоге — подскажем и оформим заявку.', 'amis' ); ?></p>
		</div>
		<div class="d-cta__actions">
			<?php echo amis_phone_link( 'free', 'btn btn-primary' ); // phpcs:ignore WordPress.Security.EscapeOutput -- экранирование внутри функции. ?>
			<a class="btn btn-ghost" href="<?php echo esc_url( amis_shop_url() ); ?>"><?php esc_html_e( 'Перейти в каталог', 'amis' ); ?></a>
		</div>
	</div>
</section>

<?php
get_footer();
