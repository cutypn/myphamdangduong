# ADR-0001: Clean rebuild instead of legacy extension

Status: Accepted
Date: 2026-08-21

## Context

The legacy release flow reconstructs theme/core payloads and uses multiple MU-plugin hotfix/data/page components. That makes source inspection, testing, migration safety and rollback reasoning unnecessarily difficult.

## Decision

Build V2 on `codex/rebuild-v2` as a clean, readable-source WordPress architecture with three explicit modules:

- `bizrise-core`
- `bizrise-ddg-theme`
- `bizrise-ddg-migrator`

Legacy stays available only for audit/migration until staging passes.

## Consequences

Positive:
- deterministic Git diffs
- testable data/publication rules
- clean separation between data and presentation
- reversible cutover
- safer Multisite reuse

Tradeoff:
- migration work is explicit rather than hidden behind hotfix compatibility
- V2 must rebuild templates/deploy flow instead of copying legacy runtime behavior
