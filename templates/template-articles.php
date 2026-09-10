<?php
/**
 * Template Name: База знаний
 *
 * Архив обычных записей WP (post) — не отдельный тип контента и не
 * привязан к одной категории: любая опубликованная запись появляется
 * здесь автоматически, добавлять её в код не нужно. Если позже
 * понадобится не показывать часть записей (например, отдельные
 * «новости») — здесь же завести отдельную категорию-исключение.
 *
 * Фильтр по категориям строится из реально существующих категорий
 * (get_categories()) и не выводится, если категория всего одна —
 * вести список тем в коде не нужно, он сам подстраивается под контент.
 *
 * Карточка — template-parts/article-card.php, та же, что и в блоке
 * «Похожие статьи» на single.php.
 *
 * @package amis
 */

defined( 'ABSPATH' ) || exit;

get_header();

$paged        = max( 1, get_query_var( 'paged' ) ? get_query_var( 'paged' ) : get_query_var( 'page' ) );
$current_cat  = isset( $_GET['cat'] ) ? absint( $_GET['cat'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- обычная ссылка-фильтр, без сохранения состояния.

$query_args = array(
	'post_type'      => 'post',
	'posts_per_page' => 9,
	'paged'          => $paged,
);

if ( $current_cat ) {
	$query_args['cat'] = $current_cat;
}

$articles   = new WP_Query( $query_args );
$categories = get_categories( array( 'hide_empty' => true ) );

/**
 * «Misc» — служебная категория по умолчанию для записей без темы,
 * не настоящая рубрика знаний. В фильтре и на карточках не показываем;
 * сами записи под ней в архиве остаются видны.
 */
$categories = array_filter(
	$categories,
	static function ( $cat ) {
		return 'misc' !== $cat->slug;
	}
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
		<span class="eyebrow"><?php esc_html_e( 'Статьи и инструкции', 'amis' ); ?></span>
		<h1><?php the_title(); ?></h1>
		<p class="page-lead">
			<?php esc_html_e( 'Как выбрать прибор, разобраться в характеристиках и терминах, подготовиться к поверке — статьи по мотивам вопросов наших клиентов.', 'amis' ); ?>
		</p>

		<?php if ( count( $categories ) > 1 ) : ?>
			<div class="article-filter">
				<a href="<?php echo esc_url( home_url( '/articles/' ) ); ?>" class="<?php echo esc_attr( 0 === $current_cat ? 'is-active' : '' ); ?>">
					<?php esc_html_e( 'Все статьи', 'amis' ); ?>
				</a>
				<?php foreach ( $categories as $cat ) : ?>
					<a href="<?php echo esc_url( add_query_arg( 'cat', $cat->term_id, home_url( '/articles/' ) ) ); ?>" class="<?php echo esc_attr( $current_cat === $cat->term_id ? 'is-active' : '' ); ?>">
						<?php echo esc_html( $cat->name ); ?>
					</a>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</section>

<!-- Статьи -->
<section class="section">
	<div class="wrap">

		<?php if ( $articles->have_posts() ) : ?>

			<div class="article-grid">
				<?php
				while ( $articles->have_posts() ) :
					$articles->the_post();
					get_template_part( 'template-parts/article-card' );
				endwhile;
				wp_reset_postdata();
				?>
			</div>

			<?php if ( $articles->max_num_pages > 1 ) : ?>
				<div class="article-pagination">
					<?php
					echo paginate_links( array( // phpcs:ignore WordPress.Security.EscapeOutput -- paginate_links() возвращает готовую разметку.
						'total'   => $articles->max_num_pages,
						'current' => $paged,
						'add_args' => $current_cat ? array( 'cat' => $current_cat ) : array(),
					) );
					?>
				</div>
			<?php endif; ?>

		<?php else : ?>

			<div class="article-empty">
				<h3><?php esc_html_e( 'Пока здесь пусто', 'amis' ); ?></h3>
				<p><?php esc_html_e( 'Готовим первые статьи о выборе оборудования и работе с приборами. Если нужна консультация прямо сейчас — напишите или позвоните нам.', 'amis' ); ?></p>
			</div>

		<?php endif; ?>

	</div>
</section>

<!-- CTA -->
<section class="section section--panel">
	<div class="wrap d-cta">
		<div>
			<h2><?php esc_html_e( 'Не нашли нужную тему?', 'amis' ); ?></h2>
			<p><?php esc_html_e( 'Спросите менеджера или инженера напрямую — ответим быстрее, чем пишется статья.', 'amis' ); ?></p>
		</div>
		<div class="d-cta__actions">
			<?php echo amis_phone_link( 'free', 'btn btn-primary' ); // phpcs:ignore WordPress.Security.EscapeOutput -- экранирование внутри функции. ?>
			<a class="btn btn-ghost" href="<?php echo esc_url( home_url( '/contact/#write' ) ); ?>"><?php esc_html_e( 'Написать нам', 'amis' ); ?></a>
		</div>
	</div>
</section>

<?php
get_footer();
