<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAccess extends Model
{
    protected $table = 'user_access';
    protected $primaryKey = 'id_access';
    public $timestamps = false;
    protected $fillable = ['id_user', 'jabatan', 'role', 'kode_area', 'kode_project'];
}