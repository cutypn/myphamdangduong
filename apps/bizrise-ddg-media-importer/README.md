# Bizrise DDG Media Importer

Plugin 1-click để import và gắn **các ảnh DDG đã có sẵn** vào content còn thiếu ảnh đại diện/banner.

## Nguyên tắc

- Không ghi đè featured image đã có.
- Không re-encode/recompress ảnh nguồn; file JPG trong package là bản có sẵn từ thư viện dự án.
- Import các asset đã xác định chắc chắn: nhà máy, One Today, Hatagold B5.
- Gắn banner/thumbnail theo slug/tên sản phẩm.
- Sau đó thử ghép ảnh đã có trong Media Library bằng title/ALT với ngưỡng tương đồng cao.
- Hỗ trợ CPT `bizrise_product`, `ddg_product`, `bizrise_brand`, `ddg_brand` và `product` nếu WooCommerce tồn tại.

## Cách chạy

1. Upload ZIP qua **Plugins → Add Plugin → Upload Plugin**.
2. Activate.
3. Vào **Tools → DDG Media Importer**.
4. Bấm **Import & gắn ảnh còn thiếu**.

Hoặc WP-CLI:

```bash
wp bizrise ddg-media --apply
```

## Mapping chính

- Factory aerial → `nha-may-san-xuat-my-pham`, `nha-may`, `nang-luc-san-xuat`, `manufacturing`, `factory`.
- Factory front → `nang-luc`, `ve-dang-duong`, `gioi-thieu` nếu thiếu ảnh.
- One Today banner → brand/page `one-today`.
- Hatagold B5 banner → brand/page `hatagold`.
- Các ảnh Hatagold B5 square → SKU tương ứng theo Product Master.

## Safety

Plugin chỉ điền chỗ trống. Nếu post/page đã có thumbnail, plugin giữ nguyên ảnh hiện tại.
