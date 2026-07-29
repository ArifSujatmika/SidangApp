<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RevisionNote extends Model
{
    protected $fillable = [
        'submission_id',
        'dosen_id',
        'catatan_revisi',
        'status_poin',
    ];

    protected function casts(): array
    {
        return [
            'status_poin' => 'string',
        ];
    }

    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
    }

    public function dosen(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dosen_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(RevisionAttachment::class);
    }
}
