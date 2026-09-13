# PRODUCT PAGE COPY — PUBLISH_ALLOWED 2026

**Scope:** Product-detail content deck cho toàn bộ SKU đang `active + VERIFIED_* + PUBLISH_ALLOWED` trong Product Truth snapshot hiện hành.  
**Rule:** Tên, brand, category, size dùng theo Product Truth. Không tự thêm ingredient, công dụng chi tiết, tần suất, thời gian có kết quả, claim điều trị hoặc compatibility nếu chưa có Approved Claim Library/nhãn được duyệt.

---

# 1. PRODUCT PAGE MASTER STRUCTURE

Mỗi product page triển khai đúng thứ tự:

1. **H1:** Canonical Product Name.
2. **Direct Answer:** sản phẩm là gì + brand + category + size + vai trò routine ở mức an toàn.
3. **Beauty Situation:** người dùng đang tìm một bước nào trong routine.
4. **Product Role:** vai trò theo category, không suy diễn claim.
5. **Verified Identity:** Brand / Category / Size / Product Truth status.
6. **Approved Benefits:** chỉ render khi có claim library được duyệt; nếu chưa có thì bỏ section, không để placeholder.
7. **Ingredient Story:** chỉ render khi có ingredient source được duyệt.
8. **How to Use:** “Sử dụng theo hướng dẫn trên nhãn/bao bì hiện hành của sản phẩm.” Nếu có dữ liệu nhãn chi tiết thì thay bằng dữ liệu đã duyệt.
9. **Routine Position:** dùng module category bên dưới.
10. **Warnings:** “Ngưng sử dụng nếu xuất hiện phản ứng không phù hợp và tham khảo chuyên gia y tế khi cần. Thông tin cảnh báo chi tiết theo nhãn hiện hành.”
11. **FAQ:** identity + routine role + source discipline.
12. **Sources:** Product Truth + hồ sơ xác minh tương ứng.
13. **CTA:** Tìm điểm bán / Xem routine / Xem sản phẩm cùng nhóm.

## SEO pattern

- **SEO Title:** `[Canonical Product Name] | [Brand]`
- **Meta Description:** `Thông tin [Canonical Product Name] [Size] của [Brand]: nhóm [Category], vị trí gợi ý trong routine và dữ liệu sản phẩm theo Product Truth đã xác minh.`
- **Canonical:** product permalink duy nhất.
- **H1:** đúng canonical name, không thêm claim mới.

---

# 2. CATEGORY COPY MODULES

## 2.1 Kem dưỡng/chăm sóc da

**Direct Answer module:**  
`[Product Name]` là sản phẩm thuộc nhóm kem dưỡng/chăm sóc da của `[Brand]`, quy cách `[Size]`, đang có trạng thái Product Truth cho phép hiển thị. Tên và quy cách được dùng theo hồ sơ xác minh; các lợi ích chi tiết, thành phần, cách dùng và cảnh báo chỉ trình bày theo dữ liệu nhãn/claim được duyệt.

**Beauty Situation:**  
Phù hợp để người dùng xem xét khi đang tìm một bước chăm sóc da mặt có mục tiêu trong routine. Tên thương mại có thể chứa các cụm như “mụn”, “nám”, “tàn nhang”, “đồi mồi”, “dấu hiệu lão hóa” hoặc “trắng da”; website dùng các cụm này ở cấp identity và điều hướng, không biến chúng thành lời hứa điều trị hoặc chuẩn ngoại hình.

**Routine Position:**  
Đặt ở bước chăm sóc/dưỡng sau làm sạch. Không mặc định xếp nhiều kem cùng vai trò trong một routine. Sáng/tối và cách phối hợp cụ thể theo hướng dẫn được duyệt cho từng SKU.

## 2.2 Chống nắng

**Direct Answer module:**  
`[Product Name]` là sản phẩm chống nắng của `[Brand]`, quy cách `[Size]`, có identity đã được Product Truth xác minh. Trong website, sản phẩm được đặt ở bước bảo vệ ban ngày; lượng dùng, cách thoa lại và lưu ý cụ thể phải theo nhãn hiện hành.

