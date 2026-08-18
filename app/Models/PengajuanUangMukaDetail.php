<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PengajuanUangMukaDetail extends Model
{
    protected $table = 'pengajuan_uang_muka_detail';

    public $timestamps = false;

    protected $fillable = ['no_aju', 'keterangan', 'kode_budget', 'nominal'];

    protected $casts = ['nominal' => 'decimal:2'];

    public function pengajuan(): BelongsTo
    {
        return $this->belongsTo(PengajuanUangMuka::class, 'no_aju', 'no_aju');
    }
}
