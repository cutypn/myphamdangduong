# Bizrise Core source recovery

Sprint 0 source-recovery baseline.

- Canonical source tree restored from existing Git object `972a05c7ae41ad7bd611275a0ed580533146e4ff`.
- The historical `bizrise-core-v0.8.1` Base64 payload is preserved under `deploy/payloads/` but is truncated at the end and cannot reconstruct its missing plugin bootstrap byte-for-byte.
- The current production deploy script treats that invalid Core payload as optional and skips it when `tar -tzf` fails.
- This restored tree is the complete self-contained Git-sourced Core baseline: `bizrise_product`, product taxonomies, Product Truth fields, and publication gate. It does not restore catalog/content/media data from the damaged payload.
