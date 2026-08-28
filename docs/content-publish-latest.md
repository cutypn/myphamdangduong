# DDG content publish report — latest

## Source status

- Article registry: **10 total / 10 publish_ready / 0 editorial_review**.
- Markdown front matter: **10/10 publish_ready**, reviewer pending: **0**.
- Product detail copy: **not modified in this run**.
- Product Marketing Context was read before editing; public copy continues to avoid unverified certifications, capacity, partners, contact facts and medical/product efficacy claims.

## Improvement in this run

Updated `data/content/articles/nghien-cuu-cong-thuc-my-pham.md` in commit `aa5b01ddc01eae21165a1147726d6f3e53f31df3`.

The article received a full public-language pass rather than a report-only audit. Internal/technical wording such as `product role`, `SKU`, `brief`, `ingredient trend`, `routine`, `version`, `feedback` and `claim` was replaced with clearer Vietnamese. The SEO title/meta, direct answer and `last_verified` were synchronized to the rewritten body.

The body was strengthened around four practical decisions: define the product role before formulation work, turn wishes into observable sample criteria, consolidate sample feedback through one owner, and separate formulation objectives from market-facing claims.

Counts before/after:

- publish-ready articles: **10 → 10**
- synchronized article metadata: **10/10 → 10/10**
- articles materially rewritten this run: **0 → 1**
- product detail copy changed: **0**

## Structural-page finding

`SiteStructureImporter` currently updates titles for existing structural pages but skips their excerpt/body content after first creation. This means later source rewrites for brand/routine/subpages can remain stale in WordPress even when `data/site-structure.php` is improved. This is a publication-path blocker for finishing thin structural pages and should be fixed by the build/orchestration agent before those rewrites are counted as deployed content.

## Article media inventory

Target endpoint:

`/wp-json/bizrise-ddg/v1/media-inventory?scope=articles&per_page=100`

Runtime media before: **NOT VERIFIED**.

Runtime media after: **NOT VERIFIED**.

No live counts are inferred for article ID/slug/title/category, Featured Image ID/file/URL/ALT/dimensions, missing featured or duplicate attachment until the production endpoint is reachable.

## Production gate

Production is not marked complete until the final CI-passing SHA is deployed and smoke-tested. Required evidence remains:

1. deployed SHA matches the validated HEAD;
2. article sync reports 10/10 with zero errors;
3. `/kien-thuc/` and all 10 public article URLs render correctly;
4. article media inventory returns complete featured-image and duplicate data;
5. structural-page content synchronization is confirmed for managed subpages.

## Current state

**SOURCE: 10/10 KNOWLEDGE ARTICLES PUBLISH-READY.**

**CONTENT CHANGE THIS RUN: 1 ARTICLE MATERIALLY REWRITTEN.**

**PRODUCT DETAIL COPY: UNCHANGED.**

**BLOCKER FOUND: EXISTING STRUCTURAL PAGE BODY CONTENT IS NOT UPDATED BY `SiteStructureImporter`.**

**PENDING: CI / DEPLOY / PRODUCTION ARTICLE-MEDIA VERIFICATION.**
