# Architecture — Bizrise DDG V2

## North star

Readable source → deterministic data → controlled migration → testable release → reversible cutover.

## Runtime ownership

### `apps/bizrise-core`
Owns business data and validation:
- `bizrise_product` CPT
- brand/category/collection/concern/routine taxonomies
- Product Truth fields and provenance
- publication gate
- media relation metadata
- SEO/AI-search fields
- reusable APIs

### `apps/bizrise-ddg-theme`
Presentation only:
- semantic templates
- Be Vietnam Pro design system
- responsive layout
- navigation and frontend interaction
- no ownership of product truth

### `apps/bizrise-ddg-migrator`
Migration-only tool:
- import legacy/master candidates
- deterministic media mapping
- conflict reporting
- redirect candidates
- idempotent re-runs

It must not become a frontend runtime dependency.

## Canonical content model

- Product CPT: `bizrise_product`
- Rewrite: `/san-pham/`
- Taxonomies: `bizrise_brand`, `bizrise_product_category`, `bizrise_collection`, `bizrise_concern`, `bizrise_routine_type`

## Product publication contract

A product is publishable only when:
- `regulatory_status === active`
- `verification_status === verified`
- `legal_hold !== true`
- canonical product name exists
- brand exists
- size/pack exists when required
- provenance/source metadata exists

Missing fields are not inferred.

## Brand representation rule

Taxonomy/entity and packaging label are separate fields. Example: the Hatagold ecosystem may contain packaging labelled `HAVIGOLD`; V2 must preserve the packaging label rather than rewrite it for taxonomy consistency.

## Media mapping order

1. stable product ID / SKU
2. Product Master ID
3. exact source filename
4. exact normalized official product title
5. brand + normalized title
6. slug
7. fuzzy only as explicit low-confidence fallback with brand guard

Manual images are never overwritten by an automated rerun.

## Deployment

V2 deploys readable source to separate V2 target paths. It does not decode Base64/tar payloads and does not overwrite the legacy theme before release-candidate validation.
