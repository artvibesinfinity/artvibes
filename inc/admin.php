<?php
/**
 * Admin side: content types, custom fields, theme settings (Customizer), contact/subscribe inbox.
 */

/* ---------- Content types ---------- */

add_action('init', function () {
	$labels = function ($one, $many) {
		return ['name' => $many, 'singular_name' => $one, 'add_new' => 'Thêm mới', 'add_new_item' => "Thêm $one", 'edit_item' => "Sửa $one",
			'new_item' => "$one mới", 'view_item' => "Xem $one", 'search_items' => "Tìm $one", 'not_found' => 'Chưa có dữ liệu', 'all_items' => "Tất cả $many", 'menu_name' => $many];
	};

	register_post_type('av_video', [
		'labels' => $labels('Video', 'Videos'), 'public' => true, 'has_archive' => 'videos', 'rewrite' => ['slug' => 'video'],
		'menu_icon' => 'dashicons-video-alt3', 'menu_position' => 6, 'show_in_rest' => true,
		'supports' => ['title', 'editor', 'thumbnail', 'excerpt'], 'taxonomies' => ['post_tag'],
	]);
	register_taxonomy('av_video_type', 'av_video', [
		'labels' => $labels('Loại video', 'Loại video'), 'hierarchical' => true, 'show_admin_column' => true,
		'show_in_rest' => true, 'rewrite' => ['slug' => 'videos/loai'],
	]);

	register_post_type('av_portrait', [
		'labels' => $labels('Chân dung', 'Chân dung'), 'public' => true, 'has_archive' => 'chan-dung', 'rewrite' => ['slug' => 'chan-dung'],
		'menu_icon' => 'dashicons-id-alt', 'menu_position' => 7, 'show_in_rest' => true,
		'supports' => ['title', 'editor', 'thumbnail', 'excerpt'],
	]);

	// Inbox types: visible in admin only, created by the front-end forms.
	foreach (['av_contact' => ['Liên hệ', 'Liên hệ', 'dashicons-email-alt'], 'av_subscriber' => ['Người đăng ký', 'Đăng ký nhận tin', 'dashicons-groups']] as $type => [$one, $many, $icon]) {
		register_post_type($type, [
			'labels' => $labels($one, $many), 'public' => false, 'show_ui' => true, 'menu_icon' => $icon, 'menu_position' => 26,
			'supports' => ['title', 'editor'], 'capability_type' => 'post', 'capabilities' => ['create_posts' => 'do_not_allow'], 'map_meta_cap' => true,
		]);
	}
});

/* ---------- Custom fields (meta boxes) ---------- */

/** Field definitions per post type: key => [label, type, help]. */
function av_fields($type) {
	$common = [
		'sub'  => ['Phụ đề (dòng thứ 2 dưới tiêu đề, vd: tên studio)', 'text'],
		'hero' => ['Hiện ở slider đầu trang chủ', 'check'],
	];
	switch ($type) {
		case 'post':
			return $common + [
				'intro'   => ['Đoạn giới thiệu (cột phải, cạnh Published/Words/Photography)', 'textarea'],
				'words'   => ['Words (người viết)', 'text'],
				'photo'   => ['Photography (nhiếp ảnh)', 'text'],
				'video'   => ['Link video (YouTube/Vimeo) — bài sẽ có nút ▶', 'url'],
				'credits' => ['Credits — mỗi dòng: Vai trò | Tên (vd: Architecture by | Pop Architecture)', 'textarea'],
			];
		case 'av_video':
			return $common + [
				'video' => ['Link video (YouTube/Vimeo/TikTok)', 'url'],
				'views' => ['Lượt xem (hiển thị trên Short video)', 'number'],
			];
		case 'av_portrait':
			return [
				'sub'     => ['Mô tả ngắn trên thẻ (vd: nghề nghiệp)', 'text'],
				'info'    => ['Thông tin cá nhân — mỗi dòng: Nhãn | Giá trị (vd: Quốc tịch | Việt Nam)', 'textarea'],
				'works'   => ['Tác phẩm — mỗi dòng: Tên tác phẩm | Năm', 'textarea'],
				'gallery' => ['Ảnh cột phải mục Tác phẩm', 'gallery'],
				'tag'     => ['Slug thẻ của bài viết liên quan (để trống = bài mới nhất)', 'text'],
			];
	}
	return [];
}

