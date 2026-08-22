# DATA SPRINT 0 — PRODUCT TRUTH AUDIT

Date: 2026-08-22  
Branch: `agent/brz-30-data`  
Scope: `apps/bizrise-ddg-product-sync/` + repository-local media mapping evidence.

## Publish gate

A product is a publish candidate only when all three conditions are true:

1. `regulatory_status = active`
2. `verification_status` is a verified status (`VERIFIED_*`)
3. `content_gate = PUBLISH_ALLOWED`

Legacy names and legacy copy are identity/research inputs only. They are not approved marketing claims.

## Summary

| Metric | Count |
|---|---:|
| Total unique product IDs | 99 |
| Product Master rows | 93 |
| Product Truth verified rows | 30 |
| PUBLISH_ALLOWED | 30 |
| Regulatory hold | 0 |
| Regulatory unknown | 69 |
| Missing canonical identity | 69 |
| Missing verification source | 69 |
| Exact duplicate IDs | 0 |
| Exact canonical-name duplicates | 0 |
| Exact generated-slug duplicates | 0 |
| Duplicate/identity candidate groups | 3 groups / 7 rows |
| Missing SKU field | 99 |
| PUBLISH_ALLOWED with deterministic repo media mapping | 5 |
| PUBLISH_ALLOWED requiring runtime Media Library check | 25 |

## Duplicate / identity candidates

- `DUP-CAND-01`: IDs `76`, `85` — Hatagold anti-aging name family. ID 76 is verified; ID 85 remains legacy/unknown. The media manifest targets both titles to the same product asset family.
- `DUP-CAND-02`: IDs `77`, `86` — Hatagold sunscreen 10g name family. ID 77 is verified; ID 86 remains legacy/unknown. The media manifest targets both titles to the same 10g product asset.
- `DUP-CAND-03`: IDs `79`, `80`, `90` — Hatagold dark-spots name family. ID 79 is verified; IDs 80/90 remain legacy/unknown. The media manifest targets these legacy titles to the same product asset family.

No rows are auto-merged in Sprint 0. These remain review candidates until identity evidence is approved.

## Critical findings

1. Base Product Sync v1.1.0 used only `regulatory_status = active` for frontend gating. That is weaker than the Production V1 rule.
2. Base sync v1.1.0 overwrote truth metadata (`regulatory`, `verification`, `content_gate`) back to `unknown / NEED_VERIFY / LEGAL_HOLD` on a versioned re-sync. Because the truth overlay has its own independent version flag, it may not re-run afterward. This can regress previously verified records.
3. Base sync report counters did not reflect actual active/publish-allowed state.
4. Product Master has no SKU field. All 99 combined identities therefore lack a dedicated SKU value; master ID (`ddg-2026-NNN`) is only an internal identity key, not a verified SKU.
5. Legacy source URLs exist for 93 master rows, but they are not verification sources under Product Truth rules.
6. ID 82 `Nước Tẩy Trang` is categorized as `Tẩy tế bào chết` in legacy master data. It remains unknown/LEGAL_HOLD and requires category verification before canonicalization.
7. Repository media mapping deterministically covers five publish-allowed Hatagold identities (76, 77, 79, 83, 89). The remaining 25 publish-allowed rows require BRZ-40 runtime Media Library audit before they can be declared missing or complete.

## Importer acceptance rules

- Re-running base sync must not downgrade existing verified Product Truth metadata.
- Frontend/indexability gate must require `active + VERIFIED_* + PUBLISH_ALLOWED`.
- Existing manual title/content/featured image are not overwritten by base sync.
- Canonical title changes remain owned by verified Product Truth overlay.
- Existing slugs remain stable; new posts get a slug only at creation.
- Source metadata is retained.
- Dry-run remains available through WP-CLI (omit `--apply`).

## Verification / publish set

Verified + publish-allowed IDs from the project Product Truth overlay:

`4, 5, 6, 8, 9, 11, 12, 13, 14, 15, 16, 17, 19, 20, 21, 75, 76, 77, 78, 79, 83, 89, 92, 93, 94, 95, 96, 97, 98, 99`

All other Product Master IDs in `1..93` are `regulatory_status=unknown`, `verification_status=NEED_VERIFY`, and `content_gate=LEGAL_HOLD` unless a later verified overlay supersedes them.
