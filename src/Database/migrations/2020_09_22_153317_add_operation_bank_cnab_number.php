<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOperationBankCnabNumber extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Settings::create([
            "key"           => "rem_operation",
            "value"         => '',
            "tool_tip"      => '(ARQUIVO DE REMESSA) PT-BR: Numero de operacao do banco (se a conta tiver). EN: Number of bank operation',
            "page"          => '0',
            "category"      => "0",
            "sub_category"  => "0"
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
