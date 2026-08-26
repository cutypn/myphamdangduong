# DDG content audit — 2026-08-26

## Scope completed

Editorial pass completed for the core Theme 2 page set and the 10-article knowledge registry. Copy is intentionally written to remain useful without inventing company facts, certifications, capacity, partner names, medical outcomes or regulatory status.

### Core pages covered in Theme 2

- `/ve-dang-duong/` — company story and editorial principles.
- `/nang-luc/` — capability framework expressed as a project journey, not certification claims.
- `/nghien-cuu-phat-trien/` — educational R&D explanation and sample-feedback logic.
- `/nha-may-san-xuat-my-pham/` — production-process role with an explicit no-unverified-certification policy.
- `/oem-odm-my-pham/` — OEM/ODM scope and decision framework.
- `/thuong-hieu/` — brand architecture and product-data consistency.
- `/san-pham/` — safe product-catalog reading guidance; no media remapping.
- `/kien-thuc/` — Journal positioning and editorial scope.
- `/doi-tac/` — partnership-intake structure and better brief guidance.
- `/lien-he/` — contact routing and source-of-truth rule for company details.
- `/tim-diem-ban/` — safe store-finder guidance without invented locations.

`front-page.php` already has a complete Theme 2 narrative structure and was left intact in this pass to avoid mixing editorial work with the active product/media cleanup.

## Article registry status

All 10 registered topics now have full source files and are marked `editorial_review`. No article is auto-published by this source change.

P0 files already complete before this pass:

1. `oem-my-pham-la-gi.md`
2. `odm-my-pham-la-gi.md`
3. `oem-va-odm-my-pham-khac-nhau-the-nao.md`
4. `quy-trinh-gia-cong-my-pham.md`
5. `cach-lua-chon-nha-may-gia-cong-my-pham.md`

P1 files completed in this pass:

6. `cac-buoc-phat-trien-my-pham-thuong-hieu-rieng.md`
7. `rd-my-pham-la-gi.md`
8. `nghien-cuu-cong-thuc-my-pham.md`
9. `lam-mau-my-pham-can-luu-y-gi.md`
10. `thiet-ke-bao-bi-my-pham.md`

## Regulatory references checked

Current official source checks used in this pass:

- Nghị định 93/2016/NĐ-CP — điều kiện sản xuất mỹ phẩm; official Government legal-document portal.
- Thông tư 34/2025/TT-BYT — sửa đổi, bổ sung Thông tư 06/2011/TT-BYT về quản lý mỹ phẩm; issued 03-07-2025, effective 18-08-2025 on the official Government legal-document portal.

These references are used only for generic educational context. They are not evidence that Đăng Dương Group, a specific factory or a specific SKU holds any certification, approval or active regulatory status.

## Open facts — do not invent

The following facts remain intentionally absent until a source document is supplied and reviewed:

- Current legal company name as it should appear publicly in the footer/contact page, if different from the site brand.
- Registered/current operating address for public display.
- Official public phone number and email if not already configured in WordPress.
- Factory owner/operator identity and exact factory address.
- Any current CGMP-ASEAN/cGMP, ISO, FDA or other certification and its issuing body, certificate number, scope and expiry date.
- Production-line count, capacity, batch size or other manufacturing metrics.
- Named clients, distributors, retailers or partners and permission to display them.
- Current verified store/dealer list.
- Product-specific approved claims and their evidence provenance.
- Product-specific current regulatory status, unless already represented in the Product Truth source with current evidence.

## Editorial rules for future edits

1. Product identity must come from Product Truth, not legacy posts, filenames or visual inference.
2. Never turn an R&D goal into a public efficacy claim.
3. Never copy a claim, pack size or notification status from a similar SKU.
4. Do not use `FDA`, `ISO`, `cGMP`, `CGMP-ASEAN`, capacity numbers or named partners without an auditable current source.
5. Use one H1 per page; H2 for major user questions; H3 for supporting decisions.
6. Each page should have one primary next-step CTA and contextual internal links.
7. Article metadata should keep direct answers concise and useful for search/AEO, while the body explains nuance.
8. Human editorial review remains the publication gate for knowledge articles.

## Files changed by this content pass

- `apps/bizrise-ddg-theme/inc/editorial-content.php`
- `apps/bizrise-ddg-theme/page.php`
- `data/content/article-registry.json`
- five P1 article Markdown files under `data/content/articles/`
- this audit document

## Deliberately not changed

- Product/media mapping and Featured Images.
- WooCommerce product facts.
- Regulatory hold logic.
- Production deployment/cPanel.
- WordPress live database content.

This separation is intentional while the product catalog visual/data repair is still being validated on the frontend.
