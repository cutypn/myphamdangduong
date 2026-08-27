# DDG content publish report — latest

## Kết luận

Knowledge content hiện có **10/10** bài trong `data/content/article-registry.json` ở trạng thái `publish_ready`, và **10/10 Markdown front matter** đã đồng bộ `publish_ready` với reviewer/source-safe metadata phù hợp.

Runtime importer deterministic tồn tại trong `apps/bizrise-ddg-migrator/src/ArticleContentImporter.php`: exact-slug upsert, `post_status=publish`, category `kien-thuc`, idempotent content fingerprint và bỏ H1 trong Markdown khi render để theme sở hữu H1 duy nhất.

Core-page source trong `apps/bizrise-ddg-migrator/data/site-content.php` tiếp tục dùng source-safe copy, không công bố cGMP/ISO/FDA/công suất/đối tác hoặc claim y tế khi chưa có hồ sơ xác minh.

## Cải thiện source vòng này

Đã rà bài `thiet-ke-bao-bi-my-pham.md` và phát hiện nhiều jargon nội bộ/tiếng Anh chưa được giải thích cho người đọc phổ thông: `hierarchy`, `artwork`, `version`, `preflight`, `Product Truth`.

Đã sửa source ở commit `f4c95198833cb56987710ce2a35c7bd10e2b4cb6`:

- đổi `hierarchy` thành “thứ tự ưu tiên thông tin”;
- đổi `artwork` thành “file thiết kế (artwork)” ở lần xuất hiện cần thiết, sau đó dùng tiếng Việt;
- đổi `version` thành “phiên bản” và làm rõ các trạng thái nháp/kiểm tra/đã duyệt/sản xuất;
- giải thích `preflight` là “kiểm tra lần cuối trước khi gửi sản xuất”;
- thay `Product Truth` trong body bằng “nguồn dữ liệu sản phẩm chuẩn/đã thống nhất” để người đọc không cần biết jargon nội bộ;
- cập nhật meta description, direct answer và CTA cho nhất quán với cách gọi mới;
- cập nhật `last_verified` sang **2026-08-28**;
- không thay Product Truth, không sửa product detail copy và không thêm claim/certification/contact fact chưa xác minh.

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

Homepage nằm trong `front-page.php` và không còn fallback hard-code tên thương hiệu chưa xác minh.

## Article media inventory

Endpoint source:

`/wp-json/bizrise-ddg/v1/media-inventory?scope=articles&per_page=100`

Endpoint có thể audit Featured Image ID/file/URL/ALT/kích thước, missing featured và duplicate attachment cho bài public.

Counts vòng này:

- article source before: **10 publish-ready / 10 metadata synchronized**;
- article source after: **10 publish-ready / 10 metadata synchronized**;
- bài packaging còn jargon chưa giải thích trước: **1**;
- bài packaging còn jargon chưa giải thích sau: **0**;
- article runtime media before: **CHƯA XÁC MINH**;
- article runtime media after: **CHƯA XÁC MINH**;
- media mapping tự tạo/gán mơ hồ: **0**.

Production endpoint vẫn chưa đọc được từ runtime kiểm tra hiện tại: direct web open tới URL REST bị safe-URL restriction và search không trả endpoint indexable. Vì vậy không suy diễn Featured Image ID/file/URL/ALT/kích thước, missing featured, duplicate attachment, article sync runtime hay trạng thái live render.

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

**PACKAGING ARTICLE LANGUAGE QA: 1/1 JARGON CLEANUP COMPLETE.**

**PENDING: CI FOR FINAL HEAD + PRODUCTION ARTICLE/MEDIA QA.**
