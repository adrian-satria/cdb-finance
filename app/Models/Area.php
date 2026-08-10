<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Area extends Model
{
    protected $table = 'area';

    protected $primaryKey = 'kode_area';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = ['kode_area', 'nama_area'];

    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'project_area', 'kode_area', 'kode_project', 'kode_area', 'kode_project');
    }
}
