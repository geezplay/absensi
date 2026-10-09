<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sesi extends Model
{
    protected $table = 'sesi';

    protected $fillable = [
        'kelas_id',
        'mapel',
        'tanggal',
        'jam_mulai',
        'jam_selesai',
        'topik',
        'status',
        'created_by',
    ];

    protected $casts = ['tanggal' => 'date:Y-m-d'];

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }

    public function absensi(): HasMany
    {
        return $this->hasMany(Absensi::class);
    }

    public function isFinal(): bool
    {
        return $this->status === 'final';
    }
}
