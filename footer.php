<?php
/**
 * Подвал сайта.
 *
 * Парный файл к header.php: теги, открытые в шапке, закрываются здесь.
 *
 * @package amis
 */

defined( 'ABSPATH' ) || exit;
?>
</div><!-- /#content -->

<footer class="footer">
	<div class="wrap">

		<div class="f-grid">

			<!-- О компании -->
			<div class="f-col f-col--about">
				<h4 class="f-title"><?php esc_html_e( 'О компании', 'amis' ); ?></h4>

				<p class="f-about">
					<strong>ООО «АМИС ГРУПП»</strong> — поставщик радиоизмерительного
					и контрольно-измерительного оборудования для лабораторий, КБ
					и промышленных предприятий. Работаем по всей России с 2022 года.
				</p>

				<div class="f-badges">
					<span class="f-badge">
						<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
							<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/>
						</svg>
						<?php esc_html_e( 'Поверка через аккредитованные ЦСМ', 'amis' ); ?>
					</span>
					<span class="f-badge">
						<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
							<rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
						</svg>
						<?php esc_html_e( 'Склад в Москве', 'amis' ); ?>
					</span>
					<span class="f-badge">
						<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
							<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
						</svg>
						<?php esc_html_e( 'Работаем по 44-ФЗ и 223-ФЗ', 'amis' ); ?>
					</span>
				</div>

				<div class="f-actions">
					<a class="f-btn f-btn--primary" href="<?php echo esc_url( home_url( '/documents/rekvizity.pdf' ) ); ?>" download>
						<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
							<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
							<polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/>
						</svg>
						<?php esc_html_e( 'Карточка предприятия', 'amis' ); ?>
					</a>
					<a class="f-btn" href="<?php echo esc_url( home_url( '/about/' ) ); ?>">
						<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
							<circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/>
						</svg>
						<?php esc_html_e( 'О компании', 'amis' ); ?>
					</a>
					<a class="f-btn" href="<?php echo esc_url( home_url( '/documents/' ) ); ?>">
						<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
							<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/>
						</svg>
						<?php esc_html_e( 'Уставные документы', 'amis' ); ?>
					</a>
				</div>
			</div>

			<!-- Контакты -->
			<div class="f-col">
				<h4 class="f-title"><?php esc_html_e( 'Контакты', 'amis' ); ?></h4>

				<div class="f-contact">
					<span class="f-ico">
						<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
							<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
						</svg>
					</span>
					<div class="f-phones">
						<?php
						// Основной номер крупнее, городской — справочно.
						$phone_classes = array(
							'free' => 'f-phone',
							'msk'  => 'f-phone f-phone--sec',
						);

						foreach ( amis_company_phones() as $key => $phone ) :
							?>
							<a class="<?php echo esc_attr( $phone_classes[ $key ] ); ?>" href="tel:<?php echo esc_attr( $phone['href'] ); ?>">
								<?php echo esc_html( $phone['display'] ); ?>
							</a>
							<span class="f-note"><?php echo esc_html( $phone['note'] ); ?></span>
						<?php endforeach; ?>
					</div>
				</div>

				<div class="f-contact">
					<span class="f-ico">
						<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
							<path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/>
						</svg>
					</span>
					<div>
						<a class="f-link-strong" href="mailto:<?php echo esc_attr( amis_company_email() ); ?>">
							<?php echo esc_html( amis_company_email() ); ?>
						</a>
						<span class="f-note"><?php esc_html_e( 'Ответим в течение 2 часов', 'amis' ); ?></span>
					</div>
				</div>

				<div class="f-contact">
					<span class="f-ico">
						<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
							<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>
						</svg>
					</span>
					<div>
						<span class="f-text">127566, Москва,<br>Алтуфьевское шоссе, 48к1</span>
						<a class="f-note-link" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">
							<?php esc_html_e( 'Показать на карте', 'amis' ); ?> →
						</a>
					</div>
				</div>

				<div class="f-contact">
					<span class="f-ico">
						<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
							<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
						</svg>
					</span>
					<div>
						<span class="f-text">
							<strong><?php esc_html_e( 'Пн–Пт:', 'amis' ); ?></strong> 09:00–18:00<br>
							<strong><?php esc_html_e( 'Сб–Вс:', 'amis' ); ?></strong> <?php esc_html_e( 'выходной', 'amis' ); ?>
						</span>
					</div>
				</div>
			</div>

			<!-- Информация -->
			<div class="f-col">
				<h4 class="f-title"><?php esc_html_e( 'Информация', 'amis' ); ?></h4>

				<div class="f-group">
					<h5 class="f-subtitle"><?php esc_html_e( 'Покупателям', 'amis' ); ?></h5>
					<ul class="f-links">
						<li><a href="<?php echo esc_url( home_url( '/how-to-order/' ) ); ?>"><?php esc_html_e( 'Как сделать заказ', 'amis' ); ?></a></li>
						<li><a href="<?php echo esc_url( home_url( '/delivery/#delivery' ) ); ?>"><?php esc_html_e( 'Условия доставки', 'amis' ); ?></a></li>
						<li><a href="<?php echo esc_url( home_url( '/payment/#payment' ) ); ?>"><?php esc_html_e( 'Оплата для юридических лиц', 'amis' ); ?></a></li>
						<li><a href="<?php echo esc_url( home_url( '/warranty/' ) ); ?>"><?php esc_html_e( 'Гарантия и возврат', 'amis' ); ?></a></li>
						<li><a href="<?php echo esc_url( home_url( '/tenders/' ) ); ?>"><?php esc_html_e( 'Работа по 44-ФЗ и 223-ФЗ', 'amis' ); ?></a></li>
					</ul>
				</div>

				<div class="f-group">
					<h5 class="f-subtitle"><?php esc_html_e( 'Услуги', 'amis' ); ?></h5>
					<ul class="f-links">
						<li><a href="<?php echo esc_url( home_url( '/demo/' ) ); ?>"><?php esc_html_e( 'Прибор на тест', 'amis' ); ?></a></li>
						<li><a href="<?php echo esc_url( home_url( '/verification/' ) ); ?>"><?php esc_html_e( 'Поверка и калибровка', 'amis' ); ?></a></li>
						<li><a href="<?php echo esc_url( home_url( '/rent/' ) ); ?>"><?php esc_html_e( 'Аренда приборов', 'amis' ); ?></a></li>
						<li><a href="<?php echo esc_url( home_url( '/trade-in/' ) ); ?>"><?php esc_html_e( 'Trade-in старого парка', 'amis' ); ?></a></li>
					</ul>
				</div>

				<div class="f-group">
					<h5 class="f-subtitle"><?php esc_html_e( 'Помощь', 'amis' ); ?></h5>
					<ul class="f-links">
						<li><a href="<?php echo esc_url( home_url( '/faq/' ) ); ?>"><?php esc_html_e( 'Вопросы и ответы', 'amis' ); ?></a></li>
						<li><a href="<?php echo esc_url( home_url( '/articles/' ) ); ?>"><?php esc_html_e( 'База знаний', 'amis' ); ?></a></li>
						<li><a href="<?php echo esc_url( home_url( '/vacancies/' ) ); ?>"><?php esc_html_e( 'Вакансии', 'amis' ); ?></a></li>
						<li><a href="<?php echo esc_url( home_url( '/privacy/' ) ); ?>"><?php esc_html_e( 'Политика конфиденциальности', 'amis' ); ?></a></li>
					</ul>
				</div>
			</div>

			<!-- Каталог -->
			<div class="f-col">
				<h4 class="f-title"><?php esc_html_e( 'Каталог', 'amis' ); ?></h4>

				<?php
				/**
				 * Иконки по слагу категории. Если категории в списке нет,
				 * рисуется нейтральная — новая категория не сломает вёрстку.
				 */
				$cat_icons = array(
					'oscilloscopes'      => '<rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/>',
					'spectrum-analyzers' => '<path d="M2 12h4l3-9 6 18 3-9h4"/>',
					'signal-generators'  => '<path d="M2 12c2-4 4-4 6 0s4 4 6 0 4-4 6 0"/>',
					'power-amplifiers'   => '<path d="M2 12h4M18 12h4"/><path d="M8 5l10 7-10 7z"/>',
					'power-supplies'     => '<path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/>',
					'multimeters'        => '<rect x="5" y="2" width="14" height="20" rx="2"/><path d="M9 6h6M9 17h2M13 17h2"/>',
				);

				$default_icon = '<circle cx="12" cy="12" r="9"/>';
				?>

				<ul class="f-links f-links--icons">
					<?php foreach ( amis_get_top_categories( 8 ) as $cat ) : ?>
						<?php $icon = isset( $cat_icons[ $cat->slug ] ) ? $cat_icons[ $cat->slug ] : $default_icon; ?>
						<li>
							<a href="<?php echo esc_url( get_term_link( $cat ) ); ?>">
								<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
									<?php echo $icon; // phpcs:ignore WordPress.Security.EscapeOutput — собственный SVG из массива выше. ?>
								</svg>
								<?php echo esc_html( $cat->name ); ?>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>

				<a class="f-btn f-btn--primary f-btn--wide" href="<?php echo esc_url( amis_shop_url() ); ?>">
					<?php esc_html_e( 'Весь каталог', 'amis' ); ?>
					<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
						<line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/>
					</svg>
				</a>
			</div>

		</div>

		<!-- Нижняя строка -->
		<div class="f-bottom">
			<div class="f-bottom__row">
				<span>© 2022–<?php echo esc_html( gmdate( 'Y' ) ); ?> ООО «АМИС ГРУПП»</span>
				<span>ИНН 9715435990 · ОГРН 1227700869440</span>
			</div>

			<p class="f-legal">
				<?php esc_html_e( 'Информация на сайте носит справочный характер и не является публичной офертой (ст. 437 ГК РФ). Наличие, цены и сроки поставки уточняйте у менеджеров. Товарные знаки принадлежат их правообладателям.', 'amis' ); ?>
			</p>
		</div>

	</div>
