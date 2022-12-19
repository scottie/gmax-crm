<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExpensemanagersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('expensemanagers', function (Blueprint $table) {
            $table->id();
            $table->string('prid')->nullable();
            $table->string('item')->nullable();
            $table->string('amount')->nullable();
            $table->string('date')->nullable();
            $table->string('bill')->nullable();
            $table->string('auth')->nullable();
            $table->string('status')->nullable();
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
        Schema::dropIfExists('expensemanagers');
    }
}
