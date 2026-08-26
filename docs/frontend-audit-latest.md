# DDG frontend QA audit — latest

## Kết luận nhanh

**Trạng thái tổng thể: FAIL / source có fix runtime mới — production vẫn CHƯA XÁC MINH PASS.**

QA recheck branch `codex/rebuild-v2` tại HEAD `afb8c4c7051f6f70ccb9417784bf74f6b2e6ec8a` (`docs(fix): add runtime verification endpoint fix`). So với audit trước, source đã có thêm endpoint read-only `/wp-json/bizrise-ddg/v1/runtime-status` trong migrator V2 để QA/PO đọc deployed SHA và Product Media Repair report.

Frontend production vẫn không truy cập trực tiếp được từ môi trường QA trong vòng này:

- Web fetch `https://dangduonggroup.com/` tiếp tục trả `Cache miss`.
- Direct network request từ runtime QA không resolve được hostname `dangduonggroup.com` (`NameResolutionError`).
- Web search scoped domain không trả result mới để mở các URL con.

Vì vậy vòng này **không browser-test production** và không suy diễn live state.

## Delta vòng này

### Source mới: runtime verification endpoint

Migrator V2 đã có `apps/bizrise-ddg-migrator/src/RuntimeStatus.php` và wiring trong `bizrise-ddg-migrator.php`.

Endpoint dự kiến:

`/wp-json/bizrise-ddg/v1/runtime-status`

Payload cho QA gồm:

- `status`: `not_run`, `repair_incomplete` hoặc `repair_clean`;
- `release.present`, `release.branch`, `release.sha`, `release.deployed_at`, `release.method`;
- `repair_version`;
- `repair.trigger`, `ran_at`, `processed`, `products_found`, `featured_repaired`;
- `public_missing_featured`;
- ambiguity counts và `error_count`.

Source endpoint dùng `Cache-Control: no-store, max-age=0`, public read-only, không nhận arbitrary repo/path/SHA và không expose backup/filesystem path.

### CI exact HEAD

Trong connector QA hiện tại, combined status API không trả status records cho HEAD và commit-workflow lookup không trả push workflow runs. Vì vậy **CI của exact HEAD `afb8c4c...` chưa được QA xác minh trong vòng này**. Không suy diễn CI PASS từ parent hoặc từ fix report.

## Trạng thái theo hạng mục

| Hạng mục | Source | Production |
|---|---|---|
| Product thiếu Featured Image | repair deterministic 44 record tồn tại | CHƯA XÁC MINH runtime/DB |
| Repair tự chạy sau deploy | SOURCE FIXED theo guarded `init` | CHƯA XÁC MINH runtime report |
| Runtime deploy/repair status | SOURCE FIXED, endpoint mới | CHƯA XÁC MINH endpoint live |
| Legacy media override | SOURCE PASS theo fix trước | CHƯA XÁC MINH activation/live |
| Header/logo/version CSS | SOURCE PASS theo fix trước | CHƯA XÁC MINH live/cache |
| Product image 9:16 | SOURCE PASS | CHƯA XÁC MINH live |
| Category filter | SOURCE PASS phần render | CHƯA XÁC MINH taxonomy DB |
| >=8 single product | BLOCKED | CHƯA TEST |
| `/kien-thuc/` + >=5 bài | BLOCKED | CHƯA TEST |
| Mobile 360/390/430 | BLOCKED | CHƯA TEST |

## P0 — blocker production

### P0-01 — Featured Image catalog chưa có bằng chứng runtime sạch

- **URL:** `/san-pham/`, product category archives, single product.
- Screenshot production gần nhất trong dự án từng cho thấy card rơi vào placeholder thay vì poster.
- Source có manifest 44 record và repair deterministic; không fuzzy-map.
- **PASS bắt buộc:** runtime report production có `public_missing_featured=[]`, `error_count=0`, `product_ambiguous_count=0`, `poster_ambiguous_count=0`; mọi product public có `_thumbnail_id` hợp lệ và attachment filename đúng manifest; HOLD/draft không xuất hiện.

### P0-02 — Autonomous repair đã có ở source, chưa xác minh production

Repair guarded `init` không còn phụ thuộc admin mở `wp-admin`, có lock/backoff và chỉ complete khi report sạch.

- **Source:** PASS về logic hiện có.
- **Production:** CHƯA XÁC MINH.
- **PASS:** runtime endpoint trả `status=repair_clean`, `repair.trigger` là runtime/admin/CLI thật, `public_missing_featured=[]`, ambiguity/error count đều 0.

