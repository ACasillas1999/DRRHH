---
name: ai-context
description: >
  Shared cross-AI/cross-session project log for this repo. Reads and writes AI_CONTEXT.md
  at the project root so any AI tool (Claude Code, Cursor, Copilot, another Claude session)
  editing this project can hand off context cheaply — decisions made, gotchas found, scope
  agreed with the user, pending work, test data left in shared systems. Capped at 10 entries
  so reading it never costs much.
  Use PROACTIVELY: read it before starting non-trivial work in this project, append an entry
  after finishing non-trivial work. Also trigger on "check AI context", "log this for other AIs",
  "what did the other AI do", "read project handoff notes", "/ai-context".
---

# AI Context Log

One file, `AI_CONTEXT.md` at the project root, holding a short reverse-chronological log any
AI working on this repo reads and writes. It is NOT a changelog and NOT a memory dump — git
already tracks what changed. This file tracks what the diff *can't* tell you.

## When to read

Before starting non-trivial work in this project (new feature, bug fix that touches more
than a line or two, anything spanning multiple files). Skip for trivial one-liners.

If `AI_CONTEXT.md` doesn't exist yet, this is the first entry — skip reading, go write.

Read the whole file — it's capped small on purpose (see below), so this is cheap.

## When to write

After finishing non-trivial work, before ending the turn. Prepend a new entry at the **top**
(most recent first). Skip for trivial changes — a typo fix doesn't need an entry.

### What belongs in an entry (high signal only)

- Decisions made and *why* (especially ones a future agent could easily reverse by accident)
- Gotchas / non-obvious facts about the codebase or external systems (schema quirks, legacy
  system behavior, naming inconsistencies, FK constraints that aren't obvious from one table)
- Scope boundaries the user agreed to (what was explicitly deferred/excluded, and why)
- Test/throwaway data left behind in shared systems (a real DB, a real API) that another
  agent or the user needs to know about
- Pending/next work, if the user indicated more is coming

### What does NOT belong (skip these — git already has them)

- Step-by-step account of what you did (that's the diff)
- File lists, line counts, routine implementation detail
- Anything a `git log`/`git diff` read would show just as well

### Entry format

```
## YYYY-MM-DD — <agent/tool name, e.g. "Claude Code (Sonnet 5)">
<1-4 short lines. Fragments fine. No filler.>
```

Keep each entry to a handful of lines — a paragraph, not a report. If it's not worth
remembering in 50 tokens, it's not worth an entry.

### Cap: 10 entries, oldest drop off

Before writing, count `##` headers in the file. If already at 10, delete the oldest (bottom)
entry so the file stays at 10 after your addition. This keeps every future read cheap
regardless of how old the project gets — no unbounded growth, no archive file to manage.

## Example entry

```
## 2026-07-14 — Claude Code (Sonnet 5)
Ported legacy RH Empleados module to Laravel (listado, detalle, crear, editar, baja, foto, docs Word).
Gotcha: laborales_empleado.Nomeclatura vs razones_sociales.Nomenclatura — different spelling per table, FK enforced.
Gotcha: several laborales_empleado/fechas_laborales columns are NOT NULL with no default (Efectivo, Salario_*, comentario, motivo_baja, recontratable) — insert 0/'' not null.
Deferred (user OK'd): auth/roles not ported, no login system in new app yet.
Left test data in real DB: empleado 1434 (BAJA, test comment), empleado 999999 (full test record) — user will clean up.
```
