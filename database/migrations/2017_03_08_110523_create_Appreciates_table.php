<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAppreciatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Appreciates', function (Blueprint $table) {
            $table->increments('id')->comment('赞ID');
            $table->integer('target')->comment('目标种类');
            $table->integer('target_id')->comment('目标号码');
            $table->integer('moka')->comment('称赞者摩卡号');
            $table->longText('head')->comment('称赞者头像');
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
        Schema::dropIfExists('Appreciates');
    }
}
