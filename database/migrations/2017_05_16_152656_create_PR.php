<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePR extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('PR', function (Blueprint $table) {
            $table->increments('id');
            $table->string('platform_name');
            $table->string('site_name');
            $table->string('tag_id');
            $table->string('tag_name');
            $table->string('pp_name');
            $table->string('product_name');
            $table->string('actual_ad_unit');
            $table->string('final_placement_name');
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
        Schema::drop('PR');
    }
}
