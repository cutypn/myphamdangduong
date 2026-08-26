# DDG frontend fix report — latest

## Kết luận

Đã xử lý các lỗi source có bằng chứng rõ trong QA audit trên branch `codex/rebuild-v2`. Chưa deploy production. Lỗi Featured Image đang thiếu trên một số product production **chưa thể được đánh dấu PASS** vì cần chạy repair/audit trực tiếp trên WordPress DB hoặc deploy package mới rồi recheck frontend.

## Lỗi đã sửa

### P0-02 — Dừng nguồn ảnh legacy cạnh tranh Featured Image

**Đã sửa.** `apps/bizrise-ddg-media-hotfix/bizrise-ddg-media-hotfix.php` được chuyển từ auto-repair sang diagnostic-only.

- Không còn fetch catalog `myphamanhduong.vn`.
- Không sideload ảnh ngoài.
- Không gọi `set_post_thumbnail()`.
- Chỉ audit product thiếu Featured Image và cảnh báo admin.
- Product Truth + curated portrait manifest tiếp tục là source-of-truth cho Featured Image.

Commit: `4d891c4893b9a4c0a54419b5d1966ee5b8eb88e0`

### P1-01 / P1-02 — Header và asset version drift

**Đã sửa source.**

- `header.php` không còn hard-code `theme212.css?ver=2.1.2` sau `wp_head()`.
- Theme header comment đổi về 2.1.3.
- `BIZRISE_DDG_THEME_VERSION` đổi từ `2.1.2` sang `2.1.3`.
- `theme212.css` được enqueue bằng WordPress dependency graph sau `theme2.css`, dùng cùng `BIZRISE_DDG_THEME_VERSION` để cache-busting nhất quán.

Commits:

- `e0cc9acff22b0f6aa069b898c4bc6ad75ac39480`
- `a74dd35f6470b90c1219850591f052c03f851d14`

### P1-03 — Product image stage phụ thuộc cascade không ổn định

**Đã gia cố source.** `theme212.css` hiện là lớp canonical cuối cho portrait card:

- 9:16 giữ nguyên.
- image stage dùng `object-fit: contain` + `object-position:center`.
- hover không scale/crop ảnh.
- card copy dùng flex để CTA ổn định hơn.
- file banner/version đổi thành 2.1.3.

Commit: `11be52d9964f72b57d8d6b51550c67e43455c728`

### P1-04 — Filter category whitelist tĩnh

**Đã sửa.** `woocommerce/archive-product.php` không còn hard-code 8 slug danh mục. Filter lấy trực tiếp các `product_cat` đang có product, loại default Uncategorized. Việc gán category vẫn thuộc importer/Product Truth, theme không tạo taxonomy song song.

Commit: `a7d59289cbde4b4f56aebaf4a06bfb3e741ef0e4`

## Lỗi còn blocked / cần production repair

### P0-01 — Một số product production vẫn thiếu Featured Image

QA screenshot chứng minh có product card đang render placeholder `ĐĂNG DƯƠNG`, đồng nghĩa WordPress product post đó không có Featured Image hợp lệ tại thời điểm render.

Source fix ở run này đã ngăn media hotfix legacy tự lấp bằng ảnh cũ, nhưng **không tự gán ảnh mới** vì branch hiện không chứa một manifest portrait 44 SKU có thể chạy an toàn trực tiếp trên production DB và Agent Fix không được fuzzy-map.

Cần recheck/repair theo mapping deterministic:

`product ID -> Product Truth/product key -> expected portrait attachment -> _thumbnail_id`

PASS chỉ khi tất cả product public có attachment image hợp lệ và đúng SKU; HOLD/draft không lọt frontend.

## Kiểm tra source đã thực hiện

- Xác nhận branch head sau fix: `11be52d9964f72b57d8d6b51550c67e43455c728`.
- Code search sau sửa không còn chuỗi `2.1.2` trong index hiện tại.
- Code search sau sửa không còn đường `myphamanhduong.vn` + `set_post_thumbnail` trong media hotfix hiện tại.
- Không thay Product Truth.
- Không thay regulatory hold logic.
- Không thêm plugin UI override.
- Không fuzzy-map product.

### Giới hạn test

GitHub connector cho phép source read/write nhưng runtime này không mount repository để chạy `php -l` trực tiếp trên branch; outbound shell GitHub cũng bị DNS block. Vì vậy PHP lint thực thi trên checkout thật chưa được đánh dấu PASS trong run này. Các file PHP thay đổi phải được lint trong CI/checkout/deploy pipeline trước production.

## File đã thay đổi

- `apps/bizrise-ddg-media-hotfix/bizrise-ddg-media-hotfix.php`
- `apps/bizrise-ddg-theme/header.php`
- `apps/bizrise-ddg-theme/functions.php`
- `apps/bizrise-ddg-theme/assets/css/theme212.css`
- `apps/bizrise-ddg-theme/woocommerce/archive-product.php`
- `docs/frontend-fix-latest.md`

## QA recheck bắt buộc sau deploy

1. Desktop ≥1180 px: logo đúng tỉ lệ, nav + CTA hiện đầy đủ, header không crop.
2. Mobile 360/390/430 px: logo/menu không overflow, product card không vỡ title/CTA.
3. `/san-pham/`: không còn placeholder `ĐĂNG DƯƠNG`; toàn bộ ảnh nằm gọn trong portrait stage, không crop.
4. Mở toàn bộ category filter: product đúng taxonomy và không xuất hiện Uncategorized.
5. Kiểm tra ít nhất 8 single product nhiều brand: image/brand/pack đúng SKU, HOLD/draft không public.
6. Purge CDN/page cache sau deploy để asset 2.1.3 được tải mới.

## Deploy

Không deploy production trong run này vì không có đường deploy đã được xác nhận trong task. Source đã commit vào `codex/rebuild-v2`; cần deploy branch/package qua quy trình production hiện hành rồi QA vòng 2.
