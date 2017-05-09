<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePayRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		Schema::create('PayRecords', function( Blueprint $table){
			$table->increments('id');
			$table->string('moka')->comment('用户id');
			$table->string('name')->comment('昵称');
			$table->string('openid');
			$table->string('tomoka')->nullable();
			$table->string('tel')->comment('手机');
			$table->string('type')->comment('支付类型');
			$table->string('time')->comment('购买时长')->nullable();
			$table->string('amount')->comment('金额');
			$table->integer('status')->default(0)->comment('支付状态,0代表未支付，1代表已支付');
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
		Schema::drop('PayRecords');
    }
}
