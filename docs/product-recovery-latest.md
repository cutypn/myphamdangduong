# DDG Product Recovery — latest

## P0 conclusion

Root cause of the empty product catalog was an architectural route collision, not evidence that the WooCommerce product rows were deleted.

`Bizrise Core` registered the internal Product Truth CPT `bizrise_product` as public with `has_archive=true` and rewrite slug `san-pham`. The production storefront is WooCommerce and also uses `/san-pham/` for the public `product` catalog. Product Truth records created by `ProductImporter` are intentionally draft, so the competing CPT archive could resolve the shop URL to an archive with no public posts.

## Route recovery fix

Commit: `b2590075243f0b9544e203c9aed87ba10c3f2982`

File: `apps/bizrise-core/src/ContentTypes/Product.php`

Changes:

- Keep `bizrise_product` data intact as an internal Product Truth workspace.
- Set the CPT non-public and non-queryable.
- Remove public archive and rewrite rules.
- Remove it from search/nav menus.
- Keep admin UI and REST access for controlled Product Truth editing/migration.
- Add a versioned one-time rewrite flush so Bridge deployments remove the stale `/san-pham/` rewrite.
- WooCommerce `product` remains the only public catalog route/source for `/san-pham/`.

No legacy WooCommerce product row or Product Truth record was deleted.

## Runtime evidence after production deployment

Production runtime at deployed SHA `1349bdfdb2860820945d27149f0632eff9f482fc` reported:

| Metric | Value |
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

The 44 controlled rows are deterministic and have no unresolved/wrong Featured Image in the manifest audit. Therefore the 22 image-less public records are not evidence of a failed 44-SKU mapping. They are unmanaged/legacy storefront rows unless a later deterministic audit proves otherwise.

## Runtime audit bug found and fixed

`ProductMediaRepair::audit_public_products()` uses the combined post-type list `bizrise_product`, `ddg_product`, and `product`. That global audit is useful as a legacy warning but is too broad to be the clean gate for the controlled 44-row manifest. It caused the runtime repair to retry indefinitely even when all 44 controlled rows were exact-clean.

New source changes:

- `apps/bizrise-ddg-migrator/src/StorefrontProductAudit.php`
  - separates controlled 44-row media integrity from unmanaged storefront media gaps;
  - queries WooCommerce `post_type=product` as the storefront source;
  - exposes missing-record details: ID, title, slug, brand evidence, `product_cat`, deterministic source filename, thumbnail ID and manifest marker;
  - exposes legacy public counts for `bizrise_product` / `ddg_product` without mixing them into Woo storefront totals.
- `apps/bizrise-ddg-migrator/bizrise-ddg-migrator.php`
  - version `0.3.8` → `0.3.9`;
  - runtime retry now stops when the controlled 44-row repair is clean;
  - unmanaged/legacy media gaps remain a separate warning and are not auto-drafted or given guessed posters.
- `apps/bizrise-ddg-migrator/src/RuntimeStatus.php`
  - adds `storefront_audit`;
  - status becomes `repair_clean_unmanaged_media_gap` when the controlled manifest is clean but unmanaged public products still lack Featured Images;
  - preserves article runtime status added by the content workflow.

Current code HEAD for this change: `88c3c876f5b38608043cd30f3b3245a2b9f38736`.

## Deterministic handling policy for the 22 missing-image IDs

Do not fuzzy-map and do not assign a poster based only on title similarity.

For each missing WooCommerce product after the new runtime audit deploys:

1. Read ID, slug, exact title, brand evidence, `product_cat`, source filename and manifest marker.
2. If exact source metadata or canonical Product Truth mapping proves it is one of the 44 controlled SKUs, reconcile to that canonical row and exact poster.
3. If it is an exact duplicate/legacy row of a controlled SKU, keep the record for rollback/history but remove duplicate storefront exposure only after deterministic canonical proof.
4. If it is outside the 44 manifest, leave content/status unchanged until source evidence determines whether it is active, obsolete, retired or still valid.
5. Never auto-draft an unmanaged product merely because it lacks an image.
6. Never fabricate regulatory eligibility or marketing claims.

## Product Truth publication audit

Current Product Truth seed contains 26 verification records. Seed policy says supplied notification images establish identity/pack facts only and do not establish currently active regulatory status.

- `publish_allowed=true`: **0**
- `publish_allowed=false`: **26**
- Explicit `regulatory_status=hold`: **1** (`havigold-serum-nam-trang-da-18g`)
- Remaining **25**: unknown / partial regulatory verification.

Product Truth is therefore not used to mass-draft existing WooCommerce storefront rows until deterministic mapping and an approved publication policy exist.

## Architecture decision

- Public storefront source: WooCommerce `post_type=product`.
- Internal canonical verification workspace: `post_type=bizrise_product`.
- Product Truth must not own or shadow storefront rewrite routes.
- PublicationGate applies to the internal Product Truth CPT and must not silently draft WooCommerce products without deterministic mapping.
- Product Media Repair owns exact poster integrity for the 44 controlled manifest.
- Storefront Product Audit owns unmanaged/legacy visibility and media-gap reporting.

## Current counts / state

| Item | Current evidence |
|---|---:|
| Total Woo/public rows reported before audit split | 59 |
| Controlled manifest | 44 |
| Controlled matched | 44 |
| Controlled public media problems | 0 |
| Unmanaged/legacy missing Featured Image candidates | 22 |
| Duplicate count | pending new production `storefront_audit` |
| Controlled public count | pending new production `storefront_audit` / canonical-state audit |
| Draft / HOLD | Product Truth: 26 not publish-allowed, including 1 explicit HOLD; not applied blindly to Woo rows |

## QA / deploy gate

Exact source HEAD `88c3c876f5b38608043cd30f3b3245a2b9f38736` has Validate + Release workflows running. Do not call production fixed until both pass and Deploy Bridge reports this SHA or a descendant.

After deployment, required endpoint evidence:

- `release.sha` = deployed HEAD/descendant;
- `repair.controlled_media_clean=true`;
- `status=repair_clean` or `repair_clean_unmanaged_media_gap`;
- `storefront_audit.storefront_public_total` from WooCommerce only;
- `storefront_audit.unmanaged_public_missing_featured` contains deterministic details for each remaining unmanaged row;
- `storefront_audit.controlled_public_media_problem_ids=[]`.

## Status

**ROUTE RECOVERY: PASS**

**CONTROLLED 44 MEDIA: PASS by current production evidence**

**UNMANAGED / LEGACY 22: now explicitly audited, no destructive action without deterministic evidence**

**LATEST SOURCE CI: RUNNING**

**LATEST PRODUCTION DEPLOY: pending Bridge after CI**
