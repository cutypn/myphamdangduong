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

Static cascade audit found a real `/san-pham/` regression: the shared `mobile-p0.css` typography fix was loaded after the product mockup stylesheet, but the product mockup selectors had higher specificity (`.t2-product-archive ...`). That meant the older, smaller mobile archive values still won at 360/390/430 even though the generic mobile guard had been updated.

Fix applied in the canonical product mockup stylesheet instead of adding another override layer:

- product archive brand label: `.72rem`;
- media title: `.72rem`;
- media pack/meta: `.68rem`;
- card kicker: `.68rem`;
- card product title: `.80rem` (`.78rem` at <=380px);
- CTA text: `.70rem`, with minimum tap target still **44px**;
- card copy minimum height: **122px** so larger text can wrap without colliding with the CTA;
- retained phone product grid at **2 columns**;
- retained portrait **9:16** media frame and existing `object-fit:contain` image behavior;
- bumped product mockup cache version from `2.2.3` to `2.2.4` in both WooCommerce archive and `/san-pham/` fallback;
- no plugin UI override created;
- no Product Truth, HOLD, publication, WooCommerce mapping or media assignment logic changed.

Files changed:

- `apps/bizrise-ddg-theme/assets/css/product-mockup.css`
- `apps/bizrise-ddg-theme/woocommerce/archive-product.php`
- `apps/bizrise-ddg-theme/page-product-catalog.php`

Exact frontend code SHA: `259858bf63df2e5914e5833c000cd58ae8dbd85a`.

CI for that exact code SHA:

- Validate Bizrise DDG V2 run `33132616378`: **SUCCESS**;
  - PHP syntax: PASS;
  - deployment shell syntax: PASS;
  - Product Truth seed: PASS;
  - controlled 44-SKU manifest: PASS;
  - JSON/article data validation: PASS.
- Build Bizrise DDG V2 Release run `33132616332`: **SUCCESS**;
  - PHP/deployment validation: PASS;
  - Product Truth/content validation: PASS;
  - release package build: PASS;
  - release artifact upload: PASS.

No JS file changed in this run, so no JS-specific code change required a new behavior test beyond the retained source audit.

## Mobile checklist

| Check | 360 | 390 | 430 |
|---|---|---|---|
| Sticky header source geometry | PASS source | PASS source | PASS source |
| Logo/hamburger fit | PASS source | PASS source | PASS source |
| Hamburger/submenu targets >=44px | PASS source | PASS source | PASS source |
| No known horizontal overflow in theme guards | PASS source | PASS source | PASS source |
| Product search >=48px | PASS source | PASS source | PASS source |
| Product filter pills >=44px | PASS source | PASS source | PASS source |
| Product-card CTA >=44px | PASS source | PASS source | PASS source |
| Product-card typography readable after cascade resolution | PASS source | PASS source | PASS source |
| Product grid 2 columns | PASS source | PASS source | PASS source |
| Product media portrait 9:16 | PASS source | PASS source | PASS source |
| Product images `object-fit:contain` | PASS source | PASS source | PASS source |
| Single product image contained | PASS source | PASS source | PASS source |
| Article card/body responsive | PASS source | PASS source | PASS source |
| Article aside tap targets >=44px | PASS source | PASS source | PASS source |
| Tables scroll instead of viewport break | PASS source | PASS source | PASS source |
| Form controls avoid phone auto-zoom | PASS source | PASS source | PASS source |
| Footer targets >=44px / compact | PASS source | PASS source | PASS source |

`PASS source` means static source/cascade/interaction inspection plus CI, not live screenshot verification.

## Product / desktop guards retained

- `/san-pham/` remains WooCommerce-backed; fallback page queries public WooCommerce `product`, not Product Truth.
- Product grid remains 2 columns on canonical phone widths, 3 columns at tablet and 4 columns on larger desktop.
- Product cards remain portrait 9:16 with the existing `object-fit:contain` cascade.
- Filter pills horizontal-scroll on phone and wrap at wider widths.
- Desktop >=1180px source layout is unchanged by this fix.

## Production / browser verification

Product/Data Recovery attempted production access at 2026-08-28 08:06 ICT and production DNS did not resolve from the execution environment. This frontend run also could not fetch the public site, so no fresh deployed SHA, screenshot or browser evidence is available. Production is therefore not marked PASS.

Required production PASS evidence remains:

1. deployed SHA equals current validated HEAD or descendant;
2. `/san-pham/` at 360, 390, 430 and desktop >=1180;
3. homepage/related product cards at 360/390/430;
4. representative product category and product single;
5. article body/aside, footer and form interaction;
6. hamburger/submenu interaction and zero horizontal overflow.

## Status

**MOBILE SOURCE: PASS**

**EXACT CODE CI: PASS**

**PRODUCTION DEPLOY: CHƯA XÁC MINH**

**BROWSER QA 360/390/430: CHƯA XÁC MINH**