**Routine Position:**  
Bước cuối của routine chăm sóc da ban ngày trước khi ra ngoài, theo hướng dẫn riêng của sản phẩm.

## 2.3 Sữa rửa mặt

**Direct Answer module:**  
`[Product Name]` là sản phẩm thuộc nhóm sữa rửa mặt của `[Brand]`, quy cách `[Size]`, có Product Truth `PUBLISH_ALLOWED`. Vai trò của sản phẩm trên website là bước làm sạch; các claim khác trong tên không được mở rộng thành hiệu quả điều trị.

**Routine Position:**  
Bước làm sạch trước các sản phẩm chăm sóc khác.

## 2.4 Tẩy tế bào chết

**Direct Answer module:**  
`[Product Name]` là sản phẩm thuộc nhóm tẩy tế bào chết của `[Brand]`, quy cách `[Size]`, đã được xác minh identity trong Product Truth. Website không tự đặt tần suất sử dụng chung cho mọi sản phẩm; tần suất và cách dùng phải theo nhãn hiện hành.

**Routine Position:**  
Bước chăm sóc bổ sung, không mặc định dùng mỗi ngày.

## 2.5 Serum

**Direct Answer module:**  
`[Product Name]` là serum của `[Brand]`, quy cách `[Size]`, có Product Truth `PUBLISH_ALLOWED`. Serum được trình bày như một bước chăm sóc mục tiêu; ingredient, nồng độ và cách phối hợp chỉ công bố khi có dữ liệu được duyệt.

**Routine Position:**  
Sau làm sạch và trước bước kem/dưỡng nếu routine có nhiều bước, tùy hướng dẫn sản phẩm.

## 2.6 Chăm sóc body

**Direct Answer module:**  
`[Product Name]` là sản phẩm chăm sóc body của `[Brand]`, quy cách `[Size]`, đã được Product Truth xác minh. Website tập trung vào vai trò chăm sóc cơ thể và thói quen sử dụng, không dùng màu da như tiêu chuẩn đẹp và không biến từ “trắng/whitening” trong tên sản phẩm thành lời hứa thay đổi ngoại hình.

**Routine Position:**  
Sau bước làm sạch body, sử dụng theo hướng dẫn trên nhãn hiện hành.

## 2.7 Sữa tắm / Body Wash

**Direct Answer module:**  
`[Product Name]` là sản phẩm làm sạch body của `[Brand]`, quy cách `[Size]`, có Product Truth `PUBLISH_ALLOWED`. Tên sản phẩm được giữ theo hồ sơ; website dùng sản phẩm ở bước làm sạch cơ thể.

**Routine Position:**  
Bước làm sạch body trước sản phẩm chăm sóc body.

## 2.8 Tắm trắng

**Direct Answer module:**  
`[Product Name]` là sản phẩm thuộc nhóm chăm sóc body theo category Product Truth, quy cách `[Size]`. Tên thương mại được giữ theo hồ sơ xác minh; website không dùng “trắng” để tạo áp lực ngoại hình hoặc hứa thay đổi màu da.

**Routine Position:**  
Sử dụng theo hướng dẫn trên nhãn hiện hành; không tự đặt tần suất hoặc phối hợp khi chưa có dữ liệu được duyệt.

---

# 3. PUBLISH_ALLOWED PRODUCT INVENTORY — 35 SKU

## One Today

### ID 4 — Kem Kỳ Tế Bào Da Chết - 60g
- Brand: One Today
- Category: Tẩy tế bào chết
- Size: 60g
- H1: Kem Kỳ Tế Bào Da Chết - 60g
- SEO Title: Kem Kỳ Tế Bào Da Chết - 60g | One Today
- Routine role: Bước chăm sóc bổ sung theo hướng dẫn nhãn
- CTA: Xem routine / Tìm điểm bán

