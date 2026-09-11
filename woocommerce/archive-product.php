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

/**
 * is_product_taxonomy() — это не только product_cat/product_tag, но и
 * любой атрибут с включённым архивом (например, серия — pa_series):
 * такая страница работает через этот же шаблон и должна показывать
 * своё название в заголовке и крошках так же, как обычная категория.
 */
$queried_term  = ( function_exists( 'is_product_taxonomy' ) && is_product_taxonomy() ) ? get_queried_object() : null;
$current_cat   = ( $queried_term && 'product_cat' === $queried_term->taxonomy ) ? $queried_term->term_id : 0;
$top_cats      = function_exists( 'amis_get_top_categories' ) ? amis_get_top_categories( 10 ) : array();
$paged         = max( 1, (int) get_query_var( 'paged' ) );
$in_stock_only = ! empty( $_GET['instock'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- обычный GET-фильтр витрины, без сохранения состояния.
$current_brand = isset( $_GET['brand'] ) ? sanitize_title( wp_unslash( $_GET['brand'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$current_series = isset( $_GET['series'] ) ? sanitize_title( wp_unslash( $_GET['series'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

/**
 * Ссылка с учётом состояния ВСЕХ активных фильтров сразу — используется
 * в ссылках категорий/бренда/серии, чтобы переключение одного фильтра
 * не сбрасывало остальные уже выбранные.
 */
$with_filters = function ( $url ) use ( $in_stock_only, $current_brand, $current_series ) {
	if ( $in_stock_only ) {
		$url = add_query_arg( 'instock', '1', $url );
	}
	if ( $current_brand ) {
		$url = add_query_arg( 'brand', $current_brand, $url );
	}
	if ( $current_series ) {
		$url = add_query_arg( 'series', $current_series, $url );
	}
	return $url;
};

// Оставлено под старым именем — так уже вызывается в разметке категорий ниже.
$with_stock = $with_filters;

/**
 * Бренд и серия имеют смысл только там, где под ними реально бывает
 * несколько разных значений: на /shop/ и на страницах категорий/меток.
 * На странице конкретного бренда/серии эти же фильтры уводили бы с
 * текущего выбора, а не дополняли его — там их не показываем (та же
 * логика, что и у $show_cat_filter ниже).
 */
$show_facets   = ! $queried_term || in_array( $queried_term->taxonomy, array( 'product_cat', 'product_tag' ), true );
$brand_terms   = $show_facets ? amis_get_archive_facet_terms( 'product_brand' ) : array();
$series_terms  = $show_facets ? amis_get_archive_facet_terms( 'pa_series' ) : array();
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
		<?php elseif ( ! $queried_term ) : ?>
			<p class="page-lead">
				<?php esc_html_e( 'Осциллографы, генераторы, анализаторы и лабораторные приборы RIGOL, Siglent, Keysight, Tektronix, АКИП, ПриСТ — в наличии и под заказ.', 'amis' ); ?>
			</p>
		<?php endif; ?>

		<?php
		/**
		 * Кнопки категорий имеют смысл только на общем каталоге и на
		 * самих страницах категорий — там это переключение «смотрю
		 * похожее». На архиве атрибута (серия, бренд) они бы уводили
		 * с текущего фильтра, а не дополняли его, поэтому скрываем.
		 */
		$show_cat_filter = ! $queried_term || 'product_cat' === $queried_term->taxonomy;
		?>
		<?php if ( $show_cat_filter && $top_cats ) : ?>
			<div class="shop-filter">
				<a href="<?php echo esc_url( $with_stock( amis_shop_url() ) ); ?>" class="<?php echo esc_attr( $current_cat ? '' : 'is-active' ); ?>">
					<?php esc_html_e( 'Все категории', 'amis' ); ?>
				</a>
				<?php foreach ( $top_cats as $cat ) : ?>
					<a href="<?php echo esc_url( $with_stock( get_term_link( $cat ) ) ); ?>" class="<?php echo esc_attr( $current_cat === $cat->term_id ? 'is-active' : '' ); ?>">
						<?php echo esc_html( $cat->name ); ?>
					</a>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<?php if ( $show_facets && ( $brand_terms || $series_terms ) ) : ?>
			<?php
			$base_url = $queried_term ? get_term_link( $queried_term ) : amis_shop_url();

			/**
			 * Строит ссылку фильтра с учётом остальных активных фильтров:
			 * $overrides задаёт, что поставить/убрать в ЭТОМ ряду фильтров
			 * (пустая строка — убрать), остальные (instock) добавляются как есть.
			 */
			$build_facet_url = function ( $overrides ) use ( $base_url, $in_stock_only ) {
				$url = $base_url;
				foreach ( $overrides as $key => $value ) {
					$url = '' === $value ? remove_query_arg( $key, $url ) : add_query_arg( $key, $value, $url );
				}
				return $in_stock_only ? add_query_arg( 'instock', '1', $url ) : $url;
			};
			?>

			<div class="shop-facets">

			<?php if ( $brand_terms ) : ?>
				<div class="shop-facet">
					<span class="shop-facet__label"><?php esc_html_e( 'Бренд', 'amis' ); ?></span>
					<div class="shop-filter">
						<a href="<?php echo esc_url( $build_facet_url( array( 'brand' => '', 'series' => $current_series ) ) ); ?>" class="<?php echo esc_attr( '' === $current_brand ? 'is-active' : '' ); ?>">
							<?php esc_html_e( 'Все бренды', 'amis' ); ?>
						</a>
						<?php foreach ( $brand_terms as $term ) : ?>
							<a href="<?php echo esc_url( $build_facet_url( array( 'brand' => $term->slug, 'series' => $current_series ) ) ); ?>" class="<?php echo esc_attr( $current_brand === $term->slug ? 'is-active' : '' ); ?>">
								<?php echo esc_html( $term->name ); ?>
							</a>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endif; ?>

			<?php if ( $series_terms ) : ?>
				<div class="shop-facet">
					<span class="shop-facet__label"><?php esc_html_e( 'Серия', 'amis' ); ?></span>
					<div class="shop-filter">
						<a href="<?php echo esc_url( $build_facet_url( array( 'series' => '', 'brand' => $current_brand ) ) ); ?>" class="<?php echo esc_attr( '' === $current_series ? 'is-active' : '' ); ?>">
							<?php esc_html_e( 'Все серии', 'amis' ); ?>
						</a>
						<?php foreach ( $series_terms as $term ) : ?>
							<a href="<?php echo esc_url( $build_facet_url( array( 'series' => $term->slug, 'brand' => $current_brand ) ) ); ?>" class="<?php echo esc_attr( $current_series === $term->slug ? 'is-active' : '' ); ?>">
								<?php echo esc_html( $term->name ); ?>
							</a>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endif; ?>

			<?php if ( $in_stock_only || $current_brand || $current_series ) : ?>
				<a class="shop-reset" href="<?php echo esc_url( $base_url ); ?>">
					<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6L6 18M6 6l12 12"/></svg>
					<?php esc_html_e( 'Сбросить все фильтры', 'amis' ); ?>
				</a>
			<?php endif; ?>

			</div>
		<?php endif; ?>
	</div>
</section>

<!-- Товары -->
<section class="section">
	<div class="wrap">

		<?php if ( have_posts() ) : ?>

			<div class="shop-toolbar">
				<p class="shop-count">
					<?php
					printf(
						/* translators: %d — число товаров. */
						esc_html( _n( '%d товар', '%d товаров', $GLOBALS['wp_query']->found_posts, 'amis' ) ),
						(int) $GLOBALS['wp_query']->found_posts
					);
					?>
				</p>

				<a
					class="shop-instock <?php echo esc_attr( $in_stock_only ? 'is-active' : '' ); ?>"
					href="<?php echo esc_url( $in_stock_only ? remove_query_arg( 'instock' ) : add_query_arg( 'instock', '1' ) ); ?>"
				>
					<svg width="15" height="15" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M3 8.5l3 3 7-7"/></svg>
					<?php esc_html_e( 'В наличии', 'amis' ); ?>
				</a>
			</div>

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
				<?php if ( $in_stock_only ) : ?>
					<h3><?php esc_html_e( 'Нет товаров в наличии', 'amis' ); ?></h3>
					<p>
						<?php esc_html_e( 'В этой категории сейчас всё под заказ.', 'amis' ); ?>
						<a href="<?php echo esc_url( remove_query_arg( 'instock' ) ); ?>"><?php esc_html_e( 'Показать все товары', 'amis' ); ?></a>
					</p>
				<?php else : ?>
					<h3><?php esc_html_e( 'Здесь пока пусто', 'amis' ); ?></h3>
					<p><?php esc_html_e( 'В этой категории пока нет товаров на сайте. Позвоните или напишите нам — подберём прибор и посчитаем цену вручную.', 'amis' ); ?></p>
				<?php endif; ?>
			</div>

		<?php endif; ?>

		<?php wp_reset_postdata(); ?>

	</div>
</section>

<?php get_footer(); ?>
