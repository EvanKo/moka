<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Orders', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('moka')->comment('作者摩卡号');
            $table->integer('area')->nullable()->comment('分区');
            $table->float('price')->comment('价格');
            $table->integer('type')->comment('类型(固定)');
            $table->integer('view')->default(0)->comment('浏览量');
            $table->string('content')->comment('内容');
            $table->longText('img')->comment('图片网址');
            $table->longText('imgnum')->comment('图片地址');
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
        Schema::dropIfExists('Orders');
    }
}
