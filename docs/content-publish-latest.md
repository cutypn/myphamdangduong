# DDG content publish report — latest

## Source status

- Article registry: **10 total / 10 publish_ready / 0 editorial_review**.
- Markdown front matter: **10/10 publish_ready**, reviewer pending: **0**.
- Deterministic article importer: **available**, exact-slug and idempotent publication path retained.
- Core-page curated content remains present for `/ve-dang-duong/`, `/nang-luc/`, `/nghien-cuu-phat-trien/`, `/nha-may-san-xuat-my-pham/`, `/oem-odm-my-pham/`, `/thuong-hieu/`, `/san-pham/`, `/kien-thuc/`, `/doi-tac/`, `/lien-he/`, `/tim-diem-ban/`, plus homepage.
- Product detail copy: **not modified**.

## Improvement in this run

Updated `data/content/articles/lam-mau-my-pham-can-luu-y-gi.md` in commit `d698dcf7ab431317c31ea69db27a0f3ddd8fd458`.

The article was rewritten for cleaner public Vietnamese while retaining the same educational scope and publish-ready status. Internal/business jargon such as `feedback`, `brief`, `product owner`, `version`, `routine`, `copy feedback`, `claim` and `form feedback` was replaced with clearer Vietnamese phrasing. The direct answer, SEO title/meta and body copy now use the same public-facing terminology, and `last_verified` was advanced to **2026-08-28**.

The body was also strengthened around three practical decisions: defining review criteria before receiving a sample, assigning one person to consolidate comments, and recording each sample version so later decisions remain traceable.

Counts before/after:

- publish-ready articles before: **10**
- publish-ready articles after: **10**
- metadata synchronized before: **10/10**
- metadata synchronized after: **10/10**
- article with identified public-facing jargon cleaned in this run: **1**
- product detail copy changed: **0**

## Article media inventory

Target endpoint:

`/wp-json/bizrise-ddg/v1/media-inventory?scope=articles&per_page=100`

Runtime media before: **NOT VERIFIED**.

Runtime media after: **NOT VERIFIED**.

The endpoint could not be read from the current verification environment in this run. Therefore article ID/slug/title/category, Featured Image ID/file/URL/ALT/dimensions, missing-featured counts, duplicate-attachment counts and live article-sync counts are not inferred.

## CI state for content commit

For content commit `d698dcf7ab431317c31ea69db27a0f3ddd8fd458`, GitHub had not yet returned completed status checks at the time this report was written. This report therefore does not claim CI PASS early.

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

**CONTENT CHANGE THIS RUN: 1 ARTICLE REWRITTEN FOR CLEARER PUBLIC VIETNAMESE.**

**PRODUCT DETAIL COPY: UNCHANGED.**

**PENDING: FINAL CI + PRODUCTION ARTICLE/MEDIA VERIFICATION.**
