<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $table = 'notifications';

    protected $primaryKey = 'id_notifikasi';

    protected $fillable = ['user_id', 'type', 'title', 'message', 'reference_type', 'reference_id', 'is_read', 'read_at'];

    protected $casts = ['is_read' => 'boolean', 'read_at' => 'datetime'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id_user');
    }

    public function scopeUnread($q)
    {
        return $q->where('is_read', false);
    }

    public function scopeForUser($q, $userId)
    {
        return $q->where('user_id', $userId);
    }
}
