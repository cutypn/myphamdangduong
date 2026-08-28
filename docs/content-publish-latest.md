# DDG content publish report — latest

## Scope status

- Known indexable target scope: **93 URLs** = 39 corporate/hub/subpage URLs + 10 Knowledge articles + 44 product detail URLs.
- Knowledge registry: **10 total / 10 publish_ready / 0 editorial_review**.
- Product detail copy changed in this run: **0/44**. Product Truth and product media mapping were not modified.
- Structural publication path: **unblocked in source**. `SiteStructureImporter` schema `1.2.0` updates title + excerpt + body for existing managed pages.
- Full-site final/pending/blocked count remains **not claimed as production-final** until deploy + URL smoke-test evidence exists.

## Content written in this run

Materially rewrote `data/content/articles/rd-my-pham-la-gi.md` and synchronized `data/content/article-registry.json`.

The R&D article now follows a clearer public journey: user need → measurable sample criteria → controlled sample feedback → implementation readiness → handoff data → next action. It adds a useful FAQ, stronger internal links, clearer CTA and `last_verified: 2026-08-28`, while removing avoidable internal wording such as brief/SKU/routine/artwork/claim from reader-facing copy where Vietnamese wording is clearer.

No DDG factory ownership, certification, capacity, partner, medical efficacy or other unsupported claim was introduced.

Counts before/after:

- Knowledge publish-ready: **10 → 10**
- Registry metadata synchronized: **10/10 → 10/10**
- Articles materially rewritten this run: **0 → 1**
- Product detail copy changed: **0 → 0**

Content commit: `96b297d75500ec2740b3c0dd430463b021587ac9`  
Registry sync commit: `f248aa495016d0462de9036e9cb94493f5378a4a`

## Validation

Exact registry HEAD `f248aa495016d0462de9036e9cb94493f5378a4a` passed both required workflows:

- Validate Bizrise DDG V2: **PASS** — PHP syntax, shell syntax, Product Truth seed, controlled 44-SKU media manifest, JSON and publish-ready article data all passed.
- Build Bizrise DDG V2 Release: **PASS** — validation, release build and artifact upload all passed.

## Facts missing / publication constraints

Still do not publish or infer facts that are absent from verified sources, including: cGMP/ISO/FDA or other certifications, production capacity, named partners, unverified contact/address details, medical effects, product efficacy beyond approved Product Truth, or unsupported suitability/safety claims.

Product detail expansion remains conditional on verified Product Truth/provenance for each field. No product image remapping is allowed from content work.

## Article media inventory

Target endpoint:

`/wp-json/bizrise-ddg/v1/media-inventory?scope=articles&per_page=100`

Production media before: **NOT VERIFIED**.  
Production media after: **NOT VERIFIED**.

Missing featured-image count: **NOT VERIFIED**.  
Duplicate attachment count: **NOT VERIFIED**.  
ALT/dimensions completeness: **NOT VERIFIED**.

The production host/endpoint could not be retrieved from the current external runtime, so no media count is inferred.

## Production verification

**NOT COMPLETE.** Source/CI PASS is not production proof.

Required production evidence remains:

1. deployed SHA matches the final CI-passing branch SHA;
2. managed structural pages show the latest synchronized body/excerpt;
3. `/kien-thuc/` plus all 10 registry article URLs smoke-test successfully;
4. media inventory returns complete ID/slug/title/category + Featured Image ID/file/URL/ALT/dimensions and missing/duplicate counts;
5. representative brand/routine/corporate/product URLs pass browser smoke tests.

## Current state

**SOURCE CHANGE THIS RUN: 1 KNOWLEDGE ARTICLE MATERIALLY DEEPENED + REGISTRY SYNCED.**

**KNOWLEDGE: 10/10 PUBLISH-READY.**

**STRUCTURAL IMPORTER BODY SYNC: FIXED IN SOURCE.**

**CI FOR `f248aa495016d0462de9036e9cb94493f5378a4a`: PASS.**

**PRODUCT DETAIL COPY / PRODUCT TRUTH / PRODUCT MEDIA MAPPING: UNCHANGED.**

**PENDING: FINAL REPORT-COMMIT CI, DEPLOYMENT, PRODUCTION URL SMOKE TEST AND ARTICLE MEDIA INVENTORY.**
