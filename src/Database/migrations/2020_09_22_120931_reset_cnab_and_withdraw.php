<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;


class ResetCnabAndWithdraw extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        
        //Delete all withdraw 
        DB::table('withdraw')->whereNotNull('id')->delete();
        
        //reset id = 11. id >= 11 its necessary in some cnab files banks
        DB::update("ALTER TABLE withdraw AUTO_INCREMENT = 11;");


        //Delete all withdraw 
        DB::table("cnab_files")->whereNotNull('id')->delete();
        
            //reset id = 11. id >= 11 its necessary in some cnab files banks
        DB::update("ALTER TABLE cnab_files AUTO_INCREMENT = 11;");
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        
    }
}