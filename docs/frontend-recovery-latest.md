# DDG Frontend Recovery — latest

## Current result

Mobile-first recovery continues on `codex/rebuild-v2` without changing Product Truth, publication rules or WooCommerce mapping. Product/Data Recovery remains authoritative: public catalog = WooCommerce `post_type=product`; internal `bizrise_product` remains non-public/non-queryable and must not own `/san-pham/`.

Latest Product/Data Recovery state read before this frontend change:

- controlled manifest: **44**;
- controlled matched: **44**;
- last verified controlled wrong Featured Image: **0**;
- unmanaged/legacy missing-media rows remain separate for deterministic classification;
- production on current HEAD remains **CHƯA XÁC MINH**.

## P0 frontend fix this run

Static mobile interaction audit found a real navigation regression: submenu parents existed in the header, but the JS only toggled the whole hamburger menu and closed it on every link click. There was no dedicated mobile control for expanding/collapsing submenu branches such as `Năng lực` and `Sản phẩm & Routine`.

Fix applied:

- create a dedicated submenu toggle button for every `.menu-item-has-children` item;
- submenu toggle is independent from the parent link, so the parent page remains directly navigable;
- toggle exposes `aria-expanded` + `aria-controls` and a localized accessible label;
- button target is **44×44px** on mobile/tablet;
- only one submenu stays open at a time;
- Escape closes an open submenu first and restores focus to its toggle; a second Escape closes the main mobile menu and restores focus to the hamburger;
- viewport transition to desktop clears mobile menu/submenu state;
- submenu is explicit `display:none` / `display:block` below 981px rather than depending on hover/focus behavior;
- fallback menu regression was repaired by restoring `Trang chủ` before the approved site branches;
- no Product Truth, SKU mapping, publication status or WooCommerce visibility rule changed.

Files changed:

- `apps/bizrise-ddg-theme/assets/js/theme2.js`
- `apps/bizrise-ddg-theme/style.css`
- `apps/bizrise-ddg-theme/header.php`

Frontend exact SHA after the fix:

- `fc61a9941caf3856f86b9a87baa10c141e37635f`

## Mobile checklist

| Check | 360 | 390 | 430 |
|---|---|---|---|
| Sticky header source geometry | PASS source | PASS source | PASS source |
| Logged-in WP admin-bar offset | PASS source | PASS source | PASS source |
| Logo/hamburger fit | PASS source | PASS source | PASS source |
| Hamburger target >=44px | PASS source | PASS source | PASS source |
| Parent menu links remain navigable | PASS source | PASS source | PASS source |
| Submenu toggle target 44×44px | PASS source | PASS source | PASS source |
| Submenu explicit open/close state | PASS source | PASS source | PASS source |
| Submenu ARIA state | PASS source | PASS source | PASS source |
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
- Product filter taxonomy row wraps at tablet/desktop widths and horizontal-scrolls on phone.
- Product grid remains 2 columns on canonical phone widths, 3 columns at tablet, 4 columns on larger desktop.
- Product cards remain portrait 9:16 with `object-fit:contain`, avoiding image crop.
- Related-product query retains canonical WooCommerce `exclude-from-catalog` exclusion from Product/Data recovery.
- Desktop >=981px keeps existing hover/focus submenu behavior; the new submenu buttons are hidden there.

## CI

Exact frontend SHA `fc61a9941caf3856f86b9a87baa10c141e37635f`:

- Validate Bizrise DDG V2 run `33093030727`: **SUCCESS**.
- Build Bizrise DDG V2 Release run `33093030658`: **SUCCESS**.

## Production / browser verification

Live production/browser verification is still unavailable from this execution environment, so no screenshot-based production claim is made.

Required production PASS evidence remains:

1. deployed SHA equals `fc61a9941caf3856f86b9a87baa10c141e37635f` or validated descendant;
2. `/san-pham/` at 360, 390, 430 and desktop >=1180;
3. representative `product_cat` archive;
4. >=8 product singles across brands;
5. homepage/core pages;
6. `/kien-thuc/` plus representative article pages;
7. hamburger + submenu interaction including 44px toggles, parent navigation, Escape and orientation/viewport transition;
8. zero horizontal overflow at 360/390/430;
9. all primary/secondary CTA and footer navigation/contact/legal targets render at ~44px minimum height;
10. visual polish of product 9:16 cards, spacing, hero height, typography, CTA visibility and footer.

## Status

**MOBILE SOURCE: PASS**

**MOBILE SUBMENU SOURCE: PASS**

**EXACT FRONTEND CI: PASS**

**PRODUCTION DEPLOY: CHƯA XÁC MINH**

**BROWSER QA 360/390/430: CHƯA XÁC MINH**
