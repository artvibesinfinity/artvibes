<?php
/** Fallback: search results, blog index, 404. */
get_header();
$title = is_search() ? 'Kết quả cho “' . get_search_query() . '”' : (is_404() ? 'Không tìm thấy trang' : (get_the_title(get_option('page_for_posts')) ?: 'Bài viết'));
?>
<div class="av-page-head"><h1 class="av-h1"><?php echo esc_html($title); ?></h1></div>
<div class="av-wrap av-sec">
	<?php if (have_posts()) : ?>
		<div class="av-grid3"><?php while (have_posts()) : the_post(); echo av_card(get_the_ID(), ['excerpt' => true]); endwhile; ?></div>
		<div class="av-pages"><?php echo paginate_links(['prev_text' => '←', 'next_text' => '→']); ?></div>
	<?php else : ?>
		<p class="av-empty">Không có nội dung phù hợp. Thử tìm từ khóa khác:</p>
		<?php get_search_form(); ?>
	<?php endif; ?>
</div>
<?php get_footer();
