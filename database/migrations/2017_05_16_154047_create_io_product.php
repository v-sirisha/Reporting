<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateIoProduct extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('io_product', function (Blueprint $table) {
            $table->increments('id');
            $table->string('final_placement_tag');
            $table->string('deal_type');
            $table->string('parent_plusher');
            $table->string('date_of_io_creation');
            $table->string('publisher_manager');
            $table->string('ym_manger');
            $table->string('publisher_url');
            $table->string('publisher_category');
            $table->string('country_origin');
            $table->string('language');
            $table->string('business_name');
            $table->string('billing_currency');
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
        Schema::drop('io_product');
    }
}
