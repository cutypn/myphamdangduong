# DDG Project Orchestrator — latest

Reviewed: 2026-08-28 12:19 ICT

## Objective
P0 remains: finish the production-ready Đăng Dương Group website without expanding into ads/email/social. Source of truth is `codex/rebuild-v2`. Completion requires an exact deployed SHA plus live production evidence on that release.

## Inputs read
- Current branch HEAD at review start: `642c97489776c99f41ffe145bf9c82c43a36a985` (`docs(product): refresh 11:32 recovery state`).
- `.agents/product-marketing-context.md`: complete shared context covering overview, audience/beauty situations, differentiation, brand voice, verified proof points/constraints, goals, URL ownership and evidence rules.
- `docs/product-recovery-latest.md`: source/CI PASS on its recorded release lineage; controlled manifest remains 44/44 with controlled wrong Featured Image 0; no product state/media mutation; fresh production catalog/runtime remains CHƯA XÁC MINH.
- Previous `docs/project-orchestrator-latest.md`: post-deploy evidence remained unavailable; responsive source fix was not yet browser-verified on production.
- GitHub combined-status API for current HEAD returned an empty legacy status list. This is not evidence of Validate/Release success or failure and is not used to infer production state.

## Product marketing context decision
No context rewrite is required this round. Existing context already satisfies the shared contract and explicitly prohibits invented certifications, capacity, partners, VOC, medical effects and unsupported regulatory claims.

## IA / customer journey decision
Approved top-level IA remains frozen as the source contract:

Trang chủ → Về Đăng Dương → Năng lực → Thương hiệu → Sản phẩm & Routine → Kiến thức → Đối tác → Liên hệ.

Primary intent ownership remains:
- `/` orientation/routing;
- `/ve-dang-duong/` corporate context;
- `/nang-luc/` capability hub with approved R&D/factory/OEM-ODM/quality-control details;
- `/thuong-hieu/` brand discovery;
- `/san-pham/` WooCommerce transactional/product discovery; routine supports selection without replacing product detail;
- `/kien-thuc/` education;
- `/doi-tac/` partnership, linking to OEM/ODM detail rather than duplicating it;
- `/lien-he/` primary conversion and approved action subpages.

CTA journey remains: orientation/education → relevant capability, brand, product or routine hub → explicit next action (`xem sản phẩm`, `khám phá routine`, `tìm hiểu năng lực`, `gửi yêu cầu`, `liên hệ`). No evidence-backed reason was found to mutate IA, CTA ownership or internal-link intent this round.

## Priority decision
The largest unresolved blocker is **P0 production observability / post-deploy verification**. Marketing, IA, Product Truth and catalog source must not be churned merely to create a new commit. Strategy therefore freezes source structure until a reproducible production defect is observed.

## Action owner
- **Release/Deploy:** provide/read exact `deployed_sha` and `remote_sha` for a lineage whose Validate + Release CI are demonstrably successful.
- **Post-Deploy Production Tester:** on each newly observable deployed SHA, test core pages/submenus, Woo catalog, representative products, 360/390/430, desktop >=1180, links/H1/404/content and media inventory.
- **Frontend Recovery:** fix only exact production frontend failures with SHA + URL + viewport evidence.
- **Product/Data Recovery:** fix only deterministic catalog/runtime defects; preserve controlled 44-SKU mapping and Product Truth.
- **Content Publish:** fix only evidence-backed public copy/link defects.
- **Strategy/Orchestrator:** preserve IA, CTA/internal-link journey and URL ownership; prevent duplicate work/cannibalization.

## KPI / Definition of Done
PRODUCTION PASS requires all P0 checks on the same deployed release lineage:
1. `deployed_sha == remote_sha` and corresponding Validate + Release CI are successful.
2. Runtime status is readable with no P0 publication/catalog failure.
3. Eight top-level journeys plus approved submenu URLs render without abnormal 404s.
4. `/san-pham/` is non-empty and WooCommerce-owned; representative category/archive/single products render; Featured Images are correct; HOLD/draft does not leak.
5. Mobile 360/390/430 passes header/hamburger/submenu, no horizontal overflow, hero, typography, CTA, two-column product grid, article cards, footer and tap targets.
6. Desktop >=1180 passes navigation/layout.
7. No P0 broken links, duplicate H1, placeholder/internal jargon or unsafe claim is observed.
8. Product/article media inventory is verified where runtime endpoints support it.

## Blocker
**P0 — fresh deployed SHA + live production evidence are still unavailable in the reports read this round.** Current HEAD is newer than the prior orchestrator review, but an empty legacy combined-status list and source reports are not production evidence.

## Next action
Do not open ads/email/social scope and do not create speculative marketing/theme/catalog rewrites. Obtain the next observable deployed SHA, prove its Validate + Release lineage, then run independent production QA. Route any P0 with exact SHA + URL + viewport + evidence to its owner. Close website P0 only after every required P0 check passes on that same deployed SHA.
