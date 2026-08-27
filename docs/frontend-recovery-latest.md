# DDG Frontend Recovery — latest

## Current result

Mobile-first source hardening completed on `codex/rebuild-v2` without changing Product Truth or publication rules.

Product/Data Recovery remains the catalog-data reference: `/san-pham/` must use WooCommerce `post_type=product`; internal `bizrise_product` remains non-public/non-queryable and must not own the storefront route.

## P0 mobile issue fixed this run

A source audit found one remaining tap-target regression inside product cards: the mobile override forced `.t2-product-card__copy .t2-text-link` to `min-height:36px`, below the project's ~44px mobile target.

Fix applied:

- product-card CTA now has `min-height:44px` and flex vertical centering;
- article-card CTA now has `min-height:44px`;
- pagination links/spans now have `min-height:44px`;
- Theme asset version bumped `2.1.5` → `2.1.6` so production browsers do not retain the previous mobile CSS;
- canonical product card remains two-column portrait 9:16 at 360/390/430 with `object-fit:contain`.

## Code

- `321b20feb8f3a6ffe553c285309a22700b9b54d8` — enforce 44px mobile tap targets in CSS.
- `6c60c7dc7cedd2c86686b489238f7d78d4b0ee52` — bump theme asset version to 2.1.6.

Files:

- `apps/bizrise-ddg-theme/assets/css/theme212.css`
- `apps/bizrise-ddg-theme/functions.php`

## Mobile checklist

| Check | 360 | 390 | 430 |
|---|---|---|---|
| Sticky header source geometry | PASS source | PASS source | PASS source |
| Logo/hamburger fit | PASS source | PASS source | PASS source |
| Menu/submenu links >=44px | PASS source | PASS source | PASS source |
| Product CTA >=44px | PASS source | PASS source | PASS source |
| Article CTA >=44px | PASS source | PASS source | PASS source |
| Pagination target >=44px | PASS source | PASS source | PASS source |
| Product grid stays 2 columns | PASS source | PASS source | PASS source |
| Product media stays 9:16 | PASS source | PASS source | PASS source |
| Product images use contain, not crop | PASS source | PASS source | PASS source |
| Filter pills horizontal-scroll | PASS source | PASS source | PASS source |
| Sort select >=44px | PASS source | PASS source | PASS source |
| Single product image contained | PASS source | PASS source | PASS source |
| H1/hero scale moderated | PASS source | PASS source | PASS source |
| Article body/type scale readable | PASS source | PASS source | PASS source |
| Tables scroll instead of breaking viewport | PASS source | PASS source | PASS source |
| Form controls avoid phone auto-zoom | PASS source | PASS source | PASS source |
| Footer compact | PASS source | PASS source | PASS source |

`PASS source` means source/cascade inspection only, not browser screenshot evidence.

## CI

Exact final SHA: `6c60c7dc7cedd2c86686b489238f7d78d4b0ee52`.

- Validate Bizrise DDG V2 run `33049212990`: **SUCCESS**.
- Build Bizrise DDG V2 Release run `33049212972`: **SUCCESS**.

The preceding CSS commit `321b20f…` also passed both Validate and Release before the asset-version bump.

## Product/Data context

Latest Product Recovery report continues to show:

- controlled manifest: 44;
- controlled matched: 44;
- last verified controlled wrong Featured Image: 0;
- last verified product/poster missing or ambiguity: 0;
- unmanaged/legacy public missing Featured Image candidates remain a Product/Data concern and are not hidden or fuzzy-mapped by frontend.

No frontend code in this run changes publication status, Product Truth, product mapping or product media records.

## Production / browser verification

Production is not marked PASS from this runtime because public REST/browser access is still not reliably available here. The Deploy Bridge should pick up the final SHA after CI success, but deployed SHA must be confirmed independently before claiming production PASS.

Required browser checks remain:

1. `/san-pham/` at 360, 390, 430 and desktop >=1180.
2. Representative `product_cat` archive.
3. >=8 representative product singles across brands.
4. Homepage/core pages.
5. `/kien-thuc/` and representative article pages.
6. Sticky header/menu opening and submenu tapping.
7. No horizontal overflow at 360/390/430.
8. Actual visual polish of portrait cards, spacing, hero height, typography, CTA visibility and footer.

## Status

**MOBILE SOURCE: PASS**

**FINAL CI: PASS**

**PRODUCTION DEPLOY: CHƯA XÁC MINH**

**BROWSER QA 360/390/430: CHƯA XÁC MINH**
