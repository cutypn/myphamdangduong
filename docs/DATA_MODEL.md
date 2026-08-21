# Data Model — Product Truth V2

## Identity

- canonical title: WordPress post title
- `_bizrise_sku`
- brand taxonomy: `bizrise_brand`
- `_bizrise_packaging_label` for the label printed on packaging when it differs from taxonomy/entity naming
- `_bizrise_pack_size`

## Regulatory and verification

- `_bizrise_regulatory_status`: `active | hold | recalled | retired | unknown`
- `_bizrise_verification_status`: `verified | partial | unverified`
- `_bizrise_legal_hold`: boolean
- `_bizrise_verified_at`
- `_bizrise_verified_by`
- `_bizrise_last_verified`
- `_bizrise_source_refs`: list of provenance references

`hold` means the product must not be published even when other descriptive data is known. A withdrawal of a cosmetic notification receipt is recorded as a hold unless a source specifically establishes a consumer-level recall.

## Claims

- `_bizrise_approved_claims`
- `_bizrise_claim_sources`

The system must never strengthen or invent claims. Marketplace/distributor copy is not Product Truth.

## Media

- `_bizrise_media_mapping_key`
- `_bizrise_media_mapping_version`

Migrator-specific attachment metadata is defined in the migrator module so repeat runs remain idempotent and manual selections are preserved.

## Publication gate

A product can remain `publish` only when regulatory status is active, verification is verified, no legal hold exists, the title/brand/pack size are available, and provenance exists. Invalid publish attempts are demoted to draft and gate reasons are recorded.
