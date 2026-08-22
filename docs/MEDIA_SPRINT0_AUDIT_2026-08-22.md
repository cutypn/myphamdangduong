# MEDIA SPRINT 0 — PRODUCTION MEDIA AUDIT

Branch: `agent/brz-40-media`  
Baseline HEAD: `5d83b8723e7db609a24aefe6c7f7820ff0d6d210`  
Audit date: 2026-08-22  
Owner: BRZ-40 Media

## Scope

Audit repository-side media behavior only. No theme layout changes, no Product Truth edits, no product identity changes.

Audited:

- `apps/bizrise-ddg-media-importer/`
- `apps/bizrise-ddg-media-hotfix/` because it can change Featured Images after the importer
- `apps/bizrise-ddg-product-sync/data/product-truth-2026-08-18.psv`
- `apps/bizrise-ddg-product-sync/bizrise-ddg-product-truth-overlay.php`

## Data dependency

`agent/brz-30-data` is still identical to the production baseline at the time of this audit. There is no newer Data Sprint output to consume.

Status: **WAITING_DATA**.

For this audit only, the current Product Truth snapshot dated 2026-08-18 is used. It contains **35** rows with:

- regulatory status = `active`
- verification status = `VERIFIED_NOTIFICATION_IMAGE`
- content gate = `PUBLISH_ALLOWED`

Re-run this audit when BRZ-30 publishes a newer Product Truth / Publish Gate commit.

## Importer behavior audit

### PASS

- Idempotent attachment reuse via `_bizrise_ddg_asset_key`.
- Reuses existing Media Library attachment before importing.
- Does not overwrite a valid manual Featured Image.
- Product binding uses exact title/canonical identity + brand guard; no fuzzy product mapping.
- Tracks missing products, missing assets and ambiguous matches in runtime report data.
- ALT is only filled when attachment ALT is empty and manifest provides an ALT value.

### GAPS

1. The branch does not contain `apps/bizrise-ddg-media-importer/assets/media/`. Therefore bundled-file import cannot resolve any new local file in this snapshot; importer is effectively dependent on already-existing Media Library attachments for manifest assets.
2. Admin report renders missing products but does not visibly render `missing_assets` or `ambiguous_matches`, although both are tracked internally.
3. Current manifest has no explicit aspect-ratio metadata and no desktop/mobile variant metadata.
4. Five PUBLISH_ALLOWED Hatagold product candidates are present in manifest, but their target titles are legacy titles while Product Truth canonical titles now contain `B5` and/or different canonical wording. They cannot be counted as deterministic exact-title mappings without runtime verification or a canonical-safe mapping update.
5. No PUBLISH_ALLOWED One Today product has a deterministic product asset in the importer manifest.
6. She One PUBLISH_ALLOWED product ID 93 has no deterministic product asset in the importer manifest.

## Media hotfix audit

`apps/bizrise-ddg-media-hotfix/` runs after the importer and can sideload product images from `myphamanhduong.vn` using normalized exact catalog text.

Findings:

- It preserves an existing Featured Image.
- It does not fuzzy-match product titles.
- Dynamic source categories cover One Today, One Today Gold, Ever Today, Cream X2 and She One; Hatagold is not in its external source category list.
- The dynamically sideloaded assets are not controlled by the importer manifest and are not evidence that the project Photoshop `Export for Web` workflow was completed.
- Therefore external-hotfix output is **not accepted as web-ready proof in this Sprint audit** until source/rights and Export-for-Web compliance are reviewed.

## PUBLISH_ALLOWED product audit

Detailed per-product registry:

`apps/bizrise-ddg-media-importer/data/publish-allowed-media-audit-2026-08-22.psv`

Repository-side summary:

- PUBLISH_ALLOWED products audited: **35**
- Deterministic canonical-safe Featured Image mappings proven from repo: **0**
- Candidate Hatagold manifest assets requiring mapping/runtime review: **5**
- Products with no deterministic importer asset: **30**
- Live CMS Featured Image state: **UNKNOWN** — requires WP-CLI/admin runtime report after Data finalizes
- Gallery mappings in importer manifest: **0**

The audit deliberately does not treat Product Truth evidence/notification images as product packshots.

## Hero / banner audit

