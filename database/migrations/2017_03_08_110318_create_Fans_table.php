<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Fans', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('fan')->comment('粉丝摩卡号');
            $table->longText('fanhead')->nullable()->comment('粉丝头像');
            $table->integer('fansex')->comment('粉丝性别');
            $table->string('fanname')->comment('粉丝名字');
            $table->integer('idol')->comment('偶像摩卡号');
            $table->longText('idolhead')->nullable()->comment('偶像头像');
            $table->integer('idolsex')->comment('偶像性别');
            $table->string('idolname')->comment('偶像名字');
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
        Schema::dropIfExists('Fans');
    }
}
