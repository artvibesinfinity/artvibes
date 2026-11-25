<?php
get_header();

/* Hero slider: posts/videos ticked "Hiện ở slider", else latest posts with an image. */
$hero = av_ids(['post_type' => ['post', 'av_video'], 'posts_per_page' => 5, 'meta_key' => '_av_hero', 'meta_value' => '1']);
if (!$hero) $hero = av_ids(['posts_per_page' => 3, 'meta_key' => '_thumbnail_id']);
if ($hero) : ?>
<section class="av-hero" data-slider aria-roledescription="carousel">
	<div class="av-hero__track">
		<?php foreach ($hero as $i => $id) :
			$video = av_is_video($id);
			$cat = get_the_category($id); ?>
			<article class="av-hero__slide">
				<?php echo get_the_post_thumbnail($id, 'full', ['class' => 'av-hero__img', 'loading' => $i ? 'lazy' : 'eager', 'fetchpriority' => $i ? 'auto' : 'high']); ?>
				<div class="av-hero__text">
					<span class="av-hero__label"><?php echo esc_html($video ? 'Videos' : ($cat[0]->name ?? '')); ?></span>
					<h2 class="av-hero__title"><a href="<?php echo esc_url(get_permalink($id)); ?>"><b><?php echo esc_html(get_the_title($id)); ?></b>
						<?php if ($s = av_meta('sub', $id)) echo '<span>' . esc_html($s) . '</span>'; ?></a></h2>
					<a class="av-btn-ghost" href="<?php echo esc_url(get_permalink($id)); ?>"><?php echo esc_html($video ? 'Watch Now' : av__('Đọc tiếp')); ?></a>
				</div>
			</article>
		<?php endforeach; ?>
	</div>
	<?php if (count($hero) > 1) : ?>
		<div class="av-hero__nav"><button type="button" data-prev aria-label="<?php echo esc_attr(av__('Trước')); ?>">←</button><button type="button" data-next aria-label="<?php echo esc_attr(av__('Sau')); ?>">→</button></div>
	<?php endif; ?>
</section>
<?php endif;

/* Lead block (Xu hướng): 3 cards, then 2 small + 1 big + side ad. */
$tc = av_term(av_mod('av_trend_cat'));
$ids = $tc ? av_ids(['cat' => $tc->term_id, 'posts_per_page' => 6]) : [];
if ($ids) : ?>
<section class="av-wrap av-sec">
	<?php av_section_head($tc->name); ?>
	<div class="av-grid3"><?php foreach (array_slice($ids, 0, 3) as $id) echo av_card($id, ['excerpt' => true]); ?></div>
	<?php if (count($ids) > 3) :
		$side = av_mod('av_side_img'); ?>
		<div class="av-mosaic<?php echo $side ? ' has-ad' : ''; ?>">
			<div class="av-mosaic__small"><?php foreach (array_slice($ids, 3, 2) as $id) echo av_card($id, ['size' => 'medium_large']); ?></div>
			<?php if (isset($ids[5])) echo av_card($ids[5], ['class' => 'av-mosaic__big']); ?>
			<?php if ($side) : $u = av_mod('av_side_url'); ?>
				<aside class="av-mosaic__ad"><?php echo $u ? '<a href="' . esc_url($u) . '" target="_blank" rel="noopener sponsored">' : ''; ?><img src="<?php echo esc_url($side); ?>" alt="<?php echo esc_attr(av__('Quảng cáo')); ?>" loading="lazy"><?php echo $u ? '</a>' : ''; ?></aside>
			<?php endif; ?>
		</div>
	<?php endif; ?>
</section>
<?php endif;

/* Category sections: feature + 3 cards. Portraits follow the first one. */
$first = true;
foreach (array_filter(array_map('trim', explode(',', av_mod('av_home_cats')))) as $slug) :
	$c = av_term($slug);
	$ids = $c ? av_ids(['cat' => $c->term_id, 'posts_per_page' => 4]) : [];
	if (!$ids) continue; ?>
	<section class="av-wrap av-sec">
		<?php av_section_head($c->name, get_category_link($c), av_more_label($c->name)); ?>
		<?php echo av_feature($ids[0]); ?>
		<?php if (count($ids) > 1) : ?><div class="av-grid3"><?php foreach (array_slice($ids, 1) as $id) echo av_card($id, ['excerpt' => true, 'tags' => false]); ?></div><?php endif; ?>
	</section>
	<?php if ($first && ($people = av_ids(['post_type' => 'av_portrait', 'posts_per_page' => 2]))) : ?>
		<section class="av-wrap av-people"><?php foreach ($people as $id) get_template_part('parts/person', null, ['id' => $id]); ?></section>
	<?php endif;
	$first = false;
endforeach;

$videos = av_videos(6);
if ($videos) : ?>
	<section class="av-wrap av-sec">
		<?php av_section_head('Videos', get_post_type_archive_link('av_video'), av__('Thêm Videos')); ?>
		<?php av_rail($videos); ?>
	</section>
<?php endif;

$shorts = av_videos(10, true);
if ($shorts) : ?>
	<section class="av-wrap av-sec">
		<?php av_section_head('Short videos', get_term_link(av_term('short-video', 'av_video_type')), av__('Thêm Short videos')); ?>
		<?php av_rail($shorts, true); ?>
	</section>
<?php endif;

get_footer();
