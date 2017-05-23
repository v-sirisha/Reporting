<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class tags extends Model
{
    protected $table = 'tag';
    protected $fillable = ['platform_name','tag_id','tag_name','site_name','tag'];
}
