# CONTENT BATCH D — KNOWLEDGE & PARTNER — READY QA NORMALIZATION

**Scope:** Content only.  
**Base copy:** `docs/content/CONTENT_BATCH_D_KNOWLEDGE_PARTNER.md`  
**Rule:** Không viết lại body copy đã đạt. File này là lớp chuẩn hóa authoritative cho SEO metadata, CTA, internal links, dedupe, business TBD và B2B Knowledge còn thiếu.  
**Status:** `READY_QA`

---

# 1. GOVERNANCE

## Keep
Giữ body copy hiện có của 12 URL chính nếu không xung đột với metadata/CTA/internal-link map bên dưới.

## Dedupe
- Không lặp nguyên một bảng chính sách thương mại ở nhiều trang. Trên trang public chỉ giữ câu contextual ngắn; danh sách TBD đầy đủ để QA/business owner quản lý tập trung tại Section 6 của file này.
- `/oem-odm-my-pham/` là pillar informational/commercial; `/oem-my-pham-la-gi/` và `/odm-my-pham-la-gi/` là supporting informational; `/hop-tac-oem-odm/` là lead-generation. Không dùng cùng H1/SEO Title/Direct Answer cho ba intent.
- `/doi-tac/` là partner hub; các trang `/he-thong-phan-phoi/`, `/tro-thanh-dai-ly/`, `/affiliate/`, `/hop-tac-oem-odm/` phải đi sâu theo từng loại hợp tác, không lặp phần giới thiệu hub.
- `/tim-diem-ban/` là consumer/navigation intent, không biến thành trang tuyển đại lý.
- Knowledge pages không mở đầu bằng sản phẩm; product/routine chỉ xuất hiện sau phần giải thích và chỉ khi Product Truth cho phép.

## TBD rule
Không có nguồn doanh nghiệp = giữ nguyên token:

`[TBD — BUSINESS CONFIRMATION]`

Không đổi thành số, mức giá, phạm vi, thời gian hoặc lời hứa ước lượng.

---

# 2. SEO / CTA / INTERNAL LINK MATRIX — AUTHORITATIVE

