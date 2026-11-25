<?php
/**
 * Bilingual VI/EN via Polylang (same setup as Infinity Homes). Without Polylang the site stays Vietnamese-only.
 *
 * UI strings: av__('Vietnamese text') → Polylang string translation if the admin entered one
 * (Ngôn ngữ → Bản dịch chuỗi), else the built-in English below when the page language is "en".
 */

const AV_EN = [
	// header / footer
	'Bỏ qua tới nội dung' => 'Skip to content',
	'Tìm kiếm' => 'Search',
	'Tìm kiếm…' => 'Search…',
	'Menu chính' => 'Main menu',
	'Chuyên mục' => 'Categories',
	'Đăng ký nhận bản tin' => 'Subscribe to our newsletter',
	'Nhận những câu chuyện kiến trúc, nội thất và thiết kế hay nhất qua email.' => 'Get the best architecture, interiors and design stories in your inbox.',
	'Đóng' => 'Close',
	'Cảm ơn bạn đã đăng ký nhận tin!' => 'Thanks for subscribing!',
	'Đã gửi liên hệ. Ban biên tập sẽ phản hồi sớm.' => 'Message sent. Our editors will get back to you soon.',
	'Chưa gửi được — vui lòng kiểm tra lại email.' => 'Not sent — please check your email address.',
	// listings
	'Đọc tiếp' => 'Read more',
	'Trước' => 'Previous',
	'Sau' => 'Next',
	'Quảng cáo' => 'Advertisement',
	'Thêm %s' => 'More %s',
	'Thêm Videos' => 'More videos',
	'Thêm Short videos' => 'More short videos',
	'Tất cả' => 'All',
	'Chưa có bài viết trong mục này.' => 'No articles in this section yet.',
	'Xem thêm →' => 'See more →',
	'Kết quả cho “%s”' => 'Results for “%s”',
	'Không tìm thấy trang' => 'Page not found',
	'Bài viết' => 'Articles',
	'Không có nội dung phù hợp. Thử tìm từ khóa khác:' => 'Nothing found. Try another search:',
	// article / video / portrait
	'Trang chủ' => 'Home',
	'Chia sẻ' => 'Share',
	'Chia sẻ Facebook' => 'Share on Facebook',
	'In bài' => 'Print',
	'Sao chép link' => 'Copy link',
	'Gửi email' => 'Send by email',
	'Chưa có video.' => 'No videos yet.',
	'Videos khác' => 'More videos',
	'Xem video ↗' => 'Watch video ↗',
	'Chân dung' => 'Portraits',
	'Tổng quan' => 'Overview',
	'Thông tin cá nhân' => 'Personal information',
	'Tác phẩm' => 'Works',
	'Trở lại đầu trang ↑' => 'Back to top ↑',
	// about / forms
	'Về chúng tôi' => 'About us',
	'Địa chỉ' => 'Address',
	'Liên hệ' => 'Contact',
	'Họ Tên Bạn' => 'Your name',
	'Công ty, Doanh nghiệp & Tổ chức' => 'Company / Organisation',
	'Bộ phận & chức vụ' => 'Department & position',
	'Số điện thoại' => 'Phone number',
	'Chọn nội dung liên hệ' => 'Choose a topic',
	'Chi tiết các nội dung cần liên hệ & trao đổi' => 'Tell us more',
	'Nội dung' => 'Message',
	'Gửi' => 'Send',
	'Email của bạn' => 'Your email',
	'Đăng ký' => 'Subscribe',
	// default settings text (Customize → Artvibes); edited text is translated in Polylang's string list
	'Artvibes là mạng xã hội định hình phong cách sống thế hệ mới. Từ những triển lãm nghệ thuật đương đại, tuyệt tác kiến trúc thế giới cho đến nghệ thuật sắp đặt nội thất tinh tế'
		=> 'Artvibes is a social network shaping the lifestyle of a new generation. From contemporary art exhibitions and world architectural masterpieces to refined interior installations',
	"Hợp tác nội dung\nQuảng cáo\nGóp ý\nKhác" => "Content partnership\nAdvertising\nFeedback\nOther",
	"Kết nối để cùng tạo giá trị!\n\nMọi đề xuất hợp tác, trao đổi thông tin hoặc câu hỏi liên quan đến nội dung và hoạt động của The Artvibes, vui lòng liên hệ với Ban Biên tập qua info@artvibes.vn hay biểu mẫu dưới đây."
		=> "Let's create value together!\n\nFor partnership proposals, information or questions about The Artvibes' content and activities, please contact the Editorial Board at info@artvibes.vn or use the form below.",
	"HÀ NỘI OFFICE\n97-99 Láng Hạ\nĐống Đa District\nHà Nội City\nTel. +84 934 600 603\nEmail: info@artvibes.vn\n\nSÀI GÒN SHOWROOM\n43 Street Number 3\nPhú Mỹ Ward, District 7\nHồ Chí Minh City\nTel. +84 934 600 603\nEmail: info@artvibes.vn\n\nUSA OFFICE\n3308 Laventure Dr, Atlanta GA 30341\nB-150\nAtlanta GA\nTel. +84 934 600 603\nEmail: info@artvibes.vn"
		=> "HANOI OFFICE\n97-99 Lang Ha\nDong Da District\nHanoi\nTel. +84 934 600 603\nEmail: info@artvibes.vn\n\nSAIGON SHOWROOM\n43 Street Number 3\nPhu My Ward, District 7\nHo Chi Minh City\nTel. +84 934 600 603\nEmail: info@artvibes.vn\n\nUSA OFFICE\n3308 Laventure Dr, Atlanta GA 30341\nB-150\nAtlanta GA\nTel. +84 934 600 603\nEmail: info@artvibes.vn",
];

