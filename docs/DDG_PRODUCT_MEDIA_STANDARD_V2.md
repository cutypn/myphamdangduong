# DDG PRODUCT MEDIA STANDARD V2.0

## 1. Mục tiêu
Chuẩn hình ảnh sản phẩm cho toàn bộ Đăng Dương Group / Brand Network. Ảnh sản phẩm phải nhìn như một product packshot premium: sạch, có chiều sâu, rõ bao bì, đúng màu, đúng logo/nhãn và không lẫn tài liệu pháp lý.

## 2. Phân loại asset bắt buộc

### PRODUCT_PACKSHOT
- Dùng làm Featured Image / card / gallery chính.
- Canvas chuẩn: 1500 x 1500 px, 1:1.
- Sản phẩm chiếm khoảng 62-78% chiều cao khung tùy dáng bao bì.
- Safe zone quanh sản phẩm tối thiểu 8%.
- Không stretch, không bóp méo, không tự sửa chữ/nhãn/logo.
- Được phép làm sạch nền, cân sáng, tạo bóng đổ mềm, nền gradient/pedestal tinh tế để tạo cảm giác studio 3D cao cấp.
- Background phải phối theo brand, không để viền sản phẩm bị hòa vào nền.

### PRODUCT_MOBILE_9X16
- Dùng riêng cho mobile product hero / story.
- Tỷ lệ 9:16.
- Chủ thể vẫn phải đầy đủ, không cắt mất nắp, đáy, logo hoặc label quan trọng.
- Không lấy ảnh 1:1 kéo giãn thành 9:16.

### LEGAL_DOCUMENT
- Phiếu công bố / bảng công bố / hồ sơ pháp lý / evidence.
- KHÔNG dùng làm Featured Image.
- KHÔNG đưa vào gallery ảnh sản phẩm.
- KHÔNG dùng làm hero/banner.
- Hiển thị trong phần **Mô tả sản phẩm > Hồ sơ công bố sản phẩm** để người dùng đối chiếu.
- Nếu tài liệu là ảnh: render ảnh preview rõ nét + link mở file gốc.
- Nếu tài liệu là PDF: render card/link tài liệu.

### PRODUCT_PLUS_DOCUMENT_COMPOSITE
- Ảnh nguồn có sản phẩm + phiếu công bố cùng một khung.
- Không được đưa thẳng lên frontend.
- Phải tách thành 2 asset độc lập: PRODUCT_PACKSHOT và LEGAL_DOCUMENT.

## 3. Visual direction
- Premium studio product photography.
- Nền sạch, gradient mềm, ánh sáng kiểm soát, bóng đổ tự nhiên, cảm giác 3D nhưng không thay đổi cấu trúc sản phẩm.
- Ưu tiên vật liệu/bao bì thật, không hallucinate logo, chữ, dung tích hoặc chi tiết packaging.
- Không dùng nền trắng phẳng nếu làm mất viền sản phẩm; chọn nền ivory/champagne/rose/gold/mint theo brand.

## 4. Màu nền theo brand
- One Today: warm ivory / champagne / soft red-gold accent.
- She One: blush / rose ivory / champagne.
- Cream X2: mint / aqua / cool ivory.
- Hatagold: warm gold / champagne / burgundy accent.
- Ever Today: sage / botanical ivory / fresh green.
- One Today Gold: deep champagne / warm gold / ivory.

## 5. Output web
- Không dùng PNG cho ảnh web production.
- Primary: WebP, 1500 x 1500, sRGB, quality 80-85.
- Fallback: JPG, 1500 x 1500, sRGB, quality khoảng 80, optimized/progressive nếu encoder hỗ trợ.
- Mobile: WebP/JPG 9:16, sRGB.
- Metadata không cần thiết phải loại bỏ.

## 6. Mapping WordPress
- Featured / desktop: `_ddg_pc_image_id` + `_thumbnail_id`.
- Mobile: `_ddg_mobile_image_id`.
- Gallery: `_product_image_gallery` / `_ddg_gallery_ids` chỉ chứa PRODUCT_PACKSHOT / alternate product views.
- Legal docs: `_ddg_legal_document_ids`.
- Attachment role: `_ddg_media_role` = `PRODUCT_PACKSHOT`, `PRODUCT_MOBILE_9X16`, hoặc `LEGAL_DOCUMENT`.

## 7. QA bắt buộc
- Đúng SKU, đúng thương hiệu, đúng label.
- Không lẫn phiếu công bố trong ảnh đại diện/gallery.
- 1:1 không méo hình.
- 9:16 là art direction riêng, không stretch 1:1.
- Không halo/viền trắng lỗi tách nền.
- Không cháy highlight, không bệt shadow.
- Không tự bịa claim/bao bì/chi tiết.
- Legal document nằm trong mô tả sản phẩm, có link mở bản gốc.

## 8. Export production
Ảnh production cuối phải qua Photoshop **Export for Web / Export As** theo workflow dự án trước khi upload Media Library.
