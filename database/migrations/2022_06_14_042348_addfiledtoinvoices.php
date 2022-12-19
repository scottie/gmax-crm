<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Addfiledtoinvoices extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('projectid')->nullable();
            $table->string('taskid')->nullable();
            $table->string('recorring')->nullable();
            $table->string('recorringtype')->nullable();  
            $table->string('recorringnextdate')->nullable();   
            $table->string('recorringcreated')->nullable(); 
              
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('projectid');
            $table->dropColumn('taskid');
            $table->dropColumn('recorring');
            $table->dropColumn('recorringtype');
            $table->dropColumn('recorringnextdate');
            $table->dropColumn('recorringcreated');

     
        });
    }
}
