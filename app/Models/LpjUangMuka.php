<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LpjUangMuka extends Model
{
    protected $table = 'lpj_uang_muka';

    protected $primaryKey = 'no_lpj';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'no_lpj', 'no_aju', 'id_pelaksana', 'tanggal', 'total_realisasi',
        'selisih', 'status_lpj', 'posisi_saat_ini', 'keterangan_checker',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'total_realisasi' => 'decimal:2',
        'selisih' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function pengajuan(): BelongsTo
    {
        return $this->belongsTo(PengajuanUangMuka::class, 'no_aju', 'no_aju');
    }

    public function pelaksana(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_pelaksana', 'id_user');
    }

    public function details(): HasMany
    {
        return $this->hasMany(LpjUangMukaDetail::class, 'no_lpj', 'no_lpj');
    }

    public function files(): HasMany
    {
        return $this->hasMany(LpjUangMukaFile::class, 'no_lpj', 'no_lpj');
    }

    public function reimburse(): HasMany
    {
        return $this->hasMany(ReimburseLpj::class, 'no_lpj', 'no_lpj');
    }

    public function needsRefund(): bool
    {
        return bccomp((string) $this->selisih, '0', 2) === 1;
    }

    public function needsReimburse(): bool
    {
        return bccomp((string) $this->selisih, '0', 2) === -1;
    }
}
