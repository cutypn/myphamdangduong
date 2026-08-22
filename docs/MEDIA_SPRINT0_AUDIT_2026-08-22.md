# MEDIA SPRINT 0 — RE-AUDIT AFTER DATA PASS

Date: 2026-08-22  
Branch: `agent/brz-40-media`  
Production base reviewed: `agent/ddg-media-importer` @ `5d83b8723e7db609a24aefe6c7f7820ff0d6d210`  
Data source: `agent/brz-30-data` commit `08fd7b7383ff45abba2aedfcef2ff72184fa8d33`  
Scope: media audit / handoff only. No theme change. No Product Truth change. No canonical identity change.

## 1. Data reconciliation

BRZ-30 PASS snapshot is authoritative for this media re-audit:

- Total unique IDs: **104**
- PUBLISH_ALLOWED: **35**
- Regulatory active: **35**
- `unknown + LEGAL_HOLD`: **69**
- PUBLISH_ALLOWED brands: **One Today 25 / Hatagold 9 / She One 1**
- IDs `100`, `101`, `102`, `103`, `104` are included.
- IDs `76`, `77`, `79`, `83`, `89` remain `CANDIDATE_MAPPING_REVIEW` by Data ownership and are not promoted by this audit.

The product-level registry is:
`apps/bizrise-ddg-media-importer/data/publish-allowed-media-audit-2026-08-22.psv`.

## 2. Featured Image gate — 35/35 classified

Strict statuses only:

| Status | Count |
|---|---:|
| `FEATURED_OK` | 0 |
| `MISSING_ASSET` | 30 |
| `CANDIDATE_MAPPING_REVIEW` | 5 |
| **Total** | **35** |

`FEATURED_OK = 0` is intentional: repository/static evidence cannot prove the current live WordPress attachment, dimensions, rights, Export for Web history, or manual Featured Image state.

### Candidate IDs retained

- **76** — `hatagold_anti_aging`: candidate filename exists in manifest vocabulary, but canonical title differs from legacy target and Data records DUP-CAND-01 with legacy ID 85.
- **77** — `hatagold_sunscreen_10g`: filename suggests 10g, but runtime attachment/source and canonical isolation from legacy ID 86 are not proven.
- **79** — `hatagold_dark_spots`: manifest legacy identity contains wording not identical to canonical title; Data records DUP-CAND-03 with legacy IDs 80/90.
- **83** — `hatagold_serum`: candidate target `Serum Nám Trắng Da` is materially shorter than canonical Product Truth identity.
- **89** — `hatagold_sunscreen`: candidate filename does not prove the canonical 50g pack and can overlap a sunscreen product family.

No candidate is promoted to deterministic without proving canonical title, brand, pack/size, exact attachment/source, and non-overlap with legacy identities.

### New overlay-only IDs

IDs **100–104** are fully present in the registry and currently classified:

- 100 — `MISSING_ASSET`
- 101 — `MISSING_ASSET`
- 102 — `MISSING_ASSET`
- 103 — `MISSING_ASSET`
- 104 — `MISSING_ASSET`

ID 104 must **not** be mapped to `hatagold_lotus_melasma`; equivalence is not proven.

## 3. Importer static audit

`apps/bizrise-ddg-media-importer/` remains deterministic in intent:

- no fuzzy product matching;
- exact title/canonical identity fallback + brand guard;
- existing valid Featured Image is preserved;
- importer-managed attachments use metadata for reuse;
- report includes missing products/assets and ambiguous matches.

Static limitation: the branch does not contain `apps/bizrise-ddg-media-importer/assets/media/`, so bundled `import_asset()` cannot resolve a new binary file on this branch. Current behavior depends primarily on first-party attachments already present in the WordPress Media Library.

The importer CLI `products_total` / `audit_products()` covers all recognized product post types, not only PUBLISH_ALLOWED records. Therefore Product Truth reconciliation must remain a separate gate around the 35 IDs.

No importer code is changed in this batch because no runtime bug was proven.

## 4. External media hotfix audit

`apps/bizrise-ddg-media-hotfix/` can discover/sideload external catalogue images for configured categories including One Today and She One. It does not currently configure Hatagold as an external category.

A successful download is **not** production approval.

Any external hotfix attachment remains not approved until all are verified:

1. source page and source image;
2. usage rights;
3. exact canonical product identity;
4. brand;
5. pack/size where identity depends on it;
6. dimensions/aspect ratio;
7. Photoshop `Export for Web` compliance;
8. canonical ALT.

Until those gates pass, the product remains `MISSING_ASSET` or `CANDIDATE_MAPPING_REVIEW`.

## 5. Runtime WordPress / Media Library

Runtime commands requested:

```bash
wp bizrise ddg-media
wp bizrise ddg-media --apply
wp bizrise ddg-media
```

