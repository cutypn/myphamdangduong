# DDG content publish report — latest

## Kết luận

Knowledge content hiện có **10/10** bài trong `data/content/article-registry.json` ở trạng thái `publish_ready`, và **10/10 Markdown front matter** đã đồng bộ `publish_ready` với reviewer/source-safe metadata phù hợp.

Runtime importer deterministic tồn tại trong `apps/bizrise-ddg-migrator/src/ArticleContentImporter.php`: exact-slug upsert, `post_status=publish`, category `kien-thuc`, idempotent content fingerprint và bỏ H1 trong Markdown khi render để theme sở hữu H1 duy nhất.

Core-page source trong `apps/bizrise-ddg-migrator/data/site-content.php` tiếp tục dùng source-safe copy, không công bố cGMP/ISO/FDA/công suất/đối tác hoặc claim y tế khi chưa có hồ sơ xác minh.

## Cải thiện source vòng này

Phát hiện `/nha-may-san-xuat-my-pham/` đã có body copy, cấu trúc H2 và internal links nhưng chưa có CTA kết thúc rõ như các core page khác.

Đã sửa source ở commit `e4d62b081abc33b4a3bfd8f9d7e29165d4202c90`:

- thêm CTA kết thúc cho `/nha-may-san-xuat-my-pham/`;
- CTA hướng người đọc hoàn thiện brief + danh sách dữ liệu cần xác minh rồi dùng cùng bộ đầu vào để so sánh phạm vi, điểm bàn giao và trách nhiệm;
- giữ nguyên internal links tới checklist lựa chọn nhà máy và quy trình gia công;
- không thêm claim cGMP/ISO/FDA, công suất, đối tác, contact fact hoặc product detail copy.

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

Core-page targeted CTA gap this round:

- before: **1** (`Năng lực sản xuất`)
- after: **0**

## Article media inventory

Endpoint source:

`/wp-json/bizrise-ddg/v1/media-inventory?scope=articles&per_page=100`

Endpoint có thể audit Featured Image ID/file/URL/ALT/kích thước, missing featured và duplicate attachment cho bài public.

Counts vòng này:

- article source before: **10 publish-ready / 10 metadata synchronized**;
- article source after: **10 publish-ready / 10 metadata synchronized**;
- core-page targeted CTA gaps before: **1**;
- core-page targeted CTA gaps after: **0**;
- article runtime media before: **CHƯA XÁC MINH**;
- article runtime media after: **CHƯA XÁC MINH**;
- media mapping tự tạo/gán mơ hồ: **0**.

Production endpoint vẫn chưa đọc được từ runtime kiểm tra hiện tại: DNS resolution tới `dangduonggroup.com` thất bại, còn web direct-open bị safe-URL restriction. Vì vậy không suy diễn Featured Image/ALT/missing/duplicate counts, article sync runtime hay trạng thái live render.

## Production gate

Chưa đánh production PASS nếu chưa có live runtime/media verification cho final HEAD. Production PASS cần đủ:

1. Deploy Bridge `deployed_sha` khớp final HEAD đã PASS CI.
2. Runtime status báo article sync `10/10`, `error_count=0`.
3. `/kien-thuc/` và 10 article URL render đúng H1/direct answer/body/CTA.
4. Media inventory xác minh Featured Image/ALT và missing/duplicate counts.
5. Không còn internal link 404 trong 10 bài publish-ready.

## An toàn nội dung

Không thêm cGMP/ISO/FDA không có hồ sơ hiện hành, số liệu công suất chưa xác minh, tên đối tác chưa xác minh, contact fact chưa có nguồn, claim y tế/hiệu quả chưa được duyệt hoặc product detail copy.

## Trạng thái

**SOURCE CONTENT: 10/10 KNOWLEDGE ARTICLES PUBLISH-READY.**

**MARKDOWN METADATA SYNC: 10/10 CONFIRMED.**

**CORE CONTENT FIX: FACTORY PAGE CTA GAP CLOSED.**

**PENDING: CI FOR FINAL HEAD + PRODUCTION ARTICLE/MEDIA QA.**
