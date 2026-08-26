# DDG frontend QA audit — latest

## Kết luận nhanh

**Trạng thái: FAIL — chưa nên coi frontend là hoàn tất.**

Frontend production không thể được fetch trực tiếp từ môi trường QA hiện tại: request tới `https://dangduonggroup.com/` bị `DisabledError`, nên không thể tự click toàn bộ URL hoặc mô phỏng viewport thật trong run này. Audit dưới đây dùng ba nguồn: (1) screenshot production mới nhất do người dùng cung cấp trong cuộc trao đổi, (2) source hiện tại của branch `codex/rebuild-v2`, và (3) dữ liệu/logic importer-media trong cùng branch. Không suy diễn DNS/hosting.

Hai lỗi có bằng chứng production rõ nhất là **ảnh Featured Image mất ở nhiều product card** và **header/logo desktop hiển thị sai/collapse**. Ngoài ra source đang có drift phiên bản CSS/theme và một media hotfix có thể tái đưa ảnh catalog cũ vào Featured Image, tạo đúng loại lỗi hình ảnh không đồng nhất mà production đang thể hiện.

## Phạm vi đã kiểm tra

| Khu vực | Cách kiểm tra | Kết quả |
|---|---|---|
| `/` | source Theme 2 + screenshot production | PARTIAL |
| `/ve-dang-duong/` | page template + editorial source | PARTIAL |
| `/nang-luc/` | page template + editorial source | PARTIAL |
| `/thuong-hieu/` | page template/source | PARTIAL |
| `/san-pham/` | screenshot production + archive template + product card renderer | FAIL |
| product category | archive routing/taxonomy source | PARTIAL / RISK |
| product detail | `woocommerce/single-product.php` + media logic | PARTIAL / RISK |
| `/kien-thuc/` | page template + article registry | PARTIAL |
| 5+ bài viết | source files/registry/single template | SOURCE PASS, LIVE BLOCKED |
| mobile | responsive CSS source only; không có live viewport | SOURCE REVIEW ONLY |

## P0 — cần sửa trước

### P0-01 — Nhiều product card production không có Featured Image

- **URL/khu vực:** `/san-pham/` và các archive/category sản phẩm.
- **Bằng chứng production:** screenshot mới nhất hiển thị dãy ONE TODAY; ít nhất 2 card có vùng ảnh trắng và placeholder chữ `ĐĂNG DƯƠNG`, trong khi các card cạnh bên có ảnh hũ sản phẩm.
- **Bằng chứng source:** `apps/bizrise-ddg-theme/functions.php` trong `ddg_theme2_card_product()` chỉ render placeholder `ĐĂNG DƯƠNG` khi `has_post_thumbnail($product_id)` trả về false. Vì vậy đây không phải lỗi CSS che ảnh; tại thời điểm render, product post đó không có Featured Image hợp lệ theo WordPress.
- **Nguyên nhân khả dĩ:** `_thumbnail_id` chưa được gán cho các product post thực sự đang xuất hiện trong archive; archive có duplicate/legacy posts ngoài tập đã repair; hoặc Featured Image từng gán đã bị thay/xóa sau repair.
- **File/source liên quan:** `apps/bizrise-ddg-theme/functions.php`; importer/repair media đang active trên production; product database.
- **Tiêu chí PASS:** duyệt toàn bộ product public và xác nhận `has_post_thumbnail() === true`; không còn placeholder `ĐĂNG DƯƠNG`; mỗi product public có đúng một Featured Image đã xác minh theo manifest/product identity.

### P0-02 — Media hotfix có thể tái đưa ảnh catalog cũ vào Featured Image

- **URL/khu vực:** mọi product archive và single product.
- **Bằng chứng source:** `apps/bizrise-ddg-media-hotfix/bizrise-ddg-media-hotfix.php` chạy `maybe_repair()` ở `init` và với product thiếu thumbnail có thể discover/sideload ảnh từ `myphamanhduong.vn`, sau đó gọi `set_post_thumbnail()`.
- **Tác động:** hệ thống có hai nguồn hình ảnh cạnh tranh: bộ poster portrait curated mới và catalog ngoài/legacy. Khi thumbnail thiếu, hotfix có thể lấp lại bằng ảnh kiểu cũ, làm catalog không đồng nhất dù repair poster trước đó báo thành công.
- **Nguyên nhân khả dĩ:** plugin hotfix vẫn active hoặc option/version khiến nó chạy lại trên product thiếu ảnh.
- **File/source liên quan:** `apps/bizrise-ddg-media-hotfix/bizrise-ddg-media-hotfix.php`, đặc biệt `maybe_repair()`, `repair_catalog_sources()`, `repair_product()`.
- **Tiêu chí PASS:** production chỉ có **một** source-of-truth cho Featured Image; hotfix legacy không được phép override curated portrait poster; audit 100% product public xác nhận attachment ID/filename khớp manifest mong muốn.

## P1 — lỗi quan trọng

### P1-01 — Header/logo production hiển thị sai trên desktop

