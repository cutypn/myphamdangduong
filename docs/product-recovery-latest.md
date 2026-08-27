# DDG Product Recovery — latest

## Current verdict

- **SOURCE / CI: PASS**
- **CATALOG VISIBILITY SAFETY: PASS in source + CI**
- **PUBLIC HOLD EXCLUSION: PASS in source + CI**
- **CONTROLLED 44-SKU MEDIA: PASS by last verified production evidence**
- **CONTROLLED REPAIR GATE: FIXED in source + CI** — unmanaged/legacy public products missing Featured Image remain observable but no longer force the deterministic 44-SKU repair to rerun.
- **LIVE PRODUCT INVENTORY: CHƯA XÁC MINH trong run này do DNS production không resolve**
- **PRODUCTION ON CURRENT HEAD: CHƯA XÁC MINH**

Public storefront remains WooCommerce `post_type=product`. Internal Product Truth `bizrise_product` remains non-public/non-queryable and must not own `/san-pham/`.

## Current Git / CI

- Branch: `codex/rebuild-v2`
- Current HEAD before this report refresh: `3b122953c4e62c451b5c514a56e898697be221bb`
- Commit: `docs: update content publish and media QA state`
- Product-impact assessment: **none** — current HEAD only refreshes content/media QA documentation and does not change WooCommerce mapping, Product Truth, Featured Image assignment, HOLD handling, catalog visibility or product publish state.
- Validate Bizrise DDG V2 run `33122718665`: **SUCCESS**.
- Build Bizrise DDG V2 Release run `33122718730`: **SUCCESS**.
- CI validation therefore covers the repository PHP lint / JSON / data checks configured by `validate-v2.yml` on this HEAD.
- No fuzzy mapping, guessed image assignment, product deletion, mass draft or mass publish was performed.

## Last verified production evidence

Last verified production payload reported deployed SHA `1349bdfdb2860820945d27149f0632eff9f482fc` at that time.

| Metric | Last verified value |
|---|---:|
| Woo/public legacy audit total | 59 |
| Controlled manifest | 44 |
| Controlled matched | 44 |
| Already exact featured | 42 |
| Featured repaired | 2 |
| Controlled public wrong featured | 0 |
| Product not found | 0 |
| Product ambiguous | 0 |
| Poster missing | 0 |
| Poster ambiguous | 0 |
| Errors | 0 |
| Global public missing featured | 22 |

The deterministic 44-row media manifest was fully matched and had no wrong Featured Image at the last verified runtime. The 22 missing-image public rows remain unmanaged/legacy candidates until deterministic live inventory proves otherwise.

## Media inventory / runtime endpoints

- `/wp-json/bizrise-ddg/v1/media-inventory?scope=products&per_page=100`
- `/wp-json/bizrise-ddg/v1/runtime-status`
- `/wp-json/bizrise-deploy/v1/status`

The product inventory returns public WooCommerce product ID, slug, title, URL, product categories and Featured Image metadata including attachment ID, filename, URL, ALT, width, height, MIME and missing-featured state; summary includes missing IDs and duplicate Featured Image usage.

## Product Truth publication policy

Current verification seed does not establish publish eligibility for any of its 26 records:

- `publish_allowed=true`: **0**
- `publish_allowed=false`: **26**
- explicit regulatory HOLD: **1** (`havigold-serum-nam-trang-da-18g`)
- remaining 25: unknown / partial verification

No Product Truth HOLD/unknown/unverified record should be newly exposed. No fuzzy mapping, guessed image assignment, mass draft, mass publish or deletion is allowed.

## Before / after

| Check | Before recovery | Current source / last verified evidence |
|---|---|---|
| `/san-pham/` ownership | route collision possible | WooCommerce intended as only public catalog route |
| Woo `exclude-from-catalog` handling | custom/fallback leakage possible | canonical exclusion in fallback + related products; CI PASS |
| Legal HOLD public exposure | incomplete protection possible | archive/search/direct single/fallback protection; CI PASS |
| Controlled manifest mapping | unresolved | **44 / 44 matched** |
| Controlled wrong Featured Image | unresolved | **0** |
| Product/poster ambiguity | unresolved | **0** |
| Product/poster missing in controlled manifest | unresolved | **0** |
| Unmanaged public missing Featured Image | mixed into controlled clean gate | still reported; last known **22**, but no longer retriggers controlled repair |
| Product media inventory | unavailable | endpoint implemented; full live payload pending production reachability |
| Runtime catalog observability | no direct counters | `catalog_runtime` implemented |
| Current code HEAD CI | unknown | **Validate PASS + Release PASS** on `3b122953c…` |
| Current HEAD production deploy | unknown | **CHƯA XÁC MINH** |

## Production verification attempt — 2026-08-28 06:08 ICT

This run attempted direct DNS/HTTP access to:

- `https://dangduonggroup.com/wp-json/bizrise-ddg/v1/media-inventory?scope=products&per_page=100`
- `https://dangduonggroup.com/wp-json/bizrise-ddg/v1/runtime-status`

Production DNS failed to resolve from the execution environment (`Temporary failure in name resolution`), so no fresh production payload could be obtained.

Therefore the following remain **CHƯA XÁC MINH** on current HEAD:

- deployed SHA;
- live `catalog_runtime` counters;
- full public Woo product inventory;
- current missing Featured Image IDs;
- duplicate Featured Image groups;
- exact state of the 22 unmanaged/legacy missing-image candidates.

No destructive, fuzzy, guessed or publication-changing recovery action was taken.

## Next safe action

When production DNS is reachable, read deploy status + runtime + product inventory, confirm the validated SHA/descendant is deployed, audit all public Woo products against the 44-row manifest and Product Truth, then classify unmanaged missing-image rows deterministically. Apply only exact non-destructive fixes. Do not fuzzy-map or publish Product Truth records without verified eligibility.
