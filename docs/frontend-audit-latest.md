# DDG frontend QA audit — latest

## Kết luận nhanh

**Trạng thái tổng thể: FAIL / source đã sửa thêm P0 media-health, production vẫn CHƯA XÁC MINH PASS.**

QA recheck branch `codex/rebuild-v2` tại HEAD `84dfebd5ea5ef8dfe2922675dd7ba68b773c18d9` (`docs(fix): record exact 44 media repair health fix`). So với audit trước, source đã sửa thêm hai lỗi quan trọng có thể tạo false PASS cho Product Media Repair: runtime status trước đó đọc sai schema report, và runtime auto-repair trước đó có thể dừng dù manifest 44 record còn unresolved.

Frontend production vẫn không truy cập trực tiếp được từ môi trường QA trong vòng này:

- Web fetch `https://dangduonggroup.com/` tiếp tục trả `Cache miss`.
- Direct network request tới `/`, `/san-pham/`, `/kien-thuc/`, `/wp-json/bizrise-ddg/v1/runtime-status` và `/wp-json/bizrise-deploy/v1/status` đều không resolve được hostname `dangduonggroup.com` (`NameResolutionError`).
- Domain-scoped web search không trả result production mới để mở URL con.

Vì vậy vòng này **không browser-test production** và không suy diễn live state.

## Delta vòng này

### P0 source fix: runtime endpoint phản ánh đúng report 44 record

`apps/bizrise-ddg-migrator/src/RuntimeStatus.php` hiện map đúng schema thực tế của `ProductMediaRepair::run()`:

- `manifest_total`
- `matched_products`
- `already_valid`
- `repaired` → API field `featured_repaired`
- `public_products`
- `public_missing_featured`
- count cho `product_not_found`, `product_ambiguous`, `poster_missing`, `poster_ambiguous`, `errors`

`status=repair_clean` chỉ được trả khi:

- `manifest_total=44`
- `matched_products=44`
- `product_not_found=[]`
- `product_ambiguous=[]`
- `poster_missing=[]`
- `poster_ambiguous=[]`
- `public_missing_featured=[]`
- `errors=[]`

Đây là cải thiện thực sự so với audit trước vì endpoint không còn có thể báo sạch chỉ do đọc nhầm key report.

### P0 source fix: runtime repair không còn false-complete

`apps/bizrise-ddg-migrator/bizrise-ddg-migrator.php` hiện ở version `0.3.6` và có guarded `init` repair độc lập với việc admin mở wp-admin.

Runtime chỉ return sớm khi option repair version đúng **và** saved report exact-clean đủ 44 record. Nếu report cũ có `product_not_found`, `poster_missing`, ambiguity, error hoặc thiếu Featured Image public thì runtime tiếp tục repair; khi chưa sạch sẽ xóa completion option và retry sau 5 phút.

Điểm này đóng blocker cũ “repair chỉ chạy khi admin mở wp-admin”.

### ProductMediaRepair manifest guard

`ProductMediaRepair::load_manifest()` bắt buộc đúng **44 record**, nếu khác sẽ throw lỗi. Matching vẫn deterministic: exact source filename hoặc exact identity (brand + product name + pack size); không thấy fuzzy-map mới trong source được kiểm tra.

### CI exact HEAD

GitHub combined status cho exact HEAD `84dfebd5...` hiện trả danh sách status rỗng trong connector QA. Vì vậy **Validate/Release CI của exact HEAD chưa được QA xác minh trong vòng này**. Không suy diễn PASS từ fix report hoặc parent commit.

## Trạng thái theo hạng mục

| Hạng mục | Source | Production |
|---|---|---|
| Product thiếu Featured Image | deterministic repair 44 record + exact-clean gate | CHƯA XÁC MINH runtime/DB |
| Repair tự chạy sau deploy | PASS theo guarded `init` | CHƯA XÁC MINH runtime report |
| Runtime deploy/repair status | PASS theo schema report hiện tại | CHƯA XÁC MINH endpoint live |
| Legacy media override | PASS theo fix trước | CHƯA XÁC MINH activation/live |
| Header/logo/version CSS | PASS theo fix trước | CHƯA XÁC MINH live/cache |
| Product image 9:16 | PASS ở source theme | CHƯA XÁC MINH live |
| Category filter | PASS phần render source | CHƯA XÁC MINH taxonomy DB |
| >=8 single product | BLOCKED production | CHƯA TEST |
| `/kien-thuc/` + >=5 bài | BLOCKED production | CHƯA TEST |
| Mobile 360/390/430 | BLOCKED production | CHƯA TEST |
| Broken links / duplicate H1 / CTA | chưa có regression source mới | CHƯA LIVE-CRAWL |

## P0 — blocker production

### P0-01 — Featured Image catalog chưa có bằng chứng runtime sạch

- **URL:** `/san-pham/`, product category archives, single product.
- Screenshot production gần nhất trong dự án từng cho thấy card rơi vào placeholder thay vì poster.
- Source hiện có manifest 44 record và repair deterministic; exact-clean gate đã chặt hơn.
- **PASS bắt buộc:** runtime production có `status=repair_clean`, `manifest_total=44`, `matched_products=44`, `product_not_found_count=0`, `product_ambiguous_count=0`, `poster_missing_count=0`, `poster_ambiguous_count=0`, `public_missing_featured=[]`, `error_count=0`.

### P0-02 — Autonomous repair đã fixed ở source, chưa xác minh production

- **Source:** PASS về guarded runtime retry logic.
- **Production:** CHƯA XÁC MINH.
- **PASS:** runtime endpoint live trả report exact-clean; `repair.trigger` phản ánh lần chạy thật (`runtime_init`/admin/CLI), không phải report cũ không đầy đủ.

