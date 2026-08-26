# DDG frontend QA audit — latest

## Kết luận nhanh

**Trạng thái tổng thể: FAIL / SOURCE IMPROVED — production chưa được xác nhận PASS.**

QA recheck sau các commit fix mới nhất trên `codex/rebuild-v2`. Branch HEAD quan sát tại thời điểm audit: `de1fde421ffbd61e4db0262b31773794dde19535` (`docs(qa): record frontend fixes and production recheck blockers`).

Frontend production vẫn không fetch trực tiếp được từ môi trường QA này: request tới `https://dangduonggroup.com/` trả `Cache miss`, nên không thể tự click/viewport-test production trong run. Vì vậy trạng thái live không được suy đoán. Audit dùng: source branch hiện tại, `docs/frontend-fix-latest.md`, screenshot production gần nhất đã cung cấp, và logic WordPress/WooCommerce trong repo.

## Delta so với audit trước

| Hạng mục | Trạng thái source | Trạng thái production |
|---|---|---|
| P0-01 Product thiếu Featured Image | CHƯA SỬA DB | FAIL theo screenshot gần nhất; live recheck blocked |
| P0-02 Legacy media hotfix override | SOURCE FIXED | CHƯA XÁC MINH deploy/activation |
| P1-01 Header/logo desktop | SOURCE FIXED một phần qua asset loading/version | CHƯA XÁC MINH live |
| P1-02 Theme version drift 2.1.2/2.1.3 | SOURCE FIXED | CHƯA XÁC MINH deploy/cache |
| P1-03 Product image `object-fit` cascade | SOURCE FIXED | CHƯA XÁC MINH live |
| P1-04 Category whitelist tĩnh | SOURCE FIXED | CHƯA XÁC MINH taxonomy DB/live |
| P1-05 8 single-product live QA | BLOCKED | CHƯA TEST |

## Phạm vi recheck

| Khu vực | Cách kiểm tra | Kết quả |
|---|---|---|
| `/` | source + production screenshot cũ | PARTIAL |
| `/ve-dang-duong/` | source + production screenshot cũ | PARTIAL |
| `/nang-luc/` | source | SOURCE REVIEW ONLY |
| `/thuong-hieu/` | source | SOURCE REVIEW ONLY |
| `/san-pham/` | source + screenshot production | FAIL LIVE EVIDENCE / SOURCE IMPROVED |
| product categories | archive source | SOURCE IMPROVED / LIVE BLOCKED |
| 8+ product detail | template source | LIVE BLOCKED |
| `/kien-thuc/` | source/article registry | SOURCE PASS / LIVE BLOCKED |
| 5+ bài viết | registry + templates | SOURCE PASS / LIVE BLOCKED |
| mobile 360/390/430 | responsive CSS source | LIVE BLOCKED |

## P0 — blocker production

### P0-01 — Product public thiếu Featured Image

- **URL/khu vực:** `/san-pham/`, product category archives, single product.
- **Bằng chứng production gần nhất:** nhiều card hiển thị placeholder `ĐĂNG DƯƠNG` thay vì ảnh sản phẩm.
- **Bằng chứng source:** renderer chỉ dùng placeholder khi `has_post_thumbnail($product_id)` false.
- **Tình trạng hiện tại:** source fix đã ngăn legacy hotfix tự gán ảnh ngoài, nhưng **không sửa `_thumbnail_id` trong WordPress DB**.
- **Nguyên nhân còn khả dĩ:** product public thực tế chưa có `_thumbnail_id`; attachment bị thiếu/xóa; duplicate/legacy product đang lọt archive; mapping 44 poster đã chạy trên post khác với post đang public.
- **PASS bắt buộc:** audit 100% product public theo `product ID -> product key -> brand -> category -> _thumbnail_id -> attachment filename`; không còn placeholder; attachment khớp deterministic manifest; HOLD/draft không xuất hiện.

### P0-02 — Legacy media override

- **Source recheck:** `bizrise-ddg-media-hotfix` đã được đổi sang diagnostic-only theo fix report; không còn fetch catalog ngoài, sideload hay `set_post_thumbnail()`.
- **Đánh giá QA:** **SOURCE PASS**.
- **Production:** **CHƯA XÁC MINH** vì chưa có bằng chứng branch này đã deploy/active trên live.
- **PASS production:** xác nhận plugin/live code không còn tự ghi Featured Image và không có process khác override portrait poster.

## P1 — lỗi/QA quan trọng

### P1-01 — Header/logo desktop

