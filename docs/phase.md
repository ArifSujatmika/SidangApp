# SIMSIDANG — Phase Development Checklist
## Sistem Manajemen Sidang Akademik (Laravel 13 + Fortify + TailwindCSS)

---

## Phase 0: Project Infrastructure ✅
- [x] Setup Laravel 13 project (Livewire starter kit)
- [x] Config database MySQL
- [x] Setup storage link & disk config

## Phase 1: DB Migrations & Models ✅
- [x] Tables: users (+username, +role), schedules, submissions, revision_notes, revision_attachments
- [x] Models with relationships (User, Schedule, Submission, RevisionNote, RevisionAttachment)
- [x] UserFactory with role states
- [x] DatabaseSeeder (1 dosen, 1 mahasiswa, 1 schedule, 1 submission, 1 revision note + attachment)

## Phase 2: Auth & RBAC ✅
- [x] Fortify login via username
- [x] Gates: admin, dosen, mahasiswa
- [x] Role-based dashboard redirect (same route /dashboard, different view per role)

## Phase 3: Submission Upload (Mahasiswa) ✅
- [x] SubmissionController (create, store, show, download)
- [x] View: submissions/create.blade.php (PDF, max 10MB, Storage::local)
- [x] Dashboard mahasiswa shows submission + revision notes

## Phase 4: Dosen Dashboard & Schedule Filter ✅
- [x] DashboardController → dosenDashboard() filters by today's schedules
- [x] View: dashboard-dosen.blade.php (table per schedule, eager load submissions.user)
- [x] Link to detail & download

## Phase 5: Revision Notes (Dosen) ✅
- [x] RevisionController (create revision notes, resolve status)
- [x] Dosen can add revision notes per submission

## Phase 6: Revision Response (Mahasiswa) ✅
- [x] RevisionController (reply with keterangan + file attachment)
- [x] Validasi attachment: PDF, DOCX, JPEG, PNG, max 5MB
- [x] Dosen resolves poin

## Phase 7: Views & Routing ✅
- [x] Routes: dashboard, submissions CRUD, revisions reply & resolve
- [x] Views: dashboard-admin, dashboard-dosen, dashboard-mahasiswa, submissions.create, submissions.show, revisions.reply
- [x] Flash messages on success

## Phase 8: Verification & Self-Check ✅
- [x] Run migrate:fresh --seed
- [x] Test auth flow (login as admin/dosen/mahasiswa)
- [x] Verify dashboard per role
- [x] Test upload submission
- [x] Test revision flow
- [x] Clean debug artifacts

## Phase 9: MVP Enhancements ✅
- [x] Pivot table schedule_dosen (migration + relasi)
- [x] Admin CRUD users (create/edit dosen & mahasiswa)
- [x] Admin CRUD schedules
- [x] Plotting dosen penguji pada form jadwal
- [x] Plotting mahasiswa peserta via submission placeholder
- [x] Mahasiswa melengkapi judul + PDF setelah diplot admin
- [x] Safe participant removal (uploaded submission retained + warning)
- [x] Workflow status transitions (admin action)
- [x] Dosen dashboard filter by assigned schedules
- [x] Download access control (gate check)
- [x] Logout link in layout
- [x] Update seeder: admin (telo/kaspe), 2 dosen, 3 mahasiswa, 3 jadwal
- [x] Migrate:fresh --seed & verify
- [x] Full automated test suite: 38 passed (100 assertions)
- [x] Production frontend build succeeded

---
**Status:** Phase 0–9 selesai. Plotting dosen/mahasiswa dengan Alpine checkbox picker, upload placeholder, seeder, dan 38 test (100 assertions) semuanya passing.
