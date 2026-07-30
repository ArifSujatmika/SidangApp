# AI Document Analysis — Design Spec

**Date**: 2026-07-30
**Project**: SidangApp
**Phase**: AI Feature

## Overview

Fitur analisa dokumen laporan sidang menggunakan AI (LLM). Mahasiswa upload laporan PDF → admin/dosen manually trigger analisa → AI generate ringkasan, cek plagiarisme, evaluasi struktur & kualitas.

## Requirements

### Functional

- **FR-AI-01**: Admin dan dosen (assigned) bisa trigger analisa AI pada submission yang sudah punya file PDF
- **FR-AI-02**: AI generate ringkasan laporan (200-300 kata)
- **FR-AI-03**: AI estimasi skor plagiarisme (0-100) + detail
- **FR-AI-04**: AI evaluasi struktur dokumen (0-100) + detail
- **FR-AI-05**: AI evaluasi kualitas argumen/metodologi (0-100) + saran perbaikan
- **FR-AI-06**: Skor overall (rata-rata dari 3 skor)
- **FR-AI-07**: Hasil analisa disimpan permanen, bisa dilihat kembali
- **FR-AI-08**: Progress real-time via Livewire polling
- **FR-AI-09**: Retry otomatis 1x jika gagal
- **FR-AI-10**: Gate `analyze-submission` membatasi akses

### Non-Functional

- **NFR-AI-01**: OpenAI-compatible API (support OpenAI, Azure, Ollama, dst)
- **NFR-AI-02**: Config via `.env` + `config/ai-analysis.php`
- **NFR-AI-03**: Queue-based processing, non-blocking
- **NFR-AI-04**: Handle PDF hasil scan (teks tidak terbaca)
- **NFR-AI-05**: Handle PDF besar (truncate text jika melebihi token limit)

## Architecture

### Data Flow

```
Admin/Dosen → klik "Analisa" di halaman submission
     ↓
Livewire component trigger
     ↓
dispatch AnalyzeDocument job
     ↓
Job: extract text dari PDF → kirim ke LLM API
     ↓
Simpan hasil ke table document_analyses
     ↓
Livewire polling → tampilkan ringkasan + skor + saran
```

### New Components

| Component | Path | Type |
|---|---|---|
| DocumentAnalysis | `app/Models/DocumentAnalysis.php` | Model |
| AnalyzeDocument | `app/Jobs/AnalyzeDocument.php` | Queue Job |
| DocumentAnalyzer | `app/Livewire/DocumentAnalyzer.php` | Livewire Component |
| AiAnalysisService | `app/Services/AiAnalysisService.php` | Service |
| config | `config/ai-analysis.php` | Config |
| migration | `database/migrations/..._create_document_analyses_table.php` | Migration |
| view | `resources/views/livewire/document-analyzer.blade.php` | Blade |
| view | `resources/views/analysis/show.blade.php` | Blade |
| route | `GET /submissions/{submission}/analysis` | Route |

### New Packages

- `openai-php/client` — OpenAI-compatible PHP SDK
- `smalot/pdfparser` — Pure PHP PDF text extraction

### Database

**Table: `document_analyses`**

| Column | Type | Notes |
|---|---|---|
| id | bigint PK | auto-increment |
| submission_id | bigint FK | references submissions(id) cascade |
| status | string | pending / processing / completed / failed |
| summary | text nullable | Ringkasan AI |
| plagiarism_score | int nullable | 0-100 |
| plagiarism_detail | json nullable | Detail kemiripan |
| structure_score | int nullable | 0-100 |
| structure_detail | json nullable | Evaluasi bab, format |
| quality_score | int nullable | 0-100 |
| quality_detail | json nullable | Saran perbaikan |
| overall_score | int nullable | Rata-rata 3 skor |
| raw_response | json nullable | Response mentah LLM |
| error_message | text nullable | Pesan error jika gagal |
| started_at | timestamp nullable | Waktu mulai job |
| completed_at | timestamp nullable | Waktu selesai job |
| timestamps | timestamp | created_at, updated_at |

Unique constraint: `submission_id` (satu record per submission, upsert on retry)

### Config

**`config/ai-analysis.php`**:
```php
return [
    'base_url' => env('AI_API_BASE_URL', 'https://api.openai.com/v1'),
    'api_key' => env('AI_API_KEY'),
    'model' => env('AI_MODEL', 'gpt-4o'),
    'max_tokens' => env('AI_MAX_TOKENS', 4096),
    'timeout' => env('AI_TIMEOUT', 120),
    'max_text_length' => env('AI_MAX_TEXT_LENGTH', 50000),
    'retry_attempts' => 1,
];
```

### Service Layer

**`AiAnalysisService`**:
- `extractText(string $pdfPath): string` — extract text dari PDF via smalot/pdfparser
- `analyze(string $text): array` — kirim text ke LLM, return structured array
- `buildPrompt(string $text): string` — construct prompt untuk ringkasan + plagiarisme + struktur + kualitas
- `parseResponse(string $json): array` — parse JSON response dari LLM, validasi struktur

