# CASE DDG-CONTENT-HTML-001

TYPE: CONTENT / HTML / CODEX

BUSINESS_GOAL: đưa phần sinh content + HTML production qua Codex, WordPress chỉ import package APPROVED.

OWNER_AGENT: CODEX-WEB-CONTENT-HTML

SUPPORT: G4-CONTENT, SEO, G7-PROOF, DEV-CMS, TESTER

INPUT: Product Truth, Content Master, approved copy, SEO spec, G7 evidence, UI/UX contract.

OUTPUT: `apps/bizrise-ddg-codex-content/exports/products/*.json` và `apps/bizrise-ddg-codex-content/exports/articles/*.json`.

PASS_CRITERIA:
- schema 1.0
- G4/SEO/G7/HTML PASS
- Product Truth exact match
- no H1/TBD/script/style in content_html
- LEGAL_DOCUMENT never Featured Image
- JSON validation PASS

NEXT_AGENT: DEV-CMS

STATUS: READY
