<?php

namespace App\Jobs;

use App\Models\DocumentAnalysis;
use App\Models\Submission;
use App\Services\AiAnalysisService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;

class AnalyzeDocument implements ShouldQueue
{
    use Queueable;

    public int $tries = 2;

    public int $timeout = 180;

    public function __construct(
        private readonly int $submissionId,
    ) {}

    public function handle(AiAnalysisService $service): void
    {
        $submission = Submission::findOrFail($this->submissionId);

        if (! $submission->file_path) {
            throw new \RuntimeException('Submission has no file attached');
        }

        $filePath = Storage::disk('local')->path($submission->file_path);

        $analysis = DocumentAnalysis::updateOrCreate(
            ['submission_id' => $submission->id],
            [
                'status' => 'processing',
                'started_at' => now(),
            ],
        );

        try {
            $text = $service->extractText($filePath);

            if (trim($text) === '') {
                throw new \RuntimeException('Teks tidak terbaca (PDF hasil scan)');
            }

            $result = $service->analyze($text);

            $analysis->update([
                ...$result,
                'status' => 'completed',
                'completed_at' => now(),
                'error_message' => null,
            ]);
        } catch (\Exception $e) {
            $analysis->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'completed_at' => now(),
            ]);

            throw $e;
        }
    }
}
