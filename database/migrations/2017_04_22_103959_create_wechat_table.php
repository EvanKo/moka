<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWechatTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		Schema::create('wechats',function(Blueprint $table){
			$table->increments('id');
			$table->string('openid');
			$table->string('mokaid');
			$table->string('nickname');
			$table->string('headimgurl');
			$table->string('country');
			$table->string('province');
			$table->string('city');
			$table->string('language');
			$table->string('sex');
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
		Schema::drop('wechats');
    }
}
