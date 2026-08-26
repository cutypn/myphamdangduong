# DDG frontend QA audit — latest

## Kết luận nhanh

**Trạng thái tổng thể: FAIL / source đã đóng thêm lỗi P0 Featured Image integrity; production vẫn CHƯA XÁC MINH PASS.**

QA recheck branch `codex/rebuild-v2` tại HEAD `884e7062c8ef3aed9b52234e2c0e0c4e915e61f3` (`docs(fix): record exact Featured Image integrity repair`). Exact HEAD hiện đã được xác minh có cả **Validate Bizrise DDG V2 = SUCCESS** và **Build Bizrise DDG V2 Release = SUCCESS**.

Frontend production vẫn không fetch trực tiếp được từ môi trường QA trong vòng này: mở `https://dangduonggroup.com/` trả `Cache miss`; do đó QA không suy diễn trạng thái live và chưa browser-test các URL production.

## Delta vòng này

### P0 source fix — Featured Image phải đúng exact poster, không chỉ “có ảnh”

Fix report mới xác nhận `ProductMediaRepair` trước đây có false-clean quan trọng: nếu `_thumbnail_id` trỏ tới bất kỳ image attachment hợp lệ nào thì engine coi `already_valid`, kể cả ảnh legacy/sai SKU.

Source mới đã sửa:

- resolve product và expected poster trước khi đánh giá thumbnail;
- `already_valid` chỉ khi `_thumbnail_id === expected poster attachment ID`;
- nếu thumbnail hiện tại khác expected poster thì repair thay bằng exact poster manifest;
- sau `set_post_thumbnail()` kiểm lại ID thực tế;
- post-audit thêm `public_wrong_featured`;
- source filename chỉ match trên các source-meta key deterministic;
- brand evidence chỉ dùng known brand meta/taxonomies, không quét category/tag;
- admin/runtime/status cùng dùng một exact-clean gate qua `ProductMediaRepair::is_clean_report()`.

Đây là fix trực tiếp cho triệu chứng người dùng đã báo: nhiều product có ảnh nhưng ảnh cũ/xấu/sai vẫn sống qua repair.

### Exact HEAD CI — PASS

HEAD: `884e7062c8ef3aed9b52234e2c0e0c4e915e61f3`.

GitHub Actions xác minh trong vòng QA này:

- `Build Bizrise DDG V2 Release` run `33002839883`: **completed / success** cho exact HEAD.
- `Validate Bizrise DDG V2` run `33002839887`: **completed / success** cho exact HEAD.

Vì vậy CI gate source hiện PASS cho đúng SHA đang ở đầu branch.

## Trạng thái theo hạng mục

| Hạng mục | Source | Production |
|---|---|---|
| Product thiếu Featured Image | deterministic repair 44 record | CHƯA XÁC MINH runtime/DB |
| Product có ảnh nhưng sai poster/SKU | exact expected attachment enforcement đã fix | CHƯA XÁC MINH runtime/DB |
| Repair tự chạy sau deploy | guarded runtime retry | CHƯA XÁC MINH runtime report |
| Runtime media integrity | expose missing + wrong featured | CHƯA XÁC MINH endpoint live |
| Exact HEAD CI | PASS Validate + Release | n/a |
| Exact deployed SHA | n/a | CHƯA XÁC MINH |
| Header/logo/version CSS | source fix trước vẫn hiện diện theo fix history | CHƯA XÁC MINH live/cache |
| Product image 9:16 | source theme đã có rule | CHƯA XÁC MINH live |
| Category filter | source render đã fix | CHƯA XÁC MINH taxonomy DB |
| >=8 single product | BLOCKED production | CHƯA TEST |
| `/kien-thuc/` + >=5 bài | BLOCKED production | CHƯA TEST |
| Mobile 360/390/430 | BLOCKED production | CHƯA TEST |
| Broken links / duplicate H1 / CTA | chưa có live crawl | CHƯA TEST |

## P0 — blocker production

### P0-01 — Exact poster integrity chưa có bằng chứng runtime sạch

- **URL:** `/san-pham/`, product category archives, single product.
- **Bằng chứng:** screenshot production trước đây cho thấy card thiếu ảnh; fix report mới còn xác nhận source cũ có thể giữ ảnh legacy/sai SKU dù report sạch.
- **Nguyên nhân source đã sửa:** engine cũ chỉ kiểm attachment hiện tại có phải image hay không, không so với expected poster manifest.
- **PASS bắt buộc:** runtime production báo `status=repair_clean`, `manifest_total=44`, `matched_products=44`, `wrong_featured_count=0`, `public_missing_featured=[]`, `public_wrong_featured=[]`, missing/ambiguity/error count = 0.

### P0-02 — Production deployed SHA chưa xác minh

Source exact HEAD đã PASS cả hai CI workflow, nhưng production endpoint chưa fetch được trong môi trường QA.

**PASS:** `/wp-json/bizrise-ddg/v1/runtime-status` và/hoặc Deploy Bridge status phải cho deployed/release SHA khớp một SHA đã PASS Validate + Release; ưu tiên exact current HEAD nếu auto-deploy hoạt động.

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

- Branch HEAD: `884e7062c8ef3aed9b52234e2c0e0c4e915e61f3`.
- Fix report code commits: `17f2ed5c3427ec87c022c5c130dd769be13d6e33`, `8a496abcb1b2ce1b6a09884f4f4fd4c3cc634583`, `76f8c55d64c24e6b960010c34ecc1a57011f1260`, `77a2ac00a193f96b26882a1eee7ae92795bfcb8b`.
- Validate exact HEAD: run `33002839887` = SUCCESS.
- Release exact HEAD: run `33002839883` = SUCCESS.
- Production deployed SHA: **CHƯA XÁC MINH**.
- Runtime Product Media Repair report: **CHƯA XÁC MINH**.
- Frontend browser QA: **BLOCKED trong vòng này bởi production fetch `Cache miss`**.

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

Vòng này có delta thực sự: source đã sửa đúng một nguyên nhân P0 khiến **ảnh sai vẫn được coi là hợp lệ**, và exact HEAD hiện đã được xác minh PASS cả Validate lẫn Release CI. Tuy nhiên production vẫn chưa có bằng chứng runtime/browser để PASS.

**Trạng thái giữ: SOURCE CI PASS + P0 MEDIA INTEGRITY FIXED / PRODUCTION CHƯA XÁC MINH — QA FAIL.**
