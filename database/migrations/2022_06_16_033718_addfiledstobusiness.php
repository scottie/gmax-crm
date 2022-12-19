<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Addfiledstobusiness extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->string('headerimage')->nullable();
            $table->string('enablelogo')->nullable();
            $table->string('footerimage')->nullable();
              
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropColumn('headerimage');
            $table->dropColumn('enablelogo');
            $table->dropColumn('footerimage');           
        });
    }
}
