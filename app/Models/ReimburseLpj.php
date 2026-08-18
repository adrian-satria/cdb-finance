<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReimburseLpj extends Model
{
    protected $table = 'reimburse_lpj';

    protected $primaryKey = 'no_reimburse';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'no_reimburse', 'no_lpj', 'no_aju', 'total_nominal',
        'status_reimburse', 'posisi_saat_ini',
    ];

    protected $casts = [
        'total_nominal' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function lpj(): BelongsTo
    {
        return $this->belongsTo(LpjUangMuka::class, 'no_lpj', 'no_lpj');
    }

    public function pengajuan(): BelongsTo
    {
        return $this->belongsTo(PengajuanUangMuka::class, 'no_aju', 'no_aju');
    }

    public function files(): HasMany
    {
        return $this->hasMany(ReimburseLpjFile::class, 'no_reimburse', 'no_reimburse');
    }
}
