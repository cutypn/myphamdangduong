# DDG frontend QA audit — latest

## Kết luận nhanh

**Trạng thái tổng thể: FAIL / SOURCE PASSING CI — production chưa được xác nhận PASS.**

QA recheck branch `codex/rebuild-v2` tại HEAD `800b494ef9758201b394e73cd51bbc5fc512d183` (`docs(fix): record autonomous post-deploy media repair`). Hai workflow của đúng HEAD này đều đã hoàn tất `success`:

- **Validate Bizrise DDG V2** — run `32960670245`.
- **Build Bizrise DDG V2 Release** — run `32960670229`.

Frontend production vẫn không fetch trực tiếp được từ môi trường QA: request tới `https://dangduonggroup.com/` trả `Cache miss`. Endpoint dự kiến của Deploy Bridge `/wp-json/bizrise-deploy/v1/status` cũng chưa truy cập được từ công cụ hiện tại. Vì vậy run này không thể tự click/viewport-test production và không suy đoán trạng thái live.

Audit sử dụng source branch mới nhất, fix report mới nhất, GitHub CI và screenshot production gần nhất đã cung cấp trong dự án.

## Delta so với audit trước

| Hạng mục | Trạng thái source | Trạng thái production |
|---|---|---|
| P0-01 Product thiếu Featured Image | SOURCE REPAIR EXISTS | CHƯA XÁC MINH runtime report / DB |
| P0-02 Repair phụ thuộc admin | **SOURCE FIXED** | CHƯA XÁC MINH runtime đã chạy |
| P0-03 Legacy media override | SOURCE PASS | CHƯA XÁC MINH live code/activation |
| P1-01 Header/logo desktop | SOURCE IMPROVED | CHƯA XÁC MINH live |
| P1-02 Theme version drift | SOURCE PASS | CHƯA XÁC MINH deploy/cache |
| P1-03 Product image 9:16 | SOURCE PASS | CHƯA XÁC MINH live |
| P1-04 Category filter | SOURCE PASS phần render | CHƯA XÁC MINH taxonomy DB/live |
| P1-05 8 single-product live QA | BLOCKED | CHƯA TEST |
| P1-06 Knowledge + 5 article live QA | BLOCKED | CHƯA TEST |

## Phạm vi recheck

| Khu vực | Cách kiểm tra | Kết quả |
|---|---|---|
| `/` | source + screenshot production cũ | PARTIAL |
| `/ve-dang-duong/` | source + screenshot production cũ | PARTIAL |
| `/nang-luc/` | source | SOURCE REVIEW ONLY |
| `/thuong-hieu/` | source | SOURCE REVIEW ONLY |
| `/san-pham/` | source + screenshot production gần nhất | FAIL LIVE EVIDENCE / SOURCE REPAIR EXISTS |
| product categories | archive source | SOURCE IMPROVED / LIVE BLOCKED |
| 8+ product detail | template/source | LIVE BLOCKED |
| `/kien-thuc/` | source/article registry | SOURCE PASS / LIVE BLOCKED |
| 5+ bài viết | registry + templates | SOURCE PASS / LIVE BLOCKED |
| mobile 360/390/430 | responsive CSS source | LIVE BLOCKED |

## P0 — blocker production

### P0-01 — Product public thiếu Featured Image: repair source đã có, production report chưa có

- **URL/khu vực:** `/san-pham/`, product category archives, single product.
- **Bằng chứng production gần nhất:** screenshot trước đó cho thấy nhiều card từng hiển thị placeholder `ĐĂNG DƯƠNG` thay vì ảnh sản phẩm.
- **Source repair:** `apps/bizrise-ddg-migrator/data/product-media-manifest.csv` có 44 record; `ProductMediaRepair.php` matching deterministic bằng exact source filename, fallback exact brand + product name + pack size; poster exact manifest key/basename; ambiguity thì không gán.
- Repair chỉ điền Featured Image đang thiếu/hỏng, không ghi đè manual image hợp lệ, không đổi Product Truth/taxonomy/publish state.
- **Production còn thiếu bằng chứng:** chưa có runtime report WordPress cho `matched_products`, `repaired`, `public_missing_featured`, ambiguity và errors.
- **PASS bắt buộc:** `public_missing_featured = []`, `errors = []`, không `product_ambiguous`/`poster_ambiguous`; `/san-pham/` không placeholder; attachment filename khớp manifest; HOLD/draft không xuất hiện.

### P0-02 — Repair không còn phụ thuộc admin: SOURCE FIXED, runtime chưa xác minh

Fix Agent đã thay đổi migrator để repair chạy qua guarded `init` runtime thay vì chỉ `admin_init` + `manage_options`.

- Migrator V2 chạy `ProductMediaRepair::run(true)` khi repair version chưa hoàn tất.
- Có transient lock chống chạy song song.
- Nếu report còn unresolved/errors thì không đánh dấu hoàn tất và retry sau backoff.
- Nếu report sạch thì lưu repair version và không chạy lại.
- Runtime report có `trigger=runtime_init` và `ran_at`.

**Source status:** PASS theo fix report và CI.

