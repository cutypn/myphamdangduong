# DDG Frontend Recovery — latest

## Current result

Mobile-first hardening continues on `codex/rebuild-v2` without changing Product Truth, publication rules, WooCommerce mapping or catalog data.

Product/Data Recovery remains authoritative: public catalog = WooCommerce `post_type=product`; internal `bizrise_product` remains non-public/non-queryable and must not own `/san-pham/`. Latest Product Recovery still reports controlled manifest **44**, controlled matched **44**, last verified controlled wrong Featured Image **0**, with unmanaged/legacy missing-media rows kept separate for deterministic Product/Data classification.

## P0 mobile UX fix this run

Source audit found the hamburger controller could leave mobile UI in a stale locked state:

- menu could not be closed with `Escape`;
- if the viewport crossed from mobile/tablet to desktop while menu was open, `body.t2-menu-open` could remain set and preserve scroll locking;
- keyboard users did not get focus restored to the menu toggle after closing with Escape.

Fix commit: `b6d5cdd2ad85cf25c5b99d826ce0abb80522d2a3`

File: `apps/bizrise-ddg-theme/assets/js/theme2.js`

Changes:

- centralized menu state into `setMenuState()`;
- `Escape` closes the menu and restores focus to the hamburger button;
- `matchMedia('(min-width: 981px)')` clears open/locked mobile state when crossing into desktop;
- nav-link clicks still close the menu;
- legacy `MediaQueryList.addListener` fallback retained for older browsers.

This directly hardens menu interaction on 360 / 390 / 430px and prevents a stale `overflow:hidden` body state after orientation/viewport changes.

## Mobile checklist

| Check | 360 | 390 | 430 |
|---|---|---|---|
| Sticky header source geometry | PASS source | PASS source | PASS source |
| Logo/hamburger fit | PASS source | PASS source | PASS source |
| Hamburger tap target ~44px | PASS source | PASS source | PASS source |
| Menu/submenu links >=44px | PASS source | PASS source | PASS source |
| Escape closes menu + restores focus | PASS source | PASS source | PASS source |
| Resize/orientation to desktop clears scroll lock | PASS source | PASS source | PASS source |
| No known article-card horizontal overflow | PASS source | PASS source | PASS source |
| Product CTA >=44px | PASS source | PASS source | PASS source |
| Article CTA >=44px | PASS source | PASS source | PASS source |
| Pagination target >=44px | PASS source | PASS source | PASS source |
| Product grid stays 2 columns | PASS source | PASS source | PASS source |
| Product media stays portrait 9:16 | PASS source | PASS source | PASS source |
| Product images use `object-fit:contain` | PASS source | PASS source | PASS source |
| Filter pills horizontal-scroll | PASS source | PASS source | PASS source |
| Sort select >=44px | PASS source | PASS source | PASS source |
| Single product image contained | PASS source | PASS source | PASS source |
| H1/hero scale moderated | PASS source | PASS source | PASS source |
| Article body/type scale readable | PASS source | PASS source | PASS source |
| Tables scroll instead of breaking viewport | PASS source | PASS source | PASS source |
| Form controls avoid phone auto-zoom | PASS source | PASS source | PASS source |
| Footer compact | PASS source | PASS source | PASS source |

`PASS source` means static source/cascade/interaction-code inspection, not live browser screenshot verification.

## CI

Exact frontend code SHA: `b6d5cdd2ad85cf25c5b99d826ce0abb80522d2a3`.

- Validate Bizrise DDG V2 run `33057980136`: **SUCCESS**.
- Build Bizrise DDG V2 Release run `33057980153`: **SUCCESS**.

## Production / browser verification

Current execution environment still cannot reliably fetch `dangduonggroup.com` production REST/browser pages, so no live visual claim is made.

Required production PASS evidence remains:

1. deployed SHA equals this fix or a validated descendant;
2. `/san-pham/` at 360, 390, 430 and desktop >=1180;
3. representative `product_cat` archive;
4. >=8 product singles across brands;
5. homepage/core pages;
6. `/kien-thuc/` plus representative article pages;
7. sticky header/menu/submenu interaction including Escape and orientation/viewport transition;
8. zero horizontal overflow at 360/390/430;
9. visual polish of product 9:16 cards, spacing, hero height, typography, CTA visibility and footer.

## Status

**MOBILE SOURCE: PASS**

**EXACT FIX CI: PASS**

**PRODUCTION DEPLOY: CHƯA XÁC MINH**

**BROWSER QA 360/390/430: CHƯA XÁC MINH**
