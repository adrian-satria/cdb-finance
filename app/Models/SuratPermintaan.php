<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SuratPermintaan extends Model
{
    protected $table = 'surat_permintaan';

    protected $primaryKey = 'no_surat';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'no_surat',
        'tanggal',
        'jenis_permintaan',
        'kode_project',
        'kode_area',
        'sumber_dana',
        'bank_tujuan',
        'no_rekening_tujuan',
        'nama_rekening_tujuan',
        'total_nominal',
        'status_surat',
        'posisi_saat_ini',
        'id_maker',
        'keterangan_checker',
        'updated_at',
    ];

    public $timestamps = false;

    protected $casts = [
        'tanggal' => 'date',
        'total_nominal' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ==========================================
    // Relationships
    // ==========================================

    public function maker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_maker', 'id_user');
    }

    public function details(): HasMany
    {
        return $this->hasMany(SuratPermintaanDetail::class, 'no_surat', 'no_surat');
    }

    public function files(): HasMany
    {
        return $this->hasMany(SuratPermintaanFiles::class, 'no_surat', 'no_surat');
    }

    public function history(): HasMany
    {
        return $this->hasMany(SppHistory::class, 'no_surat', 'no_surat');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'kode_project', 'kode_project');
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class, 'kode_area', 'kode_area');
    }

    // ==========================================
    // Scopes
    // ==========================================

    public function scopePending($query)
    {
        return $query->where('status_surat', 'Pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status_surat', 'Approved');
    }

    public function scopeDisbursed($query)
    {
        return $query->where('status_surat', 'Disbursed');
    }

    public function scopeByProject($query, string $kodeProject)
    {
        return $query->where('kode_project', $kodeProject);
    }

    public function scopeAwaitingMyApproval($query, string $role)
    {
        return $query->where('posisi_saat_ini', $role)
            ->whereIn('status_surat', ['Pending', 'Pending Director Otorisasi']);
    }

    // ==========================================
    // Accessors
    // ==========================================

    public function getTotalNominalFormattedAttribute(): string
    {
        return 'Rp '.number_format((float) $this->total_nominal, 0, ',', '.');
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status_surat) {
            'Pending' => '<span class="badge bg-warning text-dark">Pending</span>',
            'Pending Director Otorisasi' => '<span class="badge bg-info text-dark">Pending Director</span>',
            'Approved' => '<span class="badge bg-success">Approved</span>',
            'Disbursed' => '<span class="badge bg-primary">Disbursed</span>',
            'Revisi' => '<span class="badge bg-secondary">Revisi</span>',
            'Rejected' => '<span class="badge bg-danger">Rejected</span>',
            default => '<span class="badge bg-light text-dark">'.e($this->status_surat).'</span>',
        };
    }

    public function getIsEditableAttribute(): bool
    {
        return in_array($this->status_surat, ['Pending', 'Revisi'], true);
    }

    // ==========================================
    // Business Methods
    // ==========================================

    public function canBeApprovedBy(string $role): bool
    {
        if ($role === 'ADMIN') {
            return true;
        }

        return $this->posisi_saat_ini === $role;
    }

    public function routeTo(string $nextPosition): void
    {
        $this->update(['posisi_saat_ini' => $nextPosition]);
    }

    public function markAsDisbursed(): void
    {
        $this->update([
            'status_surat' => 'Disbursed',
            'posisi_saat_ini' => 'FINISH',
        ]);
    }

    public function markAsRejected(?string $alasan = null): void
    {
        $this->update([
            'status_surat' => 'Rejected',
            'posisi_saat_ini' => 'REJECTED',
            'keterangan_checker' => $alasan,
        ]);
    }

    public function markAsRevised(?string $alasan = null, ?string $byRole = null): void
    {
        $this->update([
            'status_surat' => 'Revisi',
            'posisi_saat_ini' => 'MAKER',
            'keterangan_checker' => $alasan,
        ]);
    }

    public function updateWorkflow(string $status, string $nextPosition, ?string $keterangan = null): void
    {
        $this->update([
            'status_surat' => $status,
            'posisi_saat_ini' => $nextPosition,
            'keterangan_checker' => $keterangan ?? $this->keterangan_checker,
        ]);
    }

    public function hasMaker(int $userId): bool
    {
        return (int) $this->id_maker === $userId;
    }
}
