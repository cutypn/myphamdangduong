# DDG content publish report — latest

## Kết luận

Knowledge content hiện có **10/10** bài trong `data/content/article-registry.json` ở trạng thái `publish_ready`, và **10/10 Markdown front matter** đã đồng bộ `publish_ready` với reviewer/source-safe metadata phù hợp.

Runtime importer deterministic tồn tại trong `apps/bizrise-ddg-migrator/src/ArticleContentImporter.php`: exact-slug upsert, `post_status=publish`, category `kien-thuc`, idempotent content fingerprint và bỏ H1 trong Markdown khi render để theme sở hữu H1 duy nhất.

Core-page source trong `apps/bizrise-ddg-migrator/data/site-content.php` tiếp tục dùng source-safe copy, không công bố cGMP/ISO/FDA/công suất/đối tác hoặc claim y tế khi chưa có hồ sơ xác minh.

## Cải thiện source vòng này

Phát hiện các trang `/nang-luc/`, `/thuong-hieu/`, `/kien-thuc/`, `/doi-tac/`, `/lien-he/` và `/tim-diem-ban/` còn ngắn, thiếu hành trình đọc, internal links hoặc CTA đủ rõ.

Đã sửa source ở commit `cd2925189211261340075483e843cc21410f0055`:

- mở rộng `/nang-luc/` theo hành trình R&D → phát triển sản phẩm → sản xuất/chất lượng → OEM/ODM; thêm internal links và bước tiếp theo;
- mở rộng `/thuong-hieu/` với cách đi từ câu chuyện thương hiệu tới routine, link sang `/san-pham/` và `/kien-thuc/`, không tạo claim thương hiệu chưa xác minh;
- mở rộng `/kien-thuc/` theo bốn hướng đọc: làn da, thành phần, routine và B2B; thêm exact internal links tới bài quy trình gia công và so sánh OEM/ODM;
- mở rộng `/doi-tac/` theo từng nhu cầu phân phối, đại lý, affiliate, OEM/ODM; thêm chuẩn bị brief và CTA, không tự nêu điều kiện thương mại;
- mở rộng `/lien-he/` bằng checklist dữ liệu cần chuẩn bị theo mục tiêu, không tự điền contact fact;
- mở rộng `/tim-diem-ban/` với policy chỉ hiển thị dữ liệu điểm bán đã xác minh và fallback về `/lien-he/`;
- không sửa product detail copy.

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

Core-page targeted content gaps this round:

- before: **6** (`Năng lực`, `Thương hiệu`, `Kiến thức`, `Đối tác`, `Liên hệ`, `Tìm điểm bán`)
- after: **0** cho sáu trang đã nhắm mục tiêu trong vòng này

## Article media inventory

Endpoint source:

`/wp-json/bizrise-ddg/v1/media-inventory?scope=articles&per_page=100`

Endpoint có thể audit Featured Image ID/file/URL/ALT/kích thước, missing featured và duplicate attachment cho bài public.

Counts vòng này:

- article source before: **10 publish-ready / 10 metadata synchronized**;
- article source after: **10 publish-ready / 10 metadata synchronized**;
- core-page targeted gaps before: **6**;
- core-page targeted gaps after: **0**;
- article runtime media before: **CHƯA XÁC MINH**;
- article runtime media after: **CHƯA XÁC MINH**;
- media mapping tự tạo/gán mơ hồ: **0**.

Production endpoint chưa đọc được từ runtime kiểm tra hiện tại: direct open bị safe-URL policy chặn và public search không index REST inventory. Vì vậy không suy diễn Featured Image/ALT/missing/duplicate counts, article sync runtime hay trạng thái live render.

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

**CORE CONTENT FIX: SIX REMAINING CORE-PAGE JOURNEYS EXPANDED WITH SOURCE-SAFE COPY, INTERNAL LINKS AND CTA.**

**PENDING: CI FOR FINAL HEAD + PRODUCTION ARTICLE/MEDIA QA.**
