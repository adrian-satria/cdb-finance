<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $table = 'project';

    protected $primaryKey = 'kode_project';

    public $incrementing = false;

    public $timestamps = false;
}
