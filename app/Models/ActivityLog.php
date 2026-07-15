<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $table = 'activity_logs';
    protected $primaryKey = 'id_activity';
    protected $fillable = ['user_id', 'username', 'role', 'aktivitas', 'deskripsi', 'ip_address', 'user_agent', 'session_id'];
}
