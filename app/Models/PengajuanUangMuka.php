<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PengajuanUangMuka extends Model
{
    protected $table = 'pengajuan_uang_muka';

    protected $primaryKey = 'no_aju';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'no_aju', 'tanggal', 'id_pengaju', 'kode_project', 'kode_area',
        'keterangan', 'total_nominal', 'sisa_lpj', 'status_um',
        'posisi_saat_ini', 'tanggal_jatuh_tempo', 'tanggal_cair', 'keterangan_checker',
        'refund_jumlah', 'refund_tanggal', 'refund_bukti',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'tanggal_jatuh_tempo' => 'date',
        'tanggal_cair' => 'date',
        'total_nominal' => 'decimal:2',
        'sisa_lpj' => 'decimal:2',
        'refund_jumlah' => 'decimal:2',
        'refund_tanggal' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function pengaju(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_pengaju', 'id_user');
    }

    public function details(): HasMany
    {
        return $this->hasMany(PengajuanUangMukaDetail::class, 'no_aju', 'no_aju');
    }

    public function files(): HasMany
    {
        return $this->hasMany(PengajuanUangMukaFile::class, 'no_aju', 'no_aju');
    }

    public function lpjs(): HasMany
    {
        return $this->hasMany(LpjUangMuka::class, 'no_aju', 'no_aju');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'kode_project', 'kode_project');
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class, 'kode_area', 'kode_area');
    }

    public function isOverdue(): bool
    {
        return $this->tanggal_jatuh_tempo
            && $this->status_um === 'Cair'
            && $this->sisa_lpj > 0
            && $this->tanggal_jatuh_tempo->isPast();
    }

    /**
     * Umur kas bon (hari): sejak dicairkan sampai settle (refund) atau hari ini.
     * Null bila belum cair.
     */
    public function getUmurKasBonAttribute(): ?int
    {
        if (! $this->tanggal_cair) {
            return null;
        }

        $selesai = $this->refund_tanggal ?? now();

        return $this->tanggal_cair->diffInDays($selesai);
    }
}
