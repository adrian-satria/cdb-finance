<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'users';

    protected $primaryKey = 'id_user';

    public $timestamps = false;

    protected $fillable = [
        'name',
        'username',
        'nama',
        'email',
        'password',
        'signature_path',
        'nama_lengkap',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // ==========================================
    // Relationships
    // ==========================================

    public function akses()
    {
        return $this->hasMany(UserAccess::class, 'id_user', 'id_user');
    }

    public function sppAsMaker()
    {
        return $this->hasMany(SuratPermintaan::class, 'id_maker', 'id_user');
    }

    // ==========================================
    // Role Check Methods
    // ==========================================

    public function hasRole(string $role): bool
    {
        return $this->akses()->where('role', $role)->exists();
    }

    public function hasAnyRole(array $roles): bool
    {
        return $this->akses()->whereIn('role', $roles)->exists();
    }

    public function getActiveRolesAttribute(): Collection
    {
        return $this->akses()->pluck('role')->unique();
    }

    public function getPrimaryRoleAttribute(): ?string
    {
        return $this->akses()->value('role');
    }

    // ==========================================
    // Access Check Methods
    // ==========================================

    public function hasAccessToProject(?string $kodeProject): bool
    {
        if ($kodeProject === null || $kodeProject === 'all') {
            return true;
        }

        return $this->akses()
            ->where(function ($q) use ($kodeProject) {
                $q->where('kode_project', $kodeProject)
                    ->orWhere('kode_project', 'all')
                    ->orWhereNull('kode_project');
            })
            ->exists();
    }

    public function hasAccessToArea(?string $kodeArea): bool
    {
        if ($kodeArea === null) {
            return true;
        }

        return $this->akses()
            ->where(function ($q) use ($kodeArea) {
                $q->where('kode_area', $kodeArea)
                    ->orWhereNull('kode_area');
            })
            ->exists();
    }

    public function getAuthPassword()
    {
        return $this->password;
    }
}
