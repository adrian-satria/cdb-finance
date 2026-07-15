<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SumberDana extends Model
{
    protected $table = 'sumber_dana';
    // Sesuaikan primary key jika di SQL bukan 'id' (misal: 'id_sumber_dana')
    protected $primaryKey = 'id_bank_kas'; 
    public $timestamps = false;
    protected $guarded = [];
}