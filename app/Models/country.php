<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class country extends Model
{
    protected $table = 'country';
    protected $fillable = ['country_name','analytics_country_group','deal_country_group'];
}
