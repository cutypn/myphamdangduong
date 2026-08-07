# Mỹ phẩm Đăng Dương — Bizrise Framework

Repository mã nguồn cho website Đăng Dương Group.

## Cấu trúc

- `apps/ddg-beauty-premium/` — WordPress theme Beauty Premium, hiện tại v0.3.1 hotfix.
- `apps/bizrise-core/` — Bizrise Core: Product CPT, taxonomy, metadata và DDG starter importer.
- `docs/` — hướng dẫn cài đặt/import.

## Nguyên tắc hiện tại

- WordPress Multisite ready.
- Be Vietnam Pro toàn site.
- Mỗi URL indexable chỉ 01 H1.
- Product dùng CPT `bizrise_product`, chưa phụ thuộc WooCommerce.
- Mobile media ưu tiên 9:16; desktop 16:9 / 1:1 / 3:4.
- Claim, chứng nhận và dữ liệu doanh nghiệp chỉ publish khi đã xác minh.

> Lưu ý: binary assets (PNG/screenshot/ZIP) được giữ ngoài source commit khi connector GitHub chỉ hỗ trợ file UTF-8. Logo production nên cấu hình bằng WordPress Custom Logo / Media Library.
