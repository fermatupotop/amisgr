<?php
/**
 * Оформление страницы «Оформление заказа» (/checkout/).
 *
 * Тот же приём, что и для корзины (inc/cart.php): шаблон WooCommerce
 * не переопределяем — там nonce, расчёт доставки/налогов и логика
 * платёжных шлюзов, которую легко тонко сломать, переписывая по памяти.
 * Вместо этого оборачиваем то, что WooCommerce и так выводит по шорткоду
 * [woocommerce_checkout], через хуки woocommerce_before_checkout_form /
 * woocommerce_after_checkout_form — они срабатывают в начале и в конце
 * формы независимо от количества полей/шлюзов. Оформление — в
 * assets/css/checkout.css, по классам, которые отдаёт сама WooCommerce
 * (.woocommerce-billing-fields, .form-row, #payment и так далее).
 *
 * @package amis
 */

defined( 'ABSPATH' ) || exit;

/**
 * Своя шапка страницы вместо стандартного заголовка Astra — как на
 * корзине и остальных страницах сайта (крошки + section--head).
 */
function amis_checkout_wrap_open() {
	?>
	<nav class="crumbs">
		<div class="wrap">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Главная', 'amis' ); ?></a>
			<span>/</span>
			<a href="<?php echo esc_url( wc_get_cart_url() ); ?>"><?php esc_html_e( 'Корзина', 'amis' ); ?></a>
			<span>/</span>
			<span><?php esc_html_e( 'Оформление заказа', 'amis' ); ?></span>
		</div>
	</nav>

	<section class="section section--head">
		<div class="wrap">
			<h1><?php esc_html_e( 'Оформление заказа', 'amis' ); ?></h1>
		</div>
	</section>

	<section class="section">
		<div class="wrap">
			<div class="checkout-page">
	<?php
}
add_action( 'woocommerce_before_checkout_form', 'amis_checkout_wrap_open', 5 );

/**
 * Закрывающие теги обёртки.
 */
function amis_checkout_wrap_close() {
	?>
			</div>
		</div>
	</section>
	<?php
}
add_action( 'woocommerce_after_checkout_form', 'amis_checkout_wrap_close' );

/**
 * Свой заголовок дублировал бы стандартный <h1 class="entry-title"> Astra
 * на странице «Оформление заказа» — отключаем его так же, как на корзине.
 */
function amis_checkout_title_enabled( $enabled ) {
	if ( function_exists( 'is_checkout' ) && is_checkout() && ! is_order_received_page() ) {
		return false;
	}
	return $enabled;
}
add_filter( 'astra_the_title_enabled', 'amis_checkout_title_enabled' );

/**
 * Упрощённая форма: с сайта уходит не готовый заказ с доставкой,
 * а заявка на КП — адрес и детали поставки менеджер уточняет сам при
 * обработке. Адресные поля тут только лишний барьер перед отправкой.
 *
 * @param array $fields Поля чекаута.
 * @return array
 */
function amis_checkout_simplify_fields( $fields ) {

	unset(
		$fields['billing']['billing_address_1'],
		$fields['billing']['billing_address_2'],
		$fields['billing']['billing_city'],
		$fields['billing']['billing_state'],
		$fields['billing']['billing_postcode'],
		$fields['billing']['billing_country'],
		$fields['billing']['billing_last_name']
	);

	// Без фамилии «Имя» больше не в паре — растягиваем на всю строку.
	if ( isset( $fields['billing']['billing_first_name'] ) ) {
		$fields['billing']['billing_first_name']['class'] = array( 'form-row-wide' );
	}

	return $fields;
}
add_filter( 'woocommerce_checkout_fields', 'amis_checkout_simplify_fields' );

/**
 * «Примечание к заказу» — тоже убираем: это про доставку, которой тут нет,
 * все детали заявки обсуждаются с менеджером после отправки.
 */
add_filter( 'woocommerce_enable_order_notes_field', '__return_false' );

/**
 * Поле страны убрано из формы, но заказу всё равно нужна какая-то
 * страна — сайт работает только по России, подставляем её сами вместо
 * ручного выбора.
 *
 * @param array $data Отправленные данные чекаута.
 * @return array
 */
function amis_checkout_default_country( $data ) {
	$data['billing_country'] = 'RU';
	return $data;
}
add_filter( 'woocommerce_checkout_posted_data', 'amis_checkout_default_country' );
