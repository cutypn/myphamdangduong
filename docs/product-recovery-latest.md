# DDG Product Recovery — latest

## Current verdict

**SOURCE / CI: PASS**

**CONTROLLED 44-SKU MEDIA: PASS by last verified production evidence**

**PRODUCTION ON CURRENT HEAD: CHƯA XÁC MINH**

The public storefront remains WooCommerce `post_type=product`. Internal Product Truth `bizrise_product` is non-public/non-queryable and must not own `/san-pham/`.

## Current Git / CI

- Branch: `codex/rebuild-v2`
- Current HEAD observed: `727f8f26c3642f18a53afe45b2ebb58f0291c9c3`
- Commit: `chore(migrator): trigger site architecture import`
- Change scope: migrator version bump `0.4.1` → `0.4.2` only; no product mapping, publication status or media mutation in this HEAD.
- Validate Bizrise DDG V2 run `33048457572`: **SUCCESS**.
- Build Bizrise DDG V2 Release run `33048457610`: **SUCCESS**.
- Migrator version: `0.4.2`.
- `MediaInventory` remains loaded and registered in the migrator source.

## Last verified production evidence

The last production runtime payload supplied from production reported deployed SHA `1349bdfdb2860820945d27149f0632eff9f482fc` with Bridge status `up_to_date` at that time.

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

This proves the deterministic 44-row media manifest was fully matched and had no wrong Featured Image at that verified runtime. The 22 missing-image public rows therefore remain unmanaged/legacy candidates until deterministic evidence proves otherwise.

## Current storefront audit design

`StorefrontProductAudit` separates controlled media integrity from unmanaged WooCommerce storefront warnings:

- storefront source: WooCommerce `post_type=product` only;
- controlled clean gate: 44 manifest rows, 44 matched, no errors/not-found/ambiguity/poster/wrong-featured problems;
- unmanaged missing Featured Image rows are reported separately;
- internal/legacy `bizrise_product` / `ddg_product` public counts are reported separately and do not pollute Woo storefront totals;
- no fuzzy mapping, guessed poster assignment, mass drafting or destructive cleanup is allowed.

## Media inventory endpoint

Source provides public read-only endpoint:

`/wp-json/bizrise-ddg/v1/media-inventory`

Required product audit call after deployment:

`?scope=products&per_page=100`

For every public WooCommerce product the endpoint exposes:

- ID, slug, title, URL;
- `product_cat` labels;
- Featured Image attachment ID;
- filename and public URL;
- ALT;
- width / height / MIME;
- `missing_featured` flag.

Summary also exposes public product count, product missing-featured IDs, duplicate Featured Image usage, library image count and orphan image count.

## Deterministic policy for unmanaged / legacy rows

For each public product outside the controlled 44-SKU manifest:

1. Read exact ID, slug, title, brand/category/source metadata and Featured Image.
2. If deterministic metadata proves it maps to a controlled SKU, reconcile only to the exact canonical record/poster.
3. If deterministic metadata proves it is an exact duplicate/legacy row, preserve the record for history/rollback and remove duplicate storefront exposure only through an approved non-destructive status change.
4. If it is outside the manifest, do not invent mapping, poster, regulatory eligibility or marketing claims.
5. Missing image alone is not evidence that a WooCommerce product should be drafted.
6. Never delete legacy data as part of recovery.

## Product Truth publication policy

The current Product Truth verification seed contains 26 records and does not establish current publish eligibility for any record:

- `publish_allowed=true`: **0**
- `publish_allowed=false`: **26**
- explicit regulatory HOLD: **1** (`havigold-serum-nam-trang-da-18g`)
- remaining 25: unknown / partial verification

Therefore Product Truth must not mass-draft or mass-publish the existing WooCommerce catalog until deterministic Woo mapping and an approved publication policy exist.

## Before / after

| Check | Before recovery | Current source / last verified evidence |
|---|---|---|
| `/san-pham/` ownership | route collision possible with internal Product Truth CPT | WooCommerce is the only intended public catalog route |
| Controlled manifest mapping | incident state unclear | **44 / 44 matched** |
| Controlled wrong Featured Image | unresolved incident | **0** |
| Product/poster ambiguity | unresolved incident | **0** |
| Product/poster missing in controlled manifest | unresolved incident | **0** |
| Unmanaged public missing Featured Image | mixed into global repair gate | reported separately; last known candidate count **22** |
| Product media inventory | unavailable | source endpoint implemented and registered |
| Current HEAD CI | previous report stale | **Validate PASS + Release PASS** on `727f8f26…` |
| Current HEAD product mutation | unknown | **none in HEAD; version bump only** |
| Current HEAD production deploy | unknown | **CHƯA XÁC MINH** |

## Production verification gate

Do not mark this recovery fully complete until production confirms the current HEAD or a validated descendant via both:

- `/wp-json/bizrise-deploy/v1/status`
- `/wp-json/bizrise-ddg/v1/runtime-status`

and media inventory can be read from:

- `/wp-json/bizrise-ddg/v1/media-inventory?scope=products&per_page=100`

Required PASS evidence:

- `deployed_sha` equals current validated HEAD/descendant;
- `repair.controlled_media_clean=true`;
- `storefront_audit.controlled_public_media_problem_ids=[]`;
- 44 controlled SKU mapping remains exact;
- no Product Truth HOLD/draft record is newly exposed by recovery;
- every remaining unmanaged missing-image row is reported with deterministic metadata before any status/media change.

## Blocker this run

The QA environment still cannot resolve/fetch the production REST endpoints reliably (`dangduonggroup.com` DNS resolution fails from this runtime; web fetch also cannot open unindexed REST URLs). Therefore current deployed SHA and live media-inventory rows are **CHƯA XÁC MINH** here.

No destructive product status or media mutation is justified without that production evidence. The correct next action remains: read the live media inventory after Bridge deploy, classify every unmanaged row deterministically, then only apply exact non-destructive fixes backed by evidence.
