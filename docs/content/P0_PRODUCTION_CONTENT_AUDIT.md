# DDG Website Production V1 — P0 Content Audit

**Sprint:** BRZ-20 Content Sprint 0  
**Branch:** `agent/brz-20-content`  
**Scope:** P0 production URLs only  
**Status:** Content/SEO handoff; no PHP/template/deploy changes

## 1. Sources and governance

This audit follows the project production rules defined in:

- `DANGDUONGGROUP_SEO_AI_CONTENT_STANDARD_2026.md`
- `DANGDUONGGROUP_CONTENT_WRITING_STANDARD_2026_v2.md`
- `DDG_MENU_THEME_REBUILD_v0.2.md`
- Current read-only implementation: `apps/bizrise-ddg-site-pages/bizrise-ddg-full-pages.php`
- Product content must use Product Truth / approved product data. Legacy copy is research material only and is not an approved claim source.

Mandatory rules for every indexable P0 URL:

- Be Vietnam Pro.
- Exactly one H1.
- Primary keyword appears naturally in H1.
- Direct Answer appears immediately after H1 and is 2–4 sentences.
- Semantic H2/H3 structure.
- Clear internal links and CTA.
- No unverified corporate metrics or certification claims.
- Product claims only from Product Truth / Approved Claim Library.

## 2. Current implementation observations

The current full-page layer already creates the main corporate, capability, brand, routine, knowledge and partner pages. However:

1. Homepage is currently organized around a digital/brand ecosystem message and moves quickly into brand/routine/knowledge; it does not yet follow the required Corporate Excellence story order.
2. Current `/nang-luc/` copy describes “data, content, media, brand and cooperation” more than first-party corporate/manufacturing capability.
3. Current R&D/factory/OEM pages intentionally avoid certification and numerical claims, which is safer than legacy copy, but page-level capability claims still require first-party confirmation before production indexing.
4. Current implementation has a route/owner mismatch: project SEO standard assigns product discovery to `/san-pham/`, while the full-page layer currently uses `/san-pham-routine/` as the hub.
5. Current page hierarchy may create nested permalinks for child pages while project SEO owner mapping uses top-level URLs. DEV must confirm final canonical routes before release.

## 3. P0 URL audit matrix

