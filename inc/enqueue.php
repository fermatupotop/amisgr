<?php
/**
 * Подключение стилей/скриптов и связанные с ними фильтры layout Astra.
 *
 * @package amis
 */

defined( 'ABSPATH' ) || exit;

/**
 * Мы на шаблоне главной?
 *
 * @return bool
 */
function amis_is_home_template() {
	return is_page_template( 'templates/template-home.php' );
}

/**
 * Подключение стилей и скриптов.
 *
 * base.css — шапка, меню, подвал: нужен на каждой странице.
 * home.css — секции главной: только на ней.
 * shop.css — сетка и фильтр архива каталога (/shop/, категории, метки).
 */
function amis_enqueue_assets() {

	wp_enqueue_style( 'amis-child', AMIS_URI . '/style.css', array(), AMIS_VERSION );

	wp_enqueue_style(
		'amis-fonts',
		'https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600;700&family=IBM+Plex+Mono:wght@400;500;600&display=swap',
		array(),
		null
	);

	wp_enqueue_style( 'amis-base', AMIS_URI . '/assets/css/base.css', array( 'amis-child' ), AMIS_VERSION );
		// Стили обычных страниц: контакты, доставка, оплата и так далее.
		// Одиночная запись, архив «Базы знаний» и архив каталога используют
		// те же базовые компоненты (.section--head, .eyebrow, .crumbs и так далее).
	$is_shop_archive  = function_exists( 'is_shop' ) && ( is_shop() || is_product_taxonomy() );
	$is_cart_page     = function_exists( 'is_cart' ) && is_cart();
	$is_checkout_page = function_exists( 'is_checkout' ) && is_checkout();

	if ( ( is_page() && ! amis_is_home_template() ) || is_singular( 'post' ) || $is_shop_archive || $is_cart_page || $is_checkout_page ) {
		wp_enqueue_style( 'amis-page', AMIS_URI . '/assets/css/page.css', array( 'amis-base' ), AMIS_VERSION );
	}

	wp_enqueue_script( 'amis-base', AMIS_URI . '/assets/js/base.js', array(), AMIS_VERSION, true );


	if ( function_exists( 'is_product' ) && is_product() ) {
		wp_enqueue_style( 'amis-product', AMIS_URI . '/assets/css/product.css', array( 'amis-base' ), AMIS_VERSION );
		wp_enqueue_script( 'amis-product', AMIS_URI . '/assets/js/product.js', array(), AMIS_VERSION, true );
	}

	if ( $is_shop_archive ) {
		wp_enqueue_style( 'amis-shop', AMIS_URI . '/assets/css/shop.css', array( 'amis-page' ), AMIS_VERSION );
	}

	if ( $is_cart_page ) {
		wp_enqueue_style( 'amis-cart', AMIS_URI . '/assets/css/cart.css', array( 'amis-page' ), AMIS_VERSION );
	}

	if ( $is_checkout_page ) {
		wp_enqueue_style( 'amis-checkout', AMIS_URI . '/assets/css/checkout.css', array( 'amis-page' ), AMIS_VERSION );
	}

	if ( amis_is_home_template() ) {
		wp_enqueue_style( 'amis-home', AMIS_URI . '/assets/css/home.css', array( 'amis-base' ), AMIS_VERSION );
		wp_enqueue_script( 'amis-home', AMIS_URI . '/assets/js/home.js', array(), AMIS_VERSION, true );
	}

	// «База знаний»: архив (templates/template-articles.php) и одиночная запись.
	if ( is_singular( 'post' ) || is_page_template( 'templates/template-articles.php' ) ) {
		wp_enqueue_style( 'amis-article', AMIS_URI . '/assets/css/article.css', array( 'amis-page' ), AMIS_VERSION );
	}
}
add_action( 'wp_enqueue_scripts', 'amis_enqueue_assets', 15 );

/**
 * Свои шаблоны сами управляют шириной и отступами,
 * контейнер Astra им только мешает.
 */
