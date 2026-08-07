# Đăng Dương Group × Bizrise Framework

Repository phát triển website **Đăng Dương Group** trên nền **Bizrise Framework**.

## Source of Truth

### Visual
- Theme 1 là visual baseline: cherry red, ivory/soft pink, logo Đăng Dương, hero premium, responsive desktop/mobile.
- Không thay Theme 1 bằng một visual system khác nếu chưa được duyệt.

### Content / SEO
- Font toàn site: **Be Vietnam Pro**.
- Mỗi URL indexable có đúng **01 H1**.
- H1 chứa primary keyword tự nhiên.
- H2/H3 semantic.
- SEO + AI Search/AEO/GEO.
- Mobile hero/story dùng asset riêng **9:16**.
- Desktop dùng **16:9 / 1:1 / 3:4** theo use case.
- Không publish claim, chứng nhận, công suất, số năm kinh nghiệm hoặc dữ liệu doanh nghiệp chưa xác minh.

### Business model
Website hỗ trợ đồng thời:
- Corporate authority.
- Brand ecosystem.
- Product catalogue / Product Engine.
- Distribution / đại lý.
- Affiliate.
- OEM/ODM lead generation.
- Beauty Knowledge.

Luồng content/product ưu tiên:

`Problem → Routine → Product → Combo → Where to Buy`

## Architecture

```text
myphamdangduong/
├── apps/
│   ├── bizrise-theme/
│   ├── bizrise-core/
│   └── bizrise-installer/
├── docs/
│   ├── PROJECT_SOURCE_OF_TRUTH.md
│   ├── SEO_CONTENT_STANDARD.md
│   ├── CONTENT_WRITING_STANDARD.md
│   └── MENU_IA.md
├── demo/
└── tools/
```

## WordPress target
- WordPress Multisite
- PHP 8.2+
- LiteSpeed + cPanel
- Gutenberg-first
- WooCommerce chỉ bật ở site thực sự cần checkout; Product Engine mặc định dùng Bizrise CPT.

## Development rule
Mọi thay đổi theme/code đi qua branch + PR. Branch tích hợp đầu tiên: `agent/theme1-bizrise-foundation`.
