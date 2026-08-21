# Bizrise / Đăng Dương Group V2

Clean rebuild workspace for the Đăng Dương Group implementation of the Bizrise Framework.

## Non-negotiable rules

- Legacy code is reference/migration material only; V2 does not extend the Base64/tar + MU-plugin hotfix architecture.
- Readable Git source is the source of truth.
- WordPress, PHP 8.2+, Gutenberg-first, Multisite-ready.
- No mandatory WooCommerce or Elementor dependency.
- Product/business data belongs to `bizrise-core`, not the theme.
- Product publication is gated by verification and regulatory state.
- Media mapping is deterministic, brand-guarded and idempotent.
- Product Truth is never inferred from missing fields.
- Theme presentation must keep one H1 per indexable URL and use Be Vietnam Pro.
- Production legacy remains untouched until V2 staging passes.

## Target layout

```text
apps/
  bizrise-core/
  bizrise-ddg-theme/
  bizrise-ddg-migrator/
profiles/dang-duong/
data/product-master/
data/product-truth/
data/migration/
docs/
deploy/
tests/
```

## Current branch

V2 development: `codex/rebuild-v2`

Legacy reference: `agent/ddg-media-importer`

## First delivery sequence

1. Workspace + architecture/migration contracts.
2. Bizrise Core product/brand data model and publication gate.
3. Product Truth seed + deterministic migrator/media manifest.
4. Theme shell/design system.
5. Product/brand templates, IA, homepage, SEO and staging QA.
