<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuratPermintaanFiles extends Model
{
    protected $table = 'surat_permintaan_files';
    public $timestamps = false;

    protected $fillable = [
        'no_surat',
        'nama_file',
        'kategori',
        'tipe_file',
    ];

    public function suratPermintaan()
    {
        return $this->belongsTo(SuratPermintaan::class, 'no_surat', 'no_surat');
    }
}
