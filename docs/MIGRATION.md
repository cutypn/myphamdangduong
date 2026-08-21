# Migration Plan — Legacy → Bizrise DDG V2

## Legacy reference

Repository: `cutypn/myphamdangduong`

Reference branch: `agent/ddg-media-importer`

Legacy is used to understand URLs, existing product IDs/slugs, media, content and migration behavior. It is not the V2 runtime architecture.

## Migration phases

### M0 — Audit
- inventory legacy theme/plugins/MU plugins
- export current URLs, post types, menu and settings
- inventory media and product IDs/slugs
- create redirect candidate map

### M1 — Product Master
Every candidate receives a verification state:
- verified → eligible for publication gate evaluation
- partial → draft
- unverified → hold/draft

Research rows are not automatically publishable SKUs.

### M2 — Product Truth correction first
Before UI migration:
- deduplicate Hatagold/HAVIGOLD variants
- preserve packaging label separately from brand taxonomy
- add verified Cream X2 records from supplied notification images
- add new Bạch Ngọc Lang candidates without overwriting older variants
- mark the HAVIGOLD `SERUM GIÚP MỜ NÁM TRẮNG DA` record as `hold` / not publishable because the Cần Thơ health authority withdrew notification receipt `307/26/CBMP-CT` by Decision `1226/QĐ-SYT` dated 2026-06-05
- keep source provenance for each status decision

### M3 — Media
Create deterministic manifest entries with:
- product ID/SKU
- exact source filename
- role (featured/cover/gallery/lifestyle)
- mapping confidence
- mapping version
- managed/manual flag

Low-confidence fuzzy matches are reported, never silently applied.

### M4 — Corporate/brand/product pages
Create V2 IA and render only verified facts.

### M5 — SEO migration
Owner URLs, canonical, redirects, one H1, breadcrumbs, schema and internal links.

### M6 — Staging QA
Desktop/tablet/mobile smoke tests and data/media reports.

### M7 — Reversible cutover
Backup DB/uploads/theme/plugin state, deploy V2 to separate target paths, activate only after validation, and retain rollback to legacy.

## Source precedence

1. current regulatory/authority decisions for regulatory state
2. supplied cosmetic notification documents/images for official name/packaging facts
3. official brand site for SKU/variant and supporting product facts
4. legacy Product Master for IDs/aliases/slugs and migration continuity
5. distributor/marketplace only for discovery; never as legal claim truth