| URL | Primary keyword / owner topic | Search intent | H1 | SEO Title | Meta Description | Canonical | Primary CTA | Required internal links | Status |
|---|---|---|---|---|---|---|---|---|---|
| `/kien-thuc/` | kiến thức chăm sóc da & mỹ phẩm | Informational hub | Kiến thức chăm sóc da và mỹ phẩm dễ hiểu | Kiến thức chăm sóc da & mỹ phẩm | Đăng Dương Group | Hiểu làn da, thành phần mỹ phẩm, routine và cách dùng theo hướng dễ hiểu, ưu tiên giải thích trước khi gợi ý lựa chọn. | `/kien-thuc/` | Hiểu làn da trước khi chọn routine | `/hieu-lan-da/`, `/thanh-phan-my-pham/`, `/routine-cach-dung/`, `/cau-chuyen-san-pham/`, `/oem-my-pham-la-gi/`, `/odm-my-pham-la-gi/` | READY_QA |
| `/hieu-lan-da/` | hiểu làn da | Informational | Hiểu làn da trước khi xây routine chăm sóc | Hiểu làn da trước khi xây routine | Đăng Dương Group | Bắt đầu từ quan sát cảm giác da, thói quen và mục tiêu chăm sóc để xây routine gọn, dễ theo dõi và tránh thêm quá nhiều bước cùng lúc. | `/hieu-lan-da/` | Xây routine theo vai trò từng bước | `/routine-cach-dung/`, `/thanh-phan-my-pham/`, `/san-pham-routine/` | READY_QA |
| `/thanh-phan-my-pham/` | thành phần mỹ phẩm | Informational | Thành phần mỹ phẩm: hiểu vai trò trước khi tin vào lời hứa | Thành phần mỹ phẩm: cách đọc đúng vai trò | Đăng Dương Group | Hiểu ingredient trong bối cảnh công thức, cách dùng và claim đã duyệt; không suy diễn hiệu quả của cả sản phẩm từ một thành phần đơn lẻ. | `/thanh-phan-my-pham/` | Hiểu thứ tự dùng trong routine | `/hieu-lan-da/`, `/routine-cach-dung/`, `/cau-chuyen-san-pham/` | READY_QA |
| `/routine-cach-dung/` | routine chăm sóc da / cách dùng mỹ phẩm | Informational | Routine & cách dùng: sắp xếp sản phẩm theo vai trò | Routine chăm sóc da & cách dùng mỹ phẩm | Đăng Dương Group | Cách tổ chức routine theo vai trò từng bước, thời điểm sử dụng và hướng dẫn của từng sản phẩm, ưu tiên sự đơn giản và dễ duy trì. | `/routine-cach-dung/` | Bắt đầu với Starter Routine | `/hieu-lan-da/`, `/thanh-phan-my-pham/`, `/starter-routine/`, `/routine-buoi-sang/`, `/routine-buoi-toi/` | READY_QA |
| `/cau-chuyen-san-pham/` | câu chuyện sản phẩm mỹ phẩm | Informational/editorial | Câu chuyện sản phẩm: từ nhu cầu thật đến vai trò trong routine | Câu chuyện sản phẩm mỹ phẩm | Đăng Dương Group | Một product story hữu ích bắt đầu từ nhu cầu, tiêu chí lựa chọn và vai trò trong routine trước khi nói tới benefit, ingredient và cách dùng. | `/cau-chuyen-san-pham/` | Xem Sản phẩm & Routine | `/hieu-lan-da/`, `/thanh-phan-my-pham/`, `/routine-cach-dung/`, `/san-pham-routine/` | READY_QA |
| `/oem-my-pham-la-gi/` | OEM mỹ phẩm là gì | Informational supporting | OEM mỹ phẩm là gì? Hiểu mô hình trước khi gửi brief | OEM mỹ phẩm là gì? | Đăng Dương Group | Tìm hiểu OEM mỹ phẩm, khi nào mô hình này phù hợp và những thông tin doanh nghiệp nên chuẩn bị trước khi trao đổi với đối tác. | `/oem-my-pham-la-gi/` | Xem tổng quan OEM/ODM | `/oem-odm-my-pham/`, `/odm-my-pham-la-gi/`, `/quy-trinh-gia-cong-my-pham/`, `/hop-tac-oem-odm/` | READY_QA |
| `/odm-my-pham-la-gi/` | ODM mỹ phẩm là gì | Informational supporting | ODM mỹ phẩm là gì? Hiểu phạm vi hỗ trợ trước khi hợp tác | ODM mỹ phẩm là gì? | Đăng Dương Group | Tìm hiểu ODM mỹ phẩm, mức độ hỗ trợ thường gặp và những câu hỏi cần làm rõ để chọn mô hình hợp tác phù hợp với dự án. | `/odm-my-pham-la-gi/` | Xem tổng quan OEM/ODM | `/oem-odm-my-pham/`, `/oem-my-pham-la-gi/`, `/quy-trinh-gia-cong-my-pham/`, `/hop-tac-oem-odm/` | READY_QA |
| `/doi-tac/` | hợp tác Đăng Dương Group | Partner hub / lead routing | Hợp tác cùng Đăng Dương Group | Đối tác & hợp tác cùng Đăng Dương Group | Kết nối theo đúng nhu cầu: phân phối, đại lý, affiliate, OEM/ODM hoặc corporate inquiry; điều kiện thương mại chỉ xác nhận theo chính sách hiện hành. | `/doi-tac/` | Chọn loại yêu cầu và gửi brief | `/he-thong-phan-phoi/`, `/tro-thanh-dai-ly/`, `/affiliate/`, `/hop-tac-oem-odm/`, `/lien-he/` | READY_QA |
| `/he-thong-phan-phoi/` | hệ thống phân phối mỹ phẩm | B2B informational/lead | Hệ thống phân phối: từ Product Truth đến trải nghiệm tại điểm bán | Hệ thống phân phối mỹ phẩm | Đăng Dương Group | Tìm hiểu cách chuẩn hóa thông tin sản phẩm, nội dung tư vấn và dữ liệu điểm bán; quy mô và phạm vi chỉ công bố khi đã xác nhận. | `/he-thong-phan-phoi/` | Tìm hiểu trở thành đại lý | `/tim-diem-ban/`, `/tro-thanh-dai-ly/`, `/doi-tac/`, `/lien-he/` | READY_QA |
| `/tim-diem-ban/` | tìm điểm bán Đăng Dương Group | Navigation / consumer support | Tìm điểm bán và kênh mua được xác nhận | Tìm điểm bán Đăng Dương Group | Tra cứu điểm bán hoặc gửi khu vực và sản phẩm quan tâm để được hướng dẫn theo dữ liệu kênh mua đã được xác nhận. | `/tim-diem-ban/` | Gửi yêu cầu tìm kênh mua | `/lien-he/`, `/he-thong-phan-phoi/` | READY_QA |
| `/tro-thanh-dai-ly/` | trở thành đại lý mỹ phẩm | B2B lead | Trở thành đại lý: bắt đầu từ thông tin kinh doanh rõ ràng | Trở thành đại lý Đăng Dương Group | Gửi thông tin mô hình kinh doanh, khu vực, kênh bán và thương hiệu quan tâm để bắt đầu luồng đánh giá; chính sách thương mại chờ xác nhận. | `/tro-thanh-dai-ly/` | Gửi đăng ký đại lý | `/he-thong-phan-phoi/`, `/doi-tac/`, `/lien-he/` | READY_QA |
| `/affiliate/` | affiliate mỹ phẩm | Creator/affiliate lead | Affiliate: sáng tạo nhiều cách kể, dùng chung một Product Truth | Affiliate mỹ phẩm | Đăng Dương Group | Dành cho creator, publisher và social commerce partner muốn tìm hiểu chương trình; claim và product facts phải theo nguồn chính thức. | `/affiliate/` | Gửi kênh để tìm hiểu Affiliate | `/cau-chuyen-san-pham/`, `/doi-tac/`, `/lien-he/` | READY_QA |
| `/hop-tac-oem-odm/` | hợp tác OEM ODM mỹ phẩm | B2B lead generation | Hợp tác OEM/ODM: chuẩn bị brief trước khi trao đổi dự án | Hợp tác OEM/ODM mỹ phẩm | Đăng Dương Group | Chuẩn bị brief về sản phẩm, khách hàng mục tiêu, trạng thái công thức/bao bì và phạm vi cần hỗ trợ trước khi trao đổi dự án OEM/ODM. | `/hop-tac-oem-odm/` | Gửi brief OEM/ODM | `/oem-odm-my-pham/`, `/oem-my-pham-la-gi/`, `/odm-my-pham-la-gi/`, `/quy-trinh-gia-cong-my-pham/`, `/lien-he/` | READY_QA |
| `/lien-he/` | liên hệ Đăng Dương Group | Navigation / routed lead | Liên hệ Đăng Dương Group theo đúng nhu cầu | Liên hệ Đăng Dương Group | Gửi Corporate, OEM/ODM, Distributor, Dealer, Affiliate hoặc Consumer Support inquiry theo đúng luồng để nội dung được tiếp nhận rõ ràng. | `/lien-he/` | Chọn loại yêu cầu và gửi thông tin | `/doi-tac/`, `/hop-tac-oem-odm/`, `/tro-thanh-dai-ly/`, `/affiliate/`, `/tim-diem-ban/` | READY_QA |

