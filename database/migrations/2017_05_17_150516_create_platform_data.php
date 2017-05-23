<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePlatformData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('platform_data', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamp('date');
            $table->string('platform_name');
            $table->string('site_name');
            $table->string('tag_id');
            $table->string('tag_name');
            $table->string('ad_unit');
            $table->string('device');
            $table->string('country');
            $table->string('buyer');
            $table->integer('adserver_impressions');
            $table->integer('ssp_impressions');
            $table->integer('filled_impressions');
            $table->double('gross_revenue', 15, 8);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('platform_data');
    }
}
