<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePRTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('PR_table', function (Blueprint $table) {
            $table->increments('id');
            $table->string('tag_index_placement');
            $table->string('io_publisher_name');
            $table->string('product_name');
            $table->string('actual_ad_unit');
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
        Schema::drop('PR_table');
    }
}