**Schema guidance:** Knowledge hub `CollectionPage`/`WebPage`; knowledge articles `Article` hoặc `WebPage` theo implementation; partner/contact pages `WebPage`, `/lien-he/` dùng `ContactPage`. FAQ schema chỉ dùng nếu policy/search implementation hiện hành cho phép và nội dung FAQ hiển thị thật trên trang.

---

# 3. B2B KNOWLEDGE GAP FILL

## 3.1 `/oem-my-pham-la-gi/`

### Direct Answer
OEM mỹ phẩm là mô hình trong đó một doanh nghiệp làm việc với đối tác sản xuất để hiện thực hóa sản phẩm theo phạm vi đã thống nhất. Mức độ chủ động của brand owner về concept, công thức, bao bì và hồ sơ có thể khác nhau theo từng dự án; vì vậy nên xác định rõ mình đã có gì và cần đối tác hỗ trợ phần nào trước khi gửi brief.

### H2 — OEM phù hợp khi nào?
OEM thường phù hợp hơn khi doanh nghiệp đã có định hướng sản phẩm tương đối rõ và muốn làm việc với một đối tác thực thi theo brief. Tuy nhiên, tên gọi OEM không tự động xác định toàn bộ phạm vi dịch vụ; từng hạng mục cần được xác nhận theo dự án và hồ sơ năng lực hiện hành.

