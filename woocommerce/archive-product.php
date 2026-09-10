<?php
/**
 * Архив каталога: /shop/, страницы категорий и меток товара.
 *
 * Заменяет стандартный цикл WooCommerce/Astra полностью — без него
 * страница рисовалась вообще без стилей темы (голый список в один ряд).
 * Карточка та же, что и на главной, — template-parts/product-card.php,
 * сетка и фильтр — свои классы в assets/css/shop.css (page.css грузится
 * тоже, ради .crumbs/.section/.eyebrow — общих для всех обычных страниц).
 *
 * @package amis
 */

defined( 'ABSPATH' ) || exit;

get_header();

$queried_term = is_tax( array( 'product_cat', 'product_tag' ) ) ? get_queried_object() : null;
$current_cat  = ( $queried_term && 'product_cat' === $queried_term->taxonomy ) ? $queried_term->term_id : 0;
$top_cats     = function_exists( 'amis_get_top_categories' ) ? amis_get_top_categories( 10 ) : array();
$paged        = max( 1, (int) get_query_var( 'paged' ) );
?>

<nav class="crumbs">
	<div class="wrap">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Главная', 'amis' ); ?></a>
		<span>/</span>
		<?php if ( $queried_term ) : ?>
			<a href="<?php echo esc_url( amis_shop_url() ); ?>"><?php esc_html_e( 'Каталог', 'amis' ); ?></a>
			<span>/</span>
			<span><?php echo esc_html( $queried_term->name ); ?></span>
		<?php else : ?>
			<span><?php esc_html_e( 'Каталог', 'amis' ); ?></span>
		<?php endif; ?>
	</div>
</nav>

<!-- Шапка страницы -->
<section class="section section--head">
	<div class="wrap">
		<span class="eyebrow"><?php esc_html_e( 'Каталог', 'amis' ); ?></span>
		<h1><?php woocommerce_page_title(); ?></h1>

		<?php if ( $queried_term && $queried_term->description ) : ?>
			<p class="page-lead"><?php echo wp_kses_post( $queried_term->description ); ?></p>
		<?php else : ?>
			<p class="page-lead">
				<?php esc_html_e( 'Осциллографы, генераторы, анализаторы и лабораторные приборы RIGOL, Siglent, Keysight, Tektronix, АКИП, ПриСТ — в наличии и под заказ.', 'amis' ); ?>
			</p>
		<?php endif; ?>

		<?php if ( $top_cats ) : ?>
			<div class="shop-filter">
				<a href="<?php echo esc_url( amis_shop_url() ); ?>" class="<?php echo esc_attr( $current_cat ? '' : 'is-active' ); ?>">
					<?php esc_html_e( 'Все категории', 'amis' ); ?>
				</a>
				<?php foreach ( $top_cats as $cat ) : ?>
					<a href="<?php echo esc_url( get_term_link( $cat ) ); ?>" class="<?php echo esc_attr( $current_cat === $cat->term_id ? 'is-active' : '' ); ?>">
						<?php echo esc_html( $cat->name ); ?>
					</a>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</section>

<!-- Товары -->
<section class="section">
	<div class="wrap">

		<?php if ( have_posts() ) : ?>

			<p class="shop-count">
				<?php
				printf(
					/* translators: %d — число товаров. */
					esc_html( _n( '%d товар', '%d товаров', $GLOBALS['wp_query']->found_posts, 'amis' ) ),
					(int) $GLOBALS['wp_query']->found_posts
				);
				?>
			</p>

			<div class="shop-grid">
				<?php
				while ( have_posts() ) :
					the_post();
					global $product;
					if ( ! $product instanceof WC_Product ) {
						$product = wc_get_product( get_the_ID() );
					}
					get_template_part( 'template-parts/product-card', null, array( 'product' => $product ) );
				endwhile;
				?>
			</div>

			<?php if ( $GLOBALS['wp_query']->max_num_pages > 1 ) : ?>
				<div class="shop-pagination">
					<?php
					echo paginate_links( array( // phpcs:ignore WordPress.Security.EscapeOutput -- paginate_links() возвращает готовую разметку.
						'total'   => $GLOBALS['wp_query']->max_num_pages,
						'current' => $paged,
					) );
					?>
				</div>
			<?php endif; ?>

		<?php else : ?>

			<div class="shop-empty">
				<h3><?php esc_html_e( 'Здесь пока пусто', 'amis' ); ?></h3>
				<p><?php esc_html_e( 'В этой категории пока нет товаров на сайте. Позвоните или напишите нам — подберём прибор и посчитаем цену вручную.', 'amis' ); ?></p>
			</div>

		<?php endif; ?>

		<?php wp_reset_postdata(); ?>

	</div>
</section>

<?php get_footer(); ?>
