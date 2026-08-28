# DDG content publish report — latest

## Source status

- Article registry: **10 total / 10 publish_ready / 0 editorial_review**.
- Markdown front matter: **10/10 publish_ready**, reviewer pending: **0**.
- Deterministic article importer: **available**, exact-slug and idempotent publication path retained.
- Core-page curated content remains present for `/ve-dang-duong/`, `/nang-luc/`, `/nghien-cuu-phat-trien/`, `/nha-may-san-xuat-my-pham/`, `/oem-odm-my-pham/`, `/thuong-hieu/`, `/san-pham/`, `/kien-thuc/`, `/doi-tac/`, `/lien-he/`, `/tim-diem-ban/`, plus homepage.
- Product detail copy: **not modified**.

## Improvement in this run

Updated `data/content/articles/odm-my-pham-la-gi.md` in commit `c204883eef12e02c8c37aac5603cc2f37927f419`.

The ODM article now uses clearer Vietnamese wording for technical/business terms, keeps the same legal/source-safe meaning, updates `last_verified` to **2026-08-28**, and adds a clear final next-step section aligned with its CTA metadata.

Counts before/after:

- publish-ready articles before: **10**
- publish-ready articles after: **10**
- metadata synchronized before: **10/10**
- metadata synchronized after: **10/10**
- ODM article language-cleanup items before: **1 article**
- ODM article language-cleanup items after: **0 articles**

## Article media inventory

Target endpoint:

`/wp-json/bizrise-ddg/v1/media-inventory?scope=articles&per_page=100`

Runtime media before: **NOT VERIFIED**.

Runtime media after: **NOT VERIFIED**.

The endpoint could not be read from the current verification environment: direct REST open was rejected by the safe-URL layer and exact-domain search returned no indexable endpoint. Therefore Featured Image ID/file/URL/ALT/dimensions, missing-featured counts, duplicate-attachment counts and live article-sync counts are not inferred.

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

**ODM LANGUAGE QA: COMPLETE.**

**PENDING: FINAL CI + PRODUCTION ARTICLE/MEDIA VERIFICATION.**
