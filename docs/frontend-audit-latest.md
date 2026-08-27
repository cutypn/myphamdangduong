# DDG frontend QA audit — latest

## Kết luận nhanh

**Trạng thái tổng thể: FAIL / source code không có regression mới; production vẫn CHƯA XÁC MINH PASS.**

QA recheck branch `codex/rebuild-v2` tại HEAD `f5398c055b127d4a74da5c33c9a33aa82c0efade` (`docs(qa): recheck production reachability and current head`). HEAD hiện tại vẫn là commit tài liệu QA; không có code delta mới sau P0 media-integrity fix.

Frontend production tiếp tục không fetch trực tiếp được từ môi trường QA trong vòng này:

- `https://dangduonggroup.com/` trả `Cache miss`.
- `/san-pham/` và `/kien-thuc/` không thể mở trực tiếp qua web fetch vì root chưa fetch thành công.
- Không có bằng chứng mới cho runtime/deploy status từ production trong vòng này.

Vì vậy vòng này **không browser-test production** và không suy diễn live state.

## Delta vòng này

### Không có code delta mới

Branch HEAD vẫn là docs-only QA commit. Source behavior vẫn giữ các fix đã xác minh trước đó:

- Featured Image chỉ được coi hợp lệ khi `_thumbnail_id` đúng exact expected poster attachment;
- repair thay thumbnail sai SKU/legacy bằng exact poster manifest;
- runtime report expose `public_missing_featured` và `public_wrong_featured`;
- exact-clean gate yêu cầu manifest/matched đủ 44/44, không missing/ambiguity/error và không sai Featured Image public;
- matching product/poster deterministic, không fuzzy-map.

### CI exact HEAD

GitHub combined-status connector cho exact HEAD `f5398c055...` tiếp tục trả `statuses=[]`, nên **CI exact docs-only HEAD CHƯA XÁC MINH** trong vòng này. Không suy diễn PASS từ parent code SHA.

## Trạng thái theo hạng mục

| Hạng mục | Source | Production |
|---|---|---|
| Product thiếu Featured Image | deterministic repair 44 record | CHƯA XÁC MINH runtime/DB |
| Product có ảnh nhưng sai poster/SKU | exact expected attachment enforcement đã fix | CHƯA XÁC MINH runtime/DB |
| Repair tự chạy sau deploy | guarded runtime retry | CHƯA XÁC MINH runtime report |
| Runtime media integrity | expose missing + wrong featured | CHƯA XÁC MINH endpoint live |
| Exact docs-only HEAD CI | CHƯA XÁC MINH | n/a |
| Exact deployed SHA | n/a | CHƯA XÁC MINH |
| Header/logo/version CSS | source fix trước vẫn hiện diện | CHƯA XÁC MINH live/cache |
| Product image 9:16 | source theme đã có rule | CHƯA XÁC MINH live |
| Category filter | source render đã fix | CHƯA XÁC MINH taxonomy DB |
| >=8 single product | BLOCKED production | CHƯA TEST |
| `/kien-thuc/` + >=5 bài | BLOCKED production | CHƯA TEST |
| Mobile 360/390/430 | BLOCKED production | CHƯA TEST |
| Broken links / duplicate H1 / CTA | chưa có live crawl | CHƯA TEST |

## P0 — blocker production

### P0-01 — Exact poster integrity chưa có bằng chứng runtime sạch

- **URL:** `/san-pham/`, product category archives, single product.
- **Bằng chứng lịch sử:** screenshot production trước đây cho thấy card thiếu ảnh; source cũ từng có thể giữ ảnh legacy/sai SKU dù report sạch.
- **Source hiện tại:** đã sửa exact poster enforcement.
- **PASS bắt buộc:** runtime production báo `status=repair_clean`, `manifest_total=44`, `matched_products=44`, `wrong_featured_count=0`, `public_missing_featured=[]`, `public_wrong_featured=[]`, missing/ambiguity/error count = 0.

