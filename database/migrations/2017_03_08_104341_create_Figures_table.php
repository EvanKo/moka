<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFiguresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Figures', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('moka')->unique()->comment('模特身份标识');
            $table->integer('height')->comment('身高/cm');
            $table->integer('weight')->comment('体重/kg');
            $table->integer('bust')->comment('胸围/cm');
            $table->integer('waist')->comment('腰围/cm');
            $table->integer('hips')->comment('臀围/cm');
            $table->integer('shoe')->comment('鞋码');
            $table->longText('exp')->nullable()->comment('工作经验');
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
        Schema::dropIfExists('Figures');
    }
}