### ID 5 — Kem Trắng Da Mặt 3 In 1 - 5g
- Brand: One Today
- Category: Kem dưỡng/chăm sóc da
- Size: 5g
- H1: Kem Trắng Da Mặt 3 In 1 - 5g
- SEO Title: Kem Trắng Da Mặt 3 In 1 - 5g | One Today
- Routine role: Bước chăm sóc/dưỡng
- CTA: Xem sản phẩm cùng nhóm / Tìm điểm bán

### ID 6 — Kem Trắng Da Ngừa Mụn 3 Tác Dụng - 8g
- Brand: One Today
- Category: Kem dưỡng/chăm sóc da
- Size: 8g
- H1: Kem Trắng Da Ngừa Mụn 3 Tác Dụng - 8g
- SEO Title: Kem Trắng Da Ngừa Mụn 3 Tác Dụng - 8g | One Today
- Routine role: Bước chăm sóc/dưỡng; không mô tả như thuốc trị mụn
- CTA: Xem routine chăm sóc da có mụn / Tìm điểm bán

### ID 8 — Kem Trắng Da Mặt Ngọc Trai - 20g
- Brand: One Today
- Category: Kem dưỡng/chăm sóc da
- Size: 20g
- H1: Kem Trắng Da Mặt Ngọc Trai - 20g
- SEO Title: Kem Trắng Da Mặt Ngọc Trai - 20g | One Today
- Routine role: Bước chăm sóc/dưỡng
- CTA: Xem routine / Tìm điểm bán

### ID 9 — Kem Chống Nắng Dưỡng Trắng Da SPF50+ - 15g
- Brand: One Today
- Category: Chống nắng
- Size: 15g
- H1: Kem Chống Nắng Dưỡng Trắng Da SPF50+ - 15g
- SEO Title: Kem Chống Nắng Dưỡng Trắng Da SPF50+ - 15g | One Today
- Routine role: Bước bảo vệ ban ngày
- CTA: Xem routine buổi sáng / Tìm điểm bán

### ID 11 — Kem Trắng Da Ban Ngày - 15g
- Brand: One Today
- Category: Kem dưỡng/chăm sóc da
- Size: 15g
- H1: Kem Trắng Da Ban Ngày - 15g
- SEO Title: Kem Trắng Da Ban Ngày - 15g | One Today
- Routine role: Bước chăm sóc ban ngày theo hướng dẫn nhãn
- CTA: Xem routine buổi sáng / Tìm điểm bán

### ID 12 — Kem Trắng Da Ban Đêm - 15g
- Brand: One Today
- Category: Kem dưỡng/chăm sóc da
- Size: 15g
- H1: Kem Trắng Da Ban Đêm - 15g
- SEO Title: Kem Trắng Da Ban Đêm - 15g | One Today
- Routine role: Bước chăm sóc buổi tối theo hướng dẫn nhãn
- CTA: Xem routine buổi tối / Tìm điểm bán

### ID 13 — Kem Dưỡng Trắng Giúp Mờ Tàn Nhang - Đồi Mồi Cao Cấp - 15g
- Brand: One Today
- Category: Kem dưỡng/chăm sóc da
- Size: 15g
- H1: Kem Dưỡng Trắng Giúp Mờ Tàn Nhang - Đồi Mồi Cao Cấp - 15g
- SEO Title: Kem Dưỡng Trắng Giúp Mờ Tàn Nhang - Đồi Mồi Cao Cấp - 15g | One Today
- Routine role: Bước chăm sóc mục tiêu; không dùng claim “xóa/trị”
- CTA: Xem nhóm chăm sóc độ đều màu / Tìm điểm bán

### ID 14 — Kem Dưỡng Trắng Giúp Mờ Các Dấu Hiệu Lão Hóa Da - 20g
- Brand: One Today
- Category: Kem dưỡng/chăm sóc da
- Size: 20g
- H1: Kem Dưỡng Trắng Giúp Mờ Các Dấu Hiệu Lão Hóa Da - 20g
- SEO Title: Kem Dưỡng Trắng Giúp Mờ Các Dấu Hiệu Lão Hóa Da - 20g | One Today
- Routine role: Bước chăm sóc mục tiêu; không age shaming
- CTA: Xem routine dấu hiệu lão hóa / Tìm điểm bán

