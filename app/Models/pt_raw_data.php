<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class pt_raw_data extends Model
{
    protected $table = 'pt_raw_data';
    protected $fillable = ['date','tag','ad_unit','device','country','buyer','adserver_impressions','ssp_impressions','filled_impressions','gross_revenue'];
}
