<?php
/**
 * Страница «Уставные документы».
 *
 * Файл назван по слагу страницы: WordPress найдёт его автоматически
 * для страницы с адресом /documents/. Выбирать шаблон в админке не нужно.
 * Ссылка на эту страницу уже есть в footer.php (блок «О компании»).
 *
 * Список документов — заглушка вёрстки: пути вида /documents/ustav.pdf
 * ведут на файлы, которых пока нет на сервере. Как только заказчик
 * пришлёт сканы, файлы нужно положить в /documents/ под этими именами
 * (или поправить пути на реальные) — разметку менять не придётся.
 *
 * @package amis
 */

defined( 'ABSPATH' ) || exit;

get_header();

$details       = amis_company_details();
$short_details = array_intersect_key(
	$details,
	array_flip( array( 'Сокращённое', 'ИНН', 'ОГРН', 'Юридический адрес', 'Генеральный директор' ) )
);

/**
 * Документы: заголовок, описание, файл. 'file' — имя внутри /documents/.
 */
$documents = array(
	array(
		'title' => __( 'Устав ООО «АМИС ГРУПП»', 'amis' ),
		'desc'  => __( 'Действующая редакция учредительного документа общества.', 'amis' ),
		'file'  => 'ustav.pdf',
	),
	array(
		'title' => __( 'Свидетельство о государственной регистрации (ОГРН)', 'amis' ),
		'desc'  => __( 'Лист записи ЕГРЮЛ о создании юридического лица.', 'amis' ),
		'file'  => 'ogrn.pdf',
	),
	array(
		'title' => __( 'Свидетельство о постановке на налоговый учёт (ИНН)', 'amis' ),
		'desc'  => __( 'Документ о постановке на учёт в налоговом органе.', 'amis' ),
		'file'  => 'inn.pdf',
	),
	array(
		'title' => __( 'Выписка из ЕГРЮЛ', 'amis' ),
		'desc'  => __( 'Актуальная выписка из Единого государственного реестра юридических лиц.', 'amis' ),
		'file'  => 'egrul.pdf',
	),
	array(
		'title' => __( 'Решение о создании общества', 'amis' ),
		'desc'  => __( 'Решение единственного участника об учреждении ООО «АМИС ГРУПП».', 'amis' ),
		'file'  => 'reshenie.pdf',
	),
	array(
		'title' => __( 'Приказ о назначении генерального директора', 'amis' ),
		'desc'  => __( 'Приказ о вступлении в должность действующего руководителя.', 'amis' ),
		'file'  => 'prikaz-direktor.pdf',
	),
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
			<?php esc_html_e( 'Учредительные и регистрационные документы ООО «АМИС ГРУПП» для проверки контрагента и участия в закупках.', 'amis' ); ?>
		</p>
	</div>
</section>

<!-- Список документов -->
<section class="section">
	<div class="wrap">
		<div class="doc-list">
			<?php foreach ( $documents as $doc ) : ?>
				<div class="doc-card">
					<span class="c-ico">
						<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
							<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/>
						</svg>
					</span>
					<div class="doc-card__body">
						<b><?php echo esc_html( $doc['title'] ); ?></b>
						<span><?php echo esc_html( $doc['desc'] ); ?></span>
					</div>
					<a class="btn btn-ghost btn-sm doc-card__dl" href="<?php echo esc_url( home_url( '/documents/' . $doc['file'] ) ); ?>" download>
						<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
							<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
							<polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/>
						</svg>
						<?php esc_html_e( 'PDF', 'amis' ); ?>
					</a>
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
				<h2><?php esc_html_e( 'Реквизиты компании', 'amis' ); ?></h2>
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

		<a class="btn btn-ghost btn-sm" style="margin-top:20px" href="<?php echo esc_url( home_url( '/documents/rekvizity.pdf' ) ); ?>" download>
			<?php esc_html_e( 'Скачать карточку предприятия (PDF)', 'amis' ); ?>
		</a>
	</div>
</section>

<!-- CTA -->
<section class="section">
	<div class="wrap d-cta">
		<div>
			<h2><?php esc_html_e( 'Нужен документ, которого нет в списке?', 'amis' ); ?></h2>
			<p><?php esc_html_e( 'Пришлём дополнительные документы для тендера или проверки контрагента по запросу.', 'amis' ); ?></p>
		</div>
		<div class="d-cta__actions">
			<?php echo amis_phone_link( 'free', 'btn btn-primary' ); // phpcs:ignore WordPress.Security.EscapeOutput -- экранирование внутри функции. ?>
			<a class="btn btn-ghost" href="<?php echo esc_url( home_url( '/contact/#write' ) ); ?>"><?php esc_html_e( 'Написать нам', 'amis' ); ?></a>
		</div>
	</div>
</section>

<?php
get_footer();
