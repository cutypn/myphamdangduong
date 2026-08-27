# DDG Frontend Recovery — latest

## Current result

Mobile-first recovery continues on `codex/rebuild-v2` without changing Product Truth, publication rules or WooCommerce mapping. Product/Data Recovery remains authoritative: public catalog = WooCommerce `post_type=product`; internal `bizrise_product` remains non-public/non-queryable and must not own `/san-pham/`. Latest Product Recovery reports controlled manifest **44**, controlled matched **44**, last verified controlled wrong Featured Image **0**; unmanaged/legacy missing-media rows remain separate for deterministic Product/Data classification.

## P0 mobile fix this run

Static cascade audit found a real regression introduced by the new Product Mockup 2.2 stylesheet: because `product-mockup.css` is enqueued after `theme212.css`, its phone rules overrode the earlier ~44px interaction targets. At `<=720px`, product category filter pills were only **38px** high and product-card CTA was only **40px** high.

Fixes applied:

- product mockup stylesheet updated to **2.2.1**;
- product filter pills now keep `min-height:44px` at desktop and phone breakpoints;
- product-card CTA now keeps `min-height:44px` and explicit flex centering on phone;
- archive and `/san-pham/` fallback template asset version changed from `2.2.0` to `2.2.1` to break browser/CDN cache;
- product layout remains two columns on phone, portrait 9:16 media, source image `object-fit:contain`;
- no Product Truth or publication-rule changes.

Frontend code commits in this run:

- `fac49a88b79fedfe3afaf3ae7420dbc6c6bf974c` — restore 44px product interaction targets;
- `877262132c9461b041cdd17e49ffc28c2375af55` — bump Woo archive mockup asset to 2.2.1;
- `c1d405d32eb20ac2dab3fd9e4b58a3dc43bdb1c6` — bump `/san-pham/` fallback mockup asset to 2.2.1.

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

## CI

Exact frontend code SHA: `c1d405d32eb20ac2dab3fd9e4b58a3dc43bdb1c6`.

- Validate Bizrise DDG V2 run `33071326418`: **SUCCESS**.
- Build Bizrise DDG V2 Release run `33071326432`: **SUCCESS**.

## Production / browser verification

Live production verification is still unavailable from this execution environment. Direct REST/page opening cannot be established from current web retrieval, so no production visual claim is made.

Required production PASS evidence remains:

1. deployed SHA equals `c1d405d32eb20ac2dab3fd9e4b58a3dc43bdb1c6` or validated descendant;
2. `/san-pham/` at 360, 390, 430 and desktop >=1180;
3. representative `product_cat` archive;
4. >=8 product singles across brands;
5. homepage/core pages;
6. `/kien-thuc/` plus representative article pages;
7. sticky header/menu/submenu interaction including Escape and orientation/viewport transition;
8. zero horizontal overflow at 360/390/430;
9. product filter chips and card CTA render with >=44px touch height;
10. visual polish of product 9:16 cards, spacing, hero height, typography, CTA visibility and footer.

## Status

**MOBILE SOURCE: PASS**

**EXACT FRONTEND CI: PASS**

**PRODUCTION DEPLOY: CHƯA XÁC MINH**

**BROWSER QA 360/390/430: CHƯA XÁC MINH**
