<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    protected $fillable = [
        'name',
        'phone',
        'email',
        'country',
        'interest',
        'message',
        'consent',
        'source',
    ];

    protected $casts = [
        'consent' => 'boolean',
    ];
}
