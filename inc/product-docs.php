<?php
/**
 * Документация товара: медиабиблиотека вместо ручных ссылок.
 *
 * Файл сам считает размер и дату по вложению, а если PDF удалили
 * из медиабиблиотеки — просто не покажет строку вместо битой ссылки.
 *
 * @package amis
 */

defined( 'ABSPATH' ) || exit;

/**
 * Типы документов.
 *
 * Ключ становится частью имени меты: _amis_doc_{ключ}.
 * Фиксированный список нужен, чтобы на всех товарах документы
 * назывались одинаково — при свободном вводе через полгода
 * появятся и «Руководство», и «Мануал», и «Инструкция».
 *
 * @return array
 */
function amis_doc_types() {

	return array(
		'datasheet' => array(
			'label' => 'Техническое описание',
			'group' => 'Техническая документация',
			'icon'  => 'file',
			'lang'  => 'English',
		),
		'manual_ru' => array(
			'label' => 'Руководство по эксплуатации (рус.)',
			'group' => 'Руководства',
			'icon'  => 'book',
			'lang'  => 'Русский',
		),
		'manual' => array(
			'label' => 'Руководство по эксплуатации',
			'group' => 'Руководства',
			'icon'  => 'book',
			'lang'  => 'English',
		),
		'quickstart' => array(
			'label' => 'Быстрый старт',
			'group' => 'Руководства',
			'icon'  => 'zap',
			'lang'  => 'Русский',
		),
		'programming' => array(
			'label' => 'Руководство по программированию',
			'group' => 'Руководства',
			'icon'  => 'code',
			'lang'  => 'English',
		),
		'type_description' => array(
			'label' => 'Описание типа СИ',
			'group' => 'Метрология',
			'icon'  => 'file',
			'lang'  => 'Русский',
		),
		'verification' => array(
			'label' => 'Методика поверки',
			'group' => 'Метрология',
			'icon'  => 'file',
			'lang'  => 'Русский',
		),
		'certificate' => array(
			'label' => 'Сертификат соответствия',
			'group' => 'Метрология',
			'icon'  => 'file',
			'lang'  => 'Русский',
		),
	);
}

/* ==========================================================================
   АДМИНКА
   ========================================================================== */

/**
 * Вкладка «Документация» в блоке данных товара.
 *
 * @param array $tabs Вкладки.
 * @return array
 */
function amis_docs_tab( $tabs ) {

	$tabs['amis_docs'] = array(
		'label'    => __( 'Документация', 'amis' ),
		'target'   => 'amis_docs_data',
		'class'    => array(),
		'priority' => 26,
	);

	return $tabs;
}
add_filter( 'woocommerce_product_data_tabs', 'amis_docs_tab' );

/**
 * Содержимое вкладки.
 */