### H2 — Những thông tin nên chuẩn bị trước khi trao đổi
1. Khách hàng mục tiêu và nhu cầu chính.
2. Nhóm sản phẩm dự kiến.
3. Định vị và khoảng giá mong muốn nếu đã có.
4. Trạng thái concept/công thức/bao bì/nhãn.
5. Thị trường dự kiến.
6. Phạm vi muốn đối tác hỗ trợ.
7. Mốc thời gian mong muốn ở mức tham khảo, không xem là cam kết.

### H2 — 4 câu hỏi cần làm rõ với đối tác
- Phạm vi nào thuộc trách nhiệm của brand owner và phạm vi nào thuộc đối tác?
- Dữ liệu kỹ thuật/pháp lý nào cần có trước khi chuyển bước?
- Các mốc review/approval trong dự án được tổ chức thế nào?
- Điều kiện thương mại nào cần xác nhận trước khi khởi động?

### H2 — Đọc tiếp
- `/oem-odm-my-pham/` — pillar so sánh và tổng quan.
- `/odm-my-pham-la-gi/` — hiểu mô hình ODM.
- `/quy-trinh-gia-cong-my-pham/` — xem blueprint quy trình.
- `/hop-tac-oem-odm/` — gửi brief nếu đã sẵn sàng trao đổi.

### FAQ
**OEM có đồng nghĩa đối tác chỉ “sản xuất theo công thức có sẵn” không?**  
Không nên mặc định như vậy. Phạm vi OEM thực tế phụ thuộc brief, mức độ hoàn thiện dữ liệu đầu vào và dịch vụ mà hai bên xác nhận.

**MOQ OEM là bao nhiêu?**  
`[TBD — BUSINESS CONFIRMATION]`

**Đăng Dương Group đang cung cấp chính xác những hạng mục OEM nào?**  
`[TBD — BUSINESS CONFIRMATION]` — chỉ publish sau khi business owner xác nhận phạm vi dịch vụ hiện hành.

### CTA
**CTA chính:** Xem tổng quan OEM/ODM  
**Link:** `/oem-odm-my-pham/`  
**CTA phụ khi đã có brief:** Gửi brief OEM/ODM → `/hop-tac-oem-odm/`

---

## 3.2 `/odm-my-pham-la-gi/`

### Direct Answer
ODM mỹ phẩm là mô hình thường được nhắc tới khi doanh nghiệp cần đối tác tham gia nhiều hơn vào quá trình định hướng hoặc phát triển sản phẩm, thay vì chỉ tiếp nhận một đầu bài đã hoàn chỉnh. Phạm vi ODM có thể khác nhau giữa các đơn vị và dự án, nên cần làm rõ trách nhiệm, dữ liệu đầu vào và điểm phê duyệt trước khi quyết định mô hình hợp tác.

### H2 — ODM phù hợp khi nào?
ODM có thể phù hợp khi brand owner đã có khách hàng mục tiêu và định vị nhưng chưa hoàn thiện một số lớp của sản phẩm. Đây không phải cam kết rằng mọi đối tác ODM đều cung cấp cùng một bộ dịch vụ; phạm vi cụ thể phải được xác nhận theo từng dự án.

### H2 — Checklist trước khi gửi brief ODM
1. Mô tả rõ nhóm khách hàng và nhu cầu.
2. Nêu nhóm sản phẩm hoặc format mong muốn.
3. Chia sẻ benchmark/concept nếu có quyền sử dụng.
4. Xác định các quyết định brand owner muốn giữ quyền phê duyệt.
5. Liệt kê phần đang cần hỗ trợ: concept, phát triển, bao bì hoặc các lớp liên quan.
6. Chuẩn bị câu hỏi về quy trình review, dữ liệu và điều kiện thương mại.

### H2 — Cách phân biệt với OEM ở mức ra quyết định
Thay vì chỉ hỏi “OEM hay ODM tốt hơn?”, hãy hỏi doanh nghiệp đang chủ động đến đâu và cần đối tác tham gia sâu ở bước nào. Câu trả lời này giúp chọn mô hình hợp tác tốt hơn việc dựa vào tên gọi.

### H2 — Đọc tiếp
- `/oem-odm-my-pham/` — pillar so sánh và tổng quan.
- `/oem-my-pham-la-gi/` — hiểu mô hình OEM.
- `/quy-trinh-gia-cong-my-pham/` — xem blueprint quy trình.
- `/hop-tac-oem-odm/` — gửi brief khi cần trao đổi dự án.

