<?php
/**
 * Demo importer: Appearance → Dữ liệu mẫu.
 * Rebuilds the Figma pages with sample content from demo/content.json and images from assets/demo/.
 * Safe to run twice: existing posts (same type + title), terms and media are reused.
 */

add_action('admin_menu', function () {
	add_theme_page('Dữ liệu mẫu Artvibes', 'Dữ liệu mẫu', 'manage_options', 'av-demo', 'av_demo_page');
});

add_action('after_switch_theme', function () {
	set_transient('av_demo_nudge', 1, DAY_IN_SECONDS);
});

add_action('admin_notices', function () {
	if (!get_transient('av_demo_nudge') || get_option('av_demo_done') || !current_user_can('manage_options')) return;
	echo '<div class="notice notice-info"><p>Theme The Artvibes đã bật. <a href="' . esc_url(admin_url('themes.php?page=av-demo')) . '">Nhập dữ liệu mẫu</a> để trang giống bản thiết kế.</p></div>';
});

function av_demo_page() {
	$msg = '';
	if (isset($_POST['av_demo']) && check_admin_referer('av_demo') && current_user_can('manage_options')) $msg = av_demo_import();
	echo '<div class="wrap"><h1>Dữ liệu mẫu The Artvibes</h1>';
	if ($msg) echo '<div class="notice notice-success"><p>' . esc_html($msg) . ' <a href="' . esc_url(home_url('/')) . '">Xem trang chủ →</a></p></div>';
	echo '<p>Tạo chuyên mục, bài viết, videos, chân dung, trang About, menu và cài đặt giống bản thiết kế Figma. Ảnh mẫu lấy từ thư mục <code>assets/demo</code> của theme.</p>';
	echo '<p>Chạy lại sẽ không tạo trùng: mục nào đã có (cùng tiêu đề) sẽ được giữ nguyên.</p><form method="post">';
	wp_nonce_field('av_demo');
	submit_button(get_option('av_demo_done') ? 'Nhập lại phần còn thiếu' : 'Nhập dữ liệu mẫu', 'primary', 'av_demo');
	echo '</form></div>';
}

/** Theme demo image → attachment id (cached in an option so re-runs reuse it). */
function av_demo_media($file) {
	$map = get_option('av_demo_media', []);
	if (!empty($map[$file]) && get_post($map[$file])) return $map[$file];
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';
	$tmp = wp_tempnam($file);
	copy(get_template_directory() . '/assets/demo/' . basename($file), $tmp);
	$id = media_handle_sideload(['name' => basename($file), 'tmp_name' => $tmp], 0);
	if (is_wp_error($id)) {
		@unlink($tmp);
		return 0;
	}
	$map[$file] = $id;
	update_option('av_demo_media', $map, false);
	return $id;
}

/** Replace {img:file.jpg} with the uploaded image URL. */
function av_demo_fill($s) {
	return preg_replace_callback('/\{img:([\w.-]+)\}/', fn($m) => wp_get_attachment_url(av_demo_media($m[1])), (string) $s);
}

function av_demo_term($name, $slug, $tax, $parent = 0) {
	if ($t = get_term_by('slug', $slug, $tax)) return $t->term_id;
	$r = wp_insert_term($name, $tax, ['slug' => $slug, 'parent' => $parent]);
	return is_wp_error($r) ? (int) $r->get_error_data('term_exists') : $r['term_id'];
}

function av_demo_cats($list, $parent = 0) {
	foreach ($list as $c) {
		$id = av_demo_term($c[0], $c[1], 'category', $parent);
		if (!empty($c[2])) av_demo_cats($c[2], $id);
	}
}

