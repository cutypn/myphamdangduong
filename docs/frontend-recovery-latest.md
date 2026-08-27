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

A mobile typography regression remained outside the `/san-pham/` mockup. The shared Theme 2 product cards used on homepage and related-product sections still inherited the old `<=374px` rules. At the canonical 360px viewport, several labels were only about **8–11px** (`.50rem–.68rem`), even though the archive-specific Product Mockup 2.2.3 had already been corrected.

Fix applied:

- replaced stale `apps/bizrise-ddg-theme/assets/css/mobile-p0.css` with canonical 360/390/430 safeguards;
- shared product-card brand/media/title/pack/kicker/CTA typography is now readable in the two-column portrait layout;
- shared product CTA retains a **44px** minimum tap target;
- the 360px-specific rule no longer shrinks product titles back to the old tiny size;
- article-aside links now explicitly keep a **44px** minimum phone tap target;
- `header.php` now enqueues this theme-owned mobile safeguard stylesheet after the main Theme 2 cascade on every public page, with version `2026.08.28.1` for cache busting;
- no plugin UI override was created;
- no Product Truth, HOLD, publication, WooCommerce mapping or media assignment logic changed.

Files changed:

- `apps/bizrise-ddg-theme/assets/css/mobile-p0.css`
- `apps/bizrise-ddg-theme/header.php`

Exact frontend code SHA: `0202384a18ab7b865500e799d0da014f056ac546`.

CI for that exact code SHA:

- Validate Bizrise DDG V2 run `33117567275`: **SUCCESS**;
- Build Bizrise DDG V2 Release run `33117567165`: **SUCCESS**.

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
| Archive product typography readable | PASS source | PASS source | PASS source |
| Homepage/related product typography readable | PASS source | PASS source | PASS source |
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
- Desktop >=1180px source layout remains unchanged by this fix.

## Production / browser verification

Direct production retrieval was attempted again in this run, but `dangduonggroup.com` was not obtainable from the available execution web path, matching Product/Data Recovery's current DNS/access blocker. No screenshot/browser-production claim is made.

Required production PASS evidence remains:

1. deployed SHA equals current validated HEAD or descendant;
2. `/san-pham/` at 360, 390, 430 and desktop >=1180;
3. homepage product cards and related-product cards at 360px to verify the typography fix visually;
4. representative product category and product single;
5. article body/aside and footer interaction;
6. hamburger/submenu interaction and zero horizontal overflow.

## Status

**MOBILE SOURCE: PASS**

**EXACT CODE CI: PASS**

**PRODUCTION DEPLOY: CHƯA XÁC MINH**

**BROWSER QA 360/390/430: CHƯA XÁC MINH**
