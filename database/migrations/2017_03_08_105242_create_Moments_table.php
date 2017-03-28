<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMomentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Moments', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('moka')->comment('作者摩卡号');
            $table->longText('content')->nullable()->comment('内容');
            $table->string('img')->comment('图片地址(仅一张)');
            $table->string('imgnum')->comment('图片编号');
            $table->integer('area')->nullable()->comment('分区');
            $table->integer('view')->default(0)->comment('访问量');
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
        Schema::dropIfExists('Moments');
    }
}
