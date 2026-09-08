<?php
/**
 * Карточка статьи «Базы знаний».
 *
 * В отличие от template-parts/product-card.php вызывается только внутри
 * цикла WP_Query (the_post() уже отработал) — использует глобальный пост
 * через стандартные шаблонные теги, отдельные аргументы не нужны.
 *
 * @package amis
 */

defined( 'ABSPATH' ) || exit;

$categories  = get_the_category();
$primary_cat = $categories ? $categories[0] : null;
?>

<article class="article-card">

	<a class="article-card__img<?php echo has_post_thumbnail() ? '' : ' article-card__img--empty'; ?>" href="<?php the_permalink(); ?>">
		<?php if ( has_post_thumbnail() ) : ?>
			<?php the_post_thumbnail( 'medium_large' ); ?>
		<?php else : ?>
			<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
				<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="9" y1="13" x2="15" y2="13"/><line x1="9" y1="17" x2="15" y2="17"/>
			</svg>
		<?php endif; ?>
	</a>

	<div class="article-card__body">
		<?php if ( $primary_cat ) : ?>
			<span class="eyebrow article-card__cat"><?php echo esc_html( $primary_cat->name ); ?></span>
		<?php endif; ?>

		<h3 class="article-card__title">
			<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
		</h3>

		<p class="article-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 18 ) ); ?></p>

		<span class="article-card__meta"><?php echo esc_html( get_the_date() ); ?></span>
	</div>

</article>
