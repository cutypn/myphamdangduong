# DDG Frontend Recovery — latest

## Current result

Mobile-first recovery continues on `codex/rebuild-v2` without changing Product Truth, publication rules or WooCommerce mapping. Product/Data Recovery remains authoritative: public catalog = WooCommerce `post_type=product`; internal `bizrise_product` remains non-public/non-queryable and must not own `/san-pham/`. Latest Product Recovery reports controlled manifest **44**, controlled matched **44**, last verified controlled wrong Featured Image **0**; unmanaged/legacy missing-media rows remain separate for deterministic Product/Data classification.

## P0 frontend fix this run

Static mobile interaction audit found several text links whose clickable area was only the line box rather than a finger-sized control. This affected the single-product brand link, section-level “Xem tất cả” CTA, footer secondary CTA, footer company/contact links, footer navigation and footer legal links. These are important mobile navigation/action surfaces and were below the target ~44px hit height even though primary buttons, product-card CTA and menu links had already been hardened.

Fix applied in theme core CSS:

- add `min-height:44px` + inline-flex alignment for single-product brand CTA;
- add `min-height:44px` for split-section text CTA such as “Xem tất cả”;
- add `min-height:44px` for footer secondary CTA, company link, navigation links, legal links and contact links;
- footer navigation links use full available width on phone for easier tapping;
- footer legal row wraps safely with gaps to avoid narrow-screen collision;
- existing WordPress mobile admin-bar sticky-header offset remains intact;
- no Product Truth, SKU mapping, publication status or WooCommerce visibility rule changed.

Frontend code SHA:

- `60be5679ed1c4b401de8c28ad0b64c34c1cc2a0f` — `fix(theme): harden mobile touch targets across site`

File:

- `apps/bizrise-ddg-theme/style.css`

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
| Product-card CTA >=44px | PASS source | PASS source | PASS source |
| Single-product secondary/text CTA >=44px | PASS source | PASS source | PASS source |
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
| Footer nav/contact/legal targets >=44px | PASS source | PASS source | PASS source |
| Footer legal row wraps safely | PASS source | PASS source | PASS source |
| Footer compact | PASS source | PASS source | PASS source |

`PASS source` means static source/cascade/interaction inspection, not live screenshot verification.

## Product / tablet / desktop guards retained

- `/san-pham/` remains WooCommerce-backed; fallback page queries public WooCommerce `product`, not Product Truth.
- Product filter taxonomy row wraps at tablet/desktop widths and horizontal-scrolls on phone.
- Product grid: 2 columns on canonical phone widths, 3 columns at tablet, 4 columns on larger desktop.
- Product cards remain portrait 9:16 with `object-fit:contain`, avoiding image crop.
- Related-product query retains canonical WooCommerce `exclude-from-catalog` exclusion from latest Product/Data recovery.

## CI

Exact frontend code SHA: `60be5679ed1c4b401de8c28ad0b64c34c1cc2a0f`.

- Validate Bizrise DDG V2 run `33087077723`: **SUCCESS**.
- Build Bizrise DDG V2 Release run `33087077658`: **SUCCESS**.

## Production / browser verification

Live production/browser verification is still unavailable from this execution environment, so no screenshot-based production claim is made.

Required production PASS evidence remains:

1. deployed SHA equals `60be5679ed1c4b401de8c28ad0b64c34c1cc2a0f` or validated descendant;
2. `/san-pham/` at 360, 390, 430 and desktop >=1180;
3. representative `product_cat` archive;
4. >=8 product singles across brands;
5. homepage/core pages;
6. `/kien-thuc/` plus representative article pages;
7. sticky header/menu/submenu interaction including Escape and orientation/viewport transition;
8. zero horizontal overflow at 360/390/430;
9. all primary/secondary CTA and footer navigation/contact/legal targets render at ~44px minimum height;
10. visual polish of product 9:16 cards, spacing, hero height, typography, CTA visibility and footer.

## Status

**MOBILE SOURCE: PASS**

**TOUCH TARGET SOURCE: PASS**

**EXACT FRONTEND CI: PASS**

**PRODUCTION DEPLOY: CHƯA XÁC MINH**

**BROWSER QA 360/390/430: CHƯA XÁC MINH**
