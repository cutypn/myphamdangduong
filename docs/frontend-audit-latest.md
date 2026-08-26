# DDG frontend QA audit — latest

## Kết luận nhanh

**Trạng thái tổng thể: FAIL / SOURCE CI PASS — production vẫn chưa được xác nhận PASS.**

QA recheck branch `codex/rebuild-v2` tại HEAD `0b3998ccd080102baabf47e83671a6d95228ba67` (`docs(qa): recheck autonomous media repair and current CI`). Cả hai workflow của đúng HEAD này đều đã hoàn tất `success`:

- **Validate Bizrise DDG V2** — run `32969172034`.
- **Build Bizrise DDG V2 Release** — run `32969172088`.

Frontend production vẫn không fetch trực tiếp được từ môi trường QA: request tới `https://dangduonggroup.com/` tiếp tục trả `Cache miss`; web search cũng không trả index result mới để mở các URL con an toàn. Vì vậy vòng này không thể tự browser-test production và không suy đoán trạng thái live.

## Delta vòng này

Không có bằng chứng production mới cho phép đóng các lỗi P0/P1. Source HEAD mới nhất chỉ là commit audit; code fix nền tảng vẫn là parent `800b494ef9758201b394e73cd51bbc5fc512d183`. CI cho HEAD audit mới nhất vẫn PASS cả Validate và Release.

| Hạng mục | Source | Production |
|---|---|---|
| Product thiếu Featured Image | repair deterministic tồn tại | CHƯA XÁC MINH runtime/DB |
| Repair tự chạy sau deploy | SOURCE FIXED | CHƯA XÁC MINH runtime report |
| Legacy media override | SOURCE PASS | CHƯA XÁC MINH activation/live |
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
- **PASS bắt buộc:** runtime report production có `public_missing_featured=[]`, `errors=[]`, không ambiguity; mọi product public có `_thumbnail_id` hợp lệ và attachment filename đúng manifest; HOLD/draft không xuất hiện.

### P0-02 — Autonomous repair đã có ở source, chưa xác minh production

Repair đã được chuyển sang guarded runtime `init`, có lock/backoff và chỉ đánh dấu hoàn tất khi report sạch.

- **Source:** PASS.
- **Production:** CHƯA XÁC MINH.
- **PASS:** report production phải có trigger runtime sau deployed SHA chứa fix, `public_missing_featured=[]`, `errors=[]`, không ambiguity.

### P0-03 — Production deploy/runtime state chưa đọc được

Deploy Bridge/status endpoint không truy cập được từ môi trường QA hiện tại, nên chưa thể chứng minh production đang chạy SHA nào.

- **PASS:** đọc được deployed SHA/log deploy hoặc bằng chứng tương đương từ production; SHA phải chứa fix đã CI PASS.

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

Source review trước chưa có bằng chứng chắc chắn về duplicate H1 hoặc broken link. Đây vẫn là mục bắt buộc live-crawl cho `/`, `/ve-dang-duong/`, `/nang-luc/`, `/thuong-hieu/`, `/san-pham/`, `/kien-thuc/`, `/doi-tac/`, `/lien-he/`, `/tim-diem-ban/`, `/nghien-cuu-phat-trien/`, `/oem-odm-my-pham/`.

## CI / deploy evidence

- Branch HEAD: `0b3998ccd080102baabf47e83671a6d95228ba67`.
- Validate: **SUCCESS**, run `32969172034`.
- Release: **SUCCESS**, run `32969172088`.
- Production deployed SHA: **CHƯA XÁC MINH**.
- Runtime Product Media Repair report: **CHƯA XÁC MINH**.
- Frontend production browser QA: **BLOCKED bởi Cache miss từ môi trường QA**.

## Tiêu chí PASS vòng tiếp theo

1. Xác minh production deployed SHA chứa fix đã CI PASS.
2. Xác minh runtime Product Media Repair report sạch.
3. Audit 100% product public: product ID → product key → brand/category → `_thumbnail_id` → attachment filename → expected manifest.
4. `/san-pham/` + mọi category không placeholder/ảnh legacy/crop.
5. Desktop >=1180px header/logo/nav/CTA PASS.
6. Mobile 360/390/430 header/menu/cards PASS.
7. >=8 single product nhiều brand PASS facts/media/HOLD.
8. `/kien-thuc/` + >=5 article live PASS layout/link/CTA.
9. Live crawl các page chính không broken link, duplicate H1 hoặc CTA sai.

## Kết luận QA

Không có regression source mới được phát hiện trong vòng này; HEAD hiện tại tiếp tục qua cả hai CI gate. Tuy nhiên **production vẫn chưa đủ bằng chứng để PASS** vì môi trường QA không truy cập được frontend/status runtime. Trạng thái giữ nguyên: **SOURCE CI PASS / PRODUCTION CHƯA XÁC MINH — QA FAIL**.
