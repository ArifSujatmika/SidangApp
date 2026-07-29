<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Schedule extends Model
{
    protected $fillable = [
        'nama_grup_sidang',
        'ruangan',
        'tanggal_sidang',
        'jam_mulai',
        'jam_selesai',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_sidang' => 'date',
            'jam_mulai' => 'string',
            'jam_selesai' => 'string',
        ];
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }

    public function dosens(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'schedule_dosen');
    }
}
