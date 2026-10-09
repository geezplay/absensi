<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    protected $fillable = ['waktu', 'actor_id', 'aksi', 'sesi_id', 'sebelum', 'sesudah', 'alasan'];

    protected $casts = ['waktu' => 'datetime'];

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function sesi(): BelongsTo
    {
        return $this->belongsTo(Sesi::class);
    }
}
