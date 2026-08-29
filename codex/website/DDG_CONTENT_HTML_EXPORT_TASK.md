# DDG CONTENT + HTML EXPORT TASK

CASE: DDG-CONTENT-HTML-001

## Objective

Chuyển phần sinh content + HTML production của website Đăng Dương Group sang Codex. PHP/WordPress Publisher chỉ consume package `APPROVED`, không tự sáng tác nội dung.

## Owner

`CODEX-WEB-CONTENT-HTML`

## Upstream

`G4-CONTENT + SEO + G7-PROOF`

## Downstream

`DEV-CMS → TESTER → SEO-QA → PO → DEVOPS`

## Inputs

Đọc trước:
- `AGENTS.md`
- `codex/marketing-agents/ROUTER.yaml`
- `codex/website/CODEX-WEB-CONTENT-HTML.md`
- Product Truth / Product Master hiện hành.
- `docs/content/DDG_WEBSITE_CONTENT_MASTER_2026.md`
- `docs/content/PRODUCT_PAGE_COPY_PUBLISH_ALLOWED_2026.md`
- DDG Content Writing Standard 2026 v2.
- DDG SEO AI Content Standard 2026.
- UI/mockup/source hiện hành trước khi dựng HTML.

## Scope

### Product
Sinh package cho SKU đã PASS Product Truth. Không sinh package cho unknown/hold/recalled/retired hoặc claim chưa duyệt.

### Article
Sinh package cho article/page đã có content brief/copy và G7 PASS. Không dùng Knowledge Seeder để tự tạo copy mới ở runtime.

## Output path

```text
apps/bizrise-ddg-codex-content/exports/products/*.json
apps/bizrise-ddg-codex-content/exports/articles/*.json
```

## Acceptance Criteria

- schema_version 1.0
- status APPROVED
- G4 PASS
- SEO PASS
- G7 PASS
- HTML PASS
- không H1 trong body
- không TBD
- không script/style inline
- Product Truth exact match
- Phiếu công bố chỉ role LEGAL_DOCUMENT
- content HTML render được qua wp_kses_post
- không tự deploy production

## Required validation

- parse toàn bộ JSON
- unique slug trong từng type
- unique product master_key
- validate required fields
- scan prohibited HTML/TBD
- report package count PASS/FAIL

## Next owner

DEV-CMS sau khi package commit và PM review.
