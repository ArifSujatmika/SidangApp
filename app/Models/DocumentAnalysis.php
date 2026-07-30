<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentAnalysis extends Model
{
    protected $fillable = [
        'submission_id',
        'status',
        'summary',
        'plagiarism_score',
        'plagiarism_detail',
        'structure_score',
        'structure_detail',
        'quality_score',
        'quality_detail',
        'overall_score',
        'raw_response',
        'error_message',
        'started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'string',
            'plagiarism_score' => 'integer',
            'plagiarism_detail' => 'json',
            'structure_score' => 'integer',
            'structure_detail' => 'json',
            'quality_score' => 'integer',
            'quality_detail' => 'json',
            'overall_score' => 'integer',
            'raw_response' => 'json',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function isProcessing(): bool
    {
        return $this->status === 'processing';
    }
}
