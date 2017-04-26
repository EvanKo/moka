<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChatRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		Schema::create('ChatRecords', function( Blueprint $table){
			$table->increments('id');
			$table->string('from')->comment('消息发送者id');
			$table->string('fromName')->comment('消息发送者昵称');
			$table->string('to')->comment('消息接受者id');
			$table->string('toName')->comment('消息接受者昵称');
			$table->text('content')->comment('消息内容');
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
		Scheme::drop('ChatRecords');
    }
}
