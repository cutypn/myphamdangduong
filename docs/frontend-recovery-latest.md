# DDG Frontend Recovery — latest

## Current result

Theme/Frontend/CRO source on `codex/rebuild-v2` remains mobile-first and does not change Product Truth, HOLD/publication rules or WooCommerce product mapping. Public catalog remains WooCommerce-owned per the last Product/Data Recovery evidence; last verified controlled media state remains 44/44 matched and 0 wrong Featured Image.

`docs/post-deploy-test-latest.md` is still authoritative for production: runtime/frontend cannot currently be reached by the tester, so production deployed SHA, live screenshots, CWV and browser QA remain unverified.

## P0/P1 change this run

CRO tracking foundation was missing from the active theme JS. Added a vendor-neutral bridge directly to `assets/js/theme2.js`:

- `scroll` at 50% and 90%;
- `form_start` once per form interaction;
- `form_submit` on submit;
- `ClickCTA` for `.t2-btn`, `.t2-text-link`, `[data-ddg-cta]`;
- `ClickPhone` for `tel:` links;
- `ClickZalo` for Zalo links;
- campaign persistence for `utm_source`, `utm_medium`, `utm_campaign`, `utm_term`, `utm_content`, `gclid`, `fbclid` via session storage;
- stored campaign values are injected as hidden fields into forms so attribution survives internal navigation;
- every event is emitted as `ddg:tracking`; if a real `gtag` exists it is used, otherwise an existing `dataLayer` is used;
- no GA4/GTM vendor ID or fake key was added;
- `page_view` is intentionally left to the verified GA4/GTM integration to avoid duplicate automatic page views.

An intermediate unused tracking draft file was removed; active implementation lives only in the already-enqueued `theme2.js`.

Exact code HEAD after cleanup: `dd67a9b8cd1ab4e283c9d3f50b4539fd87c79541`.

Files changed in active source:

- `apps/bizrise-ddg-theme/assets/js/theme2.js`

## Mobile checklist

| Check | 360 | 390 | 430 |
|---|---|---|---|
| Sticky header source geometry | PASS source | PASS source | PASS source |
| Logo/hamburger fit | PASS source | PASS source | PASS source |
| Hamburger/submenu targets >=44px | PASS source | PASS source | PASS source |
| Navigation panel no x-overflow | PASS source | PASS source | PASS source |
| Product search >=48px | PASS source | PASS source | PASS source |
| Product filter/CTA targets >=44px | PASS source | PASS source | PASS source |
| Product grid 2 columns | PASS source | PASS source | PASS source |
| Product media portrait 9:16 / contain | PASS source | PASS source | PASS source |
| Single product image contained | PASS source | PASS source | PASS source |
| Article/body/table/form/footer responsive | PASS source | PASS source | PASS source |
| Homepage LCP hero eager/high priority | PASS source | PASS source | PASS source |
| Homepage image width/height/srcset | PASS source | PASS source | PASS source |

`PASS source` is static source/cascade/interaction evidence only, not production screenshot verification.

## URLs/templates in P0 scope

- `/` → `front-page.php`
- `/san-pham/`, product category/tag archives → WooCommerce archive template / WooCommerce `product`
- representative single product → WooCommerce single template
- `/thuong-hieu/`, corporate pages, `/lien-he/` → page/editorial templates as applicable
- `/kien-thuc/` + article single/archive → editorial/article templates

## Tracking state

| Signal | Source state |
|---|---|
| GA4/GTM vendor ID | MISSING / not verified; no fake ID added |
| `page_view` | External verified GA4/GTM integration required |
| `scroll` | PRESENT in theme bridge |
| `form_start` | PRESENT in theme bridge |
| `form_submit` | PRESENT in theme bridge |
| `ClickCTA` | PRESENT in theme bridge |
| `ClickPhone` | PRESENT in theme bridge |
| `ClickZalo` | PRESENT in theme bridge |
| UTM preservation | PRESENT for session + form hidden fields |

## Performance evidence

Source guards remain in place for responsive images, attachment width/height/srcset and eager/high-priority homepage LCP media. Measured FCP, LCP, CLS and PageSpeed mobile remain **CHƯA XÁC MINH** because production cannot currently be rendered from the available tester environment.

Target remains: FCP <1.5s, LCP <3s, CLS <0.1, PageSpeed mobile >80 when measurement becomes available.

## Tests / CI

For code HEAD `dd67a9b8cd1ab4e283c9d3f50b4539fd87c79541`:

- Validate Bizrise DDG V2: **SUCCESS**;
- Build Bizrise DDG V2 Release: **SUCCESS**;
- PHP syntax / deployment shell / Product Truth / controlled media manifest checks in Validate: **SUCCESS**;
- JS source smoke via local `node --check`: **BLOCKED in this runner** because raw GitHub hostname could not resolve; no claim of a local JS smoke PASS is made.

## Production state

**PRODUCTION PASS: CHƯA XÁC MINH**.

Still required before PASS:

1. verify production `deployed_sha` matches a validated/released current SHA;
2. render `/`, `/san-pham/`, category, product single, brand, article, corporate/contact pages;
3. browser QA at 360/390/430 before desktop >=1180;
4. verify forms/phone/Zalo/contact/store-locator paths actually work with production content/plugins;
5. verify tracking events against the real GA4/GTM container if/when present;
6. measure CWV/PageSpeed when production is reachable.
