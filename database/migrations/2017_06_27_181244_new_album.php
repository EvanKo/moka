<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class NewAlbum extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		Schema::create('Album', function (Blueprint $table) {
			          $table->increments('id');
					            $table->integer('moka')->comment('moka');
					            $table->string('img')->comment('封面');
								          $table->integer('sum')->default(0)->comment('总数');
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
