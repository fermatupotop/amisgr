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
$top_cats      = function_exists( 'amis_get_top_categories' ) ? amis_get_top_categories( 20 ) : array();
$paged         = max( 1, (int) get_query_var( 'paged' ) );
$in_stock_only = ! empty( $_GET['instock'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- обычный GET-фильтр витрины, без сохранения состояния.
$current_brand = isset( $_GET['brand'] ) ? sanitize_title( wp_unslash( $_GET['brand'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$current_series = isset( $_GET['series'] ) ? sanitize_title( wp_unslash( $_GET['series'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

/**
 * На самой странице бренда/серии (/brand/rigol/, не /shop/?brand=rigol)
 * бренд/серия — это сам запрошенный таксономический термин, а не
 * GET-параметр фильтра: $_GET['brand'] тут пуст, хотя бренд, очевидно,
 * выбран. Без этого ссылки категорий ниже ($with_filters()) не несли
 * бы его дальше, и клик по категории со страницы бренда сбрасывал бы
 * бренд вместо того, чтобы его сохранить.
 */
if ( $queried_term && 'product_brand' === $queried_term->taxonomy ) {
	$current_brand = $queried_term->slug;

	/**
	 * Плашки категорий на странице бренда — только те, где у этого
	 * бренда реально есть товары (amis_filter_categories_with_brand_products(),
	 * inc/queries.php). Без этого показывались бы и категории, где у
	 * бренда нет ни одной позиции — пустой клик на страницу с 0 товаров.
	 */
	if ( function_exists( 'amis_filter_categories_with_brand_products' ) ) {
		$top_cats = amis_filter_categories_with_brand_products( $top_cats, $queried_term->term_id );
	}
} elseif ( $queried_term && 'pa_series' === $queried_term->taxonomy ) {
	$current_series = $queried_term->slug;
}
/**
 * Не прогоняем через sanitize_title() здесь: термины диапазона — на
 * кириллице, а слаг в БД хранится в процентно-закодированном виде.
 * sanitize_title() пытается закодировать обратно, но это хрупко (см.
 * разбор в amis_shop_facet_filter(), inc/queries.php) — здесь значение
 * только переносится дальше в ссылки других фильтров, фактическое
 * сопоставление с термином (с запасным вариантом на сыром значении)
 * происходит там же, в amis_shop_facet_filter().
 */
$current_freq   = isset( $_GET['filter_frequency-range'] ) ? wp_unslash( $_GET['filter_frequency-range'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- шкала с главной, см. amis_shop_facet_filter()

/**
 * Цена — та же GET-схема, что у остальных фильтров (inc/queries.php,
 * amis_shop_price_filter()). sanitize_text_field() тут достаточно: оба
 * значения уходят в (float) на стороне запроса, лишнего не исполнится,
 * а здесь они только возвращаются обратно в поля формы и в ссылки.
 */
$current_price_min = isset( $_GET['price_min'] ) ? sanitize_text_field( wp_unslash( $_GET['price_min'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$current_price_max = isset( $_GET['price_max'] ) ? sanitize_text_field( wp_unslash( $_GET['price_max'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

/**
 * Сортировка по ключевому параметру — имеет смысл только внутри одной
 * категории (amis_shop_spec_sort(), inc/queries.php), но саму ссылку
 * храним и переносим и там, где контрол не показан: иначе переход по
 * категории/бренду с активной сортировкой сбрасывал бы её.
 */
$current_sort = isset( $_GET['sort'] ) ? sanitize_key( wp_unslash( $_GET['sort'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
if ( ! in_array( $current_sort, array( 'spec-asc', 'spec-desc' ), true ) ) {
	$current_sort = '';
}

/**
 * Ссылка с учётом состояния ВСЕХ активных фильтров сразу — используется
 * в ссылках категорий/бренда/серии, чтобы переключение одного фильтра
 * не сбрасывало остальные уже выбранные.
 */
$with_filters = function ( $url ) use ( $in_stock_only, $current_brand, $current_series, $current_freq, $current_price_min, $current_price_max, $current_sort ) {
	if ( $in_stock_only ) {
		$url = add_query_arg( 'instock', '1', $url );
	}
	if ( $current_brand ) {
		$url = add_query_arg( 'brand', $current_brand, $url );
	}
	if ( $current_series ) {
		$url = add_query_arg( 'series', $current_series, $url );
	}
	if ( $current_freq ) {
		$url = add_query_arg( 'filter_frequency-range', $current_freq, $url );
	}
	if ( '' !== $current_price_min ) {
		$url = add_query_arg( 'price_min', $current_price_min, $url );
	}
	if ( '' !== $current_price_max ) {
		$url = add_query_arg( 'price_max', $current_price_max, $url );
	}
	if ( $current_sort ) {
		$url = add_query_arg( 'sort', $current_sort, $url );
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

		<?php
		/**
		 * Логотип бренда — то же поле «Изображение» термина
		 * (thumbnail_id), что и у фото категорий на главной
		 * (templates/template-home.php): WooCommerce хранит его под
		 * одним и тем же ключом term meta что для product_cat, что
		 * для product_brand. Показываем только на архиве самого бренда,
		 * не на /shop/ и не на категориях/сериях — там нет одного
		 * конкретного бренда, которому логотип соответствовал бы.
		 */
		$brand_logo_id = ( $queried_term && 'product_brand' === $queried_term->taxonomy )
			? get_term_meta( $queried_term->term_id, 'thumbnail_id', true )
			: 0;
		?>
		<?php if ( $brand_logo_id ) : ?>
			<div class="brand-logo">
				<?php echo wp_get_attachment_image( $brand_logo_id, 'medium', false, array( 'alt' => esc_attr( $queried_term->name ) ) ); ?>
			</div>
		<?php endif; ?>

		<h1><?php woocommerce_page_title(); ?></h1>

		<?php if ( $queried_term && $queried_term->description ) : ?>
			<?php
			/**
			 * div, не p: описание категории может содержать свою разметку
			 * (несколько абзацев, списки, <strong> и т.п.) — обёртка в <p>
			 * ломала бы её на вложенные блочные теги. .page-lead — чисто
			 * типографский класс, от тега не зависит (assets/css/page.css).
			 */
			?>
			<?php
			/**
			 * --wide снимает max-width:72ch у .page-lead (assets/css/page.css) —
			 * то ограничение расчитано на короткую одну строку, как у
			 * большинства категорий. Когда в описании несколько абзацев
			 * и список (как у «Оборудование ГНСС»), та же ширина оставляет
			 * половину страницы пустой — тут она не нужна.
			 */
			?>
			<div class="page-lead page-lead--wide"><?php echo wp_kses_post( $queried_term->description ); ?></div>
		<?php elseif ( ! $queried_term ) : ?>
			<p class="page-lead">
				<?php esc_html_e( 'Осциллографы, генераторы, анализаторы и лабораторные приборы RIGOL, МигТрейдинг, R&S, Tektronix, Keysight, Ceyear, Emctestlab — в наличии и под заказ.', 'amis' ); ?>
			</p>
		<?php endif; ?>

		<?php
		/**
		 * Базовый адрес текущей страницы архива (без query-параметров
		 * фильтров) — нужен и плашкам категорий, и форме цены, и
		 * сортировке, и facet-ссылкам ниже, поэтому считаем один раз здесь.
		 */
		$base_url = $queried_term ? get_term_link( $queried_term ) : amis_shop_url();

		/**
		 * Название ключевого параметра для контрола сортировки —
		 * только на страницах категорий (amis_archive_spec_sort_label(),
		 * inc/queries.php): единицы у разных категорий разные, сравнивать
		 * «полосу» осциллографа с «мощностью» усилителя в одном списке
		 * было бы бессмысленно, поэтому на /shop/ и на бренде/серии
		 * (где категории вперемешку) сортировки нет.
		 */
		$spec_sort_label = ( $queried_term && 'product_cat' === $queried_term->taxonomy && function_exists( 'amis_archive_spec_sort_label' ) )
			? amis_archive_spec_sort_label( $queried_term )
			: '';

		/**
		 * Строит ссылку фильтра бренда/серии с учётом остальных активных
		 * фильтров: $overrides задаёт, что поставить/убрать в ЭТОМ ряду
		 * (пустая строка — убрать), остальные (instock, цена, сортировка,
		 * второй из пары бренд/серия) добавляются как есть. Объявлено
		 * здесь — один closure нужен и блоку бренда, и блоку серии ниже.
		 */
		$build_facet_url = function ( $overrides ) use ( $base_url, $in_stock_only, $current_freq, $current_price_min, $current_price_max, $current_sort ) {
			$url = $base_url;
			foreach ( $overrides as $key => $value ) {
				$url = '' === $value ? remove_query_arg( $key, $url ) : add_query_arg( $key, $value, $url );
			}
			if ( $in_stock_only ) {
				$url = add_query_arg( 'instock', '1', $url );
			}
			if ( $current_freq ) {
				$url = add_query_arg( 'filter_frequency-range', $current_freq, $url );
			}
			if ( '' !== $current_price_min ) {
				$url = add_query_arg( 'price_min', $current_price_min, $url );
			}
			if ( '' !== $current_price_max ) {
				$url = add_query_arg( 'price_max', $current_price_max, $url );
			}
			if ( $current_sort ) {
				$url = add_query_arg( 'sort', $current_sort, $url );
			}
			return $url;
		};

		/**
		 * Кнопки категорий — на общем каталоге, на самих страницах
		 * категорий и на странице бренда: категория и бренд — разные
		 * оси фильтра, одно другое не перебивает. Ссылки строятся через
		 * $with_filters(), который сохраняет текущий ?brand=, так что
		 * клик по категории со страницы бренда ведёт на «категория +
		 * этот же бренд», а не сбрасывает его.
		 * На странице серии (pa_series) и на метках — по-прежнему
		 * скрыто: туда уводило бы с текущего выбора, а не дополняло.
		 */
		$show_cat_filter = ! $queried_term || in_array( $queried_term->taxonomy, array( 'product_cat', 'product_brand' ), true );
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

		<div class="shop-facets">

			<!-- Цена: обычная GET-форма, без JS. Показывается всегда на
			     этом шаблоне (/shop/, категория, бренд, серия) — в отличие
			     от категории/бренда цена не «уводит» с текущего выбора,
			     а только сужает его, поэтому не скрываем её нигде. -->
			<div class="shop-facet">
				<span class="shop-facet__label"><?php esc_html_e( 'Цена, ₽', 'amis' ); ?></span>
				<form class="shop-price" method="get" action="<?php echo esc_url( $base_url ); ?>">
					<input type="number" name="price_min" inputmode="numeric" min="0" step="1" placeholder="<?php esc_attr_e( 'от', 'amis' ); ?>" value="<?php echo esc_attr( $current_price_min ); ?>">
					<span class="shop-price__dash">—</span>
					<input type="number" name="price_max" inputmode="numeric" min="0" step="1" placeholder="<?php esc_attr_e( 'до', 'amis' ); ?>" value="<?php echo esc_attr( $current_price_max ); ?>">
					<button type="submit" class="btn btn-ghost btn-sm"><?php esc_html_e( 'Применить', 'amis' ); ?></button>
					<?php
					/**
					 * Остальные активные фильтры — скрытыми полями, чтобы
					 * отправка формы их не сбрасывала (обычная GET-форма
					 * идёт с нуля, в отличие от add_query_arg() у ссылок).
					 */
					?>
					<?php if ( $in_stock_only ) : ?><input type="hidden" name="instock" value="1"><?php endif; ?>
					<?php if ( $current_brand ) : ?><input type="hidden" name="brand" value="<?php echo esc_attr( $current_brand ); ?>"><?php endif; ?>
					<?php if ( $current_series ) : ?><input type="hidden" name="series" value="<?php echo esc_attr( $current_series ); ?>"><?php endif; ?>
					<?php if ( $current_freq ) : ?><input type="hidden" name="filter_frequency-range" value="<?php echo esc_attr( $current_freq ); ?>"><?php endif; ?>
					<?php if ( $current_sort ) : ?><input type="hidden" name="sort" value="<?php echo esc_attr( $current_sort ); ?>"><?php endif; ?>
				</form>
			</div>

			<?php if ( $spec_sort_label ) : ?>
				<?php
				/**
				 * Строит ссылку сортировки, сохраняя остальные фильтры —
				 * через тот же $with_filters(), что и у плашек категорий,
				 * только сперва снимаем текущий ?sort=, чтобы не задвоить.
				 */
				$sort_url = function ( $value ) use ( $with_filters, $base_url ) {
					$url = remove_query_arg( 'sort', $with_filters( $base_url ) );
					return $value ? add_query_arg( 'sort', $value, $url ) : $url;
				};
				?>
				<div class="shop-facet">
					<span class="shop-facet__label">
						<?php
						/* translators: %s — название ключевого параметра категории, например «Полоса пропускания». */
						printf( esc_html__( 'Сортировка по: %s', 'amis' ), esc_html( $spec_sort_label ) );
						?>
					</span>
					<div class="shop-filter">
						<a href="<?php echo esc_url( $sort_url( 'spec-asc' ) ); ?>" class="<?php echo esc_attr( 'spec-asc' === $current_sort ? 'is-active' : '' ); ?>">
							<?php esc_html_e( 'По возрастанию', 'amis' ); ?>
						</a>
						<a href="<?php echo esc_url( $sort_url( 'spec-desc' ) ); ?>" class="<?php echo esc_attr( 'spec-desc' === $current_sort ? 'is-active' : '' ); ?>">
							<?php esc_html_e( 'По убыванию', 'amis' ); ?>
						</a>
					</div>
				</div>
			<?php endif; ?>

			<?php if ( $show_facets && $brand_terms ) : ?>
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

			<?php if ( $show_facets && $series_terms ) : ?>
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

			<?php if ( $in_stock_only || $current_brand || $current_series || $current_freq || '' !== $current_price_min || '' !== $current_price_max || $current_sort ) : ?>
				<a class="shop-reset" href="<?php echo esc_url( $base_url ); ?>">
					<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6L6 18M6 6l12 12"/></svg>
					<?php esc_html_e( 'Сбросить все фильтры', 'amis' ); ?>
				</a>
			<?php endif; ?>

		</div>
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
