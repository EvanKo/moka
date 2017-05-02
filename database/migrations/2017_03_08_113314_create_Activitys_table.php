<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateActivitysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Activities', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('moka')->comment('作者摩卡号');
            $table->integer('area')->nullable()->comment('分区');
            $table->longText('content')->comment('内容');
            $table->string('img')->nullable()->comment('图片地址(仅一张)');
            $table->integer('view')->default(0)->comment('访问量');
            $table->integer('pass')->default(0)->comment('审核');
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
        Schema::dropIfExists('Activitys');
    }
}
