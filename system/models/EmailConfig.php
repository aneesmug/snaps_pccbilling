<?php
use Illuminate\Database\Eloquent\Model;

class EmailConfig extends Model
{
    protected $table = 'sys_emailconfig';
    protected $fillable = ['method', 'host', 'username', 'password', 'port', 'secure'];
    public $timestamps = false;
}