- **URL/khu vực:** screenshot production trang `Câu chuyện Đăng Dương` (`/ve-dang-duong/` theo nội dung).
- **Bằng chứng production:** logo ở góc trên trái bị nhỏ/cắt sát mép trên; navigation và CTA header không hiện trong vùng header, trong khi nội dung hero bắt đầu gần sát top. Đây không giống layout desktop được định nghĩa trong source.
- **Bằng chứng source:** `header.php` định nghĩa sticky header 3 cột gồm logo, nav và CTA; `theme2.css` định nghĩa `min-height:78px`; `theme212.css` lại đổi thành `min-height:92px` và logo tối đa 270×76.
- **Nguyên nhân khả dĩ:** CSS version/cache drift hoặc stylesheet chồng nhau không đồng bộ production; cũng cần kiểm tra custom logo file/crop và plugin/CDN optimization.
- **File/source liên quan:** `apps/bizrise-ddg-theme/header.php`, `assets/css/theme2.css`, `assets/css/theme212.css`.
- **Tiêu chí PASS:** desktop ≥1180px hiển thị đầy đủ logo đúng tỉ lệ, primary nav và CTA trong header; không crop logo; sticky không che nội dung; mobile menu chỉ áp dụng dưới breakpoint dự kiến.

### P1-02 — Theme version drift làm cache-busting không đáng tin

- **Bằng chứng source:** `style.css` khai báo Theme **2.1.3**, nhưng `functions.php` vẫn `BIZRISE_DDG_THEME_VERSION = '2.1.2'`; `header.php` cũng ghi Theme 2.1.2 và hard-code `theme212.css?ver=2.1.2`.
- **Tác động:** asset mới có thể tiếp tục dùng cache key 2.1.2; CDN/browser có khả năng phục vụ CSS cũ dù package/theme header là 2.1.3. Đây là ứng viên trực tiếp cho việc production nhìn khác source/mockup.
- **File/source liên quan:** `apps/bizrise-ddg-theme/style.css`, `functions.php`, `header.php`.
- **Tiêu chí PASS:** một version duy nhất cho theme và toàn bộ CSS/JS; không hard-code asset version cũ; purge cache sau deploy và kiểm tra network response asset mới.

### P1-03 — Hai lớp CSS product card cùng điều khiển `object-fit`

- **Bằng chứng source:** `theme2.css` đặt `.t2-product-card__media img { object-fit: cover; }`; `theme212.css` đặt `.t2-product-card__image-stage img { object-fit: contain; }`. `header.php` nạp `theme212.css` ngoài hệ enqueue sau `wp_head()`.
- **Tác động:** nếu `theme212.css` không load, bị tối ưu/reorder hoặc cache sai, ảnh sẽ quay về `cover` và crop; thiết kế portrait phụ thuộc vào thứ tự cascade thay vì một rule canonical.
- **File/source liên quan:** `assets/css/theme2.css`, `assets/css/theme212.css`, `header.php`.
- **Tiêu chí PASS:** chỉ một rule canonical cho product image stage; ảnh luôn `contain`, không crop, không cần phụ thuộc thứ tự hai stylesheet.

### P1-04 — Filter danh mục frontend dùng whitelist slug tĩnh, dễ lệch taxonomy thật

- **URL/khu vực:** `/san-pham/`, product category archives.
- **Bằng chứng source:** `woocommerce/archive-product.php` chỉ hiện 8 slug hard-code: `cham-soc-da-mat`, `duong-sang-deu-mau`, `da-co-xu-huong-noi-mun`, `chong-nang`, `cham-soc-dau-hieu-lao-hoa`, `cham-soc-co-the`, `lam-sach`, `cham-soc-vung-kin`.
- **Bằng chứng data:** Product Truth seed còn dùng các category semantic như `Kem dưỡng/chăm sóc da`, `Chăm sóc body`, `Serum`, `Dung dịch vệ sinh`, `Sữa rửa mặt`, `Tẩy tế bào chết`.
- **Tác động:** nếu importer/repair không map tuyệt đối từ Product Truth sang 8 managed slugs, sản phẩm có thể nằm sai nhóm hoặc category hợp lệ không xuất hiện ở filter. Điều này phù hợp với phản hồi trước đó rằng sản phẩm sai danh mục.
- **File/source liên quan:** `woocommerce/archive-product.php`, Product Truth/importer taxonomy mapping.
- **Tiêu chí PASS:** có bảng mapping taxonomy explicit, deterministic; mỗi product public có expected category; filter lấy từ taxonomy managed source thay vì danh sách ad-hoc không được test.

### P1-05 — QA live cho product detail chưa đạt vì không thể xác minh 8 sản phẩm đại diện