| URL / owner target | Primary Keyword | Intent | H1 | Direct Answer | H2/H3 | CTA | Fact status | Missing fact | Owner |
|---|---|---|---|---|---|---|---|---|---|
| `/` | Đăng Dương Group | Brand / Corporate | **Đăng Dương Group – Hệ sinh thái thương hiệu, sản phẩm và hợp tác mỹ phẩm** | Website Đăng Dương Group được tổ chức như một điểm chạm corporate trước khi dẫn người xem tới thương hiệu và sản phẩm. Nội dung ưu tiên câu chuyện doanh nghiệp, các năng lực đã được xác minh, R&D, sản xuất, mô hình hợp tác, hệ thương hiệu và kiến thức. Những dữ liệu chưa có hồ sơ xác minh được giữ ở trạng thái TBD thay vì biến thành claim. | H2 Who We Are; H2 Năng lực; H2 R&D; H2 Sản xuất; H2 OEM/ODM; H2 Thương hiệu; H2 Sản phẩm; H2 Routine & Kiến thức; H2 Phân phối & Đối tác; H2 Journal; H2 CTA | Khám phá Đăng Dương / Trao đổi hợp tác | **SAFE WITH FACT GATES** | Legal entity; R&D/manufacturing/OEM scope; factory entity; certifications; distribution facts | BRZ-20; facts: DDG owner / BRZ-30 where product-related |
| `/ve-dang-duong/` | công ty mỹ phẩm Đăng Dương | Entity / Corporate | **Về Đăng Dương Group – Hệ sinh thái thương hiệu mỹ phẩm** | Trang Về Đăng Dương giúp người đọc hiểu Đăng Dương Group là thực thể nào, hệ thương hiệu được tổ chức ra sao và những giá trị nào định hướng nội dung, sản phẩm và hợp tác. Lịch sử, pháp nhân, dấu mốc và năng lực chỉ được công bố khi có nguồn doanh nghiệp xác minh. | H2 Đăng Dương Group là ai?; H2 Câu chuyện; H2 Tầm nhìn & sứ mệnh; H2 Giá trị thương hiệu; H2 Hệ sinh thái; H2 Bước tiếp theo | Xem năng lực | **TBD CORPORATE FACTS** | Legal name, tax code, addresses, history dates, vision/mission approval, brand ownership | BRZ-20 + DDG fact owner |
| `/nang-luc/` | năng lực Đăng Dương Group | B2B Capability | **Năng lực Đăng Dương Group trong hệ sinh thái mỹ phẩm** | Trang Năng lực là hub dẫn tới R&D, sản xuất, chất lượng và các mô hình hợp tác. Mỗi năng lực chỉ được mô tả bằng thông tin có thể kiểm chứng; chứng nhận, công suất, diện tích và số liệu vận hành không được suy diễn từ hình ảnh hoặc nội dung cũ. | H2 Tổng quan năng lực; H2 R&D; H2 Sản xuất; H2 Quy trình chất lượng; H2 Gia công mỹ phẩm; H2 OEM/ODM; H2 Quy trình hợp tác | Khám phá từng năng lực / Gửi brief | **BLOCKED FOR STRONG CLAIMS** | Capability statement, factory ownership/operation, R&D scope, QA/QC scope, service scope | BRZ-20 + DDG fact owner |
| `/gia-cong-my-pham/` | gia công mỹ phẩm | Commercial | **Gia công mỹ phẩm: từ định hướng sản phẩm đến kế hoạch triển khai** | Gia công mỹ phẩm là quá trình doanh nghiệp phối hợp với một đơn vị nghiên cứu hoặc sản xuất để phát triển sản phẩm mang thương hiệu riêng. Tại Đăng Dương Group, phạm vi dịch vụ, hạng mục hỗ trợ và điều kiện hợp tác chỉ được công bố theo hồ sơ năng lực đã xác minh; phần chưa xác minh được đánh dấu TBD. | H2 Gia công mỹ phẩm là gì?; H2 Khi nào doanh nghiệp cần gia công?; H2 Phạm vi hợp tác tại Đăng Dương (TBD verified); H2 Quy trình; H2 Tiêu chí chuẩn bị brief; H2 FAQ | Gửi nhu cầu gia công / Xem OEM & ODM | **TBD SERVICE SCOPE** | Whether DDG directly provides manufacturing; categories; MOQ; lead time; legal/packaging services; facility evidence | BRZ-20 + DDG fact owner |
| `/oem-odm-my-pham/` | OEM ODM mỹ phẩm | Commercial / Informational | **OEM và ODM mỹ phẩm: chọn mô hình hợp tác phù hợp** | OEM và ODM là hai mô hình hợp tác khác nhau về mức độ chủ động của thương hiệu và phạm vi phát triển sản phẩm. Trang này giải thích điểm khác biệt trước, sau đó mới trình bày phạm vi Đăng Dương Group có thể hỗ trợ khi dữ liệu dịch vụ đã được xác minh. | H2 OEM mỹ phẩm là gì?; H2 ODM mỹ phẩm là gì?; H2 OEM và ODM khác nhau thế nào?; H2 Chọn mô hình nào?; H2 Phạm vi Đăng Dương (TBD verified); H2 Quy trình; H2 FAQ | Gửi brief OEM/ODM / Xem quy trình gia công | **EDUCATION SAFE; SERVICE TBD** | Confirm OEM/ODM definitions used internally, actual service scope, deliverables, exclusions, MOQ/timeline | BRZ-20 + DDG fact owner |
| `/nha-may-san-xuat-my-pham/` | nhà máy sản xuất mỹ phẩm | Capability / Commercial | **Nhà máy sản xuất mỹ phẩm: năng lực chỉ công bố từ dữ liệu đã xác minh** | Một trang nhà máy có giá trị khi cho người xem thấy không gian thực tế, quy trình và phạm vi vận hành có thể kiểm chứng. Đăng Dương Group không công bố cGMP, ISO, FDA, diện tích, công suất, dây chuyền hay sản lượng nếu chưa có hồ sơ xác minh tương ứng. | H2 Tổng quan nhà máy; H2 Không gian & khu vực vận hành; H2 Quy trình sản xuất; H2 Kiểm soát chất lượng; H2 Hồ sơ/chứng nhận đã xác minh; H2 Hình ảnh thực tế; H2 Liên hệ B2B | Khám phá OEM/ODM / Trao đổi hợp tác | **CRITICAL FACT HOLD** | Factory legal entity/address, ownership/operation relation, standards/certificates, lines, categories, capacity, area, QA/QC evidence | BRZ-20 + DDG fact owner |
| `/nghien-cuu-phat-trien/` | R&D mỹ phẩm | Capability / Informational | **R&D mỹ phẩm: từ nhu cầu người dùng đến định hướng phát triển sản phẩm** | R&D mỹ phẩm kết nối nhu cầu người dùng, mục tiêu sản phẩm, trải nghiệm sử dụng và các bước đánh giá trước khi thông tin được công bố. Phạm vi phòng R&D, đội ngũ, thiết bị, thử nghiệm và năng lực phát triển của Đăng Dương Group chỉ được mô tả khi có hồ sơ xác minh. | H2 R&D mỹ phẩm là gì?; H2 Bắt đầu từ insight; H2 Định hướng sản phẩm; H2 Phát triển & đánh giá; H2 Kiểm soát thông tin; H2 Năng lực R&D Đăng Dương (TBD) | Xem phát triển công thức / Trao đổi dự án | **CONCEPT SAFE; DDG CAPABILITY TBD** | R&D team, lab/facility, process, equipment, testing, formula ownership, evidence | BRZ-20 + DDG fact owner |
| `/thuong-hieu/` | thương hiệu mỹ phẩm Đăng Dương | Brand discovery | **Hệ sinh thái thương hiệu mỹ phẩm Đăng Dương** | Trang Thương hiệu giúp người dùng khám phá từng brand theo câu chuyện, nhóm nhu cầu, routine và sản phẩm thay vì chỉ xem một lưới SKU. Chỉ các thương hiệu có Brand Owner và trạng thái sử dụng đã được xác minh mới được hiển thị như một phần chính thức của hệ sinh thái Đăng Dương. | H2 Hệ sinh thái thương hiệu; H2 Brand story; H2 Nhu cầu & beauty territory; H2 Routine; H2 Sản phẩm theo brand; H2 Beauty Journal | Khám phá thương hiệu / Tìm routine | **BRAND OWNERSHIP TBD** | Owner/status for One Today, One Today Gold, Ever Today, Cream X2, Hatagold, She One and any additional brands | BRZ-20 + BRZ-30 identity verification |
| `/san-pham/` **(SEO owner; implementation currently uses `/san-pham-routine/`)** | sản phẩm Đăng Dương | Product discovery | **Sản phẩm Đăng Dương: khám phá theo thương hiệu, nhu cầu và routine** | Danh mục sản phẩm chỉ hiển thị SKU có Product Truth và trạng thái cho phép publish. Người dùng có thể đi từ thương hiệu, nhu cầu hoặc routine tới trang chi tiết, nơi benefits, ingredients, cách dùng, warning và thông tin liên quan phải bám dữ liệu đã được duyệt. | H2 Khám phá theo thương hiệu; H2 Theo nhu cầu; H2 Theo routine; H2 Sản phẩm nổi bật đã xác minh; H2 Cách chọn sản phẩm; H2 Where to Buy | Xem sản phẩm / Tìm routine / Tìm điểm bán | **PRODUCT TRUTH GATED** | Final route; PUBLISH_ALLOWED list; approved benefits; ingredients; usage; warnings; brand/category mapping | BRZ-20 + BRZ-30 Product Truth |
| `/kien-thuc/` | kiến thức gia công mỹ phẩm | Informational / AEO-GEO | **Kiến thức mỹ phẩm: gia công, R&D, thành phần và routine** | Khu vực Kiến thức giải thích các khái niệm về gia công mỹ phẩm, OEM/ODM, R&D, thành phần và cách xây routine trước khi dẫn người đọc tới dịch vụ hoặc sản phẩm. Mỗi bài phải trả lời intent sớm, có nguồn phù hợp và không dùng blog để lặp lại commercial intent của URL owner. | H2 Gia công mỹ phẩm; H2 OEM/ODM; H2 R&D; H2 Hiểu làn da; H2 Thành phần; H2 Routine & cách dùng; H2 Câu chuyện sản phẩm | Đọc chủ đề / Xem URL owner liên quan | **SAFE WITH ARTICLE FACT GATES** | Author/reviewer model; source requirements; final article owner map; current 40-article overlap audit | BRZ-20 |
| `/doi-tac/` | đối tác Đăng Dương Group | B2B / Lead | **Hợp tác cùng Đăng Dương Group** | Trang Đối tác định tuyến nhu cầu phân phối, đại lý, affiliate và hợp tác phát triển tới đúng luồng trao đổi. Không công bố số lượng đại lý, khách hàng, đối tác, thị trường hay phạm vi phân phối nếu chưa có dữ liệu xác minh. | H2 Hình thức hợp tác; H3 Phân phối; H3 Đại lý; H3 Affiliate; H3 OEM/ODM; H2 Quy trình tiếp nhận; H2 Thông tin cần chuẩn bị; H2 FAQ | Chọn hình thức hợp tác / Liên hệ | **PROGRAM DETAILS TBD** | Distribution model, dealer criteria, affiliate availability, partner terms, geographic coverage, named partners | BRZ-20 + DDG commercial owner |
| `/lien-he/` | liên hệ Đăng Dương Group | Navigational / Lead | **Liên hệ Đăng Dương Group** | Trang Liên hệ giúp người dùng chọn đúng chủ đề: thương hiệu & sản phẩm, đại lý & phân phối, OEM/ODM hoặc affiliate. Chỉ hiển thị địa chỉ, hotline, email, pháp nhân và thời gian làm việc từ nguồn doanh nghiệp đã xác minh. | H2 Chọn nhu cầu liên hệ; H3 Sản phẩm; H3 Đại lý & phân phối; H3 OEM/ODM; H3 Affiliate; H2 Thông tin liên hệ đã xác minh; H2 Gửi yêu cầu | Gửi yêu cầu / Chọn luồng liên hệ | **CONTACT FACTS TBD** | Official email(s), phone/hotline, Zalo, address, legal entity, working hours, form routing/consent text | BRZ-20 + DDG business owner |

