<?php
/**
 * Отдельная запись «Базы знаний».
 *
 * WordPress подхватывает single.php для всех одиночных записей
 * автоматически, без выбора в админке (как header.php/footer.php).
 * Раньше этого файла не было — записи рисовались шаблоном родительской
 * темы Astra, без стилей сайта. amis_page_builder_layout() и
 * amis_no_sidebar_layout() в inc/enqueue.php убирают контейнер и
 * сайдбар Astra на этой странице (см. inc/enqueue.php).
 *
 * @package amis
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();

	// «Misc» — служебная категория по умолчанию, не показываем её как тему статьи.
	$categories  = array_filter(
		get_the_category(),
		static function ( $cat ) {
			return 'misc' !== $cat->slug;
		}
	);
	$primary_cat = $categories ? reset( $categories ) : null;
	?>

	<nav class="crumbs">
		<div class="wrap">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Главная', 'amis' ); ?></a>
			<span>/</span>
			<a href="<?php echo esc_url( home_url( '/articles/' ) ); ?>"><?php esc_html_e( 'База знаний', 'amis' ); ?></a>
			<span>/</span>
			<span><?php the_title(); ?></span>
		</div>
	</nav>

	<!-- Шапка статьи -->
	<section class="section section--head">
		<div class="wrap">
			<?php if ( $primary_cat ) : ?>
				<span class="eyebrow"><?php echo esc_html( $primary_cat->name ); ?></span>
			<?php endif; ?>

			<h1><?php the_title(); ?></h1>

			<div class="article-meta">
				<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
			</div>

			<?php if ( has_post_thumbnail() ) : ?>
				<div class="article-cover">
					<?php the_post_thumbnail( 'large' ); ?>
				</div>
			<?php endif; ?>
		</div>
	</section>

	<!-- Текст статьи -->
	<section class="section">
		<div class="wrap">
			<div class="article-body">
				<?php
				the_content();

				wp_link_pages( array(
					'before' => '<nav class="article-pagination">',
					'after'  => '</nav>',
				) );
				?>
			</div>

			<?php
			/**
			 * Похожие статьи: три последних записи из той же категории,
			 * кроме текущей. Если категория не задана — три последних
			 * записи из «Базы знаний» вообще.
			 */
			$related_args = array(
				'post_type'           => 'post',
				'posts_per_page'      => 3,
				'post__not_in'        => array( get_the_ID() ),
				'ignore_sticky_posts' => true,
			);

			if ( $primary_cat ) {
				$related_args['cat'] = $primary_cat->term_id;
			}

			$related = new WP_Query( $related_args );

			if ( $related->have_posts() ) :
				?>
				<div class="article-related">
					<div class="s-head">
						<div>
							<h2><?php esc_html_e( 'Похожие статьи', 'amis' ); ?></h2>
						</div>
					</div>

					<div class="article-grid">
						<?php
						while ( $related->have_posts() ) :
							$related->the_post();
							get_template_part( 'template-parts/article-card' );
						endwhile;
						wp_reset_postdata();
						?>
					</div>
				</div>
			<?php endif; ?>
		</div>
	</section>

	<!-- CTA -->
	<section class="section">
		<div class="wrap d-cta">
			<div>
				<h2><?php esc_html_e( 'Не нашли ответ на свой вопрос?', 'amis' ); ?></h2>
				<p><?php esc_html_e( 'Напишите или позвоните — поможем подобрать прибор и ответим на технические вопросы.', 'amis' ); ?></p>
			</div>
			<div class="d-cta__actions">
				<?php echo amis_phone_link( 'free', 'btn btn-primary' ); // phpcs:ignore WordPress.Security.EscapeOutput -- экранирование внутри функции. ?>
				<a class="btn btn-ghost" href="<?php echo esc_url( home_url( '/contact/#write' ) ); ?>"><?php esc_html_e( 'Написать нам', 'amis' ); ?></a>
			</div>
		</div>
	</section>

	<?php
endwhile;

get_footer();
