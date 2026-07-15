<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuratPermintaan extends Model
{
   protected $table = 'surat_permintaan';
    
    // Beritahu Laravel bahwa Primary Key-nya bukan 'id'
    protected $primaryKey = 'no_surat';
    
    // Beritahu bahwa PK ini bukan angka yang bertambah otomatis (auto-increment)
    public $incrementing = false;
    
    // Beritahu tipe data PK-nya adalah string
    protected $keyType = 'string';

    protected $guarded = [];
    
    // Nonaktifkan timestamps otomatis Laravel jika kamu hanya pakai created_at dari SQL
    public $timestamps = false;
}
