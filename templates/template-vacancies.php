<?php
/**
 * Template Name: Вакансии
 *
 * Открытых вакансий сейчас нет, поэтому вместо списка позиций —
 * рассказ о том, кого обычно ищем, и способ прислать резюме «на будущее».
 *
 * Когда появятся открытые позиции: замените массив $vacancies ниже на
 * реальные — сетка карточек отрисуется автоматически, ничего в разметке
 * менять не нужно.
 *
 * @package amis
 */

defined( 'ABSPATH' ) || exit;

get_header();

/**
 * Открытые вакансии. Пусто — блок со списком скрывается сам,
 * вместо него остаётся форма отклика «на перспективу».
 */
$vacancies = array();
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
		<span class="eyebrow"><?php esc_html_e( 'Вакансии', 'amis' ); ?></span>
		<h1><?php the_title(); ?></h1>
		<p class="page-lead">
			<?php if ( $vacancies ) : ?>
				<?php esc_html_e( 'Открытые позиции — ниже. Если подходящей нет, всё равно пришлите резюме: напишем, когда появится что-то по вашему профилю.', 'amis' ); ?>
			<?php else : ?>
				<?php esc_html_e( 'Открытых вакансий сейчас нет, но мы всегда смотрим резюме сильных кандидатов. Пришлите своё — свяжемся, как только появится подходящая позиция.', 'amis' ); ?>
			<?php endif; ?>
		</p>
	</div>
</section>

<?php if ( $vacancies ) : ?>
	<!-- Открытые позиции -->
	<section class="section section--tight">
		<div class="wrap">
			<div class="why-grid">
				<?php foreach ( $vacancies as $job ) : ?>
					<div class="why-card">
						<h3><?php echo esc_html( $job['title'] ); ?></h3>
						<p><?php echo esc_html( $job['text'] ); ?></p>
						<a class="s-link" style="display:inline-block;margin-top:14px" href="<?php echo esc_url( home_url( '/contact/#write' ) ); ?>">
							<?php esc_html_e( 'Откликнуться', 'amis' ); ?>
						</a>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
<?php endif; ?>

<!-- Кого мы обычно ищем -->
<section class="section<?php echo $vacancies ? '' : ' section--tight'; ?>">
	<div class="wrap">
		<div class="s-head">
			<div>
				<h2><?php esc_html_e( 'Кого мы обычно ищем', 'amis' ); ?></h2>
				<p><?php esc_html_e( 'Команда небольшая, роли конкретные — вот направления, по которым чаще всего расширяемся.', 'amis' ); ?></p>
			</div>
		</div>

		<div class="why-grid">
			<?php
			$roles = array(
				array(
					'title' => __( 'Инженер по применению', 'amis' ),
					'text'  => __( 'Консультирует по подбору приборов, помогает с ТЗ и демонстрациями оборудования клиентам.', 'amis' ),
					'icon'  => '<path d="M12 1a9 9 0 0 0-9 9v7a2 2 0 0 0 2 2h1a1 1 0 0 0 1-1v-4a1 1 0 0 0-1-1H5v-3a7 7 0 0 1 14 0v3h-1a1 1 0 0 0-1 1v4a1 1 0 0 0 1 1h1a2 2 0 0 0 2-2v-7a9 9 0 0 0-9-9z"/>',
				),
				array(
					'title' => __( 'Менеджер по продажам', 'amis' ),
					'text'  => __( 'Ведёт сделку от заявки до отгрузки: считает КП, согласует сроки, оформляет документы.', 'amis' ),
					'icon'  => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="9" y1="13" x2="15" y2="13"/><line x1="9" y1="17" x2="15" y2="17"/>',
				),
				array(
					'title' => __( 'Специалист по тендерам', 'amis' ),
					'text'  => __( 'Работает с закупками по 44-ФЗ и 223-ФЗ: документы, площадки, сопровождение контракта.', 'amis' ),
					'icon'  => '<path d="M9 12l2 2 4-4"/><circle cx="12" cy="12" r="10"/>',
				),
				array(
					'title' => __( 'Логист / кладовщик', 'amis' ),
					'text'  => __( 'Приёмка, хранение и отгрузка приборов со склада в Москве — точность и аккуратность важнее скорости.', 'amis' ),
					'icon'  => '<path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>',
				),
			);

			foreach ( $roles as $role ) :
				?>
				<div class="why-card">
					<span class="c-ico">
						<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
							<?php echo $role['icon']; // phpcs:ignore WordPress.Security.EscapeOutput -- статичный, захардкоженный SVG-путь, не пользовательский ввод. ?>
						</svg>
					</span>
					<h3><?php echo esc_html( $role['title'] ); ?></h3>
					<p><?php echo esc_html( $role['text'] ); ?></p>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<!-- Как откликнуться -->
<section class="section">
	<div class="wrap d-cta">
		<div>
			<h2><?php esc_html_e( 'Пришлите резюме', 'amis' ); ?></h2>
			<p>
				<?php esc_html_e( 'На почту, с темой «Резюме» и должностью, которая интересует. Ответим, если для вас найдётся позиция.', 'amis' ); ?>
			</p>
		</div>
		<div class="d-cta__actions">
			<a
				class="btn btn-primary"
				href="mailto:<?php echo esc_attr( amis_company_email() ); ?>?subject=<?php echo rawurlencode( 'Резюме — вакансия' ); ?>"
			>
				<?php echo esc_html( amis_company_email() ); ?>
			</a>
			<?php echo amis_phone_link( 'free', 'btn btn-ghost' ); // phpcs:ignore WordPress.Security.EscapeOutput -- экранирование внутри функции. ?>
		</div>
	</div>
</section>

<?php
get_footer();
