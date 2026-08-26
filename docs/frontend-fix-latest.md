# DDG frontend fix report — latest

## Kết luận

Vòng này sửa một lỗi P0 có thể tái hiện trực tiếp từ source Product Media Repair: engine cũ coi **bất kỳ Featured Image hợp lệ nào** là `already_valid`, nên một sản phẩm đang dùng ảnh legacy/sai SKU vẫn được bỏ qua và report có thể sạch dù ảnh không đúng poster trong manifest 44 sản phẩm.

Source hiện đã chuyển từ kiểm tra “có ảnh hay không” sang kiểm tra **Featured Image có đúng attachment poster expected theo manifest hay không**.

**Production vẫn CHƯA XÁC MINH deploy.** Không gọi production PASS trước khi runtime endpoint live xác nhận deployed SHA và media integrity sạch.

## Lỗi P0 đã xác nhận

### P0 — Featured Image sai nhưng vẫn bị coi là hợp lệ

File cũ: `apps/bizrise-ddg-migrator/src/ProductMediaRepair.php`

Code trước fix:

- lấy `_thumbnail_id` hiện tại;
- nếu attachment tồn tại và là image thì tăng `already_valid` rồi `continue`;
- không resolve poster expected trước khi quyết định ảnh hiện tại đúng hay sai.

Hệ quả: ảnh cũ/xấu/nhầm SKU vẫn sống tiếp dù manifest đã có đúng poster.

Ngoài ra source cũ còn hai điểm làm matching kém chặt:

- source filename query so `pm.meta_value=%s` trên mọi postmeta thay vì chỉ các source-meta key đã định nghĩa;
- brand fallback quét mọi taxonomy, khiến category/tag không liên quan cũng bị coi là “brand evidence”.

## Fix mới

### 1. Enforce exact manifest Featured Image

Commit: `17f2ed5c3427ec87c022c5c130dd769be13d6e33`

File: `apps/bizrise-ddg-migrator/src/ProductMediaRepair.php`

Thay đổi chính:

- Product Media Repair version `1.0.0` → `1.1.x`.
- Resolve product và expected poster trước khi đánh giá Featured Image.
- `already_valid` chỉ khi `_thumbnail_id === expected poster attachment ID`.
- Nếu Featured Image hiện tại là image hợp lệ nhưng khác expected poster, repair sẽ thay bằng exact poster từ manifest.
- Sau `set_post_thumbnail`, kiểm lại `_thumbnail_id` phải đúng expected attachment ID; nếu không sẽ ghi error.
- Source filename matching chỉ dùng các meta key deterministic:
  - `_bizrise_source_image`
  - `_bizrise_ddg_source_filename`
  - `_bizrise_ddg_source_image`
  - `_ddg_source_filename`
- Brand matching chỉ dùng known brand meta/taxonomies; không dùng `product_cat`/tag hoặc taxonomy không liên quan làm brand evidence.
- Thêm post-audit `public_wrong_featured` để phát hiện product public còn dùng thumbnail khác manifest.

### 2. Một exact-clean gate dùng chung

Commit: `8a496abcb1b2ce1b6a09884f4f4fd4c3cc634583`

File: `apps/bizrise-ddg-migrator/bizrise-ddg-migrator.php`

Thay đổi:

- Migrator version `0.3.6` → `0.3.7`.
- Runtime retry dùng `ProductMediaRepair::is_clean_report()` thay vì copy một clean gate riêng.
- Admin repair, runtime repair và status verification giờ cùng một định nghĩa “clean”.
- Runtime vẫn có lock và backoff 5 phút khi report chưa sạch.

### 3. Runtime status expose sai-poster integrity

Commit: `76f8c55d64c24e6b960010c34ecc1a57011f1260`

File: `apps/bizrise-ddg-migrator/src/RuntimeStatus.php`

Endpoint `/wp-json/bizrise-ddg/v1/runtime-status` hiện expose thêm:

- `wrong_featured_count`
- `public_wrong_featured`
- cùng các counts product/poster missing/ambiguity/error trước đó.

`status=repair_clean` dùng trực tiếp `ProductMediaRepair::is_clean_report()`.

### 4. Phân biệt mismatch đã repair và mismatch còn unresolved

Commit: `77a2ac00a193f96b26882a1eee7ae92795bfcb8b`

Trong apply mode, một Featured Image sai được sửa thành công không còn bị giữ trong bucket unresolved `wrong_featured`; thay vào đó counter `wrong_featured_repaired` ghi số mismatch đã sửa. `public_wrong_featured` là post-audit thực tế sau repair và phải rỗng để report được clean.

Điều này tránh vòng retry vô hạn sau khi ảnh sai đã được sửa thành công.

## Test / QA đã chạy

### Static/PHP

- PHP syntax `ProductMediaRepair.php` v1.1.1: PASS.
- PHP syntax `bizrise-ddg-migrator.php` 0.3.7: PASS.
- PHP syntax `RuntimeStatus.php`: PASS.
- Source review deterministic matching: không fuzzy-map, không đổi Product Truth, taxonomy hay publish/HOLD.

### GitHub CI cho exact code HEAD

Exact code SHA: `77a2ac00a193f96b26882a1eee7ae92795bfcb8b`.

- Validate Bizrise DDG V2 — run `33002750412`: **SUCCESS**.
- Build Bizrise DDG V2 Release — run `33002750367`: **SUCCESS**.

Vì vậy source code ở SHA trên đã qua cả Validate và Release workflow.

## Production evidence còn thiếu

Sau khi exact SHA đã deploy lên WordPress, cần đọc:

`/wp-json/bizrise-ddg/v1/runtime-status`

PASS bắt buộc:

- `release.present=true`
- `release.sha` khớp exact SHA đã deploy/CI
- `status=repair_clean`
- `repair.manifest_total=44`
- `repair.matched_products=44`
- `repair.product_not_found_count=0`
- `repair.product_ambiguous_count=0`
- `repair.poster_missing_count=0`
- `repair.poster_ambiguous_count=0`
- `repair.wrong_featured_count=0`
- `repair.public_missing_featured=[]`
- `repair.public_wrong_featured=[]`
- `repair.error_count=0`

Nếu có mismatch legacy, `wrong_featured_repaired` có thể >0 ở lần repair đầu nhưng post-audit vẫn phải sạch.

## QA recheck bắt buộc

1. Xác minh deployed SHA từ runtime/deploy status.
2. Kiểm runtime report exact-clean 44/44 như trên.
3. Audit 100% product public: product key → brand/category → `_thumbnail_id` → attachment filename → manifest expected.
4. `/san-pham/` và mọi category: không placeholder, không ảnh legacy/sai SKU, stage 9:16, `object-fit: contain`.
5. >=8 single product nhiều brand: title/brand/pack/image đúng và HOLD/draft không public.
6. Desktop >=1180 và mobile 360/390/430: header/menu/cards/layout.
7. `/kien-thuc/` + >=5 article và crawl page chính: không 404/broken link/duplicate H1/CTA sai.

## Deploy

Các fix đã commit/push trực tiếp lên `codex/rebuild-v2` và exact code SHA `77a2ac00...` đã PASS Validate + Release CI.

**Không tuyên bố production đã deploy hoặc production PASS cho tới khi có log/endpoint production xác nhận.**
