# ROUTINE CONTENT — CONTENT BATCH C

## `/san-pham-routine/` — Routine Hub

### H1
Routine chăm sóc theo nhu cầu và vai trò sản phẩm

### Direct Answer
Routine hub giúp người đọc bắt đầu từ **nhu cầu → mục tiêu → vai trò từng bước**, sau đó mới xem sản phẩm phù hợp về mặt category. Batch này không tự suy ra thành phần, liều dùng, compatibility hoặc hiệu quả của SKU khi Product Truth chưa có nguồn duyệt cho các dữ liệu đó.

### Tìm routine theo nhu cầu
- `/san-pham-routine/lam-sach-va-cham-soc-co-ban/`
- `/san-pham-routine/chong-nang-buoi-sang/`
- `/san-pham-routine/cham-soc-da-khong-deu-mau/`
- `/san-pham-routine/cham-soc-da-co-dau-hieu-lao-hoa/`
- `/san-pham-routine/cham-soc-body-hang-ngay/`
- `/san-pham-routine/lam-sach-va-tay-te-bao-chet/`

### Nguyên tắc
1. Không biến routine thành toa điều trị.
2. Mỗi sản phẩm chỉ được gán vai trò theo category/identity đã xác minh.
3. Cách dùng cụ thể phải theo nhãn/tài liệu đã duyệt.
4. Optional upgrade chỉ là đề xuất cấu trúc routine, không phải compatibility claim.
5. Không dùng ngôn ngữ “trị”, “xóa”, “đánh bay”, “hết sau X ngày”.

---

# `/san-pham-routine/lam-sach-va-cham-soc-co-ban/`

## H1
Routine làm sạch và chăm sóc da cơ bản

### Direct Answer
Đây là routine nền dành cho người muốn giữ chu trình ngắn: **làm sạch → chăm sóc → chống nắng vào ban ngày**. Chi tiết sản phẩm, lượng dùng và tần suất phải theo hướng dẫn đã duyệt của từng SKU.

### Problem
Routine có quá nhiều bước hoặc không rõ sản phẩm nào làm nhiệm vụ gì.

### Goal
Xây một chu trình dễ hiểu, mỗi bước có một vai trò rõ ràng.

### Morning
1. **Step 1 — Làm sạch:** sản phẩm thuộc nhóm sữa rửa mặt. Candidate: One Today ID 17 — *Sữa Rửa Mặt Sáng Da Ngừa Mụn - 50g*. Chỉ dùng tên SKU, không suy rộng claim.
2. **Step 2 — Chăm sóc:** chọn một sản phẩm thuộc nhóm kem dưỡng/chăm sóc da sau khi cách dùng được xác minh.
3. **Step 3 — Chống nắng:** chọn SKU thuộc nhóm chống nắng; candidate: One Today ID 99 hoặc Hatagold ID 89.

### Evening
1. Làm sạch.
2. Bước chăm sóc phù hợp.
3. Không tự thêm treatment/hoạt chất khi chưa có nguồn approved.

### Product role
- Cleanser = làm sạch.
- Care cream/serum = bước chăm sóc; benefit cụ thể TBD.
- Sunscreen category = bước chống nắng ban ngày.

### Optional upgrade
Chỉ thêm serum hoặc bước tẩy tế bào chết khi đã có hướng dẫn sử dụng/compatibility rõ ràng.

### Combo
**Starter combo candidate:** cleanser + care cream + sunscreen.  
Không publish SKU combo cố định cho tới khi QA xác nhận cách dùng từng sản phẩm.

### Notes
Routine càng ngắn càng cần rõ vai trò. Không dùng nhiều SKU chỉ vì cùng một tên nhu cầu.

### FAQ
**Có cần đủ ba bước mỗi lần không?** Cấu trúc trên là content framework; thực tế phải theo nhu cầu và hướng dẫn sản phẩm.  
**Có thể trộn nhiều kem dưỡng cùng lúc không?** Chưa có compatibility source; không khuyến nghị tự xếp lớp.  
**Bao lâu có hiệu quả?** Không có approved timeline; không đưa lời hứa thời gian.

---

# `/san-pham-routine/chong-nang-buoi-sang/`

## H1
Routine chống nắng buổi sáng theo vai trò sản phẩm

### Direct Answer
Mục tiêu của trang này là giúp người đọc nhận biết vị trí của **nhóm sản phẩm chống nắng** trong routine ban ngày. Product Truth hiện có các SKU chống nắng của One Today và Hatagold; batch này không suy rộng mức hiệu quả ngoài canonical name/category.

### Problem
Người dùng có sản phẩm chăm sóc nhưng chưa biết đâu là bước chống nắng trong chu trình buổi sáng.

### Goal
Giữ routine sáng rõ ràng và tách “chăm sóc” với “chống nắng” thành các vai trò khác nhau.

### Morning
1. Làm sạch.
2. Bước chăm sóc nếu cần.
3. **Bước chống nắng:** lựa chọn một SKU thuộc category chống nắng.
   - One Today ID 9 — 15g
   - One Today ID 98 — 8g
   - One Today ID 99 — 50g
   - Hatagold ID 77 — 10g
   - Hatagold ID 89 — 50g

