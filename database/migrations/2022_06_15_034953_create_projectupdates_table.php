<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProjectupdatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('projectupdates', function (Blueprint $table) {
            $table->id();
            $table->string('projectid')->nullable();
            $table->string('taskid')->nullable();
            $table->string('auth')->nullable();
            $table->string('message')->nullable();
            $table->string('file')->nullable();
            $table->string('image')->nullable();
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
        Schema::dropIfExists('projectupdates');
    }
}
