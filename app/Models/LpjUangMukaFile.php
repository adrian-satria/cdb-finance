<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LpjUangMukaFile extends Model
{
    protected $table = 'lpj_uang_muka_files';

    public $timestamps = false;

    protected $fillable = ['no_lpj', 'nama_file', 'kategori', 'tipe_file'];

    public function lpj(): BelongsTo
    {
        return $this->belongsTo(LpjUangMuka::class, 'no_lpj', 'no_lpj');
    }
}
