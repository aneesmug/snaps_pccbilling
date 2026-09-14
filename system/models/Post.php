<?php
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $casts = [
        'settings' => 'array',
    ];
}
