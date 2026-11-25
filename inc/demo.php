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
	echo av_pll()
		? '<p><strong>Song ngữ:</strong> Polylang đang bật — sẽ tạo ngôn ngữ Tiếng Việt (mặc định) + English và bản tiếng Anh cho mọi nội dung.</p>'
		: '<p><strong>Song ngữ Việt – Anh:</strong> <a href="' . esc_url(admin_url('plugin-install.php?s=polylang&tab=search&type=term')) . '">cài và bật plugin Polylang</a> (miễn phí) trước khi nhập để có thêm bản tiếng Anh.</p>';
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

/** Polylang loaded → content is created in Vietnamese + English and linked as translations. */
function av_pll() {
	return function_exists('pll_insert_post') && function_exists('PLL');
}

function av_demo_langs() {
	return av_pll() ? ['vi', 'en'] : ['vi'];
}

/** Creates the vi (default) and en languages when Polylang has none yet. */
function av_demo_languages() {
	if (!av_pll()) return;
	$have = pll_languages_list(['fields' => 'slug']);
	$add = [
		'vi' => ['locale' => 'vi', 'name' => 'Tiếng Việt', 'slug' => 'vi', 'flag' => 'vn', 'term_group' => 0],
		'en' => ['locale' => 'en_US', 'name' => 'English', 'slug' => 'en', 'flag' => 'us', 'term_group' => 1],
	];
	foreach ($add as $slug => $args) {
		if (in_array($slug, $have, true)) continue;
		isset(PLL()->model->languages) && method_exists(PLL()->model->languages, 'add')
			? PLL()->model->languages->add($args)   // Polylang 3.7+
			: PLL()->model->add_language($args);     // older Polylang (admin)
	}
	PLL()->model->clean_languages_cache();
}

function av_demo_term($name, $slug, $tax, $parent = 0, $lang = 'vi') {
	$found = get_terms(['taxonomy' => $tax, 'slug' => $slug, 'hide_empty' => false, 'lang' => '']);
	if ($found && !is_wp_error($found)) {
		$id = $found[0]->term_id;
		if (av_pll() && !pll_get_term_language($id)) pll_set_term_language($id, $lang);
		return $id;
	}
	$r = av_pll() ? pll_insert_term($name, $tax, $lang, ['slug' => $slug, 'parent' => $parent]) : wp_insert_term($name, $tax, ['slug' => $slug, 'parent' => $parent]);
	return is_wp_error($r) ? (int) $r->get_error_data('term_exists') : (int) $r['term_id'];
}

/** Term id by Vietnamese slug, in $lang. */
function av_demo_tid($slug, $tax, $lang) {
	$t = get_terms(['taxonomy' => $tax, 'slug' => $slug, 'hide_empty' => false, 'lang' => '']);
	if (!$t || is_wp_error($t)) return 0;
	return $lang === 'vi' || !av_pll() ? $t[0]->term_id : (int) pll_get_term($t[0]->term_id, $lang);
}

function av_demo_cats($nodes, $parents = ['vi' => 0, 'en' => 0]) {
	foreach ($nodes as $c) {
		$ids = ['vi' => av_demo_term($c['name'], $c['slug'], 'category', $parents['vi'], 'vi')];
		if (av_pll()) {
			$ids['en'] = av_demo_term($c['en'][0], $c['en'][1], 'category', $parents['en'], 'en');
			pll_save_term_translations($ids);
		}
		if (!empty($c['children'])) av_demo_cats($c['children'], $ids + ['en' => 0]);
	}
}

/** Tag ids for the names, created in $lang if missing. */
function av_demo_tags($names, $lang) {
	$ids = [];
	foreach ($names as $name) {
		$t = get_terms(['taxonomy' => 'post_tag', 'name' => $name, 'hide_empty' => false, 'lang' => av_pll() ? $lang : '']);
		$ids[] = $t && !is_wp_error($t) ? (int) $t[0]->term_id
			: av_demo_term($name, sanitize_title($name) . ($lang === 'en' ? '-en' : ''), 'post_tag', 0, $lang);
	}
	return $ids;
}

/** Vietnamese label → English for menu items (theme dictionary). */
function av_demo_label($s, $lang) {
	return $lang === 'en' ? (AV_EN[$s] ?? $s) : $s;
}