**Production status:** CHƯA XÁC MINH vì chưa đọc được runtime report hoặc Deploy Bridge status từ production.

**PASS bắt buộc:** production report phải cho thấy `trigger=runtime_init`, `public_missing_featured=[]`, `errors=[]`, không ambiguity, và timestamp sau deploy SHA chứa fix này.

### P0-03 — Legacy media override

Source fix trước đã chuyển `bizrise-ddg-media-hotfix` sang diagnostic-only, không còn fetch/sideload/`set_post_thumbnail()`.

- **Source:** PASS.
- **Production:** CHƯA XÁC MINH plugin/live code đang đúng source mới.
- **PASS:** không có process production nào ghi đè portrait Featured Image sau repair.

## P1 — QA quan trọng

### P1-01 — Header/logo desktop

Source đã bỏ hard-code asset 2.1.2 và dùng enqueue/version thống nhất. Live chưa kiểm browser được.

**PASS:** desktop >=1180px logo đúng tỉ lệ; nav + CTA đầy đủ; sticky header không crop/che hero; mobile dùng đúng menu ở breakpoint.

### P1-02 — Theme version/cache

Source hiện thống nhất Theme 2.1.3 theo fix report. **Production PASS** chỉ khi deployed SHA được xác minh và cache/CDN đã purge; asset live phải là version hiện hành.

### P1-03 — Portrait product stage

Canonical source dùng 9:16 + `object-fit: contain` + centered.

**Live status:** CHƯA XÁC MINH.

**PASS:** mọi card ảnh nằm gọn 9:16 trên desktop/mobile, không crop, không hover-scale làm cắt sản phẩm, không nhảy chiều cao.

### P1-04 — Product category filter

Archive source không còn whitelist 8 slug tĩnh; render dựa trên `product_cat` có product và loại Uncategorized.

**Data vẫn chưa PASS** vì taxonomy thực tế nằm trong WordPress DB.

**PASS:** 100% product public đúng category deterministic; filter không category rác/legacy; từng category trả đúng SKU.

### P1-05 — >=8 single product nhiều brand

**Live status:** CHƯA TEST do frontend fetch bị chặn.

**PASS:** ít nhất 8 SKU thuộc nhiều brand/collection: image, title, brand, pack, CTA, hồ sơ công bố nếu có, related section đều đúng; không kéo HOLD/draft.

### P1-06 — `/kien-thuc/` + >=5 article live

Article registry/source đã hoàn chỉnh về mặt source và vẫn giữ publication gate `editorial_review`.

Live 200/404, typography, ảnh, excerpt, internal link, CTA và responsive vẫn CHƯA XÁC MINH.

## H1 / copy / CTA / broken link

- Source review trước: `page.php`, `single.php`, `single-product.php` không thấy duplicate H1 chắc chắn.
- Không có broken link chắc chắn từ source; helper URL theo slug vẫn có thể 404 nếu WordPress DB chưa có page tương ứng.
- Cần live crawl tối thiểu: `/san-pham/`, `/doi-tac/`, `/lien-he/`, `/tim-diem-ban/`, `/kien-thuc/`, `/nghien-cuu-phat-trien/`, `/oem-odm-my-pham/`.

## CI / deploy evidence

- Branch HEAD hiện tại: `800b494ef9758201b394e73cd51bbc5fc512d183`.
- Validate workflow: **SUCCESS** run `32960670245`.
- Release workflow: **SUCCESS** run `32960670229`.
- **Production deployed SHA:** CHƯA XÁC MINH.
- **WordPress Deploy Bridge status:** CHƯA XÁC MINH.
- **WordPress runtime media-repair report:** CHƯA XÁC MINH.
- CI PASS chỉ chứng minh source/build, không chứng minh WordPress DB hoặc frontend production PASS.

## Tiêu chí QA vòng tiếp theo

1. Xác minh production `deployed_sha` đúng hoặc mới hơn HEAD đã CI PASS.
2. Xác minh Product Media Repair đã thực sự chạy sau deploy; report phải sạch.
3. Purge cache/CDN và xác nhận asset theme đúng version.
4. Audit 100% product public: product ID → product key → brand/category → `_thumbnail_id` → attachment filename → manifest expected.
5. `/san-pham/` và toàn bộ category không placeholder, không ảnh legacy/crop.
6. Desktop >=1180px: header/logo/nav/CTA PASS.
7. Mobile 360/390/430px: header/menu/card PASS.
8. Mở >=8 single product nhiều brand, kiểm facts/media/HOLD.
9. Mở `/kien-thuc/` + >=5 article live; không 404, layout/CTA/internal links PASS.

## Kết luận QA

Source hiện đã giải quyết blocker P0-02: media repair có đường runtime tự động sau deploy và exact HEAD đang qua cả hai CI gate. Tuy nhiên **production vẫn chưa đạt PASS** vì môi trường QA chưa truy cập được frontend/Deploy Bridge endpoint, chưa xác minh deployed SHA, chưa đọc được WordPress runtime repair report và chưa thực hiện browser QA bắt buộc cho catalog, 8 product, mobile và Knowledge.

Không có bằng chứng mới cho phép chuyển trạng thái production sang PASS trong vòng này.