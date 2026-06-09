<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteImageOverride extends Model
{
    protected $fillable = [
        'key',
        'path',
    ];
}