## 4. Keyword ownership / cannibalization guard

P0 owner map:

- `/` → **Đăng Dương Group**
- `/ve-dang-duong/` → **công ty mỹ phẩm Đăng Dương**
- `/nang-luc/` → **năng lực Đăng Dương Group**
- `/gia-cong-my-pham/` → **gia công mỹ phẩm**
- `/oem-odm-my-pham/` → **OEM ODM mỹ phẩm**
- `/nha-may-san-xuat-my-pham/` → **nhà máy sản xuất mỹ phẩm**
- `/nghien-cuu-phat-trien/` → **R&D mỹ phẩm**
- `/thuong-hieu/` → **thương hiệu mỹ phẩm Đăng Dương**
- `/san-pham/` → **sản phẩm Đăng Dương**
- `/kien-thuc/` → **kiến thức gia công mỹ phẩm** (hub; supporting articles must not take commercial owner intent)
- `/doi-tac/` → **đối tác Đăng Dương Group**
- `/lien-he/` → **liên hệ Đăng Dương Group**

## 5. Required internal-link spine

- Home → About → Capability.
- Home / Capability → R&D, Factory, Gia công, OEM/ODM.
- Gia công → OEM/ODM → Quy trình gia công → Contact.
- Brands → Product hub → Product detail → Routine → Where to Buy.
- Knowledge articles → their P0 owner URL, not sibling articles competing for the same commercial intent.
- Partner hub → Dealer / Distribution / Affiliate / OEM-ODM → Contact.

## 6. SEO metadata drafting rules for implementation

DEV/SEO engine should expose unique SEO title, meta description, canonical, OG and schema fields per P0 URL. No production URL should inherit a shared generic meta description. Home should establish the Đăng Dương Group entity; capability pages should use capability schema/page types only when facts match visible content; product schema must only contain Product Truth data.

## 7. Release gate

A P0 URL is not content-PASS if any of the following is true:

- H1 count is not exactly 1.
- Direct Answer is missing or is primarily sales copy.
- Primary keyword owner conflicts with another indexable URL.
- Corporate claim is stronger than available first-party evidence.
- Product copy is sourced from legacy marketing instead of Product Truth.
- A named certification, number, partner, market or award is unverified.
- CTA or internal link destination is missing/broken.
- `/san-pham/` owner vs `/san-pham-routine/` implementation mismatch is unresolved.
