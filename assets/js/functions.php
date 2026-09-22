<?php
/**
 * Дочерняя тема Astra для АМИС Групп.
 *
 * @package amis
 */

defined( 'ABSPATH' ) || exit;

define( 'AMIS_VERSION', '1.1.5' );
define( 'AMIS_DIR', get_stylesheet_directory() );
define( 'AMIS_URI', get_stylesheet_directory_uri() );

require_once AMIS_DIR . '/inc/queries.php';
require_once AMIS_DIR . '/inc/shortcodes.php';
require_once AMIS_DIR . '/inc/product-fields.php';
require_once AMIS_DIR . '/inc/product-map.php';

/**
 * Поддержка возможностей темы и регистрация меню.
 */
function amis_theme_setup() {

	register_nav_menus( array(
		'primary' => __( 'Основное меню', 'amis' ),
	) );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'navigation-widgets' ) );

	// Без этого WooCommerce ругается на несовместимость темы.
	add_theme_support( 'woocommerce' );
}
add_action( 'after_setup_theme', 'amis_theme_setup' );

/**
 * Мы на шаблоне главной?
 *
 * @return bool
 */
function amis_is_home_template() {
	return is_page_template( 'templates/template-home.php' );
}

/**
 * Ссылка на каталог. Если WooCommerce выключен — на главную,
 * чтобы шапка не вела в никуда.
 *
 * @return string
 */
function amis_shop_url() {
	return function_exists( 'wc_get_page_permalink' )
		? wc_get_page_permalink( 'shop' )
		: home_url( '/' );
}

/**
 * Количество товаров в корзине.
 *
 * Проверка на null обязательна: на некоторых запросах
 * (REST, cron) объект корзины ещё не создан.
 *
 * @return int
 */
function amis_cart_count() {

	if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
		return 0;
	}

	return (int) WC()->cart->get_cart_contents_count();
}

/**
 * Запасное меню: категории товаров, пока меню не создано в админке.
 */
function amis_nav_fallback() {

	$cats = amis_get_top_categories( 6 );

	if ( ! $cats ) {
		return;
	}

	echo '<ul>';
	foreach ( $cats as $cat ) {
		printf(
			'<li><a href="%s">%s</a></li>',
			esc_url( get_term_link( $cat ) ),
			esc_html( $cat->name )
		);
	}
	echo '</ul>';
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
	wp_enqueue_script( 'amis-base', AMIS_URI . '/assets/js/base.js', array(), AMIS_VERSION, true );

	if ( function_exists( 'is_product' ) && is_product() ) {
		wp_enqueue_style( 'amis-product', AMIS_URI . '/assets/css/product.css', array( 'amis-base' ), AMIS_VERSION );
		wp_enqueue_script( 'amis-product', AMIS_URI . '/assets/js/product.js', array(), AMIS_VERSION, true );
	}

	if ( amis_is_home_template() ) {
		wp_enqueue_style( 'amis-home', AMIS_URI . '/assets/css/home.css', array( 'amis-base' ), AMIS_VERSION );
		wp_enqueue_script( 'amis-home', AMIS_URI . '/assets/js/home.js', array(), AMIS_VERSION, true );
	}
}
add_action( 'wp_enqueue_scripts', 'amis_enqueue_assets', 15 );

/**
 * Раскладка контента: снимаем флекс-контейнер Astra.
 *
 * @param string $layout Текущая раскладка.
 * @return string
 */
function amis_home_content_layout( $layout ) {
	return amis_is_home_template() ? 'page-builder' : $layout;
}
add_filter( 'astra_get_content_layout', 'amis_home_content_layout' );

/**
 * Без сайдбара.
 */
function amis_home_page_layout( $layout ) {
	return amis_is_home_template() ? 'no-sidebar' : $layout;
}
add_filter( 'astra_page_layout', 'amis_home_page_layout' );

/**
 * Порядок терминов частотной шкалы.
 */
function amis_frequency_terms_order( $args, $taxonomies ) {
	if ( in_array( 'pa_frequency-range', (array) $taxonomies, true ) && empty( $args['orderby'] ) ) {
		$args['orderby'] = 'term_order';
	}
	return $args;
}
add_filter( 'get_terms_args', 'amis_frequency_terms_order', 10, 2 );


/**
 * Contact Form 7 сам расставляет <br> и <p> в разметке формы.
 * Внутри CSS-грида они становятся лишними элементами и ломают колонки.
 */
add_filter( 'wpcf7_autop_or_not', '__return_false' );

add_filter( 'astra_get_content_layout', function ( $layout ) {
	if ( function_exists( 'is_product' ) && is_product() ) {
		return 'page-builder';
	}
	return $layout;
} );