function av_demo_import() {
	@set_time_limit(600);
	$d = json_decode(file_get_contents(get_template_directory() . '/demo/content.json'), true);

	update_option('blogname', $d['site']['name']);
	update_option('blogdescription', $d['site']['tagline']);
	if (!get_option('permalink_structure')) update_option('permalink_structure', '/%postname%/');

	av_demo_languages();
	av_demo_cats($d['categories']);
	foreach ($d['video_types'] as $t) {
		$ids = ['vi' => av_demo_term($t['name'], $t['slug'], 'av_video_type')];
		if (av_pll()) {
			$ids['en'] = av_demo_term($t['name'], $t['slug'] . '-en', 'av_video_type', 0, 'en');
			pll_save_term_translations($ids);
		}
	}

	/** Creates one post in one language; returns [id, created]. */
	$save = function ($type, $p, $lang, $age) {
		$found = get_posts(['post_type' => $type, 'title' => $p['title'], 'post_status' => 'any', 'fields' => 'ids', 'numberposts' => 1, 'lang' => av_pll() ? $lang : '']);
		if ($found) return [$found[0], false];
		$arr = [
			'post_type' => $type, 'post_status' => 'publish', 'post_title' => $p['title'],
			'post_excerpt' => $p['excerpt'] ?? '', 'post_content' => av_demo_fill($p['content'] ?? ''),
			'post_date' => wp_date('Y-m-d H:i:s', time() - $age * HOUR_IN_SECONDS), // JSON order = newest first
		];
		$id = av_pll() ? pll_insert_post($arr, $lang) : wp_insert_post($arr);
		if (!$id || is_wp_error($id)) return [0, false];
		if (!empty($p['image'])) set_post_thumbnail($id, av_demo_media($p['image']));
		foreach (['sub', 'intro', 'words', 'photo', 'credits', 'video', 'views', 'hero', 'info', 'works', 'tag'] as $k) {
			if (!empty($p[$k])) update_post_meta($id, "_av_$k", $p[$k]);
		}
		if (!empty($p['gallery'])) update_post_meta($id, '_av_gallery', implode(',', array_map('av_demo_media', $p['gallery'])));
		return [$id, true];
	};

	$age = 0;
	$count = [0, 0, 0];
	/** Every item in each language; $terms($id, $item, $lang) runs for newly created posts. */
	$each = function ($type, $items, $slot, $terms) use ($save, &$age, &$count, $d) {
		foreach ($items as $p) {
			$age++;
			$ids = [];
			foreach (av_demo_langs() as $lang) {
				$item = $lang === 'en' ? array_merge($p, $p['en'] ?? []) : $p;
				if ($lang === 'en' && !empty($item['tags'])) $item['tags'] = array_map(fn($t) => $d['tag_en'][$t] ?? $t, $item['tags']);
				[$id, $new] = $save($type, $item, $lang, $age);
				if (!$id) continue;
				$ids[$lang] = $id;
				if (!$new) continue;
				if (!empty($item['tags'])) wp_set_post_terms($id, av_demo_tags($item['tags'], $lang), 'post_tag');
				$terms($id, $item, $lang);
				if ($lang === 'vi') $count[$slot]++;
			}
			if (av_pll() && count($ids) > 1) pll_save_post_translations($ids);
		}
	};

	// Videos first so the video hero slide is the newest.
	$each('av_video', $d['videos'], 1, function ($id, $p, $lang) {
		wp_set_object_terms($id, av_demo_tid(sanitize_title($p['type']), 'av_video_type', $lang), 'av_video_type');
	});
	$each('post', $d['posts'], 0, function ($id, $p, $lang) {
		wp_set_post_categories($id, array_filter(array_map(fn($s) => av_demo_tid($s, 'category', $lang), $p['cats'])));
	});
	$each('av_portrait', $d['portraits'], 2, fn() => null);

	// About page (Vietnamese + English).
	$pages = [];
	foreach (av_demo_langs() as $lang) {
		$a = $lang === 'en' ? array_merge($d['about'], $d['about']['en']) : $d['about'];
		$page = get_page_by_path($a['slug']);
		$arr = ['post_type' => 'page', 'post_status' => 'publish', 'post_title' => $a['title'], 'post_name' => $a['slug'], 'post_content' => $a['content']];
		$pid = $page ? $page->ID : (av_pll() ? pll_insert_post($arr, $lang) : wp_insert_post($arr));
		update_post_meta($pid, '_wp_page_template', 'template-about.php');
		if (!has_post_thumbnail($pid)) set_post_thumbnail($pid, av_demo_media($a['image']));
		$pages[$lang] = $pid;
	}
	if (av_pll()) pll_save_post_translations($pages);

	foreach ($d['mods'] as $k => $v) if (!get_theme_mod($k)) set_theme_mod($k, av_demo_fill($v));

	// English tagline in Polylang's string translations.
	if (av_pll() && class_exists('PLL_MO') && ($en = PLL()->model->get_language('en'))) {
		$mo = new PLL_MO();
		$mo->import_from_db($en);
		$mo->add_entry($mo->make_entry($d['site']['tagline'], $d['site']['tagline_en']));
		$mo->export_to_db($en);
	}

	av_demo_menus($d['menus'], $pages);
	flush_rewrite_rules();
	update_option('av_demo_done', 1);
	delete_transient('av_demo_nudge');
	return sprintf('Đã nhập %d bài viết, %d video, %d chân dung, trang About và menu', ...$count)
		. (av_pll() ? ' — mỗi mục có bản tiếng Việt và tiếng Anh.' : '. Cài plugin Polylang rồi nhập lại để có bản tiếng Anh.');
}

