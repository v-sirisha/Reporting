<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class platform_dates extends Model
{
    protected $table = 'platform_dates';
    protected $fillable = ['platform_name','start_date','end_date'];
}
