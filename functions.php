<?php
/**
 * The Artvibes theme — front-end setup and template helpers.
 * Admin (post types, fields, settings, forms) lives in inc/admin.php, demo importer in inc/demo.php.
 */
define('AV_VER', '1.0.1');
define('AV_URI', get_template_directory_uri());

require __DIR__ . '/inc/i18n.php';
require __DIR__ . '/inc/admin.php';
require __DIR__ . '/inc/demo.php';

add_action('after_setup_theme', function () {
	add_theme_support('title-tag');
	add_theme_support('post-thumbnails');
	add_theme_support('custom-logo');
	add_theme_support('responsive-embeds');
	add_theme_support('align-wide');
	add_theme_support('html5', ['search-form', 'gallery', 'caption', 'style', 'script']);
	register_nav_menus([
		'primary' => 'Menu chính (header)',
		'footer'  => 'Menu chân trang (mỗi mục cấp 1 là một cột)',
	]);
});

add_action('wp_enqueue_scripts', function () {
	wp_enqueue_style('av-fonts', 'https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700&family=Atkinson+Hyperlegible+Next:wght@300;400;600;700&family=Lora:wght@400;500&family=Average&display=swap', [], null);
	wp_enqueue_style('artvibes', get_stylesheet_uri(), ['av-fonts'], AV_VER);
	wp_enqueue_script('artvibes', AV_URI . '/assets/main.js', [], AV_VER, true);
});

/** Category archives use the design's 15-post rhythm; video archive 9 videos per page. */
add_action('pre_get_posts', function ($q) {
	if (is_admin() || !$q->is_main_query()) return;
	if ($q->is_category()) $q->set('posts_per_page', 15);
	if ($q->is_post_type_archive('av_video') || $q->is_tax('av_video_type')) $q->set('posts_per_page', 9);
	if ($q->is_post_type_archive('av_video')) { // "Tất cả" lists long videos; shorts get their own rail
		$q->set('tax_query', [['taxonomy' => 'av_video_type', 'terms' => av_term_ids('short-video'), 'operator' => 'NOT IN']]);
	}
});

/* ---------- small helpers ---------- */

function av_meta($key, $id = null) {
	return get_post_meta($id ?: get_the_ID(), '_av_' . $key, true);
}

/** "Left | Right" lines → [[left, right], ...] (used by credits, profile info, works). */
function av_lines($text) {
	$out = [];
	foreach (preg_split('/\r\n|\n/', (string) $text) as $line) {
		if (trim($line) === '') continue;
		$parts = array_map('trim', explode('|', $line, 2));
		$out[] = [$parts[0], $parts[1] ?? ''];
	}
	return $out;
}

function av_img($id, $size = 'large', $attr = []) {
	return has_post_thumbnail($id)
		? get_the_post_thumbnail($id, $size, $attr + ['loading' => 'lazy'])
		: '<span class="av-ph"></span>';
}

function av_excerpt($id, $words = 28) {
	return wp_trim_words(get_the_excerpt($id), $words, '…');
}

/** Pills under each card: post tags, falling back to categories. */
function av_tags($id, $max = 2) {
	$terms = get_the_terms($id, 'post_tag');
	if (!$terms || is_wp_error($terms)) $terms = get_the_terms($id, get_post_type($id) === 'av_video' ? 'av_video_type' : 'category');
	if (!$terms || is_wp_error($terms)) return '';
	$html = '';
	foreach (array_slice($terms, 0, $max) as $t) {
		$html .= '<a class="av-pill" href="' . esc_url(get_term_link($t)) . '">' . esc_html($t->name) . '</a>';
	}
	return '<div class="av-pills">' . $html . '</div>';
}

function av_is_video($id) {
	return get_post_type($id) === 'av_video' || av_meta('video', $id);
}

/**
 * One card. $o: excerpt (bool), tags (bool), size (image size), class.
 */
