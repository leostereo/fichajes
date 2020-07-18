<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFichajesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fichajes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
	    $table->string('diff')->default('');
	    $table->string('day');
            $table->biginteger('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('usuarios')->onDelete('cascade');
            $table->string('type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fichajes');
    }
}
