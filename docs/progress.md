# Progress

## Phase 9 work

- [x] Updated frontend accent and primary actions to Teal/Cyan; guest `/` redirects to login.
- [x] Verified admin access route via `tests/Feature/AdminUserTest.php`.
- [x] Confirmed pivot relation `schedule_dosen` exists and is used by `User` and `Schedule` models.
- [x] Added admin dosen assignment to schedule create/edit forms.
- [x] Added mahasiswa participant assignment to schedule create/edit forms.
- [x] Added nullable submission placeholders for assigned participants; mahasiswa completes report later.
- [x] Participant removal deletes only empty placeholders; uploaded reports are retained with warning.
- [x] Confirmed dosen dashboard filters schedules by assigned `dosens` relationship.
- [x] Confirmed download access control via `download-submission` gate in `AppServiceProvider`.
- [x] Expanded seeder to 1 admin, 2 dosen, 3 mahasiswa, 3 schedules, assignments, complete submissions, and a placeholder.
- [x] Added `ScheduleParticipantTest` covering participant assignment, safe removal, retention, and upload completion.
- [x] Final verification: `migrate:fresh --seed` succeeded; Pint passed; production build succeeded; 38 tests passed (100 assertions).
- [x] Replaced native multi-select schedule pickers with scrollable checkbox lists, selected counters, select/clear-all actions, and mahasiswa name/NIM search.
- [x] Added root `README.md` covering installation, seed accounts, role workflows, commands, and project documentation.
- [x] Picker UX verification: Blade view cache succeeded, Vite production build succeeded, Pint passed, and 38 tests passed (100 assertions).

## Session: Docs audit & sync (2026-07-22)

- [x] Reviewed all 5 docs against actual codebase state.
- [x] Updated `prd.md`: tech stack (Tailwind 4 + Flux 2), FR-01 (Fortify), DB schema (actual migration syntax + nullable fields + schedule_dosen pivot), MVP-01 plotting flow (Alpine checkboxes).
- [x] Updated `design.md`: teal accent convention, screen blueprints reflect Alpine checkbox lists instead of Flux listbox.
- [x] Updated `phase.md`: Phase 8 all ✅, footer with current test count.
- [x] Updated `progress.md` + `memory.md`: this session entry.
- [x] Final verification: 38 tests passed (100 assertions).

## Notes

- Project docs live under `docs/`; root `AGENTS.md` links to them.
- Participant source of truth is `submissions.schedule_id`, not a separate participant pivot.
- `judul_laporan` and `file_path` are nullable only while the assigned participant has not completed the report.
- Full verification results are recorded after every implementation batch.
