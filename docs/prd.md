# Product Requirement Document (PRD)

## Project Name: Sistem Manajemen Sidang Akademik (SIMSIDANG)
**Author:** Senior Laravel Developer  
**Status:** Updated (Laravel 13 & MySQL Focus)  
**Target Release:** Q3 2026  

---

## 1. Objective & Product Overview
Aplikasi ini bertujuan untuk mendigitalisasi proses pasca-sidang dan pelaksanaan sidang (Tugas Akhir, Kerja Praktek, dll). Sistem ini menjembatani mahasiswa untuk mengelola revisi secara transparan dan dosen penguji untuk mengakses berkas laporan berdasarkan ruang atau grouping jadwal sidang secara real-time.

### Target Audiences
*   **Mahasiswa:** Mengunggah draf laporan, melihat catatan revisi dari penguji, dan mengunggah bukti perbaikan.
*   **Dosen Penguji:** Memeriksa laporan mahasiswa sesuai ruang/jadwal sidang yang ditugaskan dan memberikan catatan revisi terstruktur.

---

## 2. User Personas & User Journeys

### 2.1 Mahasiswa Journey
1. Login menggunakan NIM.
2. Mengunggah dokumen laporan awal (PDF) ke sistem.
3. Melaksanakan sidang, lalu memantau *input* revisi dari Dosen Penguji melalui dashboard.
4. Menerima daftar poin revisi (*Revision Notes*).
5. Merespons tiap poin revisi dengan menuliskan penjelasan dan melampirkan (*attach*) bukti gambar/dokumen.
6. Menunggu status tiap poin berubah menjadi *Resolved* (Disetujui Dosen).

### 2.2 Dosen Penguji Journey
1. Login menggunakan NIDN.
2. Masuk ke halaman dashboard utama yang otomatis memfilter daftar mahasiswa berdasarkan **Ruang Sidang** dan **Grouping Jadwal** pada hari berjalan.
3. Membuka detail mahasiswa untuk membaca/mengunduh dokumen laporan saat sidang.
4. Memberikan catatan revisi per poin secara spesifik ke mahasiswa.
5. Memeriksa kembali balasan revisi dari mahasiswa (melihat teks penjelasan & attachment), lalu mengubah status poin menjadi *Resolved*.

---

## 3. Functional Requirements (Fitur Utama)

### FR-01: Autentikasi & Manajemen Pengguna
*   **Kebutuhan:** Login multi-role (Mahasiswa & Dosen).
*   **Spesifikasi Laravel 13:** 
    *   Menggunakan **Laravel Fortify** (bukan Livewire starter kit) untuk backend auth — login via `username` (NIM/NIDN).
    *   Pemisahan hak akses (*Role-based Access Control*) menggunakan Laravel Gate di `AppServiceProvider::configureGates()`.
    *   *Security check*: Rate-limiting login bawaan Fortify untuk mencegah brute force.
    *   Passkey authentication tersedia melalui Fortify + tabel `passkeys`.

### FR-02: Upload Data Laporan (Mahasiswa)
*   **Kebutuhan:** Mahasiswa mengunggah berkas laporan utama sebelum sidang.
*   **Spesifikasi Teknik:**
    *   Format file wajib `.pdf` dengan batas maksimal ukuran file 10MB.
    *   Penyimpanan menggunakan `Storage::disk('local')` (di luar direktori publik) untuk menjaga kerahasiaan data sebelum dipublikasikan.
    *   File diakses oleh dosen menggunakan mekanisme *Stream/Download Response* yang aman melalui Controller terlindungi.

### FR-03: Dashboard Ruang & Jadwal Sidang (Dosen)
*   **Kebutuhan:** Dosen melihat mahasiswa sesuai ruangan dan *grouping* jadwal.
*   **Spesifikasi Teknik:**
    *   Halaman dashboard menyajikan data mahasiswa yang difilter secara *default* berdasarkan tanggal hari ini dan `ruang_id` di mana dosen tersebut bertugas.
    *   Optimasi *query* MySQL menggunakan eagear loading (`with('student')`) untuk menghindari masalah N+1 query saat menampilkan daftar mahasiswa di ruangan tersebut.

