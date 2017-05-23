<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class tag_index extends Model
{
    protected $table = 'tag_index';
    protected $fillable = ['tag_id','final_placement_name'];
}
