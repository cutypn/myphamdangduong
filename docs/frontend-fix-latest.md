# DDG frontend fix report — latest

## Kết luận

Đã xử lý tiếp blocker P0-01 ở **source** bằng một repair deterministic cho 44 Featured Image sản phẩm. Repair mới không fuzzy-map, không đổi Product Truth, không đổi taxonomy và không đổi trạng thái publish/HOLD. GitHub CI đã PASS cho source ở commit `cd157397f2e9c580a956e2f775a9ac1a376a89ef`; release workflow cũng build thành công. **Production chưa được tuyên bố PASS** vì chưa có log deploy và chưa có DB/frontend recheck sau deploy.

## P0-01 — Product production thiếu Featured Image

### Fix mới

Thêm manifest canonical 44 sản phẩm:

- `apps/bizrise-ddg-migrator/data/product-media-manifest.csv`
- 44 record, mỗi record có `source_filename`, brand, product name, pack size, exact portrait `poster_filename`, status.
- HOLD record vẫn chỉ là dữ liệu nhận diện; repair không thay post status hay Product Truth.

Thêm repair engine:

- `apps/bizrise-ddg-migrator/src/ProductMediaRepair.php`
- được wire vào `apps/bizrise-ddg-migrator/bizrise-ddg-migrator.php`.
- tự chạy một lần ở admin khi source mới được deploy, và có thể re-run tại **Tools → DDG Product Media Repair** hoặc WP-CLI `wp bizrise-ddg repair-product-media --apply`.

### Matching rules

Repair chỉ nhận product khi có bằng chứng deterministic:

1. ưu tiên exact `source_filename` đã lưu trong post meta;
2. nếu không có, dùng exact `brand + product_name + pack_size`;
3. nếu có hơn một candidate thì đánh dấu ambiguity và **không gán ảnh**;
4. poster được tìm bằng exact `_ddg_catalog_repair_poster_key`, hoặc exact basename `_wp_attached_file`;
5. nếu poster trùng/ambiguous thì **không gán**;
6. chỉ điền khi Featured Image hiện tại đang thiếu/hỏng, không ghi đè manual image hợp lệ.

Report sau chạy sẽ chứa `matched_products`, `already_valid`, `repaired`, `product_not_found`, `product_ambiguous`, `poster_missing`, `poster_ambiguous`, `public_products`, `public_missing_featured`, `errors`.

PASS production bắt buộc: `public_missing_featured = []`, không ambiguity/error và `/san-pham/` không còn placeholder `ĐĂNG DƯƠNG`.

Commits:

- `c50fc57200cb5071dd7b77c8423987b13967baf8` — add deterministic 44-product poster manifest
- `73300d568d3f9e6c2ab8b112c44de0a756973f1f` — add exact product Featured Image repair
- `cd157397f2e9c580a956e2f775a9ac1a376a89ef` — wire repair into V2 migrator

## Các source fix trước vẫn giữ nguyên

- Legacy media hotfix đã diagnostic-only; không fetch/sideload/ghi Featured Image.
- Theme/version asset đã đồng bộ 2.1.3.
- Product card canonical 9:16 + `object-fit: contain`.
- Category filter không còn whitelist 8 slug tĩnh.
- Không đổi Product Truth hoặc regulatory hold logic.
- Không tạo plugin UI override.

## Test đã chạy

GitHub Actions trên commit `cd157397f2e9c580a956e2f775a9ac1a376a89ef`:

- **Validate Bizrise DDG V2** — `success`, run `32950340987`.
- **Build Bizrise DDG V2 Release** — `success`, run `32950341046`.

Validation pipeline bao gồm lint/check source theo workflow V2; release package được build thành công. Runtime Fix Agent không có WordPress production DB để thực thi repair report thật, nên số `repaired/public_missing_featured` production vẫn phải lấy sau deploy.

## File đã thay đổi trong vòng này

- `apps/bizrise-ddg-migrator/data/product-media-manifest.csv`
- `apps/bizrise-ddg-migrator/src/ProductMediaRepair.php`
- `apps/bizrise-ddg-migrator/bizrise-ddg-migrator.php`
- `docs/frontend-fix-latest.md`

## QA recheck bắt buộc sau deploy

1. Lấy report **DDG Product Media Repair**; yêu cầu `public_missing_featured = []`, `errors = []`, không ambiguity.
2. `/san-pham/`: không còn placeholder `ĐĂNG DƯƠNG`; ảnh đúng SKU và nằm gọn 9:16.
3. Mở toàn bộ category filter; không category rác/legacy.
4. Kiểm ít nhất 8 single product thuộc nhiều brand; title/brand/pack/image đúng mapping.
5. HOLD/draft không xuất hiện frontend.
6. Desktop ≥1180px và mobile 360/390/430px recheck header/card/layout.
7. Purge cache/CDN sau deploy trước khi kết luận live.

## Deploy

**CHƯA XÁC MINH production deploy.** Source đã push vào `codex/rebuild-v2` và release build PASS; không được gọi là deployed cho tới khi có log cPanel/deploy marker hoặc production artifact/HEAD xác nhận.
