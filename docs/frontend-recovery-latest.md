# DDG Frontend Recovery — latest

## Current result

Mobile-first recovery continues on `codex/rebuild-v2` without changing Product Truth, publication rules or WooCommerce mapping. Product/Data Recovery remains authoritative: public catalog = WooCommerce `post_type=product`; internal `bizrise_product` remains non-public/non-queryable and must not own `/san-pham/`.

Latest Product/Data Recovery state read before this frontend change:

- controlled manifest: **44**;
- controlled matched: **44**;
- last verified controlled wrong Featured Image: **0**;
- unmanaged/legacy missing-media rows remain separate for deterministic classification;
- production on current HEAD remains **CHƯA XÁC MINH**.

## P0 frontend audit / fix this run

Static mobile interaction audit re-checked the current deterministic 8-branch header after the latest navigation change. Product grid, mobile dimensions, CTA/tap targets and WooCommerce catalog routing remain intact in source.

A real accessibility/state bug remained in the mobile hamburger/submenu interaction: visual and `aria-expanded` state changed, but assistive labels still always announced “Mở menu” / “Mở menu con” even while those controls were already open.

Fix applied in `apps/bizrise-ddg-theme/assets/js/theme2.js`:

- hamburger label now switches between `Mở menu` and `Đóng menu` in sync with `aria-expanded`;
- screen-reader text and `aria-label` stay synchronized;
- each submenu toggle stores its parent label and switches between `Mở menu con: …` and `Đóng menu con: …`;
- closing by Escape, link navigation or desktop viewport reset restores the closed labels;
- no Product Truth, SKU, media assignment, publish status or WooCommerce visibility rule changed.

Exact frontend code SHA for this fix: `7dce80097091c8faa6b6d6df4fc35ef0b5dcc3ec`.

## Mobile checklist

| Check | 360 | 390 | 430 |
|---|---|---|---|
| Sticky header source geometry | PASS source | PASS source | PASS source |
| Logged-in WP admin-bar offset | PASS source | PASS source | PASS source |
| Logo/hamburger fit | PASS source | PASS source | PASS source |
| Hamburger target >=44px | PASS source | PASS source | PASS source |
| Hamburger accessible label follows state | PASS source | PASS source | PASS source |
| Parent menu links remain navigable | PASS source | PASS source | PASS source |
| Submenu toggle target 44×44px | PASS source | PASS source | PASS source |
| Submenu explicit open/close state | PASS source | PASS source | PASS source |
| Submenu accessible label follows state | PASS source | PASS source | PASS source |
| Escape closes submenu then menu | PASS source | PASS source | PASS source |
| Resize/orientation clears scroll lock/state | PASS source | PASS source | PASS source |
| Product search control >=48px | PASS source | PASS source | PASS source |
| Product filter pills >=44px | PASS source | PASS source | PASS source |
| Product-card CTA >=44px | PASS source | PASS source | PASS source |
| Product grid stays 2 columns | PASS source | PASS source | PASS source |
| Product media stays portrait 9:16 | PASS source | PASS source | PASS source |
| Product images use `object-fit:contain` | PASS source | PASS source | PASS source |
| Filter pills horizontal-scroll | PASS source | PASS source | PASS source |
| Sort select >=44px | PASS source | PASS source | PASS source |
| Single product image contained | PASS source | PASS source | PASS source |
| H1/hero scale moderated | PASS source | PASS source | PASS source |
| Article-card overflow guard | PASS source | PASS source | PASS source |
| Article body/type scale readable | PASS source | PASS source | PASS source |
| Tables scroll instead of breaking viewport | PASS source | PASS source | PASS source |
| Form controls avoid phone auto-zoom | PASS source | PASS source | PASS source |
| Footer nav/contact/legal targets >=44px | PASS source | PASS source | PASS source |
| Footer compact | PASS source | PASS source | PASS source |

`PASS source` means static source/cascade/interaction inspection, not live screenshot verification.

## Product / tablet / desktop guards retained

- `/san-pham/` remains WooCommerce-backed; fallback page queries public WooCommerce `product`, not Product Truth.
- Fallback catalog search passes the sanitized `s` keyword into the WooCommerce product query and preserves it through pagination.
- Product filter taxonomy row wraps at tablet/desktop widths and horizontal-scrolls on phone.
- Product grid remains 2 columns on canonical phone widths, 3 columns at tablet, 4 columns on larger desktop.
- Product cards remain portrait 9:16 with `object-fit:contain`, avoiding image crop.
- Related-product query retains canonical WooCommerce `exclude-from-catalog` exclusion from Product/Data Recovery.

## Production / browser verification

Live production/browser retrieval remains unavailable from this execution environment, so no screenshot-based production claim is made.

Required production PASS evidence remains:

1. deployed SHA equals current validated HEAD or descendant;
2. `/san-pham/` at 360, 390, 430 and desktop >=1180;
3. representative `product_cat` archive;
4. representative product singles across brands;
5. homepage/core pages;
6. `/kien-thuc/` plus representative article pages;
7. hamburger + submenu interaction including state labels, Escape and viewport transition;
8. zero horizontal overflow at 360/390/430;
9. primary/secondary CTA and footer targets render at ~44px minimum height;
10. visual polish of product 9:16 cards, spacing, hero height, typography, CTA visibility and footer.

## Status

**MOBILE SOURCE: PASS**

**MOBILE MENU STATE LABELS: PASS SOURCE**

**PRODUCTION DEPLOY: CHƯA XÁC MINH**

**BROWSER QA 360/390/430: CHƯA XÁC MINH**
