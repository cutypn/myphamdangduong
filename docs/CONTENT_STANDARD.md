# DDG V2 Content Standard

Status: Authoritative editorial baseline for V2  
Last updated: 2026-08-21

## 1. Purpose

DDG V2 content must be useful to readers first, indexable/searchable second, and safe to reuse by sales, affiliate, CRM and AI systems. A page is not considered complete just because it has a title, excerpt, bullet points and FAQ.

Legacy knowledge-bank files are reference material only. They must not be bulk-published as finished articles.

## 2. Editorial states

Every article must move through explicit states:

1. `outline` — topic, keyword, intent and angle only.
2. `researched` — source set checked; factual gaps marked.
3. `editorial_review` — full article written and structurally reviewed.
4. `legal_or_claim_review` — required when the article contains regulatory, certification, corporate-capability, product-efficacy or safety claims.
5. `publish_ready` — sources, links, metadata and factual scope are complete.
6. `published` — only after staging/site QA.

No generator may turn `outline` directly into `published`.

## 3. Mandatory article metadata

Each indexable article must have:

```yaml
slug: ""
title: ""
primary_keyword: ""
secondary_keywords: []
search_intent: ""
category: ""
seo_title: ""
meta_description: ""
direct_answer: ""
canonical_path: ""
source_scope: ""
sources: []
review_status: ""
reviewer: ""
last_verified: ""
internal_links: []
cta: ""
```

If a required factual field is unknown, keep it unknown. Do not infer it to make the page look complete.

## 4. Structure contract

A normal article should follow this logic when appropriate:

- exactly one H1;
- Direct Answer immediately after the H1;
- context/problem framing;
- core explanation;
- practical framework/checklist/table when it helps;
- risks, trade-offs or common mistakes;
- FAQ only when the questions genuinely fit the topic;
- internal links;
- sources and `Last verified`;
- one relevant CTA.

The same generic FAQ must not be copied onto every article.

## 5. Source hierarchy

Prefer sources in this order:

1. Vietnamese regulators / official legal documents;
2. ASEAN or other directly applicable official frameworks;
3. first-party verified corporate/product records;
4. approved project Product Truth;
5. reputable secondary sources for context only.

Distributor, marketplace, affiliate, advertorial and social posts may help discover terminology or market context, but they are not legal Product Truth.

## 6. B2B / OEM / ODM articles

- Treat `OEM`, `ODM` and `private label` as business terminology unless a cited rule defines a specific legal meaning.
- Do not imply DDG owns a factory, certification, capacity, MOQ, turnaround time or technical capability unless the corporate source has been verified.
- Distinguish educational process guidance from legal requirements.
- When discussing cosmetics regulation in Viet Nam, cite the current legal source set and note amendments/updates where material.
- Do not give legal guarantees such as “đủ hồ sơ là chắc chắn được cấp”.

## 7. Beauty articles

- Cosmetics are not medicines.
- Do not use or strengthen prohibited/high-risk claims without approved evidence.
- Avoid fear marketing, body/age shaming and guaranteed outcomes.
- Do not invent suitability, pregnancy safety, ingredient concentration, clinical proof, SPF/PA, result timeline or compatibility.
- For adverse reactions, prioritize stopping experimentation and seeking appropriate professional help when symptoms are significant or persistent.

## 8. Product and brand references

Any product-specific fact must come from Product Truth with provenance. Articles may explain a routine role, but must not silently upgrade a product claim.

If `regulatory_status != active` or required Product Truth is incomplete, do not use the SKU as a recommendation target.

## 9. SEO / AI Search

- one primary intent per URL;
- one H1 per indexable URL;
- H1 contains the primary keyword naturally;
- Direct Answer should answer the query, not tease it;
- use semantic H2/H3 headings;
- avoid keyword stuffing;
- use descriptive internal anchor text;
- add canonical, breadcrumb and schema in the presentation/SEO layer;
- `last_verified` must reflect an actual source check, not a bulk timestamp.

There is no fixed word-count target. Completeness and clarity take priority over length.

## 10. Language and tone

Vietnamese-first, clear and professional. English terms such as `brief`, `Product Truth`, `go-to-market`, `launch`, `scope` may be used when useful, but the Vietnamese meaning should remain understandable without specialist jargon.

Tone:

- knowledgeable, not lecturing;
- premium, not distant;
- practical, not sensational;
- precise, not overconfident.

## 11. Legacy migration rule

The legacy 40-article knowledge bank is an editorial idea bank, not a publish-ready source. During V2 migration:

- preserve useful topic/keyword/slug candidates;
- rewrite the body from readable source;
- verify facts and current law;
- review internal links;
- set articles to draft until they pass the editorial gate;
- never run the legacy `build_content()` bulk auto-publish behavior in V2.
