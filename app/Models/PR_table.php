<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PR_table extends Model
{
    protected $table = 'PR_table';
    protected $fillable = ['tag_index_placement','io_publisher_name','product_name','actual_ad_unit'];
}
