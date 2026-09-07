<?php
/**
 * Шапка сайта.
 *
 * Этот файл полностью заменяет header.php родительской Astra.
 * WordPress ищет header.php сначала в дочерней теме — находит здесь
 * и до Astra уже не доходит.
 *
 * Плата за контроль: настройки шапки в кастомайзере Astra
 * перестают влиять на сайт. Всё, что вы видите, написано ниже.
 *
 * @package amis
 */

defined( 'ABSPATH' ) || exit;
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); // Обязательно: сюда WordPress и плагины вставляют стили и скрипты. ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); // Обязательно: точка входа для плагинов, счётчиков, админ-бара. ?>

<a class="skip-link screen-reader-text" href="#content"><?php esc_html_e( 'Перейти к содержимому', 'amis' ); ?></a>

<!-- Верхняя строка -->
<div class="topbar">
	<div class="wrap">
		<span class="live"><i></i> <?php esc_html_e( 'Пн–Пт 09:00–18:00', 'amis' ); ?></span>
		<span><?php esc_html_e( 'Москва, Алтуфьевское ш., 48к1', 'amis' ); ?></span>
		<div class="tb-r">
			<a href="<?php echo esc_url( home_url( '/verification/' ) ); ?>"><?php esc_html_e( 'Поверка и калибровка', 'amis' ); ?></a>
			<a href="<?php echo esc_url( home_url( '/rent/' ) ); ?>"><?php esc_html_e( 'Аренда приборов', 'amis' ); ?></a>
			<a href="<?php echo esc_url( home_url( '/payment/' ) ); ?>"><?php esc_html_e( 'Оплата для юрлиц', 'amis' ); ?></a>
		</div>
	</div>
</div>

<!-- Основная шапка -->
<header class="header">
	<div class="wrap">

		<a class="logo" href="<?php echo esc_url( home_url( '/' ) ); ?>">
			<svg viewBox="0 0 64 64" fill="none" aria-hidden="true">
				<circle cx="32" cy="32" r="29" stroke="#D9673C" stroke-width="2.6"/>
				<path d="M9 40c5-1 7-16 11-16s5 12 8 12 4-18 9-18 6 17 10 20" stroke="#D9673C" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"/>
			</svg>
			<span>
				<b><?php esc_html_e( 'АМИС', 'amis' ); ?></b>
				<span><?php esc_html_e( 'групп', 'amis' ); ?></span>
			</span>
		</a>

		<a class="catbtn" href="<?php echo esc_url( amis_shop_url() ); ?>">
			<svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
				<path d="M1 3h14M1 8h14M1 13h14"/>
			</svg>
			<span><?php esc_html_e( 'Каталог', 'amis' ); ?></span>
		</a>

		<?php
		/**
		 * Поиск. Если WooCommerce активен — ищем только по товарам
		 * (скрытое поле post_type=product), иначе обычный поиск по сайту.
		 */
		?>
		<form class="search" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
			<label class="screen-reader-text" for="amis-s"><?php esc_html_e( 'Поиск', 'amis' ); ?></label>
			<input
				id="amis-s"
				type="search"
				name="s"
				value="<?php echo esc_attr( get_search_query() ); ?>"
				placeholder="<?php esc_attr_e( 'Модель, серия или параметр: MSO-2104, 3 ГГц', 'amis' ); ?>"
			>
			<?php if ( class_exists( 'WooCommerce' ) ) : ?>
				<input type="hidden" name="post_type" value="product">
			<?php endif; ?>
			<button type="submit" aria-label="<?php esc_attr_e( 'Найти', 'amis' ); ?>">
				<svg width="19" height="19" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
					<circle cx="9" cy="9" r="6.5"/><path d="m14 14 4.5 4.5"/>
				</svg>
			</button>
		</form>

		<div class="hd-r">

			<a class="phone" href="tel:+74953637709">
				+7 (495) 363-77-09
				<span><?php esc_html_e( 'Инженер на линии', 'amis' ); ?></span>
			</a>

			<?php if ( class_exists( 'WooCommerce' ) ) : ?>
				<a class="ico" href="<?php echo esc_url( wc_get_cart_url() ); ?>" aria-label="<?php esc_attr_e( 'Корзина', 'amis' ); ?>">
					<svg width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
						<path d="M3 4h2l2 9h8l2-6H6"/><circle cx="8.5" cy="16" r="1.2"/><circle cx="14.5" cy="16" r="1.2"/>
					</svg>
					<?php
					// Счётчик показываем только если в корзине что-то есть.
					$count = amis_cart_count();
					if ( $count ) :
						?>
						<span class="badge"><?php echo esc_html( $count ); ?></span>
					<?php endif; ?>
				</a>
			<?php endif; ?>

			<button class="burger" aria-label="<?php esc_attr_e( 'Меню', 'amis' ); ?>" aria-expanded="false">
				<span></span><span></span><span></span>
			</button>

		</div>
	</div>
</header>

<!-- Меню -->
<nav class="nav" id="amis-nav">
	<div class="wrap">
		<?php
		/**
		 * Меню создаётся в админке: Внешний вид → Меню,
		 * область отображения «Основное меню».
		 *
		 * fallback_cb выводит категории товаров, пока меню не создано —
		 * чтобы навигация не пропадала на пустом сайте.
		 */
		wp_nav_menu( array(
			'theme_location' => 'primary',
			'container'      => false,
			'menu_class'     => '',
			'fallback_cb'    => 'amis_nav_fallback',
			'depth'          => 2,
		) );
		?>
	</div>
</nav>

<div id="content" class="site-content">
