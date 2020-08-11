<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Withdrawals extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('withdraw', function(Blueprint $table) {
            $table->increments('id');

            $table->integer('finance_withdraw_id')->unsigned();
            $table->foreign('finance_withdraw_id')->references('id')->on('finance')->onUpdate('RESTRICT')->onDelete('RESTRICT');

            $table->integer('finance_withdraw_tax_id')->unsigned()->nullable();
            $table->foreign('finance_withdraw_tax_id')->references('id')->on('finance')->onUpdate('RESTRICT')->onDelete('RESTRICT');

            $table->integer('cnab_file_id')->unsigned()->nullable();
            $table->foreign('cnab_file_id')->references('id')->on('cnab_files')->onUpdate('RESTRICT')->onDelete('RESTRICT');

            $table->enum('type', array('requested', 'awaiting return', 'concluded', 'error'))->nullable()->default(null);
            
            $table->string('bank_receipt_url')->nullable();
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
        Schema::drop('withdraw');
    }
}
