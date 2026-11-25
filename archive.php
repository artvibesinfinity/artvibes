<?php
/** Category / tag archives — layout from the "XU HƯỚNG" frame. */
get_header();
$term = get_queried_object();
$ids = wp_list_pluck($GLOBALS['wp_query']->posts, 'ID');
?>
<div class="av-page-head">
	<h1 class="av-h1"><?php echo esc_html(is_category() || is_tag() || is_tax() ? $term->name : wp_strip_all_tags(get_the_archive_title())); ?></h1>
	<?php
	if (is_category()) {
		// Chips: children of this category, or its siblings when it has none.
		$kids = get_categories(['parent' => $term->term_id, 'hide_empty' => false, 'orderby' => 'term_id']);
		$root = $kids ? $term : ($term->parent ? get_category($term->parent) : null);
		if ($root) {
			$items = [['Tất cả', get_category_link($root), $root->term_id === $term->term_id]];
			foreach ($kids ?: get_categories(['parent' => $root->term_id, 'hide_empty' => false, 'orderby' => 'term_id']) as $k) {
				$items[] = [$k->name, get_category_link($k), $k->term_id === $term->term_id];
			}
			av_chips($items);
		}
	}
	?>
</div>

<?php if (!$ids) : ?>
	<p class="av-wrap av-empty">Chưa có bài viết trong mục này.</p>
<?php else : ?>
	<div class="av-wrap av-wrap--mid">
		<div class="av-grid2"><?php foreach (array_slice($ids, 0, 2) as $id) echo av_card($id, ['excerpt' => true]); ?></div>
		<?php if (isset($ids[2])) : ?><div class="av-grid3"><?php foreach (array_slice($ids, 2, 3) as $id) echo av_card($id, ['excerpt' => true, 'size' => 'medium_large']); ?></div><?php endif; ?>
	</div>
	<?php if (isset($ids[5])) : ?>
		<?php av_banner(); ?>
		<div class="av-wrap av-sec">
			<?php echo av_feature($ids[5]); ?>
			<?php if (isset($ids[6])) : ?><div class="av-grid3"><?php foreach (array_slice($ids, 6) as $id) echo av_card($id, ['excerpt' => true]); ?></div><?php endif; ?>
		</div>
	<?php endif; ?>
	<?php if ($GLOBALS['wp_query']->max_num_pages > max(1, get_query_var('paged'))) : ?>
		<div class="av-wrap av-loadmore"><a href="<?php echo esc_url(get_next_posts_page_link()); ?>">Xem thêm →</a></div>
	<?php endif; ?>
<?php endif;

get_footer();
