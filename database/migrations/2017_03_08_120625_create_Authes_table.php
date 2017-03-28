<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAuthesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Authes', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('moka')->unique()->comment('唯一身份');
            $table->string('authentication_name')->unique()->comment('认证姓名');
            $table->string('authentication')->comment('认证机构');
            $table->string('identification')->unique()->comment('身份证');
            $table->string('identification_img')->comment('手持身份证照片地址');
            $table->string('bussiness_img')->comment('营业执照照片地址');
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
        Schema::dropIfExists('Authes');
    }
}