### ID 15 — Kem Giúp Mờ Nám Cao Cấp - 15g
- Brand: One Today
- Category: Kem dưỡng/chăm sóc da
- Size: 15g
- H1: Kem Giúp Mờ Nám Cao Cấp - 15g
- SEO Title: Kem Giúp Mờ Nám Cao Cấp - 15g | One Today
- Routine role: Bước chăm sóc mục tiêu; không mô tả điều trị nám
- CTA: Xem nhóm độ đều màu / Tìm điểm bán

### ID 16 — Kem Giúp Mờ Nám - 8g
- Brand: One Today
- Category: Kem dưỡng/chăm sóc da
- Size: 8g
- H1: Kem Giúp Mờ Nám - 8g
- SEO Title: Kem Giúp Mờ Nám - 8g | One Today
- Routine role: Bước chăm sóc mục tiêu
- CTA: Xem nhóm độ đều màu / Tìm điểm bán

### ID 17 — Sữa Rửa Mặt Sáng Da Ngừa Mụn - 50g
- Brand: One Today
- Category: Sữa rửa mặt
- Size: 50g
- H1: Sữa Rửa Mặt Sáng Da Ngừa Mụn - 50g
- SEO Title: Sữa Rửa Mặt Sáng Da Ngừa Mụn - 50g | One Today
- Routine role: Bước làm sạch; không mô tả như sản phẩm trị mụn
- CTA: Xem routine làm sạch / Tìm điểm bán

### ID 19 — Kem Dưỡng Trắng Da Toàn Thân - 80g
- Brand: One Today
- Category: Chăm sóc body
- Size: 80g
- H1: Kem Dưỡng Trắng Da Toàn Thân - 80g
- SEO Title: Kem Dưỡng Trắng Da Toàn Thân - 80g | One Today
- Routine role: Bước chăm sóc body
- CTA: Xem routine body / Tìm điểm bán

### ID 20 — Kem Giúp Mờ Nám - Mụn, Trắng Da 3 Tác Dụng Cao Cấp - 15g
- Brand: One Today
- Category: Kem dưỡng/chăm sóc da
- Size: 15g
- H1: Kem Giúp Mờ Nám - Mụn, Trắng Da 3 Tác Dụng Cao Cấp - 15g
- SEO Title: Kem Giúp Mờ Nám - Mụn, Trắng Da 3 Tác Dụng Cao Cấp - 15g | One Today
- Routine role: Bước chăm sóc mục tiêu; không mở rộng thành claim điều trị
- CTA: Xem routine tối giản / Tìm điểm bán

### ID 21 — Kem Dưỡng Trắng Giúp Mờ Tàn Nhang - Đồi Mồi Cao Cấp - 30g
- Brand: One Today
- Category: Kem dưỡng/chăm sóc da
- Size: 30g
- H1: Kem Dưỡng Trắng Giúp Mờ Tàn Nhang - Đồi Mồi Cao Cấp - 30g
- SEO Title: Kem Dưỡng Trắng Giúp Mờ Tàn Nhang - Đồi Mồi Cao Cấp - 30g | One Today
- Routine role: Bước chăm sóc mục tiêu
- CTA: Xem nhóm độ đều màu / Tìm điểm bán

### ID 94 — Kem Giúp Mờ Nám Mụn Trắng Da - 10g
- Brand: One Today
- Category: Kem dưỡng/chăm sóc da
- Size: 10g
- H1: Kem Giúp Mờ Nám Mụn Trắng Da - 10g
- SEO Title: Kem Giúp Mờ Nám Mụn Trắng Da - 10g | One Today
- Routine role: Bước chăm sóc mục tiêu
- CTA: Xem routine / Tìm điểm bán

