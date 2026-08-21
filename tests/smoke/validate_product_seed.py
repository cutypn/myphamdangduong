#!/usr/bin/env python3
import json
from pathlib import Path

ROOT = Path(__file__).resolve().parents[2]
SEED = ROOT / "apps" / "bizrise-ddg-migrator" / "data" / "product-truth-seed.json"
BRANDS = ROOT / "profiles" / "dang-duong" / "brand.json"
MEDIA = ROOT / "profiles" / "dang-duong" / "media-manifest.json"


def load(path: Path):
    with path.open("r", encoding="utf-8") as handle:
        return json.load(handle)


def main() -> None:
    seed = load(SEED)
    brands = load(BRANDS)
    media = load(MEDIA)

    records = seed.get("records", [])
    assert len(records) == 26, f"expected 26 approved source records, got {len(records)}"

    keys = [r["product_key"] for r in records]
    images = [r["source_image"] for r in records]
    assert len(keys) == len(set(keys)), "duplicate product_key"
    assert len(images) == len(set(images)), "duplicate source_image"

    required = {
        "product_key",
        "brand_taxonomy",
        "packaging_label",
        "official_name",
        "pack_size",
        "regulatory_status",
        "verification_status",
        "publish_allowed",
        "source_image",
    }

    for record in records:
        missing = required - set(record)
        assert not missing, f"{record.get('product_key', 'unknown')}: missing {sorted(missing)}"
        assert record["regulatory_status"] in {"active", "hold", "recalled", "retired", "unknown"}
        assert record["verification_status"] in {"verified", "partial", "unverified"}
        if record["regulatory_status"] != "active" or record["verification_status"] != "verified":
            assert record["publish_allowed"] is False, f"unsafe publish flag: {record['product_key']}"

    serum = next(r for r in records if r["product_key"] == "havigold-serum-nam-trang-da-18g")
    assert serum["regulatory_status"] == "hold"
    assert serum["publish_allowed"] is False
    assert any("1226/QĐ-SYT" in ref for ref in serum.get("source_refs", []))
    assert any("307/26/CBMP-CT" in ref for ref in serum.get("source_refs", []))

    water = next(r for r in records if r["product_key"] == "havigold-nuoc-tay-trang-210")
    assert water.get("conflicts"), "210g/210ml conflict must remain explicit"

    brand_map = {b["slug"]: b for b in brands.get("brands", [])}
    assert "hatagold" in brand_map
    assert "HAVIGOLD" in brand_map["hatagold"].get("packaging_labels", [])

    assert media["verification_sources"]["count"] == 26
    assert media["rules"]["notification_images_must_not_be_used_as_featured_images"] is True

    print("V2 product seed validation passed: 26 records")


if __name__ == "__main__":
    main()
