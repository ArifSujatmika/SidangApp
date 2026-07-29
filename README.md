# SIMSIDANG

Sistem Manajemen Sidang Akademik untuk mengelola plotting jadwal, dosen penguji, mahasiswa peserta, laporan sidang, dan alur revisi.

## Fitur Utama

### Admin
- Kelola akun admin, dosen, dan mahasiswa.
- Buat dan ubah jadwal sidang (grup, ruangan, tanggal, jam).
- Plot dosen penguji dan mahasiswa peserta pada jadwal yang sama.
- Cari mahasiswa berdasarkan nama atau NIM, pilih satu per satu, atau pilih semua.
- Ubah status laporan: `pending` → `sidang_berjalan` → `revisi` → `selesai`.

### Dosen
- Melihat jadwal hari ini yang ditugaskan kepada dirinya.
- Melihat mahasiswa peserta pada jadwal tersebut.
- Mengunduh laporan secara aman.
- Memberikan catatan revisi per poin.
- Memeriksa tanggapan mahasiswa dan menandai poin sebagai selesai.

### Mahasiswa
- Melihat jadwal sidang yang sudah ditetapkan admin.
- Melengkapi judul laporan dan mengunggah PDF maksimal 10 MB.
- Melihat catatan revisi dosen.
- Mengirim tanggapan dan attachment (`pdf`, `docx`, `jpeg`, `png`, maksimal 5 MB).

## Alur Penggunaan

1. Admin membuat akun dosen dan mahasiswa.
2. Admin membuat jadwal, memilih dosen penguji, dan memilih mahasiswa peserta.
3. Sistem membuat submission `pending` untuk setiap mahasiswa yang diplot.
4. Mahasiswa login lalu memilih **Lengkapi Laporan** untuk mengisi judul dan mengunggah PDF.
5. Dosen membuka dashboard, mengunduh laporan, lalu memberikan poin revisi.
6. Mahasiswa menanggapi setiap poin revisi dan mengunggah bukti perbaikan bila perlu.
7. Dosen menandai poin yang disetujui sebagai `resolved`.
8. Admin memperbarui status submission hingga `selesai`.

Peserta yang sudah mengunggah laporan tidak dapat dikeluarkan hanya dengan membatalkan pilihan pada form jadwal. Sistem mempertahankannya dan menampilkan peringatan agar file tidak hilang.

## Tech Stack

- PHP 8.3+
- Laravel 13
- Livewire 4
- Flux 2 (free)
- Tailwind CSS 4
- Laravel Fortify + Passkeys
- Pest 4
- MySQL untuk aplikasi dan test DB sesuai `phpunit.xml`

## Instalasi

Persyaratan: PHP, Composer, Node.js/npm, dan MySQL.

```bash
composer run setup
```

Script tersebut menjalankan instalasi dependency, membuat `.env` bila belum ada, membuat application key, menjalankan migration, memasang dependency frontend, dan membangun asset.

Pastikan konfigurasi database di `.env` sesuai environment lokal:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=simsidang
DB_USERNAME=root
DB_PASSWORD=
```

Untuk mengisi data contoh dari awal:

```bash
php artisan migrate:fresh --seed
```

> Perintah tersebut menghapus seluruh data pada database yang aktif.

## Menjalankan Aplikasi

Semua service development (server Laravel, queue listener, Vite):

```bash
composer run dev
```

Atau jalankan frontend saja:

```bash
npm run dev
```

Halaman `/` otomatis mengarahkan guest ke `/login`. Pengguna yang sudah login diarahkan ke dashboard sesuai role.

## Akun Seed

| Role | Username | Password |
|---|---|---|
| Admin | `telo` | `kaspe` |
| Dosen | `1234567890` | `password` |
| Dosen | `1234567891` | `password` |
| Mahasiswa | `21120120120001` | `password` |
| Mahasiswa | `21120120120002` | `password` |
| Mahasiswa | `21120120120003` | `password` |

Akun seed hanya untuk development/demo. Ganti password sebelum memakai data serupa di environment publik.

## Verifikasi

```bash
# Semua test
php artisan test --compact

# Test terfokus
php artisan test --compact --filter=ScheduleParticipantTest

# Format PHP
vendor/bin/pint --format agent

# Build frontend production
npm run build
```

Test menggunakan database MySQL `simsidang_test` sesuai `phpunit.xml`. Database tersebut harus tersedia sebelum menjalankan suite.

## Struktur Dokumentasi

- `docs/prd.md` — kebutuhan produk dan status MVP.
- `docs/design.md` — design system Flux/Tailwind bertema Teal/Cyan.
- `docs/phase.md` — checklist fase pengembangan.
- `docs/progress.md` — ringkasan progres terbaru.
- `docs/memory.md` — catatan teknis sesi agent.
- `AGENTS.md` — instruksi kerja khusus repository untuk OpenCode.