### ID 95 — Kem Dưỡng Trắng Da Mặt - 8g
- Brand: One Today
- Category: Kem dưỡng/chăm sóc da
- Size: 8g
- H1: Kem Dưỡng Trắng Da Mặt - 8g
- SEO Title: Kem Dưỡng Trắng Da Mặt - 8g | One Today
- Routine role: Bước chăm sóc/dưỡng
- CTA: Xem sản phẩm cùng nhóm / Tìm điểm bán

### ID 96 — Kem Dưỡng Trắng Giúp Mờ Các Dấu Hiệu Lão Hóa Da - 8g
- Brand: One Today
- Category: Kem dưỡng/chăm sóc da
- Size: 8g
- H1: Kem Dưỡng Trắng Giúp Mờ Các Dấu Hiệu Lão Hóa Da - 8g
- SEO Title: Kem Dưỡng Trắng Giúp Mờ Các Dấu Hiệu Lão Hóa Da - 8g | One Today
- Routine role: Bước chăm sóc mục tiêu
- CTA: Xem routine dấu hiệu lão hóa / Tìm điểm bán

### ID 97 — Kem Dưỡng Trắng Giúp Mờ Tàn Nhang - Đồi Mồi - 8g
- Brand: One Today
- Category: Kem dưỡng/chăm sóc da
- Size: 8g
- H1: Kem Dưỡng Trắng Giúp Mờ Tàn Nhang - Đồi Mồi - 8g
- SEO Title: Kem Dưỡng Trắng Giúp Mờ Tàn Nhang - Đồi Mồi - 8g | One Today
- Routine role: Bước chăm sóc mục tiêu
- CTA: Xem nhóm độ đều màu / Tìm điểm bán

### ID 98 — Kem Trắng Da Chống Nắng SPF50+ - 8g
- Brand: One Today
- Category: Chống nắng
- Size: 8g
- H1: Kem Trắng Da Chống Nắng SPF50+ - 8g
- SEO Title: Kem Trắng Da Chống Nắng SPF50+ - 8g | One Today
- Routine role: Bước bảo vệ ban ngày
- CTA: Routine buổi sáng / Tìm điểm bán

### ID 99 — Kem Chống Nắng Dưỡng Trắng Da SPF50+ - 50g
- Brand: One Today
- Category: Chống nắng
- Size: 50g
- H1: Kem Chống Nắng Dưỡng Trắng Da SPF50+ - 50g
- SEO Title: Kem Chống Nắng Dưỡng Trắng Da SPF50+ - 50g | One Today
- Routine role: Bước bảo vệ ban ngày
- CTA: Routine buổi sáng / Tìm điểm bán

### ID 100 — Smoothing and Moisturizing Body Wash - 1000g
- Brand: One Today
- Category: Chăm sóc body / Body Wash
- Size: 1000g
- H1: Smoothing and Moisturizing Body Wash - 1000g
- SEO Title: Smoothing and Moisturizing Body Wash - 1000g | One Today
- Routine role: Bước làm sạch body
- CTA: Routine body / Tìm điểm bán

### ID 101 — Kem Tắm Trắng Cao Cấp Ngọc Trai - 120g
- Brand: One Today
- Category: Tắm trắng
- Size: 120g
- H1: Kem Tắm Trắng Cao Cấp Ngọc Trai - 120g
- SEO Title: Kem Tắm Trắng Cao Cấp Ngọc Trai - 120g | One Today
- Routine role: Chăm sóc body theo hướng dẫn nhãn; không dùng màu da làm chuẩn đẹp
- CTA: Xem chăm sóc body / Tìm điểm bán

### ID 102 — Kem Dưỡng Trắng Giúp Mờ Các Dấu Hiệu Lão Hóa Da Giúp Mờ Nếp Nhăn Da - 30g
- Brand: One Today
- Category: Kem dưỡng/chăm sóc da
- Size: 30g
- H1: Kem Dưỡng Trắng Giúp Mờ Các Dấu Hiệu Lão Hóa Da Giúp Mờ Nếp Nhăn Da - 30g
- SEO Title: Kem Dưỡng Trắng Giúp Mờ Các Dấu Hiệu Lão Hóa Da - 30g | One Today
- Routine role: Bước chăm sóc mục tiêu; không hứa xóa nếp nhăn
- CTA: Xem routine dấu hiệu lão hóa / Tìm điểm bán

