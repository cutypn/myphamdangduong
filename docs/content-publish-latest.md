# DDG content publish report — latest

## Kết luận

Knowledge content hiện có **10/10** bài trong `data/content/article-registry.json` ở trạng thái `publish_ready`, và **10/10 Markdown front matter đã được xác nhận đồng bộ `publish_ready`** với reviewer/source-safe metadata phù hợp.

Runtime importer deterministic tồn tại trong `apps/bizrise-ddg-migrator/src/ArticleContentImporter.php`: exact-slug upsert, `post_status=publish`, category `kien-thuc`, idempotent content fingerprint và bỏ H1 trong Markdown khi render để theme sở hữu H1 duy nhất.

Core-page source trong `apps/bizrise-ddg-theme/inc/editorial-content.php` tiếp tục dùng curated source-safe copy, không công bố cGMP/ISO/FDA/công suất/đối tác hoặc claim y tế khi chưa có hồ sơ xác minh.

## Cải thiện source vòng này

Đã đồng bộ bốn bài còn lệch metadata:

1. `quy-trinh-gia-cong-my-pham.md` — commit `eb8037f63f69a4d84c824d274df9d49c974c1b0a`.
2. `nghien-cuu-cong-thuc-my-pham.md` — commit `1db91e7b31b0ed435a75cabd9a0dedfe03656b2d`.
3. `lam-mau-my-pham-can-luu-y-gi.md` — commit `f6752b311b69c281e1984609a466c4e2df2ff09c`.
4. `thiet-ke-bao-bi-my-pham.md` — commit `406a003bb002bf32c4897ee0558b08b803785204`.

Các thay đổi chung:

- `review_status: editorial_review` → `publish_ready`;
- reviewer `pending` → `source-safe editorial`;
- `last_verified` → `2026-08-27`;
- không thêm claim, chứng nhận, công suất, đối tác hoặc product detail copy.

Riêng bài `quy-trinh-gia-cong-my-pham.md` còn có internal link `/checklist-brief-oem-odm-my-pham/` không tồn tại trong 10-article registry. Link này đã được thay deterministic bằng hub hợp lệ `/oem-odm-my-pham/` ở cả front matter và phần “Đọc tiếp”; dòng `Last verified` trong body cũng được cập nhật 27/08/2026.

## Knowledge articles

- total registry: **10**
- publish_ready registry: **10**
- editorial_review registry: **0**
- Markdown front matter publish_ready confirmed: **10/10**
- Markdown reviewer pending còn lại: **0**
- deterministic importer: **CÓ**
- importer bỏ Markdown H1 khỏi `post_content`: **CÓ**
- exact-slug/idempotent publication path: **CÓ**
- invalid registry-link phát hiện/sửa vòng này: **1**

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

Endpoint này có thể audit Featured Image ID/file/URL/ALT/kích thước, missing featured và duplicate attachment cho bài public.

Counts source vòng này:

- article source before: **10 publish-ready registry / 6 Markdown metadata confirmed** (report trước ghi 4/10 nhưng audit lại phát hiện `oem-va-odm-my-pham-khac-nhau-the-nao` và `cach-lua-chon-nha-may-gia-cong-my-pham` đã được đồng bộ từ trước);
- article source after: **10 publish-ready registry / 10 Markdown metadata confirmed**;
- internal links sai phát hiện/sửa: **1**;
- article runtime media before: **CHƯA XÁC MINH**;
- article runtime media after: **CHƯA XÁC MINH**;
- media mapping tự tạo/gán mơ hồ: **0**.

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

**PENDING: FINAL CI + PRODUCTION ARTICLE/MEDIA QA.**
