# DDG Frontend Recovery — latest

## Current result

Mobile-first source fix completed on `codex/rebuild-v2` without changing Product Truth or publication rules.

Product/Data Recovery remains the reference for catalog data: `/san-pham/` must use WooCommerce `post_type=product`; the internal `bizrise_product` type must not own the public catalog route.

## Mobile issue fixed

The base theme had a `<=520px` rule that collapsed product grids to one column and changed each product card into a horizontal 42/58 layout. That conflicted with the approved portrait product-card mockup.

The final override now keeps product cards portrait and compact on phones.

## Code

- `771a47cd493d3579aac3053da775620598258c0f` — mobile CSS hardening.
- `8842c34e22ec70b71b6a4cf19e7fb2951474ea7b` — asset cache version bump to Theme 2.1.5.

Files:

- `apps/bizrise-ddg-theme/assets/css/theme212.css`
- `apps/bizrise-ddg-theme/functions.php`

## Mobile checklist

| Check | 360 | 390 | 430 |
|---|---|---|---|
| Header/logo/hamburger fit by final CSS | PASS source | PASS source | PASS source |
| Menu links have >=44px target | PASS source | PASS source | PASS source |
| Product grid stays 2 columns | PASS source | PASS source | PASS source |
| Product media stays 9:16 | PASS source | PASS source | PASS source |
| Product images use contain, not crop | PASS source | PASS source | PASS source |
| Filter pills can horizontal-scroll | PASS source | PASS source | PASS source |
| Sort select is phone friendly | PASS source | PASS source | PASS source |
| Single product image is contained | PASS source | PASS source | PASS source |
| H1/hero scale is moderated | PASS source | PASS source | PASS source |
| Article body/type scale is readable | PASS source | PASS source | PASS source |
| Tables scroll instead of breaking viewport | PASS source | PASS source | PASS source |
| Form controls avoid phone auto-zoom | PASS source | PASS source | PASS source |

`PASS source` is a source/cascade result, not screenshot/browser evidence.

## Detail of fixes

- Phone shell reduced to 12px side gutters.
- Header is 64px on phone; logo is limited to 156px and 142px below 375px.
- Hamburger stays 44x44px.
- Mobile menu uses dynamic viewport height and text can wrap.
- Product grid stays two portrait columns down to 360px.
- Product card spacing, title, brand and metadata are compacted for narrow widths.
- Product image stage remains `object-fit: contain` and centered.
- Page/article/product H1 scale is reduced on phone.
- Single product image uses `min(88vw, 360px)` and remains portrait.
- Article/editorial line-height and heading sizes are reduced to readable mobile values.
- Tables become horizontally scrollable.
- Inputs/selects/textareas use 16px font size on phone.
- Footer spacing and logo size are reduced on phone.

## CI

Exact mobile code SHA: `8842c34e22ec70b71b6a4cf19e7fb2951474ea7b`.

- Validate Bizrise DDG V2 run `33045434598`: SUCCESS.
- Build Bizrise DDG V2 Release run `33045434587`: SUCCESS.

## Production verification

Production is not marked PASS in this report because the current environment still cannot fetch the public deploy/runtime endpoints reliably.

Browser verification is still required for:

- `/san-pham/` at 360, 390, 430 and desktop >=1180;
- category archive;
- representative product single pages;
- homepage/core pages;
- knowledge archive and article pages;
- sticky header/menu behavior;
- horizontal overflow and actual visual polish.

## Status

**MOBILE SOURCE FIX: PASS**

**EXACT CI: PASS**

**PRODUCTION / BROWSER QA: CHƯA XÁC MINH**
