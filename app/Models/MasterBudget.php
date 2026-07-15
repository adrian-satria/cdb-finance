<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterBudget extends Model
{
    protected $table = 'master_budget';
    protected $primaryKey = 'id_budget';
    public $timestamps = false;

    protected $fillable = [
        'kode_project',
        'kode_budget',
        'nama_budget',
        'alokasi_dana'
    ];

    // Relasi balik ke model Project
    public function project()
    {
        return $this->belongsTo(Project::class, 'kode_project', 'kode_project');
    }
}