Prompt strategy: single API call, request JSON structured output. Prompt mengandung instruksi untuk:
- Ringkasan 200-300 kata dalam Bahasa Indonesia
- Estimasi skor plagiarisme (0-100) + alasan
- Evaluasi struktur (kelengkapan bab, format penulisan) → skor 0-100 + detail
- Evaluasi kualitas (argumen, metodologi, kebaruan) → skor 0-100 + saran

### Queue Job

**`AnalyzeDocument`**:
- Menerima `submission_id`
- Set status → processing, simpan `started_at`
- Baca file PDF dari storage
- Panggil `AiAnalysisService::analyze()`
- Simpan hasil, set status → completed, simpan `completed_at`
- On failure: set status → failed, simpan `error_message`
- Retry: `public int $tries = 2`

### Livewire Component

**`DocumentAnalyzer`**:
- Properties: `$submission`, `$analysis`, `$status`
- `mount(Submission $submission)` — load existing analysis atau null
- `triggerAnalysis()` — dispatch `AnalyzeDocument` job, set status pending
- `pollStatus()` — polling setiap 2 detik, update `$analysis` dari DB
- `retry()` — re-dispatch job
- Rendering: progress bar saat processing, hasil saat completed, error saat failed

### Routes

```php
Route::get('/submissions/{submission}/analysis', [AnalysisController::class, 'show'])
    ->middleware('can:analyze-submission,submission')
    ->name('submissions.analysis');
```

### Gate

**`analyze-submission`**:
- `admin` → true
- `dosen` → true if assigned to submission's schedule (via schedule_dosen pivot)
- `mahasiswa` → false

### UI

**Trigger button**: Di halaman `submissions.show`, tombol "Analisa AI" (visible untuk admin + dosen assigned). Disabled jika submission belum punya file.

**Halaman analisa** (`/submissions/{id}/analysis`):
- Status bar: "Menunggu..." / "Menganalisa..." / "Selesai" / "Gagal"
- Progress: spinner animasi selama processing
- Hasil (completed):
  - Card: Ringkasan (teks paragraf)
  - Card: Skor Plagiarisme — badge 0-100 (merah >70, kuning 40-70, hijau <40) + detail dropdown
  - Card: Skor Struktur — badge 0-100 + detail
  - Card: Skor Kualitas — badge 0-100 + saran perbaikan (list)
  - Card: Skor Overall — badge besar di atas, rata-rata 3 skor
- Error (failed):
  - Pesan error + tombol "Coba Lagi"
  - Error spesifik: "Teks tidak terbaca (PDF hasil scan)" / "Gagal menghubungi AI" / "Timeout"

### Error Handling

| Scenario | Handling |
|---|---|
| PDF hasil scan (no text) | Set status failed, error: "Teks tidak terbaca" |
| PDF terlalu besar | Truncate text to `max_text_length` chars |
| LLM API down/timeout | Set status failed, retry 1x otomatis, tampilkan error |
| LLM response invalid JSON | Set status failed, simpan raw_response untuk debug |
| Submission tidak punya file | Tombol analisa disabled, 422 jika dipaksa |
| Multiple concurrent trigger | Upsert — replace existing record |

### Testing

**Feature tests** (`tests/Feature/DocumentAnalysisTest.php`):
- Admin can trigger analysis
- Dosen assigned can trigger analysis
- Mahasiswa cannot trigger analysis (403)
- Dosen not assigned cannot trigger (403)
- Cannot analyze submission without file (422)
- Job creates DocumentAnalysis record
- Polling shows correct status transitions
- Completed analysis shows results
- Failed analysis shows error + retry button
- Retry replaces existing analysis

**Unit tests** (`tests/Unit/AiAnalysisServiceTest.php`):
- Extract text from valid PDF
- Handle empty PDF (scan)
- Build correct prompt structure
- Parse valid LLM JSON response
- Handle invalid LLM JSON response
- Handle API timeout

## Out of Scope

- Auto-analysis on upload (manual trigger only)
- Batch analysis (satu per satu)
- Export analysis to PDF/print
- History comparison between analyses
- Plagiarism check against real database (LLM estimation only)
- Notifikasi hasil analisa
- Mahasiswa melihat hasil analisa (admin/dosen only)

## Dependencies

- `openai-php/client` — OpenAI-compatible API SDK
- `smalot/pdfparser` — PDF text extraction
- Queue worker (database driver, sudah ada jobs table)
- Livewire 4 (sudah terinstall)
- Flux 2 (sudah terinstall)

## Fixes from Self-Review

### Missing Component
AnalysisController listed in Routes section but not in New Components table. Added: `app/Http/Controllers/AnalysisController.php` — controller with `show()` method that renders the dedicated analysis page with the Livewire component.
