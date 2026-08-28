# DDG Project Orchestrator — latest

Reviewed: 2026-08-28 10:20 ICT

## Objective
P0 remains: finish a production-ready Đăng Dương Group website without expanding into ads/email/social. Source of truth is `codex/rebuild-v2`; production completion requires deployed-SHA and live evidence.

## Inputs read
- Git branch HEAD observed before this strategy write: `baa6c91af873a9310bb456c51ad0902bbee67e8d` (`chore(frontend): bust mobile navigation cache`).
- `docs/post-deploy-test-latest.md`: production runtime/frontend remains **CHƯA XÁC MINH**; last observed Git HEAD there was older (`202f7e1...`) with Validate/Release SUCCESS, but deployed SHA was unavailable.
- `docs/frontend-recovery-latest.md`: approved mindmap/source + CI PASS; mobile navigation source recovered through 1180px; production/browser QA unverified.
- `docs/content-publish-latest.md`: 10/10 knowledge articles publish-ready; production article/media verification pending.
- `apps/bizrise-ddg-theme/header.php`: top-level IA in source matches the approved order and contains the approved submenu families.
- Shared product-marketing context did not exist at the start of this run.

## Decisions
1. Created `.agents/product-marketing-context.md` as the shared marketing/IA contract. It separates consumer, OEM/ODM prospect and partner journeys; records only repository-verified proof points; defines voice, goals and URL ownership.
2. IA top-level source is already aligned and is not rewritten this round: Trang chủ → Về Đăng Dương → Năng lực → Thương hiệu → Sản phẩm & Routine → Kiến thức → Đối tác → Liên hệ.
3. Canonical ownership is enforced conceptually to prevent cannibalization: capability detail belongs to capability URLs; WooCommerce catalog owns product discovery; knowledge owns educational intent; `/doi-tac/` links to OEM/ODM rather than duplicating its detail intent; `/lien-he/` owns primary conversion.
4. Largest unresolved P0 is not another marketing rewrite: it is production deploy/runtime verification. Adding more source changes before a deploy can be observed risks moving the target and duplicating Frontend/Product/Content agents.
5. No unverified certification, capacity, partner, medical-effect or VOC statement is introduced.

## Action owners
- Release/Deploy path: obtain exact deployed SHA/runtime evidence for the latest CI-passing descendant of current source.
- Post-Deploy Production Tester: once a new deployed SHA is observable, run core URLs, catalog, 360/390/430 and desktop tests and record P0/P1/P2 evidence.
- Frontend Recovery: only act on reproducible frontend failures from production evidence; do not change Product Truth.
- Product/Data Recovery: retain WooCommerce catalog ownership and HOLD/publication safety; only act on verified runtime/catalog defects.
- Content Publish: keep 10/10 article registry publish-ready; only fix evidence-backed public copy/link issues.
- Strategy/Orchestrator: maintain IA, journey, CTA/internal-link and URL ownership contract; avoid duplicate implementation work.

## KPI / Definition of Done
P0 website completion requires all of the following on the same deployed release lineage:
- deployed SHA is observable and corresponds to a Validate + Release CI-successful commit;
- runtime status is readable and has no P0 catalog/publication failure;
- eight top-level journeys and approved submenu URLs render without abnormal 404s;
- `/san-pham/` is non-empty and WooCommerce-backed; HOLD/draft items do not leak publicly;
- representative product/category/single pages and Featured Images pass production checks;
- mobile 360/390/430 and desktop >=1180 pass header/submenu, overflow, hero, CTA, grid, article and footer checks;
- no P0 broken links, duplicate H1, placeholder/internal jargon or unsafe claim is observed;
- knowledge/article production and media inventory are verified where endpoints support it.

## Blocker
**P0 — production evidence unavailable in the latest tester report.** Source and CI progress cannot be promoted to PRODUCTION PASS until `/wp-json/bizrise-deploy/v1/status`, `/wp-json/bizrise-ddg/v1/runtime-status` and frontend production are reachable from a tester and the exact deployed SHA is checked.

## Next action
Do not create speculative marketing scope. First observe the next deployed SHA and execute independent post-deploy production QA. If it exposes a P0, route the exact URL/viewport/evidence to the owning Frontend or Product agent. If all P0 checks pass, then close the website P0 and move remaining journey/copy refinements to P1.