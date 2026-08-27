# DDG Frontend Recovery — latest

## Kết luận

Frontend source tiếp tục giữ kiến trúc storefront đúng: `/san-pham/` đọc WooCommerce `post_type=product`, không dùng internal Product Truth CPT `bizrise_product` làm catalog public.

Product/Data Recovery đã xác nhận root cause P0 ban đầu là route collision: `bizrise_product` từng dùng rewrite `/san-pham/`; Core hiện đã chuyển CPT này thành non-public/non-queryable và flush stale rewrite một lần. Theme có safety net `page-san-pham.php` → `page-product-catalog.php` để `/san-pham/` vẫn render WooCommerce catalog nếu Shop Page option bị stale/unset.

## Thay đổi frontend hiện hành

### 1. Catalog fallback riêng cho `/san-pham/`

Commit nền: `a20bde71d6770e040c6b3c4b3e1850125ba9f6ba`

File: `apps/bizrise-ddg-theme/page-product-catalog.php`

- Query duy nhất WooCommerce `post_type=product`.
- Chỉ lấy `post_status=publish`.
- Không query `bizrise_product`.
- Loại `product_visibility=exclude-from-catalog` khi taxonomy/term tồn tại.
- 16 sản phẩm/trang, có pagination.
- Danh mục lấy trực tiếp từ `product_cat` đang có product public; bỏ default Uncategorized.
- Dùng `ddg_theme2_card_product()` để giữ visual system và Featured Image hiện hành.
- Chỉ có một H1 trong template.

### 2. Bind template theo WordPress page hierarchy

Commit nền: `dfc65e5b04cc7578e98edd45ff879738507675c6`

File: `apps/bizrise-ddg-theme/page-san-pham.php`

- Nếu `/san-pham/` resolve thành Page bình thường: render `page-product-catalog.php`.
- Nếu resolve đúng WooCommerce Shop Archive: `template_include` dùng `woocommerce/archive-product.php`.
- Cả hai đường đều đọc WooCommerce public catalog.

### 3. Harden product brand resolver

Commit: `a53a8d540e889895ac60e9274d7f4e9175b4cf6f`

File: `apps/bizrise-ddg-theme/functions.php`

Lỗi tìm thấy trong audit vòng này: helper `ddg_theme2_product_brand()` từng dùng `_bizrise_packaging_label` làm fallback thương hiệu. Điều này có thể khiến card, breadcrumb và single product hiển thị quy cách/bao bì như tên brand.

Fix:

- Loại `_bizrise_packaging_label` khỏi brand fallback.
- Chỉ đọc các meta key mang nghĩa brand: `_bizrise_brand_label`, `brand`, `_brand`, `brand_name`, `_brand_name`, `product_brand`, `_product_brand`, `ddg_brand`, `_ddg_brand`.
- Bổ sung taxonomy `brand` vào danh sách taxonomy hợp lệ ngoài `product_brand`, `pwb-brand`, `yith_product_brand`, `bizrise_brand`.
- Bump theme version `2.1.3` → `2.1.4` để asset/runtime cache theo release mới.

Không thay Product Truth, publish rules, taxonomy assignment hoặc catalog data.

## Audit template hiện hữu

- `functions.php`: WooCommerce support + route product single/archive sang Theme 2.
- `woocommerce/archive-product.php`: dùng main WooCommerce query, `product_cat`, `ddg_theme2_card_product()`.
- `woocommerce/single-product.php`: dùng WooCommerce product post, một H1, CTA tới `/tim-diem-ban/` và `/lien-he/`, related query là `post_type=product`, `post_status=publish`.
- `theme212.css`: product card media giữ `aspect-ratio:9/16`; ảnh dùng `object-fit:contain`, centered.
- Header/logo CSS vẫn có breakpoint desktop/mobile hiện hành; vòng này không thêm UI override/plugin legacy.

## Test / CI

Exact frontend code SHA: `a53a8d540e889895ac60e9274d7f4e9175b4cf6f`.

- Validate Bizrise DDG V2 — run `33039028923`: **SUCCESS**.
- Build Bizrise DDG V2 Release — run `33039028932`: **SUCCESS**.

Hai workflow gồm PHP lint Core/Theme/Migrator và source/data validation hiện hành.

## Production blocker / verification

Production chỉ được đánh PASS khi Deploy Bridge/runtime xác nhận:

1. deployed SHA là `a53a8d540e889895ac60e9274d7f4e9175b4cf6f` hoặc descendant đã PASS cả Validate + Release.
2. `/san-pham/` thực tế trả WooCommerce product cards.
3. `product_cat` archives có sản phẩm.
4. >=8 single product representative render đúng title/brand/pack/image/CTA.
5. Runtime media report exact-clean, không missing/wrong Featured Image public.
6. Desktop >=1180 và mobile 360/390/430 browser QA PASS.

Môi trường fetch hiện tại vẫn không đọc được production ổn định (`Cache miss`), nên không suy diễn live PASS.

## Status

**FRONTEND SOURCE: PASS**

**EXACT CI: PASS**

**PRODUCTION: CHƯA XÁC MINH — chờ Deploy Bridge + Release QA/runtime endpoint.**
