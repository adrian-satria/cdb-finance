<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PengajuanUangMukaFile extends Model
{
    protected $table = 'pengajuan_uang_muka_files';

    public $timestamps = false;

    protected $fillable = ['no_aju', 'nama_file', 'kategori', 'tipe_file'];

    public function pengajuan(): BelongsTo
    {
        return $this->belongsTo(PengajuanUangMuka::class, 'no_aju', 'no_aju');
    }
}
