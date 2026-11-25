# The Artvibes — WordPress theme

Theme tạp chí phong cách sống **The Artvibes**, dựng từ file thiết kế Figma (Trang chủ, Chuyên mục, Bài viết, Videos, Chân dung, About/Liên hệ). Phần quản trị là WordPress admin, cùng cách tổ chức với Infinity Homes: loại nội dung riêng, trường dữ liệu riêng, cài đặt theme và hộp thư liên hệ.

## Xem demo

- **Website (bản tĩnh, VI + EN):** https://artvibesinfinity.github.io/artvibes/ — tiếng Anh: https://artvibesinfinity.github.io/artvibes/en/ (form liên hệ/đăng ký và tìm kiếm không chạy trên bản tĩnh).
- **WordPress chạy thật + trang admin:** [mở trên WordPress Playground](https://playground.wordpress.net/?blueprint-url=https://raw.githubusercontent.com/artvibesinfinity/artvibes/main/blueprint.json) — chạy ngay trong trình duyệt, đã đăng nhập admin, tự cài Polylang + theme + dữ liệu mẫu (đợi 1–3 phút). Dữ liệu chỉ nằm trong trình duyệt của bạn, đóng tab là mất.

## Cài đặt

1. Tải repo về và đặt vào `wp-content/themes/artvibes` (hoặc nén zip rồi vào **Giao diện → Giao diện → Thêm mới → Tải lên**).
2. Kích hoạt theme **The Artvibes**.
3. Vào **Giao diện → Dữ liệu mẫu → Nhập dữ liệu mẫu**. Bước này tạo chuyên mục, 27 bài viết, 11 video, 3 chân dung, trang About, menu và cài đặt giống bản Figma. Chạy lại không tạo trùng.
4. **Cài đặt → Đường dẫn tĩnh**: chọn "Tên bài viết" (trình nhập đã tự đặt nếu đang trống).

Yêu cầu: WordPress 6.2+, PHP 7.4+. Song ngữ cần plugin Polylang (miễn phí); không có Polylang site vẫn chạy tiếng Việt.

## Quản trị (wp-admin)

| Menu | Dùng để |
|---|---|
| **Bài viết** | Bài tạp chí. Hộp *Thông tin bài viết*: phụ đề (dòng 2 dưới tiêu đề), đoạn giới thiệu, Words, Photography, link video, Credits (`Vai trò \| Tên` mỗi dòng), tick *Hiện ở slider trang chủ*. **Đoạn trích** = đoạn mở đầu (lead) của bài. |
| **Chuyên mục** | Xu hướng, Thiết kế (Kiến trúc, Nội thất…), Life Style (Hội họa, Văn hóa, Đời sống…). Mục con tự hiện thành các nút lọc trên trang chuyên mục. |
| **Videos** | Video + *Loại video* (Video, Podcast, Short Video). Trường: phụ đề, link YouTube/Vimeo (tự nhúng player), lượt xem (hiện trên Short video). |
| **Chân dung** | Hồ sơ nhân vật: Thông tin cá nhân (`Nhãn \| Giá trị`), Tác phẩm (`Tên \| Năm`), ảnh cột phải, nội dung = Tổng quan. |
| **Liên hệ** | Tin nhắn từ form trang About (đồng thời gửi email). |
| **Đăng ký nhận tin** | Email từ nút Subscribe. |
| **Giao diện → Tùy biến → Artvibes** | Trang chủ (chuyên mục hiển thị & thứ tự, quảng cáo cột phải), banner quảng cáo, mạng xã hội, văn phòng & ảnh trang About, lựa chọn form liên hệ, email nhận liên hệ, chân trang. |
| **Giao diện → Menu** | *Menu chính* (header, hỗ trợ menu con) và *Menu chân trang* (mỗi mục cấp 1 là một cột). |

Trang About dùng mẫu trang **About (Giới thiệu – Địa chỉ – Liên hệ)**: nội dung trang = "Về chúng tôi", ảnh đại diện = banner.

## Song ngữ Việt – Anh (Polylang, giống Infinity Homes)

1. Cài và bật plugin **Polylang** (miễn phí) — trước khi bấm *Nhập dữ liệu mẫu*.
2. Trình nhập tự tạo ngôn ngữ **Tiếng Việt (mặc định)** + **English**, chuyên mục/thẻ/loại video tiếng Anh, bản tiếng Anh cho mọi bài, video, chân dung, trang About (`/en/about-us/`) và menu riêng cho từng ngôn ngữ. Trang tiếng Anh nằm dưới `/en/`.
3. Header có nút chuyển **VI | EN**, trỏ thẳng tới bản dịch của trang đang xem.

Khi viết bài mới: tạo bài tiếng Việt, rồi bấm dấu **+** ở cột English (hộp *Ngôn ngữ* trong trình soạn thảo) để tạo bản tiếng Anh — các trường Phụ đề, Credits… được chép sẵn để dịch; link video, lượt xem, ảnh và tick slider luôn đồng bộ giữa hai bản.

Chữ giao diện (nút, nhãn form, "Xem thêm"…) và các đoạn văn trong *Tùy biến → Artvibes* đã có sẵn bản tiếng Anh; muốn sửa thì vào **Ngôn ngữ → Bản dịch chuỗi**, nhóm *Artvibes*. Không cài Polylang thì site chạy một ngôn ngữ tiếng Việt như bình thường.

## Font

Figma dùng **SVN-Jeko** (font thương mại, không đóng gói trong repo). Theme tự dùng SVN-Jeko nếu máy người xem đã cài, nếu không thì dùng **Be Vietnam Pro** (Google Fonts). Muốn dùng SVN-Jeko cho mọi người: thêm file `.woff2` có bản quyền vào `assets/fonts/` và khai báo `@font-face` ở đầu `style.css`.

## Chạy thử trên máy (Docker)

```bash
THEME_DIR=$PWD docker compose up -d
docker compose run --rm cli wp core install --url=http://localhost:8088 --title="The Artvibes" --admin_user=admin --admin_password=<mật-khẩu> --admin_email=you@example.com --skip-email
docker compose run --rm cli wp theme activate artvibes
```

Mở http://localhost:8088/wp-admin → Giao diện → Dữ liệu mẫu.

## Cấu trúc

```
style.css            CSS toàn bộ giao diện (biến màu/font ở :root)
functions.php        setup, helper render thẻ bài/khối, form
inc/admin.php        loại nội dung, trường dữ liệu, Tùy biến, hộp thư liên hệ
inc/demo.php         trình nhập dữ liệu mẫu (đọc demo/content.json + assets/demo/)
front-page.php       trang chủ          archive.php        trang chuyên mục
single.php           bài viết           archive-av_video.php / single-av_video.php
single-av_portrait.php  chân dung       template-about.php  About & Liên hệ
```

Ảnh trong `assets/demo/` là ảnh minh họa lấy từ file Figma — thay bằng ảnh có bản quyền trước khi chạy thật.
