<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteContentOverride extends Model
{
    protected $fillable = [
        'locale',
        'key',
        'value',
    ];
}
