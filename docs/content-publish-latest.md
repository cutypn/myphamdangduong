# DDG content publish report — latest

## Source status

- Article registry: **10 total / 10 publish_ready / 0 editorial_review**.
- Markdown front matter: **10/10 publish_ready**, reviewer pending: **0**.
- Deterministic article importer: **available**, exact-slug and idempotent publication path retained.
- Core-page curated content remains present for `/ve-dang-duong/`, `/nang-luc/`, `/nghien-cuu-phat-trien/`, `/nha-may-san-xuat-my-pham/`, `/oem-odm-my-pham/`, `/thuong-hieu/`, `/san-pham/`, `/kien-thuc/`, `/doi-tac/`, `/lien-he/`, `/tim-diem-ban/`, plus homepage.
- Product detail copy: **not modified**.

## Improvement in this run

Updated `apps/bizrise-ddg-migrator/data/site-content.php` in commit `6699022b02af1d1cc86acc12ea5abbcefa23c8ba`.

Public core-page copy was simplified to remove internal/business jargon without changing verified meaning or product data. Replacements include clearer Vietnamese wording for `Product Truth`, `claim`, `affiliate`, `brief`, `product brief`, `artwork`, `feedback`, `checklist`, `B2B`, and routine labels where the English term was unnecessary.

Core pages materially cleaned in this run:

- `/nang-luc/`
- `/thuong-hieu/`
- `/san-pham/`
- `/kien-thuc/`
- `/doi-tac/`
- `/nghien-cuu-phat-trien/`
- `/nha-may-san-xuat-my-pham/`
- `/oem-odm-my-pham/`
- `/lien-he/`

Counts before/after:

- publish-ready articles before: **10**
- publish-ready articles after: **10**
- metadata synchronized before: **10/10**
- metadata synchronized after: **10/10**
- core pages with identified public-facing jargon before: **9**
- core pages with identified public-facing jargon after: **0** for the terms addressed in this pass
- product detail copy changed: **0**

## Article media inventory

Target endpoint:

`/wp-json/bizrise-ddg/v1/media-inventory?scope=articles&per_page=100`

Runtime media before: **NOT VERIFIED**.

Runtime media after: **NOT VERIFIED**.

The endpoint still could not be read from the current verification environment. Direct REST open was rejected by the safe-URL layer, and a fresh exact-domain search on **2026-08-28** returned no indexable endpoint. Therefore article ID/slug/title/category, Featured Image ID/file/URL/ALT/dimensions, missing-featured counts, duplicate-attachment counts and live article-sync counts are not inferred.

## CI state for content commit

For commit `6699022b02af1d1cc86acc12ea5abbcefa23c8ba`, both Validate Bizrise DDG V2 and Build Bizrise DDG V2 Release were triggered. At the time this report was written they were still running, so this report does not claim CI PASS early.

## Production gate

Production remains pending until all of the following are verified on the final CI-passing HEAD:

1. deployed SHA matches final HEAD;
2. article sync reports 10/10 with zero errors;
3. `/kien-thuc/` and all 10 public article URLs render correctly;
4. article media inventory returns complete featured-image and duplicate data;
5. no broken internal links remain.

## Safety and scope

No unverified certification, production-capacity, partner, contact, medical-effect or product-detail statements were added.

## Current state

**SOURCE: 10/10 KNOWLEDGE ARTICLES PUBLISH-READY.**

**METADATA: 10/10 SYNCHRONIZED.**

**CORE PUBLIC LANGUAGE QA: IMPROVED — 9 PAGES CLEANED IN THIS PASS.**

**PRODUCT DETAIL COPY: UNCHANGED.**

**PENDING: FINAL CI + PRODUCTION ARTICLE/MEDIA VERIFICATION.**