### ID 103 — Kem Trắng Da Mặt Đa Chức Năng - 30g
- Brand: One Today
- Category: Kem dưỡng/chăm sóc da
- Size: 30g
- H1: Kem Trắng Da Mặt Đa Chức Năng - 30g
- SEO Title: Kem Trắng Da Mặt Đa Chức Năng - 30g | One Today
- Routine role: Bước chăm sóc/dưỡng
- CTA: Xem routine / Tìm điểm bán

## Hatagold

### ID 75 — Kem Dưỡng Trắng Da Toàn Thân B5 - 100g
- Brand: Hatagold
- Category: Chăm sóc body
- Size: 100g
- H1: Kem Dưỡng Trắng Da Toàn Thân B5 - 100g
- SEO Title: Kem Dưỡng Trắng Da Toàn Thân B5 - 100g | Hatagold
- Routine role: Bước chăm sóc body
- CTA: Xem routine body / Tìm điểm bán

### ID 76 — Kem Dưỡng Trắng Giúp Mờ Các Dấu Hiệu Lão Hóa Da B5 - 10g
- Brand: Hatagold
- Category: Kem dưỡng/chăm sóc da
- Size: 10g
- H1: Kem Dưỡng Trắng Giúp Mờ Các Dấu Hiệu Lão Hóa Da B5 - 10g
- SEO Title: Kem Dưỡng Trắng Giúp Mờ Các Dấu Hiệu Lão Hóa Da B5 - 10g | Hatagold
- Routine role: Bước chăm sóc mục tiêu; B5 chỉ dùng như phần identity khi chưa có claim library
- CTA: Routine dấu hiệu lão hóa / Tìm điểm bán

### ID 77 — Kem Dưỡng Trắng Da Chống Nắng SPF50+ B5 - 10g
- Brand: Hatagold
- Category: Chống nắng
- Size: 10g
- H1: Kem Dưỡng Trắng Da Chống Nắng SPF50+ B5 - 10g
- SEO Title: Kem Dưỡng Trắng Da Chống Nắng SPF50+ B5 - 10g | Hatagold
- Routine role: Bước bảo vệ ban ngày
- CTA: Routine buổi sáng / Tìm điểm bán

### ID 78 — Kem Trắng Da Mặt Đa Chức Năng B5 - 10g
- Brand: Hatagold
- Category: Kem dưỡng/chăm sóc da
- Size: 10g
- H1: Kem Trắng Da Mặt Đa Chức Năng B5 - 10g
- SEO Title: Kem Trắng Da Mặt Đa Chức Năng B5 - 10g | Hatagold
- Routine role: Bước chăm sóc/dưỡng
- CTA: Xem routine / Tìm điểm bán

### ID 79 — Kem Dưỡng Trắng Giúp Mờ Tàn Nhang - Đồi Mồi B5 - 10g
- Brand: Hatagold
- Category: Kem dưỡng/chăm sóc da
- Size: 10g
- H1: Kem Dưỡng Trắng Giúp Mờ Tàn Nhang - Đồi Mồi B5 - 10g
- SEO Title: Kem Dưỡng Trắng Giúp Mờ Tàn Nhang - Đồi Mồi B5 - 10g | Hatagold
- Routine role: Bước chăm sóc mục tiêu
- CTA: Nhóm độ đều màu / Tìm điểm bán

### ID 83 — Serum Giúp Mờ Nám Ngừa Mụn Trắng Da B5 - 20g
- Brand: Hatagold
- Category: Serum
- Size: 20g
- H1: Serum Giúp Mờ Nám Ngừa Mụn Trắng Da B5 - 20g
- SEO Title: Serum Giúp Mờ Nám Ngừa Mụn Trắng Da B5 - 20g | Hatagold
- Routine role: Bước serum/chăm sóc mục tiêu; không mô tả điều trị
- CTA: Xem routine / Tìm điểm bán

