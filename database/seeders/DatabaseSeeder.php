<?php

namespace Database\Seeders;

use App\Models\RevisionAttachment;
use App\Models\RevisionNote;
use App\Models\Schedule;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin
        User::factory()->create([
            'name' => 'Administrator',
            'username' => 'telo',
            'email' => 'admin@example.com',
            'role' => 'admin',
            'password' => bcrypt('kaspe'),
        ]);

        // Dosen (2)
        $dosens = collect([
            ['name' => 'Dr. Budi Santoso', 'username' => '1234567890', 'email' => 'budi@example.com'],
            ['name' => 'Dr. Siti Aminah', 'username' => '1234567891', 'email' => 'siti@example.com'],
        ])->map(fn (array $data) => User::factory()->dosen()->create($data));

        // Mahasiswa (3)
        $mahasiswas = collect([
            ['name' => 'Ahmad Rizki', 'username' => '21120120120001', 'email' => 'ahmad@example.com'],
            ['name' => 'Dewi Lestari', 'username' => '21120120120002', 'email' => 'dewi@example.com'],
            ['name' => 'Rian Pratama', 'username' => '21120120120003', 'email' => 'rian@example.com'],
        ])->map(fn (array $data) => User::factory()->create($data));

        // Jadwal (3)
        $schedules = collect([
            ['nama_grup_sidang' => 'Sidang TA Gelombang 1', 'ruangan' => 'Ruang Lab Komputer 3', 'tanggal_sidang' => now()->toDateString(), 'jam_mulai' => '08:00', 'jam_selesai' => '10:00'],
            ['nama_grup_sidang' => 'Sidang TA Gelombang 2', 'ruangan' => 'Ruang Seminar A', 'tanggal_sidang' => now()->toDateString(), 'jam_mulai' => '10:00', 'jam_selesai' => '12:00'],
            ['nama_grup_sidang' => 'Sidang KP Gelombang 1', 'ruangan' => 'Ruang Lab Komputer 1', 'tanggal_sidang' => now()->addDay()->toDateString(), 'jam_mulai' => '13:00', 'jam_selesai' => '15:00'],
        ])->map(fn (array $data) => Schedule::create($data));

        // Plot dosen ke jadwal
        $schedules[0]->dosens()->attach([$dosens[0]->id, $dosens[1]->id]);
        $schedules[1]->dosens()->attach($dosens[0]->id);
        $schedules[2]->dosens()->attach($dosens[1]->id);

        // Submission mahasiswa
        $submission = Submission::create([
            'user_id' => $mahasiswas[0]->id,
            'schedule_id' => $schedules[0]->id,
            'judul_laporan' => 'Sistem Informasi Akademik Berbasis Web',
            'file_path' => 'dummy.pdf',
            'status' => 'sidang_berjalan',
        ]);

        Submission::create([
            'user_id' => $mahasiswas[1]->id,
            'schedule_id' => $schedules[1]->id,
            'judul_laporan' => 'Aplikasi Monitoring IoT',
            'file_path' => 'dummy.pdf',
            'status' => 'pending',
        ]);

        // Peserta sudah diplot admin, laporan belum dilengkapi.
        Submission::create([
            'user_id' => $mahasiswas[2]->id,
            'schedule_id' => $schedules[2]->id,
            'status' => 'pending',
        ]);

        // Revision note + attachment
        $revision = RevisionNote::create([
            'submission_id' => $submission->id,
            'dosen_id' => $dosens[0]->id,
            'catatan_revisi' => 'Perbaiki metodologi penelitian pada bab 3',
            'status_poin' => 'open',
        ]);

        RevisionAttachment::create([
            'revision_note_id' => $revision->id,
            'keterangan_mahasiswa' => 'Sudah diperbaiki sesuai saran',
            'file_path' => 'revisi/bab3.pdf',
        ]);
    }
}
