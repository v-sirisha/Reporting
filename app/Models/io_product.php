<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class io_product extends Model
{
    protected $table = 'io_product';
    protected $fillable = ['final_placement_tag','deal_type','parent_plusher','date_of_io_creation','publisher_manager','ym_manger','publisher_url','publisher_category','country_origin','language','business_name','billing_currency'];
}
