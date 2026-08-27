# DDG Product Recovery — latest

## Current verdict

- **SOURCE / CI: PASS**
- **CATALOG VISIBILITY SAFETY: PASS in source + CI**
- **PUBLIC HOLD EXCLUSION: PASS in source + CI**
- **CONTROLLED 44-SKU MEDIA: PASS by last verified production evidence**
- **LIVE CATALOG RUNTIME COUNTERS: implemented + CI PASS**
- **PRODUCTION ON CURRENT HEAD: CHƯA XÁC MINH**

The public storefront remains WooCommerce `post_type=product`. Internal Product Truth `bizrise_product` stays non-public/non-queryable and must not own `/san-pham/`.

## Current Git / CI

- Branch: `codex/rebuild-v2`
- Current HEAD observed before this report refresh: `b22a9faadd2be593bd5e0d8d0b21e9f88e0a6827`
- Commit: `fix(catalog): enforce legal HOLD exclusion in fallback query`
- Product-impact assessment: **positive P0 storefront safety fix** — fallback `/san-pham/` now excludes Woo rows carrying `_bizrise_legal_hold=1`; no Product Truth publication, media reassignment, deletion or fuzzy mapping is introduced.
- Validate Bizrise DDG V2 run `33101670801`: **SUCCESS**.
- Build Bizrise DDG V2 Release run `33101670715`: **SUCCESS**.
- Existing canonical Woo `exclude-from-catalog` fallback handling, related-product visibility fix and storefront-wide direct single-product HOLD protection remain in validated source.
- `MediaInventory` remains implemented and registered in migrator source.
- `RuntimeStatus` remains implemented with `catalog_runtime` for published/visible/HOLD/excluded/shop-page counters.

## Last verified production evidence

The last production payload supplied from production reported deployed SHA `1349bdfdb2860820945d27149f0632eff9f482fc` at that time.

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

## Current media-inventory capability

Public read-only endpoint in source:

`/wp-json/bizrise-ddg/v1/media-inventory?scope=products&per_page=100`

For every public WooCommerce product it returns ID, slug, title, URL, `product_cat`, Featured Image attachment ID, filename, URL, ALT, width, height, MIME and `missing_featured`. Summary also reports public-product count, missing-featured IDs, duplicate Featured Image usage, library image count and orphan image count.

Runtime triage endpoint in source:

`/wp-json/bizrise-ddg/v1/runtime-status`

The runtime payload exposes live Woo catalog counts so recovery can distinguish total published Woo rows, actually catalog-visible rows, explicit legal HOLD rows, Woo `exclude-from-catalog` rows and shop-page route state.

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
| Woo `exclude-from-catalog` handling in fallback | localized/name lookup could silently fail | canonical slug lookup; CI PASS |
| Legal HOLD handling in fallback | HOLD row could remain visible if fallback query bypassed main Woo query gates | explicit `_bizrise_legal_hold != 1` exclusion; CI PASS |
| Related-product visibility on single product | custom query could re-show excluded products | canonical `exclude-from-catalog` exclusion; CI PASS |
| Controlled manifest mapping | unresolved | **44 / 44 matched** |
| Controlled wrong Featured Image | unresolved | **0** |
| Product/poster ambiguity | unresolved | **0** |
| Product/poster missing in controlled manifest | unresolved | **0** |
| Unmanaged public missing Featured Image | mixed into global repair gate | separated; last known **22** |
| Product media inventory | unavailable | endpoint implemented and registered |
| Runtime catalog observability | no direct Woo catalog-health counters | **`catalog_runtime` implemented; CI PASS** |
| Public legal HOLD exclusion | incomplete fallback protection | archive/search/direct single/fallback protection in source + CI |
| Current HEAD CI | stale | **Validate PASS + Release PASS** on `b22a9faa…` |
| Current HEAD production deploy | unknown | **CHƯA XÁC MINH** |

## Production verification gate

Do not mark recovery complete until production confirms current validated HEAD or descendant via:

- `/wp-json/bizrise-deploy/v1/status`
- `/wp-json/bizrise-ddg/v1/runtime-status`
- `/wp-json/bizrise-ddg/v1/media-inventory?scope=products&per_page=100`

Required PASS evidence:

- deployed SHA equals current validated HEAD/descendant;
- `repair.controlled_media_clean=true`;
- controlled public media problem IDs are empty;
- 44 controlled SKU mapping remains exact;
- `catalog_runtime.available=true`;
- `catalog_runtime.public_catalog_visible` is consistent with live catalog and excludes legal HOLD / Woo hidden rows;
- no Product Truth HOLD/draft record is newly exposed;
- WooCommerce products marked `exclude-from-catalog` are absent from fallback `/san-pham/` and custom related-product cards;
- Woo rows with `_bizrise_legal_hold=1` are absent from archive/search/fallback and not publicly reachable as direct product pages;
- every unmanaged missing-image row is classified with deterministic metadata before any status/media change.

## Blocker this run

Production verification remains blocked from this execution environment. Direct retrieval of `dangduonggroup.com` and its REST endpoints could not be established, so current deployed SHA, live `catalog_runtime`, live product inventory, duplicate Featured Image groups, current missing-image IDs and storefront counts remain **CHƯA XÁC MINH**.

No destructive or guessed recovery action was taken in this run.

Next safe action: read live production deploy/runtime/media inventory when reachable, classify unmanaged rows deterministically, then apply only exact non-destructive fixes.
