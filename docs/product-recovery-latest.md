# DDG Product Recovery — latest

## Verdict

- Source/CI: PASS.
- Branch: `codex/rebuild-v2`.
- HEAD checked: `70067bbb90e8051fe0815e56527d1611c5de5790` (`docs(frontend): record responsive nav JS fix`).
- Validate run `33139170151`: SUCCESS.
- Release run `33139170190`: SUCCESS.
- Production catalog/runtime/media inventory on this HEAD: CHƯA XÁC MINH from this execution environment.
- Product state changes this run: **0**.
- Controlled SKU state remains protected; no guessed mapping or guessed media assignment was performed.

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

The 22 missing-image rows remain non-controlled candidates until the live product inventory proves their exact identity and relationship to a canonical storefront row.

## Publication policy

Product Truth currently establishes no record as eligible for new publication: `publish_allowed=true` is 0/26. One record has explicit regulatory HOLD and the remaining records are unknown/partial. No unknown or HOLD record is exposed by this recovery run.

## Current audit gate

When production access is available, inspect:

- `/wp-json/bizrise-deploy/v1/status`
- `/wp-json/bizrise-ddg/v1/runtime-status`
- `/wp-json/bizrise-ddg/v1/media-inventory?scope=products&per_page=100`
- `/san-pham/`

For every public WooCommerce product, verify ID, slug, title, brand/category, pack identity and Featured Image ID/file/URL/ALT/dimensions. Controlled products must resolve to the exact manifest poster. Non-controlled rows may only be reclassified when exact identity evidence and a canonical replacement are available.

## Before / after — this run

| Check | Before | After |
|---|---:|---:|
| Controlled manifest rows | 44 | 44 |
| Controlled matched (last verified production evidence) | 44 | 44 |
| Controlled wrong Featured Image | 0 | 0 |
| Product records with status changed by this run | 0 | 0 |
| Product media assignments changed by this run | 0 | 0 |
| Product IDs changed by this run | none | none |

## Production verification attempt — 2026-08-28 10:35 ICT

The available web path returned no usable fresh production payload for the catalog/runtime/media endpoints, and a direct execution-environment HTTP probe failed before returning a trustworthy application response. Therefore the run does not infer production health from source or CI.

Still CHƯA XÁC MINH:

- deployed SHA;
- live `/san-pham/` product count;
- runtime catalog counters;
- full public Woo product inventory;
- current missing Featured Image IDs;
- duplicate Featured Image attachment groups;
- exact classification of non-controlled candidates.

## Media policy

Prefer first-party Media Library assets. Preserve exact controlled poster identity. ALT must describe the actual media without adding unsupported claims. Do not crop away product identity or pack information. For web export optimization, record a Photoshop Export-for-Web requirement only when needed; no Photoshop execution is claimed in this run.

## Next safe action

Once production is reachable: confirm deployed SHA and catalog health first; then audit all public Woo rows against the 44-row manifest and Product Truth, enumerate missing/duplicate media usage, classify non-controlled rows from exact evidence, and apply only reversible status changes with product IDs and before/after counts recorded here.
