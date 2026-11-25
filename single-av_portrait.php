<?php
/** Portrait profile — layout from the "CHÂN DUNG" frame. */
get_header();
the_post();
$id = get_the_ID();
$info = av_lines(av_meta('info'));
$works = av_lines(av_meta('works'));
$gallery = array_filter(array_map('absint', explode(',', (string) av_meta('gallery'))));
?>
<div class="av-page-head" id="top">
	<h1 class="av-h1"><?php the_title(); ?></h1>
	<?php av_chips(array_filter([
		['Tổng quan', '#tong-quan', true],
		$info ? ['Thông tin cá nhân', '#thong-tin', false] : null,
		$works ? ['Tác phẩm', '#tac-pham', false] : null,
	])); ?>
</div>

<?php if ($info) : ?>
<section class="av-wrap av-sec av-split" id="thong-tin">
	<h2 class="av-h3">Thông tin cá nhân</h2>
	<div class="av-split__cols">
		<table class="av-info"><?php foreach ($info as [$k, $v]) echo '<tr><th>' . esc_html($k) . '</th><td>' . nl2br(esc_html(str_replace(' / ', "\n", $v))) . '</td></tr>'; ?></table>
		<?php echo av_img($id, 'large'); ?>
	</div>
</section>
<?php endif; ?>

<section class="av-wrap av-sec av-split" id="tong-quan">
	<h2 class="av-h3">Tổng quan</h2>
	<div class="av-prose"><?php the_content(); ?></div>
</section>

<?php if ($works) : ?>
<section class="av-wrap av-sec av-split" id="tac-pham">
	<h2 class="av-h3">Tác phẩm</h2>
	<div class="av-split__cols">
		<table class="av-works"><?php
			$last = null;
			foreach ($works as [$name, $year]) {
				echo '<tr' . ($year !== '' && $year !== $last ? ' class="is-new"' : '') . '><td>' . esc_html($name) . '</td><td>' . esc_html($year !== $last ? $year : '') . '</td></tr>';
				if ($year !== '') $last = $year;
			}
		?></table>
		<div class="av-split__imgs"><?php foreach ($gallery as $g) echo wp_get_attachment_image($g, 'large', false, ['loading' => 'lazy']); ?></div>
	</div>
</section>
<?php endif;

$tag = av_meta('tag');
$posts = av_ids(['posts_per_page' => 4] + ($tag ? ['tag' => $tag] : []));
if ($posts) : ?>
	<section class="av-wrap av-sec">
		<?php av_section_head('Bài viết'); ?>
		<div class="av-grid4 av-wrap--mid"><?php foreach ($posts as $p) echo av_card($p, ['size' => 'medium_large']); ?></div>
	</section>
<?php endif;

if ($v = av_videos(6)) : ?>
	<section class="av-wrap av-sec"><?php av_section_head('Videos', get_post_type_archive_link('av_video'), 'Thêm Videos'); av_rail($v); ?></section>
<?php endif;
if ($s = av_videos(10, true)) : ?>
	<section class="av-wrap av-sec"><?php av_section_head('Short videos'); av_rail($s, true); ?></section>
<?php endif; ?>
<div class="av-wrap av-loadmore"><a href="#top">Trở lại đầu trang ↑</a></div>
<?php get_footer();