### P0-03 — Runtime/deploy status endpoints chưa xác minh live

Cần đọc được ít nhất:

- `/wp-json/bizrise-ddg/v1/runtime-status`
- nếu Deploy Bridge đã cài: `/wp-json/bizrise-deploy/v1/status`

**PASS:** production cho biết exact deployed SHA, thời điểm deploy, release marker hiện diện; runtime media repair exact-clean.

### P0-04 — Exact deployed SHA và CI chưa xác minh

HEAD source hiện tại là `84dfebd5ea5ef8dfe2922675dd7ba68b773c18d9`; commit code ngay trước report là `44cb2046d72ec85a5ecc155f7c5b3c02b124b136`.

Connector QA hiện không có status check records cho exact HEAD. Production cũng chưa truy cập được để đọc deployed SHA.

**PASS:** exact SHA đang chạy production phải có Validate V2 + Release V2 `success` trước khi coi release hợp lệ.

## P1 — browser QA còn blocked

### Header / logo / typography

Cần mở production desktop >=1180px và mobile 360/390/430. PASS khi logo đúng tỉ lệ, nav/CTA đầy đủ, sticky header không che hero, mobile menu đúng breakpoint, không asset version cũ do cache.

### Product portrait 9:16

PASS khi toàn bộ card catalog/category dùng stage 9:16, `object-fit: contain`, centered, không crop/scale sai, không nhảy chiều cao và không placeholder `ĐĂNG DƯƠNG`.

### Taxonomy/category

PASS khi 100% product public đúng category deterministic, không category legacy/rác, filter trả đúng SKU.

### >=8 product detail nhiều brand

Chưa test live. Cần kiểm ít nhất 8 SKU đại diện nhiều brand/collection: image, title, brand, pack, CTA, hồ sơ công bố nếu có, related products và không lộ HOLD/draft.

### `/kien-thuc/` + >=5 bài viết

Chưa test live. Cần kiểm HTTP/render, H1, typography, featured image, excerpt/body, internal links, CTA, responsive và không 404.

## Broken link / H1 / CTA

Live-crawl vẫn bắt buộc cho:

- `/`
- `/ve-dang-duong/`
- `/nang-luc/`
- `/thuong-hieu/`
- `/san-pham/`
- `/kien-thuc/`
- `/doi-tac/`
- `/lien-he/`
- `/tim-diem-ban/`
- `/nghien-cuu-phat-trien/`
- `/oem-odm-my-pham/`

Không có bằng chứng source mới đủ để kết luận broken link hoặc duplicate H1, nên không tự gắn PASS/FAIL cho các mục này khi chưa mở live.

## Evidence vòng này

- Branch HEAD: `84dfebd5ea5ef8dfe2922675dd7ba68b773c18d9`.
- Fix report HEAD: `docs/frontend-fix-latest.md` ghi code commits `23873213d600c7a29304b36aab8bdb206e7dc366` và `44cb2046d72ec85a5ecc155f7c5b3c02b124b136`.
- Runtime endpoint source: `apps/bizrise-ddg-migrator/src/RuntimeStatus.php`.
- Runtime retry source: `apps/bizrise-ddg-migrator/bizrise-ddg-migrator.php` version `0.3.6`.
- Product media engine: `apps/bizrise-ddg-migrator/src/ProductMediaRepair.php`; manifest guard = exactly 44 records.
- Exact HEAD combined status records: **empty / CI CHƯA XÁC MINH trong QA connector vòng này**.
- Production deployed SHA: **CHƯA XÁC MINH**.
- Runtime Product Media Repair report: **CHƯA XÁC MINH**.
- Frontend production browser QA: **BLOCKED bởi Cache miss + DNS resolution failure từ môi trường QA**.

## Tiêu chí PASS vòng tiếp theo

1. Xác minh exact deployed SHA và cả Validate + Release CI đều `success` cho SHA đó.
2. Đọc `/wp-json/bizrise-ddg/v1/runtime-status`: `release.present=true`, `status=repair_clean`, `manifest_total=44`, `matched_products=44`, missing/ambiguity/error count = 0.
3. Nếu Deploy Bridge live, đối chiếu `/wp-json/bizrise-deploy/v1/status`: `deployed_sha` phải khớp release/runtime SHA.
4. Audit 100% product public: product ID → product key → brand/category → `_thumbnail_id` → attachment filename → expected manifest.
5. `/san-pham/` + mọi category không placeholder/ảnh legacy/crop.
6. Desktop >=1180px header/logo/nav/CTA PASS.
7. Mobile 360/390/430 header/menu/cards PASS.
8. >=8 single product nhiều brand PASS facts/media/HOLD.
9. `/kien-thuc/` + >=5 article live PASS layout/link/CTA.
10. Live crawl các page chính không broken link, duplicate H1 hoặc CTA sai.

## Kết luận QA

Source đã tiến thêm một bước quan trọng: runtime verification và runtime media-repair hiện dùng cùng exact-clean gate 44 record, loại bỏ hai khả năng false PASS đã phát hiện ở vòng trước. Đây là delta có ý nghĩa ở code.

Tuy nhiên **production vẫn chưa đủ bằng chứng để PASS** vì môi trường QA hiện không fetch/resolve được `dangduonggroup.com`, exact deployed SHA chưa đọc được và exact HEAD CI cũng chưa xác minh được qua connector trong vòng này.

Trạng thái giữ: **SOURCE FIXED THÊM P0 / PRODUCTION CHƯA XÁC MINH — QA FAIL**.
