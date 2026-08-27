# DDG Frontend Recovery — latest

## Current result

Mobile-first source hardening continues on `codex/rebuild-v2` without changing Product Truth, publication rules, product mapping or WooCommerce data.

Product/Data Recovery remains authoritative for catalog data: `/san-pham/` must use WooCommerce `post_type=product`; internal `bizrise_product` remains non-public/non-queryable and must not own the storefront route.

## P0 issue fixed this run

A source/cascade audit found a reproducible horizontal-overflow risk on article cards at `<=520px`:

```css
.t2-article-card { grid-template-columns:34% minmax(0,66%); }
```

The two percentage columns already consume 100% of the grid width, while the inherited article-grid gap adds another 14px. On 360/390/430px phones this can make the article card exceed its container.

Fix commit: `0f654428e8c5e7e7fbc818f7bd5ebf11a8bb2279`

File: `apps/bizrise-ddg-theme/header.php`

A small post-`wp_head()` mobile overflow guard now forces:

- article card columns to `minmax(104px,32%) minmax(0,1fr)` at `<=520px`;
- article copy to `min-width:0` so long titles/excerpts can shrink and wrap instead of widening the grid.

This preserves the existing horizontal editorial-card design while eliminating the `100% + gap` geometry.

## Mobile checklist

| Check | 360 | 390 | 430 |
|---|---|---|---|
| Sticky header source geometry | PASS source | PASS source | PASS source |
| Logo/hamburger fit | PASS source | PASS source | PASS source |
| Menu/submenu links >=44px | PASS source | PASS source | PASS source |
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

`PASS source` means static source/cascade inspection. It is not a browser screenshot PASS.

## CI

Exact frontend fix SHA: `0f654428e8c5e7e7fbc818f7bd5ebf11a8bb2279`.

- Validate Bizrise DDG V2 run `33053798958`: **SUCCESS**.
- Build Bizrise DDG V2 Release run `33053798953`: **SUCCESS**.

## Product/Data context

Latest Product Recovery report states:

- controlled manifest: **44**;
- controlled matched: **44**;
- last verified controlled wrong Featured Image: **0**;
- last verified product/poster missing or ambiguity: **0**;
- unmanaged/legacy public missing Featured Image candidates remain a Product/Data concern and are not hidden or fuzzy-mapped by frontend.

## Production / browser verification

Current execution environment still cannot reliably fetch production REST/browser pages, so no live visual claim is made.

Required production PASS evidence remains:

1. deployed SHA equals this fix or a validated descendant;
2. `/san-pham/` at 360, 390, 430 and desktop >=1180;
3. representative `product_cat` archive;
4. >=8 product singles across brands;
5. homepage/core pages;
6. `/kien-thuc/` plus representative article pages;
7. sticky header/menu/submenu interaction;
8. zero horizontal overflow at 360/390/430;
9. visual polish of product 9:16 cards, spacing, hero height, typography, CTA visibility and footer.

## Status

**MOBILE SOURCE: PASS**

**EXACT FIX CI: PASS**

**PRODUCTION DEPLOY: CHƯA XÁC MINH**

**BROWSER QA 360/390/430: CHƯA XÁC MINH**
