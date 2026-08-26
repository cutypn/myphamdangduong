# DDG frontend fix report — latest

## Kết luận

Đã xử lý blocker mới P0-02 ở **source**: Product Media Repair không còn phụ thuộc việc admin mở `wp-admin`. Migrator V2 hiện tự chạy repair deterministic ở runtime sau deploy, có lock chống chạy song song, backoff 5 phút khi còn unresolved và chỉ đánh dấu version hoàn tất khi report sạch. Không fuzzy-map, không đổi Product Truth, taxonomy hay publish/HOLD. Commit code `42bc71e38cb8a5d5ac979c3ae9e97bbd0e944060` đã PASS cả Validate và Release CI. **Production vẫn CHƯA XÁC MINH deploy/runtime report**, nên chưa tuyên bố frontend PASS.

## P0-02 — Repair phải tự chạy sau deploy

### Fix mới

File: `apps/bizrise-ddg-migrator/bizrise-ddg-migrator.php`

- Migrator version tăng `0.3.3` → `0.3.4`.
- Thêm guarded `init` hook chạy `ProductMediaRepair::run(true)` khi option repair version chưa đạt `1.0.0`.
- Không yêu cầu `current_user_can()` và không phụ thuộc `admin_init`, vì vậy request frontend/smoke đầu tiên sau deploy có thể kích hoạt repair.
- Transient lock 10 phút ngăn hai request chạy repair đồng thời.
- Nếu report còn `errors`, `public_missing_featured`, `product_ambiguous` hoặc `poster_ambiguous`, không đánh dấu complete và backoff 5 phút trước lần thử tiếp theo.
- Nếu sạch, lưu version `1.0.0` để các request sau không chạy lại.
- Report runtime thêm `trigger=runtime_init` và `ran_at` để PO/QA phân biệt repair thật trên WordPress với source-only CI.
- Exception được ghi vào cùng report option, không làm request production fatal; lần retry sau diễn ra sau backoff.

### Vì sao fix này giải quyết audit

Audit trước xác định repair chỉ hook `admin_init` + `manage_options`, nên deploy source không bảo đảm DB ảnh được sửa. Runtime hook mới loại bỏ điều kiện đó và biến source deploy + request WordPress đầu tiên thành đường thực thi tự động. Repair engine gốc vẫn giữ nguyên nguyên tắc chỉ điền Featured Image thiếu/hỏng bằng manifest exact 44 SKU.

Commit:

- `42bc71e38cb8a5d5ac979c3ae9e97bbd0e944060` — `fix(migrator): auto-run product media repair after deploy`

## P0-01 — Product thiếu Featured Image

Repair deterministic trước đó vẫn giữ nguyên:

- `apps/bizrise-ddg-migrator/data/product-media-manifest.csv` — đúng 44 record.
- `apps/bizrise-ddg-migrator/src/ProductMediaRepair.php` — exact source filename; fallback exact brand + product name + pack size; poster exact manifest key/basename; ambiguity thì không gán.
- Chỉ sửa Featured Image thiếu/hỏng, không ghi đè manual image hợp lệ.
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

Local syntax check cho file migrator mới:

- `php -l apps/bizrise-ddg-migrator/bizrise-ddg-migrator.php` — PASS (`No syntax errors detected`).

GitHub Actions cho exact code commit `42bc71e38cb8a5d5ac979c3ae9e97bbd0e944060`:

- **Validate Bizrise DDG V2** — `success`, run `32960579495`.
- **Build Bizrise DDG V2 Release** — `success`, run `32960579572`.

CI xác nhận source/build; không thay thế runtime WordPress DB test.

## File thay đổi trong vòng này

- `apps/bizrise-ddg-migrator/bizrise-ddg-migrator.php`
- `docs/frontend-fix-latest.md`

## Blocked / production evidence còn thiếu

**CHƯA XÁC MINH production deploy.** Chưa có deployed SHA hoặc runtime repair report từ WordPress production trong vòng này. Do đó chưa thể khẳng định card ảnh live đã hết placeholder.

## QA recheck bắt buộc

1. Xác minh production deployed SHA chứa hoặc mới hơn `42bc71e38cb8a5d5ac979c3ae9e97bbd0e944060`.
2. Sau deploy, tạo ít nhất một request WordPress rồi đọc Product Media Repair report: `trigger=runtime_init`, `public_missing_featured=[]`, `errors=[]`, không product/poster ambiguity.
3. `/san-pham/`: không còn placeholder `ĐĂNG DƯƠNG`; ảnh đúng SKU, không crop và giữ 9:16.
4. Audit 100% product public: product → `_thumbnail_id` → attachment filename → manifest expected.
5. Mở toàn bộ category filter; không category rác/legacy.
6. Kiểm ít nhất 8 single product nhiều brand; title/brand/pack/image đúng, HOLD/draft không xuất hiện.
7. Desktop ≥1180px và mobile 360/390/430px: header/menu/card/layout PASS.
8. `/kien-thuc/` + ít nhất 5 article live: không 404, typography/CTA/internal links PASS.

## Deploy

Source đã commit/push lên `codex/rebuild-v2`; exact code commit đã qua cả hai CI gate. **Không gọi là production deployed cho tới khi có log/deploy marker hoặc WordPress production status xác nhận.**
