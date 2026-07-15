<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'users';

    // DB Anda: users PK = id_user
    protected $primaryKey = 'id_user';

    public $timestamps = false;

    public function akses()
    {
        return $this->hasMany(UserAccess::class, 'id_user', 'id_user');
    }

    public function getAuthPassword()
    {
        return $this->password;
    }

    protected $fillable = [
        // sesuai struktur tabel: id_user, username, password, signature_path, nama_lengkap, nama
        'username',
        'nama', // kolom nama (bukan name)

        'password',

        // untuk audit/signature (jika kolom ada di DB)
        'signature_path',

        // opsional (jika kolom ada di DB)
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
}
