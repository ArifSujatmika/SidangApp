# Agent Memory — Work Log

Running log of agent-performed steps. Newest first. Complements `phase.md` (feature checklist) and `progress.md` (Phase 9 notes).

---

## Session: AI Virtual Assistant + Queue Fix (2026-07-30)

### Root Cause Found
AI analysis feature (Phase 10) was fully implemented but **queue worker wasn't running**. The `AnalyzeDocument` job sits in the database queue indefinitely without a worker. SQLite/MySQL jobs table was empty (0 pending, 0 failed).

### Done
- [x] Started `php artisan queue:listen` in background to process AI analysis jobs.
- [x] Created `chat_messages` migration + `ChatMessage` model.
- [x] Created `AiAssistantService` — gathers system context (users, schedules, submissions, revision notes, analyses) and sends chat to LLM with conversation history.
- [x] Created `ChatAssistant` Livewire component — chat UI with message send, loading state, clear history.
- [x] Created route `GET /admin/ai-assistant` gated with `can:admin`.
- [x] Created `admin/ai-assistant.blade.php` + `livewire/chat-assistant.blade.php` views.
- [x] Added sidebar menu item "Asisten AI" to both desktop and mobile sidebars under admin section.
- [x] 10 tests (6 feature + 4 unit) covering access control, send/receive, validation, history clear, persistence.
- [x] Verification: 65 tests (200 assertions) all green, Pint clean, Vite build succeeded.

### Key decisions
- `ChatAssistant` uses array public properties (not Collection) for Livewire 4 compatibility.
- `AiAssistantService::chat()` persists both user message and assistant reply to DB, plus returns reply string.
- System context is gathered dynamically per request for fresh data.
- Conversation history limited to last 20 messages with token budgeting.
- Mocked `AiAssistantService` in feature tests to avoid real API calls.

---

## Session: Docs audit & sync (2026-07-22)

### Done
- [x] Reviewed all 5 docs against actual codebase state.
- [x] Updated `prd.md`: tech stack, FR-01 (Fortify), DB schema, MVP-01 plotting flow.
- [x] Updated `design.md`: teal convention, screen blueprints (Alpine checkboxes).
- [x] Updated `phase.md`: Phase 8 all ✅, footer test count.
- [x] Updated `progress.md` + `memory.md`: entries for this session.
- [x] Verification: 38 tests passed (100 assertions).

---

## Session: Schedule picker UX + README

### Done
- [x] Replaced Ctrl/Cmd native multi-selects with Alpine-backed checkbox lists on schedule create/edit forms.
- [x] Added selected counters and select-all/clear-all actions for dosen and mahasiswa.
- [x] Added mahasiswa search by name or NIM; checkbox values and backend request names remain unchanged.
- [x] Added global `[x-cloak]` CSS to prevent pre-Alpine search-list flicker.
- [x] Added root `README.md` documenting setup, role workflows, seed accounts, verification commands, and docs locations.
- [x] Verification: Blade view cache OK; Vite production build OK; Pint passed; **38 tests passed (100 assertions)**.

---

## Session: Mahasiswa participant plotting

### Done
- [x] Added migration making `submissions.judul_laporan` and `file_path` nullable for admin-created participant placeholders.
- [x] Schedule create/edit now assigns dosen and mahasiswa participants in one plotting form.
- [x] Participant sync creates/moves submission placeholders; unselected empty placeholders are deleted.
- [x] Uploaded participant submissions cannot be removed by unchecking; they are retained with an admin warning.
- [x] Added mahasiswa completion route/view for assigned placeholders; upload remains PDF max 10MB.
- [x] Added placeholder CTA on mahasiswa dashboard and safe 404 for downloading an empty placeholder.
- [x] Expanded seeder with an assigned participant awaiting upload.
- [x] Added `ScheduleParticipantTest` (4 scenarios).
- [x] Final verification: `migrate:fresh --seed` OK; Pint passed; Vite production build passed; **38 tests passed (100 assertions)**.

### Architecture decision
- Participant source of truth remains `submissions.schedule_id`; no `schedule_mahasiswa` pivot. This avoids duplicate assignment state.

---

## Session: Teal/Cyan theme + home redirect

