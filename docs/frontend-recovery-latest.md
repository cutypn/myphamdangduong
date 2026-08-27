# DDG Frontend Recovery — latest

## Current result

Mobile-first hardening continues on `codex/rebuild-v2` without changing Product Truth, publication rules, WooCommerce mapping or catalog data.

Product/Data Recovery remains authoritative: public catalog = WooCommerce `post_type=product`; internal `bizrise_product` remains non-public/non-queryable and must not own `/san-pham/`. Latest Product Recovery still reports controlled manifest **44**, controlled matched **44**, last verified controlled wrong Featured Image **0**, with unmanaged/legacy missing-media rows kept separate for deterministic Product/Data classification.

## P0 mobile layout fix this run

Static source audit found a real mismatch between the previous report and the CSS actually on branch: the mobile article card still used `grid-template-columns:34% minmax(0,66%)` while the grid also had a column gap. At 360/390/430 this can make the computed track widths plus gap exceed the available card width and create horizontal overflow or compressed copy.

Fixes:

- CSS commit: `9a6fe16fd8362db8ebb156eabdacc7a55a503ddf`
- Cache/version commit: `5abcccdd02a99540440e29b7a7c12ab6c12fec2f`
- Files:
  - `apps/bizrise-ddg-theme/assets/css/theme212.css`
  - `apps/bizrise-ddg-theme/functions.php`

Changes:

- article mobile grid changed to `minmax(104px,32%) minmax(0,1fr)` so the second track consumes remaining width instead of declaring another percentage beside a gap;
- `.t2-article-card__copy{min-width:0}` prevents long content from forcing the grid wider;
- article H3 now uses `overflow-wrap:anywhere` as a final guard for long tokens;
- theme asset version bumped to **2.1.7** so mobile browsers do not keep the pre-fix stylesheet in cache;
- approved product mockup behavior remains untouched: two portrait cards, 9:16 media frame, `object-fit:contain`.

## Mobile checklist

| Check | 360 | 390 | 430 |
|---|---|---|---|
| Sticky header source geometry | PASS source | PASS source | PASS source |
| Logo/hamburger fit | PASS source | PASS source | PASS source |
| Hamburger tap target ~44px | PASS source | PASS source | PASS source |
| Menu/submenu links >=44px | PASS source | PASS source | PASS source |
| Escape closes menu + restores focus | PASS source | PASS source | PASS source |
| Resize/orientation to desktop clears scroll lock | PASS source | PASS source | PASS source |
| Article-card horizontal overflow guard | PASS source | PASS source | PASS source |
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

Exact latest frontend SHA: `5abcccdd02a99540440e29b7a7c12ab6c12fec2f`.

- Validate Bizrise DDG V2 run `33062494815`: **SUCCESS**.
- Build Bizrise DDG V2 Release run `33062494824`: **SUCCESS**.

These workflows include the repository's PHP/source validation and release build checks.

## Production / browser verification

Current execution environment still cannot reliably fetch `dangduonggroup.com` production REST/browser pages, so no live visual claim is made.

Required production PASS evidence remains:

1. deployed SHA equals `5abcccdd02a99540440e29b7a7c12ab6c12fec2f` or a validated descendant;
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
