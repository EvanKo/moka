<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlbumAdd extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		Schema::table('Album', function($table)
			      {
					          $table->string('albumname')->nullable()->comment('名字');
							          $table->integer('albumtype')->nullable()->comment('类型');
							          $table->integer('albumstyle')->nullable()->comment('风格');
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
