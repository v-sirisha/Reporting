<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class device extends Model
{
    protected $table = 'device';
    protected $fillable = ['device_name','device_group'];
}
