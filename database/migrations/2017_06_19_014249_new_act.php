<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class NewAct extends Migration
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
          $table->integer('type')->nullable()->comment('类型');
          $table->integer('area')->nullable()->comment('分区');
          $table->longText('content')->nullable()->comment('内容');
          $table->string('title')->nullable()->comment('标题');
          $table->float('price')->nullable()->comment('价格');
          $table->date('start')->nullable()->comment('开始');
          $table->date('end')->nullable()->comment('结束');
          $table->string('img')->comment('图片地址(仅一张)');
          $table->integer('view')->default(0)->comment('访问量');
          $table->integer('pass')->default(0)->comment('审核');
          $table->integer('finish')->default(0)->comment('完成');
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
        //
    }
}