</footer>

<div class="mobar">
	<a href="tel:<?php echo esc_attr( amis_phone( 'free' )['href'] ); ?>"><?php esc_html_e( 'Позвонить', 'amis' ); ?></a>
	<a href="<?php echo esc_url( home_url( '/contact/#write' ) ); ?>"><?php esc_html_e( 'Написать', 'amis' ); ?></a>
</div>

<div class="cookie-notice" id="amis-cookie-notice" role="region" aria-label="<?php esc_attr_e( 'Уведомление о cookie', 'amis' ); ?>">
	<p>
		<?php
		printf(
			/* translators: %s — ссылка на политику конфиденциальности. */
			esc_html__( 'Сайт использует файлы cookie для корректной работы и статистики посещений. Подробнее — в %s.', 'amis' ),
			'<a href="' . esc_url( home_url( '/privacy/' ) ) . '">' . esc_html__( 'политике конфиденциальности', 'amis' ) . '</a>'
		); // phpcs:ignore WordPress.Security.EscapeOutput -- строка собрана из esc_html/esc_url выше.
		?>
	</p>
	<button type="button" class="btn btn-primary cookie-notice__ok" id="amis-cookie-ok">
		<?php esc_html_e( 'Понятно', 'amis' ); ?>
	</button>
</div>

<?php wp_footer(); ?>
</body>
</html>