### FAQ
**ODM có nghĩa đối tác quyết định toàn bộ sản phẩm không?**  
Không. Quyền quyết định và phê duyệt cần được xác định trong phạm vi dự án; brand owner vẫn phải làm rõ mục tiêu, tiêu chí và trách nhiệm của mình.

**MOQ ODM là bao nhiêu?**  
`[TBD — BUSINESS CONFIRMATION]`

**Đăng Dương Group đang cung cấp chính xác những hạng mục ODM nào?**  
`[TBD — BUSINESS CONFIRMATION]` — chỉ publish sau khi business owner xác nhận phạm vi dịch vụ hiện hành.

### CTA
**CTA chính:** Xem tổng quan OEM/ODM  
**Link:** `/oem-odm-my-pham/`  
**CTA phụ khi đã có brief:** Gửi brief OEM/ODM → `/hop-tac-oem-odm/`

---

# 4. PAGE-SPECIFIC GAP FIXES — DO NOT REWRITE BASE COPY

## `/kien-thuc/`
- Thêm block **B2B Knowledge** sau 4 Beauty Knowledge cards: `OEM mỹ phẩm là gì?`, `ODM mỹ phẩm là gì?`, link về pillar `/oem-odm-my-pham/`.
- Không đưa `/gia-cong-my-pham/` thành một bài knowledge owner mới; keyword commercial này đã có owner URL riêng.

## `/hieu-lan-da/`
- Giữ ngôn ngữ quan sát/beauty situation; không thêm chẩn đoán loại da hay bệnh lý.
- Routine/product section chỉ link tới khung routine, không map SKU nếu chưa qua Product Truth.

## `/thanh-phan-my-pham/`
- Không tạo danh sách “ingredient tốt/xấu” chung chung.
- Khi triển khai bài con theo ingredient, phân biệt rõ `vai trò thường gặp của ingredient` với `claim của SKU cụ thể`.

## `/routine-cach-dung/`
- Khi nói thứ tự sản phẩm, ưu tiên hướng dẫn SKU cụ thể nếu có; không biến quy tắc “lỏng trước, đặc sau” thành luật tuyệt đối.
- Internal links phải có ít nhất `Starter Routine`, `Routine buổi sáng`, `Routine buổi tối` khi các URL này được publish.

## `/cau-chuyen-san-pham/`
- Không dùng review/marketplace/distributor như proof chính.
- Khi có Product Story cụ thể phải link owner Product Page + routine liên quan + supporting knowledge + where-to-buy nếu phù hợp.

## `/doi-tac/`
- Hub chỉ phân loại nhu cầu và hướng người dùng tới trang chuyên biệt.
- Không lặp chi tiết quy trình đại lý/affiliate/OEM từ các trang con.

## `/he-thong-phan-phoi/`
- Không công bố số điểm bán, tỉnh/thành phủ, đối tác hoặc quyền phân phối khi chưa có database được xác nhận.

## `/tim-diem-ban/`
- Khi chưa có store dataset: giữ empty-state hữu ích + Consumer Support CTA; không hiển thị điểm bán suy đoán từ Google/marketplace.

## `/tro-thanh-dai-ly/`
- Không đưa “lợi nhuận”, “thu nhập”, “chiết khấu”, “độc quyền” vào value proposition nếu chưa có policy được duyệt.

## `/affiliate/`
- Không để trang imply chương trình đang mở/đang trả hoa hồng nếu business chưa xác nhận trạng thái chương trình.
- Có thể dùng CTA trung tính: “Gửi kênh để tìm hiểu chương trình”.

## `/hop-tac-oem-odm/`
- Giữ lead-generation intent; không giải thích lại toàn bộ OEM/ODM.
- Direct Answer chỉ tập trung `ai nên gửi brief + cần chuẩn bị gì + next step`.

## `/lien-he/`
- Form routing giữ đủ 6 luồng: Corporate / OEM-ODM / Distributor / Dealer / Affiliate / Consumer Support.
- Không công bố SLA/thời gian phản hồi nếu chưa được xác nhận.

---

# 5. CTA NORMALIZATION