### Evening
Không dùng trang này để chỉ định routine tối. Làm sạch/chăm sóc tối phải theo hướng dẫn của sản phẩm tương ứng.

### Product role
Các SKU trên được phân loại là **Chống nắng** trong Product Truth. Cụm `SPF50+` xuất hiện trong canonical names của một số SKU; không thêm claim thử nghiệm, thời lượng bảo vệ hoặc mức chống nước khi chưa có nguồn duyệt.

### Optional upgrade
Thêm bước serum/chăm sóc chỉ sau khi compatibility và cách dùng được xác minh.

### Combo
**Candidate:** cleanser → care → một sunscreen SKU.  
Không ghép nhiều sunscreen SKU trong cùng một combo mặc định.

### Notes
Không dùng cụm “chống nắng tuyệt đối”, “cả ngày”, “100% UV” hoặc tương tự.

### FAQ
**SKU nào là hero product?** One Today ID 99 và Hatagold ID 89 có keyword owner riêng trong Batch C.  
**Có thể nói chống nước không?** Không, trừ khi có approved source.  
**Có thể nói bảo vệ X giờ không?** Không có approved timeline trong Product Truth.

---

# `/san-pham-routine/cham-soc-da-khong-deu-mau/`

## H1
Routine chăm sóc làn da trông không đều màu và các vùng đốm sạm

### Direct Answer
Trang này tiếp cận nhu cầu **không đều màu/đốm sạm** theo hướng chăm sóc mỹ phẩm, không dùng ngôn ngữ điều trị. Một số canonical product names có các từ “nám”, “tàn nhang”, “đồi mồi” hoặc “trắng da”; các từ đó chỉ được giữ nguyên khi gọi đúng tên SKU, không được biến thành cam kết “trị/xóa”.

### Problem
Người dùng dễ chọn nhiều sản phẩm cùng một lời hứa và tạo routine chồng chéo.

### Goal
Giữ routine có một bước làm sạch, một bước chăm sóc có mục tiêu và một bước chống nắng ban ngày.

### Morning
1. Làm sạch.
2. Bước chăm sóc có mục tiêu — candidate theo tên/category, không phải efficacy approval:
   - One Today ID 21
   - One Today ID 97
   - Hatagold ID 79
   - Hatagold ID 104
3. Chống nắng category.

### Evening
1. Làm sạch.
2. Chọn **một** sản phẩm chăm sóc có mục tiêu.
3. Serum chỉ là optional upgrade; Hatagold ID 83 là serum candidate nhưng benefit/ingredient/how-to vẫn TBD.

### Product role
- Kem dưỡng/chăm sóc da = leave-on care role.
- Serum = optional targeted-care role.
- Sunscreen = daytime sun-care role.

### Optional upgrade
Hatagold ID 83 có thể được đưa vào luồng editorial như serum option sau khi có hướng dẫn sử dụng và compatibility approved.

### Combo
**Starter candidate:** cleanser + one care product + sunscreen.  
**Upgrade candidate:** cleanser + serum + one care product + sunscreen.  
Không đưa cả nhiều sản phẩm cùng nhóm “nám/tàn nhang/đồi mồi” vào một combo mặc định.

### Notes
Không hứa “xóa nám”, “trị tàn nhang”, “đánh bay đồi mồi”, “trắng sau X ngày”.

### FAQ
**Tên SKU có chữ “nám” nghĩa là sản phẩm đã được duyệt claim trị nám?** Không. Product Truth xác minh identity; claim chi tiết vẫn TBD.  
**Có nên dùng nhiều sản phẩm cùng mục tiêu?** Batch này không có compatibility source, vì vậy không khuyến nghị stacking.  
**Khi nào thấy thay đổi?** Không có approved timeline.

---

# `/san-pham-routine/cham-soc-da-co-dau-hieu-lao-hoa/`

## H1
Routine chăm sóc da có các dấu hiệu lão hóa nhìn thấy

### Direct Answer
Routine này dành cho nhu cầu chăm sóc mỹ phẩm khi người dùng chú ý nhiều hơn tới vẻ ngoài của làn da theo thời gian. Không dùng ngôn ngữ “đảo ngược lão hóa”, “xóa nhăn” hoặc cam kết trẻ hóa nếu chưa có claim approved.

### Problem
Người dùng dễ tìm “một sản phẩm giải quyết tất cả” thay vì xây chu trình ổn định.

### Goal
Giữ các bước rõ ràng và chỉ dùng product-name language đúng như Product Truth.

### Morning
1. Làm sạch.
2. Bước chăm sóc — candidate:
   - One Today ID 102 — canonical name có “Giúp Mờ Các Dấu Hiệu Lão Hóa Da Giúp Mờ Nếp Nhăn Da”.
   - One Today ID 14 hoặc 96.
   - Hatagold ID 76.
3. Chống nắng category.

### Evening
1. Làm sạch.
2. Một sản phẩm chăm sóc phù hợp.
3. Optional serum chỉ sau khi có hướng dẫn/compatibility.

