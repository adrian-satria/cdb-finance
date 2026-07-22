<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    protected $table = 'area';

    protected $primaryKey = 'kode_area';

    public $incrementing = false;

    public $timestamps = false;
}
