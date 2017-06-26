<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class NewAuth extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		Schema::create('Auths', function (Blueprint $table) {
			          $table->increments('id');
					            $table->integer('moka')->unique()->comment('唯一身份');
					            $table->string('realname')->unique()->comment('认证姓名');
								          $table->string('company')->comment('认证机构');
								          $table->string('companyname')->comment('认证机构名称');
										            $table->string('idcardnumber')->unique()->comment('身份证');
										            $table->string('img')->comment('手持身份证照片地址');
													          $table->integer('pass')->default(0)->comment('是否通过');
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
