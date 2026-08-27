# DDG Product Recovery — latest

## P0 conclusion

Root cause of the empty product catalog was an architectural route collision, not evidence that the WooCommerce product rows were deleted.

`Bizrise Core` registered the internal Product Truth CPT `bizrise_product` as public with `has_archive=true` and rewrite slug `san-pham`. The production storefront is WooCommerce and also uses `/san-pham/` for the public `product` catalog. Product Truth records created by `ProductImporter` are intentionally draft, so the competing CPT archive could resolve the shop URL to an archive with no public posts.

## Fix applied

Commit: `b2590075243f0b9544e203c9aed87ba10c3f2982`

File: `apps/bizrise-core/src/ContentTypes/Product.php`

Changes:

- Keep `bizrise_product` data intact as an internal Product Truth workspace.
- Set the CPT non-public and non-queryable.
- Remove public archive and rewrite rules.
- Remove it from search/nav menus.
- Keep admin UI and REST access for controlled Product Truth editing/migration.
- Add a versioned one-time rewrite flush on `init` priority 99 so Bridge deployments remove the stale `/san-pham/` rewrite even though the plugin is not re-activated.
- WooCommerce `product` remains the only public catalog route/source for `/san-pham/`.

No legacy WooCommerce product row or Product Truth record was deleted.

## CI

Exact fix SHA `b2590075243f0b9544e203c9aed87ba10c3f2982`:

- Validate Bizrise DDG V2: PASS.
- Build Bizrise DDG V2 Release: PASS.

The WordPress Deploy Bridge can therefore deploy this SHA automatically.

## Product Truth publication audit

Current Product Truth seed contains 26 verification records. Seed policy explicitly says the supplied notification images establish identity/pack facts only and do not establish currently active regulatory status.

Current publication counts from the seed:

- `publish_allowed=true`: **0**
- `publish_allowed=false`: **26**
- Explicit `regulatory_status=hold`: **1** (`havigold-serum-nam-trang-da-18g`)
- Remaining records: **25** are not publishable under the current seed policy because current regulatory evidence is not established (`unknown` / partial verification).

Therefore this recovery does **not** mass-convert Product Truth staging records to public products and does not fabricate eligibility. The public storefront is restored by removing the route collision and returning control to the existing WooCommerce catalog. Any SKU already public in WooCommerce is not automatically reclassified by this fix; explicit HOLD/unverified Product Truth must be reconciled separately before using Product Truth as a hard publication gate on WooCommerce.

## Architecture decision

- Public storefront source: WooCommerce `post_type=product`.
- Internal canonical verification workspace: `post_type=bizrise_product`.
- Product Truth must not own or shadow storefront rewrite routes.
- PublicationGate currently applies only to the internal Product Truth CPT; it is not allowed to silently draft WooCommerce products until deterministic WooCommerce mapping and an approved policy exist.
- Product Media Repair continues to operate against the real WooCommerce catalog and exact poster mapping.

## Production verification required after Bridge deployment

1. `/san-pham/` resolves to WooCommerce shop/archive, not `bizrise_product`.
2. Existing public WooCommerce products reappear without database recreation.
3. Product categories resolve through `product_cat`.
4. `/wp-json/bizrise-deploy/v1/status` reports a deployed SHA at or after the fix commit.
5. `/wp-json/bizrise-ddg/v1/runtime-status` reports deterministic media integrity without public missing/wrong featured images.
6. No Product Truth HOLD record is newly promoted by this recovery.

## Status

**SOURCE FIX: PASS**

**CI: PASS**

**PRODUCTION ROUTE/CATALOG: awaiting Deploy Bridge/runtime verification.**