### ID 89 — Kem Chống Nắng Dưỡng Trắng Da SPF50+ B5 - 50g
- Brand: Hatagold
- Category: Chống nắng
- Size: 50g
- H1: Kem Chống Nắng Dưỡng Trắng Da SPF50+ B5 - 50g
- SEO Title: Kem Chống Nắng Dưỡng Trắng Da SPF50+ B5 - 50g | Hatagold
- Routine role: Bước bảo vệ ban ngày
- CTA: Routine buổi sáng / Tìm điểm bán

### ID 92 — Kem Dưỡng Trắng Da Toàn Thân - 120g
- Brand: Hatagold
- Category: Chăm sóc body
- Size: 120g
- H1: Kem Dưỡng Trắng Da Toàn Thân - 120g
- SEO Title: Kem Dưỡng Trắng Da Toàn Thân - 120g | Hatagold
- Routine role: Bước chăm sóc body
- CTA: Routine body / Tìm điểm bán

### ID 104 — Kem Giúp Mờ Nám - 18g
- Brand: Hatagold
- Category: Kem dưỡng/chăm sóc da
- Size: 18g
- H1: Kem Giúp Mờ Nám - 18g
- SEO Title: Kem Giúp Mờ Nám - 18g | Hatagold
- Routine role: Bước chăm sóc mục tiêu; không mô tả điều trị nám
- CTA: Nhóm độ đều màu / Tìm điểm bán

## She One

### ID 93 — Premium Whitening Body Cream - 140g
- Brand: She One
- Category: Chăm sóc body
- Size: 140g
- H1: Premium Whitening Body Cream - 140g
- SEO Title: Premium Whitening Body Cream - 140g | She One
- Routine role: Bước chăm sóc body; không dùng màu da làm tiêu chuẩn đẹp
- CTA: Routine body / Tìm điểm bán

---

# 4. STANDARD FAQ FOR PRODUCT DETAIL

## Sản phẩm này thuộc bước nào trong routine?

Vị trí routine được xác định theo category của sản phẩm. Kem dưỡng/chăm sóc da nằm ở bước chăm sóc; serum là bước chăm sóc mục tiêu; sữa rửa mặt là bước làm sạch; chống nắng là bước bảo vệ ban ngày; sản phẩm body được đặt trong routine cơ thể. Hướng dẫn chi tiết phải theo nhãn từng SKU.

## Tên sản phẩm có phải là cam kết hiệu quả không?

Không. Website giữ canonical product name theo hồ sơ xác minh, nhưng body copy không tự nâng mọi cụm từ trong tên thành claim điều trị hoặc cam kết kết quả.

## Có thể dùng nhiều sản phẩm cùng nhóm trong một routine không?

Không mặc định. Nếu các sản phẩm có vai trò trùng nhau, việc dùng nhiều SKU không tự động tạo thêm giá trị. Chỉ gợi ý pairing khi có vai trò khác nhau và thông tin sử dụng phù hợp.

## Dùng sản phẩm sáng hay tối?

Chỉ nêu sáng/tối khi category hoặc dữ liệu sản phẩm cho phép xác định an toàn. Với SKU chưa có hướng dẫn chi tiết được duyệt, website hướng người dùng theo nhãn hiện hành.

## Tôi có thể mua ở đâu?

CTA dẫn tới `/tim-diem-ban/` hoặc kênh bán hàng đã được doanh nghiệp xác minh. Không hard-code marketplace hoặc đại lý chưa có owner/status rõ.

---

# 5. CONTENT STATUS

**35/35 SKU PUBLISH_ALLOWED** đã có identity copy, SEO naming rule, routine role và CTA an toàn để triển khai product page mà không cần bịa ingredient/claim. Khi Approved Claim Library được bổ sung, các section Benefits / Ingredient Story / How to Use chi tiết có thể mở rộng mà không thay đổi cấu trúc URL hay keyword owner.