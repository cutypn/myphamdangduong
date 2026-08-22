# MEDIA IMPORTER SAFETY BLOCKER

Date: 2026-08-22  
Branch: `agent/brz-40-media`  
Importer reviewed: `apps/bizrise-ddg-media-importer/bizrise-ddg-media-importer.php` v1.1.0  
Data baseline: `08fd7b7383ff45abba2aedfcef2ff72184fa8d33`

## Status

`STATIC_APPLY_BLOCKER`

BRZ-40 has no WordPress/cPanel runtime in this session. No `wp bizrise ddg-media --apply` has been run. This document records statically proven unsafe apply paths so the operator does not treat a clean-looking dry-run as permission to apply.

## Bug / unsafe path

### 1. Fallback canonical identity drops pack/size and B5

`canonical_identity()` removes the token `b5`, bare digits, and size tokens such as `10g`, `15g`, `50g`, `120g`.

Impact: different canonical products in the same brand can collapse to the same fallback identity. Example: One Today sunscreen products with the same product wording but different packs (15g vs 50g) can become indistinguishable to `bind_exact_existing_product_media()` if the attachment descriptor also collapses to that identity.

### 2. Brand guard accepts unknown runtime brand

`brand_guard()` currently returns true when the expected brand exists but the runtime product brand resolves to an empty string.

Impact: an intended brand guard can fail open instead of fail closed.

### 3. Apply fallback is not gated by Product Truth publish status

`bind_exact_existing_product_media()` iterates all recognized product posts. It does not require `active + VERIFIED_* + PUBLISH_ALLOWED` before binding.

Impact: media repair may touch legacy/LEGAL_HOLD product posts even though Production V1 media ownership is defined only for the 35 PUBLISH_ALLOWED identities.

### 4. Dry-run does not execute the risky binding path

`repair_missing_media(false)` resolves manifest assets but `bind_asset()` and `bind_exact_existing_product_media()` execute only when apply is true.

Impact: a dry-run cannot prove that direct title targets or fallback attachment bindings are safe. A low-risk dry-run summary is therefore not sufficient acceptance evidence for `--apply`.

## Impact

Potential impact is wrong Featured Image binding across pack/identity boundaries, legacy sibling binding, or media changes on non-publishable product records. Existing manual Featured Images are protected by `maybe_set_thumbnail()`, but that protection does not solve wrong binding into currently empty Featured Image slots.

## File

`apps/bizrise-ddg-media-importer/bizrise-ddg-media-importer.php`

## Fix direction — NOT APPLIED IN THIS COMMIT

A separate importer code fix should be reviewed before production apply. Minimum safe direction:

1. Preserve product pack/size in media identity matching, or bind by persistent Product ID/master key rather than descriptor-only identity.
2. Fail closed when an expected brand is present but runtime brand cannot be resolved.
3. Require Product Truth gate `active + VERIFIED_* + PUBLISH_ALLOWED` before automatic product binding.
4. Make dry-run preview the same candidate binding decisions that apply would make, without mutating state.
5. Keep manual Featured Image preservation and attachment reuse/idempotency behavior.

## Regression risk

The safer matcher can intentionally produce fewer automatic matches. This is acceptable: a false negative becomes `MISSING_ASSET` / `CANDIDATE_MAPPING_REVIEW`; a false positive can put the wrong product image into production.

## Acceptance criteria for any future code fix

- IDs with different packs do not collapse to one auto-binding identity.
- IDs `76`, `77`, `79`, `83`, `89` are not auto-promoted from candidate status without exact attachment/source proof.
- LEGAL_HOLD / unknown products are never auto-bound by the Production V1 media run.
- Unknown runtime brand fails closed when the manifest expects a brand.
- Dry-run reports the exact proposed product bindings and ambiguities without mutation.
- Existing valid manual Featured Images remain untouched.
- First apply creates no incorrect bindings.
- Second apply creates zero duplicate attachments and zero new bindings when state is unchanged.
- Product Truth metadata and canonical titles remain unchanged.

## Operator gate

Until BRZ-80 accepts a safe importer path, BRZ-90/operator may run read-only diagnostics only. Do **not** run:

```bash
wp bizrise ddg-media --apply
```

The production runtime handoff is documented separately in `docs/MEDIA_RUNTIME_HANDOFF.md`.