function av_card($id, $o = []) {
	$o += ['excerpt' => false, 'tags' => true, 'size' => 'large', 'class' => ''];
	$url = get_permalink($id);
	$sub = av_meta('sub', $id);
	ob_start(); ?>
	<article class="av-card <?php echo esc_attr($o['class']); ?>">
		<a class="av-card__img<?php echo av_is_video($id) ? ' is-video' : ''; ?>" href="<?php echo esc_url($url); ?>"><?php echo av_img($id, $o['size']); ?></a>
		<h3 class="av-card__title"><a href="<?php echo esc_url($url); ?>"><?php echo esc_html(get_the_title($id)); ?></a></h3>
		<?php if ($sub) : ?><p class="av-card__sub"><?php echo esc_html($sub); ?></p><?php endif; ?>
		<?php if ($o['excerpt']) : ?><p class="av-card__ex"><?php echo esc_html(av_excerpt($id)); ?></p><?php endif; ?>
		<?php if ($o['tags']) echo av_tags($id); ?>
	</article>
	<?php return ob_get_clean();
}

/** Vertical short-video card with view count. */
function av_short($id) {
	$views = (int) av_meta('views', $id);
	return '<a class="av-short" href="' . esc_url(get_permalink($id)) . '">' . av_img($id, 'medium_large')
		. '<span class="av-short__v">▷ ' . number_format_i18n($views) . '</span>'
		. '<span class="screen-reader-text">' . esc_html(get_the_title($id)) . '</span></a>';
}

/** Big two-column feature: title / subtitle / excerpt / tags + image. */
function av_feature($id) {
	$url = get_permalink($id);
	ob_start(); ?>
	<article class="av-feature">
		<div class="av-feature__text">
			<h3 class="av-feature__title"><a href="<?php echo esc_url($url); ?>"><b><?php echo esc_html(get_the_title($id)); ?></b>
				<?php if ($s = av_meta('sub', $id)) echo '<span>' . esc_html($s) . '</span>'; ?></a></h3>
			<div>
				<p class="av-feature__ex"><?php echo esc_html(av_excerpt($id, 40)); ?></p>
				<?php echo av_tags($id); ?>
			</div>
		</div>
		<a class="av-feature__img<?php echo av_is_video($id) ? ' is-video' : ''; ?>" href="<?php echo esc_url($url); ?>"><?php echo av_img($id, 'large'); ?></a>
	</article>
	<?php return ob_get_clean();
}

function av_section_head($title, $more_url = '', $more_label = '', $tag = 'h2') {
	echo '<div class="av-head"><' . $tag . ' class="av-h2">' . esc_html($title) . '</' . $tag . '>';
	if ($more_url) echo '<a class="av-more" href="' . esc_url($more_url) . '">' . esc_html($more_label) . ' →</a>';
	echo '</div>';
}

/** "Tất cả • A • B" chips. $items = [[label, url, active], ...] */
function av_chips($items) {
	if (count($items) < 2) return;
	echo '<nav class="av-chips">';
	foreach ($items as [$label, $url, $active]) {
		echo '<a href="' . esc_url($url) . '"' . ($active ? ' class="is-active" aria-current="page"' : '') . '>' . esc_html($label) . '</a>';
	}
	echo '</nav>';
}

function av_ids($args) {
	return get_posts($args + ['fields' => 'ids', 'no_found_rows' => true, 'ignore_sticky_posts' => true]);
}

function av_videos($n = 6, $short = false) {
	return av_ids([
		'post_type' => 'av_video', 'posts_per_page' => $n,
		'tax_query' => [['taxonomy' => 'av_video_type', 'terms' => av_term_ids('short-video'), 'operator' => $short ? 'IN' : 'NOT IN']],
	]);
}

/** Horizontal rail with progress bar (videos, shorts). */
function av_rail($ids, $short = false) {
	if (!$ids) return;
	echo '<div class="av-rail' . ($short ? ' av-rail--short' : '') . '"><div class="av-rail__track">';
	foreach ($ids as $id) echo $short ? av_short($id) : av_card($id, ['size' => 'medium_large']);
	echo '</div><div class="av-rail__bar"><span></span></div></div>';
}

function av_socials() {
	$out = '';
	foreach (['youtube', 'instagram', 'tiktok', 'facebook', 'pinterest', 'linkedin'] as $s) {
		$url = av_mod("av_social_$s");
		if ($url) $out .= '<a href="' . esc_url($url) . '" target="_blank" rel="noopener" aria-label="' . esc_attr(ucfirst($s)) . '"><img src="' . AV_URI . "/assets/img/$s.png?v=" . AV_VER . '" alt="" width="22" height="22"></a>';
	}
	return $out ? '<div class="av-socials">' . $out . '</div>' : '';
}

