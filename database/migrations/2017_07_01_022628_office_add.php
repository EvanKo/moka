<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class OfficeAdd extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		Schema::table('Photos', function($table)
			      {
					          $table->string('ps')->nullable()->comment('描述');
							          // $table->integer('photonum')->nullable()->comment('拍摄片数');
							  //         // $table->integer('focusphoto')->nullable()->comment('精修照片数');
				  });
		      Schema::table('Orders', function($table)
				        {
							        $table->string('local')->nullable()->comment('拍摄地点');
									        $table->integer('photonum')->nullable()->comment('拍摄片数');
									        $table->integer('focusphoto')->nullable()->comment('精修照片数');
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
