<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class SppHistory extends Model
{
    protected $table = 'spp_history';
    protected $primaryKey = 'id_history';
    protected $fillable = ['no_surat', 'status_dari', 'status_ke', 'posisi_dari', 'posisi_ke', 'aktor_username', 'aktor_role', 'keterangan', 'payload_before', 'payload_after'];
    protected $casts = ['payload_before' => 'array', 'payload_after' => 'array'];

    public function surat()
    {
        return $this->belongsTo(SuratPermintaan::class, 'no_surat', 'no_surat');
    }
}
