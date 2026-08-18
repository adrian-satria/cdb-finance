<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReimburseLpjFile extends Model
{
    protected $table = 'reimburse_lpj_files';

    public $timestamps = false;

    protected $fillable = ['no_reimburse', 'nama_file', 'kategori', 'tipe_file'];

    public function reimburse(): BelongsTo
    {
        return $this->belongsTo(ReimburseLpj::class, 'no_reimburse', 'no_reimburse');
    }
}