| Slot | Desktop | Mobile | Repository status | Notes |
|---|---|---|---|---|
| Homepage | MISSING | MISSING | NOT_CONFIGURED | No homepage hero asset in importer manifest |
| Năng lực | `factory_front` candidate | MISSING | PARTIAL | Actual dimensions/ratio not verifiable from repo |
| Factory | `factory_aerial` candidate | MISSING | PARTIAL | Actual dimensions/ratio not verifiable from repo |
| R&D | MISSING | MISSING | NOT_CONFIGURED | No R&D banner asset in importer manifest |
| OEM/ODM | MISSING | MISSING | NOT_CONFIGURED | No OEM/ODM banner asset in importer manifest |
| Brand hub | MISSING | MISSING | NOT_CONFIGURED | No brand-hub banner asset in importer manifest |
| One Today | `onetoday_brand_banner` candidate | MISSING | PARTIAL | Desktop/mobile art direction not separated |
| Hatagold | `hatagold_brand_banner` candidate | MISSING | PARTIAL | Desktop/mobile art direction not separated |
| She One | MISSING | MISSING | NOT_CONFIGURED | Active in current PUBLISH_ALLOWED snapshot |
| Other active brands | WAITING_DATA | WAITING_DATA | WAITING_DATA | Re-audit after BRZ-30 final output |

## Ratio compliance

Required production rules:

- Mobile hero/story: `9:16`
- Desktop hero/corporate: `16:9`
- Product: `1:1` or `3:4`

Current importer manifest does not store dimensions or ratio, and source image files are not committed in this branch. Ratio compliance therefore cannot be proven from repository state.

Status for all existing candidate hero/banner/product assets: **RATIO_NOT_VERIFIED**.

No desktop asset may be declared a valid mobile 9:16 asset by crop alone.

## ALT audit

All current hard-coded manifest entries include a non-empty ALT string.

However:

- Product candidates without manifest assets have no controlled ALT in the media manifest.
- Media Hotfix generates ALT from the product title; this is a fallback, not a reviewed per-image ALT.
- Decorative assets are not represented as a separate class in the importer manifest, so `alt=""` compliance must be checked in theme/runtime QA.

## SEO headline in images

No evidence in the importer code intentionally embeds the page H1/SEO headline into generated imagery. New creative must keep primary headlines as HTML text where possible.

## Photoshop Export for Web gate

No new binary asset was created in this Sprint.

Any future replacement/new hero, banner, product packshot or video poster must be marked `NEEDS_PHOTOSHOP_EXPORT_FOR_WEB` until the project workflow is completed:

`Photoshop → Export for Web → web asset → CMS`.

Do not call a newly created master/PSD/TIFF/raw asset web-ready before this gate.

## Required next actions

### BRZ-30 Data

- Publish final PUBLISH_ALLOWED list and canonical IDs/names.
- Notify BRZ-40 of identity changes before media mapping is updated.

### BRZ-40 Media

- Replace legacy-title product mappings with Product-ID/canonical-safe mappings only after Data finalizes.
- Acquire/approve product packshots for all rows currently `MISSING_ASSET`.
- Produce dedicated mobile 9:16 assets for P0 hero/story slots; do not crop desktop 16:9 mechanically.
- Produce/approve desktop 16:9 Homepage, R&D, OEM/ODM, Brand hub and She One banners.
- Record source, usage rights, ratio, desktop/mobile variant and ALT in the production manifest.
- Run Photoshop Export for Web before marking newly produced assets web-ready.

### Runtime QA after Data finalizes

Run dry-run first:

```bash
wp bizrise ddg-media
```

Then apply only after reviewing unmatched/ambiguous output:

```bash
wp bizrise ddg-media --apply
```

QA must capture:

- PUBLISH_ALLOWED total
- Featured Image count
- Missing Featured Image list
- ambiguous matches
- missing manifest assets
- manual images skipped
- desktop/mobile hero slots
- image dimensions/ratios
- ALT values

## Release conclusion

**MEDIA SPRINT 0 AUDIT COMPLETE — NOT RELEASE READY.**

Blocking reasons:

1. Data Sprint final output is pending.
2. 30/35 current PUBLISH_ALLOWED products have no deterministic importer asset.
3. 5/35 have only legacy-title candidate mappings and require canonical-safe verification.
4. P0 hero/banner coverage is incomplete and dedicated mobile assets are missing.
5. Ratio compliance cannot be proven from current repository assets.
