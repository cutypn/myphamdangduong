# DDG frontend QA audit — latest

## Kết luận nhanh

**Trạng thái tổng thể: FAIL / source code không có regression mới; production vẫn CHƯA XÁC MINH PASS.**

QA recheck branch `codex/rebuild-v2` tại HEAD `37df95e4caa5e160889a48dd4584441635ae7b61` (`docs(qa): verify exact HEAD CI and media integrity fix`). Commit HEAD hiện tại chỉ là cập nhật tài liệu QA; parent code SHA là `884e7062c8ef3aed9b52234e2c0e0c4e915e61f3`.

Frontend production tiếp tục không fetch trực tiếp được từ môi trường QA trong vòng này:

- `https://dangduonggroup.com/` trả `Cache miss`.
- Các URL con `/san-pham/`, `/kien-thuc/` và runtime/deploy status không thể mở trực tiếp trong web fetch vì root chưa fetch thành công.
- Domain-scoped search không trả kết quả production mới để dùng làm đường mở an toàn.

Vì vậy vòng này **không browser-test production** và không suy diễn live state.

## Delta vòng này

### Không có code delta sau P0 media-integrity fix

HEAD hiện tại là commit docs QA. Không có commit code mới sau parent `884e7062...`, nên source behavior vẫn giữ các fix đã xác minh ở vòng trước:

- Featured Image chỉ được coi hợp lệ khi `_thumbnail_id` đúng exact expected poster attachment;
- repair thay thumbnail sai SKU/legacy bằng exact poster manifest;
- runtime report expose `public_missing_featured` và `public_wrong_featured`;
- exact-clean gate yêu cầu manifest/matched đủ 44/44, không missing/ambiguity/error và không sai Featured Image public;
- matching product/poster vẫn deterministic, không fuzzy-map.

### CI

Parent code SHA `884e7062c8ef3aed9b52234e2c0e0c4e915e61f3` đã được xác minh ở vòng trước có cả Validate V2 và Release V2 = SUCCESS.

Với docs-only HEAD `37df95e4...`, GitHub combined status connector hiện trả `statuses=[]`, nên **CI exact docs-only HEAD chưa được xác minh trong vòng này**. Không suy diễn PASS từ parent.

## Trạng thái theo hạng mục

| Hạng mục | Source | Production |
|---|---|---|
| Product thiếu Featured Image | deterministic repair 44 record | CHƯA XÁC MINH runtime/DB |
| Product có ảnh nhưng sai poster/SKU | exact expected attachment enforcement đã fix | CHƯA XÁC MINH runtime/DB |
| Repair tự chạy sau deploy | guarded runtime retry | CHƯA XÁC MINH runtime report |
| Runtime media integrity | expose missing + wrong featured | CHƯA XÁC MINH endpoint live |
| Parent code SHA CI | PASS Validate + Release | n/a |
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

- Branch HEAD: `37df95e4caa5e160889a48dd4584441635ae7b61`.
- Parent code SHA: `884e7062c8ef3aed9b52234e2c0e0c4e915e61f3`.
- Parent code SHA CI: Validate + Release = SUCCESS theo audit vòng trước.
- Exact docs-only HEAD combined status connector: `statuses=[]` → CHƯA XÁC MINH.
- Production root fetch: `Cache miss`.
- Domain-scoped search production: không có result mới.
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

Vòng này không có code regression mới để báo. Source vẫn giữ P0 media-integrity fix của parent code SHA đã PASS CI, nhưng production tiếp tục chưa có bằng chứng runtime/browser để PASS.

**Trạng thái giữ: SOURCE CODE STABLE / PRODUCTION CHƯA XÁC MINH — QA FAIL.**
