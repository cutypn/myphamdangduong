# DDG frontend QA audit — latest

## Kết luận nhanh

**Trạng thái tổng thể: FAIL / SOURCE IMPROVED — production chưa được xác nhận PASS.**

QA recheck branch `codex/rebuild-v2` tại HEAD `ef09c908aa6c96be9c47911cc72e1c71f9c4127e` (`docs(fix): record deterministic product media repair and CI`). Hai workflow của đúng HEAD này đều đã hoàn tất `success`: **Validate Bizrise DDG V2** run `32950439735` và **Build Bizrise DDG V2 Release** run `32950439629`.

Frontend production vẫn không fetch trực tiếp được từ môi trường QA: request tới `https://dangduonggroup.com/` trả `Cache miss`. Vì vậy không thể tự click/viewport-test production trong run này và không suy đoán trạng thái live. Audit dùng source branch mới nhất, fix report, GitHub CI và screenshot production gần nhất đã cung cấp.

## Delta so với audit trước

| Hạng mục | Trạng thái source | Trạng thái production |
|---|---|---|
| P0-01 Product thiếu Featured Image | SOURCE REPAIR ADDED | CHƯA XÁC MINH repair đã chạy / DB đã sạch |
| P0-02 Repair không tự chạy ngay sau deploy | **NEW BLOCKER** | Có thể production vẫn giữ ảnh thiếu cho tới khi admin có quyền vào wp-admin |
| P0-03 Legacy media hotfix override | SOURCE PASS | CHƯA XÁC MINH live code/activation |
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
| `/san-pham/` | source + screenshot production | FAIL LIVE EVIDENCE / SOURCE REPAIR ADDED |
| product categories | archive source | SOURCE IMPROVED / LIVE BLOCKED |
| 8+ product detail | template/source | LIVE BLOCKED |
| `/kien-thuc/` | source/article registry | SOURCE PASS / LIVE BLOCKED |
| 5+ bài viết | registry + templates | SOURCE PASS / LIVE BLOCKED |
| mobile 360/390/430 | responsive CSS source | LIVE BLOCKED |

## P0 — blocker production

### P0-01 — Product public thiếu Featured Image: đã có repair source, chưa có production report

- **URL/khu vực:** `/san-pham/`, product category archives, single product.
- **Bằng chứng production gần nhất:** nhiều card từng hiển thị placeholder `ĐĂNG DƯƠNG` thay vì ảnh sản phẩm.
- **Fix source mới:** `apps/bizrise-ddg-migrator/data/product-media-manifest.csv` có đúng 44 record và `ProductMediaRepair.php` dùng matching deterministic: exact source filename, fallback exact brand + product name + pack size; poster exact meta key/basename; ambiguity thì không gán.
- Repair chỉ điền Featured Image đang thiếu/hỏng, không ghi đè manual image hợp lệ, không đổi Product Truth/taxonomy/publish state.
- **Production còn thiếu bằng chứng:** chưa có report runtime từ WordPress cho `matched_products`, `repaired`, `public_missing_featured`, ambiguity và errors.
- **PASS bắt buộc:** `public_missing_featured = []`, `errors = []`, không `product_ambiguous`/`poster_ambiguous`; `/san-pham/` không còn placeholder; attachment filename khớp manifest; HOLD/draft không xuất hiện.

### P0-02 — NEW: repair được gọi ở `admin_init`, không bảo đảm chạy tự động sau deploy

- **Source:** `ProductMediaRepair::maybe_auto_repair()` chỉ được hook vào `admin_init` và còn yêu cầu `current_user_can('manage_options')`.
- **Tác động:** deploy source thành công không đồng nghĩa DB ảnh được repair ngay. Nếu sau deploy không có admin có quyền mở wp-admin, repair có thể chưa chạy; frontend vẫn có thể tiếp tục placeholder dù code mới đã lên production.
- Điều này đặc biệt quan trọng với pipeline tự động Git → WordPress: source deploy và data repair hiện chưa phải một giao dịch end-to-end tự động.
- **PASS bắt buộc:** sau mỗi deploy cần có bằng chứng repair đã chạy cho đúng version/manifest, hoặc deploy pipeline gọi WP-CLI/action server-side tương đương; production report phải sạch trước khi đánh dấu release PASS.

