# DDG Product Recovery — latest

## Current verdict

**SOURCE / CI: PASS**

**PUBLIC HOLD EXCLUSION: PASS in source + CI**

**CONTROLLED 44-SKU MEDIA: PASS by last verified production evidence**

**PRODUCTION ON CURRENT HEAD: CHƯA XÁC MINH**

The public storefront remains WooCommerce `post_type=product`. Internal Product Truth `bizrise_product` is non-public/non-queryable and must not own `/san-pham/`.

## Current Git / CI

- Branch: `codex/rebuild-v2`
- Current HEAD observed before this report refresh: `cc70e72a06f6dd188b605ee5cc70776729e59373`
- Commit: `fix(catalog): harden public HOLD product exclusion`
- Theme version bumped to `2.1.8`.
- The current change adds a public WooCommerce safety gate for `_bizrise_legal_hold=1`: public archive/search/product queries exclude HOLD products, and a direct public single-product request is forced to 404. Editors with product-edit capability retain preview access.
- Validate Bizrise DDG V2 run `33070258354`: **SUCCESS**.
- Build Bizrise DDG V2 Release run `33070258335`: **SUCCESS**.
- Validate workflow covers PHP 8.2 syntax for theme/migrator/core, deployment shell syntax, Product Truth seed validation, and JSON validation for Product Truth, brand profile, media manifest and article registry.
- `MediaInventory` remains implemented and registered in migrator source.

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

This proves the deterministic 44-row media manifest was fully matched and had no wrong Featured Image at the last verified runtime. The 22 missing-image public rows remain unmanaged/legacy candidates until deterministic live inventory proves otherwise.

## Current media-inventory capability

Public read-only endpoint in source:

`/wp-json/bizrise-ddg/v1/media-inventory?scope=products&per_page=100`

For every public WooCommerce product it returns ID, slug, title, URL, `product_cat`, Featured Image attachment ID, filename, URL, ALT, width, height, MIME and `missing_featured`. Summary also reports public-product count, missing-featured IDs, duplicate Featured Image usage, library image count and orphan image count.

## Deterministic policy for unmanaged / legacy rows

1. Read exact ID, slug, title, category and Featured Image from live inventory.
2. Map only when exact deterministic metadata proves the controlled SKU relationship.
3. If exact duplicate/legacy is proven, preserve record history and remove duplicate storefront exposure only via approved non-destructive status change.
4. If outside manifest, do not invent poster, Product Truth mapping, regulatory eligibility or marketing claims.
5. Missing image alone is not evidence for drafting a product.
6. Never delete legacy data during recovery.

## Product Truth publication policy

Current verification seed does not establish publish eligibility for any of its 26 records:

- `publish_allowed=true`: **0**
- `publish_allowed=false`: **26**
- explicit regulatory HOLD: **1** (`havigold-serum-nam-trang-da-18g`)
- remaining 25: unknown / partial verification

Therefore Product Truth must not mass-draft or mass-publish the existing WooCommerce catalog until deterministic Woo mapping and an approved publication policy exist. The current theme safety gate additionally prevents any WooCommerce product explicitly carrying `_bizrise_legal_hold=1` from being publicly exposed through catalog/search/direct single-product access.

## Before / after

| Check | Before recovery | Current source / last verified evidence |
|---|---|---|
| `/san-pham/` ownership | route collision possible | WooCommerce intended as only public catalog route |
| Controlled manifest mapping | unresolved | **44 / 44 matched** |
| Controlled wrong Featured Image | unresolved | **0** |
| Product/poster ambiguity | unresolved | **0** |
| Product/poster missing in controlled manifest | unresolved | **0** |
| Unmanaged public missing Featured Image | mixed into global repair gate | separated; last known **22** |
| Product media inventory | unavailable | endpoint implemented and registered |
| Public legal HOLD exclusion | no explicit storefront-wide theme gate | **archive/search excluded + direct single returns 404 in source** |
| Current HEAD CI | stale | **Validate PASS + Release PASS** on `cc70e72a…` |
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
- no Product Truth HOLD/draft record is newly exposed;
- direct public access to the explicit HOLD-mapped product is not publicly reachable if the Woo row carries `_bizrise_legal_hold=1`;
- every unmanaged missing-image row is classified with deterministic metadata before any status/media change.

## Blocker this run

Production verification remains blocked from this execution environment. Direct HTTPS fetches to `dangduonggroup.com` fail DNS resolution here, while web retrieval cannot discover/open the REST URLs from search results. Therefore current deployed SHA, live product inventory, duplicate Featured Image groups and the live 59-product/legacy counts remain **CHƯA XÁC MINH**.

No fuzzy mapping, guessed image assignment, mass draft, mass publish or deletion was performed. The next safe action is to read live production runtime/media inventory when DNS/REST becomes reachable, classify unmanaged rows deterministically, then apply only exact non-destructive fixes.