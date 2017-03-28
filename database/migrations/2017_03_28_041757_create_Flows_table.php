<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFlowsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Flows', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('payer')->comment('支付者moka');
            $table->integer('earner')->comment('收入者moka');
            $table->longText('way')->comment('支付详情');
            $table->integer('money')->comment('金额数');
            $table->integer('income')->comment('公司收入费用');
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
        Schema::dropIfExists('Flows');
    }
}
