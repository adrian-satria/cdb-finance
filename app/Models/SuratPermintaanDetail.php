<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuratPermintaanDetail extends Model
{
    protected $table = 'surat_permintaan_detail';

    public $timestamps = false;

    protected $fillable = [
        'no_surat',
        'keterangan',
        'kode_budget',
        'nominal',
    ];

    public function suratPermintaan()
    {
        return $this->belongsTo(SuratPermintaan::class, 'no_surat', 'no_surat');
    }
}
