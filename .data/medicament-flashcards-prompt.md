# Build Prompt: Medicament Flashcard App (Laravel + Livewire)

## Goal

Build a flashcard study application for learning about medicaments. The source
of truth is a folder of Markdown files — one file per medicament — each
describing the drug's name, what it does, its risks, how to apply it, and
related fields. The app imports these files into a MySQL database, then presents
them as flashcards in a study session. After viewing a card's answer, the user
rates their recall as **"Know it"**, **"Not so sure"**, or **"Nothing at all"**,
and the app uses that rating to decide when the card resurfaces.

## Tech Stack (use exactly this)

- Laravel (latest stable, 11 or 12)
- Livewire 3 (for the study session and all interactivity)
- Alpine.js (for purely client-side niceties — flip animation, keyboard
  shortcuts — bundled via Livewire or imported in `app.js`)
- Tailwind CSS v4 (CSS-first config via `@import "tailwindcss"` and
  `@theme`, no `tailwind.config.js` unless genuinely needed)
- Vite for asset bundling
- MySQL

Don't pull in extra UI libraries (no Filament, no Jetstream). Keep it lean.

## Source Data Format

Markdown files live in `database/data/medicaments/*.md`. Each file uses YAML
front matter for structured fields, with the long-form body underneath. Assume
this shape, but parse defensively — some files may be missing optional fields:

```markdown
---
name: Ibuprofen
category: NSAID
risk_level: medium        # one of: low | medium | high
---

## What it does
Reduces inflammation, pain, and fever by inhibiting COX enzymes.

## Risks
GI bleeding, kidney strain with prolonged use, not for late pregnancy.

## How to apply
200–400 mg orally every 4–6 hours with food. Max 1200 mg/day OTC.
```

The `## What it does`, `## Risks`, and `## How to apply` sections should be
parsed out of the body into their own fields. If a heading is absent, store
null and don't break. Use `spatie/yaml-front-matter` for the front matter and
`league/commonmark` to render body sections to HTML for display. Keep the raw
markdown stored too, so re-renders are possible.

> If you'd prefer all fields in front matter instead of body headings, that's
> fine — make the importer handle whichever is present.

## Database Schema

**`medicaments`**
- `id`
- `slug` (unique, derived from filename or name)
- `name`
- `category` (nullable)
- `risk_level` (enum: low/medium/high, nullable)
- `what_it_does` (text, nullable) — rendered or raw, your call, but be consistent
- `risks` (text, nullable)
- `how_to_apply` (text, nullable)
- `raw_markdown` (longtext)
- `source_path` (string) — so re-imports can match existing rows
- timestamps

**`card_states`** (one row per medicament; single-user model for now)
- `id`
- `medicament_id` (FK, unique)
- `leitner_box` (unsigned tinyint, default 1) — boxes 1–5
- `last_rating` (enum: know / unsure / unknown, nullable)
- `last_reviewed_at` (nullable)
- `due_at` (nullable) — when the card is next eligible
- timestamps

Auth and multi-user are out of scope for v1. Structure the code so a `user_id`
could be added to `card_states` later without a rewrite, but don't build login.

## Importer

An idempotent Artisan command:

```
php artisan medicaments:import
```

- Scans `database/data/medicaments/*.md`
- Upserts by `source_path` (re-running updates existing rows, doesn't duplicate)
- Creates a `card_states` row (box 1, due now) for any new medicament
- Prints a summary: created / updated / skipped counts
- Should be safe to run repeatedly and after editing source files

Also wire this into a seeder so `php artisan migrate:fresh --seed` produces a
working app from the markdown files.

## Spaced Repetition Logic (Leitner)

Five boxes with increasing intervals. On rating a card:

- **Know it** → move up one box (max 5), set `due_at` by the new box's interval
- **Not so sure** → stay in current box, `due_at` = short interval
- **Nothing at all** → reset to box 1, `due_at` = now (resurfaces this session)

Suggested intervals (make these a single config array, easy to tweak):
box 1 → 10 min, box 2 → 1 day, box 3 → 3 days, box 4 → 7 days, box 5 → 14 days.

A study session queries cards where `due_at <= now()` (or null), ordered by
`due_at` then random, and serves them one at a time. When the due queue empties,
show a "session complete" state with a small summary (how many of each rating).

## Livewire Component & UI Flow

One primary full-page Livewire component, e.g. `StudySession`:

1. **Front of card** — shows the medicament name (and maybe category/risk
   badge). A "Reveal" button (or spacebar) flips to the back.
2. **Back of card** — shows what it does, risks, how to apply, rendered from
   markdown. Three rating buttons appear: Know it / Not so sure / Nothing at all.
3. On rating: persist the new `card_state`, advance to the next due card without
   a full page reload (Livewire handles the swap).
4. A small progress indicator (e.g. "12 cards remaining today").

Use Alpine for the flip animation and keyboard shortcuts (space = reveal,
1/2/3 = the three ratings). Keep the source of truth in Livewire; Alpine only
handles presentation.

## Styling (Tailwind v4)

- Clean, calm, readable. This is a study tool, so prioritize legibility and low
  friction over decoration.
- Centered single-card layout, generous whitespace, large tap targets for the
  rating buttons (color-code them: green / amber / red, but accessibly — don't
  rely on color alone, include labels).
- Risk-level badge with a sensible color scale.
- Responsive: works well on a phone (likely how it'll be used for quick review).
- Respect `prefers-color-scheme` for dark mode if it's low effort; otherwise
  light mode is fine for v1.

## Deliverables

1. Fresh Laravel app with the stack above installed and wired (Vite building
   Tailwind v4 + Alpine).
2. Migrations for both tables.
3. The `medicaments:import` command + seeder.
4. 3–4 realistic example markdown files in `database/data/medicaments/` so the
   app is demoable immediately (use common, well-known medicaments).
5. The `StudySession` Livewire component, its view, and a route.
6. A short `README` section covering: install steps, how to add new medicament
   markdown files, how to run the import, and how to tweak the Leitner intervals.

## Acceptance Criteria

- `composer install && npm install && npm run build`, configure `.env` for
  MySQL, then `php artisan migrate:fresh --seed` yields a working app.
- Visiting the study route shows a card; revealing then rating it advances to
  the next card.
- Rating "Nothing at all" makes the same card reappear within the session;
  "Know it" pushes it out by the box interval.
- Re-running `medicaments:import` after editing a markdown file updates the row
  without duplicating it and without wiping its `card_state`.
- No `tailwind.config.js` unless required; Tailwind configured CSS-first.

## Notes & Constraints

- Build incrementally and keep commits/steps logical: scaffold → schema →
  importer → study component → styling.
- Favor clear, maintainable code over cleverness. Small, well-named methods.
- If a decision is genuinely ambiguous (e.g. front-matter vs. body headings),
  pick the simpler option, note it in the README, and move on.
