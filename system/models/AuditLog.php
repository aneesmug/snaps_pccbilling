<?php

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $table = 'sys_audit_logs';
    protected $guarded = [];

    public $timestamps = false;

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
