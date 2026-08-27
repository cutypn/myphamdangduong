#!/usr/bin/env python3
import json
import sys
from pathlib import Path


def fail(message: str) -> None:
    raise SystemExit(f"[runtime-verify] {message}")


def main() -> None:
    if len(sys.argv) != 2:
        fail("usage: verify-runtime.py <runtime-status.json>")

    path = Path(sys.argv[1])
    try:
        payload = json.loads(path.read_text(encoding="utf-8"))
    except Exception as exc:
        fail(f"invalid runtime JSON: {exc}")

    repair = payload.get("repair") or {}
    catalog = payload.get("catalog_runtime") or {}

    expected_zero = (
        "wrong_featured_count",
        "product_not_found_count",
        "product_ambiguous_count",
        "poster_missing_count",
        "poster_ambiguous_count",
        "error_count",
    )

    if repair.get("manifest_total") != 44:
        fail(f"controlled manifest_total={repair.get('manifest_total')!r}, expected 44")
    if repair.get("matched_products") != 44:
        fail(f"controlled matched_products={repair.get('matched_products')!r}, expected 44")
    if repair.get("controlled_media_clean") is not True:
        fail("controlled_media_clean is not true")
    if repair.get("public_wrong_featured") not in ([], None):
        fail(f"public_wrong_featured is not empty: {repair.get('public_wrong_featured')!r}")

    for key in expected_zero:
        if int(repair.get(key, -1)) != 0:
            fail(f"{key}={repair.get(key)!r}, expected 0")

    if catalog.get("available") is not True:
        fail("WooCommerce product runtime is not available")
    if int(catalog.get("public_catalog_visible", 0)) <= 0:
        fail("public_catalog_visible must be greater than 0")
    if int(catalog.get("legal_hold_published", -1)) != 0:
        fail(f"legal_hold_published={catalog.get('legal_hold_published')!r}, expected 0")

    status = str(payload.get("status") or "")
    if status not in {"repair_clean", "repair_clean_unmanaged_media_gap"}:
        fail(f"unexpected runtime status: {status!r}")

    print(
        "[runtime-verify] PASS: controlled media 44/44 exact-clean, "
        f"public_catalog_visible={catalog.get('public_catalog_visible')}, "
        f"status={status}"
    )


if __name__ == "__main__":
    main()
