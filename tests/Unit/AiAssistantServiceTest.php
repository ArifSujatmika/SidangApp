<?php

use App\Models\Schedule;
use App\Models\Submission;
use App\Models\User;
use App\Services\AiAssistantService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('getSystemContext returns all required keys', function () {
    User::factory()->admin()->create(['username' => 'admin1']);
    User::factory()->dosen()->create(['username' => 'dosen1']);
    User::factory()->dosen()->create(['username' => 'dosen2']);
    User::factory()->create(['username' => 'mhs1']);

    $service = new AiAssistantService;
    $context = (new ReflectionMethod($service, 'getSystemContext'))->invoke($service);

    expect($context)->toHaveKeys([
        'total_users', 'total_admin', 'total_dosen', 'total_mahasiswa',
        'total_schedules', 'schedules_today', 'schedules_this_week',
        'total_submissions', 'submissions_pending', 'submissions_sidang_berjalan',
        'submissions_revisi', 'submissions_selesai',
        'total_revision_notes', 'revision_notes_open', 'revision_notes_resolved',
        'total_analyses', 'avg_overall_score',
    ]);

    expect($context)
        ->total_users->toBe(4)
        ->total_admin->toBe(1)
        ->total_dosen->toBe(2)
        ->total_mahasiswa->toBe(1)
        ->total_schedules->toBe(0)
        ->schedules_today->toBe(0)
        ->schedules_this_week->toBe(0)
        ->total_submissions->toBe(0)
        ->submissions_pending->toBe(0)
        ->submissions_sidang_berjalan->toBe(0)
        ->submissions_revisi->toBe(0)
        ->submissions_selesai->toBe(0)
        ->total_revision_notes->toBe(0)
        ->revision_notes_open->toBe(0)
        ->revision_notes_resolved->toBe(0)
        ->total_analyses->toBe(0)
        ->avg_overall_score->toBe(0.0);
});

test('getSystemContext counts schedules correctly', function () {
    $admin = User::factory()->admin()->create(['username' => 'admin1']);

    Schedule::create([
        'nama_grup_sidang' => 'Sidang Hari Ini',
        'ruangan' => 'Ruang 1',
        'tanggal_sidang' => now()->toDateString(),
        'jam_mulai' => '08:00',
        'jam_selesai' => '10:00',
    ]);

    Schedule::create([
        'nama_grup_sidang' => 'Sidang Besok',
        'ruangan' => 'Ruang 2',
        'tanggal_sidang' => now()->addDay()->toDateString(),
        'jam_mulai' => '08:00',
        'jam_selesai' => '10:00',
    ]);

    $service = new AiAssistantService;
    $context = (new ReflectionMethod($service, 'getSystemContext'))->invoke($service);

    expect($context)
        ->total_schedules->toBe(2)
        ->schedules_today->toBe(1);
    expect($context['schedules_this_week'])->toBeGreaterThanOrEqual(2);
});

test('getSystemContext counts submissions by status', function () {
    $admin = User::factory()->admin()->create(['username' => 'admin1']);
    $mhs = User::factory()->create(['username' => 'mhs1']);
    $schedule = Schedule::create([
        'nama_grup_sidang' => 'Test',
        'ruangan' => 'Ruang 1',
        'tanggal_sidang' => now()->toDateString(),
        'jam_mulai' => '08:00',
        'jam_selesai' => '10:00',
    ]);

    Submission::create(['user_id' => $mhs->id, 'schedule_id' => $schedule->id, 'status' => 'pending']);
    Submission::create(['user_id' => $mhs->id, 'schedule_id' => $schedule->id, 'status' => 'sidang_berjalan']);
    Submission::create(['user_id' => $mhs->id, 'schedule_id' => $schedule->id, 'status' => 'revisi']);

    $service = new AiAssistantService;
    $context = (new ReflectionMethod($service, 'getSystemContext'))->invoke($service);

    expect($context)
        ->total_submissions->toBe(3)
        ->submissions_pending->toBe(1)
        ->submissions_sidang_berjalan->toBe(1)
        ->submissions_revisi->toBe(1)
        ->submissions_selesai->toBe(0);
});

test('buildSystemPrompt contains context data', function () {
    $admin = User::factory()->admin()->create(['username' => 'admin1']);

    $service = new AiAssistantService;
    $context = (new ReflectionMethod($service, 'getSystemContext'))->invoke($service);
    $prompt = (new ReflectionMethod($service, 'buildSystemPrompt'))->invoke($service, $context);

    expect($prompt)
        ->toContain('SIMSIDANG')
        ->toContain('asisten virtual')
        ->toContain('1');
});
