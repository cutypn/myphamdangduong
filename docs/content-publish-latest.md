# DDG content publish report — latest

## Kết luận

Knowledge content vẫn có **10/10** bài trong `data/content/article-registry.json` ở trạng thái `publish_ready`. Runtime importer deterministic đã tồn tại trong `apps/bizrise-ddg-migrator/src/ArticleContentImporter.php`: exact-slug upsert, `post_status=publish`, category `kien-thuc`, idempotent content fingerprint và bỏ H1 trong Markdown khi render để theme sở hữu H1 duy nhất.

Core-page source trong `apps/bizrise-ddg-theme/inc/editorial-content.php` tiếp tục dùng curated source-safe copy, không công bố cGMP/ISO/FDA/công suất/đối tác hoặc claim y tế khi chưa có hồ sơ xác minh.

## Cải thiện source vòng này

Commit content: `88006b4dba021ea69aa8f8b5687b5f340b728986`

File: `data/content/articles/oem-my-pham-la-gi.md`

Đã sửa:

- đồng bộ front matter `review_status` từ `editorial_review` sang `publish_ready`;
- đổi reviewer từ `pending` sang `source-safe editorial`;
- cập nhật `last_verified` sang `2026-08-27`;
- loại internal link `/checklist-brief-oem-odm-my-pham/` vì slug này không tồn tại trong registry hiện hành;
- thay bằng hub hợp lệ `/oem-odm-my-pham/` ở cả metadata và phần “Đọc tiếp”;
- không thay đổi claim, chứng nhận, công suất, đối tác hoặc product detail copy.

Đây là fix user-facing thực tế: tránh publish một internal link có nguy cơ 404 và giảm inconsistency giữa registry với Markdown source.

## Knowledge articles

- total registry: **10**
- publish_ready registry: **10**
- editorial_review registry: **0**
- Markdown front matter đã đồng bộ publish-ready xác nhận vòng này: **1/10** (`oem-my-pham-la-gi`)
- các Markdown còn lại cần tiếp tục rà front matter/link consistency ở vòng sau: **9**
- deterministic importer: **CÓ**
- importer bỏ Markdown H1 khỏi `post_content`: **CÓ**
- exact-slug/idempotent publication path: **CÓ**

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

Nó có thể audit Featured Image ID/file/URL/ALT/kích thước, missing featured và duplicate attachment cho bài public. Môi trường fetch hiện tại vẫn không truy cập trực tiếp endpoint production một cách đáng tin cậy, vì vậy vòng này không tự suy diễn media live.

Counts:

- article source before: **10 publish-ready**
- article source after: **10 publish-ready**
- known broken/unregistered internal link fixed this round: **1**
- article runtime media before: **CHƯA XÁC MINH**
- article runtime media after: **CHƯA XÁC MINH**
- media mapping tự tạo/gán mơ hồ: **0**

## CI

Exact content SHA `88006b4dba021ea69aa8f8b5687b5f340b728986`:

- Validate Bizrise DDG V2: **SUCCESS**
- Build Bizrise DDG V2 Release: **SUCCESS**

## Production gate

Chưa đánh production PASS trong report này vì chưa đọc được live runtime/media endpoint từ môi trường fetch. Production PASS cần đủ:

1. Deploy Bridge `deployed_sha` khớp final HEAD đã PASS CI.
2. Runtime status báo article sync `10/10`, `error_count=0`.
3. `/kien-thuc/` và 10 article URL render đúng H1/direct answer/body/CTA.
4. Media inventory xác minh Featured Image/ALT và missing/duplicate counts.
5. Không còn internal link 404 trong 10 bài publish-ready.

## An toàn nội dung

Không thêm cGMP/ISO/FDA không có hồ sơ hiện hành, số liệu công suất chưa xác minh, tên đối tác chưa xác minh, contact fact chưa có nguồn, claim y tế/hiệu quả chưa được duyệt hoặc product detail copy.

## Trạng thái

**SOURCE CONTENT: 10/10 KNOWLEDGE ARTICLES PUBLISH-READY.**

**THIS ROUND: OEM ARTICLE METADATA + INTERNAL LINK CONSISTENCY IMPROVED.**

**CI FOR CONTENT SHA: PASS.**

**PRODUCTION ARTICLE/MEDIA QA: CHƯA XÁC MINH LIVE.**
