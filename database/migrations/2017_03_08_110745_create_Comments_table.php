<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCommentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Comments', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('target')->comment('目标种类');
            $table->integer('target_id')->comment('目标号码');
            $table->integer('moka')->comment('评论者摩卡号');
            $table->string('author')->comment('评论者昵称');
            $table->longText('head')->comment('评论者头像');
            $table->integer('answer')->nullable()->comment('回复者摩卡号');
            $table->string('answername')->nullable()->comment('回复者名字');
            $table->string('content')->comment('评论内容');
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
        Schema::dropIfExists('Comments');
    }
}
