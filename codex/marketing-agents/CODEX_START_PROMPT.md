# CODEX START PROMPT — BIZRISE MULTI-AGENT MARKETING

Bạn đang làm trong dự án Bizrise Framework / Đăng Dương Group.

1. Đọc `AGENTS.md` và `codex/marketing-agents/ROUTER.yaml` trước.
2. Với yêu cầu mới, không lao vào code ngay:
   - requirement chưa rõ → `AGT-BA`;
   - scope/value/priority → `AGT-PO`;
   - multi-agent/dependency/release → `AGT-PM`;
   - sau đó route specialist agent.
3. Khi task liên quan sản phẩm/khách hàng/định vị, kiểm tra `.agents/product-marketing-context.md`; thiếu thì chạy `MKT-CTX` trước.
4. Mỗi specialist có thể code + viết content trong ownership, nhưng không được tự sửa production/deploy hoặc scope ngoài contract.
5. Nội dung DDG phải tuân thủ `DANGDUONGGROUP_SEO_AI_CONTENT_STANDARD_2026.md` và `DANGDUONGGROUP_CONTENT_WRITING_STANDARD_2026_v2.md` nếu các file chuẩn hiện diện trong workspace/project package.
6. Ảnh web tuân thủ Photoshop Export for Web; không chế chi tiết sản phẩm.
7. Nếu code GitHub: đọc branch/file hiện tại trước, audit dependency, test/lint, commit rõ message, báo SHA; không nói đã deploy nếu chưa có cPanel evidence.
8. Mỗi task phải ghi rõ Objective, Inputs, Scope, Acceptance Criteria, Owner, Files/URLs, KPI, Risks và Next Owner.
9. Mục tiêu là nhiều agent hoạt động như một team, không tạo 24 luồng tự do cùng sửa một source.

## Handoff format
- Summary
- Files/URLs changed
- Assumptions/TBD/Sources
- Tests/QA
- Metrics affected
- Risks/known gaps
- NEXT_OWNER
- Commit SHA nếu có code
