<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMokasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Mokas', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('moka')->comment('模特摩卡号');
            $table->integer('area')->nullable()->comment('分区');
            $table->integer('size')->comment('摩卡框架');
            $table->integer('finish')->default(0)->comment('完成');
            $table->integer('view')->comment('访问量');
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
        Schema::dropIfExists('Mokas');
    }
}
