# DDG Project Orchestrator — latest

Reviewed: 2026-08-28 11:22 ICT

## Objective
P0 remains: finish the production-ready Đăng Dương Group website without expanding into ads/email/social. Source of truth is `codex/rebuild-v2`. Completion requires an exact deployed SHA plus live production evidence on that release.

## Inputs read
- Current branch HEAD: `01f7aa0ff92eb17dd429494d55c23e0584d2ff1a` (`docs(product): record current recovery gate`).
- `.agents/product-marketing-context.md`: shared context exists and already covers overview, audience/beauty situations, differentiation, brand voice, verified proof points/constraints, goals, URL ownership and evidence rules.
- `docs/post-deploy-test-latest.md`: last production test remains **CHƯA XÁC MINH** because deploy/runtime/frontend endpoints were unreachable; no deployed SHA was observed.
- `docs/frontend-recovery-latest.md`: mobile source PASS; responsive nav JS/CSS breakpoint mismatch fixed; production/browser QA still unverified.
- Latest product recovery report at HEAD: controlled manifest remains 44, last verified controlled match 44/44, wrong Featured Image 0, no product state/media changes; current production inventory remains unverified.
- GitHub combined-status API returned no legacy commit statuses for current HEAD; therefore this round does not infer current Validate/Release success from an empty status list.

## IA / journey decision
Approved top-level IA remains the contract and does not need another speculative rewrite:

Trang chủ → Về Đăng Dương → Năng lực → Thương hiệu → Sản phẩm & Routine → Kiến thức → Đối tác → Liên hệ.

URL ownership remains:
- corporate context: `/ve-dang-duong/`;
- B2B capability hub/details: `/nang-luc/` and approved capability subpages;
- brand discovery: `/thuong-hieu/` and brand subpages;
- transactional discovery: WooCommerce `/san-pham/`; routine supports selection without duplicating product-detail intent;
- education: `/kien-thuc/` and articles;
- partnership: `/doi-tac/`, linking to OEM/ODM detail rather than duplicating it;
- primary conversion: `/lien-he/` and approved action subpages.

No new certification, capacity, partner, VOC, medical-effect or regulatory claim is introduced.

## Priority decision
The largest unresolved blocker is still **P0 production observability / post-deploy verification**, not IA or marketing copy. Further speculative source edits would move the release target and duplicate Frontend/Product/Content ownership.

The strategy lane therefore makes no theme/Product Truth/catalog mutation this round. It freezes IA/journey ownership until production evidence identifies a reproducible P0/P1 defect.

## Action owner
- **Release/Deploy:** expose/read exact `deployed_sha` and `remote_sha` for a Validate + Release successful release lineage.
- **Post-Deploy Production Tester:** when a new deployed SHA is observable, run the required core-page, submenu, WooCommerce catalog, 360/390/430, desktop >=1180, link/H1/404/content and media-inventory checks.
- **Frontend Recovery:** fix only reproducible production frontend failures; preserve WooCommerce/Product Truth ownership.
- **Product/Data Recovery:** fix only deterministic runtime/catalog defects; no guessed mapping, mass publish/draft or Product Truth override.
- **Content Publish:** fix only evidence-backed public copy/link defects; preserve verified-claim rules.
- **Strategy/Orchestrator:** maintain IA, CTA/internal-link journey and URL ownership; prevent duplicate work/cannibalization.

## KPI / Definition of Done
PRODUCTION PASS requires all P0 checks on the same deployed release lineage:
1. `deployed_sha == remote_sha` and corresponding Validate + Release CI are successful.
2. Runtime status is readable with no P0 publication/catalog failure.
3. Eight top-level journeys plus approved submenu URLs render without abnormal 404s.
4. `/san-pham/` is non-empty, WooCommerce-owned, representative category/archive/single products render, Featured Images are correct, HOLD/draft does not leak.
5. Mobile 360/390/430 passes header/hamburger/submenu, no horizontal overflow, hero, typography, CTA, two-column product grid, article cards, footer and tap targets.
6. Desktop >=1180 passes navigation/layout.
7. No P0 broken links, duplicate H1, placeholder/internal jargon or unsafe claim is observed.
8. Product/article media inventory is verified where runtime endpoints support it.

## Blocker
**P0 — exact deployed SHA and live production evidence remain unavailable in the latest independent tester report.** Source/CI progress is not production evidence.

## Next action
Do not open ads/email/social scope and do not create speculative marketing rewrites. First obtain the next observable deployed SHA and run independent production QA. Route any P0 with exact SHA + URL + viewport + evidence to its owner. If every P0 passes on that deployed SHA, close website P0 and move remaining journey/copy refinements to P1.