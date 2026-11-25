<?php
/** Videos page (also used for each "Loại video" term) — layout from the "VIDEOS" frame. */
get_header();
$current = is_tax('av_video_type') ? get_queried_object() : null;
$is_short = $current && $current->slug === 'short-video';

$items = [['Tất cả', get_post_type_archive_link('av_video'), !$current]];
foreach (get_terms(['taxonomy' => 'av_video_type', 'hide_empty' => false, 'slug__not_in' => ['video']]) as $t) {
	$items[] = [$t->name, get_term_link($t), $current && $current->term_id === $t->term_id];
}
?>
<div class="av-page-head">
	<h1 class="av-h1">Videos</h1>
	<?php av_chips($items); ?>
</div>

<section class="av-wrap av-sec">
	<?php av_section_head($current ? $current->name : 'Videos'); ?>
	<?php if (have_posts()) : ?>
		<div class="<?php echo $is_short ? 'av-grid5' : 'av-grid3'; ?>">
			<?php while (have_posts()) : the_post(); echo $is_short ? av_short(get_the_ID()) : av_card(get_the_ID(), ['size' => 'medium_large']); endwhile; ?>
		</div>
		<div class="av-pages"><?php echo paginate_links(['prev_text' => '←', 'next_text' => '→']); ?></div>
	<?php else : ?>
		<p class="av-empty">Chưa có video.</p>
	<?php endif; ?>
</section>

<?php if (!$is_short && ($shorts = av_videos(10, true))) : ?>
	<?php av_banner(); ?>
	<section class="av-wrap av-sec">
		<?php av_section_head('Short videos'); ?>
		<div class="av-grid5"><?php foreach ($shorts as $id) echo av_short($id); ?></div>
	</section>
<?php endif;

get_footer();
