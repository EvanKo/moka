<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePhotosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Photos', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('mokaid')->comment('摩卡地址');
            $table->longText('img_s')->comment('小图网址');
            $table->longText('img_snum')->comment('小图地址');
            $table->longText('img_l')->comment('大图网址');
            $table->longText('img_lnum')->comment('大图地址');
            $table->integer('view')->default(0)->comment('浏览量');
            $table->integer('fee')->default(0)->comment('打赏次数');
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
        Schema::dropIfExists('Photos');
    }
}
