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
			<?php echo amis_phone_link( 'msk' ); // phpcs:ignore WordPress.Security.EscapeOutput — экранирование внутри функции. ?>
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
			<img src="<?php echo esc_url( AMIS_URI . '/assets/img/logo-amis.png' ); ?>" alt="<?php esc_attr_e( 'АМИС групп', 'amis' ); ?>">
		</a>

		<div class="cat-drop">
			<a class="catbtn" href="<?php echo esc_url( amis_shop_url() ); ?>">
				<svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
					<path d="M1 3h14M1 8h14M1 13h14"/>
				</svg>
				<span><?php esc_html_e( 'Каталог', 'amis' ); ?></span>
			</a>

			<?php
			/**
			 * Мега-меню категорий при наведении — только для десктопа
			 * (см. .cat-panel в base.css). На мобильном кнопка остаётся
			 * обычной ссылкой в каталог, без панели.
			 */
			$cat_panel_items = function_exists( 'amis_get_top_categories' ) ? amis_get_top_categories( 10 ) : array();
			if ( $cat_panel_items ) :
				?>
				<div class="cat-panel">
					<ul>
						<?php foreach ( $cat_panel_items as $cat ) : ?>
							<li><a href="<?php echo esc_url( get_term_link( $cat ) ); ?>"><?php echo esc_html( $cat->name ); ?></a></li>
						<?php endforeach; ?>
					</ul>
					<a class="cat-panel__all" href="<?php echo esc_url( amis_shop_url() ); ?>">
						<?php esc_html_e( 'Все категории', 'amis' ); ?>
						<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
					</a>
				</div>
			<?php endif; ?>
		</div>

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

			<a class="phone" href="tel:<?php echo esc_attr( amis_phone( 'free' )['href'] ); ?>">
				<?php echo esc_html( amis_phone( 'free' )['display'] ); ?>
				<span><?php echo esc_html( amis_phone( 'free' )['note'] ); ?></span>
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
