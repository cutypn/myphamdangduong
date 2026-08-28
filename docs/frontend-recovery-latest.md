# DDG Frontend Recovery — latest

## Current result

Mobile-first recovery continues on `codex/rebuild-v2` without changing Product Truth, publication rules, WooCommerce mapping or media assignments. Product/Data Recovery remains authoritative: public catalog = WooCommerce `post_type=product`; internal `bizrise_product` remains non-public/non-queryable and must not own `/san-pham/`.

Latest Product/Data Recovery read before this frontend change:

- controlled manifest: **44**;
- controlled matched: **44**;
- last verified controlled wrong Featured Image: **0**;
- public legal HOLD exclusion: **PASS source + CI**;
- product cleanup: **BLOCKED-SAFE / 0 product state changes** while live deterministic inventory is unavailable;
- production on current HEAD: **CHƯA XÁC MINH**.

## P0/P1 audit and fix this run

`docs/post-deploy-test-latest.md` remains blocked because production deploy/runtime/frontend cannot currently be reached from the tester, so no live screenshot or deployed-SHA evidence exists yet.

Source audit found one responsive navigation mismatch:

- CSS intentionally uses hamburger/touch navigation through **1180px** because the approved mindmap has eight top-level items plus logo/CTA.
- `assets/js/theme2.js` still treated **981px** as the desktop transition.
- Crossing 981px could therefore reset the open menu/submenu state while the UI was still in hamburger mode between 981–1180px.

Fix:

- aligned JS desktop media query to `min-width: 1181px`;
- Escape/submenu/focus behavior otherwise unchanged;
- no Product Truth, WooCommerce, HOLD, media mapping or publication rule changed.

Exact frontend code commit: `f0f18faa2df555e0e62641aeefaee376bf31f1d2`.

Files changed:

- `apps/bizrise-ddg-theme/assets/js/theme2.js`

## Mobile checklist

| Check | 360 | 390 | 430 |
|---|---|---|---|
| Sticky header source geometry | PASS source | PASS source | PASS source |
| Logo/hamburger fit | PASS source | PASS source | PASS source |
| Hamburger/submenu targets >=44px | PASS source | PASS source | PASS source |
| Navigation panel vertical scroll / no x-overflow | PASS source | PASS source | PASS source |
| Product search >=48px | PASS source | PASS source | PASS source |
| Product filter pills >=44px | PASS source | PASS source | PASS source |
| Product-card CTA >=44px | PASS source | PASS source | PASS source |
| Product grid 2 columns | PASS source | PASS source | PASS source |
| Product media portrait 9:16 / contain | PASS source | PASS source | PASS source |
| Single product image contained | PASS source | PASS source | PASS source |
| Article card/body responsive | PASS source | PASS source | PASS source |
| Tables/form/footer responsive | PASS source | PASS source | PASS source |

`PASS source` means static source/cascade/interaction inspection, not live production screenshot verification.

## Wider viewport guard

- `<=1180px`: touch navigation/hamburger.
- `>=1181px`: full desktop navigation and JS state reset.
- JS and CSS responsive ownership are now aligned.

## CRO / tracking audit

Theme source does **not** currently contain a GA4/GTM vendor ID or a `dataLayer`/`gtag` integration. No fake vendor key was added.

Current state:

- native page rendering/navigation: present;
- `page_view`: depends on external analytics integration, **CHƯA XÁC MINH**;
- `scroll`: **CHƯA XÁC MINH**;
- `form_start` / `form_submit`: **CHƯA XÁC MINH**;
- `ClickCTA` / `ClickPhone` / `ClickZalo`: **CHƯA XÁC MINH**;
- UTM preservation: no theme-level rewriting that intentionally strips query parameters was identified in this audit.

Tracking should only be wired once the real GA4/GTM container/property is known; no vendor ID is inferred.

## Performance evidence

FCP, LCP, CLS and PageSpeed mobile are **CHƯA XÁC MINH** because production cannot currently be rendered/tested from the available environment. No synthetic numbers are reported.

## CI / deploy state

For code commit `f0f18faa2df555e0e62641aeefaee376bf31f1d2` at report time:

- Validate Bizrise DDG V2: queued/running;
- Build Bizrise DDG V2 Release: running;
- production deployed SHA: **CHƯA XÁC MINH**.

## Production PASS evidence still required

1. `deployed_sha` equals a validated/released current HEAD or descendant;
2. `/`, `/san-pham/`, representative category/single product, brand, article, contact/store locator render successfully;
3. 360/390/430 browser QA: hamburger/submenu, no horizontal overflow, 2-column product grid, hero/CTA/typography/footer;
4. desktop >=1181px full nav does not break;
5. catalog remains non-empty and WooCommerce-owned;
6. performance/tracking evidence where instrumentation is actually available.

## Status

**MOBILE SOURCE: PASS**

**RESPONSIVE NAV JS/CSS ALIGNMENT: FIXED**

**EXACT CODE CI: RUNNING**

**PRODUCTION DEPLOY: CHƯA XÁC MINH**

**BROWSER QA 360/390/430: CHƯA XÁC MINH**
