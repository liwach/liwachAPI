<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateItemsTable extends Migration
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
            $table->text('picture')->nullable();
            $table->string('name', 50)->nullable();
            $table->string('description', 255)->nullable();
            $table->string('status', 30)->nullable();            
            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->integer('number_of_flag')->nullable();
            $table->integer('number_of_request')->nullable();
            $table->bigInteger('bartering_location_id')->unsigned()->nullable();
            $table->bigInteger('type_id')->unsigned()->nullable();
            $table->timestamps();
            $table->foreign('bartering_location_id')->references('id')->on('addresses');
            $table->foreign('type_id')->references('id')->on('types');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('items');
    }
}
