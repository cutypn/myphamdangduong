# DDG Frontend Recovery — latest

## Current result

Mobile-first recovery continues on `codex/rebuild-v2` without changing Product Truth, publication rules, WooCommerce mapping or media assignments. Product/Data Recovery remains authoritative: public catalog = WooCommerce `post_type=product`; internal `bizrise_product` remains non-public/non-queryable and must not own `/san-pham/`.

Latest Product/Data Recovery state read before this frontend change:

- controlled manifest: **44**;
- controlled matched: **44**;
- last verified controlled wrong Featured Image: **0**;
- public legal HOLD exclusion: **PASS source + CI**;
- approved mindmap structure: **PASS source + CI**;
- production on current HEAD: **CHƯA XÁC MINH**;
- product cleanup remains blocked-safe with **0** product state changes while live inventory is unavailable.

## P0 frontend audit / fix this run

The approved mindmap expanded the header to eight top-level navigation items plus logo and CTA. Theme 2 still switched to hamburger only at `<=980px`, leaving the 981–1180px range vulnerable to a crowded or overflowing desktop navigation row.

Fix applied in the existing theme stylesheet layer (`mobile-p0.css`), not a plugin override:

- touch navigation now activates at `<=1180px`;
- hamburger remains **44×44px**;
- expanded navigation uses a fixed scrollable panel with `overflow-x:hidden` and `overscroll-behavior:contain`;
- top-level and submenu links keep minimum **44px** touch height and allow wrapping;
- admin-bar offsets are retained per breakpoint;
- canonical 360/390/430 header geometry remains 64px on `<=520px` and 68px through the wider phone breakpoint;
- the mobile product typography/card rules from the prior pass are retained unchanged;
- desktop navigation remains the full horizontal row only above 1180px;
- cache version for `mobile-p0.css` bumped to `2026.08.28.4`.

Files changed:

- `apps/bizrise-ddg-theme/assets/css/mobile-p0.css`
- `apps/bizrise-ddg-theme/header.php`

Exact frontend code HEAD: `475253b49e2566cb88391d8a8f416a6f3fbe7149`.

CI for exact HEAD:

- Validate Bizrise DDG V2 run `33135472264`: **SUCCESS**;
- Build Bizrise DDG V2 Release run `33135472214`: **SUCCESS**.

No JS file changed in this run. Existing menu/submenu JS behavior is unchanged.

## Mobile checklist

| Check | 360 | 390 | 430 |
|---|---|---|---|
| Sticky header source geometry | PASS source | PASS source | PASS source |
| Logo/hamburger fit | PASS source | PASS source | PASS source |
| Hamburger/submenu targets >=44px | PASS source | PASS source | PASS source |
| Navigation panel vertical scroll / no x-overflow | PASS source | PASS source | PASS source |
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

## Wider viewport guard

The expanded mindmap now uses the touch navigation through 1180px, preventing the new eight-item sitemap from competing for a single crowded row in the 981–1180 range. Desktop >=1181px keeps the canonical full navigation row.

## Product / catalog guards retained

- `/san-pham/` remains WooCommerce-backed; fallback page queries public WooCommerce `product`, not Product Truth.
- Product grid remains 2 columns on canonical phone widths, 3 columns at tablet and 4 columns on larger desktop.
- Product cards remain portrait 9:16 with `object-fit:contain` in the final cascade.
- No Product Truth/HOLD/publication/media mapping rule changed.

## Production / browser verification

Product/Data Recovery attempted production access at **2026-08-28 09:10 ICT** and DNS did not resolve. This frontend run also found no searchable production result for `dangduonggroup.com`, so no fresh deployed SHA, screenshot or browser evidence is available.

Required production PASS evidence remains:

1. deployed SHA equals current validated HEAD or descendant;
2. `/san-pham/` at 360, 390, 430 and desktop >=1180;
3. homepage/related product cards at 360/390/430;
4. representative category and product single;
5. article body/aside, footer and form interaction;
6. hamburger/submenu interaction at 360/390/430 and 981–1180, with zero horizontal overflow.

## Status

**MOBILE SOURCE: PASS**

**EXACT HEAD CI: PASS**

**PRODUCTION DEPLOY: CHƯA XÁC MINH**

**BROWSER QA 360/390/430: CHƯA XÁC MINH**
