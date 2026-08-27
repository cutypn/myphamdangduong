# DDG Frontend Recovery — latest

## Current result

Mobile-first recovery continues on `codex/rebuild-v2` without changing Product Truth, publication rules or WooCommerce mapping. Product/Data Recovery remains authoritative: public catalog = WooCommerce `post_type=product`; internal `bizrise_product` remains non-public/non-queryable and must not own `/san-pham/`. Latest Product Recovery reports controlled manifest **44**, controlled matched **44**, last verified controlled wrong Featured Image **0**; unmanaged/legacy missing-media rows remain separate for deterministic Product/Data classification.

## P0 frontend fix this run

Static cascade audit found a real responsive regression in Product Mockup 2.2.1: category filter pills only switched to horizontal scrolling at `<=720px`. Between **721px and 980px** the filter remained a single non-wrapping flex row, so a larger WooCommerce product taxonomy could push beyond the content width and create horizontal overflow on tablets/small laptops.

Fixes applied:

- product mockup stylesheet updated to **2.2.2**;
- base product filter row now uses `flex-wrap:wrap` plus `min-width:0`, preventing the taxonomy row from widening its container on desktop/tablet;
- phone breakpoint `<=720px` intentionally overrides back to `flex-wrap:nowrap` + `overflow-x:auto`, preserving the approved horizontal-chip mobile interaction;
- filter pills retain `min-height:44px` and product-card CTA retains `min-height:44px` on phone;
- Woo archive and `/san-pham/` fallback both bump the product mockup asset from `2.2.1` to `2.2.2` to invalidate browser/CDN cache;
- product layout remains two columns on phone, portrait 9:16 media, source image `object-fit:contain`;
- no Product Truth or publication-rule changes.

Frontend code commits this run:

- `ae7846622ea00d0a457ba64352fdb7ee59939406` — prevent product filter overflow on tablet;
- `5d53d29dc9c0cf33cb592e216403b8c5b0c6aa8f` — bump Woo archive asset to 2.2.2;
- `9284d7db87a47cce39b95ed21d848fee82536923` — bump `/san-pham/` fallback asset to 2.2.2.

Files:

- `apps/bizrise-ddg-theme/assets/css/product-mockup.css`
- `apps/bizrise-ddg-theme/woocommerce/archive-product.php`
- `apps/bizrise-ddg-theme/page-product-catalog.php`

## Mobile checklist

| Check | 360 | 390 | 430 |
|---|---|---|---|
| Sticky header source geometry | PASS source | PASS source | PASS source |
| Logged-in WP admin-bar offset | PASS source | PASS source | PASS source |
| Logo/hamburger fit | PASS source | PASS source | PASS source |
| Hamburger target >=44px | PASS source | PASS source | PASS source |
| Menu/submenu links >=44px | PASS source | PASS source | PASS source |
| Escape closes menu + restores focus | PASS source | PASS source | PASS source |
| Resize/orientation clears scroll lock | PASS source | PASS source | PASS source |
| Product search control >=48px | PASS source | PASS source | PASS source |
| Product filter pills >=44px | PASS source | PASS source | PASS source |
| Product CTA >=44px | PASS source | PASS source | PASS source |
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
| Footer compact | PASS source | PASS source | PASS source |

`PASS source` means static source/cascade/interaction inspection, not live screenshot verification.

## Tablet / desktop overflow guard

- Product filter taxonomy row: **PASS source** at 721–980px via wrapping.
- Product filter taxonomy row: **PASS source** at >=981px via wrapping.
- Product grid: 3 columns at <=980px, 4 columns on larger desktop.
- Phone filter chips remain horizontal-scroll instead of wrapping into tall multi-row controls.

## CI

Exact frontend code SHA to verify: `9284d7db87a47cce39b95ed21d848fee82536923`.

CI status is updated after the exact SHA workflows complete.

## Production / browser verification

Live production verification remains unavailable from this execution environment, so no production visual claim is made.

Required production PASS evidence remains:

1. deployed SHA equals `9284d7db87a47cce39b95ed21d848fee82536923` or validated descendant;
2. `/san-pham/` at 360, 390, 430 and desktop >=1180;
3. representative `product_cat` archive;
4. >=8 product singles across brands;
5. homepage/core pages;
6. `/kien-thuc/` plus representative article pages;
7. sticky header/menu/submenu interaction including Escape and orientation/viewport transition;
8. zero horizontal overflow at 360/390/430 and no category-pill overflow at 721–980;
9. product filter chips and card CTA render with >=44px touch height;
10. visual polish of product 9:16 cards, spacing, hero height, typography, CTA visibility and footer.

## Status

**MOBILE SOURCE: PASS**

**TABLET FILTER OVERFLOW SOURCE: PASS**

**EXACT FRONTEND CI: PENDING**

**PRODUCTION DEPLOY: CHƯA XÁC MINH**

**BROWSER QA 360/390/430: CHƯA XÁC MINH**
