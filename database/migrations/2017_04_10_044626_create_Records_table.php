<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateListsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Records', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('moka')->comment('作者摩卡号');
            $table->integer('area')->nullable()->comment('分区');
            $table->integer('view')->default(0)->comment('浏览量');
            $table->integer('target')->comment('目标');
            $table->integer('target_id')->comment('目标id');
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
        Schema::dropIfExists(':Lists');
    }
}
