# DDG Frontend Recovery — latest

## Current result

Mobile-first frontend/CRO recovery continues on `codex/rebuild-v2` without changing Product Truth, publication rules, WooCommerce mapping or product media assignments. Product/Data Recovery remains authoritative: public catalog = WooCommerce `post_type=product`; controlled manifest = 44; last verified controlled matched = 44/44; last verified controlled wrong Featured Image = 0; production on current HEAD remains CHƯA XÁC MINH.

`docs/post-deploy-test-latest.md` is still blocked because production deploy/runtime/frontend cannot currently be reached from the tester, so there is no live screenshot or deployed-SHA evidence yet.

## P0/P1 audit and fix this run

P1 performance/CRO issue found on the homepage media path:

- homepage hero/collage/about images were emitted from raw attachment URLs;
- those raw `<img>` tags did not carry WordPress attachment `width` / `height` dimensions or responsive `srcset` / `sizes`;
- this weakened responsive image selection and layout-shift protection, which conflicts with the mobile-first landing-page checklist.

Fix:

- resolve homepage/page Featured Image attachment IDs;
- render hero, capability, brand and about media with `wp_get_attachment_image()`;
- primary LCP hero remains `loading=eager` + `fetchpriority=high` and is not lazy-loaded;
- supporting hero tiles remain eager; below-the-fold About image remains lazy;
- WordPress now emits attachment dimensions and responsive `srcset`/`sizes` automatically;
- explicit descriptive ALT text is retained;
- no product data, Product Truth, HOLD or publication rule changed.

Exact frontend code commit: `82ce6821caf9c6c411855c3a4732340db5bfb93a`.

Files changed:

- `apps/bizrise-ddg-theme/front-page.php`

## Mobile checklist

| Check | 360 | 390 | 430 |
|---|---|---|---|
| Sticky header source geometry | PASS source | PASS source | PASS source |
| Logo/hamburger fit | PASS source | PASS source | PASS source |
| Hamburger/submenu targets >=44px | PASS source | PASS source | PASS source |
| Navigation panel no x-overflow | PASS source | PASS source | PASS source |
| Product search >=48px | PASS source | PASS source | PASS source |
| Product filter/CTA targets >=44px | PASS source | PASS source | PASS source |
| Product grid 2 columns | PASS source | PASS source | PASS source |
| Product media portrait 9:16 / contain | PASS source | PASS source | PASS source |
| Single product image contained | PASS source | PASS source | PASS source |
| Article/body/table/form/footer responsive | PASS source | PASS source | PASS source |
| Homepage hero not lazy-loaded | PASS source | PASS source | PASS source |
| Homepage image width/height/srcset | PASS source | PASS source | PASS source |

`PASS source` means static source/cascade/interaction inspection, not live production screenshot verification.

## CRO / tracking audit

Theme source still does not contain a verified GA4/GTM vendor ID or a verified `dataLayer`/`gtag` integration. No fake vendor key was added.

- `page_view`: external integration dependent, CHƯA XÁC MINH;
- `scroll`: CHƯA XÁC MINH;
- `form_start` / `form_submit`: CHƯA XÁC MINH;
- `ClickCTA` / `ClickPhone` / `ClickZalo`: CHƯA XÁC MINH;
- UTM preservation: no theme-level query-string stripping identified.

## Performance evidence

- LCP hero implementation: source guard improved (`eager` + `fetchpriority=high`, responsive attachment markup).
- CLS guard: source improved through attachment width/height plus responsive markup.
- FCP, measured LCP, measured CLS and PageSpeed mobile: CHƯA XÁC MINH because production cannot currently be rendered/tested from the available environment.

## CI / deploy state

For code commit `82ce6821caf9c6c411855c3a4732340db5bfb93a` at report update time:

- Validate Bizrise DDG V2: running;
- Build Bizrise DDG V2 Release: running;
- production deployed SHA: CHƯA XÁC MINH.

## Production PASS evidence still required

1. `deployed_sha` equals a validated/released current code SHA or descendant;
2. `/`, `/san-pham/`, representative category/single product, brand, article, contact/store locator render successfully;
3. 360/390/430 browser QA: hamburger/submenu, no horizontal overflow, 2-column product grid, hero/CTA/typography/footer;
4. desktop >=1181px full nav does not break;
5. catalog remains non-empty and WooCommerce-owned;
6. measured performance/tracking evidence where instrumentation is actually available.

## Status

**MOBILE SOURCE: PASS**

**RESPONSIVE HOMEPAGE MEDIA: FIXED IN SOURCE**

**EXACT CODE CI: RUNNING**

**PRODUCTION DEPLOY: CHƯA XÁC MINH**

**BROWSER QA 360/390/430: CHƯA XÁC MINH**
