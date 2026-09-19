# P0 Corporate Fact & Claim Register

**Purpose:** Prevent unverified corporate/product claims from entering DDG Website Production V1.  
**Rule:** Unknown is not false and is not true. Unknown = `TBD` until first-party verification.

## 1. Corporate claims

| Claim / data | Current production-content status | Publish rule | Evidence required | Owner |
|---|---|---|---|---|
| Display name “Đăng Dương Group” | Project-approved display entity | Allowed as display name; do not infer legal name | Internal brand approval | BRZ-20 / DDG |
| Legal entity / legal name | **TBD** | Do not publish guessed legal entity | Current business registration / company-approved legal profile | DDG business owner |
| Tax code | **TBD** | Do not publish | Current legal document | DDG business owner |
| Registered / operating address | **TBD** | Do not merge legacy/public addresses | Company-approved address source | DDG business owner |
| Factory legal entity / address / relation to DDG | **TBD CRITICAL** | Do not imply ownership/operation until verified | Factory legal/operating documents + company confirmation | DDG business owner |
| R&D capability | **TBD** | Educational R&D content is allowed; DDG-specific capability claim is held | R&D process/capability document approved for public use | DDG fact owner |
| Manufacturing capability | **TBD CRITICAL** | No strong “manufacturer” claim without evidence | Corporate/factory capability profile | DDG fact owner |
| OEM service | **TBD** | Explain OEM concept; DDG service scope requires verification | Current OEM service profile / sales scope | DDG commercial owner |
| ODM service | **TBD** | Explain ODM concept; DDG service scope requires verification | Current ODM service profile / sales scope | DDG commercial owner |
| cGMP / CGMP | **TBD / BLOCKED** | Never publish until exact certificate/status/entity is verified | Valid certificate, scope, issuing authority, expiry/status | DDG fact owner |
| ISO | **TBD / BLOCKED** | Never publish generic “ISO certified” | Exact standard/certificate/entity/scope/status | DDG fact owner |
| FDA | **TBD / BLOCKED** | Never publish ambiguous “FDA” claim | Exact registration/approval context and legal permission to state it | DDG fact owner |
| Factory area | **TBD / BLOCKED** | No number | Company-approved facility evidence | DDG fact owner |
| Production capacity / output | **TBD / BLOCKED** | No number | Current verified production data + period/unit definition | DDG fact owner |
| Years of experience / founding year | **TBD / BLOCKED** | No “X years” or derived duration | Approved history timeline / legal establishment data | DDG fact owner |
| Number of formulas/products/customers | **TBD / BLOCKED** | No aggregate counts unless source and date are defined | Approved dated business data | DDG fact owner |
| Named customers / partners | **TBD / BLOCKED** | Do not name or show logo without permission | Contract/permission/approved partner list | DDG commercial/legal |
| Export markets | **TBD / BLOCKED** | No “global/export to X markets” | Current approved export data | DDG business owner |
| Awards | **TBD / BLOCKED** | Do not publish | Award evidence, organizer, year, scope | DDG business owner |
| Distribution network scale | **TBD / BLOCKED** | Do not state number/coverage | Current dealer/store database | DDG commercial owner |
| Store locations | **TBD / DATA GATE** | Only active/confirmed locations | Store record + status + last verified date | DDG commercial owner |
| Brand ownership | **TBD per brand** | Do not imply all seeded brands are owned by DDG | Brand owner/company record per brand | BRZ-30 + DDG |

## 2. Product truth gate

Product content has a separate source-of-truth rule:

1. Current legal/product notification/label evidence.
2. Company-approved Product Master.
3. Latest approved catalogue.
4. Approved Claim Library.
5. Public-approved technical/R&D documentation.

Legacy website, distributor pages, marketplace copy, social/UGC and archived copy may inform language/search research but **must not verify a product claim**.

### Product publish status

Do not publish sales content if product regulatory status is:

- `unknown`
- `hold`
- `recalled`
- `retired`

Only products with a valid Product Truth state and publish approval should feed homepage cards, product archives, routine/combo recommendations or AI knowledge.

## 3. High-risk language hold

Unless an approved claim explicitly supports it, do not use wording such as:

- trị / chữa / điều trị
- xóa / dứt điểm / tận gốc / đặc trị
- kháng viêm / diệt khuẩn / chống nấm
- tái tạo tế bào / tăng sinh collagen
- đào thải độc tố
- hiệu quả sau X ngày
- 100% an toàn
- phù hợp mọi loại da
- không gây kích ứng
- bác sĩ khuyên dùng / chuyên gia hàng đầu
- tốt nhất / số 1 / hàng đầu
- clinically proven / clinical without corresponding evidence

## 4. Production-safe fallback language

When factual evidence is missing, use one of these patterns instead of inventing proof:

- “Thông tin đang được doanh nghiệp xác minh.”
- “Phạm vi cụ thể được xác nhận theo hồ sơ năng lực hiện hành.”
- “Chỉ các dữ liệu đã được xác minh mới được công bố trên website.”
- “TBD — cần nguồn doanh nghiệp trước khi publish.”

Do not use a vague fallback to disguise a strong claim. If the page premise itself depends on an unverified capability (for example factory ownership), keep that section/page under fact hold until the evidence is available.

## 5. QA claim scan

BRZ-80 should search rendered P0 pages for at least:

`cGMP`, `CGMP`, `ISO`, `FDA`, `công suất`, `diện tích`, `năm kinh nghiệm`, `khách hàng`, `đối tác`, `xuất khẩu`, `giải thưởng`, `số 1`, `hàng đầu`, `trị`, `chữa`, `xóa`, `dứt điểm`, `tận gốc`, `đặc trị`.

Every match must map to an approved source or be removed/held before production release.