### FR-04: Manajemen Poin Revisi & Attachment (Dosen & Mahasiswa)
*   **Kebutuhan:** Dosen memberikan note per poin, Mahasiswa menjawab dan melampirkan dokumen pendukung per poin.
*   **Spesifikasi Teknik:**
    *   Sistem mencatat revisi secara granular (per item catatan).
    *   Mahasiswa diizinkan mengunggah dokumen pendukung (Format: `.pdf`, `.docx`, `.jpeg`, `.png`, max 5MB per file).
    *   Pemanfaatan fitur *File Validation* terpusat pada Form Request Laravel 13.

---

## 4. Technical Architecture & Database Design (MySQL Focus)

### 4.1 Tech Stack
*   **Framework:** Laravel 13.x (PHP 8.4+)
*   **Database:** MySQL 8.x (Memanfaatkan fitur JSON columns jika dibutuhkan untuk fleksibilitas log/metadata).
*   **Frontend:** Tailwind CSS 4 + Flux 2 (Livewire 4) — komponen antarmuka berbasis Flux + Alpine.js.

### 4.2 Database Schema (MySQL Migration Architecture)

Tables `users`, `schedules`, `submissions`, `revision_notes`, `revision_attachments`, `schedule_dosen` (pivot).

```php
// 1. Tabel Users — kombinasi Admin/Dosen/Mahasiswa + Fortify fields
//    username & role ditambahkan via migration terpisah
Schema::table('users', function (Blueprint $table) {
    $table->string('username')->unique()->after('id');     // NIM (mahasiswa) / NIDN (dosen)
    $table->enum('role', ['mahasiswa', 'dosen', 'admin'])
        ->default('mahasiswa')->after('email');
    // Kolom Fortify: two_factor_secret, two_factor_recovery_codes,
    // two_factor_confirmed_at, remember_token, email_verified_at, passkeys
});

// 2. Tabel Schedules (Ruang & Grouping Jadwal)
Schema::create('schedules', function (Blueprint $table) {
    $table->id();
    $table->string('nama_grup_sidang');                    // "Sidang TA Gelombang 1"
    $table->string('ruangan');                             // "Ruang Lab Komputer 3"
    $table->date('tanggal_sidang');
    $table->time('jam_mulai');
    $table->time('jam_selesai');
    $table->timestamps();
});

// 3. Tabel Submissions (Laporan Utama Mahasiswa)
Schema::create('submissions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
    $table->foreignId('schedule_id')->constrained('schedules');
    $table->string('judul_laporan')->nullable();           // nullable untuk placeholder
    $table->string('file_path')->nullable();               // placeholder sampai mahasiswa upload
    $table->enum('status', ['pending', 'sidang_berjalan', 'revisi', 'selesai'])
        ->default('pending');
    $table->timestamps();
});

// 4. Tabel Revision Notes (Catatan Poin Revisi dari Dosen)
Schema::create('revision_notes', function (Blueprint $table) {
    $table->id();
    $table->foreignId('submission_id')->constrained('submissions')->cascadeOnDelete();
    $table->foreignId('dosen_id')->constrained('users');
    $table->text('catatan_revisi');
    $table->enum('status_poin', ['open', 'resolved'])->default('open');
    $table->timestamps();
});

// 5. Tabel Revision Attachments (Bukti Revisi dari Mahasiswa)
Schema::create('revision_attachments', function (Blueprint $table) {
    $table->id();
    $table->foreignId('revision_note_id')->constrained('revision_notes')->cascadeOnDelete();
    $table->text('keterangan_mahasiswa')->nullable();
    $table->string('file_path');
    $table->timestamps();
});

// 6. Pivot Dosen ↔ Jadwal
Schema::create('schedule_dosen', function (Blueprint $table) {
    $table->id();
    $table->foreignId('schedule_id')->constrained('schedules')->cascadeOnDelete();
    $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
    $table->unique(['schedule_id', 'user_id']);
    $table->timestamps();
});
```

---

## 5. MVP Additions (Post-Initial Build)

### MVP-01: Relasi Dosen ↔ Jadwal + Plotting (Pivot Table)

**Status Implementasi:** ✅ plotting dosen dan mahasiswa peserta selesai.

#### Sudah ada (verified)
- Migration `schedule_dosen` (`schedule_id`, `user_id`, unique composite + timestamps) — `database/migrations/2026_07_13_090000_create_schedule_dosen_table.php`.
- Relasi `Schedule::dosens()` (belongsToMany) & `User::assignedSchedules()` (belongsToMany).
- Dashboard dosen sudah filter jadwal via `whereHas('dosens')` — `DashboardController::dosenDashboard()`.
- Gate `download-submission` cek dosen terasign — `AppServiceProvider`.

