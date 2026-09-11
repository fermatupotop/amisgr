<?php
/**
 * Дочерняя тема Astra для АМИС Групп.
 *
 * @package amis
 */

defined( 'ABSPATH' ) || exit;

define( 'AMIS_VERSION', '1.1.9' );
define( 'AMIS_DIR', get_stylesheet_directory() );
define( 'AMIS_URI', get_stylesheet_directory_uri() );

require_once AMIS_DIR . '/inc/queries.php';
require_once AMIS_DIR . '/inc/shortcodes.php';
require_once AMIS_DIR . '/inc/product-fields.php';
require_once AMIS_DIR . '/inc/product-map.php';
require_once AMIS_DIR . '/inc/product-docs.php';
require_once AMIS_DIR . '/inc/product-package.php';
require_once AMIS_DIR . '/inc/product-accessories.php';
require_once AMIS_DIR . '/inc/cart.php';
require_once AMIS_DIR . '/inc/checkout.php';
require_once AMIS_DIR . '/inc/price-import.php';
require_once AMIS_DIR . '/inc/accessories-import.php';
require_once AMIS_DIR . '/inc/company.php';
require_once AMIS_DIR . '/inc/enqueue.php';

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

add_filter( 'woocommerce_price_format', function () {
	return '%1$s&nbsp;%2$s'; // Неразрывный пробел между числом и символом.
}, 10 );

