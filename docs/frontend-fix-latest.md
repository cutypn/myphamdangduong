# DDG frontend fix report — latest

## Kết luận

Vòng này xử lý trực tiếp blocker P0-03 trong audit: production trước đây không có endpoint V2 độc lập để QA/PO đọc deployed SHA và Product Media Repair report mà không cần vào wp-admin hoặc phụ thuộc Deploy Bridge. Migrator V2 hiện có endpoint read-only `/wp-json/bizrise-ddg/v1/runtime-status`, trả release marker đã sanitize và trạng thái repair deterministic. Không đổi Product Truth, taxonomy, publish/HOLD hay media mapping.

**Production vẫn CHƯA XÁC MINH deploy.** Endpoint mới chỉ có hiệu lực sau khi SHA chứa fix được deploy lên WordPress.

## P0-03 — Runtime/deploy state phải đọc được

### Fix mới

Files:

- `apps/bizrise-ddg-migrator/src/RuntimeStatus.php`
- `apps/bizrise-ddg-migrator/bizrise-ddg-migrator.php`

Thay đổi:

- Migrator version tăng `0.3.4` → `0.3.5`.
- Đăng ký REST endpoint public read-only: `/wp-json/bizrise-ddg/v1/runtime-status`.
- Endpoint chỉ expose health metadata cần cho QA/PO, không expose filesystem path, backup path, user data hay arbitrary WordPress options.
- Đọc release marker `wp-content/.bizrise-ddg-release` và chỉ trả `branch`, `sha`, `deployed_at`, `method`.
- Đọc Product Media Repair report và trả `status`, `repair_version`, `trigger`, `ran_at`, `processed`, `products_found`, `featured_repaired`, `public_missing_featured`, ambiguity counts và error count.
- Header `Cache-Control: no-store, max-age=0` để QA không đọc trạng thái cache cũ.
- Endpoint không có write action và không nhận repo/path/SHA từ request.

Commits:

- `d8a500fef92145ae9d90e298257771c2a69cd34f` — `feat(migrator): expose runtime repair and release status`
- `aba58fb1eab9f6478555701144235733e86c73a3` — `feat(migrator): register runtime verification endpoint`

## Vì sao fix này giải quyết audit

Audit latest giữ P0-03 vì môi trường QA không đọc được Deploy Bridge/status runtime và không thể chứng minh production đang chạy SHA nào. Endpoint mới nằm ngay trong migrator V2 — component vốn đã thuộc release pipeline — nên sau deploy QA/PO có một nguồn độc lập để đối chiếu:

1. `release.sha` phải chứa fix đã CI PASS.
2. `status` phải là `repair_clean`.
3. `repair.public_missing_featured` phải rỗng.
4. `repair.product_ambiguous_count`, `poster_ambiguous_count`, `error_count` phải bằng 0.
5. `repair.trigger` phải cho thấy repair runtime/admin/CLI thật đã chạy trên WordPress, không phải source-only test.

## P0-02 — Repair tự chạy sau deploy

Fix trước vẫn giữ nguyên:

- Migrator guarded `init` hook chạy `ProductMediaRepair::run(true)` khi repair version chưa complete.
- Không phụ thuộc admin mở wp-admin.
- Lock 10 phút + retry backoff 5 phút.
- Chỉ complete khi `errors`, `public_missing_featured`, `product_ambiguous`, `poster_ambiguous` đều rỗng.
- Runtime report lưu `trigger=runtime_init`, `ran_at`.

Commit nền: `42bc71e38cb8a5d5ac979c3ae9e97bbd0e944060`.

## P0-01 — Product thiếu Featured Image

Repair deterministic trước đó vẫn giữ nguyên:

- `apps/bizrise-ddg-migrator/data/product-media-manifest.csv` — 44 record.
- `apps/bizrise-ddg-migrator/src/ProductMediaRepair.php` — exact source filename; fallback exact brand + product name + pack size; poster exact manifest key/basename; ambiguity thì không gán.
- Chỉ sửa Featured Image thiếu/hỏng; không fuzzy-map.
- HOLD/draft và Product Truth không bị thay đổi.

Các commit nền:

- `c50fc57200cb5071dd7b77c8423987b13967baf8` — manifest 44 poster.
- `73300d568d3f9e6c2ab8b112c44de0a756973f1f` — repair engine.
- `cd157397f2e9c580a956e2f775a9ac1a376a89ef` — wire repair vào migrator.

## Các source fix trước vẫn giữ nguyên

- Legacy media hotfix diagnostic-only; không fetch/sideload/ghi Featured Image.
- Theme/version asset thống nhất 2.1.3.
- Product card 9:16 + `object-fit: contain`.
- Category filter không whitelist 8 slug tĩnh.
- Không dùng `ddg-beauty-premium` legacy.
- Không tạo plugin UI override.

## Test đã chạy

Local syntax check:

- `php -l RuntimeStatus.php` — PASS (`No syntax errors detected`).
- Wiring trong `bizrise-ddg-migrator.php` chỉ thêm `require_once`, register hook và bump version; exact branch commit cần GitHub CI xác nhận sau push.

GitHub CI cho exact HEAD sau report update: **đang chờ workflow run xuất hiện/hoàn tất tại thời điểm ghi report**. Không coi source là CI PASS cho tới khi Validate và Release của exact HEAD đều `success`.

## Blocked / production evidence còn thiếu

**CHƯA XÁC MINH production deploy.** Sau deploy cần đọc:

`/wp-json/bizrise-ddg/v1/runtime-status`

PASS khi:

- `release.present=true`.
- `release.sha` là SHA chứa các fix V2 đã qua CI.
- `status=repair_clean`.
- `repair.public_missing_featured=[]`.
- `repair.product_ambiguous_count=0`.
- `repair.poster_ambiguous_count=0`.
- `repair.error_count=0`.

## QA recheck bắt buộc

1. Đọc `/wp-json/bizrise-ddg/v1/runtime-status` và xác minh deployed SHA + repair report.
2. `/san-pham/`: không placeholder `ĐĂNG DƯƠNG`, ảnh đúng SKU, 9:16, không crop.
3. Audit 100% product public: product → `_thumbnail_id` → attachment filename → manifest expected.
4. Category filter: không category rác/legacy và trả đúng SKU.
5. >=8 single product nhiều brand: title/brand/pack/image đúng, HOLD/draft không xuất hiện.
6. Desktop ≥1180 và mobile 360/390/430: header/menu/cards/layout PASS.
7. `/kien-thuc/` + ít nhất 5 article live: không 404, typography/CTA/internal links PASS.
8. Crawl các page chính: không broken link, duplicate H1 hoặc CTA sai.

## Deploy

Source đã commit/push lên `codex/rebuild-v2`. **Không gọi là production deployed cho tới khi runtime endpoint hoặc deploy log xác nhận SHA production.**