### P0-02 — Production deployed SHA chưa xác minh

Production endpoint chưa fetch được trong môi trường QA.

**PASS:** `/wp-json/bizrise-ddg/v1/runtime-status` và/hoặc `/wp-json/bizrise-deploy/v1/status` phải cho deployed/release SHA khớp SHA đã PASS Validate + Release.

### P0-03 — Runtime/deploy status endpoints chưa xác minh live

Cần đọc được:

- `/wp-json/bizrise-ddg/v1/runtime-status`
- nếu Deploy Bridge đã cài: `/wp-json/bizrise-deploy/v1/status`

**PASS:** release marker hiện diện, deployed SHA rõ ràng, media repair exact-clean 44/44.

## P1 — browser QA còn blocked

### Header / logo / typography

Cần mở production desktop >=1180px và mobile 360/390/430. PASS khi logo đúng tỉ lệ, nav/CTA đầy đủ, sticky header không che hero, mobile menu đúng breakpoint và không asset version cũ do cache.

### Product portrait 9:16

PASS khi toàn bộ card catalog/category dùng stage 9:16, `object-fit: contain`, centered, không crop/scale sai, không placeholder `ĐĂNG DƯƠNG`, không ảnh legacy/sai SKU.

### Taxonomy/category

PASS khi 100% product public đúng category deterministic, không category legacy/rác, filter trả đúng product.

### >=8 product detail nhiều brand

Chưa test live. Cần kiểm ít nhất 8 SKU đại diện nhiều brand/collection: image, title, brand, pack, CTA, hồ sơ công bố nếu có, related products và không lộ HOLD/draft.

### `/kien-thuc/` + >=5 bài viết

Chưa test live. Cần kiểm HTTP/render, H1, typography, featured image, excerpt/body, internal links, CTA, responsive và không 404.

### Broken link / duplicate H1 / CTA

Live-crawl vẫn bắt buộc cho `/`, `/ve-dang-duong/`, `/nang-luc/`, `/thuong-hieu/`, `/san-pham/`, `/kien-thuc/`, `/doi-tac/`, `/lien-he/`, `/tim-diem-ban/`, `/nghien-cuu-phat-trien/`, `/oem-odm-my-pham/`.

## Evidence vòng này

- Branch HEAD: `f5398c055b127d4a74da5c33c9a33aa82c0efade`.
- Exact HEAD commit type: docs-only QA recheck.
- Exact HEAD combined status connector: `statuses=[]` → CHƯA XÁC MINH.
- Production root fetch: `Cache miss`.
- Production deployed SHA: **CHƯA XÁC MINH**.
- Runtime Product Media Repair report: **CHƯA XÁC MINH**.
- Frontend browser QA: **BLOCKED trong vòng này bởi production fetch**.

## Tiêu chí PASS vòng tiếp theo

1. Đọc production deployed/release SHA và đối chiếu CI success.
2. Runtime status: `repair_clean`, manifest/matched `44/44`, missing/ambiguity/error = 0, `wrong_featured_count=0`, `public_missing_featured=[]`, `public_wrong_featured=[]`.
3. Audit 100% product public: product key → brand/category → `_thumbnail_id` → exact expected attachment filename.
4. `/san-pham/` + mọi category không placeholder/ảnh legacy/sai SKU/crop.
5. Desktop >=1180px header/logo/nav/CTA PASS.
6. Mobile 360/390/430 header/menu/cards PASS.
7. >=8 single product nhiều brand PASS facts/media/HOLD.
8. `/kien-thuc/` + >=5 article live PASS layout/link/CTA.
9. Live crawl page chính không broken link, duplicate H1 hoặc CTA sai.

## Kết luận QA

Không có code regression mới để báo trong vòng này. Source vẫn giữ P0 media-integrity fix, nhưng production tiếp tục chưa có bằng chứng runtime/browser để PASS.

**Trạng thái giữ: SOURCE CODE STABLE / PRODUCTION CHƯA XÁC MINH — QA FAIL.**