### Done
- [x] Changed Flux accent from zinc/neutral to teal: `teal-600` light, `teal-400` dark (`resources/css/app.css`).
- [x] Added subtle teal tint to app sidebar/header surfaces.
- [x] Replaced hardcoded zinc primary buttons and blue links with teal variants across admin, dashboard, submission, and revision views.
- [x] Fixed unreadable `text-black` on the admin "Tambah Pengguna" primary button.
- [x] Guest `/` now redirects to Fortify `login`; authenticated users still redirect to `dashboard`. `welcome.blade.php` remains unused.
- [x] Updated `tests/Feature/ExampleTest.php` to assert the new guest redirect behavior.
- [x] Updated `docs/design.md` with the Teal/Cyan theme convention.
- [x] Verification: Pint passed; **34 tests passed (83 assertions)**; `npm run build` succeeded.

### Pending / next
- [ ] Implement admin assignment of mahasiswa participants during schedule plotting via nullable submission placeholders (approved design: removing a participant with an uploaded file is skipped + warning).

---

## Session: Docs restructure + PRD expansion + design system

### Done
- [x] Reviewed codebase vs `prd.md`: confirmed FR-01..04 + MVP-01..07 status against actual code.
  - Verified: `schedule_dosen` pivot, model relations, dosen dashboard filter, `download-submission` gate, Fortify auth, admin CRUD.
  - Identified gap: **no admin UI to plot/assign dosen ↔ schedule** (pivot exists, form field missing).
- [x] Created `docs/` folder; moved `prd.md`, `phase.md`, `progress.md` into it. `AGENTS.md` kept at root (tooling convention).
- [x] Expanded `docs/prd.md`:
  - **MVP-01** → full spec: dosen plotting multi-select, `dosens()->sync()`, validation, conflict-detection rule, mahasiswa↔jadwal re-plot.
  - **MVP-02** → documented verified CRUD state + gaps (delete confirm, admin self-delete guard, ID validation messages).
- [x] Created `docs/design.md`: Flux 2 + Tailwind shadcn-inspired design system (tokens, radius/typography, Flux component inventory, status badge mapping, screen blueprints). Note: shadcn is React-only → borrow aesthetic, implement in Flux/Blade.
- [x] Created `docs/memory.md` (this file).

### Key facts learned (for future sessions)
- Stack: Livewire 4 + Flux 2 + Blade. **No React** → shadcn cannot be used directly.
- Gates defined in `AppServiceProvider::configureGates()` (admin/dosen/mahasiswa/download-submission), not Policy classes.
- Roles on `users.role`: `mahasiswa` (default) | `dosen` | `admin`. Login via `username` (NIM/NIDN).
- Seed creds: admin `telo` / `kaspe`.
- Files stored on `Storage::disk('local')` (private); download via gated controller stream.

## Session: MVP-01/02 implementation + test fixes

### Done
- [x] **MVP-01 dosen plotting**: added multi-select "Dosen Penguji" to `schedules-create.blade.php` + `schedules-edit.blade.php`; `AdminController::storeSchedule/updateSchedule` now `dosens()->sync()`; extracted `validateSchedule()` with `dosen_ids` array validation. Schedules index shows assigned dosen names.
- [x] **MVP-02 guards**: `destroyUser` blocks self-delete (403) + last-admin delete; JS confirm() on user & schedule delete forms.
- [x] **Seeder expanded**: 1 admin, 2 dosen, 3 mahasiswa, 3 jadwal (with dosen plots), 2 submissions, revision note + attachment. `migrate:fresh --seed` OK.
- [x] Fixed pre-existing bugs found via tests:
  - Invalid Flux icon `upload` → `arrow-up-tray` in `sidebar.blade.php` + `header.blade.php` (was 500-ing all authed pages).
  - Missing `new` keyword in `⚡profile.blade.php` single-file component (`#[Title] class` → `new #[Title] class`) — caused ParseError.
- [x] `php artisan test --compact`: **34 passed**. Pint clean.

### Notes
- Flux Free icon set: use Heroicons names (`arrow-up-tray`, not `upload`).
- Livewire single-file components need `new` before `#[Attribute] class extends Component`.
- Clear `view:clear` when hitting stale compiled-view ParseErrors.
- Used plain `<select multiple>` (not `flux:select listbox`) for dosen picker to keep it dependency-light; can upgrade to Flux listbox per `design.md` later.
