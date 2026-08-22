# Bizrise DDG Media Importer

Plugin repair media theo nguyên tắc deterministic cho Đăng Dương Group. Plugin ưu tiên reuse attachment đã có trong Media Library, chỉ gắn vào chỗ trống và không ghi đè featured image do người dùng gán thủ công.

## Nguyên tắc production

- Không fuzzy-map sản phẩm khác nhau.
- Product mapping dùng exact title/canonical identity + brand guard.
- Không ghi đè featured image thủ công đã hợp lệ.
- Attachment do importer quản lý được đánh dấu bằng meta để lần chạy sau reuse, tránh duplicate.
- ALT chỉ được điền khi attachment đang thiếu ALT và manifest có ALT đã duyệt.
- Báo cáo runtime có `missing_products`, `missing_assets` và `ambiguous_matches`; không được coi sản phẩm chưa resolve là đã có media.
- Product Truth là nguồn identity/gate. Media importer không được tự thay tên, brand, SKU hay Product Truth.

## Trạng thái asset bundle trên branch hiện tại

Branch `agent/brz-40-media` hiện không chứa `apps/bizrise-ddg-media-importer/assets/media/`. Vì vậy nhánh `import_asset()` chỉ hoạt động nếu bundle này được bổ sung ở một release sau; snapshot hiện tại chủ yếu reuse attachment first-party đã tồn tại trong Media Library qua `source_fragments`.

Không được mô tả asset mới là `web-ready` nếu chưa qua workflow dự án:

`Photoshop → Export for Web → web asset → CMS`.

## Cách chạy

Admin:

`Tools → DDG Media Repair → Repair / Import missing media`

WP-CLI dry-run:

```bash
wp bizrise ddg-media
```

WP-CLI apply:

```bash
wp bizrise ddg-media --apply
```

## Mapping manifest hiện tại

- Factory aerial → `nha-may-san-xuat-my-pham`, `nha-may`, `nang-luc-san-xuat`, `manufacturing`, `factory`.
- Factory front → `nang-luc`, `ve-dang-duong`, `gioi-thieu`.
- One Today brand banner → `one-today` / `onetoday`.
- Hatagold brand banner → `hatagold` / `hata-gold`.
- Một số product asset Hatagold B5 đang có candidate mapping theo legacy title; phải đối chiếu canonical Product Truth trước khi coi là deterministic mapping production.

## Sprint 0 audit

Xem:

- `docs/MEDIA_SPRINT0_AUDIT_2026-08-22.md`
- `apps/bizrise-ddg-media-importer/data/publish-allowed-media-audit-2026-08-22.psv`

## Safety

Importer chỉ điền chỗ trống. Nếu post/page đã có featured image hợp lệ, importer giữ nguyên. Không dùng fuzzy matching để suy đoán product identity.