**Result in this re-audit: `NOT_RUN_NO_WP_RUNTIME`.**

The available project connection exposes GitHub repository operations but no connected cPanel/SSH/WordPress runtime. No fake WP-CLI result is recorded.

Required runtime report once executed in the actual WordPress environment:

| Metric | Result |
|---|---|
| PUBLISH_ALLOWED total | expected 35 from Product Truth; runtime reconciliation required |
| matched products | NOT_RUN |
| missing products | NOT_RUN |
| missing assets | NOT_RUN |
| ambiguous matches | NOT_RUN |
| manual featured images skipped | NOT_RUN |
| attachments reused | NOT_RUN |
| attachments imported | NOT_RUN |
| second-run duplicate attachments | NOT_RUN |
| manual image overwrite | NOT_RUN |
| Product Truth mutation | NOT_RUN |

Acceptance after apply:

- second run imports **0 duplicate attachments**;
- no valid manual Featured Image is overwritten;
- Product Truth fields/titles are unchanged;
- no product crosses identity/brand/pack boundaries;
- runtime state is written back into this audit before production handoff.

## 6. P0 hero/banner audit

Strict web-ready coverage requires verified ratio + controlled source/rights + project Export for Web history.

| Surface | Desktop 16:9 | Mobile 9:16 | Static evidence | Web-ready |
|---|---|---|---|---|
| Homepage | MISSING | MISSING | no dedicated manifest asset | NO |
| Năng lực | CANDIDATE / ratio unverified | MISSING | `factory_front` targets `nang-luc` | NO |
| Factory | CANDIDATE / ratio unverified | MISSING | `factory_aerial` targets factory slugs | NO |
| R&D | MISSING | MISSING | no dedicated manifest asset | NO |
| OEM/ODM | MISSING | MISSING | no dedicated manifest asset | NO |
| Brand hub | MISSING | MISSING | no dedicated manifest asset | NO |
| One Today | CANDIDATE / ratio unverified | MISSING | `onetoday_brand_banner` | NO |
| Hatagold | CANDIDATE / ratio unverified | MISSING | `hatagold_brand_banner` | NO |
| She One | MISSING | MISSING | no dedicated manifest asset | NO |

PUBLISH_ALLOWED Product Truth contains only One Today, Hatagold and She One as product-bearing active brands in this snapshot. No additional active brand is inferred.

Coverage:

- Desktop **WEB_READY**: **0/9**
- Desktop static candidate: **4/9**
- Mobile **WEB_READY 9:16**: **0/9**

A desktop candidate is never counted as a mobile asset.

## 7. Product media ratio / gallery

For all 35 PUBLISH_ALLOWED products:

- Gallery: no controlled gallery manifest is present → `MISSING_ASSET`.
- Product desktop/main asset must be verified **1:1 or 3:4**.
- Candidate files for IDs 76/77/79/83/89 have **unverified ratio**, so none are `WEB_READY`.
- Dedicated mobile storytelling asset is absent for all 35 in the current importer audit.
- No desktop crop is accepted as a mobile 9:16 deliverable.

## 8. ALT audit

Rules applied:

- product image ALT must reflect the **canonical Product Truth product**, not a legacy identity;
- meaningful content media needs descriptive ALT;
- decorative media uses `alt=""`;
- no keyword stuffing.

Because no product asset is production-approved in static evidence, the registry stores the required canonical ALT target as `REQUIRES_CANONICAL_ALT:<Canonical Product>`. Existing legacy/generic manifest ALT is not treated as final approval for candidate products.

## 9. Photoshop Export for Web gate

No new binary media was created in this batch.

Current strict status:

- Product assets WEB_READY: **0/35 proven**
- P0 hero/banner WEB_READY desktop: **0/9 proven**
- P0 hero/banner WEB_READY mobile: **0/9 proven**

Any new/replacement image must follow:

`Photoshop → Export for Web → web asset → CMS`

Do not mark `WEB_READY` when only PSD/TIFF/master/raw exists, ratio is unverified, source/rights is unverified, or Export for Web cannot be evidenced.

## 10. Production media handoff status

Static re-audit is complete against Data PASS `08fd7b7…`.

Current blockers before BRZ-40 can claim production media completeness:

1. WordPress Media Library dry-run/runtime audit has not been executed.
2. 30 products have no controlled deterministic asset in repo audit.
3. 5 Hatagold products remain candidate-only.
4. No controlled product gallery coverage.
5. P0 hero desktop strict WEB_READY coverage is 0/9.
6. P0 hero mobile 9:16 coverage is 0/9.
7. Export for Web / ratio / rights evidence is not established for static candidates.

This batch is ready for **BRZ-80 re-audit of the media audit artifact**, but it is not a declaration that production media is complete.
