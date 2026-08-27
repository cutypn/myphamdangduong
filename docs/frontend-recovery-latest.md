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

A real logged-in mobile sticky-header regression remained in the CSS cascade. WordPress uses a 46px admin bar below 783px, but at `<=600px` core changes `#wpadminbar` from fixed to absolute. The theme still forced `.admin-bar .t2-header{top:46px}` and an admin-bar-adjusted fixed-nav top, so after the admin bar scrolled away the theme header/menu could retain a false 46px gap on phone widths.

Fix applied in `apps/bizrise-ddg-theme/header.php` as the final theme-side mobile guard after `wp_head()`:

- `<=600px`: logged-in sticky header returns to `top:0` after the absolute WP admin bar scrolls away;
- `521–600px`: logged-in fixed nav aligns to the 72px theme header and uses `100dvh - 72px`;
- `<=520px`: logged-in fixed nav aligns to the canonical 64px phone header and uses `100dvh - 64px`;
- existing article overflow guard is retained;
- 601–782px keeps the 46px offset required while the WordPress admin bar is fixed;
- no Product Truth, SKU, media, publish status or WooCommerce visibility rule changed.

Exact frontend code SHA: `da9e8207322501e35e9ebe8ecc24d26b6d4de2c6`.

CI for that exact code SHA:

- Validate Bizrise DDG V2 run `33103325112`: **SUCCESS**;
- Build Bizrise DDG V2 Release run `33103325195`: **SUCCESS**.

## Mobile checklist

| Check | 360 | 390 | 430 |
|---|---|---|---|
| Sticky header source geometry | PASS source | PASS source | PASS source |
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
5. logged-in phone scroll test proving no 46px stale gap after WP admin bar scrolls away;
6. hamburger/submenu interaction and zero horizontal overflow;
7. visual polish of 9:16 cards, spacing, hero height, typography, CTA visibility and footer.

## Status

**MOBILE SOURCE: PASS**

**EXACT CODE CI: PASS**

**PRODUCTION DEPLOY: CHƯA XÁC MINH**

**BROWSER QA 360/390/430: CHƯA XÁC MINH**
