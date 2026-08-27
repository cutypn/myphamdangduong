# DDG content publish report — latest

## Kết luận

Knowledge content hiện có **10/10** bài trong `data/content/article-registry.json` ở trạng thái `publish_ready`, và **10/10 Markdown front matter** đã đồng bộ `publish_ready` với reviewer/source-safe metadata phù hợp.

Runtime importer deterministic tồn tại trong `apps/bizrise-ddg-migrator/src/ArticleContentImporter.php`: exact-slug upsert, `post_status=publish`, category `kien-thuc`, idempotent content fingerprint và bỏ H1 trong Markdown khi render để theme sở hữu H1 duy nhất.

Core-page source trong `apps/bizrise-ddg-theme/inc/editorial-content.php` tiếp tục dùng curated source-safe copy, không công bố cGMP/ISO/FDA/công suất/đối tác hoặc claim y tế khi chưa có hồ sơ xác minh.

## Cải thiện source vòng này

Phát hiện khoảng trống giữa metadata và body ở bài `lam-mau-my-pham-can-luu-y-gi.md`: front matter đã khai báo 3 `internal_links` và CTA nhưng body chưa có cụm điều hướng/CTA tương ứng.

Đã sửa ở commit `72211c4ad6414f7d0a0bdd8e07efa129fa7f6e2f`:

- thêm mục **Đọc tiếp theo hành trình phát triển**;
- liên kết exact-slug tới `/rd-my-pham-la-gi/`, `/nghien-cuu-cong-thuc-my-pham/`, `/quy-trinh-gia-cong-my-pham/`;
- thêm CTA cuối bài bám đúng metadata;
- không thêm claim, certification, capacity, named partner, contact fact hoặc product detail copy.

## Knowledge articles

- total registry: **10**
- publish_ready registry: **10**
- editorial_review registry: **0**
- Markdown front matter publish_ready: **10/10**
- Markdown reviewer pending: **0**
- deterministic importer: **CÓ**
- exact-slug/idempotent publication path: **CÓ**
- body/internal-navigation gap phát hiện/sửa vòng này: **1**

## Core pages source

Curated editorial content tiếp tục bao phủ:

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

## Article media inventory

Endpoint source:

`/wp-json/bizrise-ddg/v1/media-inventory?scope=articles&per_page=100`

Endpoint có thể audit Featured Image ID/file/URL/ALT/kích thước, missing featured và duplicate attachment cho bài public.

Counts vòng này:

- article source before: **10 publish-ready / 10 metadata synchronized**;
- article source after: **10 publish-ready / 10 metadata synchronized**;
- sampling article navigation gap before: **1**;
- sampling article navigation gap after: **0**;
- article runtime media before: **CHƯA XÁC MINH**;
- article runtime media after: **CHƯA XÁC MINH**;
- media mapping tự tạo/gán mơ hồ: **0**.

Production endpoint và `/kien-thuc/` chưa đọc được đáng tin cậy từ môi trường kiểm tra hiện tại, nên không suy diễn Featured Image/ALT/missing/duplicate counts hay trạng thái live render.

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

**LATEST CONTENT FIX: SAMPLING ARTICLE INTERNAL NAVIGATION + CTA ADDED.**

**PENDING: FINAL CI + PRODUCTION ARTICLE/MEDIA QA.**
