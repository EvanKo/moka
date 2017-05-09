<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNoticesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Notices', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('target')->comment('通知者摩卡号');
            $table->integer('all')->comment('是否为全体通告');
            $table->string('title')->comment('题目');
            $table->longText('content')->comment('内容');
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
        Schema::dropIfExists('Notices');
    }
}
