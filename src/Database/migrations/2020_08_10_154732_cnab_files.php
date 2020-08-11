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
            $table->enum('type', array('rem', 'ret'))->nullable()->default(null);
            $table->integer('associated_ret');
            $table->integer('associated_rem');
            $table->string('url_file')->nullable();
            $table->float('total')->default(0);
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
        Schema::drop('cnab_files');
    }
}
