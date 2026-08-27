# DDG content publish report — latest

## Kết luận

Knowledge content hiện có 10/10 bài trong `data/content/article-registry.json` ở trạng thái `publish_ready`. Runtime importer deterministic đã tồn tại trong `apps/bizrise-ddg-migrator/src/ArticleContentImporter.php`: chỉ đọc bài `publish_ready`, exact-slug upsert, `post_status=publish`, gán category `kien-thuc`, chạy idempotent theo content fingerprint và lưu report runtime.

Core-page source trong `apps/bizrise-ddg-theme/inc/editorial-content.php` tiếp tục dùng curated source-safe copy, không công bố cGMP/ISO/FDA/công suất/đối tác hoặc claim y tế khi chưa có hồ sơ xác minh.

## Knowledge articles

- total: **10**
- publish_ready: **10**
- editorial_review trong registry: **0**
- deterministic importer: **CÓ**
- exact-slug/idempotent publication path: **CÓ**
- runtime live verified: **CHƯA XÁC MINH trong môi trường fetch hiện tại**

Các slug publish-ready:

1. `oem-my-pham-la-gi`
2. `odm-my-pham-la-gi`
3. `oem-va-odm-my-pham-khac-nhau-the-nao`
4. `quy-trinh-gia-cong-my-pham`
5. `cach-lua-chon-nha-may-gia-cong-my-pham`
6. `cac-buoc-phat-trien-my-pham-thuong-hieu-rieng`
7. `rd-my-pham-la-gi`
8. `nghien-cuu-cong-thuc-my-pham`
9. `lam-mau-my-pham-can-luu-y-gi`
10. `thiet-ke-bao-bi-my-pham`

## Core pages source

Theme 2 quản lý curated editorial content cho:

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

Homepage tiếp tục được quản lý trong `front-page.php`.

## Cải thiện source vòng này

Commit code: `b9f78bb26b5d639a0a72d2bb99bc07c6fedd26d6`

File: `apps/bizrise-ddg-theme/single.php`

Thay đổi:

- Excerpt/meta description của bài được đưa thành lead ngay dưới H1, tạo direct-answer layer rõ ràng cho người đọc và AEO.
- Dòng metadata hero chỉ còn ngày đăng, không lặp lại excerpt lần thứ hai.
- Giữ nguyên H1 duy nhất, body, related articles, CTA về `/kien-thuc/` và `/san-pham/`.
- Không thêm claim, certification, capacity, partner fact hoặc product claim mới.

## Article media inventory

Source endpoint đã có trong migrator 0.4.0:

`/wp-json/bizrise-ddg/v1/media-inventory?scope=articles&per_page=100`

Media inventory source có thể audit Featured Image ID/file/URL/ALT/kích thước, missing featured và duplicate attachment cho bài public. Tuy nhiên môi trường fetch của vòng này không đọc được endpoint production trực tiếp, nên không tự suy diễn media live.

Counts vòng này:

- article source before: **10 publish-ready**
- article source after: **10 publish-ready**
- article runtime media before: **CHƯA XÁC MINH**
- article runtime media after: **CHƯA XÁC MINH**
- media mapping tự tạo/gán mơ hồ: **0**

Không tự gán Featured Image khi chưa có mapping deterministic giữa bài và attachment.

## Source consistency cần tiếp tục

Một số Markdown front matter vẫn còn `review_status: editorial_review` / `reviewer: pending` dù registry hiện là nguồn publication authority và đã đánh dấu `publish_ready`. Đây là inconsistency editorial cần được đồng bộ dần ở các vòng tiếp theo; importer hiện chỉ dùng registry `status=publish_ready` làm gate nên không làm phát sinh publish sai.

## CI / production gate

Exact code SHA `b9f78bb26b5d639a0a72d2bb99bc07c6fedd26d6` đã kích hoạt cả Validate và Release workflow. Tại thời điểm report được cập nhật, workflow còn queued/in-progress nên chưa được ghi nhận là CI PASS.

Production PASS cần đủ:

1. Final HEAD PASS cả Validate + Release.
2. Deploy Bridge `deployed_sha` khớp final HEAD.
3. Runtime status báo article sync `10/10`, `error_count=0`.
4. `/kien-thuc/` và 10 article URL render đúng H1/direct answer/body/CTA.
5. Media inventory xác minh article Featured Image/ALT và missing/duplicate counts.

## An toàn nội dung

Không thêm:

- cGMP / ISO / FDA không có hồ sơ hiện hành;
- số liệu công suất hoặc năng lực chưa xác minh;
- tên đối tác chưa xác minh;
- contact fact chưa có nguồn;
- claim y tế / hiệu quả chưa được duyệt.

## Trạng thái

**SOURCE CONTENT: 10/10 KNOWLEDGE ARTICLES PUBLISH-READY.**

**ARTICLE TEMPLATE: DIRECT-ANSWER HERO IMPROVED.**

**PRODUCTION ARTICLE/MEDIA QA: CHƯA XÁC MINH LIVE.**
