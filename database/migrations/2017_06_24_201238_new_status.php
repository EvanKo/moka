<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class NewStatus extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('Status', function (Blueprint $table) {
          $table->increments('id');
          $table->integer('customer')->comment('客人moka');
          $table->integer('boss')->nullable()->comment('发起者moka');
          $table->integer('target')->nullable()->comment('目标');
          $table->longText('target_id')->nullable()->comment('目标id');
          $table->integer('status')->default(0)->comment('状态');
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
