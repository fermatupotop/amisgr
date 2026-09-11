<?php
/**
 * Оформление страницы «Корзина».
 *
 * Сознательно не трогаем woocommerce/cart/cart.php — там штатная логика
 * купонов, кросс-продаж, nonce и пересчёта, которую легко тонко сломать,
 * переписывая по памяти. Вместо этого оборачиваем то, что WooCommerce и
 * так выводит, своими контейнерами через хуки woocommerce_before_cart /
 * woocommerce_after_cart — они срабатывают в начале и в конце шаблона
 * независимо от того, пуста корзина или нет, так что обёртка не «поедет»
 * ни в одном из состояний. Оформление — в assets/css/cart.css, по классам,
 * которые отдаёт сама WooCommerce (.woocommerce-cart-form, .cart-collaterals
 * и так далее) — они стабильны и не меняются годами.
 *
 * @package amis
 */

defined( 'ABSPATH' ) || exit;

/**
 * Своя шапка страницы вместо стандартного заголовка Astra — как на
 * остальных страницах сайта (крошки + section--head). Заголовок Astra
 * на корзине отключается фильтром ниже, чтобы не было дублей.
 */
function amis_cart_wrap_open() {
	?>
	<nav class="crumbs">
		<div class="wrap">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Главная', 'amis' ); ?></a>
			<span>/</span>
			<span><?php esc_html_e( 'Корзина', 'amis' ); ?></span>
		</div>
	</nav>

	<section class="section section--head">
		<div class="wrap">
			<h1><?php esc_html_e( 'Корзина', 'amis' ); ?></h1>
		</div>
	</section>

	<section class="section">
		<div class="wrap">
			<div class="cart-page">
	<?php
}
add_action( 'woocommerce_before_cart', 'amis_cart_wrap_open' );

/**
 * Закрывающие теги обёртки.
 */
function amis_cart_wrap_close() {
	?>
			</div>
		</div>
	</section>
	<?php
}
add_action( 'woocommerce_after_cart', 'amis_cart_wrap_close' );

/**
 * Свой заголовок в amis_cart_wrap_open() дублировал бы стандартный
 * <h1 class="entry-title"> Astra на странице «Корзина» — отключаем его
 * только там же, README упоминает этот фильтр именно для такого случая.
 */
function amis_cart_title_enabled( $enabled ) {
	if ( function_exists( 'is_cart' ) && is_cart() ) {
		return false;
	}
	return $enabled;
}
add_filter( 'astra_the_title_enabled', 'amis_cart_title_enabled' );