| Page | Primary CTA | Secondary CTA |
|---|---|---|
| Knowledge hub | Hiểu làn da trước khi chọn routine | Tìm hiểu OEM/ODM |
| Hiểu làn da | Xây routine theo vai trò từng bước | Đọc về thành phần |
| Thành phần | Hiểu thứ tự dùng trong routine | Hiểu làn da |
| Routine & cách dùng | Bắt đầu với Starter Routine | Routine buổi sáng / tối |
| Câu chuyện sản phẩm | Xem Sản phẩm & Routine | Tìm điểm bán khi có product context |
| OEM là gì | Xem tổng quan OEM/ODM | Gửi brief OEM/ODM |
| ODM là gì | Xem tổng quan OEM/ODM | Gửi brief OEM/ODM |
| Đối tác | Chọn loại yêu cầu và gửi brief | Xem từng hình thức hợp tác |
| Hệ thống phân phối | Tìm hiểu trở thành đại lý | Tìm điểm bán |
| Tìm điểm bán | Gửi yêu cầu tìm kênh mua | Xem hệ thống phân phối |
| Trở thành đại lý | Gửi đăng ký đại lý | Xem hệ thống phân phối |
| Affiliate | Gửi kênh để tìm hiểu Affiliate | Đọc Câu chuyện sản phẩm |
| Hợp tác OEM/ODM | Gửi brief OEM/ODM | Đọc tổng quan OEM/ODM |
| Liên hệ | Chọn loại yêu cầu và gửi thông tin | Điều hướng theo inquiry type |

CTA không dùng “Mua ngay” trên Knowledge pages trừ khi trang con/product context thật sự yêu cầu và Product Truth/commerce policy cho phép.

---

# 6. BUSINESS FACTS / TBD REGISTER — AUTHORITATIVE

Các dữ liệu dưới đây vẫn là **business input bắt buộc** nếu muốn thay placeholder bằng thông tin public:

1. Legal entity / địa chỉ đăng ký / địa chỉ vận hành được phép công khai.
2. Phone/email theo từng department hoặc routing chính thức.
3. Giờ làm việc và SLA/thời gian phản hồi, nếu muốn công bố.
4. Danh sách điểm bán hiện hành + trạng thái + ngày xác minh.
5. Phạm vi/khu vực phân phối và quyền/độc quyền nếu có.
6. Chính sách đại lý, điều kiện onboarding, chiết khấu, MOQ, cam kết doanh số.
7. Trạng thái chương trình Affiliate, hoa hồng, attribution, payment, offer/combo rule.
8. Phạm vi dịch vụ OEM hiện hành.
9. Phạm vi dịch vụ ODM hiện hành.
10. MOQ, pricing/commercial model, lead time dự án OEM/ODM.
11. Năng lực/chứng nhận/cơ sở vật chất dùng làm proof cho B2B nếu được phép public.

**Critical:** Không thay các mục này bằng thông tin legacy, marketplace, đối tác ngoài hoặc suy đoán.

---

# 7. QA ACCEPTANCE

## Content structure
- [ ] 14 URL trong matrix có unique intent, H1, SEO Title, Meta, canonical.
- [ ] 5 Beauty Knowledge URL giữ cấu trúc Direct Answer → Explanation → Checklist/process → Related knowledge → Routine/product khi phù hợp → FAQ → CTA.
- [ ] 2 B2B Knowledge URL có Direct Answer → Explanation → Checklist → Related knowledge → FAQ → CTA.
- [ ] 7 Partner URL trả lời rõ: ai liên hệ / hợp tác gì / hỗ trợ theo hướng nào / quy trình / thông tin cần chuẩn bị / next action.

## SEO / AEO
- [ ] 1 H1 mỗi indexable URL.
- [ ] Primary keyword/owner topic không cannibalize.
- [ ] SEO Title/Meta không trùng nhau.
- [ ] Direct Answer đứng sớm và không hard-sell.
- [ ] Internal links theo matrix; anchor text mô tả destination, không dùng hàng loạt “xem thêm”.

## Fact safety
- [ ] Không tự thêm chiết khấu, hoa hồng, MOQ, chính sách đại lý, doanh số, địa bàn, quyền phân phối, timeline phản hồi.
- [ ] Không tự thêm cGMP/ISO/FDA/công suất/diện tích/năm kinh nghiệm/named partners nếu chưa có nguồn được duyệt.
- [ ] `[TBD — BUSINESS CONFIRMATION]` còn nguyên tại các field chưa được business xác nhận.

## Final status

`READY_QA` khi toàn bộ checklist trên PASS.  
Không cần viết lại body copy chỉ để đổi văn phong nếu nội dung hiện tại đã đáp ứng intent và fact discipline.
