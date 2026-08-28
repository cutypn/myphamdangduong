# DDG Product Recovery — latest

## Verdict

- Source/CI: PASS.
- Branch: `codex/rebuild-v2`.
- HEAD checked: `36c17c83f8b8937606f9dfb37a91eff9ca5fad67` (`docs(frontend): record responsive homepage media fix`).
- Validate run `33142118240`: SUCCESS.
- Release run `33142118201`: SUCCESS.
- Validate confirms PHP syntax, deployment shell syntax, Product Truth seed, controlled 44-SKU media manifest, JSON and publish-ready article data all PASS.
- Production catalog/runtime/media inventory on this HEAD: CHƯA XÁC MINH from this execution environment.
- Product state changes this run: **0**.
- Controlled SKU state remains protected; no guessed mapping, guessed media assignment, fuzzy mapping, hard delete, mass draft or mass publish was performed.

## Last verified production evidence

| Metric | Last verified value |
|---|---:|
| Woo/public audit total | 59 |
| Controlled manifest | 44 |
| Controlled matched | 44 |
| Already exact Featured Image | 42 |
| Featured Image repaired | 2 |
| Controlled wrong Featured Image | 0 |
| Controlled product not found | 0 |
| Controlled product ambiguous | 0 |
| Controlled poster missing | 0 |
| Controlled poster ambiguous | 0 |
| Errors | 0 |
| Public rows missing Featured Image | 22 |

The 22 missing-image rows remain non-controlled candidates until live product inventory proves exact identity, duplicate/legacy/obsolete status and any canonical replacement.

## Publication policy

Product Truth currently establishes no record as eligible for new publication: `publish_allowed=true` is 0/26. One record has explicit regulatory HOLD and the remaining records are unknown/partial. No unknown or HOLD record is exposed by this recovery run.

## Current audit gate

When production access is available, inspect:

- `/wp-json/bizrise-deploy/v1/status`
- `/wp-json/bizrise-ddg/v1/runtime-status`
- `/wp-json/bizrise-ddg/v1/media-inventory?scope=products&per_page=100`
- `/san-pham/`

For every public WooCommerce product, verify canonical ID, slug, title, brand/category, pack identity, publish/catalog state and Featured Image ID/file/URL/ALT/dimensions. Controlled products must resolve to the exact manifest poster and must not have a duplicate canonical storefront row. Non-controlled rows may only be reclassified when exact identity evidence and a canonical replacement are available.

## Before / after — this run

| Check | Before | After |
|---|---:|---:|
| Controlled manifest rows | 44 | 44 |
| Controlled matched (last verified production evidence) | 44 | 44 |
| Controlled wrong Featured Image | 0 | 0 |
| Product records with status changed by this run | 0 | 0 |
| Product media assignments changed by this run | 0 | 0 |
| Product IDs draft/trash by this run | none | none |
| Hard-deleted products/media/database rows | 0 | 0 |

## Production verification attempt — 2026-08-28 11:32 ICT

Attempts to obtain fresh production evidence did not return a trustworthy application payload. Web discovery returned no indexed REST result, direct exact-URL opening was unavailable through the safe URL gate, and the direct execution-environment HTTP probe failed before returning an application response. Source/CI therefore must not be treated as production evidence.

Still CHƯA XÁC MINH:

- deployed SHA;
- live `/san-pham/` product count and whether the public catalog is non-empty on this HEAD;
- runtime catalog counters;
- full public Woo product inventory;
- current missing Featured Image IDs;
- duplicate Featured Image attachment groups;
- exact canonical classification of non-controlled candidates;
- live ALT and image dimensions for all public rows.

## Media policy

Prefer first-party Media Library assets. Preserve exact controlled poster identity. ALT must describe the actual media without adding unsupported claims. Do not crop away product identity or pack information. Mobile assets should use 9:16 when the placement requires vertical media; desktop placements should use 16:9, 1:1 or 3:4 according to the actual component. For web export optimization, record a Photoshop Export-for-Web requirement only when needed; no Photoshop execution is claimed in this run.

## Rollback policy

No rollback is needed for this run because no product or media state changed. Any future deterministic cleanup must record product IDs, previous status, new status, canonical replacement and evidence source before applying draft/trash. Unknown/unmapped rows remain untouched and hard deletion is prohibited.

## Next safe action

Once production is reachable: confirm deployed SHA and catalog health first; then audit every public Woo row against the 44-row manifest and Product Truth, enumerate missing/duplicate media usage, classify non-controlled rows from exact evidence, and apply only reversible status changes with IDs and before/after counts recorded here. Controlled 44 SKU must remain intact and `/san-pham/` must remain non-empty throughout cleanup.