### P0-03 — Runtime verification endpoint chưa được xác minh live

Source đã bổ sung endpoint độc lập trong migrator V2, nhưng môi trường QA không truy cập được production để xác nhận route đã deploy và đang phản ánh SHA live.

- **PASS:** đọc được `https://dangduonggroup.com/wp-json/bizrise-ddg/v1/runtime-status` với `release.present=true`; `release.sha` là SHA chứa fix đã qua CI; `status=repair_clean`.

### P0-04 — Exact HEAD CI chưa được QA xác minh trong vòng này

HEAD hiện tại `afb8c4c7051f6f70ccb9417784bf74f6b2e6ec8a` là commit report sau các code commit runtime endpoint. Connector hiện không trả push workflow runs/status checks cho exact HEAD.

- **PASS:** Validate V2 và Release V2 của exact deployed SHA đều `success` trước khi production được coi là release hợp lệ.

## P1 — browser QA còn blocked

### Header / logo / typography

Cần mở production desktop >=1180px và mobile 360/390/430. PASS khi logo đúng tỉ lệ, nav/CTA đầy đủ, sticky header không che hero, mobile menu đúng breakpoint, không asset version cũ do cache.

### Product portrait 9:16

PASS khi toàn bộ card catalog/category dùng stage 9:16, `object-fit: contain`, centered, không crop/scale sai và không nhảy chiều cao.

### Taxonomy/category

PASS khi 100% product public đúng category deterministic, không category legacy/rác, filter trả đúng SKU.

### >=8 product detail nhiều brand

Chưa test live. Cần kiểm ít nhất 8 SKU đại diện nhiều brand/collection: image, title, brand, pack, CTA, hồ sơ công bố nếu có, related products và không lộ HOLD/draft.

### `/kien-thuc/` + >=5 bài viết

Chưa test live. Cần kiểm HTTP/render, H1, typography, featured image, excerpt/body, internal links, CTA, responsive và không 404.

## Broken link / H1 / CTA

Source review trước chưa có bằng chứng chắc chắn về duplicate H1 hoặc broken link. Đây vẫn là mục bắt buộc live-crawl cho:

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

## Evidence vòng này

- Branch HEAD: `afb8c4c7051f6f70ccb9417784bf74f6b2e6ec8a`.
- Runtime endpoint source: `apps/bizrise-ddg-migrator/src/RuntimeStatus.php`.
- Fix report xác định code commits endpoint: `d8a500fef92145ae9d90e298257771c2a69cd34f`, `aba58fb1eab9f6478555701144235733e86c73a3`.
- Exact HEAD Validate CI: **CHƯA XÁC MINH trong QA connector vòng này**.
- Exact HEAD Release CI: **CHƯA XÁC MINH trong QA connector vòng này**.
- Production deployed SHA: **CHƯA XÁC MINH**.
- Runtime Product Media Repair report: **CHƯA XÁC MINH**.
- Frontend production browser QA: **BLOCKED bởi Cache miss + DNS resolution failure từ môi trường QA**.

## Tiêu chí PASS vòng tiếp theo

1. Xác minh exact deployed SHA và cả Validate + Release CI đều `success` cho SHA đó.
2. Đọc `/wp-json/bizrise-ddg/v1/runtime-status`: `release.present=true`, `status=repair_clean`, `public_missing_featured=[]`, ambiguity/error count = 0.
3. Audit 100% product public: product ID → product key → brand/category → `_thumbnail_id` → attachment filename → expected manifest.
4. `/san-pham/` + mọi category không placeholder/ảnh legacy/crop.
5. Desktop >=1180px header/logo/nav/CTA PASS.
6. Mobile 360/390/430 header/menu/cards PASS.
7. >=8 single product nhiều brand PASS facts/media/HOLD.
8. `/kien-thuc/` + >=5 article live PASS layout/link/CTA.
9. Live crawl các page chính không broken link, duplicate H1 hoặc CTA sai.

## Kết luận QA

Source đã tiến thêm một bước quan trọng: runtime verification endpoint hiện có trong migrator V2, cho phép QA/PO xác minh deploy + media repair khi production truy cập được. Tuy nhiên **production vẫn chưa đủ bằng chứng để PASS** vì môi trường QA hiện không fetch/resolve được `dangduonggroup.com`, và exact HEAD CI cũng chưa xác minh được qua connector vòng này.

Trạng thái giữ: **SOURCE FIXED NHIỀU P0 / PRODUCTION CHƯA XÁC MINH — QA FAIL**.
