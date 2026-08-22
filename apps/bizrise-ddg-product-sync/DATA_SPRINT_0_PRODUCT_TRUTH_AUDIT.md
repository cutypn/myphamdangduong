# DATA SPRINT 0 — PRODUCT TRUTH AUDIT

Date: 2026-08-22  
Branch: `agent/brz-30-data`  
Scope: `apps/bizrise-ddg-product-sync/` only. No theme, content marketing, or media implementation changes.

## Source of truth used for this rebuild

- Product Master: `data/products-master-2026.psv` — 93 legacy/master rows (IDs 1–93).
- Product Truth overlay: `data/product-truth-2026-08-18.psv` — 35 verified rows in the current snapshot.
- Per-product audit output: `data/product-truth-audit-2026-08-22.psv`.

The audit is reconciled from the current overlay snapshot, not from a hard-coded previous publish list. Current Product Truth includes IDs `100`, `101`, `102`, `103`, and `104`.

## Publish gate

A product is a publish/index candidate only when all three conditions are true:

1. `regulatory_status = active`
2. `verification_status = VERIFIED_*`
3. `content_gate = PUBLISH_ALLOWED`

`unknown`, `hold`, `recalled`, and `retired` are not publishable. Legacy names/copy are identity-research inputs only and are not approved marketing claims.

## Recomputed snapshot metrics

| Metric | Count |
|---|---:|
| Total unique product IDs | 104 |
| Product Master rows | 93 |
| Product Truth verified rows | 35 |
| Regulatory active | 35 |
| Regulatory unknown | 69 |
| PUBLISH_ALLOWED | 35 |
| LEGAL_HOLD | 69 |
| Duplicate candidate groups | 3 groups / 7 rows |
| Missing dedicated SKU | 104 |

Reconciliation: 93 master IDs + 11 verified overlay-only IDs (94–104) = 104 unique IDs. Of the 93 master IDs, 24 are superseded/verified by Product Truth and 69 remain `unknown + NEED_VERIFY + LEGAL_HOLD`.

## Duplicate / identity candidates — unchanged, no auto-merge

- `DUP-CAND-01`: IDs `76 / 85` — Hatagold anti-aging identity family.
- `DUP-CAND-02`: IDs `77 / 86` — Hatagold sunscreen 10g identity family.
- `DUP-CAND-03`: IDs `79 / 80 / 90` — Hatagold dark-spots identity family.

No duplicate candidate is merged automatically. Verified identities remain separate until identity evidence is approved.

## Media ownership / handoff

DATA does not assert media completeness or canonical media mapping.

- IDs `76`, `77`, `79`, `83`, `89`: `CANDIDATE_MAPPING_REVIEW` only; BRZ-40 must confirm canonical mapping.
- Other `PUBLISH_ALLOWED` rows: `BRZ40_REVIEW_REQUIRED`.
- Non-publishable rows: `NOT_EVALUATED_BY_DATA`.
- `MAPPED_DETERMINISTIC` is intentionally not used in this audit.

## Importer v1.2.0 audit

Static code review of the current branch confirms the intended runtime direction remains valid; commits `cd09bc82...` and `346ef541...` are not changed by this QA-fix batch.

- **PASS — no Product Truth downgrade:** base sync writes safe truth defaults only when truth fields are empty and preserves verified truth/identity overlay values.
- **PASS — publish/index gate:** frontend/indexability requires `active + VERIFIED_* + PUBLISH_ALLOWED`.
- **PASS — blocked regulatory states:** `unknown`, `hold`, `recalled`, and `retired` do not satisfy the publish gate; base sync demotes non-allowed master posts that are already published to draft.
- **PASS — rerun duplicate protection:** lookup first uses `_bizrise_ddg_master_key`, then exact normalized title + brand; verified overlay also uses persistent master keys/identity matching. Runtime rerun still requires QA execution in WordPress to prove database-level idempotency.
- **PASS — verified canonical identity protection:** base sync does not overwrite brand/group identity fields once verified truth + `PUBLISH_ALLOWED` is present; canonical title changes remain owned by Product Truth overlay.
- **PASS — dry-run non-mutation by code path:** `sync(false)` returns before create/update/demotion branches. QA should still execute WP-CLI dry-run before apply and compare database state.
- **PASS — slug stability direction:** base sync only supplies `post_name` when creating a new post; it does not rewrite existing slugs on rerun.
- **PASS — source retention:** base sync retains `_bizrise_ddg_source_url`; overlay retains evidence/source metadata.

No runtime regression was found that requires modifying importer v1.2.0 in this batch.

## Current PUBLISH_ALLOWED IDs

The current overlay contains 35 rows satisfying the gate. They are derived from `product-truth-2026-08-18.psv`, including the new IDs `100–104`; this document deliberately does not maintain a manually curated hard-coded ID list as the source of truth.

## QA re-audit instructions

1. Confirm the current overlay has 35 rows and IDs `100–104` are present.
2. Confirm the audit PSV has 104 unique IDs, 35 verified/active/PUBLISH_ALLOWED, 69 unknown/LEGAL_HOLD, and 104 missing dedicated SKU values.
3. Confirm duplicate candidates remain exactly `76/85`, `77/86`, `79/80/90` and no auto-merge occurred.
4. Confirm no row uses `MAPPED_DETERMINISTIC`; the five Hatagold candidate IDs use `CANDIDATE_MAPPING_REVIEW` only.
5. Run `wp bizrise ddg-products` without `--apply`; verify DB state does not change.
6. Run `wp bizrise ddg-products --apply`, then run it again; verify no duplicate posts and no verified Product Truth downgrade.
7. Run `wp bizrise ddg-product-truth-20260818` without `--apply`, then `--apply`, then re-run; verify overlay idempotency and 35 current truth rows remain publish candidates.
8. Inspect unknown/hold/recalled/retired posts: they must not be publish/index candidates.
9. Confirm existing verified canonical titles/identity fields and existing slugs remain stable after reruns.

## Files

- Summary audit: `DATA_SPRINT_0_PRODUCT_TRUTH_AUDIT.md`
- Per-product audit: `data/product-truth-audit-2026-08-22.psv`
