# NÉT Beauty AI — DDG Theme Studio v1.0.0

Nâng cấp bộ chỉnh sửa NÉT theo theme mới và brand context của Đăng Dương Group.

## Chức năng
- Auto-detect brand theo WordPress Multisite / `bizrise_brand_key`.
- Brand profiles: Đăng Dương Group, One Today, She One, Cream X2, Hatagold, Ever Today, One Today Gold.
- Content types: Knowledge/SEO, Brand Story, Product/Routine, Landing section, OEM/ODM, Company Profile, News/PR.
- Theme-aware HTML body fragment.
- Preview với Be Vietnam Pro.
- Copy HTML / export `.html`.
- Lưu HTML thành WordPress draft.
- Copy AI prompt đã nhúng brand voice + source-of-truth + claim governance.
- Public REST contract: `/wp-json/net-beauty-ai/v1/brand-contract?brand=hatagold`.

## Contract
- Theme owns H1. AI fragment bắt đầu bằng Direct Answer rồi H2/H3.
- Không `<html>`, `<head>`, site header/footer, script/style/iframe.
- Không tự bịa certification, capacity, years, partners, markets, ingredients, efficacy claims.
- Product facts theo Product Truth / Product Master / Approved Claim Library.
- Không dùng wording điều trị mỹ phẩm khi chưa có approved claim.