#### Implementasi plotting
1. **Plotting Dosen → Jadwal (Admin UI)**
   - Field multi-select "Dosen Penguji" pada form create/edit jadwal (`admin/schedules/create`, `admin/schedules/{schedule}/edit`).
   - Sumber opsi: `User::where('role', 'dosen')->get()`.
   - `AdminController::storeSchedule()` & `updateSchedule()` → `$schedule->dosens()->sync($validated['dosen_ids'])`.
   - Validasi:
     ```php
     'dosen_ids' => ['nullable', 'array'],
     'dosen_ids.*' => ['exists:users,id'],
     ```
     Tambahan: pastikan setiap id ber-`role = dosen` (rule closure / `Rule::exists` dengan where).
    - Index jadwal (`admin/schedules`) tampilkan nama dosen terpasang + jumlah (`$schedule->dosens->pluck('name')`).
2. **Konflik jadwal dosen (belum diimplementasikan)**
   - Saat assign, deteksi jika dosen sudah ter-plot di jadwal lain dengan `tanggal_sidang` sama & rentang `jam_mulai`–`jam_selesai` beririsan → tampilkan peringatan (non-blocking flash warning).
3. **Plotting Mahasiswa → Jadwal (selesai)**
   - Admin memilih mahasiswa peserta pada form create/edit jadwal bersama dosen penguji menggunakan **Alpine checkbox list** dengan search by nama/NIM dan select-all/clear-all.
   - Setiap peserta direpresentasikan oleh `submissions.schedule_id`; peserta baru mendapat submission placeholder (`status = pending`, `judul_laporan` dan `file_path` nullable).
   - Mahasiswa melengkapi placeholder melalui halaman `submissions/{submission}/edit`; jadwal tidak dapat dipilih ulang oleh mahasiswa.
   - Jika peserta dibatalkan sebelum upload, placeholder dihapus. Jika file sudah diunggah, peserta tetap dipertahankan dan admin menerima warning dengan daftar nama yang dipertahankan.
   - Seeder memuat contoh peserta yang sudah diplot tetapi belum mengunggah laporan.
   - Verifikasi implementasi: `ScheduleParticipantTest` (4 skenario, 17 assertions).

### MVP-02: Admin CRUD Users & Schedules

**Status Implementasi:** ✅ CRUD dasar, plotting dosen/peserta, konfirmasi hapus, dan guard admin selesai.

#### Sudah ada (verified — `AdminController`, `routes/web.php`)
- Users: `index`, `create`, `store`, `edit`, `update`, `destroy` (named `admin.users.*`).
- Schedules: `index`, `create`, `store`, `edit`, `update`, `destroy` (named `admin.schedules.*`).
- Update status submission: `admin.submissions.update-status`.
- Middleware `can:admin` di constructor controller.
- Validasi unik username/email (ignore self saat update).

#### Implementasi tambahan
1. **Field assign dosen dan mahasiswa peserta** tersedia pada form schedule create/edit.
2. **Konfirmasi hapus** tersedia pada aksi hapus user dan jadwal.
3. **Guard integritas admin** mencegah admin menghapus akun dirinya sendiri dan admin terakhir.
   ```php
   abort_if($user->id === auth()->id(), 403, 'Tidak dapat menghapus akun sendiri.');
   ```
4. **Pesan validasi Bahasa Indonesia** konsisten di seluruh form admin (penyempurnaan lanjutan).

### MVP-03: Workflow Status Transitions
- Admin trigger perubahan status: pending → sidang_berjalan → revisi → selesai.
- Tombol aksi di view admin.

### MVP-04: Dosen Dashboard Filter By Assignments
- Dashboard dosen tampilkan submission dari pivot schedule_dosen saja.

### MVP-05: Download Access Control
- Gate: dosen hanya bisa download submission dari schedule terasign.

### MVP-06: Logout
- Tampilkan tombol logout di layout (Flux profil dropdown default).

### MVP-07: Seed Data
- Update DatabaseSeeder: admin (telo/kaspe), 2 dosen, 3 mahasiswa, 3 jadwal, beberapa pivot & submission.
