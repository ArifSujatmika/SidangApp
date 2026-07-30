<?php

use App\Jobs\AnalyzeDocument;
use App\Models\DocumentAnalysis;
use App\Models\Schedule;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Support\Facades\Queue;

function createSubmissionWithFile(User $mahasiswa, ?Schedule $schedule = null): Submission
{
    $schedule ??= Schedule::create([
        'nama_grup_sidang' => 'Sidang TA Test',
        'ruangan' => 'Ruang Test',
        'tanggal_sidang' => now()->toDateString(),
        'jam_mulai' => '08:00',
        'jam_selesai' => '10:00',
    ]);

    return Submission::create([
        'user_id' => $mahasiswa->id,
        'schedule_id' => $schedule->id,
        'judul_laporan' => 'Laporan Test',
        'file_path' => 'submissions/test.pdf',
        'status' => 'sidang_berjalan',
    ]);
}

test('admin can access analysis page', function () {
    $admin = User::factory()->admin()->create(['username' => 'admintest']);
    $mhs = User::factory()->create(['username' => '12345']);
    $submission = createSubmissionWithFile($mhs);

    $this->actingAs($admin)
        ->get(route('submissions.analysis', $submission))
        ->assertOk();
});

test('assigned dosen can access analysis page', function () {
    $dosen = User::factory()->dosen()->create(['username' => '54321']);
    $mhs = User::factory()->create(['username' => '12345']);
    $schedule = Schedule::create([
        'nama_grup_sidang' => 'Sidang TA Test',
        'ruangan' => 'Ruang Test',
        'tanggal_sidang' => now()->toDateString(),
        'jam_mulai' => '08:00',
        'jam_selesai' => '10:00',
    ]);
    $schedule->dosens()->attach($dosen->id);
    $submission = createSubmissionWithFile($mhs, $schedule);

    $this->actingAs($dosen)
        ->get(route('submissions.analysis', $submission))
        ->assertOk();
});

test('unassigned dosen cannot access analysis page', function () {
    $dosen = User::factory()->dosen()->create(['username' => '54321']);
    $mhs = User::factory()->create(['username' => '12345']);
    $submission = createSubmissionWithFile($mhs);

    $this->actingAs($dosen)
        ->get(route('submissions.analysis', $submission))
        ->assertForbidden();
});

test('mahasiswa cannot access analysis page', function () {
    $mhs = User::factory()->create(['username' => '12345']);
    $submission = createSubmissionWithFile($mhs);

    $this->actingAs($mhs)
        ->get(route('submissions.analysis', $submission))
        ->assertForbidden();
});

test('analysis page shows trigger button when no analysis exists', function () {
    $admin = User::factory()->admin()->create(['username' => 'admintest']);
    $mhs = User::factory()->create(['username' => '12345']);
    $submission = createSubmissionWithFile($mhs);

    $this->actingAs($admin)
        ->get(route('submissions.analysis', $submission))
        ->assertSee('Mulai Analisa AI');
});

test('analysis page shows completed results', function () {
    $admin = User::factory()->admin()->create(['username' => 'admintest']);
    $mhs = User::factory()->create(['username' => '12345']);
    $submission = createSubmissionWithFile($mhs);

    DocumentAnalysis::create([
        'submission_id' => $submission->id,
        'status' => 'completed',
        'summary' => 'Ini adalah ringkasan laporan test.',
        'plagiarism_score' => 30,
        'plagiarism_detail' => 'Konten cukup orisinal',
        'structure_score' => 80,
        'structure_detail' => 'Struktur lengkap',
        'quality_score' => 75,
        'quality_detail' => 'Kualitas baik',
        'overall_score' => 62,
        'completed_at' => now(),
    ]);

    $this->actingAs($admin)
        ->get(route('submissions.analysis', $submission))
        ->assertSee('Skor Keseluruhan')
        ->assertSee('62')
        ->assertSee('Ini adalah ringkasan laporan test.');
});

test('analysis page shows failed status with retry button', function () {
    $admin = User::factory()->admin()->create(['username' => 'admintest']);
    $mhs = User::factory()->create(['username' => '12345']);
    $submission = createSubmissionWithFile($mhs);

    DocumentAnalysis::create([
        'submission_id' => $submission->id,
        'status' => 'failed',
        'error_message' => 'Teks tidak terbaca (PDF hasil scan)',
        'completed_at' => now(),
    ]);

    $this->actingAs($admin)
        ->get(route('submissions.analysis', $submission))
        ->assertSee('Analisa Gagal')
        ->assertSee('Coba Lagi');
});

test('document analyzer triggers job on triggerAnalysis', function () {
    Queue::fake();

    $admin = User::factory()->admin()->create(['username' => 'admintest']);
    $mhs = User::factory()->create(['username' => '12345']);
    $submission = createSubmissionWithFile($mhs);

    Livewire::actingAs($admin)
        ->test('document-analyzer', ['submission' => $submission])
        ->call('triggerAnalysis')
        ->assertHasNoErrors();

    Queue::assertPushed(AnalyzeDocument::class);
});

test('document analyzer shows pending status after trigger', function () {
    Queue::fake();

    $admin = User::factory()->admin()->create(['username' => 'admintest']);
    $mhs = User::factory()->create(['username' => '12345']);
    $submission = createSubmissionWithFile($mhs);

    Livewire::actingAs($admin)
        ->test('document-analyzer', ['submission' => $submission])
        ->call('triggerAnalysis')
        ->assertSee('Menunggu analisa');
});

test('retry deletes existing analysis and re-dispatches', function () {
    Queue::fake();

    $admin = User::factory()->admin()->create(['username' => 'admintest']);
    $mhs = User::factory()->create(['username' => '12345']);
    $submission = createSubmissionWithFile($mhs);

    $analysis = DocumentAnalysis::create([
        'submission_id' => $submission->id,
        'status' => 'failed',
        'error_message' => 'API timeout',
        'completed_at' => now(),
    ]);

    Livewire::actingAs($admin)
        ->test('document-analyzer', ['submission' => $submission])
        ->call('retry')
        ->assertHasNoErrors();

    expect(DocumentAnalysis::where('submission_id', $submission->id)->count())->toBe(1);

    Queue::assertPushed(AnalyzeDocument::class);
});

test('analyze submission gate denies unassigned dosen', function () {
    $dosen = User::factory()->dosen()->create(['username' => '54321']);
    $mhs = User::factory()->create(['username' => '12345']);
    $submission = createSubmissionWithFile($mhs);

    expect(Gate::forUser($dosen)->allows('analyze-submission', $submission))->toBeFalse();
});

test('analyze submission gate allows assigned dosen', function () {
    $dosen = User::factory()->dosen()->create(['username' => '54321']);
    $mhs = User::factory()->create(['username' => '12345']);
    $schedule = Schedule::create([
        'nama_grup_sidang' => 'Sidang TA Test',
        'ruangan' => 'Ruang Test',
        'tanggal_sidang' => now()->toDateString(),
        'jam_mulai' => '08:00',
        'jam_selesai' => '10:00',
    ]);
    $schedule->dosens()->attach($dosen->id);
    $submission = createSubmissionWithFile($mhs, $schedule);

    expect(Gate::forUser($dosen)->allows('analyze-submission', $submission))->toBeTrue();
});
