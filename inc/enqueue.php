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
		// Одиночная запись и архив «Базы знаний» используют те же базовые
		// компоненты (.section--head, .eyebrow, .crumbs и так далее).
	if ( ( is_page() && ! amis_is_home_template() ) || is_singular( 'post' ) ) {
		wp_enqueue_style( 'amis-page', AMIS_URI . '/assets/css/page.css', array( 'amis-base' ), AMIS_VERSION );
	}

	wp_enqueue_script( 'amis-base', AMIS_URI . '/assets/js/base.js', array(), AMIS_VERSION, true );


	if ( function_exists( 'is_product' ) && is_product() ) {
		wp_enqueue_style( 'amis-product', AMIS_URI . '/assets/css/product.css', array( 'amis-base' ), AMIS_VERSION );
		wp_enqueue_script( 'amis-product', AMIS_URI . '/assets/js/product.js', array(), AMIS_VERSION, true );
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
	if ( amis_is_home_template() || is_singular( 'post' ) || is_page_template( 'templates/template-articles.php' ) ) {
		return 'no-sidebar';
	}
	return $layout;
}
add_filter( 'astra_page_layout', 'amis_no_sidebar_layout' );
