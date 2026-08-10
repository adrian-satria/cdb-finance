<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Project extends Model
{
    protected $table = 'project';

    protected $primaryKey = 'kode_project';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = ['kode_project', 'nama_project'];

    public function areas(): BelongsToMany
    {
        return $this->belongsToMany(Area::class, 'project_area', 'kode_project', 'kode_area', 'kode_project', 'kode_area');
    }
}