- **URL/khu vực:** ít nhất 8 single product thuộc nhiều brand.
- **Bằng chứng:** frontend fetch bị chặn trong môi trường QA này; không có screenshot single-product mới trong run.
- **Source review:** template chỉ hiển thị main Featured Image; nếu thumbnail sai/mất thì single page cũng sai. Related products hiện lấy 4 product publish mới nhất toàn site, không theo brand/category.
- **File/source liên quan:** `woocommerce/single-product.php`.
- **Tiêu chí PASS:** QA browser thật mở ít nhất 8 SKU/brand, xác nhận hero image đúng sản phẩm, pack/brand đúng, CTA hoạt động, hồ sơ công bố (nếu có) đúng attachment, related products không kéo HOLD/draft.

## P2 — chất lượng/UX cần rà

### P2-01 — Mobile product card có layout rất nhạy với title dài

- **Bằng chứng source:** dưới 520px, `.t2-product-card` chuyển sang grid `42% 58%` trong khi media vẫn giữ `aspect-ratio:9/16`; phần copy title không có clamp.
- **Rủi ro:** title dài có thể làm card lệch chiều cao/khó scan; cần browser QA thật ở 360/390/430px.
- **Tiêu chí PASS:** không overflow, không chữ đè, CTA không trôi, toàn bộ card click/tap dễ sử dụng.

### P2-02 — Related products không thực sự “related”

- **Bằng chứng source:** single-product query chỉ `orderby=date DESC`, không filter taxonomy/brand/category.
- **Tác động:** UX có thể gợi ý sản phẩm không liên quan nhu cầu/brand.
- **Tiêu chí PASS:** nếu nhãn vẫn là “Khám phá thêm” thì chấp nhận; nếu muốn “sản phẩm liên quan”, query phải dựa trên brand/category/routine rõ ràng.

## H1 / bài viết / content

### Source check

- `page.php`: một `<h1>` từ page title; editorial content được render bên dưới. Không thấy duplicate H1 trong template.
- `single.php`: một `<h1>` từ article title. Related section dùng H2.
- `single-product.php`: một `<h1>` ở summary; phần chi tiết lặp title bằng H2, **không** phải duplicate H1.
- Article registry hiện có 10 bài nguồn hoàn chỉnh ở trạng thái `editorial_review`; source audit trước đó ghi rõ không auto-publish.

### Live status

Không thể xác minh `/kien-thuc/` và 5 bài viết production trong run này do fetch frontend bị chặn. Vì vậy **không được đánh dấu live PASS** chỉ dựa vào source. Agent Fix/QA recheck cần browser thật hoặc Cloud Browser để xác nhận URL, ảnh bài viết, excerpt, typography, internal links và 404.

## Broken links / CTA

Không phát hiện broken-link chắc chắn từ source vì các helper URL dùng `get_page_by_path()` rồi fallback sang slug. Tuy nhiên fallback có thể tạo URL tồn tại về mặt chuỗi nhưng 404 nếu page chưa được tạo trong DB. Cần live crawl để kết luận.

Các CTA cần recheck live: `/san-pham/`, `/doi-tac/`, `/lien-he/`, `/tim-diem-ban/`, `/kien-thuc/`, `/nghien-cuu-phat-trien/`, `/oem-odm-my-pham/`.

## Thứ tự Agent Fix nên xử lý

1. **Dừng xung đột media source**: xác định plugin nào đang gán Featured Image và vô hiệu khả năng hotfix legacy override poster curated.
2. **Audit toàn bộ product public trong DB**: product ID → title → brand → category → `_thumbnail_id` → attachment file → expected manifest. Tìm duplicate/legacy product đang lọt archive.
3. **Sửa 100% thumbnail missing** bằng deterministic mapping, sau đó purge cache.
4. **Hợp nhất version/theme assets**: 2.1.3 xuyên suốt, bỏ hard-coded `theme212.css?ver=2.1.2` hoặc merge rules vào một stylesheet canonical.
5. **Recheck header desktop/mobile**.
6. **Recheck taxonomy/filter** theo mapping được duyệt.
7. **Browser QA vòng 2**: `/`, 4 core pages, `/san-pham/`, category, 8 SKU, `/kien-thuc/`, 5 bài viết, viewport 1440/1024/768/430/390.

## Điều kiện tổng thể để chuyển PASS

- 0 product public thiếu Featured Image.
- 0 product public dùng ảnh sai SKU hoặc ảnh legacy ngoài manifest đã duyệt.
- 100% card product giữ khung đứng và image `contain` nhất quán.
- Category của toàn bộ product public khớp mapping expected.
- Header/logo/nav/CTA đúng ở desktop và mobile.
- Không có 404 ở menu/CTA chính.
- Một H1 mỗi page/template.
- `/kien-thuc/` và ít nhất 5 article live render đầy đủ, không layout vỡ.
- Cache/CDN đã purge và asset version production khớp source deploy.

## Giới hạn của run này

Không có browser/live fetch khả dụng cho domain production trong môi trường QA hiện tại, nên không giả vờ đã click toàn bộ frontend. Báo cáo này phân biệt rõ **production evidence từ screenshot** và **source-level risk**. Vòng QA kế tiếp nên chạy bằng Work/Cloud Browser hoặc một browser runner có quyền truy cập public frontend để chụp screenshot + console/network cho từng URL.