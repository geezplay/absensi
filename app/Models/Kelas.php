<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kelas extends Model
{
    protected $table = 'kelas';

    protected $fillable = [
        'owner_guru_id',
        'nama',
        'tingkat',
        'tahun_ajaran',
        'mapel',
        'wali_kelas',
        'keterangan',
        'status',
    ];

    public function guru(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_guru_id');
    }

    public function siswa(): HasMany
    {
        return $this->hasMany(Siswa::class);
    }

    public function sesi(): HasMany
    {
        return $this->hasMany(Sesi::class);
    }
}
