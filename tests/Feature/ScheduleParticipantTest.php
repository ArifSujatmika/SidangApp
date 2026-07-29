<?php

use App\Models\Schedule;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

function schedulePayload(array $overrides = []): array
{
    return array_merge([
        'nama_grup_sidang' => 'Sidang TA Gelombang 1',
        'ruangan' => 'Ruang A',
        'tanggal_sidang' => now()->addDay()->toDateString(),
        'jam_mulai' => '08:00',
        'jam_selesai' => '10:00',
    ], $overrides);
}

test('admin can assign mahasiswa participants while creating a schedule', function () {
    $admin = User::factory()->admin()->create();
    $mahasiswa = User::factory()->create();

    $this->actingAs($admin)
        ->post(route('admin.schedules.store'), schedulePayload([
            'peserta_ids' => [$mahasiswa->id],
        ]))
        ->assertRedirect(route('admin.schedules.index'));

    $submission = Submission::where('user_id', $mahasiswa->id)->firstOrFail();

    expect($submission->judul_laporan)->toBeNull()
        ->and($submission->file_path)->toBeNull()
        ->and($submission->status)->toBe('pending');
});

test('admin can remove a participant that has not uploaded a report', function () {
    $admin = User::factory()->admin()->create();
    $mahasiswa = User::factory()->create();
    $schedule = Schedule::create(schedulePayload());
    $submission = Submission::create([
        'user_id' => $mahasiswa->id,
        'schedule_id' => $schedule->id,
        'status' => 'pending',
    ]);

    $this->actingAs($admin)
        ->put(route('admin.schedules.update', $schedule), schedulePayload())
        ->assertRedirect(route('admin.schedules.index'));

    $this->assertDatabaseMissing('submissions', ['id' => $submission->id]);
});

test('admin cannot remove a participant that has uploaded a report', function () {
    $admin = User::factory()->admin()->create();
    $mahasiswa = User::factory()->create();
    $schedule = Schedule::create(schedulePayload());
    $submission = Submission::create([
        'user_id' => $mahasiswa->id,
        'schedule_id' => $schedule->id,
        'judul_laporan' => 'Laporan TA',
        'file_path' => 'submissions/report.pdf',
        'status' => 'pending',
    ]);

    $this->actingAs($admin)
        ->put(route('admin.schedules.update', $schedule), schedulePayload())
        ->assertRedirect(route('admin.schedules.index'))
        ->assertSessionHas('warning');

    $this->assertDatabaseHas('submissions', ['id' => $submission->id]);
});

test('mahasiswa can complete an assigned submission placeholder', function () {
    Storage::fake('local');

    $mahasiswa = User::factory()->create();
    $schedule = Schedule::create(schedulePayload());
    $submission = Submission::create([
        'user_id' => $mahasiswa->id,
        'schedule_id' => $schedule->id,
        'status' => 'pending',
    ]);

    $this->actingAs($mahasiswa)
        ->put(route('submissions.update', $submission), [
            'judul_laporan' => 'Sistem Informasi Sidang',
            'file' => UploadedFile::fake()->create('laporan.pdf', 500, 'application/pdf'),
        ])
        ->assertRedirect(route('dashboard'));

    $submission->refresh();

    expect($submission->judul_laporan)->toBe('Sistem Informasi Sidang')
        ->and($submission->file_path)->not->toBeNull();

    Storage::disk('local')->assertExists($submission->file_path);
});