add_action('add_meta_boxes', function () {
	foreach (['post' => 'Thông tin bài viết', 'av_video' => 'Thông tin video', 'av_portrait' => 'Hồ sơ chân dung'] as $type => $title) {
		add_meta_box('av_fields', $title, 'av_fields_box', $type, 'normal', 'high');
	}
	foreach (['av_contact', 'av_subscriber'] as $type) {
		add_meta_box('av_inbox', 'Chi tiết', 'av_inbox_box', $type, 'normal', 'high');
	}
});

function av_fields_box($post) {
	wp_nonce_field('av_fields', 'av_fields_nonce');
	echo '<div class="av-fields">';
	foreach (av_fields($post->post_type) as $key => [$label, $type]) {
		$v = get_post_meta($post->ID, "_av_$key", true);
		$name = "av[$key]";
		echo '<p><label for="av_' . $key . '"><strong>' . esc_html($label) . '</strong></label><br>';
		switch ($type) {
			case 'textarea':
				echo '<textarea class="widefat" rows="5" id="av_' . $key . '" name="' . $name . '">' . esc_textarea($v) . '</textarea>';
				break;
			case 'check':
				echo '<label><input type="checkbox" id="av_' . $key . '" name="' . $name . '" value="1"' . checked($v, '1', false) . '> Có</label>';
				break;
			case 'gallery':
				echo '<input type="hidden" id="av_' . $key . '" name="' . $name . '" value="' . esc_attr($v) . '"><span class="av-gal">';
				foreach (array_filter(explode(',', (string) $v)) as $aid) echo wp_get_attachment_image((int) $aid, 'thumbnail');
				echo '</span><br><button type="button" class="button av-gal-pick" data-target="av_' . $key . '">Chọn ảnh</button>';
				break;
			default:
				echo '<input class="widefat" type="' . $type . '" id="av_' . $key . '" name="' . $name . '" value="' . esc_attr($v) . '">';
		}
		echo '</p>';
	}
	echo '</div>';
}

