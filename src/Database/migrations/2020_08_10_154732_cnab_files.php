<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CnabFiles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cnab_files', function(Blueprint $table) {
            $table->increments('id');
            $table->string('rem_url_file')->nullable();
            $table->string('ret_url_file')->nullable();
            $table->float('rem_total')->default(0);
            $table->float('ret_total')->default(0);
            $table->dateTime('date_rem', 0)->default(0);
            $table->dateTime('date_ret', 0)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('cnab_files');
    }
}