function av_demo_import() {
	@set_time_limit(600);
	$d = json_decode(file_get_contents(get_template_directory() . '/demo/content.json'), true);

	update_option('blogname', $d['site']['name']);
	update_option('blogdescription', $d['site']['tagline']);
	if (!get_option('permalink_structure')) update_option('permalink_structure', '/%postname%/');

	av_demo_cats($d['categories']);
	foreach ($d['video_types'] as $t) av_demo_term($t, sanitize_title($t), 'av_video_type');

	$n = 0;
	$save = function ($type, $p) use (&$n) {
		$n++;
		$found = get_posts(['post_type' => $type, 'title' => $p['title'], 'post_status' => 'any', 'fields' => 'ids', 'numberposts' => 1]);
		if ($found) return 0;
		$id = wp_insert_post([
			'post_type' => $type, 'post_status' => 'publish', 'post_title' => $p['title'],
			'post_excerpt' => $p['excerpt'] ?? '', 'post_content' => av_demo_fill($p['content'] ?? ''),
			'post_date' => wp_date('Y-m-d H:i:s', time() - $n * HOUR_IN_SECONDS), // keeps JSON order = newest first
		]);
		if (!$id || is_wp_error($id)) return 0;
		if (!empty($p['image'])) set_post_thumbnail($id, av_demo_media($p['image']));
		foreach (['sub', 'intro', 'words', 'photo', 'credits', 'video', 'views', 'hero', 'info', 'works', 'tag'] as $k) {
			if (!empty($p[$k])) update_post_meta($id, "_av_$k", $p[$k]);
		}
		if (!empty($p['gallery'])) update_post_meta($id, '_av_gallery', implode(',', array_map('av_demo_media', $p['gallery'])));
		if (!empty($p['tags'])) wp_set_post_terms($id, $p['tags'], 'post_tag');
		return $id;
	};

	$count = [0, 0, 0];
	// Videos first so the video hero slide is the newest.
	foreach ($d['videos'] as $p) {
		if ($id = $save('av_video', $p)) {
			wp_set_object_terms($id, sanitize_title($p['type']), 'av_video_type');
			$count[1]++;
		}
	}
	foreach ($d['posts'] as $p) {
		if ($id = $save('post', $p)) {
			wp_set_post_categories($id, array_map(fn($s) => get_category_by_slug($s)->term_id, $p['cats']));
			$count[0]++;
		}
	}
	foreach ($d['portraits'] as $p) if ($save('av_portrait', $p)) $count[2]++;

	$a = $d['about'];
	$page = get_page_by_path($a['slug']);
	$pid = $page ? $page->ID : wp_insert_post(['post_type' => 'page', 'post_status' => 'publish', 'post_title' => $a['title'], 'post_name' => $a['slug'], 'post_content' => $a['content']]);
	update_post_meta($pid, '_wp_page_template', 'template-about.php');
	if (!has_post_thumbnail($pid)) set_post_thumbnail($pid, av_demo_media($a['image']));

	foreach ($d['mods'] as $k => $v) if (!get_theme_mod($k)) set_theme_mod($k, av_demo_fill($v));

	av_demo_menus($d['menus'], $pid);
	flush_rewrite_rules();
	update_option('av_demo_done', 1);
	delete_transient('av_demo_nudge');
	return sprintf('Đã nhập %d bài viết, %d video, %d chân dung, trang About và menu.', ...$count);
}

function av_demo_menus($menus, $about_id) {
	$add = function ($menu, $it, $parent = 0) use (&$add, $about_id) {
		$args = ['menu-item-status' => 'publish', 'menu-item-parent-id' => $parent, 'menu-item-title' => $it['title'] ?? ''];
		$cat = null;
		if (isset($it['cat'])) {
			$cat = get_category_by_slug($it['cat']);
			$args = ['menu-item-type' => 'taxonomy', 'menu-item-object' => 'category', 'menu-item-object-id' => $cat->term_id, 'menu-item-title' => $cat->name] + $args;
		} elseif (isset($it['archive'])) {
			$args = ['menu-item-type' => 'post_type_archive', 'menu-item-object' => $it['archive']] + $args;
		} elseif (isset($it['term'])) {
			$t = get_term_by('slug', $it['term'], 'av_video_type');
			$args = ['menu-item-type' => 'taxonomy', 'menu-item-object' => 'av_video_type', 'menu-item-object-id' => $t->term_id] + $args;
		} elseif (isset($it['hash'])) {
			$args = ['menu-item-type' => 'custom', 'menu-item-url' => get_permalink($about_id) . $it['hash']] + $args;
		} elseif (isset($it['page'])) {
			$args = ['menu-item-type' => 'post_type', 'menu-item-object' => 'page', 'menu-item-object-id' => $about_id] + $args;
		}
		$id = wp_update_nav_menu_item($menu, 0, $args);
		if (!empty($it['kids']) && $cat) {
			foreach (get_categories(['parent' => $cat->term_id, 'hide_empty' => false, 'orderby' => 'term_id']) as $c) $add($menu, ['cat' => $c->slug], $id);
		}
		foreach ($it['children'] ?? [] as $ch) $add($menu, $ch, $id);
	};

	$locations = get_theme_mod('nav_menu_locations', []);
	foreach (['primary' => 'Menu chính', 'footer' => 'Menu chân trang'] as $loc => $name) {
		if (!empty($locations[$loc]) && wp_get_nav_menu_object($locations[$loc])) continue; // keep the user's menu
		if ($old = wp_get_nav_menu_object($name)) wp_delete_nav_menu($old->term_id);
		$menu = wp_create_nav_menu($name);
		foreach ($menus[$loc] as $it) $add($menu, $it);
		$locations[$loc] = $menu;
	}
	set_theme_mod('nav_menu_locations', $locations);
}
