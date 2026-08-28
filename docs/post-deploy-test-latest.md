# DDG Post-Deploy Production Test — 2026-08-28 09:45 ICT

## Verdict

**CHƯA XÁC MINH / PRODUCTION TEST BLOCKED**

Không đánh dấu PRODUCTION PASS. Môi trường tester hiện không truy cập được production runtime/frontend nên chưa thể xác nhận có `deployed_sha` mới để bắt đầu vòng post-deploy test đầy đủ.

## Deploy detection gate

- Branch theo dõi: `codex/rebuild-v2`
- Git HEAD quan sát được: `202f7e1ee3e7328cd5d84bd5d25f2b73c312e378`
- HEAD commit: `docs: refresh content publish after core language QA`
- Validate Bizrise DDG V2: **SUCCESS** cho đúng HEAD SHA.
- Build Bizrise DDG V2 Release: **SUCCESS** cho đúng HEAD SHA.
- `/wp-json/bizrise-deploy/v1/status`: **CHƯA XÁC MINH** — production domain không truy cập được từ môi trường tester trong vòng này.
- `deployed_sha`: **CHƯA XÁC MINH**.
- `remote_sha`: **CHƯA XÁC MINH** từ runtime endpoint.
- Điều kiện `deployed_sha == remote_sha`: **CHƯA XÁC MINH**.

Vì không đọc được `deployed_sha`, tester không suy diễn rằng Git HEAD đã được deploy và không chạy/ghi PASS cho các test production phụ thuộc deploy mới.

## Runtime status

- `/wp-json/bizrise-ddg/v1/runtime-status`: **CHƯA XÁC MINH** — không truy cập được từ môi trường tester.
- Catalog/runtime counters: **CHƯA XÁC MINH**.
- Media inventory runtime: **CHƯA XÁC MINH**.

## Frontend production

Các URL bắt buộc chưa thể kiểm tra trực tiếp trong vòng này vì production frontend không truy cập được từ môi trường tester:

- `/`
- `/ve-dang-duong/`
- `/nang-luc/`
- `/thuong-hieu/`
- `/san-pham/`
- `/kien-thuc/`
- `/doi-tac/`
- `/lien-he/`
- submenu mới theo mindmap: **CHƯA XÁC MINH**

Trạng thái HTTP, broken link, duplicate H1, 404 bất thường, nội dung nháp/jargon nội bộ: **CHƯA XÁC MINH**.

## WooCommerce catalog

Toàn bộ nhóm catalog production hiện **CHƯA XÁC MINH**:

- Tổng số sản phẩm visible.
- Ít nhất 8 sản phẩm đại diện nhiều brand.
- Category/archive/single product.
- Product card tỷ lệ 9:16.
- Featured Image chính xác.
- HOLD/draft không lộ ra public.
- Catalog không trống.

Không dùng CI/source để thay thế bằng chứng production runtime.

## Viewport QA

Do production frontend không truy cập được, các viewport sau chưa được render-test:

- Mobile 360px: **CHƯA XÁC MINH**
- Mobile 390px: **CHƯA XÁC MINH**
- Mobile 430px: **CHƯA XÁC MINH**
- Desktop >=1180px: **CHƯA XÁC MINH**

Các hạng mục header/hamburger/submenu, overflow ngang, hero, typography, CTA, 2-column product grid, article cards, footer, tap target: **CHƯA XÁC MINH**.

## Severity

- **P0:** Production deploy/runtime/frontend verification blocked; chưa có bằng chứng `deployed_sha`, catalog health hoặc core-page health.
- **P1:** Không đánh giá trong vòng này vì P0 gate chưa mở.
- **P2:** Không đánh giá trong vòng này vì P0 gate chưa mở.

## Evidence

- Git branch HEAD: `202f7e1ee3e7328cd5d84bd5d25f2b73c312e378`.
- GitHub Actions trên đúng HEAD: Validate **SUCCESS**, Release **SUCCESS**.
- Production domain/runtime endpoint: không truy cập được từ môi trường tester trong vòng 2026-08-28 09:45 ICT.

## Next post-deploy trigger

Vòng tiếp theo chỉ chạy full production suite khi đọc được `/wp-json/bizrise-deploy/v1/status` và xác nhận một `deployed_sha` thực tế chưa có trong report đã test trước đó. Sau đó bắt buộc xác nhận `deployed_sha == remote_sha`, CI Validate/Release success cho SHA tương ứng, rồi mới test runtime, core URLs, catalog, mobile/desktop, links/H1/404/content và media inventory.