function av_demo_menus($menus, $pages) {
	$add = function ($menu, $it, $parent, $lang) use (&$add, $pages) {
		$args = ['menu-item-status' => 'publish', 'menu-item-parent-id' => $parent, 'menu-item-title' => av_demo_label($it['title'] ?? '', $lang)];
		$cat = null;
		if (isset($it['cat'])) {
			$cat = get_term(av_demo_tid($it['cat'], 'category', $lang), 'category');
			$args = ['menu-item-type' => 'taxonomy', 'menu-item-object' => 'category', 'menu-item-object-id' => $cat->term_id, 'menu-item-title' => $cat->name] + $args;
		} elseif (isset($it['archive'])) {
			$args = ['menu-item-type' => 'post_type_archive', 'menu-item-object' => $it['archive']] + $args;
		} elseif (isset($it['term'])) {
			$args = ['menu-item-type' => 'taxonomy', 'menu-item-object' => 'av_video_type', 'menu-item-object-id' => av_demo_tid($it['term'], 'av_video_type', $lang)] + $args;
		} elseif (isset($it['hash'])) {
			$args = ['menu-item-type' => 'custom', 'menu-item-url' => get_permalink($pages[$lang]) . $it['hash']] + $args;
		} elseif (isset($it['page'])) {
			$args = ['menu-item-type' => 'post_type', 'menu-item-object' => 'page', 'menu-item-object-id' => $pages[$lang]] + $args;
		}
		$id = wp_update_nav_menu_item($menu, 0, $args);
		if (!empty($it['kids']) && $cat) {
			foreach (get_categories(['parent' => $cat->term_id, 'hide_empty' => false, 'orderby' => 'term_id', 'lang' => '']) as $c) {
				wp_update_nav_menu_item($menu, 0, ['menu-item-status' => 'publish', 'menu-item-parent-id' => $id, 'menu-item-type' => 'taxonomy',
					'menu-item-object' => 'category', 'menu-item-object-id' => $c->term_id, 'menu-item-title' => $c->name]);
			}
		}
		foreach ($it['children'] ?? [] as $ch) $add($menu, $ch, $id, $lang);
	};

	$names = ['primary' => ['vi' => 'Menu chính', 'en' => 'Main menu (English)'], 'footer' => ['vi' => 'Menu chân trang', 'en' => 'Footer menu (English)']];
	$locations = get_theme_mod('nav_menu_locations', []);
	$theme = get_stylesheet();
	$pll_menus = av_pll() ? (PLL()->options->get('nav_menus') ?: []) : [];

	foreach ($names as $loc => $by_lang) {
		foreach (av_demo_langs() as $lang) {
			$current = av_pll() ? ($pll_menus[$theme][$loc][$lang] ?? 0) : ($locations[$loc] ?? 0);
			$obj = $current ? wp_get_nav_menu_object($current) : null;
			if ($obj && $obj->name !== $by_lang[$lang]) continue; // keep a menu the user made
			if ($old = wp_get_nav_menu_object($by_lang[$lang])) wp_delete_nav_menu($old->term_id);
			$menu = wp_create_nav_menu($by_lang[$lang]);
			foreach ($menus[$loc] as $it) $add($menu, $it, 0, $lang);
			if ($lang === 'vi') $locations[$loc] = $menu;
			$pll_menus[$theme][$loc][$lang] = $menu;
		}
	}
	set_theme_mod('nav_menu_locations', $locations);
	if (av_pll()) {
		PLL()->options->set('nav_menus', $pll_menus);
		PLL()->options->save();
	}
}
