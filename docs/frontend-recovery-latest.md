# DDG Frontend Recovery — latest

## Current result

Mobile-first recovery continues on `codex/rebuild-v2` without changing Product Truth, publication rules or WooCommerce mapping. Product/Data Recovery remains authoritative: public catalog = WooCommerce `post_type=product`; internal `bizrise_product` remains non-public/non-queryable and must not own `/san-pham/`.

Latest Product/Data Recovery state read before this frontend change:

- controlled manifest: **44**;
- controlled matched: **44**;
- last verified controlled wrong Featured Image: **0**;
- public legal HOLD exclusion: **PASS source + CI**;
- production on current HEAD: **CHƯA XÁC MINH**.

## P0 frontend audit / fix this run

A sticky-header navigation defect remained on the single-product page. The in-page product navigation links to `#mo-ta` and `#cong-bo`, but neither target had `scroll-margin-top`. On 360/390/430px a tap on “Mô tả” or “Phiếu công bố” could therefore land the target heading under the sticky header even though the page itself had no layout overflow.

Fix applied in `apps/bizrise-ddg-theme/header.php` as the final theme-side guard after `wp_head()`:

- `#mo-ta` and `#cong-bo` now reserve **112px** scroll margin on large screens;
- tablet / compact layouts use **96px**;
- canonical phone widths `<=520px` use **80px**, covering the 64px phone header plus breathing room;
- the prior logged-in WordPress admin-bar geometry guards remain unchanged;
- the prior article overflow guard remains unchanged;
- no Product Truth, SKU, media, publish status or WooCommerce visibility rule changed.

Exact frontend code SHA: `ad51ff88f8d8d737316f15cbe4b5f97f27a8983e`.

CI for that exact code SHA:

- Validate Bizrise DDG V2 run `33107990781`: **SUCCESS**;
- Build Bizrise DDG V2 Release run `33107990765`: **SUCCESS**.

## Mobile checklist

| Check | 360 | 390 | 430 |
|---|---|---|---|
| Sticky header source geometry | PASS source | PASS source | PASS source |
| Single-product anchor clears sticky header | PASS source | PASS source | PASS source |
| Logged-in WP admin-bar scroll geometry | PASS source | PASS source | PASS source |
| Logo/hamburger fit | PASS source | PASS source | PASS source |
| Hamburger target >=44px | PASS source | PASS source | PASS source |
| Hamburger/submenu accessible state | PASS source | PASS source | PASS source |
| Submenu target 44×44px | PASS source | PASS source | PASS source |
| Escape/resize state reset | PASS source | PASS source | PASS source |
| No known horizontal overflow in theme guards | PASS source | PASS source | PASS source |
| Product search >=48px | PASS source | PASS source | PASS source |
| Product filter pills >=44px | PASS source | PASS source | PASS source |
| Product-card CTA >=44px | PASS source | PASS source | PASS source |
| Product grid 2 columns | PASS source | PASS source | PASS source |
| Product media portrait 9:16 | PASS source | PASS source | PASS source |
| Product images `object-fit:contain` | PASS source | PASS source | PASS source |
| Single product image contained | PASS source | PASS source | PASS source |
| Article card/body responsive | PASS source | PASS source | PASS source |
| Tables scroll instead of viewport break | PASS source | PASS source | PASS source |
| Form controls avoid phone auto-zoom | PASS source | PASS source | PASS source |
| Footer targets >=44px / compact | PASS source | PASS source | PASS source |

`PASS source` means static source/cascade/interaction inspection plus CI, not live screenshot verification.

## Product / tablet / desktop guards retained

- `/san-pham/` remains WooCommerce-backed; fallback page queries public WooCommerce `product`, not Product Truth.
- Fallback catalog retains Woo `exclude-from-catalog` and explicit legal-HOLD exclusions from Product/Data Recovery.
- Product grid remains 2 columns on canonical phone widths, 3 columns at tablet, 4 columns on larger desktop.
- Product cards remain portrait 9:16 with `object-fit:contain`, avoiding image crop.
- Filter pills horizontal-scroll on phone and wrap at wider widths.

## Production / browser verification

Direct retrieval of `dangduonggroup.com` remains unavailable from this execution environment, so no screenshot/browser-production claim is made.

Required production PASS evidence remains:

1. deployed SHA equals current validated HEAD or descendant;
2. `/san-pham/` at 360, 390, 430 and desktop >=1180;
3. representative `product_cat` archive and product singles;
4. homepage/core pages and `/kien-thuc/` article views;
5. single-product taps on `#mo-ta` and `#cong-bo` proving target headings remain visible below the sticky header;
6. logged-in phone scroll test proving no stale admin-bar gap;
7. hamburger/submenu interaction and zero horizontal overflow;
8. visual polish of 9:16 cards, spacing, hero height, typography, CTA visibility and footer.

## Status

**MOBILE SOURCE: PASS**

**EXACT CODE CI: PASS**

**PRODUCTION DEPLOY: CHƯA XÁC MINH**

**BROWSER QA 360/390/430: CHƯA XÁC MINH**
