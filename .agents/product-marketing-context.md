# Product Marketing Context — Đăng Dương Group

Last reviewed: 2026-08-28
Source of truth: `codex/rebuild-v2` repository content and verified project reports only.

## Overview
Đăng Dương Group website serves two connected journeys: (1) people exploring beauty products, brands, routines and educational content; (2) organizations evaluating Đăng Dương's cosmetic development/manufacturing and OEM/ODM capabilities. The website must make these journeys understandable without inventing certifications, capacity, partners, medical effects or other unverified claims.

## Audience & beauty situations
### Consumer / product explorer
- Needs to discover products by category/brand and understand where a product may fit in a routine.
- Needs readable educational guidance before navigating to products.
- Needs clear product imagery, category navigation and a dependable path to contact/distribution information.

### Brand / OEM-ODM prospect
- Needs to understand capabilities, R&D, manufacturing, quality-control approach and collaboration path.
- Needs a clear path from capability education to a contact/request action.
- Requires evidence-backed language; unknown certifications, capacity and partner claims remain unpublished until verified.

### Partner / distributor
- Needs a clear partnership path, relevant brand/product context and a direct request/contact route.

## Differentiation
Use only source-supported differentiation:
- One site connects corporate capability, brand ecosystem, product catalog/routine education and knowledge content.
- Dedicated capability journey includes OEM/ODM, R&D, factory/manufacturing and quality-control topics.
- Product discovery is backed by the existing WooCommerce production catalog; Product Truth controls publication safety and must not be overridden by marketing copy.
- Knowledge content is structured as educational support rather than unsupported medical/product claims.

## Brand voice
- Vietnamese, clear, confident, premium but not inflated.
- Customer-facing language; remove internal jargon and implementation terminology.
- Prefer concrete explanations and next actions over slogans.
- Never imply medical efficacy, certifications, production capacity, named partners or regulatory status without verified evidence.
- CTA language should describe the next step clearly: xem sản phẩm, khám phá routine, tìm hiểu năng lực, gửi yêu cầu, liên hệ.

## Verified proof points / constraints
Verified in repository reports as of 2026-08-28:
- Controlled product manifest: 44/44 matched in the latest Product/Data Recovery state referenced by Frontend Recovery.
- Latest controlled Featured Image check referenced by Frontend Recovery: 0 wrong Featured Images.
- Public legal HOLD exclusion: PASS at source + CI level.
- Knowledge article registry: 10/10 publish-ready and metadata synchronized.
- Approved top-level IA is implemented in theme source: Trang chủ → Về Đăng Dương → Năng lực → Thương hiệu → Sản phẩm & Routine → Kiến thức → Đối tác → Liên hệ.
- Production deployment/runtime/browser verification remains unverified in the latest post-deploy report; source/CI evidence must not be presented as production evidence.

## Goals
P0:
1. Make the approved IA and customer journeys coherent in source.
2. Keep every public claim evidence-backed.
3. Preserve WooCommerce catalog ownership and Product Truth publication safety.
4. Ensure each major journey has an intentional CTA and internal-link path.
5. Reach production readiness only after exact deployed SHA and production evidence are available.

P1:
- Reduce overlap/cannibalization by assigning one primary intent to each hub/subpage.
- Keep knowledge pages educational, product pages transactional/discovery-oriented, capability pages B2B-oriented and contact/partner pages conversion-oriented.

## URL ownership / intent contract
- `/` — orientation and routing to the four major journeys.
- `/ve-dang-duong/` — corporate identity/context; not capability-detail ownership.
- `/nang-luc/` — capability hub; owns B2B capability discovery.
- `/nghien-cuu-phat-trien/`, `/nha-may-san-xuat-my-pham/`, `/oem-odm-my-pham/`, `/kiem-soat-chat-luong/` — own their specific capability intents.
- `/thuong-hieu/` — brand ecosystem discovery; brand subpages own brand-specific context.
- `/san-pham/` — WooCommerce-backed product catalog/discovery; routine pages support selection rather than duplicate product-detail intent.
- `/kien-thuc/` — educational hub; articles answer educational questions and link contextually to relevant hubs/products without duplicating product detail copy.
- `/doi-tac/` — partnership hub; OEM/ODM detail remains owned by `/oem-odm-my-pham/` and partner pages should link there rather than duplicate it.
- `/lien-he/` — primary contact conversion hub; request/distribution/map subpages own their specific action intent.

## Evidence rule
When a fact is absent or production cannot be reached, label it unverified/blocked. Do not infer production state from GitHub HEAD, source PASS or CI PASS.