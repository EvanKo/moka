<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePropertysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Properties', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('moka')->comment('身份');
            $table->integer('member')->default(0)->comment('上次会员等级');
            $table->date('member_date')->nullable()->comment('上次会员起始时间');
            $table->integer('member_last')->default(0)->comment('上次会员起持续时间');
            $table->integer('money')->default(0)->comment('账户剩余金币');
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
        Schema::dropIfExists('propertys');
    }
}