/** Theme-mod ad banner (full width image link). */
function av_banner() {
	$img = av_mod('av_banner_img');
	if (!$img) return;
	$url = av_mod('av_banner_url');
	echo '<div class="av-banner">' . ($url ? '<a href="' . esc_url($url) . '" target="_blank" rel="noopener sponsored">' : '')
		. '<img src="' . esc_url($img) . '" alt="' . esc_attr(av__('Quảng cáo')) . '" loading="lazy">' . ($url ? '</a>' : '') . '</div>';
}

/** Home "more" link label: "Thêm kiến trúc". */
function av_more_label($name) {
	return sprintf(av__('Thêm %s'), mb_strtolower($name));
}

function av_footer_menu_fallback() {
	echo '<ul class="av-fcols">';
	foreach (get_categories(['parent' => 0, 'hide_empty' => false, 'orderby' => 'term_id', 'exclude' => get_option('default_category')]) as $c) {
		echo '<li><a href="' . esc_url(get_category_link($c)) . '">' . esc_html($c->name) . '</a><ul class="sub-menu">';
		foreach (get_categories(['parent' => $c->term_id, 'hide_empty' => false, 'orderby' => 'term_id']) as $ch) {
			echo '<li><a href="' . esc_url(get_category_link($ch)) . '">' . esc_html($ch->name) . '</a></li>';
		}
		echo '</ul></li>';
	}
	echo '</ul>';
}

function av_primary_menu_fallback() {
	echo '<ul class="av-nav__list">';
	foreach (get_categories(['parent' => 0, 'hide_empty' => false, 'orderby' => 'term_id', 'exclude' => get_option('default_category')]) as $c) {
		echo '<li><a href="' . esc_url(get_category_link($c)) . '">' . esc_html($c->name) . '</a></li>';
	}
	echo '<li><a href="' . esc_url(get_post_type_archive_link('av_video')) . '">Videos</a></li></ul>';
}

/* ---------- forms (handled in inc/admin.php → av_handle_form) ---------- */

function av_form_open($action, $class = '') {
	echo '<form class="' . esc_attr($class) . '" method="post" action="' . esc_url(admin_url('admin-post.php')) . '">'
		. '<input type="hidden" name="action" value="' . esc_attr($action) . '">'
		. wp_nonce_field('av_form', 'av_nonce', true, false)
		. '<input class="av-hp" type="text" name="av_hp" tabindex="-1" autocomplete="off" aria-hidden="true">';
}

function av_subscribe_form() {
	av_form_open('av_subscribe', 'av-subform');
	echo '<input type="email" name="email" required placeholder="' . esc_attr(av__('Email của bạn')) . '" aria-label="Email"><button type="submit">' . esc_html(av__('Đăng ký')) . '</button></form>';
}

function av_contact_form() {
	av_form_open('av_contact', 'av-contact');
	foreach (['name' => ['Họ Tên Bạn', 'text', 1], 'company' => ['Công ty, Doanh nghiệp & Tổ chức', 'text', 0], 'role' => ['Bộ phận & chức vụ', 'text', 0],
		'address' => ['Địa chỉ', 'text', 0], 'phone' => ['Số điện thoại', 'tel', 0], 'email' => ['Email', 'email', 1]] as $k => [$ph, $type, $req]) {
		$ph = av__($ph);
		echo '<input type="' . $type . '" name="' . $k . '" placeholder="' . esc_attr($ph) . '" aria-label="' . esc_attr($ph) . '"' . ($req ? ' required' : '') . '>';
	}
	$topic = esc_html(av__('Chọn nội dung liên hệ'));
	echo '<select name="topic" aria-label="' . $topic . '"><option value="">' . $topic . '</option>';
	foreach (array_filter(array_map('trim', explode("\n", av__(av_mod('av_contact_topics'))))) as $t) echo '<option>' . esc_html($t) . '</option>';
	echo '</select><textarea name="message" rows="3" placeholder="' . esc_attr(av__('Chi tiết các nội dung cần liên hệ & trao đổi')) . '" aria-label="' . esc_attr(av__('Nội dung')) . '"></textarea>'
		. '<button type="submit">' . esc_html(av__('Gửi')) . '</button></form>';
}
