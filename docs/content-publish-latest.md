# DDG content publish report — latest

## Kết luận

Knowledge content hiện có **10/10** bài trong `data/content/article-registry.json` ở trạng thái `publish_ready`, và **10/10 Markdown front matter** đã đồng bộ `publish_ready` với reviewer/source-safe metadata phù hợp.

Runtime importer deterministic tồn tại trong `apps/bizrise-ddg-migrator/src/ArticleContentImporter.php`: exact-slug upsert, `post_status=publish`, category `kien-thuc`, idempotent content fingerprint và bỏ H1 trong Markdown khi render để theme sở hữu H1 duy nhất.

Core-page source trong `apps/bizrise-ddg-migrator/data/site-content.php` tiếp tục dùng source-safe copy, không công bố cGMP/ISO/FDA/công suất/đối tác hoặc claim y tế khi chưa có hồ sơ xác minh.

## Cải thiện source vòng này

Phát hiện homepage `front-page.php` có fallback hard-code bốn tên thương hiệu khi taxonomy thương hiệu không trả về dữ liệu. Fallback này có thể khiến production hiển thị tên thương hiệu không được xác minh từ runtime taxonomy hiện tại.

Đã sửa source ở commit `094b3a0936bf3fdfbe3bba6c82ba15e878837cc2`:

- bỏ toàn bộ bốn tên thương hiệu hard-code khỏi fallback homepage;
- khi taxonomy không có term public, homepage chỉ hiển thị một CTA chung tới `/thuong-hieu/`;
- tên thương hiệu cụ thể chỉ được render khi lấy được term thật từ taxonomy public;
- không thay Product Truth, không sửa product detail copy và không thêm brand/partner/contact fact chưa xác minh.

## Knowledge articles

- total registry: **10**
- publish_ready registry: **10**
- editorial_review registry: **0**
- Markdown front matter publish_ready: **10/10**
- Markdown reviewer pending: **0**
- deterministic importer: **CÓ**
- exact-slug/idempotent publication path: **CÓ**

## Core pages source

Curated/source-safe content bao phủ:

- `/ve-dang-duong/`
- `/nang-luc/`
- `/nghien-cuu-phat-trien/`
- `/nha-may-san-xuat-my-pham/`
- `/oem-odm-my-pham/`
- `/thuong-hieu/`
- `/san-pham/`
- `/kien-thuc/`
- `/doi-tac/`
- `/lien-he/`
- `/tim-diem-ban/`

Homepage nằm trong `front-page.php`.

Homepage unverified brand fallback:

- before: **4 hard-coded brand names**;
- after: **0 hard-coded brand names**;
- runtime taxonomy-backed brand names: render only when public terms exist.

## Article media inventory

Endpoint source:

`/wp-json/bizrise-ddg/v1/media-inventory?scope=articles&per_page=100`

Endpoint có thể audit Featured Image ID/file/URL/ALT/kích thước, missing featured và duplicate attachment cho bài public.

Counts vòng này:

- article source before: **10 publish-ready / 10 metadata synchronized**;
- article source after: **10 publish-ready / 10 metadata synchronized**;
- homepage hard-coded brand fallbacks before: **4**;
- homepage hard-coded brand fallbacks after: **0**;
- article runtime media before: **CHƯA XÁC MINH**;
- article runtime media after: **CHƯA XÁC MINH**;
- media mapping tự tạo/gán mơ hồ: **0**.

Production endpoint vẫn chưa đọc được từ runtime kiểm tra hiện tại: direct web open tới URL REST bị safe-URL restriction và search không trả endpoint indexable. Vì vậy không suy diễn Featured Image/ALT/missing/duplicate counts, article sync runtime hay trạng thái live render.

## Production gate

Chưa đánh production PASS nếu chưa có live runtime/media verification cho final HEAD. Production PASS cần đủ:

1. Deploy Bridge `deployed_sha` khớp final HEAD đã PASS CI.
2. Runtime status báo article sync `10/10`, `error_count=0`.
3. `/kien-thuc/` và 10 article URL render đúng H1/direct answer/body/CTA.
4. Media inventory xác minh Featured Image/ALT và missing/duplicate counts.
5. Không còn internal link 404 trong 10 bài publish-ready.

## An toàn nội dung

Không thêm cGMP/ISO/FDA không có hồ sơ hiện hành, số liệu công suất chưa xác minh, tên đối tác/thương hiệu chưa được runtime xác minh, contact fact chưa có nguồn, claim y tế/hiệu quả chưa được duyệt hoặc product detail copy.

## Trạng thái

**SOURCE CONTENT: 10/10 KNOWLEDGE ARTICLES PUBLISH-READY.**

**MARKDOWN METADATA SYNC: 10/10 CONFIRMED.**

**HOMEPAGE CONTENT TRUTH FIX: 4 UNVERIFIED HARD-CODED BRAND FALLBACKS REMOVED.**

**PENDING: CI FOR FINAL HEAD + PRODUCTION ARTICLE/MEDIA QA.**
