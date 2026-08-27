# DDG Frontend Recovery — latest

## Kết luận

Frontend source tiếp tục giữ kiến trúc storefront đúng: `/san-pham/` đọc WooCommerce `post_type=product`, không dùng internal Product Truth CPT `bizrise_product` làm catalog public.

Product/Data Recovery mới nhất xác nhận root cause P0 ban đầu là route collision và controlled 44-SKU media hiện exact-clean theo evidence hiện có. Các sản phẩm public ngoài manifest/legacy có media gap được tách riêng khỏi controlled clean gate; frontend không tự ẩn, draft hay fuzzy-map các record này.

## Fix vòng hiện tại

Commit code: `bced4003fdf09aa8ff9f7bd5daaee349857ecb93`

File: `apps/bizrise-ddg-theme/header.php`

### Align fallback navigation với sitemap 8 nhánh đã chốt

Fallback header trước đây chỉ có 6 mục và thiếu `Trang chủ` + `Liên hệ`. Khi WordPress chưa có menu `primary` được gán hoặc menu bị mất binding, header vì vậy không phản ánh đầy đủ IA đã duyệt.

Đã sửa fallback thành 8 mục theo thứ tự:

1. Trang chủ
2. Về Đăng Dương
3. Năng lực
4. Thương hiệu
5. Sản phẩm & Routine
6. Kiến thức làm đẹp
7. Đối tác
8. Liên hệ

Không thay hoặc ghi đè menu WordPress do admin quản lý: nếu `primary` menu tồn tại, theme vẫn dùng `wp_nav_menu()` như cũ.

Header file comment được đồng bộ `Theme 2.1.4` với version runtime hiện hành.

## Audit product image rendering

Đã đối chiếu base CSS và final override:

- Base `theme2.css` có rule cũ full-bleed `object-fit:cover`.
- File được enqueue sau cùng `assets/css/theme212.css` override canonical product media bằng selector cụ thể hơn:
  - card media giữ `aspect-ratio:9/16`;
  - image stage dùng `object-fit:contain` + `object-position:center`;
  - hover không scale/crop ảnh;
  - single product main image cũng dùng `object-fit:contain`.

Vì `theme212.css` được enqueue sau `theme2.css`, source cascade hiện tại giữ ảnh sản phẩm nguyên tỷ lệ trong khung 9:16.

## Storefront/template state

Các recovery trước vẫn còn hiệu lực:

- `page-product-catalog.php`: query duy nhất WooCommerce `post_type=product`, `post_status=publish`, bỏ `exclude-from-catalog`, 16 sản phẩm/trang, pagination, `product_cat`, một H1.
- `page-san-pham.php`: safety net cho `/san-pham/` nếu Shop Page option stale/unset.
- `woocommerce/archive-product.php`: dùng main WooCommerce query.
- `woocommerce/single-product.php`: một H1, CTA `/tim-diem-ban/` + `/lien-he/`, related query chỉ `product` + `publish`.
- `functions.php`: brand resolver chỉ đọc taxonomy/meta brand hợp lệ; không còn dùng packaging label làm brand.

Không thay Product Truth, publish rules, taxonomy assignment, product status hoặc media mapping.

## Test / CI exact SHA

Exact frontend code SHA: `bced4003fdf09aa8ff9f7bd5daaee349857ecb93`.

- Validate Bizrise DDG V2 — run `33042023150`: **SUCCESS**.
- Build Bizrise DDG V2 Release — run `33042023139`: **SUCCESS**.

Các workflow hiện hành bao gồm PHP lint và source/data validation của release V2.

## Production blocker / verification

Production chỉ được đánh PASS khi Deploy Bridge/runtime xác nhận một SHA bằng `bced4003fdf09aa8ff9f7bd5daaee349857ecb93` hoặc descendant đã PASS cả Validate + Release, sau đó browser QA xác minh:

1. `/san-pham/` trả WooCommerce product cards.
2. `product_cat` archives có sản phẩm.
3. >=8 single product representative render đúng title/brand/pack/image/CTA.
4. Product cards giữ 9:16 + contain trên desktop >=1180 và mobile 360/390/430.
5. Header desktop/mobile không overflow; fallback đủ 8 nhánh khi không có primary menu.
6. Không duplicate H1 trên core pages/product/article templates.
7. Article grid/single article/CTA không vỡ layout.
8. Runtime controlled product media clean; unmanaged/legacy gaps chỉ được report, không bị frontend che giấu bằng mapping đoán.

Môi trường web fetch hiện tại vẫn không đọc production ổn định, nên không suy diễn live PASS.

## Status

**FRONTEND SOURCE: PASS**

**EXACT CI: PASS**

**PRODUCTION: CHƯA XÁC MINH — chờ Deploy Bridge/runtime + browser QA.**