### P0-03 — Legacy media override

- Source fix trước đã chuyển `bizrise-ddg-media-hotfix` sang diagnostic-only, không còn fetch/sideload/`set_post_thumbnail()`.
- **Source:** PASS.
- **Production:** CHƯA XÁC MINH plugin/live code đang đúng source mới.
- **PASS:** không có process production khác ghi đè portrait Featured Image sau repair.

## P1 — QA quan trọng

### P1-01 — Header/logo desktop

Source đã bỏ hard-code asset 2.1.2 và dùng enqueue/version thống nhất. Live chưa kiểm browser được.

**PASS:** desktop >=1180px logo đúng tỉ lệ; nav + CTA đầy đủ; sticky header không crop/che hero; mobile dùng đúng menu ở breakpoint.

### P1-02 — Theme version/cache

Source hiện thống nhất Theme 2.1.3 theo fix report. **Production PASS** chỉ khi deployed SHA được xác minh và cache/CDN đã purge; asset live phải là version hiện hành.

### P1-03 — Portrait product stage

Canonical source dùng 9:16 + `object-fit: contain` + centered. **Live status:** CHƯA XÁC MINH.

**PASS:** mọi card ảnh nằm gọn 9:16 trên desktop/mobile, không crop, không hover-scale làm cắt sản phẩm, không nhảy chiều cao.

### P1-04 — Product category filter

Archive source không còn whitelist 8 slug tĩnh; render dựa trên `product_cat` có product và loại Uncategorized. **Data vẫn chưa PASS** vì taxonomy thực tế nằm trong WordPress DB.

**PASS:** 100% product public đúng category deterministic; filter không có category rác/legacy; từng category trả đúng SKU.

### P1-05 — >=8 single product nhiều brand

**Live status:** CHƯA TEST do frontend fetch bị chặn.

**PASS:** ít nhất 8 SKU thuộc nhiều brand/collection: image, title, brand, pack, CTA, hồ sơ công bố nếu có, related section đều đúng; không kéo HOLD/draft.

### P1-06 — `/kien-thuc/` + >=5 article live

Article registry/source đã hoàn chỉnh về mặt source và vẫn giữ publication gate `editorial_review`. Live 200/404, typography, ảnh, excerpt, internal link, CTA và responsive vẫn CHƯA XÁC MINH.

## H1 / copy / CTA / broken link

- Source review trước: `page.php`, `single.php`, `single-product.php` không thấy duplicate H1 chắc chắn.
- Không có broken link chắc chắn từ source; helper URL theo slug vẫn có thể 404 nếu WordPress DB chưa có page tương ứng.
- Cần live crawl các URL tối thiểu: `/san-pham/`, `/doi-tac/`, `/lien-he/`, `/tim-diem-ban/`, `/kien-thuc/`, `/nghien-cuu-phat-trien/`, `/oem-odm-my-pham/`.

## CI / deploy evidence

- Branch HEAD audit: `ef09c908aa6c96be9c47911cc72e1c71f9c4127e`.
- Validate workflow: **SUCCESS** run `32950439735`.
- Release workflow: **SUCCESS** run `32950439629`.
- **Production deployed SHA:** CHƯA XÁC MINH.
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

Source đã tiến thêm một bước lớn: có repair deterministic cho 44 Featured Image và CI của HEAD hiện tại đều PASS. Tuy nhiên **release vẫn chưa đạt production PASS** vì chưa xác minh deployed SHA, chưa có WordPress runtime report, và repair hiện phụ thuộc `admin_init` + quyền `manage_options` nên chưa bảo đảm chạy tự động ngay sau deploy. Đây là blocker mới cần Fix Agent xử lý hoặc pipeline production phải chứng minh đã gọi repair trước khi PO đánh dấu release hoàn tất.