function amis_page_builder_layout( $layout ) {

	if ( function_exists( 'is_product' ) && is_product() ) {
		return 'page-builder';
	}

	if ( function_exists( 'is_shop' ) && ( is_shop() || is_product_taxonomy() ) ) {
		return 'page-builder';
	}

	if ( function_exists( 'is_cart' ) && is_cart() ) {
		return 'page-builder';
	}

	if ( function_exists( 'is_checkout' ) && is_checkout() ) {
		return 'page-builder';
	}

	if ( is_page_template( 'page-contact.php' ) || is_page( 'contact' ) ) {
		return 'page-builder';
	}

	if ( is_page_template( 'page-payment.php' ) ) {
		return 'page-builder';
	}

	if ( is_page_template( 'page-documents.php' ) ) {
		return 'page-builder';
	}

	/**
	 * Одиночная запись «Базы знаний» рисуется своим single.php —
	 * контейнер Astra ей, как и остальным своим шаблонам, только мешает.
	 */
	if ( is_singular( 'post' ) ) {
		return 'page-builder';
	}

	/**
	 * Любой шаблон из templates/template-*.php (главная, «О компании»,
	 * «Вакансии», «Гарантия и сервис» и так далее) сам рисует секции
	 * во всю ширину — контейнер и отступы Astra ему только мешают.
	 */
	$template = get_page_template_slug();
	if ( $template && 0 === strpos( $template, 'templates/template-' ) ) {
		return 'page-builder';
	}

	return $layout;
}
add_filter( 'astra_get_content_layout', 'amis_page_builder_layout' );

/**
 * Без сайдбара — на главной и на «Базе знаний» (архив и запись):
 * там своя сетка колонок, сайдбар Astra в неё не встроен.
 */
function amis_no_sidebar_layout( $layout ) {
	if (
		amis_is_home_template()
		|| is_singular( 'post' )
		|| is_page_template( 'templates/template-articles.php' )
		|| ( function_exists( 'is_shop' ) && ( is_shop() || is_product_taxonomy() ) )
		|| ( function_exists( 'is_cart' ) && is_cart() )
		|| ( function_exists( 'is_checkout' ) && is_checkout() )
	) {
		return 'no-sidebar';
	}
	return $layout;
}
add_filter( 'astra_page_layout', 'amis_no_sidebar_layout' );

/**
 * Служебные варианты страницы каталога — не отдельная ценная страница
 * для выдачи, а то же самое содержимое под другим URL: пагинация
 * (/shop/page/2/) и фильтры (?instock=1, ?brand=, ?series=), в любых
 * сочетаниях. noindex, но НЕ через robots.txt: страницу всё равно нужно
 * обходить роботу, чтобы он доходил по ссылкам до самих товаров —
 * noindex,follow убирает её из результатов поиска, не блокируя обход.
 * wp_robots — фильтр самого WordPress (с 5.7), работает независимо от
 * того, что дополнительно настроено в Yoast.
 */
function amis_noindex_filtered_shop( $robots ) {

	if ( ! function_exists( 'is_shop' ) || ! ( is_shop() || is_product_taxonomy() ) ) {
		return $robots;
	}

	$is_filtered = is_paged()
		|| ! empty( $_GET['instock'] ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- обычный GET-фильтр витрины, только для решения о noindex, ничего не сохраняем и не выводим.
		|| ! empty( $_GET['brand'] ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		|| ! empty( $_GET['series'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	if ( $is_filtered ) {
		$robots['noindex'] = true;
		$robots['follow']  = true;
	}

	return $robots;
}
add_filter( 'wp_robots', 'amis_noindex_filtered_shop' );

/**
 * Канонический адрес для отфильтрованного каталога — указывает на ту же
 * страницу без ?instock=/?brand=/?series=, чтобы вес ссылок не размывался
 * по кучке практически дублирующих друг друга адресов. Пагинацию
 * (/page/2/) не трогаем — у неё канонический адрес должен указывать на
 * саму себя, это отдельно уже решает noindex,follow выше.
 *
 * Два фильтра на одну и ту же логику: wpseo_canonical — если стоит Yoast
 * (сейчас так), get_canonical_url — штатный хук самого WordPress на
 * случай, если Yoast когда-нибудь отключат.
 *
 * @param string $canonical Канонический адрес по умолчанию.
 * @return string
 */
function amis_canonical_strip_shop_filters( $canonical ) {

	if ( ! function_exists( 'is_shop' ) || ! ( is_shop() || is_product_taxonomy() ) ) {
		return $canonical;
	}

	if ( empty( $_GET['instock'] ) && empty( $_GET['brand'] ) && empty( $_GET['series'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- обычный GET-фильтр витрины, только для решения о canonical.
		return $canonical;
	}

	return remove_query_arg( array( 'instock', 'brand', 'series' ), $canonical );
}
add_filter( 'wpseo_canonical', 'amis_canonical_strip_shop_filters' );
add_filter( 'get_canonical_url', 'amis_canonical_strip_shop_filters' );
