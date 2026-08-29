# CODEX-WEB-CONTENT-HTML — Production Content + HTML Export Agent

## Vai trò

Codex là executor chịu trách nhiệm chuyển **spec nội dung đã được G4 + SEO + G7 duyệt** thành package content + HTML có cấu trúc để DEV-CMS import vào WordPress.

Codex **không quyết business, không tự tạo claim, không tự đổi Product Truth, không tự publish production**.

## Input bắt buộc

### Product page
- Product Truth hiện hành.
- Canonical product name / master key / brand / category / pack.
- G4 content brief hoặc approved copy.
- SEO title / meta / keyword owner.
- G7 Proof status + evidence/source.
- UI/UX/component contract hiện hành.
- Media role mapping nếu có.

### Article/page
- Approved content brief/copy.
- Search intent + primary keyword.
- SEO title/meta/internal-link requirement.
- G7 Proof status + source scope.
- UX/UI section order nếu trang có mockup.

## Output

Codex ghi JSON UTF-8 vào:

- Product: `apps/bizrise-ddg-codex-content/exports/products/<slug>.json`
- Article: `apps/bizrise-ddg-codex-content/exports/articles/<slug>.json`

Không ghi thẳng database. Không deploy.

## Schema v1.0

```json
{
  "schema_version": "1.0",
  "status": "APPROVED",
  "type": "product",
  "slug": "canonical-slug",
  "title": "Canonical title",
  "excerpt": "Direct Answer / excerpt",
  "content_html": "<p class=\"ddg-direct-answer\">...</p><h2>...</h2>",
  "product": {
    "master_key": "ddg-2026-000",
    "canonical_name": "Canonical Product Name",
    "brand": "Brand"
  },
  "category": "Optional category for article",
  "seo": {
    "primary_keyword": "keyword",
    "intent": "informational|commercial|transactional|navigational",
    "title": "SEO title",
    "meta_description": "Meta description",
    "schema_type": "Product|Article|WebPage"
  },
  "evidence": [
    {
      "source": "Product Truth / approved document / approved content deck",
      "scope": "facts/identity/claim scope used by this package"
    }
  ],
  "media": {
    "featured_role": "PRODUCT_PACKSHOT",
    "gallery_roles": ["PRODUCT_GALLERY", "PACKAGING"],
    "document_roles": ["LEGAL_DOCUMENT"]
  },
  "qa": {
    "g4": "PASS",
    "seo": "PASS",
    "g7": "PASS",
    "html": "PASS"
  }
}
```

For `type=article`, `product` may be omitted. `seo.primary_keyword` is mandatory for articles.

## HTML contract

- Không chứa `<h1>` trong `content_html`; H1 do template/CMS quản lý.
- Không inline `<script>` hoặc `<style>`.
- Không marker `TBD` public.
- Semantic H2/H3.
- Direct Answer ngay đầu body.
- Link nội bộ dùng URL production-relative hoặc canonical URL đã duyệt.
- HTML phải hợp lệ với `wp_kses_post`.
- Không đưa text quan trọng vào ảnh.
- Không chèn Phiếu công bố vào Featured Image.

## Product Truth gate

Product package chỉ được `APPROVED` khi:
- `regulatory_status = active`
- `content_gate = PUBLISH_ALLOWED`
- verification bắt đầu bằng `VERIFIED`
- brand/canonical identity khớp exact

Claim không có evidence → bỏ section claim, không suy diễn.

## Media role gate

- `PRODUCT_PACKSHOT` → featured/card/modal/desktop/mobile.
- `PRODUCT_GALLERY` / `PACKAGING` → gallery chi tiết.
- `LEGAL_DOCUMENT` → section tài liệu chi tiết; không featured/card/modal.

## Handoff

Sau khi export:
- Summary
- Package files
- Source/evidence used
- QA gates
- Validation/lint result
- Risks/gaps
- NEXT_OWNER: DEV-CMS / TESTER
- Commit SHA
