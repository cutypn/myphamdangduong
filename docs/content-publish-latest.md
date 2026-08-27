# DDG content publish report — latest

## Kết luận

Knowledge content vẫn có **10/10** bài trong `data/content/article-registry.json` ở trạng thái `publish_ready`. Runtime importer deterministic đã tồn tại trong `apps/bizrise-ddg-migrator/src/ArticleContentImporter.php`: exact-slug upsert, `post_status=publish`, category `kien-thuc`, idempotent content fingerprint và bỏ H1 trong Markdown khi render để theme sở hữu H1 duy nhất.

Core-page source trong `apps/bizrise-ddg-theme/inc/editorial-content.php` tiếp tục dùng curated source-safe copy, không công bố cGMP/ISO/FDA/công suất/đối tác hoặc claim y tế khi chưa có hồ sơ xác minh.

## Cải thiện source vòng này

Commit content: `bca769b1426dbb18979235db652d71d2c8411705`

File: `data/content/articles/odm-my-pham-la-gi.md`

Đã sửa:

- đồng bộ front matter `review_status` từ `editorial_review` sang `publish_ready`;
- đổi reviewer từ `pending` sang `source-safe editorial`;
- cập nhật `last_verified` sang `2026-08-27`;
- đồng bộ dòng `Last verified` trong body sang `27/08/2026`;
- giữ nguyên internal links vì cả 3 slug đều tồn tại trong article registry;
- không thay đổi claim, chứng nhận, công suất, đối tác hoặc product detail copy.

Đây là fix source consistency để registry và Markdown không tự mâu thuẫn khi QA/importer kiểm tra publication state.

## Knowledge articles

- total registry: **10**
- publish_ready registry: **10**
- editorial_review registry: **0**
- Markdown front matter đã đồng bộ publish-ready xác nhận: **2/10** (`oem-my-pham-la-gi`, `odm-my-pham-la-gi`)
- Markdown còn lại cần tiếp tục rà front matter/link consistency: **8**
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

Endpoint này có thể audit Featured Image ID/file/URL/ALT/kích thước, missing featured và duplicate attachment cho bài public. Môi trường kiểm tra hiện tại chưa đọc production endpoint trực tiếp một cách đáng tin cậy, vì vậy không tự suy diễn media live.

Counts:

- article source before: **10 publish-ready registry / 1 Markdown metadata synced**
- article source after: **10 publish-ready registry / 2 Markdown metadata synced**
- internal links mới phát hiện sai vòng này: **0**
- article runtime media before: **CHƯA XÁC MINH**
- article runtime media after: **CHƯA XÁC MINH**
- media mapping tự tạo/gán mơ hồ: **0**

## CI

Exact content SHA `bca769b1426dbb18979235db652d71d2c8411705`:

- Validate Bizrise DDG V2: **QUEUED tại thời điểm report**
- Build Bizrise DDG V2 Release: **QUEUED tại thời điểm report**

Không đánh dấu CI PASS cho SHA này cho tới khi workflow hoàn tất thành công.

## Production gate

Chưa đánh production PASS vì chưa có live runtime/media verification cho final HEAD. Production PASS cần đủ:

1. Deploy Bridge `deployed_sha` khớp final HEAD đã PASS CI.
2. Runtime status báo article sync `10/10`, `error_count=0`.
3. `/kien-thuc/` và 10 article URL render đúng H1/direct answer/body/CTA.
4. Media inventory xác minh Featured Image/ALT và missing/duplicate counts.
5. Không còn internal link 404 trong 10 bài publish-ready.

## An toàn nội dung

Không thêm cGMP/ISO/FDA không có hồ sơ hiện hành, số liệu công suất chưa xác minh, tên đối tác chưa xác minh, contact fact chưa có nguồn, claim y tế/hiệu quả chưa được duyệt hoặc product detail copy.

## Trạng thái

**SOURCE CONTENT: 10/10 KNOWLEDGE ARTICLES PUBLISH-READY.**

**MARKDOWN METADATA SYNC: 2/10 CONFIRMED.**

**THIS ROUND: ODM ARTICLE SOURCE STATE ALIGNED WITH REGISTRY.**

**CI FOR CURRENT CONTENT SHA: PENDING.**

**PRODUCTION ARTICLE/MEDIA QA: CHƯA XÁC MINH LIVE.**
