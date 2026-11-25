<?php
/** Article — layout from the "XU HƯỚNG - TRANG CON" frame. */
get_header();
the_post();
$id = get_the_ID();
$url = get_permalink();
$cats = get_the_category();
$cat = $cats[0] ?? null;
$share = rawurlencode($url);
?>
<aside class="av-sharebar" aria-label="<?php echo esc_attr(av__('Chia sẻ')); ?>">
	<a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo $share; ?>" target="_blank" rel="noopener" aria-label="<?php echo esc_attr(av__('Chia sẻ Facebook')); ?>">f</a>
	<a href="https://pinterest.com/pin/create/button/?url=<?php echo $share; ?>" target="_blank" rel="noopener" aria-label="Pinterest">P</a>
	<button type="button" onclick="print()" aria-label="<?php echo esc_attr(av__('In bài')); ?>">⎙</button>
</aside>

<article class="av-article">
	<header class="av-article__head">
		<nav class="av-crumbs" aria-label="Breadcrumb">
			<a href="<?php echo esc_url(av_home()); ?>" aria-label="<?php echo esc_attr(av__('Trang chủ')); ?>">⌂</a>
			<?php if ($cat) {
				if ($cat->parent) echo '<a href="' . esc_url(get_category_link($cat->parent)) . '">' . esc_html(get_cat_name($cat->parent)) . '</a>';
				echo '<a href="' . esc_url(get_category_link($cat)) . '">' . esc_html($cat->name) . '</a>';
			} ?>
		</nav>
		<h1 class="av-article__title"><?php the_title(); ?><?php if ($s = av_meta('sub')) echo '<span>' . esc_html($s) . '</span>'; ?></h1>
		<?php if (has_excerpt()) : ?><p class="av-article__lead"><?php echo esc_html(get_the_excerpt()); ?></p><?php endif; ?>
	</header>

	<div class="av-article__meta">
		<dl>
			<dt>Published</dt><dd><?php echo esc_html(get_the_date('d/m/Y')); ?></dd>
			<?php if ($w = av_meta('words')) : ?><dt>Words</dt><dd><?php echo esc_html($w); ?></dd><?php endif; ?>
			<?php if ($p = av_meta('photo')) : ?><dt>Photography</dt><dd><?php echo esc_html($p); ?></dd><?php endif; ?>
			<dt>Share</dt>
			<dd class="av-share">
				<button type="button" data-copy="<?php echo esc_url($url); ?>" aria-label="<?php echo esc_attr(av__('Sao chép link')); ?>">🔗</button>
				<a href="mailto:?subject=<?php echo rawurlencode(get_the_title()); ?>&amp;body=<?php echo $share; ?>" aria-label="<?php echo esc_attr(av__('Gửi email')); ?>">✉</a>
				<button type="button" data-share="<?php echo esc_url($url); ?>" aria-label="<?php echo esc_attr(av__('Chia sẻ')); ?>">➦</button>
			</dd>
		</dl>
		<?php if ($intro = av_meta('intro')) : ?><p><?php echo esc_html($intro); ?></p><?php endif; ?>
	</div>

	<?php if ($v = av_meta('video')) : ?>
		<div class="av-article__media av-embed"><?php echo wp_oembed_get($v) ?: '<a href="' . esc_url($v) . '">' . esc_html($v) . '</a>'; ?></div>
	<?php elseif (has_post_thumbnail()) : ?>
		<figure class="av-article__media"><?php the_post_thumbnail('full'); ?></figure>
	<?php endif; ?>

	<div class="av-article__body"><?php the_content(); ?></div>

	<?php if ($credits = av_lines(av_meta('credits'))) : ?>
		<dl class="av-credits"><?php foreach ($credits as [$role, $name]) echo '<div><dt>' . esc_html($role) . '</dt><dd>' . esc_html($name) . '</dd></div>'; ?></dl>
	<?php endif; ?>
</article>

<?php
$related = $cat ? av_ids(['cat' => $cat->parent ?: $cat->term_id, 'posts_per_page' => 4, 'post__not_in' => [$id]]) : [];
$trending = av_ids(['post_type' => ['post', 'av_video'], 'posts_per_page' => 4, 'post__not_in' => array_merge([$id], $related), 'orderby' => 'comment_count date']);
foreach (['Related Articles' => $related, 'Trending now' => $trending] as $label => $list) :
	if (!$list) continue; ?>
	<section class="av-wrap av-wrap--mid av-related">
		<h2 class="av-related__h"><?php echo esc_html($label); ?></h2>
		<div class="av-grid4"><?php foreach ($list as $rid) echo av_card($rid, ['size' => 'medium_large']); ?></div>
	</section>
<?php endforeach; ?>

<section class="av-wrap av-wrap--mid av-subscribe">
	<?php if ($cat) : ?><a class="av-loadmore" href="<?php echo esc_url(get_category_link($cat)); ?>"><?php echo esc_html(av__('Xem thêm →')); ?></a><?php endif; ?>
	<p>Subscribe to our mailing list for the best of local architecture, interiors and design delivered to your inbox.</p>
	<?php av_subscribe_form(); ?>
</section>
<?php get_footer();