function amis_docs_panel() {

	global $post;

	wp_nonce_field( 'amis_docs_save', 'amis_docs_nonce' );
	?>
	<div id="amis_docs_data" class="panel woocommerce_options_panel hidden">
		<div class="options_group amis-docs-fields">

			<?php foreach ( amis_doc_types() as $key => $type ) : ?>
				<?php
				$id_field   = '_amis_doc_' . $key;
				$lang_field = $id_field . '_lang';

				$attachment_id = (int) get_post_meta( $post->ID, $id_field, true );
				$lang          = get_post_meta( $post->ID, $lang_field, true );

				if ( '' === $lang ) {
					$lang = $type['lang'];
				}

				// Показываем имя файла и размер, чтобы было видно, что прикреплено.
				$filename = '';

				if ( $attachment_id ) {
					$path = get_attached_file( $attachment_id );

					$filename = ( $path && file_exists( $path ) )
						? basename( $path ) . ' — ' . size_format( filesize( $path ), 1 )
						: '⚠ файл не найден (ID ' . $attachment_id . ')';
				}
				?>

				<div class="amis-doc-row">
					<span class="amis-doc-row__label"><?php echo esc_html( $type['label'] ); ?></span>

					<div class="amis-doc-row__controls">
						<input
							type="hidden"
							name="<?php echo esc_attr( $id_field ); ?>"
							value="<?php echo esc_attr( $attachment_id ); ?>"
							class="amis-doc-id"
						>

						<button type="button" class="button amis-doc-select"><?php esc_html_e( 'Выбрать PDF', 'amis' ); ?></button>

						<button type="button" class="button-link amis-doc-remove" <?php echo $attachment_id ? '' : 'style="display:none"'; ?>>
							<?php esc_html_e( 'Убрать', 'amis' ); ?>
						</button>

						<select name="<?php echo esc_attr( $lang_field ); ?>" class="amis-doc-lang">
							<option value="Русский" <?php selected( $lang, 'Русский' ); ?>>Русский</option>
							<option value="English" <?php selected( $lang, 'English' ); ?>>English</option>
						</select>

						<span class="amis-doc-filename"><?php echo esc_html( $filename ); ?></span>
					</div>
				</div>

			<?php endforeach; ?>

		</div>
	</div>

	<style>
.amis-doc-row{padding:14px 12px;border-bottom:1px solid #f0f0f1}
.amis-doc-row:last-child{border-bottom:0}
.amis-doc-row__label{
  display:block;
  float:none;
  width:auto;
  margin:0 0 8px;
  padding:0;
  font-weight:600;
  line-height:1.4;
}
.amis-doc-row__controls{display:flex;align-items:center;flex-wrap:wrap;gap:8px}
.amis-doc-filename{color:#646970;font-size:13px}
.amis-doc-remove{color:#b32d2e}
	</style>
	<?php
}
add_action( 'woocommerce_product_data_panels', 'amis_docs_panel' );

/**
 * Подключение медиазагрузчика — только на экране товара.
 *
 * wp_enqueue_media() тянет за собой всю библиотеку медиафайлов,
 * поэтому грузим её адресно, а не на всю админку.
 *
 * @param string $hook Текущий экран.
 */
function amis_docs_admin_assets( $hook ) {

	if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
		return;
	}

	global $post;

	if ( ! $post || 'product' !== $post->post_type ) {
		return;
	}

	wp_enqueue_media();

	$rel  = '/assets/js/admin/product-docs.js';
	$path = AMIS_DIR . $rel;

	if ( ! file_exists( $path ) ) {
		return;
	}

	wp_enqueue_script(
		'amis-admin-docs',
		AMIS_URI . $rel,
		array( 'jquery' ),
		filemtime( $path ), // Версия по времени файла: правки видны сразу.
		true
	);
}
add_action( 'admin_enqueue_scripts', 'amis_docs_admin_assets' );

/**
 * Сохранение.
 *
 * @param int $post_id ID товара.
 */
function amis_docs_save( $post_id ) {

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! isset( $_POST['amis_docs_nonce'] )
		|| ! wp_verify_nonce( sanitize_key( $_POST['amis_docs_nonce'] ), 'amis_docs_save' ) ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	foreach ( array_keys( amis_doc_types() ) as $key ) {

		$id_field   = '_amis_doc_' . $key;
		$lang_field = $id_field . '_lang';

		$value = isset( $_POST[ $id_field ] ) ? absint( $_POST[ $id_field ] ) : 0;

		if ( $value ) {
			update_post_meta( $post_id, $id_field, $value );
		} else {
			delete_post_meta( $post_id, $id_field ); // Пустое поле не храним.
		}

		$lang = isset( $_POST[ $lang_field ] ) ? sanitize_text_field( wp_unslash( $_POST[ $lang_field ] ) ) : '';

		if ( in_array( $lang, array( 'Русский', 'English' ), true ) ) {
			update_post_meta( $post_id, $lang_field, $lang );
		}
	}
}
add_action( 'save_post_product', 'amis_docs_save' );

/* ==========================================================================
   ВИТРИНА
   ========================================================================== */

/**
 * Документы товара, сгруппированные по разделам.
 *
 * @param int $product_id ID товара.
 * @return array Пустой массив, если документов нет.
 */
function amis_get_product_docs( $product_id ) {

	$groups = array();

	foreach ( amis_doc_types() as $key => $type ) {

		$attachment_id = (int) get_post_meta( $product_id, '_amis_doc_' . $key, true );

		if ( ! $attachment_id ) {
			continue;
		}

		$path = get_attached_file( $attachment_id );

		// Файл удалили из медиабиблиотеки — пропускаем, чтобы не давать битую ссылку.
		if ( ! $path || ! file_exists( $path ) ) {
			continue;
		}

		$lang = get_post_meta( $product_id, '_amis_doc_' . $key . '_lang', true );

		$groups[ $type['group'] ][] = array(
			'title' => $type['label'],
			'url'   => wp_get_attachment_url( $attachment_id ),
			'size'  => size_format( filesize( $path ), 1 ),
			'date'  => date_i18n( 'm.Y', filemtime( $path ) ),
			'lang'  => $lang ? $lang : $type['lang'],
			'icon'  => $type['icon'],
		);
	}

	return $groups;
}

/**
 * SVG-иконка по ключу.
 *
 * @param string $name Ключ иконки.
 * @return string
 */
function amis_doc_icon( $name ) {

	$icons = array(
		'file' => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/>',
		'book' => '<path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>',
		'code' => '<polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/>',
		'zap'  => '<polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>',
	);

	$path = isset( $icons[ $name ] ) ? $icons[ $name ] : $icons['file'];

	return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" '
		. 'stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $path . '</svg>';
}

/**
 * Вывод блока документов.
 *
 * Принимает массив параметром, а не лезет в глобальный $post —
 * так функцию можно вызвать откуда угодно, в том числе из каталога.
 *
 * @param array $groups Результат amis_get_product_docs().
 */
function amis_render_docs( $groups ) {

	if ( empty( $groups ) ) {
		return;
	}

	$download_icon = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" '
		. 'stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'
		. '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>'
		. '<polyline points="7 10 12 15 17 10"/>'
		. '<line x1="12" y1="15" x2="12" y2="3"/></svg>';
	?>
	<div class="docs-groups">

		<?php foreach ( $groups as $group_title => $items ) : ?>
			<div class="docs-group">
				<h3 class="docs-group__title"><?php echo esc_html( $group_title ); ?></h3>

				<ul class="docs-list">
					<?php foreach ( $items as $doc ) : ?>
						<li>
							<a class="docs-link" href="<?php echo esc_url( $doc['url'] ); ?>" download>
								<span class="docs-icon"><?php echo amis_doc_icon( $doc['icon'] ); // phpcs:ignore WordPress.Security.EscapeOutput — собственный SVG. ?></span>

								<span class="docs-body">
									<span class="docs-title"><?php echo esc_html( $doc['title'] ); ?></span>
									<span class="docs-meta">
										<span class="docs-badge">PDF</span>
										<span><?php echo esc_html( $doc['size'] ); ?></span>
										<span><?php echo esc_html( $doc['lang'] ); ?></span>
										<span><?php echo esc_html( $doc['date'] ); ?></span>
									</span>
								</span>

								<span class="docs-action"><?php echo $download_icon; // phpcs:ignore WordPress.Security.EscapeOutput — собственный SVG. ?></span>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php endforeach; ?>

		<p class="docs-note">
			<?php esc_html_e( 'Не нашли нужный документ —', 'amis' ); ?>
			<a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>"><?php esc_html_e( 'напишите нам', 'amis' ); ?></a>,
			<?php esc_html_e( 'пришлём.', 'amis' ); ?>
		</p>
	</div>
	<?php
}