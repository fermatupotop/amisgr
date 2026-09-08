<?php
/**
 * Template Name: Работа по 44-ФЗ и 223-ФЗ
 *
 * Ссылка в footer.php («Работа по 44-ФЗ и 223-ФЗ») уже ведёт на /tenders/ —
 * когда страница будет создана в админке, назначить ей этот шаблон.
 *
 * Контактная карточка ниже — менеджер с ролью «Менеджер по закупкам» из
 * amis_company_managers() (inc/company.php); если роль переименуют,
 * поправить строку сравнения ниже вместе с company.php.
 *
 * @package amis
 */

defined( 'ABSPATH' ) || exit;

get_header();

$details       = amis_company_details();
$short_details = array_intersect_key(
	$details,
	array_flip( array( 'ИНН', 'КПП', 'ОГРН' ) )
);

$tender_manager = null;
foreach ( amis_company_managers() as $person ) {
	if ( 'Менеджер по закупкам' === $person['role'] ) {
		$tender_manager = $person;
		break;
	}
}
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
		<span class="eyebrow"><?php esc_html_e( 'Госзакупки', 'amis' ); ?></span>
		<h1><?php the_title(); ?></h1>
		<p class="page-lead">
			<?php esc_html_e( 'Поставляем измерительное оборудование бюджетным учреждениям и корпоративным заказчикам по 44-ФЗ и 223-ФЗ — от коммерческого предложения с обоснованием НМЦК до закрывающих документов по контракту.', 'amis' ); ?>
		</p>
	</div>
</section>

<!-- Что мы предлагаем заказчикам -->
<section class="section">
	<div class="wrap">
		<div class="s-head">
			<div>
				<h2><?php esc_html_e( 'Что мы предлагаем заказчикам', 'amis' ); ?></h2>
			</div>
		</div>

		<div class="d-methods">

			<div class="d-card">
				<span class="c-ico">
					<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
						<path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
					</svg>
				</span>
				<h3><?php esc_html_e( 'Участие в закупках', 'amis' ); ?></h3>
				<p><?php esc_html_e( 'Готовим заявки и коммерческие предложения с обоснованием НМЦК, подписываем контракты по 44-ФЗ и 223-ФЗ.', 'amis' ); ?></p>
			</div>

			<div class="d-card">
				<span class="c-ico">
					<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
						<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="9" y1="13" x2="15" y2="13"/><line x1="9" y1="17" x2="15" y2="17"/>
					</svg>
				</span>
				<h3><?php esc_html_e( 'Полный пакет документов', 'amis' ); ?></h3>
				<p><?php esc_html_e( 'Учредительные документы для проверки контрагента, счета, УПД, сертификаты и декларации соответствия.', 'amis' ); ?></p>
			</div>

			<div class="d-card">
				<span class="c-ico">
					<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
						<rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/>
					</svg>
				</span>
				<h3><?php esc_html_e( 'Безналичный расчёт и ЭДО', 'amis' ); ?></h3>
				<p><?php esc_html_e( 'Оплата по счёту с НДС, электронный документооборот через Диадок, СБИС и Контур.', 'amis' ); ?></p>
			</div>

		</div>
	</div>
</section>

<!-- Как проходит закупка -->
<section class="section section--panel">
	<div class="wrap">
		<div class="s-head">
			<div>
				<h2><?php esc_html_e( 'Как проходит закупка', 'amis' ); ?></h2>
			</div>
		</div>

		<div class="steps">
			<?php
			$steps = array(
				array(
					'title' => __( 'Изучаем закупку', 'amis' ),
					'text'  => __( 'Смотрим техническое задание или спецификацию, уточняем требования к оборудованию и срокам поставки.', 'amis' ),
				),
				array(
					'title' => __( 'Готовим предложение', 'amis' ),
					'text'  => __( 'Присылаем коммерческое предложение с ценами и обоснованием НМЦК для документации закупки.', 'amis' ),
				),
				array(
					'title' => __( 'Заявка и контракт', 'amis' ),
					'text'  => __( 'Подаём заявку на участие или подписываем контракт напрямую — по 44-ФЗ или 223-ФЗ.', 'amis' ),
				),
				array(
					'title' => __( 'Поставка и документы', 'amis' ),
					'text'  => __( 'Отгружаем в срок по контракту и передаём полный пакет закрывающих документов.', 'amis' ),
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

<!-- Проверка контрагента -->
<section class="section">
	<div class="wrap">
		<div class="s-head">
			<div>
				<h2><?php esc_html_e( 'Проверка контрагента', 'amis' ); ?></h2>
				<p><?php esc_html_e( 'Учредительные документы — на странице «Уставные документы», полный список реквизитов — на странице контактов.', 'amis' ); ?></p>
			</div>
			<a class="s-link" href="<?php echo esc_url( home_url( '/documents/' ) ); ?>">
				<?php esc_html_e( 'Уставные документы', 'amis' ); ?>
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

<?php if ( $tender_manager ) : ?>
	<!-- Менеджер по тендерам -->
	<section class="section section--panel">
		<div class="wrap">
			<div class="s-head">
				<div>
					<h2><?php esc_html_e( 'Ваш менеджер по тендерам', 'amis' ); ?></h2>
					<p><?php esc_html_e( 'Ведёт закупку от коммерческого предложения до закрывающих документов.', 'amis' ); ?></p>
				</div>
			</div>

			<div class="contact-cards" style="grid-template-columns:1fr">

				<div class="c-card">
					<span class="c-ico">
						<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
							<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
						</svg>
					</span>
					<div>
						<span class="c-label"><?php echo esc_html( $tender_manager['role'] ); ?></span>
						<span class="c-value c-value--sm"><strong><?php echo esc_html( $tender_manager['name'] ); ?></strong></span>
					</div>
				</div>

				<div class="c-card">
					<span class="c-ico">
						<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
							<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
						</svg>
					</span>
					<div>
						<span class="c-label"><?php esc_html_e( 'Телефон', 'amis' ); ?></span>
						<a class="c-value c-value--mono" href="tel:<?php echo esc_attr( preg_replace( '/\D/', '', $tender_manager['phone'] ) ); ?>">
							<?php echo esc_html( $tender_manager['phone'] ); ?>
							<?php if ( $tender_manager['ext'] ) : ?>
								<?php esc_html_e( 'доб.', 'amis' ); ?> <?php echo esc_html( $tender_manager['ext'] ); ?>
							<?php endif; ?>
						</a>
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
						<a class="c-value" href="mailto:<?php echo esc_attr( $tender_manager['email'] ); ?>"><?php echo esc_html( $tender_manager['email'] ); ?></a>
					</div>
				</div>

			</div>
		</div>
	</section>
<?php endif; ?>

<!-- CTA -->
<section class="section">
	<div class="wrap d-cta">
		<div>
			<h2><?php esc_html_e( 'Готовите закупку?', 'amis' ); ?></h2>
			<p><?php esc_html_e( 'Пришлите техническое задание или номер извещения — подготовим предложение с обоснованием НМЦК.', 'amis' ); ?></p>
		</div>
		<div class="d-cta__actions">
			<?php echo amis_phone_link( 'free', 'btn btn-primary' ); // phpcs:ignore WordPress.Security.EscapeOutput -- экранирование внутри функции. ?>
			<a class="btn btn-ghost" href="<?php echo esc_url( home_url( '/contact/#write' ) ); ?>"><?php esc_html_e( 'Написать нам', 'amis' ); ?></a>
		</div>
	</div>
</section>

<?php
get_footer();
