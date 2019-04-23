<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateKeyRanksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('key_ranks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
            $table->string('host_id');
            $table->string('keyword');
            $table->integer('rank_amount');
            $table->text('rank_list');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('key_ranks');
    }
}
