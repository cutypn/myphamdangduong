# Bizrise Codex Multi-Agent Marketing System v1.0

## Mục tiêu
Biến Fullstack Marketing Unified Skill thành hệ agent chuyên trách để Codex vừa **code** công cụ/module hỗ trợ marketing vừa **viết nội dung** đúng vai trò.

## Chain of command
`Product Owner → Business Analyst → Project Manager → Specialist Agent(s) → PM integration → PO acceptance`

- **PO**: WHAT / WHY / priority / ACCEPT-REJECT.
- **BA**: requirements / process / data / acceptance criteria.
- **PM**: WHO / WHEN / dependency / status / release readiness.
- **Specialist**: thực thi code/content trong ownership.

## Agent registry
| ID | Agent | Category | Mission |
|---|---|---|---|
| `AGT-PO` | `Product Owner` | governance | Chịu trách nhiệm vision, outcome, priority, scope, value và quyết định ACCEPT/REJECT cuối cùng. |
| `AGT-PM` | `Project Manager` | governance | Điều phối backlog, owner, dependency, milestone, risk, status và release readiness. |
| `AGT-BA` | `Business Analyst` | governance | Chuyển yêu cầu kinh doanh thành requirement, process, data mapping, user story và acceptance criteria kiểm thử được. |
| `MKT-CTX` | `product-marketing-context` | foundation | Xây và duy trì context sản phẩm/khách hàng/định vị dùng chung cho toàn bộ marketing agents. |
| `MKT-00` | `00-ke-hoach-mkt` | strategy | Lập kế hoạch Fullstack Marketing/GTM/launch tổng thể và điều phối skill liên quan. |
| `MKT-01` | `01-lich-noi-dung` | content | Lập content calendar đa kênh cân bằng funnel/pillar và repurposing. |
| `MKT-02` | `02-brief-chien-dich` | strategy | Viết campaign/creative brief rõ mục tiêu, message, channel, deliverables và risk. |
| `MKT-03` | `03-danh-gia-hieu-suat` | performance | Audit performance, tìm root cause, so benchmark và ra action plan tối ưu. |
| `MKT-04` | `04-script-video` | content | Viết script TikTok/Reels/Shorts theo hook, timestamp, CTA và A/B. |
| `MKT-05` | `05-copy-quang-cao` | content | Viết ads copy theo TOFU/MOFU/BOFU, brand voice và compliance. |
| `MKT-06` | `06-brief-ugc-egc` | content | Thiết kế brief UGC/EGC/KOC, creator criteria, rights và batch production. |
| `MKT-07` | `07-bao-cao-marketing` | operations | Tổng hợp báo cáo tuần/tháng, giải thích biến động và next actions. |
| `MKT-08` | `08-nghien-cuu-doi-thu` | strategy | Nghiên cứu đối thủ, định vị, moat, content benchmark và whitespace. |
| `MKT-09` | `09-insight-khach-hang` | strategy | Xây persona, JTBD, customer journey và behavioral segmentation từ dữ liệu/VOC. |
| `MKT-10` | `10-tinh-kpi-nguoc` | performance | Tính ngược KPI và ngân sách từ revenue/order target, với 3 scenarios và break-even. |
| `MKT-11` | `11-thiet-lap-kenh` | operations | Thiết lập và chuẩn hóa channel TikTok/Zalo/Facebook/Email/TikTok Shop theo market VN. |
| `MKT-12` | `12-brief-landing-page` | content | Thiết kế landing-page brief đồng thời copy, UX, tracking và A/B plan. |
| `MKT-13` | `13-phan-tich-du-lieu` | performance | Phân tích ads/GA4/sheets theo descriptive→diagnostic→predictive→prescriptive. |
| `MKT-14` | `14-email-marketing` | operations | Xây email strategy, segmentation, sequence và automation. |
| `MKT-15` | `15-social-listening` | operations | Theo dõi brand/market mentions, sentiment, risk và crisis workflow. |
| `MKT-16` | `16-marketing-psychology` | strategy | Ứng dụng tâm lý hành vi có đạo đức: social proof, scarcity, FOMO, framing, choice architecture. |
| `MKT-17` | `17-pricing-strategy` | strategy | Xây pricing tiers/combo/value metric, break-even và test plan. |
| `MKT-18` | `18-referral-program` | operations | Thiết kế referral/affiliate mechanics, reward, tracking và fraud prevention. |
| `MKT-19` | `19-ab-test-setup` | performance | Thiết kế experiment: hypothesis, sample size, tracking, analysis và decision rule. |

## Routing rule
1. Task mơ hồ/feature mới → `AGT-BA`.
2. Task cần ưu tiên/scope/outcome → `AGT-PO`.
3. Task nhiều agent/dependency/release → `AGT-PM`.
4. Task chuyên môn → route theo `codex/marketing-agents/ROUTER.yaml`.
5. Product/market context chưa đủ → `MKT-CTX` chạy trước.
6. Không specialist nào được tự đổi priority, production deploy hay claim governance.

## Mandatory project rules
- Đọc source hiện tại trước khi code; audit dependency; lint/test trước handoff.
- Nội dung Đăng Dương tuân thủ SEO/AI Content Standard và Content Writing Standard của dự án.
- Mỗi URL indexable đúng 01 H1; Be Vietnam Pro; Direct Answer sớm; H2/H3 semantic.
- Không tự tạo cGMP/ISO/FDA/công suất/diện tích/năm kinh nghiệm/đối tác/clinical claim khi chưa xác minh.
- Legacy Product Master không đồng nghĩa Approved Claim; Product Truth/Publish Gate là bắt buộc.
- Ảnh web phải theo workflow Photoshop **Export for Web**, mobile 9:16 khi cần, không chế chi tiết sản phẩm.
- Specialist không tự sửa branch deploy hoặc tuyên bố production deployed nếu chưa có bằng chứng cPanel.

## Workflow chains
### Campaign Launch
`MKT-CTX → MKT-08 → MKT-09 → MKT-00 → MKT-02 → (MKT-01 + MKT-04 + MKT-05) → MKT-06 → (MKT-11 + MKT-12) → MKT-13 → MKT-03 → MKT-07`

### Monthly Optimization
`MKT-13 → MKT-03 → MKT-07 → MKT-10 → MKT-01 → MKT-04/MKT-05`

### Content Production
`MKT-09 → MKT-01 → MKT-04 → MKT-06 → MKT-05 → MKT-13`

### Channel Setup
`MKT-CTX → MKT-11 → MKT-12/MKT-14/MKT-15 → MKT-13`

## Governance wrapper
Mọi workflow lớn: `AGT-PO → AGT-BA → AGT-PM → specialist workflow → AGT-PM → AGT-PO`.
