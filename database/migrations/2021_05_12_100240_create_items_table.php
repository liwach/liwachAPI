<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGoodsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('items', function (Blueprint $table) {
        $table->bigIncrements('id');
		$table->text('picture');
		$table->string('status',30);
		$table->integer('number_of_flag',);
		$table->integer('number_of_request',);
		$table->integer('bartering_location_id'); 
        $table->bigInteger('type_id');
        $table->timestamps();
        $table->foreign('bartering_location_id')->references('id')->on('addresses');
        $table->foreign('type_id')->references('id')->on('types');});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('goods');
    }
}
