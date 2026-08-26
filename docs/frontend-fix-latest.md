# DDG frontend fix report — latest

## Kết luận

Vòng này phát hiện và sửa một lỗi P0 thực sự trong chính source runtime verification/media repair, không phải lỗi suy đoán từ frontend.

Hai vấn đề đã được xác nhận bằng code hiện tại:

1. `RuntimeStatus.php` đọc sai tên key của Product Media Repair report (`processed`, `products_found`, `featured_repaired`) trong khi engine thật ghi `manifest_total`, `matched_products`, `already_valid`, `repaired`, `public_products`.
2. Runtime auto-repair có thể coi repair là complete dù vẫn còn `product_not_found` hoặc `poster_missing`, và sẽ return sớm chỉ vì option version đã bằng `1.0.0`.

Điều này có thể tạo false PASS: endpoint báo sạch hoặc runtime ngừng retry trong khi manifest 44 record chưa resolve đủ.

**Production vẫn CHƯA XÁC MINH deploy.** Không gọi production PASS trước khi endpoint live xác nhận exact deployed SHA + repair clean.

## Fix mới

### Runtime status phản ánh đúng report thật

File: `apps/bizrise-ddg-migrator/src/RuntimeStatus.php`

Commit: `23873213d600c7a29304b36aab8bdb206e7dc366`

Thay đổi:

- Map đúng counters từ `ProductMediaRepair::run()`:
  - `manifest_total`
  - `matched_products`
  - `already_valid`
  - `repaired` → API field `featured_repaired`
  - `public_products`
- Expose thêm read-only counts:
  - `product_not_found_count`
  - `poster_missing_count`
  - ambiguity counts
  - `error_count`
- `repair_clean` chỉ hợp lệ khi:
  - `manifest_total=44`
  - `matched_products=44`
  - `product_not_found=[]`
  - `product_ambiguous=[]`
  - `poster_missing=[]`
  - `poster_ambiguous=[]`
  - `public_missing_featured=[]`
  - `errors=[]`

### Runtime repair không được dừng khi report chưa exact-clean

File: `apps/bizrise-ddg-migrator/bizrise-ddg-migrator.php`

Commit: `44cb2046d72ec85a5ecc155f7c5b3c02b124b136`

Thay đổi:

- Migrator version `0.3.5` → `0.3.6`.
- Trước khi return do repair version đã complete, runtime kiểm lại saved report phải exact-clean đủ 44 record.
- Nếu saved report cũ từng được đánh complete sai nhưng còn missing/ambiguity, runtime vẫn chạy repair lại.
- Clean gate sau repair bổ sung `product_not_found` và `poster_missing`, đồng thời bắt buộc `manifest_total=44` và `matched_products=44`.
- Nếu report chưa sạch, xóa completion option và backoff 5 phút để tiếp tục retry; không kẹt ở false-complete state.
- Không thay đổi Product Truth, taxonomy, publish/HOLD hay media mapping.

## Bằng chứng source

`ProductMediaRepair::run()` hiện ghi report bằng các key `manifest_total`, `matched_products`, `already_valid`, `repaired`, `product_not_found`, `product_ambiguous`, `poster_missing`, `poster_ambiguous`, `public_products`, `public_missing_featured`, `errors`.

Fix runtime/status hiện dùng đúng schema đó và cùng một exact-clean gate.

## Test / QA

- Source review exact schema giữa `ProductMediaRepair.php`, `RuntimeStatus.php` và runtime init: PASS sau fix.
- Không có fuzzy-map mới.
- Không có UI override/plugin legacy.
- GitHub combined status API tại thời điểm kiểm tra chưa trả status records cho commit `44cb2046...`; vì vậy exact HEAD CI vẫn **CHƯA XÁC MINH**, không suy diễn PASS.

## Blocked / production evidence còn thiếu

Sau deploy cần đọc:

`/wp-json/bizrise-ddg/v1/runtime-status`

PASS bắt buộc:

- `release.present=true`
- `release.sha` là exact SHA đã qua Validate + Release CI
- `status=repair_clean`
- `repair.manifest_total=44`
- `repair.matched_products=44`
- `repair.product_not_found_count=0`
- `repair.product_ambiguous_count=0`
- `repair.poster_missing_count=0`
- `repair.poster_ambiguous_count=0`
- `repair.public_missing_featured=[]`
- `repair.error_count=0`

## QA recheck bắt buộc

1. Xác minh exact deployed SHA và CI của SHA đó.
2. Đọc runtime endpoint và kiểm đủ exact-clean 44 record như trên.
3. `/san-pham/`: không placeholder `ĐĂNG DƯƠNG`, ảnh đúng SKU, stage 9:16, không crop.
4. Audit 100% product public: product → `_thumbnail_id` → attachment filename → manifest expected.
5. Category filter không legacy/rác và trả đúng SKU.
6. >=8 single product nhiều brand: title/brand/pack/image đúng, HOLD/draft không public.
7. Desktop >=1180 và mobile 360/390/430: header/menu/cards/layout PASS.
8. `/kien-thuc/` + >=5 article live và crawl page chính: không 404/broken link/duplicate H1/CTA sai.

## Deploy

Source đã commit/push trực tiếp lên `codex/rebuild-v2`.

**Không gọi là production deployed hoặc production PASS cho tới khi runtime endpoint/deploy log xác nhận.**
