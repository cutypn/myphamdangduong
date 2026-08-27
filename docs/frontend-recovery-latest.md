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

The product mockup was structurally mobile-safe but its phone typography had become too small to read comfortably in the approved two-column layout. At `<=720px`, product-card brand/kicker/title/CTA text had values as low as `0.49rem–0.69rem` (roughly 8–11px at a 16px root). That preserves layout but fails the mobile-first readability goal at 360/390/430px.

Fix applied in `apps/bizrise-ddg-theme/assets/css/product-mockup.css`:

- mockup asset bumped **2.2.2 → 2.2.3**;
- mobile media-brand increased to `0.62rem` with safer line-height;
- media title increased to `0.68rem` and pack text to `0.62rem`;
- card kicker increased to `0.62rem`;
- product title increased to `0.78rem` (`0.76rem` guard at <=380px);
- CTA increased to `0.68rem` while retaining a **44px minimum tap target**;
- filter chips increased to `0.72rem` while retaining **44px minimum height**;
- copy block height was slightly increased so the larger type does not collide or squeeze the CTA;
- product grid remains **2 columns** on canonical phone widths;
- product media remains portrait **9:16** with the existing `object-fit:contain` cascade;
- both WooCommerce archive and `/san-pham/` fallback now request asset version **2.2.3** to avoid stale phone CSS.

Files changed:

- `apps/bizrise-ddg-theme/assets/css/product-mockup.css`
- `apps/bizrise-ddg-theme/woocommerce/archive-product.php`
- `apps/bizrise-ddg-theme/page-product-catalog.php`

Exact frontend code SHA after the asset/version changes: `0b6cfee33716577c8d3abbabfef3b298ca2db2bf`.

CI for that exact code SHA:

- Validate Bizrise DDG V2 run `33112705646`: **SUCCESS**;
- Build Bizrise DDG V2 Release run `33112705609`: **SUCCESS**.

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
| Product card typography readable | PASS source | PASS source | PASS source |
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

Direct retrieval of `dangduonggroup.com` is still unavailable from this execution environment, so no screenshot/browser-production claim is made.

Required production PASS evidence remains:

1. deployed SHA equals current validated HEAD or descendant;
2. `/san-pham/` at 360, 390, 430 and desktop >=1180;
3. representative `product_cat` archive and product singles;
4. homepage/core pages and `/kien-thuc/` article views;
5. mobile visual confirmation that larger product-card typography remains balanced in two columns;
6. single-product taps on `#mo-ta` and `#cong-bo` proving target headings remain visible below the sticky header;
7. logged-in phone scroll test proving no stale admin-bar gap;
8. hamburger/submenu interaction and zero horizontal overflow;
9. visual polish of 9:16 cards, spacing, hero height, typography, CTA visibility and footer.

## Status

**MOBILE SOURCE: PASS**

**EXACT CODE CI: PASS**

**PRODUCTION DEPLOY: CHƯA XÁC MINH**

**BROWSER QA 360/390/430: CHƯA XÁC MINH**
