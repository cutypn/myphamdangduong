# DDG Frontend Recovery — latest

## Kết luận

Frontend source đã được harden để `/san-pham/` luôn đọc catalog công khai từ WooCommerce `post_type=product`, kể cả khi WooCommerce Shop Page option bị unset/stale và route rơi về một WordPress Page bình thường.

Root cause P0 chính đã được Product/Data Recovery xử lý ở Core: internal `bizrise_product` từng chiếm rewrite `/san-pham/`; CPT này hiện đã non-public và rewrite cũ được flush một lần. Frontend recovery bổ sung safety net ở theme để tránh một cấu hình shop-page lệch tiếp tục làm `/san-pham/` rơi về `page.php` và hiển thị trang tĩnh/trống.

## Thay đổi frontend

### 1. Catalog fallback riêng cho `/san-pham/`

Commit: `a20bde71d6770e040c6b3c4b3e1850125ba9f6ba`

File mới: `apps/bizrise-ddg-theme/page-product-catalog.php`

- Query duy nhất WooCommerce `post_type=product`.
- Chỉ lấy `post_status=publish`.
- Không query `bizrise_product`.
- Loại `product_visibility=exclude-from-catalog` khi taxonomy/term tồn tại.
- 16 sản phẩm/trang, có pagination.
- Danh mục lấy trực tiếp từ `product_cat` đang có product public; bỏ default Uncategorized.
- Dùng lại `ddg_theme2_card_product()` nên visual system và Featured Image vẫn theo theme hiện tại.
- Chỉ có một H1 trong template.

### 2. Bind template theo WordPress page hierarchy

Commit: `dfc65e5b04cc7578e98edd45ff879738507675c6`

File mới: `apps/bizrise-ddg-theme/page-san-pham.php`

WordPress sẽ ưu tiên `page-san-pham.php` khi `/san-pham/` resolve thành Page bình thường. File này chuyển sang catalog fallback ở trên. Nếu `/san-pham/` resolve đúng WooCommerce Shop Archive, `template_include` hiện hữu vẫn dùng `woocommerce/archive-product.php` như trước.

Như vậy có hai đường frontend đều đọc WooCommerce public catalog:

1. WooCommerce shop/archive đúng cấu hình → `woocommerce/archive-product.php`.
2. `/san-pham/` bị resolve thành Page → `page-san-pham.php` → `page-product-catalog.php`.

Không đường nào dùng Product Truth CPT làm storefront.

## Audit template hiện hữu

- `functions.php`: đã bật `add_theme_support('woocommerce')` và route product single/archive sang template Theme 2.
- `woocommerce/archive-product.php`: dùng main WooCommerce query, `product_cat`, `ddg_theme2_card_product()`.
- `woocommerce/single-product.php`: dùng WooCommerce product post, một H1, CTA tới `/tim-diem-ban/` và `/lien-he/`, related query là `post_type=product`, `post_status=publish`.
- `theme212.css`: product card media giữ `aspect-ratio:9/16`; ảnh dùng `object-fit:contain`, centered, không crop source.
- Header/logo CSS đã có breakpoint >=1180 / 980 / 720 / 520; không có thay đổi frontend override/plugin mới trong lượt này.

## Test / CI

Exact frontend HEAD: `dfc65e5b04cc7578e98edd45ff879738507675c6`.

- Validate Bizrise DDG V2 — run `33038880691`: **SUCCESS**.
- Build Bizrise DDG V2 Release — run `33038880649`: **SUCCESS**.

Các workflow này bao gồm PHP lint toàn bộ Core/Theme/Migrator và source/data validation hiện hành.

## Dependency còn chờ Product/Data / Production

Source frontend đã PASS, nhưng production chỉ được đánh PASS khi Deploy Bridge/runtime xác nhận:

1. deployed SHA >= `dfc65e5b04cc7578e98edd45ff879738507675c6` hoặc descendant đã PASS cả Validate + Release.
2. `/san-pham/` thực tế trả lại product cards từ WooCommerce catalog.
3. `product_cat` archives có sản phẩm.
4. >=8 single product representative render đúng title/brand/pack/image/CTA.
5. Runtime media report exact-clean: không missing/wrong Featured Image public.
6. Desktop >=1180 và mobile 360/390/430 browser QA PASS.

## Status

**FRONTEND SOURCE: PASS**

**EXACT CI: PASS**

**PRODUCTION: awaiting Deploy Bridge + Release QA verification.**
