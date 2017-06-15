<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SetRoleLocal extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		Schema::table('Roles', function($table)
			      {
					          $table->string('province')->default('广东')->comment('省');
							          $table->string('city')->default('广州')->comment('市');
							          $table->integer('area')->default(7)->comment('区');
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
