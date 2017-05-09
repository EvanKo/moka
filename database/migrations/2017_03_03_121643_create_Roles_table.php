<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRolesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Roles', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('moka')->unique()->comment('唯一身份');
            $table->string('tel')->unique()->comment('手机号');
            $table->integer('login')->default(1)->comment('登陆日数');
            $table->date('lastest')->comment('最近登陆日期');
            $table->integer('level')->default(0)->comment('会员等级');
            $table->integer('sex')->nullable()->comment('性别');
            $table->integer('v')->default(0)->comment('认证');
            $table->integer('fee')->default(0)->comment('打赏次数');
            $table->integer('role')->nullable()->comment('角色身份');
            $table->integer('fans')->default(0)->comment('粉丝数');
            $table->integer('idols')->default(0)->comment('关注数');
            $table->string('name')->comment('昵称');
            $table->string('province')->nullable()->comment('省份');
            $table->string('city')->nullable()->comment('城市');
            $table->integer('area')->nullable()->comment('分区');
            $table->string('head')->nullable()->comment('头像地址');
            $table->string('bgimg')->nullable()->comment('背景图地址');
            $table->string('password')->comment('密码');
            $table->longText('intro')->nullable()->comment('简介');
			$table->float('money')->defaut(0)->comment('账户余额');
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
        Schema::dropIfExists('Roles');
    }
}
