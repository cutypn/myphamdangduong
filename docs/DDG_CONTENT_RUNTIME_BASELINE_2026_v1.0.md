# DDG CONTENT RUNTIME BASELINE 2026 v1.0

Status: MANDATORY / PO-APPROVED
Owner: BRZ-PO
Workflow: PO -> BA -> Digital Marketing Director -> Content + Media -> QA -> PO APPROVE

## 1. Website roles

### Main domain: dangduonggroup.com
The main domain is a B2B proposal/corporate website, not a consumer-first shop.

Primary jobs:
1. Present the company profile.
2. Explain verified capability.
3. Present OEM/ODM / partner proposition.
4. Introduce the brand ecosystem.
5. Surface Product Truth gated products as proof of the ecosystem.
6. Route visitors to brand subdomains, products, knowledge and shared lead CTA.

### Brand subdomains
Each brand is one premium landing/lookbook site on WordPress Multisite:
- one-today.dangduonggroup.com
- she-one.dangduonggroup.com
- x2.dangduonggroup.com
- hatagold.dangduonggroup.com
- ever-today.dangduonggroup.com
- one-today-gold.dangduonggroup.com

Each brand landing must:
1. Tell the brand story.
2. Present a premium visual territory / lookbook.
3. Connect the brand with the development/manufacturing ecosystem of Dang Duong Group.
4. State the Dang Duong Group endorsement only in the scope approved by PO.
5. Show that displayed products are backed by Product Truth / corresponding publication evidence.
6. Pull only products belonging to that brand from the main network product source.
7. Use the shared network CTA and form.

Do not duplicate the product database per subsite.

## 2. Product source of truth

Frontend product object: WooCommerce `product` only.

Legacy `bizrise_product` / `ddg_product` must not be exposed as parallel frontend product systems.

Publish gate:
- regulatory status = active
- content gate = PUBLISH_ALLOWED
- verification exists
- correct product media exists
- product belongs to the correct brand

Identity is not a marketing claim.
Product-name wording must not be automatically expanded into unsupported benefit claims.

## 3. Product discovery / filtering

Mandatory filter priority:

1. THUONG HIEU
2. CONG DUNG

`Cong dung` is a controlled taxonomy/keyword layer. Public labels must contain no more than 4 Vietnamese words.

Initial safe controlled labels may be derived from verified product group/category, for example:
- Lam sach
- Duong da
- Chong nang
- Cham soc body
- Tay te bao chet

Claim-like benefit labels such as treatment / melasma / acne / whitening must only be added when the related claim source is approved.

Desktop baseline: 4-column product grid.
Mobile baseline: 2-column product grid.

## 4. Corporate pages

### Homepage
Homepage is a B2B proposal.

Required information flow:
1. B2B value proposition hero
2. Who DDG helps
3. Capability
4. OEM/ODM process
5. Verified proof / documents
6. Brand ecosystem
7. Product portfolio as proof
8. Partner / distribution proposition
9. Knowledge / journal
10. Shared network CTA/form

Homepage is not product-first and is not a consumer campaign landing page.

### About / Gioi thieu
The About page is a company profile.

Required blocks:
- Company overview
- Business scope
- Brand ecosystem
- Verified capability
- Factory / manufacturing relationship where verified
- Corporate milestones only when verified
- Legal / company profile fields only when verified
- Partner CTA

Do not fill unverified years, certifications, capacity, factory size, market count or formula count.

### Capability / OEM-ODM
B2B pages must use:
Need -> Scope -> Process -> Verified capability -> Documents/proof -> Shared CTA.

## 5. Brand landing content contract

Each brand subdomain uses the same semantic framework but its own visual system:

1. Brand hero (background media + HTML overlay)
2. Brand Story
3. Brand Promise / Beauty Territory
4. Premium Lookbook
5. Backed by Dang Duong Group ecosystem
6. Product publication / evidence statement
7. Products from network filtered to exact brand
8. Routine / usage context when verified
9. Knowledge / stories
10. Shared CTA/form

Brand landing products are queried from the main site and must satisfy the product publish gate.

## 6. Banner contract

All DDG banners are overlay banners.

Correct structure:
- full-bleed background media
- optional readability scrim
- HTML overlay content

Corporate/introduction/archive banner rule for the current phase:
- banner content contains the page title only (H1)
- Direct Answer and longer copy start immediately below the banner

Do not use `text column + image column` for banners.
Do not put a normal image block below banner text.

Desktop art direction: 16:9/full-bleed.
Mobile art direction: 9:16.

## 7. Product detail

Product detail must follow the approved product mockup:
- breadcrumb
- desktop gallery + vertical thumbnails
- mobile 9:16 media composition
- brand
- exactly one H1
- Direct Answer
- verified product facts
- CTA
- product information sections
- routine role
- approved claims/ingredients/how-to only when verified
- FAQ when supported
- related products
- publication/legal documents
- shared CTA/footer

The page may omit an unsupported section; it must never invent the missing content.

## 8. Product publication documents

Publication evidence is tied to each SKU/Product Truth row.

Brand-level wording must not imply that every historical SKU has complete documentation unless the displayed product set actually satisfies the evidence gate.

Preferred brand statement:
`Cac san pham hien thi tren landing duoc lay tu Product Truth va doi chieu ho so cong bo tuong ung.`

## 9. Shared CTA / network form

All main-domain and brand-subdomain CTA blocks use one network configuration for:
- CTA title
- CTA description
- phone
- email
- contact destination
- form fields
- privacy consent

The shared form must submit to one network lead intake on the main site. Brand source/subdomain must be attached to each lead.

Baseline fields:
- Ho va ten
- So dien thoai
- Email
- Nhu cau
- Brand/source
- Consent

## 10. Publishing order

Do not link the homepage to unfinished destinations.

Publish order:
1. Product Truth gated WooCommerce products
2. Product archive + product detail
3. About / company profile
4. Capability / OEM-ODM
5. Brand network landing pages
6. Shared network CTA/form
7. QA all destination URLs
8. Rebuild homepage B2B proposal with links only to QA-PASS destinations

## 11. QA gates

Content QA:
- correct entity
- correct brand
- no unsupported claim
- no unsupported certification/statistics
- brand story consistent with brand territory

Media QA:
- correct SKU
- correct brand
- product desktop 1:1
- product mobile 9:16
- brand hero desktop/mobile art direction

HTML/CSS QA:
- 1 H1
- banner overlay contract
- desktop 1440 fidelity
- mobile 390 fidelity
- no horizontal overflow
- CTA/form works

Network QA:
- subdomain resolves to the intended site
- expected brand theme is active
- landing only shows products of that brand
- product links resolve
- shared CTA carries the correct brand source

PO is authorized to approve all internal gates.
