<?php
/** One video: player (oEmbed from the link) or cover image, text, more videos. */
get_header();
the_post();
$v = av_meta('video');
$types = get_the_terms(get_the_ID(), 'av_video_type');
?>
<article class="av-article">
	<header class="av-article__head">
		<nav class="av-crumbs" aria-label="Breadcrumb">
			<a href="<?php echo esc_url(av_home()); ?>" aria-label="<?php echo esc_attr(av__('Trang chủ')); ?>">⌂</a>
			<a href="<?php echo esc_url(get_post_type_archive_link('av_video')); ?>">Videos</a>
			<?php if ($types && !is_wp_error($types)) echo '<a href="' . esc_url(get_term_link($types[0])) . '">' . esc_html($types[0]->name) . '</a>'; ?>
		</nav>
		<h1 class="av-article__title"><?php the_title(); ?><?php if ($s = av_meta('sub')) echo '<span>' . esc_html($s) . '</span>'; ?></h1>
	</header>
	<div class="av-article__media av-embed">
		<?php echo $v && ($embed = wp_oembed_get($v)) ? $embed : get_the_post_thumbnail(null, 'full'); ?>
		<?php if ($v && empty($embed)) echo '<p><a class="av-btn-ghost av-btn-ghost--dark" href="' . esc_url($v) . '" target="_blank" rel="noopener">' . esc_html(av__('Xem video ↗')) . '</a></p>'; ?>
	</div>
	<div class="av-article__body"><?php the_content(); ?><?php echo av_tags(get_the_ID(), 5); ?></div>
</article>

<?php if ($more = av_ids(['post_type' => 'av_video', 'posts_per_page' => 6, 'post__not_in' => [get_the_ID()]])) : ?>
	<section class="av-wrap av-sec">
		<?php av_section_head(av__('Videos khác'), get_post_type_archive_link('av_video'), av__('Thêm Videos')); ?>
		<?php av_rail($more); ?>
	</section>
<?php endif;
get_footer();
