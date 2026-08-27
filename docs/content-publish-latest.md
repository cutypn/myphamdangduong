# DDG content publish report — latest

## Kết luận

Vòng 2026-08-27 đã tháo blocker editorial rõ nhất cho knowledge content: 10/10 bài trong `data/content/article-registry.json` đã có full rewrite và hiện được đánh dấu `publish_ready` thay vì `editorial_review`.

Core-page source trong `apps/bizrise-ddg-theme/inc/editorial-content.php` hiện có curated source-safe copy cho các trang chính. Nội dung cố ý không công bố cGMP/ISO/FDA/công suất/đối tác hoặc claim y tế khi chưa có hồ sơ xác minh.

## Knowledge articles

- total: 10
- publish_ready: 10
- editorial_review: 0
- blocked_by_missing_copy: 0
- runtime_live_verified: CHƯA XÁC MINH

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

Các slug được Theme 2 quản lý bằng curated editorial content:

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

Homepage được quản lý trong `front-page.php` và cần tiếp tục rà ở vòng sau cùng với production render.

## Thay đổi vòng này

- `data/content/article-registry.json`
  - version → `2026-08-27`
  - policy cập nhật để phân biệt `publish_ready` source với trạng thái live WordPress.
  - 10/10 article `editorial_review` → `publish_ready`.
  - notes cập nhật approval source ngày 2026-08-27.

Commit thay đổi registry: `d291efb0d4fcc22408dd8dad24f0c3eb150008a1`.

## Runtime publication blocker

Trong source đã rà ở vòng này chưa tìm thấy đường deterministic rõ ràng đọc `article-registry.json` + Markdown rồi `wp_insert_post`/sync bài viết vào WordPress. Vì vậy `publish_ready` hiện là trạng thái nguồn, KHÔNG được coi là bằng chứng bài đã live production.

PASS production bắt buộc ở vòng tiếp theo:

1. Xác định hoặc bổ sung importer/sync deterministic cho 10 bài, map theo exact slug; không fuzzy-map.
2. Chỉ publish các file có registry `status=publish_ready`.
3. Preserve exact slug/title/body; update idempotent nếu post đã tồn tại.
4. Kiểm `/kien-thuc/` + 10 article URL live, HTTP/render/H1/internal links/CTA.
5. Đọc deployed SHA từ Deploy Bridge/runtime status và đối chiếu HEAD đã PASS CI.

## An toàn nội dung

Không thêm trong vòng này:

- cGMP / ISO / FDA không có hồ sơ hiện hành;
- số liệu công suất hoặc năng lực chưa xác minh;
- tên đối tác chưa xác minh;
- contact fact chưa có nguồn;
- claim y tế / hiệu quả chưa được duyệt.

## Trạng thái

**SOURCE CONTENT: 10/10 KNOWLEDGE ARTICLES PUBLISH-READY.**

**PRODUCTION ARTICLES: CHƯA XÁC MINH LIVE — cần runtime importer/sync + production QA.**