- Screenshot production gần nhất từng cho thấy logo/header không đúng layout source.
- Source đã bỏ hard-code `theme212.css?ver=2.1.2`, chuyển asset về enqueue có dependency/version thống nhất.
- **Source status:** IMPROVED.
- **Live status:** CHƯA XÁC MINH.
- **PASS:** desktop >=1180px có logo đúng tỉ lệ, nav + CTA đầy đủ, sticky header không crop/che hero; mobile dùng đúng menu ở breakpoint.

### P1-02 — Theme version drift

- Fix report xác nhận `BIZRISE_DDG_THEME_VERSION` và asset layer đã đồng bộ 2.1.3; code search sau fix không còn `2.1.2` trong index hiện tại.
- **Source status:** PASS theo evidence trong fix report.
- **Production PASS:** deploy đúng HEAD, purge cache/CDN, asset response dùng version 2.1.3.

### P1-03 — Portrait product image stage

- `theme212.css` hiện là lớp canonical cuối với 9:16, `object-fit: contain`, `object-position:center`, không hover-scale crop.
- **Source status:** PASS.
- **Live status:** CHƯA XÁC MINH.
- **PASS:** mọi card ảnh nằm gọn trong 9:16 trên desktop/mobile, không crop và không nhảy chiều cao bất thường.

### P1-04 — Product category filter

- Archive source không còn whitelist 8 slug tĩnh; filter lấy `product_cat` hiện có product, loại Uncategorized.
- **Source status:** PASS cho phần render filter.
- **Data status:** CHƯA PASS vì đúng/sai category vẫn phụ thuộc taxonomy trong WordPress DB/importer.
- **PASS:** đối chiếu toàn bộ product public với expected category deterministic; filter không xuất category rác/legacy; mỗi category mở ra đúng SKU.

### P1-05 — QA ít nhất 8 single product nhiều brand

- **Live status:** CHƯA TEST do frontend fetch bị chặn.
- **PASS:** mở ít nhất 8 SKU thuộc nhiều brand/collection; kiểm image, title, brand, pack, CTA, hồ sơ công bố nếu có, related section; không kéo HOLD/draft.

## P2 — UX cần browser QA

### Mobile cards

Dưới breakpoint nhỏ, cần test thật ở 360/390/430px với title dài để xác nhận không overflow, không chữ đè ảnh, CTA không trôi và card vẫn tap được.

### Related products

Single-product hiện cần xác nhận UX thực tế. Nếu section dùng nhãn `Khám phá thêm`, query rộng có thể chấp nhận; nếu gọi `Sản phẩm liên quan`, nên kiểm logic category/brand/routine.

## H1 / bài viết / nội dung

- `page.php`: một H1 từ page title.
- `single.php`: một H1 article title; related section H2.
- `single-product.php`: một H1 product title; title lặp ở phần chi tiết là H2.
- Article registry có 10 bài nguồn hoàn chỉnh ở `editorial_review`; không tự publish chỉ vì source hoàn tất.

**Source:** PASS cho duplicate-H1 review và article source completeness.

**Production:** `/kien-thuc/` và ít nhất 5 bài viết vẫn CHƯA XÁC MINH live. Cần kiểm 200/404, ảnh, excerpt, typography, internal link, CTA và mobile layout.

## Broken links / CTA

Source chưa cho thấy broken link chắc chắn, nhưng helper fallback theo slug có thể vẫn tạo URL 404 nếu page chưa tồn tại trong WordPress DB. Cần live crawl.

Recheck bắt buộc: `/san-pham/`, `/doi-tac/`, `/lien-he/`, `/tim-diem-ban/`, `/kien-thuc/`, `/nghien-cuu-phat-trien/`, `/oem-odm-my-pham/`.

## Tiêu chí QA vòng tiếp theo

1. Có bằng chứng deploy production của branch/HEAD mới nhất.
2. Purge cache/CDN và xác nhận asset theme 2.1.3.
3. Audit 100% product public `_thumbnail_id` + attachment filename + expected manifest/product key.
4. `/san-pham/` và toàn bộ category không còn placeholder, không ảnh legacy/crop.
5. Desktop >=1180px: header/logo/nav/CTA PASS.
6. Mobile 360/390/430px: header/menu/card PASS.
7. Mở >=8 single product nhiều brand và xác minh facts/media/HOLD.
8. Mở `/kien-thuc/` + >=5 article live; không 404, layout/CTA/internal links PASS.

## Kết luận QA

Các commit fix đã giải quyết phần lớn **lỗi source** phát hiện ở vòng trước, đặc biệt media hotfix cạnh tranh, version drift, asset cascade và category filter. Tuy nhiên **production vẫn chưa thể được đánh dấu PASS** vì blocker thật còn nằm ở Featured Image trong WordPress DB và chưa có live browser/deploy evidence để xác minh các source fix đã lên production.
