<?php
/**
 * Подвал сайта.
 *
 * Парный файл к header.php: раз мы открыли теги в шапке,
 * закрыть их обязаны здесь. Иначе разметка развалится.
 *
 * @package amis
 */

defined( 'ABSPATH' ) || exit;
?>
</div><!-- /#content -->

<footer class="footer">
	<div class="wrap">
		<div class="f-grid">

			<div>
				<a class="logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" style="margin-bottom:18px">
					<svg viewBox="0 0 64 64" fill="none" aria-hidden="true">
						<circle cx="32" cy="32" r="29" stroke="#D9673C" stroke-width="2.6"/>
						<path d="M9 40c5-1 7-16 11-16s5 12 8 12 4-18 9-18 6 17 10 20" stroke="#D9673C" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"/>
					</svg>
					<span><b style="color:#fff"><?php esc_html_e( 'АМИС', 'amis' ); ?></b><span><?php esc_html_e( 'групп', 'amis' ); ?></span></span>
				</a>
				<p><?php esc_html_e( 'ООО «АМИС ГРУПП» — поставщик радиоизмерительного и контрольно-измерительного оборудования для лабораторий, КБ и производств. Работаем по всей России.', 'amis' ); ?></p>
				<div class="f-badges">
					<span class="f-badge"><?php esc_html_e( 'Поверка в комплекте', 'amis' ); ?></span>
					<span class="f-badge"><?php esc_html_e( 'Склад в Москве', 'amis' ); ?></span>
					<span class="f-badge"><?php esc_html_e( 'Гарантия до 3 лет', 'amis' ); ?></span>
				</div>
			</div>

			<div>
				<h4><?php esc_html_e( 'Каталог', 'amis' ); ?></h4>
				<ul>
					<?php
					// Категории тянем из базы: добавили новую — появилась в подвале.
					foreach ( amis_get_top_categories( 6 ) as $cat ) :
						?>
						<li>
							<a href="<?php echo esc_url( get_term_link( $cat ) ); ?>">
								<?php echo esc_html( $cat->name ); ?>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>

			<div>
				<h4><?php esc_html_e( 'Услуги', 'amis' ); ?></h4>
				<ul>
					<li><a href="<?php echo esc_url( home_url( '/demo/' ) ); ?>"><?php esc_html_e( 'Прибор на тест', 'amis' ); ?></a></li>
					<li><a href="<?php echo esc_url( home_url( '/verification/' ) ); ?>"><?php esc_html_e( 'Поверка и калибровка', 'amis' ); ?></a></li>
					<li><a href="<?php echo esc_url( home_url( '/rent/' ) ); ?>"><?php esc_html_e( 'Аренда приборов', 'amis' ); ?></a></li>
					<li><a href="<?php echo esc_url( home_url( '/trade-in/' ) ); ?>"><?php esc_html_e( 'Trade-in', 'amis' ); ?></a></li>
					<li><a href="<?php echo esc_url( home_url( '/delivery/' ) ); ?>"><?php esc_html_e( 'Доставка', 'amis' ); ?></a></li>
					<li><a href="<?php echo esc_url( home_url( '/payment/' ) ); ?>"><?php esc_html_e( 'Оплата для юрлиц', 'amis' ); ?></a></li>
				</ul>
			</div>

			<div>
				<h4><?php esc_html_e( 'Контакты', 'amis' ); ?></h4>
				<a class="f-phone" href="tel:+74957700497">+7 (495) 770-04-97</a>
				<ul>
					<li><a href="mailto:info@amisgr.ru">info@amisgr.ru</a></li>
					<li><?php esc_html_e( '127566, Москва, Алтуфьевское шоссе, 48к1', 'amis' ); ?></li>
					<li><?php esc_html_e( 'Пн–Пт 09:00–18:00', 'amis' ); ?></li>
				</ul>
			</div>

		</div>

		<p class="f-note">
			<?php esc_html_e( 'Информация на сайте носит справочный характер и не является публичной офертой (ст. 437 ГК РФ). Наличие, цены и сроки поставки уточняйте у менеджеров.', 'amis' ); ?>
		</p>

		<div class="f-bot">
			<span>© 2022–<?php echo esc_html( gmdate( 'Y' ) ); ?> ООО «АМИС ГРУПП» · ОГРН 1227700869440</span>
			<span>
				<a href="<?php echo esc_url( home_url( '/privacy/' ) ); ?>"><?php esc_html_e( 'Политика конфиденциальности', 'amis' ); ?></a>
			</span>
		</div>
	</div>
</footer>

<?php wp_footer(); // Обязательно: сюда выводятся скрипты из подвала. ?>
</body>
</html>
