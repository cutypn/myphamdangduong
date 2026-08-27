# DDG content publish report — latest

## Kết luận

Knowledge content hiện có **10/10** bài trong `data/content/article-registry.json` ở trạng thái `publish_ready`, và **10/10 Markdown front matter** đã đồng bộ `publish_ready` với reviewer/source-safe metadata phù hợp.

Runtime importer deterministic tồn tại trong `apps/bizrise-ddg-migrator/src/ArticleContentImporter.php`: exact-slug upsert, `post_status=publish`, category `kien-thuc`, idempotent content fingerprint và bỏ H1 trong Markdown khi render để theme sở hữu H1 duy nhất.

Core-page source trong `apps/bizrise-ddg-migrator/data/site-content.php` tiếp tục dùng source-safe copy, không công bố cGMP/ISO/FDA/công suất/đối tác hoặc claim y tế khi chưa có hồ sơ xác minh.

## Cải thiện source vòng này

Đã rà bài `oem-va-odm-my-pham-khac-nhau-the-nao.md` và phát hiện nhiều jargon/tiếng Anh làm giảm độ rõ với người đọc phổ thông: `brief/spec`, `scope`, `audience`, `product role`, `owner`, `brand strategy`, `technical brief`, `trade-off`, `product architecture`, `go-to-market`, `timeline`, `ownership`, `Product Truth`, `provenance`, `hero product`, `proposal`.

Đã sửa source ở commit `8d9b4b7c3a191b3fe0f9d9ef499abfbb28fdf2c1`:

- đổi `brief/spec` thành “yêu cầu sản phẩm/yêu cầu phát triển”;
- đổi `scope` thành “phạm vi công việc”;
- đổi `audience`, `product role`, `owner` thành “nhóm khách hàng”, “vai trò sản phẩm”, “người phụ trách/ra quyết định”;
- đổi `technical brief`, `trade-off`, `timeline` thành cách diễn đạt tiếng Việt dễ hiểu;
- bỏ `Product Truth/provenance` khỏi body, thay bằng “nguồn dữ liệu đã được kiểm tra và thống nhất”;
- đổi `go-to-market`, `hero product`, `proposal` thành “cách ra thị trường”, “sản phẩm ưu tiên”, “phương án/phạm vi công việc”;
- cập nhật meta description, direct answer, CTA và `last_verified` sang **2026-08-28**;
- giữ nguyên trạng thái `publish_ready`, internal links và các nguồn pháp lý/ASEAN;
- không sửa product detail copy và không thêm claim/certification/contact fact chưa xác minh.

Nguồn pháp lý được kiểm lại ngày 2026-08-28 trên Cổng TTĐT Chính phủ: Nghị định 93/2016/NĐ-CP là văn bản về điều kiện sản xuất mỹ phẩm; Thông tư 34/2025/TT-BYT sửa đổi quy định quản lý mỹ phẩm và có hiệu lực từ 18/08/2025.

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
- bài OEM-vs-ODM còn cụm jargon cần làm sạch trước: **1**;
- bài OEM-vs-ODM còn cụm jargon cần làm sạch sau: **0**;
- article runtime media before: **CHƯA XÁC MINH**;
- article runtime media after: **CHƯA XÁC MINH**;
- media mapping tự tạo/gán mơ hồ: **0**.

Production endpoint vẫn chưa đọc được từ runtime kiểm tra hiện tại: direct web open tới URL REST bị safe-URL restriction; exact URL search trên `dangduonggroup.com` không trả endpoint indexable. Vì vậy không suy diễn Featured Image ID/file/URL/ALT/kích thước, missing featured, duplicate attachment, article sync runtime hay trạng thái live render.

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

**OEM-vs-ODM LANGUAGE QA: JARGON CLEANUP COMPLETE.**

**PENDING: CI FOR FINAL HEAD + PRODUCTION ARTICLE/MEDIA QA.**
