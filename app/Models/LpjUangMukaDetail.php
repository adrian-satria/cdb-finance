<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LpjUangMukaDetail extends Model
{
    protected $table = 'lpj_uang_muka_detail';

    public $timestamps = false;

    protected $fillable = ['no_lpj', 'keterangan', 'kode_budget', 'nominal'];

    protected $casts = ['nominal' => 'decimal:2'];

    public function lpj(): BelongsTo
    {
        return $this->belongsTo(LpjUangMuka::class, 'no_lpj', 'no_lpj');
    }
}
