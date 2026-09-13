# Bizrise DDG Product Pages 1.0.0

Plugin dựng lại toàn bộ hệ Product Catalogue + Product Detail cho Đăng Dương Group.

## Kiến trúc

- CPT mới: `bizrise_product`.
- Product Sync / Product Truth hiện có được gọi lại để đổ Product Master vào CPT mới.
- SKU/fact chưa `active + PUBLISH_ALLOWED + verified` không được index.
- Product Archive chỉ render sản phẩm có Featured Image thật; không dùng filler/placeholder như sản phẩm.
- Media từ WooCommerce legacy chỉ được copy khi exact `master_key` hoặc exact title + brand.
- Không copy legacy `post_content` sang Product Truth.
- Template tự có đúng 1 H1; content claim chi tiết chỉ render khi `_bizrise_ddg_claims_verified = 1`.

## URL

- Archive: `/san-pham/`
- Product Detail: `/san-pham/{slug}/`
- AI/HTML contract: `/wp-json/ddg/v1/product-page-contract`

## Rebuild

WordPress Admin → Tools → **DDG Product Pages** → **Rebuild toàn bộ trang sản phẩm**.

WP-CLI nếu có:

```bash
wp bizrise ddg-product-pages --apply
```

## Media

Plugin không re-encode ảnh. Asset production phải là first-party/Media Library đã duyệt. Quy trình dự án yêu cầu ảnh web cuối cùng đi qua Photoshop Export for Web; plugin không tuyên bố hoặc giả lập bước Photoshop.

## Safety

- Không bịa claim/công dụng/thành phần.
- Không publish `hold/recalled/retired/unknown`.
- Không copy content marketplace/social/legacy làm source claim.
- Không xóa WooCommerce data cũ; lớp mới có thể chạy song song trong giai đoạn migration.
