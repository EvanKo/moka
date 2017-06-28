<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class NewOrder extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		Schema::create('Orders', function (Blueprint $table) {
			          $table->increments('id');
					            $table->integer('moka')->comment('moka');
					            $table->string('img')->comment('封面');
								          $table->string('title')->nullable()->comment('题目');
								          $table->string('content')->nullable()->comment('内容');
										            $table->integer('lasting')->nullable()->comment('时长');
										            $table->integer('price')->nullable()->comment('价格');
													          $table->integer('type')->nullable()->comment('类型');
													          $table->date('reserved')->nullable()->comment('工期');
															            $table->integer('area')->comment('地区');
															            $table->integer('finish')->default(0)->comment('完成');
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
