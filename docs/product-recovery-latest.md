# DDG Product Recovery — latest

## Current verdict

- **SOURCE / CI: PASS**
- **CATALOG VISIBILITY SAFETY: PASS in source + CI**
- **PUBLIC HOLD EXCLUSION: PASS in source + CI**
- **CONTROLLED 44-SKU MEDIA: PASS by last verified production evidence**
- **LIVE PRODUCT INVENTORY: CHƯA XÁC MINH trong run này do DNS production không resolve**
- **PRODUCTION ON CURRENT HEAD: CHƯA XÁC MINH**

Public storefront remains WooCommerce `post_type=product`. Internal Product Truth `bizrise_product` remains non-public/non-queryable and must not own `/san-pham/`.

## Current Git / CI

- Branch: `codex/rebuild-v2`
- Current HEAD before this report refresh: `6a40a5d25f8caf95a73690d4bf6395c564633580`
- Commit: `fix(release): align active smoke URLs with approved DDG architecture`
- Product-impact assessment: **none** — current HEAD changes release smoke URL coverage only; it does not alter WooCommerce mapping, Product Truth, media assignment, HOLD rules, visibility gates or publication state.
- Validate Bizrise DDG V2 run `33111340658`: **SUCCESS**.
- Build Bizrise DDG V2 Release run `33111340723`: **SUCCESS**.
- `MediaInventory` remains implemented and registered.
- `RuntimeStatus` remains implemented with `catalog_runtime` counters.

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
| Unmanaged public missing Featured Image | mixed into global repair gate | separated; last known **22** |
| Product media inventory | unavailable | endpoint implemented and registered |
| Runtime catalog observability | no direct counters | `catalog_runtime` implemented; CI PASS |
| Current code HEAD CI | unknown | **Validate PASS + Release PASS** on `6a40a5d2…` |
| Current HEAD production deploy | unknown | **CHƯA XÁC MINH** |

## Production verification attempt — 2026-08-28

This run attempted the live runtime and product inventory endpoints. Production DNS failed to resolve (`Temporary failure in name resolution`), so no fresh production payload could be obtained.

Therefore the following remain **CHƯA XÁC MINH** on current HEAD:

- deployed SHA;
- live `catalog_runtime` counters;
- full public Woo product inventory;
- current missing Featured Image IDs;
- duplicate Featured Image groups;
- exact state of the 22 unmanaged/legacy missing-image candidates.

No destructive or guessed recovery action was taken.

## Next safe action

When production DNS is reachable, read deploy status + runtime + product inventory, confirm the validated SHA/descendant is deployed, classify every unmanaged missing-image row deterministically, and apply only exact non-destructive fixes. Do not fuzzy-map or publish Product Truth records without verified eligibility.
