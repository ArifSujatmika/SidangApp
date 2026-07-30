<?php

namespace App\Livewire;

use App\Jobs\AnalyzeDocument;
use App\Models\DocumentAnalysis;
use App\Models\Submission;
use Livewire\Component;

class DocumentAnalyzer extends Component
{
    public Submission $submission;

    public ?DocumentAnalysis $analysis = null;

    public function mount(Submission $submission): void
    {
        $this->submission = $submission;
        $this->analysis = $submission->documentAnalysis;
    }

    public function triggerAnalysis(): void
    {
        AnalyzeDocument::dispatch($this->submission->id);

        $this->analysis = DocumentAnalysis::updateOrCreate(
            ['submission_id' => $this->submission->id],
            ['status' => 'pending', 'started_at' => null, 'completed_at' => null],
        );
    }

    public function retry(): void
    {
        $this->analysis?->delete();
        $this->analysis = null;
        $this->triggerAnalysis();
    }

    public function refreshStatus(): void
    {
        $this->analysis = $this->submission->documentAnalysis()->first();
    }

    public function render()
    {
        return view('livewire.document-analyzer');
    }
}
