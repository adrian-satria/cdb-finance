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
        'alokasi_dana',
        'terserap',
    ];

    protected $casts = [
        'alokasi_dana' => 'decimal:2',
        'terserap' => 'decimal:2',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class, 'kode_project', 'kode_project');
    }

    public function scopeAvailable($query, string $minAmount = '0')
    {
        return $query->whereRaw('(alokasi_dana - terserap) >= ?', [$minAmount]);
    }

    public function scopeByProject($query, string $kodeProject)
    {
        return $query->where('kode_project', $kodeProject);
    }

    public function scopeByYear($query, int $tahun)
    {
        return $query->where('tahun', $tahun);
    }

    public function getRemainingBudgetAttribute(): string
    {
        return bcsub((string) $this->alokasi_dana, (string) $this->terserap, 2);
    }

    public function getUtilizationPercentageAttribute(): float
    {
        if ((float) $this->alokasi_dana <= 0) {
            return 0;
        }

        return round(((float) $this->terserap / (float) $this->alokasi_dana) * 100, 2);
    }

    public function hasAvailableFunds(string $amount): bool
    {
        $remaining = $this->remaining_budget;

        return bccomp($amount, $remaining, 2) !== 1;
    }

    public function absorb(string $amount): void
    {
        $this->increment('terserap', (float) $amount);
    }

    public function release(string $amount): void
    {
        $newTerserap = bcsub((string) $this->terserap, $amount, 2);
        if (bccomp($newTerserap, '0', 2) === -1) {
            $newTerserap = '0';
        }
        $this->update(['terserap' => $newTerserap]);
    }
}
