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

Static mobile audit found product-card typography was still too compact for the requested 360/390/430 experience. Shared cards used secondary labels around `.58–.60rem` and titles around `.74–.76rem`, which is visually small even when the two-column layout itself does not overflow.

Fix applied:

- increased shared product-card mobile brand/meta/kicker/CTA typography;
- increased mobile product title size while retaining the two-column portrait layout;
- raised card copy minimum height slightly so larger text wraps instead of colliding with the CTA;
- kept product CTA minimum tap target at **44px**;
- kept article-aside links at **44px** minimum tap target;
- bumped the theme-owned `mobile-p0.css` cache version to `2026.08.28.2`;
- no plugin UI override was created;
- no Product Truth, HOLD, publication, WooCommerce mapping or media assignment logic changed.

Files changed:

- `apps/bizrise-ddg-theme/assets/css/mobile-p0.css`
- `apps/bizrise-ddg-theme/header.php`

Exact frontend code SHA: `0907472b23e76b2b770ca5864cedb67eb110de77`.

CI for that exact code SHA:

- Validate Bizrise DDG V2 run `33125803064`: **SUCCESS**;
- Build Bizrise DDG V2 Release run `33125803068`: **SUCCESS**.

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
| Product-card typography readable | PASS source | PASS source | PASS source |
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

Product/Data Recovery attempted production access at 2026-08-28 06:08 ICT and DNS did not resolve from the execution environment. No fresh deployed SHA, screenshot or browser evidence is available in this frontend run, so production is not marked PASS.

Required production PASS evidence remains:

1. deployed SHA equals current validated HEAD or descendant;
2. `/san-pham/` at 360, 390, 430 and desktop >=1180;
3. homepage/related product cards at 360/390/430 to visually verify the new text scale;
4. representative product category and product single;
5. article body/aside, footer and form interaction;
6. hamburger/submenu interaction and zero horizontal overflow.

## Status

**MOBILE SOURCE: PASS**

**EXACT CODE CI: PASS**

**PRODUCTION DEPLOY: CHƯA XÁC MINH**

**BROWSER QA 360/390/430: CHƯA XÁC MINH**
