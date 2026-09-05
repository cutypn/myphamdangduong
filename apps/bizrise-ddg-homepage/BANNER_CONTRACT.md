# DDG BANNER OVERLAY CONTRACT v1.0

Áp dụng cho toàn bộ banner trên website Đăng Dương Group.

## Quy tắc bắt buộc

1. Banner là **một canvas duy nhất**.
2. Ảnh/banner media luôn là **background full-bleed** của section/banner.
3. H1/H2, Direct Answer, CTA, badge và trust text là **HTML semantic overlay** nằm trên background.
4. Không chia banner thành layout `text column + image column`.
5. Không render ảnh banner như một block nằm dưới hoặc cạnh nội dung.
6. Desktop và mobile có thể dùng hai background asset khác nhau nhưng giữ cùng contract overlay.
7. Background dùng `object-fit: cover` / `background-size: cover` và `object-position` theo safe-area của model/sản phẩm.
8. Phải có lớp gradient/scrim để giữ contrast chữ mà không che chủ thể.
9. LCP banner không lazy-load.
10. Mỗi URL vẫn chỉ có đúng 1 H1.

## Pattern

```html
<section class="banner">
  <picture class="banner__background">...</picture>
  <div class="banner__scrim" aria-hidden="true"></div>
  <div class="banner__content">
    <h1>...</h1>
    <p>...</p>
    <a>CTA</a>
  </div>
</section>
```

## Mobile

Mobile banner không phải desktop bị co lại. Dùng asset 9:16 hoặc portrait-safe, background full section và content overlay trong safe area.

**Không được quay lại pattern ảnh cạnh chữ.**