/** Settings whose text is translated per language. */
const AV_TRANSLATED_MODS = ['av_footer_desc', 'av_copyright', 'av_offices', 'av_contact_intro', 'av_contact_topics'];

function av__($s) {
	$t = function_exists('pll__') ? pll__($s) : $s;
	if ($t === $s && function_exists('pll_current_language') && pll_current_language() === 'en') $t = AV_EN[$s] ?? $s;
	return $t;
}

function av_home() {
	return function_exists('pll_home_url') ? pll_home_url() : home_url('/');
}

/** Term by its (Vietnamese) slug, returned in the page language. */
function av_term($slug, $tax = 'category') {
	$t = get_terms(['taxonomy' => $tax, 'slug' => $slug, 'hide_empty' => false, 'lang' => '']);
	if (!$t || is_wp_error($t)) return null;
	$id = function_exists('pll_get_term') ? (pll_get_term($t[0]->term_id) ?: $t[0]->term_id) : $t[0]->term_id;
	return get_term($id, $tax);
}

/** IDs of a term and all its translations (for tax queries that must work in every language). */
function av_term_ids($slug, $tax = 'av_video_type') {
	$t = get_terms(['taxonomy' => $tax, 'slug' => $slug, 'hide_empty' => false, 'lang' => '']);
	if (!$t || is_wp_error($t)) return [0];
	$ids = function_exists('pll_get_term_translations') ? array_values(pll_get_term_translations($t[0]->term_id)) : [];
	return $ids ?: [$t[0]->term_id];
}

function av_lang_switcher() {
	if (!function_exists('pll_the_languages')) return '';
	$langs = pll_the_languages(['raw' => 1, 'hide_if_empty' => 0]);
	if (!is_array($langs) || count($langs) < 2) return '';
	$out = '';
	foreach ($langs as $l) {
		$out .= '<a href="' . esc_url($l['url']) . '" hreflang="' . esc_attr($l['locale']) . '"' . ($l['current_lang'] ? ' class="is-active" aria-current="true"' : '') . '>' . esc_html(strtoupper($l['slug'])) . '</a>';
	}
	return '<nav class="av-lang" aria-label="Language">' . $out . '</nav>';
}

/* ---------- Polylang wiring ---------- */

add_filter('pll_get_post_types', fn($types) => $types + ['av_video' => 'av_video', 'av_portrait' => 'av_portrait']);
add_filter('pll_get_taxonomies', fn($tax) => $tax + ['av_video_type' => 'av_video_type']);

/** New translations start with all fields copied; only language-neutral ones stay in sync afterwards. */
add_filter('pll_copy_post_metas', function ($keys, $sync) {
	$shared = ['_av_video', '_av_views', '_av_hero', '_av_gallery'];
	$text = ['_av_sub', '_av_intro', '_av_words', '_av_photo', '_av_credits', '_av_info', '_av_works', '_av_tag'];
	return array_merge($keys, $shared, $sync ? [] : $text);
}, 10, 2);

/** Expose UI strings and settings text in Ngôn ngữ → Bản dịch chuỗi. */
add_action('init', function () {
	if (!is_admin() || !function_exists('pll_register_string')) return;
	foreach (array_keys(AV_EN) as $s) pll_register_string(mb_substr($s, 0, 40), $s, 'Artvibes', strpos($s, "\n") !== false);
	foreach (AV_TRANSLATED_MODS as $m) pll_register_string($m, av_mod($m), 'Artvibes – Tùy biến', true);
});
