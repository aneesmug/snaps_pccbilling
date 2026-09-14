<?php
use Illuminate\Database\Eloquent\Model;

class MediaFile extends Model
{
    protected $casts = [
        'settings' => 'array',
    ];
}
