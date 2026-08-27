# DDG Release / Production QA — 2026-08-27 11:21 ICT

## Verdict

**FAIL / PRODUCTION NOT VERIFIED**

P0 remains open because production catalog/runtime could not be verified from the QA environment. Do not mark production PASS from CI alone.

## Git / CI

- Branch: `codex/rebuild-v2`
- HEAD observed: `eba1d6126c0fa6442cbf1bd99c8604c42f4e60b6`
- HEAD commit: `docs(frontend): record brand resolver hardening`
- Validate Bizrise DDG V2: **SUCCESS** for exact HEAD SHA.
- Build Bizrise DDG V2 Release: **SUCCESS** for exact HEAD SHA.

## Production deploy verification

- `/wp-json/bizrise-deploy/v1/status`: **CHƯA XÁC MINH** from this QA environment.
- `/wp-json/bizrise-ddg/v1/runtime-status`: **CHƯA XÁC MINH** from this QA environment.
- `deployed_sha`: **CHƯA XÁC MINH**.
- Therefore deployed SHA cannot yet be asserted to equal Git HEAD.

## Catalog QA

Production checks are **BLOCKED / CHƯA XÁC MINH** for:

- `/san-pham/` archive showing eligible public products.
- Product category archives.
- Representative single-product pages.
- Exact Featured Image mapping against 44-product manifest.
- 9:16 product-card rendering.
- HOLD/draft products not leaking publicly.

Because the user reported the production product catalog disappeared, this remains a **P0 incident until runtime evidence proves recovery**.

## Content QA

Core pages and the 10 article URLs are **CHƯA XÁC MINH** in production during this run. Source/CI success is not accepted as production publication proof.

## Data used

- Git branch `codex/rebuild-v2` exact HEAD SHA above.
- GitHub Actions workflow runs for `validate-v2.yml` and `release-v2.yml` tied to that SHA.
- Expected product test set: canonical 44-product manifest / Product Truth set.
- Production runtime endpoints intended as authoritative deployment/catalog evidence.

## Release gate

**P0 FAIL.** Do not label production healthy yet.

Next action: obtain runtime endpoint evidence after WordPress Deploy Bridge completes; require `deployed_sha == HEAD`, then verify catalog counts, public/HOLD separation, exact featured-media mapping, archive/category/single product rendering, and core/article URLs. If deployed SHA matches but catalog remains empty, treat catalog query/publication gate as the active root-cause path rather than CI/deploy.