### Product role
Kem dưỡng/chăm sóc da = care step. Chưa có approved ingredient story hoặc efficacy hierarchy để xếp SKU nào “mạnh hơn”.

### Optional upgrade
Serum candidate nếu Brand/Product source sau này xác nhận cách dùng phù hợp.

### Combo
Candidate: cleanser + one age-sign care SKU + sunscreen in AM.  
Không ghép nhiều age-sign care creams cùng lúc nếu chưa có compatibility proof.

### Notes
Tên SKU có thể chứa “nếp nhăn/lão hóa”, nhưng copy ngoài tên không được mạnh hơn Product Truth.

### FAQ
**Có thể gọi đây là routine chống lão hóa không?** Có thể dùng như consumer intent, nhưng nội dung phải giữ ở phạm vi chăm sóc mỹ phẩm và không hứa đảo ngược quá trình lão hóa.  
**Có ingredient nào là hero?** TBD.  
**Bao lâu có kết quả?** TBD; không tự tạo timeline.

---

# `/san-pham-routine/cham-soc-body-hang-ngay/`

## H1
Routine chăm sóc body hằng ngày theo vai trò sản phẩm

### Direct Answer
Body routine trong Batch C được xây theo hai vai trò cơ bản: **làm sạch body → chăm sóc body**. Product Truth có SKU body care từ One Today, Hatagold và She One; ingredient, cách dùng và claim chi tiết vẫn cần nguồn duyệt.

### Problem
Body care thường bị viết thành lời hứa “trắng nhanh”, trong khi nguồn hiện có chủ yếu xác minh identity.

### Goal
Biến body care thành routine dễ hiểu mà không cường điệu kết quả thẩm mỹ.

### Morning
1. Làm sạch body — candidate: One Today ID 100 *Smoothing and Moisturizing Body Wash - 1000g*.
2. Body care leave-on — candidate theo category: One Today ID 19, Hatagold ID 75/92, She One ID 93.
3. Các bước bảo vệ vùng da phơi nắng: chỉ bổ sung khi có product/category source phù hợp.

### Evening
1. Làm sạch body.
2. Một bước body care leave-on nếu hướng dẫn sản phẩm cho phép.

### Product role
- Body Wash name → cleansing role.
- Chăm sóc body → body-care role.
- ID 101 thuộc category `Tắm trắng`; cách dùng và positioning chi tiết TBD, không biến category thành lời hứa đổi màu da.

### Optional upgrade
Không tự thêm scrub/body treatment khi chưa có hướng dẫn approved.

### Combo
**Candidate:** ID 100 body wash + một body-care SKU.  
Không gộp nhiều body creams chỉ để tăng số SKU.

### Notes
Với She One ID 93, giữ nguyên canonical English name `Premium Whitening Body Cream - 140g`; không dịch thành “trắng cấp tốc”.

### FAQ
**Có thể cam kết body trắng sau X ngày không?** Không có approved claim/timeline.  
**Có thể nói B5 là thành phần chính Hatagold không?** Chưa; `B5` có trong tên một số SKU nhưng ingredient source chưa được duyệt.  
**She One có bao nhiêu SKU xác minh?** Snapshot hiện tại có một SKU body care.

---

# `/san-pham-routine/lam-sach-va-tay-te-bao-chet/`

## H1
Routine làm sạch và tẩy tế bào chết có kiểm soát

### Direct Answer
Trang này giải thích vị trí của category **tẩy tế bào chết** trong routine mà không tự đặt tần suất. Product Truth hiện có One Today ID 4 thuộc nhóm này.

### Problem
Người dùng có thể coi tẩy tế bào chết là bước bắt buộc hằng ngày hoặc phối hợp quá nhiều bước cùng lúc.

### Goal
Xem tẩy tế bào chết như một bước optional, chỉ dùng theo hướng dẫn đã duyệt của sản phẩm.

### Morning
Routine sáng ưu tiên làm sạch/chăm sóc/chống nắng. Không mặc định đưa tẩy tế bào chết vào buổi sáng.

### Evening
1. Làm sạch.
2. **Optional:** One Today ID 4 — *Kem Kỳ Tế Bào Da Chết - 60g* theo đúng hướng dẫn khi được cung cấp.
3. Bước chăm sóc đơn giản, nếu compatibility được xác nhận.

### Product role
ID 4 được Product Truth phân loại `Tẩy tế bào chết`. Tần suất, thời gian lưu trên da và cách rửa: TBD.

### Optional upgrade
Không có. Bản thân tẩy tế bào chết đã là optional step.

### Combo
Candidate: cleanser + ID 4 + simple care product.  
Chỉ publish sau khi hướng dẫn sử dụng ID 4 được duyệt.

### Notes
Không tự khuyên dùng hằng ngày/2–3 lần mỗi tuần khi chưa có label source.

### FAQ
**Bao nhiêu lần mỗi tuần?** TBD theo hướng dẫn sản phẩm.  
**Có thể dùng cùng serum/kem khác không?** Compatibility chưa xác minh.  
**Có cần dùng nếu routine đang ổn?** Đây là optional step, không phải yêu cầu bắt buộc.