add_action('save_post', function ($id, $post) {
	if (!isset($_POST['av_fields_nonce']) || !wp_verify_nonce($_POST['av_fields_nonce'], 'av_fields')) return;
	if ((defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || !current_user_can('edit_post', $id)) return;
	$in = wp_unslash($_POST['av'] ?? []);
	foreach (av_fields($post->post_type) as $key => [, $type]) {
		$v = $in[$key] ?? '';
		$v = $type === 'textarea' ? sanitize_textarea_field($v)
			: ($type === 'url' ? esc_url_raw($v)
			: ($type === 'number' ? (string) absint($v)
			: ($type === 'gallery' ? implode(',', array_filter(array_map('absint', explode(',', $v))))
			: sanitize_text_field($v))));
		if ($v === '') delete_post_meta($id, "_av_$key");
		else update_post_meta($id, "_av_$key", $v);
	}
}, 10, 2);

add_action('admin_enqueue_scripts', function ($hook) {
	if (!in_array($hook, ['post.php', 'post-new.php'], true)) return;
	wp_enqueue_media();
	wp_enqueue_script('av-admin', AV_URI . '/assets/admin.js', ['jquery'], AV_VER, true);
	wp_add_inline_style('wp-admin', '.av-gal img{width:60px;height:60px;object-fit:cover;margin:4px 4px 0 0}');
});

/* ---------- Admin list columns: thumbnail ---------- */

foreach (['post', 'av_video', 'av_portrait'] as $type) {
	add_filter("manage_{$type}_posts_columns", function ($cols) {
		return ['cb' => $cols['cb'], 'av_thumb' => 'Ảnh'] + $cols;
	});
	add_action("manage_{$type}_posts_custom_column", function ($col, $id) {
		if ($col === 'av_thumb') echo get_the_post_thumbnail($id, [60, 60]);
	}, 10, 2);
}
add_action('admin_head', function () {
	echo '<style>.column-av_thumb{width:70px}.column-av_thumb img{width:60px;height:60px;object-fit:cover}</style>';
});

/* ---------- Inbox (contact + subscribers) ---------- */

const AV_CONTACT_FIELDS = ['name' => 'Họ tên', 'company' => 'Công ty, Doanh nghiệp & Tổ chức', 'role' => 'Bộ phận & chức vụ', 'address' => 'Địa chỉ', 'phone' => 'Số điện thoại', 'email' => 'Email', 'topic' => 'Nội dung liên hệ'];

function av_inbox_box($post) {
	echo '<table class="widefat striped">';
	foreach (AV_CONTACT_FIELDS as $k => $label) {
		if ($v = get_post_meta($post->ID, "_av_$k", true)) echo '<tr><th style="width:220px">' . esc_html($label) . '</th><td>' . esc_html($v) . '</td></tr>';
	}
	echo '<tr><th>Thời gian</th><td>' . esc_html(get_the_date('d/m/Y H:i', $post)) . '</td></tr></table>';
}

add_filter('manage_av_contact_posts_columns', fn($c) => ['cb' => $c['cb'], 'title' => 'Họ tên', 'av_email' => 'Email', 'av_phone' => 'Điện thoại', 'av_topic' => 'Nội dung', 'date' => 'Ngày gửi']);
add_action('manage_av_contact_posts_custom_column', function ($col, $id) {
	echo esc_html(get_post_meta($id, '_' . $col, true));
}, 10, 2);

/** Front-end form endpoint: admin-post.php?action=av_contact | av_subscribe */
function av_handle_form() {
	$type = $_POST['action'] === 'av_subscribe' ? 'av_subscriber' : 'av_contact';
	$back = wp_get_referer() ?: home_url('/');
	if (!wp_verify_nonce($_POST['av_nonce'] ?? '', 'av_form') || !empty($_POST['av_hp'])) { // honeypot
		wp_safe_redirect(add_query_arg('av_sent', 'err', $back)); exit;
	}
	$in = wp_unslash($_POST);
	$email = sanitize_email($in['email'] ?? '');
	if (!is_email($email)) { wp_safe_redirect(add_query_arg('av_sent', 'err', $back)); exit; }

	if ($type === 'av_subscriber') {
		if (!get_posts(['post_type' => 'av_subscriber', 'title' => $email, 'post_status' => 'any', 'fields' => 'ids'])) {
			wp_insert_post(['post_type' => $type, 'post_status' => 'publish', 'post_title' => $email]);
		}
	} else {
		$data = [];
		foreach (AV_CONTACT_FIELDS as $k => $label) $data[$k] = sanitize_text_field($in[$k] ?? '');
		$data['email'] = $email;
		$msg = sanitize_textarea_field($in['message'] ?? '');
		$id = wp_insert_post(['post_type' => $type, 'post_status' => 'publish', 'post_title' => $data['name'] ?: $email, 'post_content' => $msg]);
		foreach ($data as $k => $v) if ($v !== '') update_post_meta($id, "_av_$k", $v);
		$body = '';
		foreach (AV_CONTACT_FIELDS as $k => $label) $body .= "$label: {$data[$k]}\n";
		wp_mail(av_mod('av_contact_email') ?: get_option('admin_email'), '[Artvibes] Liên hệ mới: ' . ($data['name'] ?: $email), $body . "\n" . $msg, ['Reply-To: ' . $email]);
	}
	wp_safe_redirect(add_query_arg('av_sent', $type === 'av_subscriber' ? 'sub' : 'contact', $back) . ($type === 'av_contact' ? '#lien-he' : '')); exit;
}
foreach (['av_contact', 'av_subscribe'] as $a) {
	add_action("admin_post_$a", 'av_handle_form');
	add_action("admin_post_nopriv_$a", 'av_handle_form');
}

/* ---------- Theme settings (Appearance → Customize → Artvibes) ---------- */

function av_settings() {
	return [
		'av_home' => ['Trang chủ', [
			'av_trend_cat'  => ['Chuyên mục khối đầu (slug)', 'text', 'xu-huong'],
			'av_home_cats'  => ['Các chuyên mục hiển thị (slug, cách nhau dấu phẩy, theo thứ tự)', 'text', 'kien-truc,noi-that,hoi-hoa,van-hoa,doi-song'],
			'av_side_img'   => ['Ảnh quảng cáo cột phải (khối đầu)', 'image', ''],
			'av_side_url'   => ['Link quảng cáo cột phải', 'url', ''],
		]],
		'av_ads' => ['Banner quảng cáo', [
			'av_banner_img' => ['Ảnh banner (ngang, full màn hình)', 'image', ''],
			'av_banner_url' => ['Link banner', 'url', ''],
		]],
		'av_social' => ['Mạng xã hội', [
			'av_social_youtube' => ['YouTube', 'url', ''], 'av_social_instagram' => ['Instagram', 'url', ''], 'av_social_tiktok' => ['TikTok', 'url', ''],
			'av_social_facebook' => ['Facebook', 'url', ''], 'av_social_pinterest' => ['Pinterest', 'url', ''], 'av_social_linkedin' => ['LinkedIn', 'url', ''],
		]],
		'av_about' => ['About & Liên hệ', [
			'av_offices'       => ['Văn phòng — mỗi khối cách nhau 1 dòng trống, dòng đầu là tên', 'textarea', ''],
			'av_addr_img'      => ['Ảnh mục Địa chỉ', 'image', ''],
			'av_contact_img'   => ['Ảnh mục Liên hệ', 'image', ''],
			'av_contact_intro' => ['Lời mở đầu mục Liên hệ', 'textarea', ''],
			'av_contact_topics'=> ['Lựa chọn "Nội dung liên hệ" (mỗi dòng 1 mục)', 'textarea', "Hợp tác nội dung\nQuảng cáo\nGóp ý\nKhác"],
			'av_contact_email' => ['Email nhận liên hệ (trống = email quản trị)', 'email', ''],
		]],
		'av_footer' => ['Chân trang', [
			'av_footer_desc' => ['Mô tả', 'textarea', 'Artvibes là mạng xã hội định hình phong cách sống thế hệ mới. Từ những triển lãm nghệ thuật đương đại, tuyệt tác kiến trúc thế giới cho đến nghệ thuật sắp đặt nội thất tinh tế'],
			'av_copyright'   => ['Copyright', 'text', 'Copyright © 2025 Infinity Investments. All rights reserved.'],
		]],
	];
}

/** Theme setting with its default from av_settings(). */
function av_mod($id) {
	static $defaults;
	if ($defaults === null) {
		$defaults = [];
		foreach (av_settings() as [, $fields]) foreach ($fields as $k => $f) $defaults[$k] = $f[2];
	}
	return get_theme_mod($id, $defaults[$id] ?? '');
}

add_action('customize_register', function ($c) {
	$c->add_panel('av', ['title' => 'Artvibes', 'priority' => 30]);
	foreach (av_settings() as $sid => [$title, $fields]) {
		$c->add_section($sid, ['title' => $title, 'panel' => 'av']);
		foreach ($fields as $id => [$label, $type, $default]) {
			$sanitize = ['image' => 'esc_url_raw', 'url' => 'esc_url_raw', 'textarea' => 'sanitize_textarea_field', 'email' => 'sanitize_email'][$type] ?? 'sanitize_text_field';
			$c->add_setting($id, ['default' => $default, 'sanitize_callback' => $sanitize]);
			$type === 'image'
				? $c->add_control(new WP_Customize_Image_Control($c, $id, ['label' => $label, 'section' => $sid]))
				: $c->add_control($id, ['label' => $label, 'section' => $sid, 'type' => $type]);
		}
	}
});
