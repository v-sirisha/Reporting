<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PR extends Model
{
    protected $table = 'PR';
    protected $fillable = ['platform_name','site_name','tag_id','tag_name','pp_name','product_name','actual_ad_unit','final_placement_name'];
}
